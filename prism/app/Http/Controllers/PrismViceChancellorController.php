<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSignatureQueue;
use App\Models\BudgetProposalItem;
use App\Models\Office;
use App\Models\PurchaseRequest;
use Illuminate\View\View;

class PrismViceChancellorController extends Controller
{
    use HandlesSignatureQueue;

    protected function queueRoleCode(): string
    {
        return 'vice-chancellor';
    }

    protected function queueRoutePrefix(): string
    {
        return 'vice-chancellor';
    }

    protected function queueOfficeIds(): ?array
    {
        return $this->assignedOfficeIds();
    }

    public function forMySignature(): View
    {
        return view('prism.shared.for-my-signature', $this->withCommon('for-my-signature', [
            'pageTitle' => 'For My Signature',
            'queueRows' => $this->signatureQueueRows(),
        ]));
    }

    public function dashboard(): View
    {
        $currentQ = $this->currentQuarter();

        $offices = Office::has('budgetProposals')
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])->with('items'),
                'purchaseRequests',
            ])
            ->when($this->assignedOfficeIds(), fn ($q, $ids) => $q->whereIn('id', $ids))
            ->get();

        $totalAppItems = BudgetProposalItem::whereHas(
            'budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])
        )->count();
        $procuredCount = PurchaseRequest::where('status', 'completed')->count();

        $approvedBudget     = (float) $offices->flatMap->budgetProposals->sum('total_estimated_cost');
        $utilized           = (float) $offices->flatMap->purchaseRequests->whereIn('status', ['approved', 'completed'])->sum('total_amount');
        $divisionUtilization = $approvedBudget > 0 ? round(($utilized / $approvedBudget) * 100) : 0;

        $officeUtilization = $offices->map(function ($office) use ($currentQ) {
            $budget   = (float) $office->budgetProposals->sum('total_estimated_cost');
            $utilized = (float) $office->purchaseRequests->whereIn('status', ['approved', 'completed'])->sum('total_amount');
            $pct      = $budget > 0 ? round(($utilized / $budget) * 100) : 0;
            $delayed  = $office->purchaseRequests->where('status', 'delayed')->count();
            $overdue  = BudgetProposalItem::whereHas('budgetProposal', fn ($q) => $q->where('office_id', $office->id)->whereIn('status', ['endorsed', 'approved']))
                ->whereNotNull('target_quarter')
                ->where('target_quarter', '<', $currentQ)
                ->count();
            $risk = $pct >= 70 ? 'On Track' : ($pct >= 40 ? 'At Risk' : 'Critical');

            return ['office' => $office->name, 'utilization' => $pct, 'risk' => $risk, 'delayed' => $delayed, 'overdue' => $overdue, 'budget' => $budget];
        })->filter(fn ($r) => $r['budget'] > 0)->values()->all();

        $vcName = auth()->user()->name ?? 'Vice Chancellor';

        $pendingPrSummary = $offices->map(function ($office) {
            $pending    = $office->purchaseRequests->where('status', 'pending')->count();
            $inProgress = $office->purchaseRequests->where('status', 'in_progress')->count();
            $oldest     = $office->purchaseRequests->where('status', 'pending')->sortBy('created_at')->first();

            return [
                'office'         => $office->name,
                'pendingPrs'     => $pending,
                'inProgress'     => $inProgress,
                'oldestPending'  => $oldest?->created_at?->format('M d, Y') ?? '—',
            ];
        })->filter(fn ($r) => $r['pendingPrs'] + $r['inProgress'] > 0)->values()->all();

        return view('prism.vice-chancellor.dashboard', $this->withCommon('dashboard', [
            'pageTitle'         => 'Vice Chancellor Division Dashboard',
            'awaitingSignature' => app(\App\Services\SignatoryQueueService::class)->countForRole('vice-chancellor'),
            'divisionName'     => $vcName . "'s Division",
            'offices'          => $offices->pluck('name')->all(),
            'summary'          => [
                'totalAppItems'      => $totalAppItems,
                'procuredCount'      => $procuredCount,
                'divisionUtilization'=> $divisionUtilization,
            ],
            'officeUtilization'  => $officeUtilization,
            'pendingPrSummary'   => $pendingPrSummary,
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
                    'office'             => $pr->office?->name ?? '—',
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
        $offices = Office::has('budgetProposals')
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])->with('items'),
                'purchaseRequests',
            ])
            ->when($this->assignedOfficeIds(), fn ($q, $ids) => $q->whereIn('id', $ids))
            ->get();

        $rows = $offices->map(function ($office) {
            $totalAppItems = $office->budgetProposals->flatMap->items->count();
            $procured      = $office->purchaseRequests->where('status', 'completed')->count();
            $pending       = max(0, $totalAppItems - $procured);
            $budget        = (float) $office->budgetProposals->sum('total_estimated_cost');
            $utilized      = (float) $office->purchaseRequests->whereIn('status', ['approved', 'completed'])->sum('total_amount');
            $utilization   = $budget > 0 ? round(($utilized / $budget) * 100) : 0;

            return ['office' => $office->name, 'totalAppItems' => $totalAppItems, 'procured' => $procured, 'pending' => $pending, 'utilization' => $utilization, 'budget' => $budget];
        })->filter(fn ($r) => $r['budget'] > 0)->sortByDesc('utilization')->values();

        $performanceRows = $rows->map(fn ($r, $i) => array_merge($r, [
            'performance' => $i === 0 ? 'best' : ($i === $rows->count() - 1 ? 'lowest' : 'steady'),
        ]))->all();

        $bestOffice   = $rows->first()['office'] ?? '—';
        $lowestOffice = $rows->last()['office'] ?? '—';

        return view('prism.vice-chancellor.division-performance-report', $this->withCommon('division-performance-report', [
            'pageTitle'       => 'Vice Chancellor Division Performance Report',
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
        $ids = auth()->user()->officeAssignments()->pluck('offices.id')->all();
        return count($ids) > 0 ? $ids : null;
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
