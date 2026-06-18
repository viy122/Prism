<?php

namespace App\Http\Controllers;

use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\BudgetProposalReview;
use App\Models\Office;
use App\Models\PurchaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrismChancellorController extends Controller
{
    public function dashboard(): View
    {
        $allItems = BudgetProposalItem::whereHas(
            'budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])
        )->get();

        $totalAppItems = $allItems->count();
        $itemsProcured = PurchaseRequest::where('status', 'completed')->count();
        $itemsPending  = max(0, $totalAppItems - $itemsProcured);

        $approvedBudget    = (float) BudgetProposal::whereIn('status', ['endorsed', 'approved'])->sum('total_estimated_cost');
        $utilized          = (float) PurchaseRequest::whereIn('status', ['approved', 'completed'])->sum('total_amount');
        $campusUtilization = $approvedBudget > 0 ? round(($utilized / $approvedBudget) * 100) : 0;

        $currentQ       = $this->currentQuarter();
        $currentQNumber = (int) ltrim($currentQ, 'Q');

        $offices = Office::has('budgetProposals')
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])->with('items'),
                'purchaseRequests',
            ])
            ->get();

        $quarterlyStatuses = $offices->map(function ($office) use ($currentQ) {
            $items        = $office->budgetProposals->flatMap->items;
            $completedPrs = $office->purchaseRequests->where('status', 'completed')->count();
            $quarterRow   = [];

            foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $q) {
                $hasItems = $items->where('target_quarter', $q)->isNotEmpty();
                if (!$hasItems) {
                    $quarterRow[$q] = 'Pending';
                } elseif ($q < $currentQ) {
                    $quarterRow[$q] = $completedPrs > 0 ? 'Completed' : 'Delayed';
                } elseif ($q === $currentQ) {
                    $quarterRow[$q] = 'In Progress';
                } else {
                    $quarterRow[$q] = 'Pending';
                }
            }

            $risk = in_array('Delayed', $quarterRow) ? 'Critical'
                : (in_array('In Progress', $quarterRow) ? 'At Risk' : 'On Track');

            return [
                'office' => $office->name,
                'q1'     => $quarterRow['Q1'],
                'q2'     => $quarterRow['Q2'],
                'q3'     => $quarterRow['Q3'],
                'q4'     => $quarterRow['Q4'],
                'risk'   => $risk,
            ];
        })->values()->all();

        $officeMetrics = $offices->map(function ($office) use ($currentQNumber) {
            $budget   = (float) $office->budgetProposals->sum('total_estimated_cost');
            $utilized = (float) $office->purchaseRequests->whereIn('status', ['approved', 'completed'])->sum('total_amount');
            $pct      = $budget > 0 ? round(($utilized / $budget) * 100) : 0;
            $forecast = $currentQNumber > 0 ? min(100, (int) round($pct / $currentQNumber * 4)) : $pct;
            $risk     = $pct >= 70 ? 'On Track' : ($pct >= 40 ? 'At Risk' : 'Critical');

            return ['office' => $office->name, 'currentUtilization' => $pct, 'forecast' => $forecast, 'risk' => $risk, 'budget' => $budget];
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

        $overdueItems = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->whereNotNull('target_quarter')
            ->where('target_quarter', '<', $currentQ)
            ->latest()
            ->take(10)
            ->get();

        $overdueAlerts = $overdueItems->map(fn ($item) => [
            'item'        => $item->name,
            'office'      => $item->budgetProposal?->office?->name ?? '—',
            'prNumber'    => '—',
            'daysOverdue' => (int) now()->diffInDays($quarterEnd($item->target_quarter)),
            'status'      => 'Overdue',
        ])->all();

        return view('prism.chancellor.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Chancellor Campus Monitoring Dashboard',
            'summary'   => [
                'totalAppItems'     => $totalAppItems,
                'itemsProcured'     => $itemsProcured,
                'itemsPending'      => $itemsPending,
                'itemsOverdue'      => count($overdueAlerts),
                'campusUtilization' => $campusUtilization,
            ],
            'quarterlyStatuses'   => $quarterlyStatuses,
            'forecasts'           => $forecasts,
            'utilizationRankings' => $utilizationRankings,
            'overdueAlerts'       => $overdueAlerts,
        ]));
    }

    public function budgetApproval(): View
    {
        $proposals = BudgetProposal::with(['office', 'submittedBy', 'items.marketReferences', 'reviews.reviewedBy'])
            ->where('status', 'endorsed')
            ->latest('reviewed_at')
            ->get()
            ->map(fn ($p) => $this->formatProposalForChancellor($p))
            ->all();

        return view('prism.chancellor.budget-approval', $this->withCommon('budget-approval', [
            'pageTitle' => 'Chancellor Budget Approval',
            'proposals' => $proposals,
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

        return response()->json(['success' => true, 'message' => 'Proposal approved.']);
    }

    public function returnProposal(Request $request, BudgetProposal $proposal): JsonResponse
    {
        abort_if($proposal->status !== 'endorsed', 403);

        $remarks = trim($request->input('remarks', ''));
        if (!$remarks) {
            return response()->json(['success' => false, 'message' => 'Remarks are required to return a proposal.'], 422);
        }

        $proposal->update(['status' => 'returned', 'reviewed_at' => now()]);

        BudgetProposalReview::create([
            'budget_proposal_id'  => $proposal->id,
            'reviewed_by_user_id' => auth()->id(),
            'action'              => 'return',
            'status_from'         => 'endorsed',
            'status_to'           => 'returned',
            'remarks'             => $remarks,
            'reviewed_at'         => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Proposal returned to Finance Office.']);
    }

    public function procurementReports(): View
    {
        $offices = Office::has('budgetProposals')
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved'])->with('items'),
                'purchaseRequests',
            ])
            ->get();

        $currentQNumber = (int) ltrim($this->currentQuarter(), 'Q');

        $accomplishmentRows = $offices->map(function ($office) {
            $targeted       = $office->budgetProposals->flatMap->items->count();
            $procured       = $office->purchaseRequests->where('status', 'completed')->count();
            $completionRate = $targeted > 0 ? round(($procured / $targeted) * 100) : 0;

            return ['office' => $office->name, 'targeted' => $targeted, 'procured' => $procured, 'completionRate' => $completionRate];
        })->filter(fn ($r) => $r['targeted'] > 0)->values()->all();

        $utilizationSummary = $offices->map(function ($office) use ($currentQNumber) {
            $budget   = (float) $office->budgetProposals->sum('total_estimated_cost');
            $utilized = (float) $office->purchaseRequests->whereIn('status', ['approved', 'completed'])->sum('total_amount');
            $pct      = $budget > 0 ? round(($utilized / $budget) * 100) : 0;
            $forecast = $currentQNumber > 0 ? min(100, (int) round($pct / $currentQNumber * 4)) : $pct;
            $risk     = $pct >= 70 ? 'On Track' : ($pct >= 40 ? 'At Risk' : 'Critical');

            return ['office' => $office->name, 'budget' => $budget, 'utilized' => $utilized, 'forecast' => $forecast, 'risk' => $risk];
        })->filter(fn ($r) => $r['budget'] > 0)->values()->all();

        $delayedByOffice = PurchaseRequest::with('office')
            ->where('status', 'delayed')
            ->latest()
            ->get()
            ->groupBy(fn ($pr) => $pr->office?->name ?? '—')
            ->map(fn ($prs) => $prs->map(fn ($pr) => [
                'item'     => $pr->title,
                'prNumber' => $pr->number ?? '—',
                'remarks'  => $pr->remarks ?? '—',
            ])->all())
            ->all();

        return view('prism.chancellor.procurement-reports', $this->withCommon('procurement-reports', [
            'pageTitle'          => 'Chancellor Procurement Reports',
            'accomplishmentRows' => $accomplishmentRows,
            'utilizationSummary' => $utilizationSummary,
            'delayedByOffice'    => $delayedByOffice,
        ]));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function formatProposalForChancellor(BudgetProposal $p): array
    {
        $financeReview = $p->reviews->where('action', 'endorse')->sortByDesc('reviewed_at')->first();

        return [
            'id'            => $p->id,
            'office'        => $p->office?->name ?? '—',
            'title'         => $p->title,
            'totalAmount'   => (float) $p->total_estimated_cost,
            'dateEndorsed'  => $p->reviewed_at?->format('M d, Y') ?? '—',
            'financeRemarks'=> $financeReview?->remarks ?? $p->remarks ?? '—',
            'status'        => ucfirst($p->status),
            'marketScoping' => $p->items->flatMap->marketReferences->count() . ' market references attached',
            'approveUrl'    => route('chancellor.budget-approval.approve', $p->id),
            'returnUrl'     => route('chancellor.budget-approval.return', $p->id),
            'items'         => $p->items->map(fn ($item) => [
                'name'          => $item->name,
                'quantity'      => (int) $item->quantity,
                'justification' => $item->remarks ?? '',
                'amount'        => (float) $item->estimated_total_cost,
            ])->all(),
            'approvalTrail' => $p->reviews->sortBy('reviewed_at')
                ->map(fn ($r) => ($r->reviewed_at?->format('M d, Y') ?? '?') . ' — ' . ucfirst($r->action) . ' by ' . ($r->reviewedBy?->name ?? 'System'))
                ->join('<br>'),
        ];
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
            'roleNavigation'   => [
                ['slug' => 'office-head',        'label' => 'Office Head / Dean', 'href' => route('office-head.dashboard')],
                ['slug' => 'finance-office',     'label' => 'Finance Office',      'href' => route('finance-office.dashboard')],
                ['slug' => 'procurement-office', 'label' => 'Procurement Office',  'href' => route('procurement-office.dashboard')],
                ['slug' => 'chancellor',         'label' => 'Chancellor',           'href' => route('chancellor.dashboard')],
                ['slug' => 'vice-chancellor',    'label' => 'Vice Chancellor',      'href' => route('vice-chancellor.dashboard')],
            ],
            'moduleNavLabel'   => 'Chancellor pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',           'label' => 'Campus Monitoring',   'href' => route('chancellor.dashboard'),           'icon' => 'layout-dashboard'],
                ['slug' => 'budget-approval',     'label' => 'Budget Approval',     'href' => route('chancellor.budget-approval'),     'icon' => 'shield-check'],
                ['slug' => 'procurement-reports', 'label' => 'Procurement Reports', 'href' => route('chancellor.procurement-reports'), 'icon' => 'trending-up'],
            ],
        ], $data);
    }
}
