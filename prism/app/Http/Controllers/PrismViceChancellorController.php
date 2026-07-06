<?php

namespace App\Http\Controllers;

use App\Models\BudgetProposalItem;
use App\Models\Office;
use App\Models\PurchaseRequest;
use Illuminate\View\View;

class PrismViceChancellorController extends Controller
{
    public function dashboard(): View
    {
        $currentQ = $this->currentQuarter();

        $offices = Office::has('budgetProposals')
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])->with('items'),
                'purchaseRequests',
            ])
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
            'pageTitle'        => 'Vice Chancellor Division Dashboard',
            'divisionName'     => 'Campus Division',
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
        $items = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->latest()
            ->get()
            ->map(fn ($item) => [
                'id'                  => $item->id,
                'office'              => $item->budgetProposal?->office?->name ?? '—',
                'item'                => $item->name,
                'targetQuarter'       => $item->target_quarter ?? '—',
                'currentStatus'       => 'Pending',
                'remarks'             => $item->remarks ?? '—',
                'procurementRemarks'  => '—',
                'timeline'            => [],
            ])
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
            'roleNavigation'   => [
                ['slug' => 'office-head',        'label' => 'Office Head / Dean', 'href' => route('office-head.dashboard')],
                ['slug' => 'finance-office',     'label' => 'Finance Office',      'href' => route('finance-office.dashboard')],
                ['slug' => 'procurement-office', 'label' => 'Procurement Office',  'href' => route('procurement-office.dashboard')],
                ['slug' => 'chancellor',         'label' => 'Chancellor',           'href' => route('chancellor.dashboard')],
                ['slug' => 'vice-chancellor',    'label' => 'Vice Chancellor',      'href' => route('vice-chancellor.dashboard')],
                ['slug' => 'accounting-office',  'label' => 'Accounting Office',    'href' => route('accounting-office.dashboard')],
            ],
            'moduleNavLabel'   => 'Vice Chancellor pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',                    'label' => 'Division Dashboard',          'href' => route('vice-chancellor.dashboard'),                    'icon' => 'layout-dashboard'],
                ['slug' => 'division-procurement-status',  'label' => 'Division Procurement Status', 'href' => route('vice-chancellor.division-procurement-status'),  'icon' => 'list-check'],
                ['slug' => 'division-performance-report',  'label' => 'Division Performance Report', 'href' => route('vice-chancellor.division-performance-report'),  'icon' => 'trending-up'],
            ],
        ], $data);
    }
}
