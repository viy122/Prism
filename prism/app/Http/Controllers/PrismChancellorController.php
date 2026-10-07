<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSignatureQueue;
use App\Http\Controllers\Concerns\RendersPpmpDocument;
use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\BudgetProposalReview;
use App\Models\Office;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrismChancellorController extends Controller
{
    use HandlesSignatureQueue;
    use RendersPpmpDocument;

    public function ppmpDocument(BudgetProposal $proposal): View
    {
        return $this->ppmpDocumentView($proposal);
    }

    protected function queueRoleCode(): string
    {
        return 'chancellor';
    }

    protected function queueRoutePrefix(): string
    {
        return 'chancellor';
    }

    /**
     * Chancellor is the fixed final signatory on all three document types
     * (PR, AOC, PO) — shows EVERY document, not just currently-actionable
     * ones. `canAct` tells the UI which rows are actually his turn right now.
     */
    public function forMySignature(): View
    {
        return view('prism.shared.for-my-signature', $this->withCommon('for-my-signature', [
            'pageTitle' => 'For My Signature',
            'documents' => $this->signatureHistoryRows($this->signatureDocTypes()),
            'refreshUrl' => route($this->queueRoutePrefix() . '.for-my-signature.refresh'),
        ]));
    }

    public function forMySignatureRefresh(): JsonResponse
    {
        return $this->signatureHistoryJson($this->signatureDocTypes());
    }

    private function signatureDocTypes(): array
    {
        return ['pr', 'aoc', 'po'];
    }

    /**
     * Every "is this item actually procured" check below goes through a real
     * PPMP-item → PR match (matchPrItemsByOfficeAndName()) and the matched
     * PR's own lifecycleBucket() — never the raw PurchaseRequest::status
     * column (Procurement almost never writes the literal values
     * 'completed'/'approved'/'delayed' to it; the real vocabulary is a much
     * larger granular set) and never a same-office-wide flag applied
     * uniformly to every quarter, which is what made the old quarterly grid
     * mark every past quarter "Completed" the moment the office had any one
     * completed PR at all, regardless of that quarter's own items.
     */
    public function dashboard(): View
    {
        $allItems = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->get();

        $officeIds     = $allItems->pluck('budgetProposal.office_id')->filter()->unique()->values();
        $prItemMatches = $this->matchPrItemsByOfficeAndName($officeIds);

        $matchedPrFor = function ($item) use ($prItemMatches) {
            $officeId = $item->budgetProposal?->office_id;

            return $prItemMatches->get($officeId . '|' . strtolower(trim($item->name)))?->purchaseRequest;
        };
        $isItemProcured = fn ($item) => $matchedPrFor($item)?->lifecycleBucket() === 'completed';
        $isItemUtilized = fn ($item) => in_array($matchedPrFor($item)?->lifecycleBucket(), ['in_progress', 'completed'], true);

        $totalAppItems = $allItems->count();
        $itemsProcured = $allItems->filter($isItemProcured)->count();
        $itemsPending  = max(0, $totalAppItems - $itemsProcured);

        $approvedBudget    = (float) $allItems->sum('estimated_total_cost');
        $utilized          = (float) $allItems->filter($isItemUtilized)->sum('estimated_total_cost');
        $campusUtilization = $approvedBudget > 0 ? min(100, round(($utilized / $approvedBudget) * 100)) : 0;

        $currentQ       = $this->currentQuarter();
        $currentQNumber = (int) ltrim($currentQ, 'Q');

        $offices = Office::has('budgetProposals')
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])->with('items'),
                'purchaseRequests',
            ])
            ->get();

        $officeProcurementStatuses = $offices->map(function ($office) use ($matchedPrFor) {
            $items = $office->budgetProposals->flatMap->items;
            $totalItems = $items->count();
            $procuredItems = $items->filter(fn ($item) => $matchedPrFor($item)?->lifecycleBucket() === 'completed')->count();
            $completionRate = $totalItems > 0 ? round(($procuredItems / $totalItems) * 100) : 0;
            $quarters = collect(['Q1', 'Q2', 'Q3', 'Q4'])
                ->mapWithKeys(function ($quarter) use ($items, $matchedPrFor) {
                    $quarterItems = $items->where('target_quarter', $quarter);
                    $quarterTotal = $quarterItems->count();
                    $quarterItemRows = $quarterItems
                        ->sortBy(fn ($item) => strtolower($item->name ?? ''))
                        ->map(function ($item) use ($matchedPrFor) {
                            $pr = $matchedPrFor($item);
                            $bucket = $pr?->lifecycleBucket();

                            return [
                                'name'     => $item->name ?: 'Untitled item',
                                'proposal' => $item->budgetProposal?->code ?: 'PPMP',
                                'prNumber' => $pr?->number ?: 'No PR yet',
                                'status'   => $pr ? ucfirst(str_replace('_', ' ', $bucket)) : 'Not Started',
                                'procured' => $bucket === 'completed',
                            ];
                        })
                        ->values();
                    $quarterProcured = $quarterItemRows->where('procured', true)->count();
                    $quarterRate = $quarterTotal > 0 ? round(($quarterProcured / $quarterTotal) * 100) : 0;

                    return [
                        strtolower($quarter) => [
                            'totalItems'       => $quarterTotal,
                            'procuredItems'    => $quarterProcured,
                            'remainingItems'   => max(0, $quarterTotal - $quarterProcured),
                            'completionRate'   => $quarterRate,
                            'status'           => $quarterTotal === 0
                                ? 'No Items'
                                : ($quarterProcured === $quarterTotal ? 'Complete' : ($quarterProcured > 0 ? 'In Progress' : 'Pending')),
                            'procuredList'     => $quarterItemRows->where('procured', true)->values()->all(),
                            'notProcuredList'  => $quarterItemRows->where('procured', false)->values()->all(),
                        ],
                    ];
                });

            return [
                'office'         => $office->code,
                'officeName'     => $office->name,
                'q1'             => $quarters->get('q1'),
                'q2'             => $quarters->get('q2'),
                'q3'             => $quarters->get('q3'),
                'q4'             => $quarters->get('q4'),
                'totalItems'     => $totalItems,
                'procuredItems'  => $procuredItems,
                'remainingItems' => max(0, $totalItems - $procuredItems),
                'completionRate' => $completionRate,
                'status'         => $procuredItems === $totalItems && $totalItems > 0
                    ? 'Complete'
                    : ($procuredItems > 0 ? 'In Progress' : 'Pending'),
            ];
        })->filter(fn ($row) => $row['totalItems'] > 0)->values()->all();

        $officeMetrics = $offices->map(function ($office) use ($currentQNumber, $matchedPrFor) {
            $items    = $office->budgetProposals->flatMap->items;
            $budget   = (float) $items->sum('estimated_total_cost');
            $utilized = (float) $items
                ->filter(fn ($item) => in_array($matchedPrFor($item)?->lifecycleBucket(), ['in_progress', 'completed'], true))
                ->sum('estimated_total_cost');
            $pct      = $budget > 0 ? min(100, round(($utilized / $budget) * 100)) : 0;
            $forecast = $currentQNumber > 0 ? min(100, (int) round($pct / $currentQNumber * 4)) : $pct;
            $risk     = $pct >= 70 ? 'On Track' : ($pct >= 40 ? 'At Risk' : 'Critical');

            return [
                'office'             => $office->code,
                'officeName'         => $office->name,
                'currentUtilization' => $pct,
                'forecast'           => $forecast,
                'risk'               => $risk,
                'budget'             => $budget,
                'utilized'           => $utilized,
            ];
        })->filter(fn ($r) => $r['budget'] > 0)->values();

        $forecasts            = $officeMetrics
            ->sortBy(function ($row) {
                $riskOrder = ['Critical' => 0, 'At Risk' => 1, 'On Track' => 2];

                return sprintf(
                    '%02d|%03d|%03d|%s',
                    $riskOrder[$row['risk'] ?? ''] ?? 9,
                    (int) ($row['forecast'] ?? 0),
                    (int) ($row['currentUtilization'] ?? 0),
                    $row['office'] ?? ''
                );
            })
            ->values()
            ->all();
        $utilizationRankings  = $officeMetrics->sortByDesc('currentUtilization')->values()
            ->map(fn ($r, $i) => array_merge($r, ['rank' => $i + 1, 'utilization' => $r['currentUtilization']]))->all();

        $quarterEnd = fn ($q) => match ($q) {
            'Q1'    => now()->startOfYear()->addMonths(3),
            'Q2'    => now()->startOfYear()->addMonths(6),
            'Q3'    => now()->startOfYear()->addMonths(9),
            default => now()->startOfYear()->addMonths(12),
        };
        $categoryFor = fn (BudgetProposalItem $item) => $item->category ?: ($item->ppmpCategoryLabel() ?: 'General');

        // Full overdue set (not yet procured, past its target quarter) — the
        // alert list below shows every one of these (scrollable in the view),
        // not just a slice.
        $overdueItemsAll = $allItems->filter(fn ($item) =>
            $item->target_quarter
            && $item->target_quarter < $currentQ
            && $matchedPrFor($item)?->lifecycleBucket() !== 'completed'
        )->sortBy(fn ($item) => implode('|', [
            strtolower($categoryFor($item)),
            $item->budgetProposal?->office?->code ?? '',
            strtolower($item->name ?? ''),
        ]))->values();

        $overdueAlerts = $overdueItemsAll->map(function ($item) use ($matchedPrFor, $quarterEnd, $categoryFor) {
            $pr = $matchedPrFor($item);

            return [
                'item'        => $item->name,
                'category'    => $categoryFor($item),
                'office'      => $item->budgetProposal?->office?->code ?? '—',
                'prNumber'    => $pr?->number ?? 'Not yet raised',
                'daysOverdue' => (int) now()->diffInDays($quarterEnd($item->target_quarter)),
                'status'      => $pr ? ucfirst(str_replace('_', ' ', $pr->lifecycleBucket())) : 'Not Started',
                'action'      => $pr ? 'Follow up the current PR movement.' : 'Create or intervene on the pending PR.',
            ];
        })->values()->all();

        $overdueAlertGroups = collect($overdueAlerts)
            ->groupBy('category')
            ->sortKeys()
            ->map(fn ($alerts, $category) => [
                'category' => $category,
                'count'    => $alerts->count(),
                'alerts'   => $alerts->sortByDesc('daysOverdue')->values()->all(),
            ])
            ->values()
            ->all();

        $kpiDetails = $this->dashboardKpiDetails($allItems, $matchedPrFor, $officeMetrics, $currentQ);

        return view('prism.chancellor.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Chancellor Campus Monitoring Dashboard',
            'awaitingSignature' => app(\App\Services\SignatoryQueueService::class)->countForRole('chancellor'),
            'summary'   => [
                'totalAppItems'     => $totalAppItems,
                'itemsProcured'     => $itemsProcured,
                'itemsPending'      => $itemsPending,
                'itemsOverdue'      => $overdueItemsAll->count(),
                'campusUtilization' => $campusUtilization,
            ],
            'officeProcurementStatuses' => $officeProcurementStatuses,
            'forecasts'           => $forecasts,
            'utilizationRankings' => $utilizationRankings,
            'overdueAlerts'       => $overdueAlerts,
            'overdueAlertGroups'  => $overdueAlertGroups,
            'kpiDetails'          => $kpiDetails,
            'itemStatusChart'     => [
                'procured' => $itemsProcured,
                'pending'  => max(0, $itemsPending - $overdueItemsAll->count()),
                'overdue'  => $overdueItemsAll->count(),
            ],
            'officeUtilizationChart' => $officeMetrics->map(fn ($r) => [
                'office'   => $r['office'],
                'budget'   => round($r['budget']),
                'utilized' => round($r['utilized']),
            ])->values()->all(),
        ]));
    }

    private const CHANCELLOR_DETAIL_RELATIONS = ['office', 'submittedBy', 'items.marketReferences', 'reviews.reviewedBy'];

    public function budgetApproval(): View
    {
        $proposals = BudgetProposal::with(self::CHANCELLOR_DETAIL_RELATIONS)
            ->where('status', 'endorsed')
            ->latest('reviewed_at')
            ->get()
            ->map(fn ($p) => $this->formatProposalForChancellor($p))
            ->all();

        // Proposals already decided on — kept visible here instead of just
        // vanishing from the queue once acted on, so an approved/returned
        // PPMP is still findable, not merely gone.
        $archivedProposals = BudgetProposal::with(self::CHANCELLOR_DETAIL_RELATIONS)
            ->whereIn('status', ['approved', 'returned'])
            // approve() doesn't touch reviewed_at (only returnProposal() does),
            // so sort by whichever of the two actually reflects the Chancellor's
            // own decision, not just Finance's earlier endorsement timestamp.
            ->orderByRaw('COALESCE(approved_at, reviewed_at) DESC')
            ->get()
            ->map(fn ($p) => $this->formatProposalForChancellor($p))
            ->all();

        return view('prism.chancellor.budget-approval', $this->withCommon('budget-approval', [
            'pageTitle'          => 'Chancellor Budget Approval',
            'proposals'          => $proposals,
            'archivedProposals'  => $archivedProposals,
            'offices'            => Office::whereHas('budgetProposals')->select('id', 'code', 'name')->orderBy('code')->get()->toArray(),
        ]));
    }

    public function approve(Request $request, BudgetProposal $proposal): JsonResponse
    {
        abort_if($proposal->status !== 'endorsed', 403);

        $remarks = $request->input('remarks', '');

        $proposal->update([
            'status'              => 'approved',
            'approved_at'         => now(),
            'approved_by_user_id' => auth()->id(),
        ]);

        BudgetProposalReview::create([
            'budget_proposal_id'  => $proposal->id,
            'reviewed_by_user_id' => auth()->id(),
            'action'              => 'approve',
            'status_from'         => 'endorsed',
            'status_to'           => 'approved',
            'remarks'             => $remarks,
            'reviewed_at'         => now(),
        ]);

        NotificationService::proposalApproved($proposal);

        $proposal->load(self::CHANCELLOR_DETAIL_RELATIONS);

        return response()->json([
            'success'  => true,
            'message'  => 'Proposal approved.',
            'proposal' => $this->formatProposalForChancellor($proposal),
        ]);
    }

    public function returnProposal(Request $request, BudgetProposal $proposal): JsonResponse
    {
        abort_if($proposal->status !== 'endorsed', 403);

        $remarks = trim((string) $request->input('remarks', ''));
        if (!$remarks) {
            return response()->json(['success' => false, 'message' => 'Remarks are required to return a proposal.'], 422);
        }

        $proposal->update(['status' => 'returned', 'reviewed_at' => now(), 'remarks' => $remarks]);

        BudgetProposalReview::create([
            'budget_proposal_id'  => $proposal->id,
            'reviewed_by_user_id' => auth()->id(),
            'action'              => 'return',
            'status_from'         => 'endorsed',
            'status_to'           => 'returned',
            'remarks'             => $remarks,
            'reviewed_at'         => now(),
        ]);

        NotificationService::proposalReturnedByChancellor($proposal, $remarks);

        $proposal->load(self::CHANCELLOR_DETAIL_RELATIONS);

        return response()->json([
            'success'  => true,
            'message'  => 'Proposal returned to Budget Office.',
            'proposal' => $this->formatProposalForChancellor($proposal),
        ]);
    }

    /**
     * Same real-data fixes as dashboard(): "procured" is a genuine PPMP-item →
     * PR match reaching lifecycleBucket() 'completed' (not a raw PR count
     * compared against an item count — different units entirely), "utilized"
     * uses the lifecycleBucket() in_progress/completed convention shared with
     * Finance's Budget Utilization Report, and "delayed" means genuinely
     * still-open and past a reasonable turnaround, not the raw `status`
     * column (which never actually holds the literal value 'delayed').
     */
    public function procurementReports(Request $request): View
    {
        if ($archive = app(\App\Services\FiscalYearReports::class)->archived('chancellor', $request)) {
            return view('prism.chancellor.procurement-reports', $this->withCommon('procurement-reports', $archive + ['pageTitle' => 'Chancellor Procurement Reports']));
        }
        $selectedOffice = $request->query('office', '');
        $quarterOptions = ['Q1', 'Q2', 'Q3', 'Q4'];
        $selectedQuarter = strtoupper((string) $request->query('quarter', ''));
        if (! in_array($selectedQuarter, $quarterOptions, true)) {
            $selectedQuarter = '';
        }

        $offices = Office::has('budgetProposals')
            ->when($selectedOffice, fn ($q) => $q->where('code', $selectedOffice))
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])->with('items'),
                'purchaseRequests',
            ])
            ->get();

        $currentQ       = $this->currentQuarter();
        $currentQNumber = (int) ltrim($currentQ, 'Q');

        $allItems = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', function ($q) use ($selectedOffice) {
                $q->whereIn('status', ['endorsed', 'approved']);
                if ($selectedOffice) {
                    $q->whereHas('office', fn ($q2) => $q2->where('code', $selectedOffice));
                }
            })
            ->when($selectedQuarter, fn ($q) => $q->where('target_quarter', $selectedQuarter))
            ->get();
        $officeIds     = $allItems->pluck('budgetProposal.office_id')->filter()->unique()->values();
        $prItemMatches = $this->matchPrItemsByOfficeAndName($officeIds);
        $matchedPrFor = function ($item) use ($prItemMatches) {
            $officeId = $item->budgetProposal?->office_id;

            return $prItemMatches->get($officeId . '|' . strtolower(trim($item->name)))?->purchaseRequest;
        };
        $isItemProcured = fn ($item) => $matchedPrFor($item)?->lifecycleBucket() === 'completed';

        $accomplishmentRows = $offices->map(function ($office) use ($allItems, $isItemProcured) {
            $officeItems    = $allItems->filter(fn ($item) => $item->budgetProposal?->office_id === $office->id);
            $targeted       = $officeItems->count();
            $procured       = $officeItems->filter($isItemProcured)->count();
            $completionRate = $targeted > 0 ? round(($procured / $targeted) * 100) : 0;

            return ['office' => $office->code, 'targeted' => $targeted, 'procured' => $procured, 'completionRate' => $completionRate];
        })
            ->filter(fn ($r) => $r['targeted'] > 0)
            ->sortByDesc('completionRate')
            ->values()
            ->all();

        // Same items, broken down by target_quarter — gives a formal report
        // reader the per-quarter picture, not just an office-level rollup.
        $quarterlyRows = $allItems
            ->filter(fn ($item) => $item->target_quarter)
            ->groupBy(fn ($item) => ($item->budgetProposal?->office?->code ?? '—') . '|' . $item->target_quarter)
            ->map(function ($group) use ($isItemProcured) {
                $first    = $group->first();
                $targeted = $group->count();
                $procured = $group->filter($isItemProcured)->count();

                return [
                    'office'         => $first->budgetProposal?->office?->code ?? '—',
                    'quarter'        => $first->target_quarter,
                    'targeted'       => $targeted,
                    'procured'       => $procured,
                    'completionRate' => $targeted > 0 ? round(($procured / $targeted) * 100) : 0,
                ];
            })
            ->sortBy(fn ($r) => $r['office'] . $r['quarter'])
            ->values()->all();

        $utilizationSummary = $offices->map(function ($office) use ($allItems, $matchedPrFor, $currentQNumber) {
            $officeItems = $allItems->filter(fn ($item) => $item->budgetProposal?->office_id === $office->id);
            $budget      = (float) $officeItems->sum('estimated_total_cost');
            $utilized    = (float) $officeItems
                ->filter(fn ($item) => in_array($matchedPrFor($item)?->lifecycleBucket(), ['in_progress', 'completed'], true))
                ->sum('estimated_total_cost');
            $pct      = $budget > 0 ? round(($utilized / $budget) * 100) : 0;
            $forecast = $currentQNumber > 0 ? min(100, (int) round($pct / $currentQNumber * 4)) : $pct;
            $risk     = $pct >= 70 ? 'On Track' : ($pct >= 40 ? 'At Risk' : 'Critical');

            return ['office' => $office->code, 'budget' => $budget, 'utilized' => $utilized, 'forecast' => $forecast, 'risk' => $risk];
        })->filter(fn ($r) => $r['budget'] > 0)->values()->all();

        $delayedPrIdsForScope = $selectedQuarter
            ? $allItems->map(fn ($item) => $matchedPrFor($item)?->id)->filter()->unique()->values()
            : collect();
        $overdueThresholdDays = 30;
        $delayedByOffice = PurchaseRequest::with('office')
            ->when($selectedOffice, fn ($q) => $q->whereHas('office', fn ($q2) => $q2->where('code', $selectedOffice)))
            ->when($selectedQuarter, fn ($q) => $q->whereIn('id', $delayedPrIdsForScope))
            ->get()
            ->filter(fn ($pr) =>
                $pr->signingStatusBucket() !== 'completed'
                && $pr->submitted_at
                && $pr->submitted_at->diffInDays(now()) > $overdueThresholdDays
            )
            ->groupBy(fn ($pr) => $pr->office?->code ?? '—')
            ->map(fn ($prs) => $prs->sortByDesc(fn ($pr) => $pr->submitted_at->diffInDays(now()))
                ->map(fn ($pr) => [
                    'item'     => $pr->title,
                    'prNumber' => $pr->number ?? '—',
                    'remarks'  => $pr->remarks ?: ((int) $pr->submitted_at->diffInDays(now()) . " days pending — past the {$overdueThresholdDays}-day target."),
                ])->values()->all())
            ->all();

        return view('prism.chancellor.procurement-reports', $this->withCommon('procurement-reports', [
            'pageTitle'          => 'Chancellor Procurement Reports',
            'generatedAt'        => now()->format('M d, Y g:i A'),
            'offices'            => Office::has('budgetProposals')->select('id', 'code', 'name')->orderBy('code')->get(),
            'selectedOffice'     => $selectedOffice,
            'quarterOptions'     => $quarterOptions,
            'selectedQuarter'    => $selectedQuarter,
            'accomplishmentRows' => $accomplishmentRows,
            'deliveryRows'       => app(\App\Services\ItemReceivingService::class)->rows(null, $selectedOffice, $selectedQuarter),
            'quarterlyRows'      => $quarterlyRows,
            'utilizationSummary' => $utilizationSummary,
            'delayedByOffice'    => $delayedByOffice,
            // Campus-wide totals only — NOT broken down per office, since the
            // tables right below already show that breakdown in full detail
            // (office, targeted/procured, completion%, budget/utilized/
            // forecast/risk). A per-office chart here just repeated the same
            // numbers as bars instead of rows; these two give the Chancellor
            // an at-a-glance overall picture the tables don't state outright.
            'accomplishmentChart' => [
                'procured'  => collect($accomplishmentRows)->sum('procured'),
                'remaining' => max(0, collect($accomplishmentRows)->sum('targeted') - collect($accomplishmentRows)->sum('procured')),
            ],
            'utilizationChart' => [
                'utilized'   => round(collect($utilizationSummary)->sum('utilized')),
                'unutilized' => max(0, round(collect($utilizationSummary)->sum('budget') - collect($utilizationSummary)->sum('utilized'))),
            ],
        ]));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function dashboardKpiDetails($allItems, callable $matchedPrFor, $officeMetrics, string $currentQuarter): array
    {
        $itemRows = $allItems
            ->sortBy(fn ($item) => implode('|', [
                $item->budgetProposal?->office?->code ?? '',
                $item->target_quarter ?? '',
                strtolower($item->name ?? ''),
            ]))
            ->map(fn (BudgetProposalItem $item) => $this->dashboardAppItemDetailRow($item, $matchedPrFor, $currentQuarter))
            ->values();

        $utilizationRows = $officeMetrics
            ->sortByDesc('currentUtilization')
            ->map(function ($row) {
                $officeCode = $row['office'] ?? 'Office';
                $officeName = $row['officeName'] ?? '';

                return [
                    'title'       => $officeName ? $officeCode . ' - ' . $officeName : $officeCode,
                    'office'      => $officeCode,
                    'budget'      => (float) ($row['budget'] ?? 0),
                    'utilized'    => (float) ($row['utilized'] ?? 0),
                    'utilization' => (int) ($row['currentUtilization'] ?? 0),
                    'forecast'    => (int) ($row['forecast'] ?? 0),
                    'risk'        => $row['risk'] ?? 'At Risk',
                    'statusClass' => $this->dashboardRiskBadgeClass($row['risk'] ?? null),
                    'url'         => route('chancellor.procurement-reports', ['office' => $officeCode]),
                ];
            })
            ->values();

        return [
            'totalAppItems' => [
                'title'      => 'Total APP Items',
                'lead'       => 'All approved APP line items across campus included in monitoring.',
                'rows'       => $itemRows->all(),
                'empty'      => 'No APP items found yet.',
                'type'       => 'items',
                'countLabel' => 'APP item(s)',
            ],
            'itemsProcured' => [
                'title'      => 'Items Procured',
                'lead'       => 'APP items with matched PRs that reached the completed procurement lifecycle.',
                'rows'       => $itemRows->where('statusKey', 'procured')->values()->all(),
                'empty'      => 'No procured APP items yet.',
                'type'       => 'items',
                'countLabel' => 'procured item(s)',
            ],
            'itemsPending' => [
                'title'      => 'Items Pending',
                'lead'       => 'APP items not yet completed, including not-started, in-progress, delayed, and overdue items.',
                'rows'       => $itemRows->whereIn('statusKey', ['not_started', 'in_progress', 'delayed', 'overdue'])->values()->all(),
                'empty'      => 'No pending APP items.',
                'type'       => 'items',
                'countLabel' => 'pending item(s)',
            ],
            'itemsOverdue' => [
                'title'      => 'Items Overdue',
                'lead'       => 'APP items past their target quarter and still not completed.',
                'rows'       => $itemRows->where('statusKey', 'overdue')->values()->all(),
                'empty'      => 'No overdue APP items.',
                'type'       => 'items',
                'countLabel' => 'overdue item(s)',
            ],
            'campusUtilization' => [
                'title'      => 'Campus Utilization',
                'lead'       => 'Budget utilization by office using approved APP item amounts covered by matched active or completed PRs.',
                'rows'       => $utilizationRows->all(),
                'empty'      => 'No office utilization data yet.',
                'type'       => 'utilization',
                'countLabel' => 'office(s)',
            ],
        ];
    }

    private function dashboardAppItemDetailRow(BudgetProposalItem $item, callable $matchedPrFor, string $currentQuarter): array
    {
        $proposal = $item->budgetProposal;
        $pr       = $matchedPrFor($item);
        $bucket   = $pr?->lifecycleBucket();
        $isPastTarget = $item->target_quarter && $item->target_quarter < $currentQuarter;

        [$statusKey, $status, $statusClass] = match (true) {
            $bucket === 'completed'   => ['procured', 'Procured', 'badge-completed'],
            $isPastTarget             => ['overdue', 'Overdue', 'badge-overdue'],
            $bucket === 'in_progress' => ['in_progress', 'In Progress', 'badge-in-progress'],
            $bucket === 'delayed'     => ['delayed', 'Delayed', 'badge-overdue'],
            default                   => ['not_started', 'Not Started', 'badge-pending'],
        };

        $trackingStage = $pr ? $pr->currentTrackingStage() : null;
        $trackingLabel = $trackingStage['label'] ?? 'No linked purchase request yet.';

        return [
            'name'        => $item->name ?: 'Untitled item',
            'proposal'    => $proposal?->title ?: ($proposal?->code ?: 'Untitled PPMP'),
            'code'        => $proposal?->code ?: 'PPMP',
            'office'      => $proposal?->office?->code ?? 'Unassigned',
            'fiscalYear'  => $proposal?->fiscal_year,
            'quantity'    => $this->formatDashboardQuantity((float) $item->quantity),
            'unit'        => $item->unit ?: 'unit',
            'category'    => $item->category ?: ($item->ppmpCategoryLabel() ?: 'General'),
            'quarter'     => $item->target_quarter ?: 'Unscheduled',
            'amount'      => (float) $item->estimated_total_cost,
            'statusKey'   => $statusKey,
            'status'      => $status,
            'statusClass' => $statusClass,
            'prNumber'    => $pr?->number ?: 'No PR yet',
            'remarks'     => $trackingLabel,
            'url'         => $proposal
                ? route('chancellor.budget-approval.document', $proposal->id)
                : route('chancellor.budget-approval'),
        ];
    }

    private function dashboardRiskBadgeClass(?string $risk): string
    {
        return match (strtolower($risk ?? '')) {
            'on track', 'low'  => 'badge-low-risk',
            'critical', 'high' => 'badge-high-risk',
            default            => 'badge-medium-risk',
        };
    }

    private function formatDashboardQuantity(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 2), '0'), '.');
    }

    private function formatProposalForChancellor(BudgetProposal $p): array
    {
        $financeReview = $p->reviews->where('action', 'endorse')->sortByDesc('reviewed_at')->first();

        return [
            'id'             => $p->id,
            'code'           => $p->code ?? '—',
            'office'         => $p->office?->code ?? '—',
            'officeName'     => $p->office?->name ?? '—',
            'title'          => $p->title,
            'fiscalYear'     => (string) $p->fiscal_year,
            'officeHead'     => $p->submittedBy?->name ?? '—',
            'submittedDate'  => $p->submitted_at?->format('M d, Y') ?? '—',
            'totalAmount'    => (float) $p->total_estimated_cost,
            'proposedBudget' => (float) ($p->proposed_budget ?? $p->total_estimated_cost),
            'dateEndorsed'   => $p->reviewed_at?->format('M d, Y') ?? '—',
            'dateEndorsedRaw'=> $p->reviewed_at?->toIso8601String(),
            // The Chancellor's own decision date — approve() doesn't touch
            // reviewed_at (only returnProposal() does), so this is the
            // reliable "when did Chancellor act" field for the archive.
            'decidedDate'    => ($p->approved_at ?? $p->reviewed_at)?->format('M d, Y') ?? '—',
            'decidedAtRaw'   => ($p->approved_at ?? $p->reviewed_at)?->toIso8601String(),
            'financeRemarks' => $financeReview?->remarks ?? $p->remarks ?? '—',
            'status'         => ucfirst($p->status),
            'marketScoping'  => $p->items->flatMap->marketReferences->count() . ' market references attached',
            'approveUrl'     => route('chancellor.budget-approval.approve', $p->id),
            'returnUrl'      => route('chancellor.budget-approval.return', $p->id),
            'documentUrl'    => route('chancellor.budget-approval.document', $p->id),
            'items'          => $p->items->map(fn ($item) => [
                'name'           => $item->name,
                'quantity'       => (int) $item->quantity,
                'unit'           => $item->unit ?: '—',
                'unitCost'       => (float) $item->estimated_unit_cost,
                'sourceOfFund'   => $item->source_of_fund ?: '—',
                'classification' => $item->item_classification ?: '—',
                'targetQuarter'  => $item->target_quarter ?: '—',
                'justification'  => $item->remarks ?? '',
                'amount'         => (float) $item->estimated_total_cost,
            ])->all(),
            // {text, time} entries — rendered as a collapsible activity log,
            // same as the PR/AOC/PO signature history elsewhere in the app.
            'approvalTrail' => $p->reviews->sortBy('reviewed_at')
                ->map(fn ($r) => [
                    'text' => \App\Support\ActionVerb::label($r->action) . ' by ' . ($r->reviewedBy?->name ?? 'System') . ($r->remarks ? ' — ' . $r->remarks : ''),
                    'time' => $r->reviewed_at?->format('M d, Y g:i A') ?? '—',
                ])
                ->values()->all(),
        ];
    }

    /**
     * Best-effort match from a BudgetProposalItem (PPMP) to the downstream PR
     * item eventually raised for it. No stored link exists yet between the two,
     * so we match on office + item name and keep the most recently created PR
     * per pair. Returns PurchaseRequestItem (not the PR itself), keyed by
     * "office_id|lower(trim(item name))" — callers read ->purchaseRequest when
     * they need the parent PR.
     */
    private function matchPrItemsByOfficeAndName(\Illuminate\Support\Collection $officeIds): \Illuminate\Support\Collection
    {
        return PurchaseRequestItem::with('purchaseRequest.abstractOfCanvass.purchaseOrder')
            ->whereHas('purchaseRequest', fn ($q) => $q->whereIn('office_id', $officeIds))
            ->get()
            ->filter(fn ($pri) => $pri->purchaseRequest !== null)
            ->groupBy(fn ($pri) => $pri->purchaseRequest->office_id . '|' . strtolower(trim($pri->name)))
            ->map(fn ($group) => $group->sortByDesc(fn ($pri) => $pri->purchaseRequest->created_at)->first());
    }

    private function currentQuarter(): string
    {
        return match (true) {
            now()->month <= 3  => 'Q1',
            now()->month <= 6  => 'Q2',
            now()->month <= 9  => 'Q3',
            default            => 'Q4',
        };
    }

    public function officeAssets(\Illuminate\Http\Request $request, \App\Services\OfficeAssetService $assets)
    {
        return view('prism.shared.office-assets', $this->withCommon('office-assets', $assets->pageData($request) + [
            'pageTitle' => 'Allocation & Warranty', 'assetPageRole' => 'chancellor',
            'assetLayout' => 'prism.layouts.app',
        ]));
    }

    private function withCommon(string $activeChancellorPage, array $data): array
    {
        return array_merge([
            'activeRole'       => 'chancellor',
            'activeModulePage' => $activeChancellorPage,
            'brandHref'        => route('chancellor.dashboard'),
            'roleLabel'        => "Chancellor's Office",
            'roleInitials'     => 'CH',
            'roleNavigation'   => \App\Support\PrismNav::roleNavigation(),
            'moduleNavLabel'   => 'Chancellor pages',
            'moduleNavigation' => [
                ['slug' => 'office-assets', 'label' => 'Allocation & Warranty', 'href' => route('chancellor.office-assets'), 'icon' => 'devices'],
                ['slug' => 'dashboard',           'label' => 'Campus Monitoring',   'href' => route('chancellor.dashboard'),           'icon' => 'layout-dashboard'],
                ['slug' => 'budget-approval',     'label' => 'PPMP Approval',       'href' => route('chancellor.budget-approval'),     'icon' => 'shield-check'],
                ['slug' => 'for-my-signature',    'label' => 'For My Signature',    'href' => route('chancellor.for-my-signature'),    'icon' => 'signature'],
                ['slug' => 'procurement-reports', 'label' => 'Procurement Reports', 'href' => route('chancellor.procurement-reports'), 'icon' => 'trending-up'],
            ],
        ], $data);
    }
}
