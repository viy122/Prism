<?php

namespace App\Http\Controllers;

use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\BudgetProposalReview;
use App\Models\Office;
use App\Models\PurchaseRequest;
use App\Services\NotificationService;
use App\Services\ProcurementModeService;
use Illuminate\Http\JsonResponse;
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
            'pageTitle' => 'Budget Office Dashboard',
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
            // Submitted (awaiting endorsement) proposals surface first — stable sort
            // keeps each status group ordered by latest('submitted_at') above.
            ->sortBy(fn ($p) => $p->status === 'submitted' ? 0 : 1)
            ->values()
            ->map(fn ($p) => $this->formatProposal($p))
            ->all();

        // Land directly on the first proposal awaiting endorsement instead of a
        // blank "select one" state — that's what Finance actually needs to see first.
        $selectedProposal = $proposal
            ? collect($proposals)->firstWhere('id', (int) $proposal)
            : collect($proposals)->firstWhere('status', 'Submitted');

        return view('prism.finance-office.proposal-review', $this->withCommon('proposal-review', [
            'pageTitle'        => 'Proposal Review',
            'proposals'        => $proposals,
            'selectedProposal' => $selectedProposal,
            'pendingCount'     => collect($proposals)->where('status', 'Submitted')->count(),
        ]));
    }

    public function saveItemRemark(Request $request, BudgetProposalItem $item): JsonResponse
    {
        $validated = $request->validate([
            'ok'     => 'required|boolean',
            'remark' => 'nullable|string|max:1000',
        ]);

        if (!$validated['ok'] && empty(trim($validated['remark'] ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'A remark is required when flagging an item.',
            ], 422);
        }

        $item->update([
            'finance_ok'     => $validated['ok'],
            'finance_remark' => $validated['ok'] ? null : trim($validated['remark']),
        ]);

        return response()->json(['success' => true]);
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

        if ($proposal->items()->where('finance_ok', false)->exists()) {
            return redirect()->route('finance-office.proposal-review.show', $proposal->id)
                ->withErrors(['endorse' => 'Cannot endorse: one or more items were disapproved. Return the proposal to the office instead.']);
        }

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

        NotificationService::proposalEndorsed($proposal);

        return redirect()->route('finance-office.proposal-review.show', $proposal->id)
            ->with('success', 'PPMP approved and forwarded to the Chancellor.');
    }

    public function returnProposal(Request $request, BudgetProposal $proposal): RedirectResponse
    {
        abort_if($proposal->status !== 'submitted', 403);

        $request->validate(['remarks' => 'nullable|string']);

        $remarks = $request->input('remarks', '');

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

        NotificationService::proposalReturnedByFinance($proposal, $remarks);

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
                'id'                => $item->id,
                'financeOk'         => $item->finance_ok,
                'financeRemark'     => $item->finance_remark ?? '',
                'remarkUrl'         => route('finance-office.proposal-review.item-remark', $item->id),
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
        return ProcurementModeService::recommend($amount);
    }

    private function procurementRecommendation(float $amount): string
    {
        return ProcurementModeService::rationale($amount);
    }

    private function withCommon(string $activeFinancePage, array $data): array
    {
        return array_merge([
            'activeRole'       => 'finance-office',
            'activeModulePage' => $activeFinancePage,
            'brandHref'        => route('finance-office.dashboard'),
            'roleLabel'        => 'Budget Office',
            'roleInitials'     => 'BO',
            'roleNavigation'   => \App\Support\PrismNav::roleNavigation(),
            'moduleNavLabel'   => 'Budget Office pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',                 'label' => 'Dashboard',                 'href' => route('finance-office.dashboard'),                  'icon' => 'layout-dashboard'],
                ['slug' => 'proposal-review',           'label' => 'Proposal Review',           'href' => route('finance-office.proposal-review'),            'icon' => 'clipboard-check'],
                // Annual Procurement Plan moved to Procurement Office
                ['slug' => 'budget-utilization-report', 'label' => 'Budget Utilization Report', 'href' => route('finance-office.budget-utilization-report'),  'icon' => 'trending-up'],
            ],
        ], $data);
    }
}
