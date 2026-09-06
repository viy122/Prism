<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSignatureQueue;
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

        $totalAppItems = $allItems->count();
        $itemsProcured = $allItems->filter($isItemProcured)->count();
        $itemsPending  = max(0, $totalAppItems - $itemsProcured);

        $approvedBudget    = (float) BudgetProposal::whereIn('status', ['endorsed', 'approved'])->sum('total_estimated_cost');
        $utilized          = (float) PurchaseRequest::get()
            ->filter(fn ($pr) => in_array($pr->lifecycleBucket(), ['in_progress', 'completed'], true))
            ->sum('total_amount');
        $campusUtilization = $approvedBudget > 0 ? round(($utilized / $approvedBudget) * 100) : 0;

        $currentQ       = $this->currentQuarter();
        $currentQNumber = (int) ltrim($currentQ, 'Q');

        $offices = Office::has('budgetProposals')
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])->with('items'),
                'purchaseRequests',
            ])
            ->get();

        $quarterlyStatuses = $offices->map(function ($office) use ($currentQ, $matchedPrFor) {
            $items      = $office->budgetProposals->flatMap->items;
            $quarterRow = [];

            foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $q) {
                $quarterItems = $items->where('target_quarter', $q);
                if ($quarterItems->isEmpty()) {
                    $quarterRow[$q] = 'Pending';
                    continue;
                }

                $procuredCount = $quarterItems->filter(fn ($item) => $matchedPrFor($item)?->lifecycleBucket() === 'completed')->count();

                if ($procuredCount === $quarterItems->count()) {
                    $quarterRow[$q] = 'Completed';
                } elseif ($q === $currentQ) {
                    $quarterRow[$q] = $procuredCount > 0 ? 'In Progress' : 'Pending';
                } elseif ($q < $currentQ) {
                    $quarterRow[$q] = $procuredCount > 0 ? 'In Progress' : 'Delayed';
                } else {
                    $quarterRow[$q] = 'Pending';
                }
            }

            $risk = in_array('Delayed', $quarterRow) ? 'Critical'
                : (in_array('In Progress', $quarterRow) ? 'At Risk' : 'On Track');

            return [
                'office' => $office->code,
                'q1'     => $quarterRow['Q1'],
                'q2'     => $quarterRow['Q2'],
                'q3'     => $quarterRow['Q3'],
                'q4'     => $quarterRow['Q4'],
                'risk'   => $risk,
            ];
        })->values()->all();

        $officeMetrics = $offices->map(function ($office) use ($currentQNumber) {
            $budget   = (float) $office->budgetProposals->sum('total_estimated_cost');
            $utilized = (float) $office->purchaseRequests
                ->filter(fn ($pr) => in_array($pr->lifecycleBucket(), ['in_progress', 'completed'], true))
                ->sum('total_amount');
            $pct      = $budget > 0 ? round(($utilized / $budget) * 100) : 0;
            $forecast = $currentQNumber > 0 ? min(100, (int) round($pct / $currentQNumber * 4)) : $pct;
            $risk     = $pct >= 70 ? 'On Track' : ($pct >= 40 ? 'At Risk' : 'Critical');

            return ['office' => $office->code, 'currentUtilization' => $pct, 'forecast' => $forecast, 'risk' => $risk, 'budget' => $budget, 'utilized' => $utilized];
        })->filter(fn ($r) => $r['budget'] > 0)->values();

        $forecasts            = $officeMetrics->all();
        $utilizationRankings  = $officeMetrics->sortByDesc('currentUtilization')->values()
            ->map(fn ($r, $i) => array_merge($r, ['rank' => $i + 1, 'utilization' => $r['currentUtilization']]))->all();

        $quarterEnd = fn ($q) => match ($q) {
            'Q1'    => now()->startOfYear()->addMonths(3),
            'Q2'    => now()->startOfYear()->addMonths(6),
            'Q3'    => now()->startOfYear()->addMonths(9),
            default => now()->startOfYear()->addMonths(12),
        };

        // Full overdue set (not yet procured, past its target quarter) — the
        // "10 alerts to display" list below is only a slice of this, so the
        // summary count must come from the full filtered set, not the slice.
        $overdueItemsAll = $allItems->filter(fn ($item) =>
            $item->target_quarter
            && $item->target_quarter < $currentQ
            && $matchedPrFor($item)?->lifecycleBucket() !== 'completed'
        )->sortByDesc('target_quarter')->values();

        $overdueAlerts = $overdueItemsAll->take(10)->map(function ($item) use ($matchedPrFor, $quarterEnd) {
            $pr = $matchedPrFor($item);

            return [
                'item'        => $item->name,
                'office'      => $item->budgetProposal?->office?->code ?? '—',
                'prNumber'    => $pr?->number ?? 'Not yet raised',
                'daysOverdue' => (int) now()->diffInDays($quarterEnd($item->target_quarter)),
                'status'      => $pr ? ucfirst(str_replace('_', ' ', $pr->lifecycleBucket())) : 'Not Started',
            ];
        })->values()->all();

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
            'quarterlyStatuses'   => $quarterlyStatuses,
            'forecasts'           => $forecasts,
            'utilizationRankings' => $utilizationRankings,
            'overdueAlerts'       => $overdueAlerts,
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
    public function procurementReports(): View
    {
        $offices = Office::has('budgetProposals')
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])->with('items'),
                'purchaseRequests',
            ])
            ->get();

        $currentQ       = $this->currentQuarter();
        $currentQNumber = (int) ltrim($currentQ, 'Q');

        $allItems = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->get();
        $officeIds     = $allItems->pluck('budgetProposal.office_id')->filter()->unique()->values();
        $prItemMatches = $this->matchPrItemsByOfficeAndName($officeIds);
        $isItemProcured = function ($item) use ($prItemMatches) {
            $officeId = $item->budgetProposal?->office_id;

            return $prItemMatches->get($officeId . '|' . strtolower(trim($item->name)))?->purchaseRequest?->lifecycleBucket() === 'completed';
        };

        $accomplishmentRows = $offices->map(function ($office) use ($allItems, $isItemProcured) {
            $officeItems    = $allItems->filter(fn ($item) => $item->budgetProposal?->office_id === $office->id);
            $targeted       = $officeItems->count();
            $procured       = $officeItems->filter($isItemProcured)->count();
            $completionRate = $targeted > 0 ? round(($procured / $targeted) * 100) : 0;

            return ['office' => $office->code, 'targeted' => $targeted, 'procured' => $procured, 'completionRate' => $completionRate];
        })->filter(fn ($r) => $r['targeted'] > 0)->values()->all();

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

        $utilizationSummary = $offices->map(function ($office) use ($currentQNumber) {
            $budget   = (float) $office->budgetProposals->sum('total_estimated_cost');
            $utilized = (float) $office->purchaseRequests
                ->filter(fn ($pr) => in_array($pr->lifecycleBucket(), ['in_progress', 'completed'], true))
                ->sum('total_amount');
            $pct      = $budget > 0 ? round(($utilized / $budget) * 100) : 0;
            $forecast = $currentQNumber > 0 ? min(100, (int) round($pct / $currentQNumber * 4)) : $pct;
            $risk     = $pct >= 70 ? 'On Track' : ($pct >= 40 ? 'At Risk' : 'Critical');

            return ['office' => $office->code, 'budget' => $budget, 'utilized' => $utilized, 'forecast' => $forecast, 'risk' => $risk];
        })->filter(fn ($r) => $r['budget'] > 0)->values()->all();

        $overdueThresholdDays = 30;
        $delayedByOffice = PurchaseRequest::with('office')
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
            'accomplishmentRows' => $accomplishmentRows,
            'quarterlyRows'      => $quarterlyRows,
            'utilizationSummary' => $utilizationSummary,
            'delayedByOffice'    => $delayedByOffice,
            'accomplishmentChart' => collect($accomplishmentRows)->map(fn ($r) => [
                'office' => $r['office'], 'targeted' => $r['targeted'], 'procured' => $r['procured'],
            ])->values()->all(),
            'utilizationChart' => collect($utilizationSummary)->map(fn ($r) => [
                'office' => $r['office'], 'budget' => round($r['budget']), 'utilized' => round($r['utilized']),
            ])->values()->all(),
        ]));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

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
                    'text' => ucfirst($r->action) . ' by ' . ($r->reviewedBy?->name ?? 'System') . ($r->remarks ? ' — ' . $r->remarks : ''),
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
                ['slug' => 'dashboard',           'label' => 'Campus Monitoring',   'href' => route('chancellor.dashboard'),           'icon' => 'layout-dashboard'],
                ['slug' => 'budget-approval',     'label' => 'PPMP Approval',       'href' => route('chancellor.budget-approval'),     'icon' => 'shield-check'],
                ['slug' => 'for-my-signature',    'label' => 'For My Signature',    'href' => route('chancellor.for-my-signature'),    'icon' => 'signature'],
                ['slug' => 'procurement-reports', 'label' => 'Procurement Reports', 'href' => route('chancellor.procurement-reports'), 'icon' => 'trending-up'],
            ],
        ], $data);
    }
}
