<?php

namespace App\Http\Controllers;

use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\BudgetProposalReview;
use App\Models\Office;
use App\Models\PurchaseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrismFinanceOfficeController extends Controller
{
    public function dashboard(): View
    {
        $awaitingReview    = BudgetProposal::where('status', 'submitted')->count();
        $endorsedThisMonth = BudgetProposal::where('status', 'endorsed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        $returnedThisMonth = BudgetProposal::where('status', 'returned')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        $totalCampusBudget = BudgetProposal::whereIn('status', ['submitted', 'endorsed', 'approved'])
            ->sum('total_estimated_cost');

        $officeStatusGroups = Office::has('budgetProposals')
            ->with('budgetProposals')
            ->get()
            ->map(fn ($office) => [
                'office'   => $office->name,
                'pending'  => $office->budgetProposals->where('status', 'submitted')->count(),
                'endorsed' => $office->budgetProposals->where('status', 'endorsed')->count(),
                'returned' => $office->budgetProposals->where('status', 'returned')->count(),
            ])
            ->filter(fn ($g) => $g['pending'] + $g['endorsed'] + $g['returned'] > 0)
            ->values()
            ->all();

        $recentSubmissions = BudgetProposal::with('office')
            ->where('status', 'submitted')
            ->latest('submitted_at')
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'proposalId'    => $p->id,
                'office'        => $p->office?->name ?? '—',
                'submittedDate' => $p->submitted_at?->format('M d, Y') ?? $p->created_at->format('M d, Y'),
                'totalAmount'   => (float) $p->total_estimated_cost,
            ])
            ->all();

        return view('prism.finance-office.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Finance Office Dashboard',
            'summary' => [
                'awaitingReview'    => $awaitingReview,
                'endorsedThisMonth' => $endorsedThisMonth,
                'returned'          => $returnedThisMonth,
                'totalCampusBudget' => (float) $totalCampusBudget,
            ],
            'officeStatusGroups' => $officeStatusGroups,
            'recentSubmissions'  => $recentSubmissions,
        ]));
    }

    public function proposalReview(?string $proposal = null): View
    {
        $proposals = BudgetProposal::with(['office', 'submittedBy', 'items.marketReferences'])
            ->whereIn('status', ['submitted', 'endorsed', 'returned'])
            ->latest('submitted_at')
            ->get()
            ->map(fn ($p) => $this->formatProposal($p))
            ->all();

        if (empty($proposals)) {
            $selectedProposal = null;
        } else {
            $selectedProposal = $proposal
                ? (collect($proposals)->firstWhere('id', (int) $proposal) ?? $proposals[0])
                : $proposals[0];
        }

        return view('prism.finance-office.proposal-review', $this->withCommon('proposal-review', [
            'pageTitle'        => 'Proposal Review',
            'proposals'        => $proposals,
            'selectedProposal' => $selectedProposal,
        ]));
    }

    public function annualProcurementPlan(): View
    {
        $items = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->get()
            ->map(fn ($item) => [
                'office'          => $item->budgetProposal?->office?->name ?? '—',
                'item'            => $item->name,
                'quantity'        => (int) $item->quantity,
                'abcAmount'       => (float) $item->estimated_total_cost,
                'targetQuarter'   => $item->target_quarter ?? 'Q1',
                'procurementMode' => $this->procurementMode((float) $item->estimated_total_cost),
                'recommendation'  => $this->procurementRecommendation((float) $item->estimated_total_cost),
            ])
            ->all();

        return view('prism.finance-office.annual-procurement-plan', $this->withCommon('annual-procurement-plan', [
            'pageTitle'        => 'Annual Procurement Plan',
            'appItems'         => $items,
            'offices'          => collect($items)->pluck('office')->unique()->values()->all(),
            'quarters'         => ['Q1', 'Q2', 'Q3', 'Q4'],
            'procurementModes' => ['Public Bidding', 'Competitive Bidding', 'Small Value Procurement', 'Shopping', 'Direct Contracting'],
        ]));
    }

    public function budgetUtilizationReport(): View
    {
        $approvedProposals = BudgetProposal::with('office')
            ->whereIn('status', ['endorsed', 'approved'])
            ->get();

        $campusBudget  = $approvedProposals->sum('total_estimated_cost');
        $totalUtilized = PurchaseRequest::whereIn('status', ['approved', 'completed'])->sum('total_amount');
        $utilizationPct = $campusBudget > 0 ? round(($totalUtilized / $campusBudget) * 100) : 0;

        $utilizationRows = Office::has('budgetProposals')
            ->with([
                'budgetProposals' => fn ($q) => $q->whereIn('status', ['endorsed', 'approved']),
                'purchaseRequests',
            ])
            ->get()
            ->flatMap(function ($office) {
                $proposals = $office->budgetProposals;
                if ($proposals->isEmpty()) return [];

                $quarters = $proposals->pluck('items')->flatten()
                    ->pluck('target_quarter')->filter()->unique()->sort()->values();

                if ($quarters->isEmpty()) $quarters = collect(['Q1', 'Q2', 'Q3', 'Q4']);

                return $quarters->map(function ($quarter) use ($office) {
                    $budget    = (float) $office->budgetProposals->sum('total_estimated_cost') / 4;
                    $utilized  = (float) $office->purchaseRequests
                        ->whereIn('status', ['approved', 'completed'])
                        ->sum('total_amount');
                    $pct      = $budget > 0 ? min(100, round(($utilized / $budget) * 100)) : 0;
                    $risk     = $pct >= 70 ? 'On Track' : ($pct >= 40 ? 'Watch' : 'At Risk');

                    return [
                        'office'   => $office->name,
                        'quarter'  => $quarter,
                        'budget'   => round($budget),
                        'utilized' => round($utilized),
                        'percent'  => $pct,
                        'risk'     => $risk,
                    ];
                })->all();
            })
            ->values()
            ->all();

        $officesAtRisk = collect($utilizationRows)
            ->groupBy('office')
            ->filter(fn ($rows) => $rows->contains('risk', 'At Risk'))
            ->count();

        $offices  = collect($utilizationRows)->pluck('office')->unique()->values()->all();
        $quarters = collect($utilizationRows)->pluck('quarter')->unique()->sort()->values()->all();

        return view('prism.finance-office.budget-utilization-report', $this->withCommon('budget-utilization-report', [
            'pageTitle' => 'Budget Utilization Report',
            'summary' => [
                'campusBudget'       => (float) $campusBudget,
                'totalUtilized'      => (float) $totalUtilized,
                'utilizationPercent' => $utilizationPct,
                'officesAtRisk'      => $officesAtRisk,
            ],
            'quarters'         => $quarters ?: ['Q1', 'Q2', 'Q3', 'Q4'],
            'offices'          => $offices,
            'utilizationRows'  => $utilizationRows,
        ]));
    }

    public function endorse(Request $request, BudgetProposal $proposal): RedirectResponse
    {
        abort_if($proposal->status !== 'submitted', 403);

        $statusFrom = $proposal->status;
        $remarks    = $request->input('remarks', '');

        $proposal->update(['status' => 'endorsed', 'reviewed_at' => now(), 'remarks' => $remarks]);

        BudgetProposalReview::create([
            'budget_proposal_id'  => $proposal->id,
            'reviewed_by_user_id' => auth()->id(),
            'action'              => 'endorse',
            'status_from'         => $statusFrom,
            'status_to'           => 'endorsed',
            'remarks'             => $remarks,
            'reviewed_at'         => now(),
        ]);

        return redirect()->route('finance-office.proposal-review.show', $proposal->id)
            ->with('success', 'Proposal endorsed and forwarded to the Chancellor.');
    }

    public function returnProposal(Request $request, BudgetProposal $proposal): RedirectResponse
    {
        abort_if($proposal->status !== 'submitted', 403);

        $request->validate(
            ['remarks' => 'required|string|min:5'],
            ['remarks.required' => 'Return remarks are required.', 'remarks.min' => 'Remarks must be at least 5 characters.']
        );

        $remarks = $request->input('remarks');

        $proposal->update(['status' => 'returned', 'reviewed_at' => now(), 'remarks' => $remarks]);

        BudgetProposalReview::create([
            'budget_proposal_id'  => $proposal->id,
            'reviewed_by_user_id' => auth()->id(),
            'action'              => 'return',
            'status_from'         => 'submitted',
            'status_to'           => 'returned',
            'remarks'             => $remarks,
            'reviewed_at'         => now(),
        ]);

        return redirect()->route('finance-office.proposal-review')
            ->with('success', 'Proposal returned to the office with remarks.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function formatProposal(BudgetProposal $p): array
    {
        return [
            'id'     => $p->id,
            'title'  => $p->title,
            'status' => ucfirst($p->status),
            'office' => [
                'name'          => $p->office?->name ?? '—',
                'head'          => $p->submittedBy?->name ?? '—',
                'fiscalYear'    => (string) $p->fiscal_year,
                'submittedDate' => $p->submitted_at?->format('M d, Y') ?? '—',
                'totalAmount'   => (float) $p->total_estimated_cost,
                'fundSource'    => 'General Fund',
            ],
            'items' => $p->items->map(fn ($item) => [
                'description'       => $item->name,
                'unit'              => $item->unit,
                'quantity'          => (int) $item->quantity,
                'estimatedUnitCost' => (float) $item->estimated_unit_cost,
                'totalCost'         => (float) $item->estimated_total_cost,
                'justification'     => $item->remarks ?? '',
                'targetQuarter'     => $item->target_quarter ?? '—',
                'scoping'           => $item->marketReferences
                    ->where('is_selected', true)
                    ->map(fn ($ref) => [
                        'supplierName'  => $ref->supplier_name,
                        'price'         => (float) $ref->price,
                        'sourceLink'    => $ref->source_url ?? '#',
                        'dateRetrieved' => $ref->date_accessed?->format('M d, Y') ?? '—',
                    ])->values()->all(),
            ])->all(),
        ];
    }

    private function procurementMode(float $amount): string
    {
        if ($amount >= 2_000_000) return 'Public Bidding';
        if ($amount >= 1_000_000) return 'Competitive Bidding';
        if ($amount >= 50_000)    return 'Small Value Procurement';
        if ($amount >= 10_000)    return 'Shopping';
        return 'Direct Contracting';
    }

    private function procurementRecommendation(float $amount): string
    {
        if ($amount >= 2_000_000) return 'ABC meets Public Bidding threshold; formal competitive bidding required under RA 9184.';
        if ($amount >= 1_000_000) return 'ABC meets Competitive Bidding threshold; conduct formal canvassing with at least 3 quotations.';
        if ($amount >= 50_000)    return 'ABC is within SVP threshold; simplified procurement applies.';
        if ($amount >= 10_000)    return 'Readily available items; shopping procedure allowed.';
        return 'Sole source or low-value item; attach justification for direct contracting.';
    }

    private function withCommon(string $activeFinancePage, array $data): array
    {
        return array_merge([
            'activeRole'       => 'finance-office',
            'activeModulePage' => $activeFinancePage,
            'brandHref'        => route('finance-office.dashboard'),
            'roleLabel'        => 'Finance Office',
            'roleInitials'     => 'FO',
            'roleNavigation'   => [
                ['slug' => 'office-head',       'label' => 'Office Head / Dean',   'href' => route('office-head.dashboard')],
                ['slug' => 'finance-office',    'label' => 'Finance Office',        'href' => route('finance-office.dashboard')],
                ['slug' => 'procurement-office','label' => 'Procurement Office',    'href' => route('procurement-office.dashboard')],
                ['slug' => 'chancellor',        'label' => 'Chancellor',            'href' => route('chancellor.dashboard')],
                ['slug' => 'vice-chancellor',   'label' => 'Vice Chancellor',       'href' => route('vice-chancellor.dashboard')],
            ],
            'moduleNavLabel'   => 'Finance Office pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',                 'label' => 'Dashboard',                 'href' => route('finance-office.dashboard'),                  'icon' => 'layout-dashboard'],
                ['slug' => 'proposal-review',           'label' => 'Proposal Review',           'href' => route('finance-office.proposal-review'),            'icon' => 'clipboard-check'],
                ['slug' => 'annual-procurement-plan',   'label' => 'Annual Procurement Plan',   'href' => route('finance-office.annual-procurement-plan'),    'icon' => 'files'],
                ['slug' => 'budget-utilization-report', 'label' => 'Budget Utilization Report', 'href' => route('finance-office.budget-utilization-report'),  'icon' => 'trending-up'],
            ],
        ], $data);
    }
}
