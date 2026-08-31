<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSignatureQueue;
use App\Models\BudgetProposalItem;
use App\Models\Office;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PrismViceChancellorController extends Controller
{
    use HandlesSignatureQueue;

    /**
     * Real BatStateU ARASOF-Nasugbu org structure (per the campus FOI office
     * listing): which offices report up through Academic Affairs vs
     * Administration & Finance. office_user_assignments records where a user
     * physically sits (e.g. every VC is "assigned" to the OVC office itself),
     * not which offices their division covers — so it can't answer "what does
     * this VC oversee" and assignedOfficeIds() below doesn't use it for that.
     */
    private const VCAA_OFFICE_CODES = [
        'CICS', 'COE', 'CBA', 'CAS', 'CCJE', 'CHS', 'CTE', 'LS',
        'RS', 'SDS', 'LIB', 'NSTP', 'CULT', 'SPORTS', 'SCHOL', 'HLTHO', 'SCILAB',
    ];
    private const VCAF_OFFICE_CODES = [
        'HRMO', 'ACCT', 'BUD', 'CASH', 'PFM', 'PROC', 'PS', 'RMO', 'GS', 'EMU',
    ];

    /**
     * Only two real Vice Chancellors sign procurement documents — VCAA and
     * VCAF — recorded per-user via `users.vc_type`. Resolving this dynamically
     * (rather than the generic 'vice-chancellor') is what makes
     * HandlesSignatureQueue::authorizeStageOwnership() correctly reject a VCAA
     * user trying to sign a VCAF-designated document, and vice versa.
     */
    protected function queueRoleCode(): string
    {
        return auth()->user()?->vc_type ?? 'vice-chancellor';
    }

    protected function queueRoutePrefix(): string
    {
        return 'vice-chancellor';
    }

    /**
     * The signature queue is NOT scoped to division jurisdiction — VCAA and
     * VCAF are each the fixed, university-wide signatory for their owned
     * stages (PR's 2nd signatory, AOC countersign, PO review) regardless of
     * which office originated the document. `assignedOfficeIds()` below is
     * only for the division-level dashboard/report views.
     */
    protected function queueOfficeIds(): ?array
    {
        return null;
    }

    /**
     * Shows EVERY document the current VC type could ever be a signatory
     * for — created, in-progress, or already fully signed — not just the
     * ones currently awaiting action. `canAct` tells the UI which one row
     * is actually actionable right now.
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

    // VCAA is only ever a PR signatory. VCAF is a PR signatory too (the
    // flexible 3rd/4th slot, alongside Accounting), plus AOC's countersign
    // and PO's review — so her list needs all three document types.
    private function signatureDocTypes(): array
    {
        return $this->queueRoleCode() === 'vcaf' ? ['pr', 'aoc', 'po'] : ['pr'];
    }

    /**
     * Every "is this item procured" / "is this budget utilized" check below
     * goes through a real PPMP-item → PR match (matchPrItemsByOfficeAndName())
     * and the matched PR's own lifecycleBucket() — never the raw
     * PurchaseRequest::status column, which Procurement almost never writes
     * the literal values 'completed'/'approved'/'delayed'/'pending' to (the
     * real vocabulary is a much larger granular set). Same convention as the
     * Chancellor and Finance dashboards, so the numbers agree across roles.
     */
    public function dashboard(): View
    {
        $currentQ  = $this->currentQuarter();
        $officeIds = $this->assignedOfficeIds();

        $offices = Office::has('budgetProposals')
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])->with('items'),
                'purchaseRequests',
            ])
            ->when($officeIds, fn ($q, $ids) => $q->whereIn('id', $ids))
            ->get();

        $allItems = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->when($officeIds, fn ($q, $ids) => $q->whereHas('budgetProposal', fn ($q2) => $q2->whereIn('office_id', $ids)))
            ->get();

        $matchOfficeIds = $allItems->pluck('budgetProposal.office_id')->filter()->unique()->values();
        $prItemMatches  = $this->matchPrItemsByOfficeAndName($matchOfficeIds);
        $matchedPrFor   = function ($item) use ($prItemMatches) {
            $officeId = $item->budgetProposal?->office_id;

            return $prItemMatches->get($officeId . '|' . strtolower(trim($item->name)))?->purchaseRequest;
        };
        $isItemProcured = fn ($item) => $matchedPrFor($item)?->lifecycleBucket() === 'completed';
        $isUtilized     = fn ($pr) => in_array($pr->lifecycleBucket(), ['in_progress', 'completed'], true);

        $totalAppItems = $allItems->count();
        $procuredCount = $allItems->filter($isItemProcured)->count();

        $approvedBudget      = (float) $offices->flatMap->budgetProposals->sum('total_estimated_cost');
        $utilized            = (float) $offices->flatMap->purchaseRequests->filter($isUtilized)->sum('total_amount');
        $divisionUtilization = $approvedBudget > 0 ? round(($utilized / $approvedBudget) * 100) : 0;

        $officeUtilization = $offices->map(function ($office) use ($currentQ, $allItems, $isItemProcured, $isUtilized) {
            $budget   = (float) $office->budgetProposals->sum('total_estimated_cost');
            $utilized = (float) $office->purchaseRequests->filter($isUtilized)->sum('total_amount');
            $pct      = $budget > 0 ? round(($utilized / $budget) * 100) : 0;

            $delayed = $office->purchaseRequests->filter(fn ($pr) =>
                $pr->signingStatusBucket() !== 'completed'
                && $pr->submitted_at
                && $pr->submitted_at->diffInDays(now()) > 30
            )->count();

            $overdue = $allItems
                ->filter(fn ($item) => $item->budgetProposal?->office_id === $office->id)
                ->filter(fn ($item) => $item->target_quarter && $item->target_quarter < $currentQ && !$isItemProcured($item))
                ->count();

            $risk = $pct >= 70 ? 'On Track' : ($pct >= 40 ? 'At Risk' : 'Critical');

            return ['office' => $office->code, 'utilization' => $pct, 'risk' => $risk, 'delayed' => $delayed, 'overdue' => $overdue, 'budget' => $budget];
        })->filter(fn ($r) => $r['budget'] > 0)->values()->all();

        $pendingPrSummary = $offices->map(function ($office) {
            $pending    = $office->purchaseRequests->filter(fn ($pr) => $pr->signingStatusBucket() === 'pending')->count();
            $inProgress = $office->purchaseRequests->filter(fn ($pr) => $pr->signingStatusBucket() === 'in_progress')->count();
            $oldest     = $office->purchaseRequests->filter(fn ($pr) => $pr->signingStatusBucket() === 'pending')->sortBy('created_at')->first();

            return [
                'office'         => $office->code,
                'pendingPrs'     => $pending,
                'inProgress'     => $inProgress,
                'oldestPending'  => $oldest?->created_at?->format('M d, Y') ?? '—',
            ];
        })->filter(fn ($r) => $r['pendingPrs'] + $r['inProgress'] > 0)->values()->all();

        return view('prism.vice-chancellor.dashboard', $this->withCommon('dashboard', [
            'pageTitle'         => 'Vice Chancellor Division Dashboard',
            'generatedAt'       => now()->format('M d, Y g:i A'),
            'awaitingSignature' => app(\App\Services\SignatoryQueueService::class)->countForRole($this->queueRoleCode(), $this->queueOfficeIds()),
            'summary'          => [
                'totalAppItems'      => $totalAppItems,
                'procuredCount'      => $procuredCount,
                'divisionUtilization'=> $divisionUtilization,
            ],
            'officeUtilization'  => $officeUtilization,
            'pendingPrSummary'   => $pendingPrSummary,
            'itemStatusChart'    => ['procured' => $procuredCount, 'pending' => max(0, $totalAppItems - $procuredCount)],
        ]));
    }

    public function divisionProcurementStatus(): View
    {
        $items = PurchaseRequest::with([
                'office',
                'statusUpdates' => fn ($q) => $q->latest(),
                'signatureLogs.signedBy',
                'abstractOfCanvass.purchaseOrder',
            ])
            ->when($this->assignedOfficeIds(), fn ($q, $ids) => $q->whereIn('office_id', $ids))
            ->latest()
            ->get()
            ->map(function ($pr) {
                $aoc = $pr->abstractOfCanvass;
                $po  = $aoc?->purchaseOrder;

                // Furthest point in the pipeline wins: PO delivery > PO/AOC signing > PR
                if ($po) {
                    $currentStatus = $po->signatory_stage === 'fully_signed'
                        ? $po->status_label
                        : $po->signatory_label;
                } elseif ($aoc) {
                    $currentStatus = $aoc->signatory_label;
                } elseif ($pr->signatory_stage === 'fully_signed') {
                    $currentStatus = $pr->canvassing_label;
                } else {
                    $currentStatus = $pr->signatory_label;
                }

                $timeline = collect();

                foreach ($pr->signatureLogs as $log) {
                    $timeline->push([
                        'step'      => $pr->describeSignatureLog($log) . ($log->signedBy ? ' — ' . $log->signedBy->name : ''),
                        'timestamp' => $log->signed_at?->format('M d, Y g:i A') ?? '—',
                        'sortKey'   => $log->signed_at?->timestamp ?? 0,
                        'photoUrl'  => $log->blurred_photo_path
                            ? \Illuminate\Support\Facades\Storage::url($log->blurred_photo_path)
                            : null,
                    ]);
                }

                foreach ($pr->statusUpdates as $update) {
                    $timeline->push([
                        'step'      => ucwords(str_replace('_', ' ', $update->status)),
                        'timestamp' => $update->created_at->format('M d, Y g:i A'),
                        'sortKey'   => $update->created_at->timestamp,
                        'photoUrl'  => null,
                    ]);
                }

                return [
                    'id'                 => $pr->id,
                    'office'             => $pr->office?->code ?? '—',
                    'item'               => $pr->title,
                    'targetQuarter'      => $pr->fiscal_year ? 'FY ' . $pr->fiscal_year : '—',
                    'currentStatus'      => $currentStatus,
                    'remarks'            => $pr->remarks ?? '—',
                    'procurementRemarks' => $pr->statusUpdates->first()?->remarks ?: '—',
                    'timeline'           => $timeline->sortBy('sortKey')->map(fn ($t) => collect($t)->except('sortKey')->all())->values()->all(),
                ];
            })
            ->all();

        return view('prism.vice-chancellor.division-procurement-status', $this->withCommon('division-procurement-status', [
            'pageTitle'    => 'Vice Chancellor Division Procurement Status',
            'divisionItems'=> $items,
            'offices'      => collect($items)->pluck('office')->unique()->values()->all(),
            'quarters'     => collect($items)->pluck('targetQuarter')->filter(fn ($q) => $q !== '—')->unique()->sort()->values()->all(),
            'statuses'     => collect($items)->pluck('currentStatus')->unique()->values()->all(),
        ]));
    }

    public function divisionPerformanceReport(): View
    {
        $officeIds = $this->assignedOfficeIds();

        $offices = Office::has('budgetProposals')
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])->with('items'),
                'purchaseRequests',
            ])
            ->when($officeIds, fn ($q, $ids) => $q->whereIn('id', $ids))
            ->get();

        $allItems = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->when($officeIds, fn ($q, $ids) => $q->whereHas('budgetProposal', fn ($q2) => $q2->whereIn('office_id', $ids)))
            ->get();

        $matchOfficeIds = $allItems->pluck('budgetProposal.office_id')->filter()->unique()->values();
        $prItemMatches  = $this->matchPrItemsByOfficeAndName($matchOfficeIds);
        $isItemProcured = function ($item) use ($prItemMatches) {
            $officeId = $item->budgetProposal?->office_id;

            return $prItemMatches->get($officeId . '|' . strtolower(trim($item->name)))?->purchaseRequest?->lifecycleBucket() === 'completed';
        };

        $rows = $offices->map(function ($office) use ($allItems, $isItemProcured) {
            $officeItems   = $allItems->filter(fn ($item) => $item->budgetProposal?->office_id === $office->id);
            $totalAppItems = $officeItems->count();
            $procured      = $officeItems->filter($isItemProcured)->count();
            $pending       = max(0, $totalAppItems - $procured);
            $budget        = (float) $office->budgetProposals->sum('total_estimated_cost');
            $utilized      = (float) $office->purchaseRequests
                ->filter(fn ($pr) => in_array($pr->lifecycleBucket(), ['in_progress', 'completed'], true))
                ->sum('total_amount');
            $utilization   = $budget > 0 ? round(($utilized / $budget) * 100) : 0;

            return ['office' => $office->code, 'totalAppItems' => $totalAppItems, 'procured' => $procured, 'pending' => $pending, 'utilization' => $utilization, 'budget' => $budget];
        })->filter(fn ($r) => $r['budget'] > 0)->sortByDesc('utilization')->values();

        $performanceRows = $rows->map(fn ($r, $i) => array_merge($r, [
            'performance' => $i === 0 ? 'best' : ($i === $rows->count() - 1 ? 'lowest' : 'steady'),
        ]))->all();

        $bestOffice   = $rows->first()['office'] ?? '—';
        $lowestOffice = $rows->last()['office'] ?? '—';

        return view('prism.vice-chancellor.division-performance-report', $this->withCommon('division-performance-report', [
            'pageTitle'       => 'Vice Chancellor Division Performance Report',
            'generatedAt'     => now()->format('M d, Y g:i A'),
            'performanceRows' => $performanceRows,
            'bestOffice'      => $bestOffice,
            'lowestOffice'    => $lowestOffice,
        ]));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Returns the office IDs assigned to this VC, or null if none are set
     * (null causes ->when() to skip the filter, showing all offices as fallback).
     */
    private function assignedOfficeIds(): ?array
    {
        $codes = match (auth()->user()?->vc_type) {
            'vcaa'  => self::VCAA_OFFICE_CODES,
            'vcaf'  => self::VCAF_OFFICE_CODES,
            default => null,
        };

        if ($codes === null) {
            return null;
        }

        $ids = Office::whereIn('code', $codes)->pluck('id')->all();

        return count($ids) > 0 ? $ids : null;
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

    private function withCommon(string $activeViceChancellorPage, array $data): array
    {
        return array_merge([
            'activeRole'       => 'vice-chancellor',
            'activeModulePage' => $activeViceChancellorPage,
            'brandHref'        => route('vice-chancellor.dashboard'),
            'roleLabel'        => 'Vice Chancellor',
            'roleInitials'     => 'VC',
            'roleNavigation'   => \App\Support\PrismNav::roleNavigation(),
            'moduleNavLabel'   => 'Vice Chancellor pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',                    'label' => 'Division Dashboard',          'href' => route('vice-chancellor.dashboard'),                    'icon' => 'layout-dashboard'],
                ['slug' => 'for-my-signature',             'label' => 'For My Signature',            'href' => route('vice-chancellor.for-my-signature'),             'icon' => 'signature'],
                ['slug' => 'division-procurement-status',  'label' => 'Division Procurement Status', 'href' => route('vice-chancellor.division-procurement-status'),  'icon' => 'list-check'],
                ['slug' => 'division-performance-report',  'label' => 'Division Performance Report', 'href' => route('vice-chancellor.division-performance-report'),  'icon' => 'trending-up'],
            ],
        ], $data);
    }
}
