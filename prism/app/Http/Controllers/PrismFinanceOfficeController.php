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
        // All-time counts — a "this month" scope here would silently drop
        // everything reviewed in an earlier month, going out of sync with the
        // "Proposals by Status" table below (which was never month-scoped).
        $endorsed          = BudgetProposal::where('status', 'endorsed')->count();
        $returned          = BudgetProposal::where('status', 'returned')->count();
        $totalCampusBudget = BudgetProposal::whereIn('status', ['submitted', 'endorsed', 'approved'])
            ->sum('total_estimated_cost');

        $officeStatusGroups = Office::has('budgetProposals')
            ->with('budgetProposals')
            ->get()
            ->map(fn ($office) => [
                'office'   => $office->code,
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
                'office'        => $p->office?->code ?? '—',
                'submittedDate' => $p->submitted_at?->format('M d, Y') ?? $p->created_at->format('M d, Y'),
                'totalAmount'   => (float) $p->total_estimated_cost,
            ])
            ->all();

        return view('prism.finance-office.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Budget Office Dashboard',
            'summary' => [
                'awaitingReview'    => $awaitingReview,
                'endorsed'          => $endorsed,
                'returned'          => $returned,
                'totalCampusBudget' => (float) $totalCampusBudget,
            ],
            'officeStatusGroups'  => $officeStatusGroups,
            'recentSubmissions'   => $recentSubmissions,
            'proposalsByStatus'   => $this->proposalsByStatus(),
            'budgetByOffice'      => $this->campusBudgetByOffice(),
            'monthlyReviewActivity' => $this->monthlyReviewActivity(),
        ]));
    }

    /** Full campus PPMP pipeline breakdown (all 5 statuses), for the status doughnut chart. */
    private function proposalsByStatus(): array
    {
        $counts = BudgetProposal::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $labels = ['draft' => 'Draft', 'submitted' => 'Submitted', 'endorsed' => 'Endorsed', 'returned' => 'Returned', 'approved' => 'Approved'];

        return collect($labels)
            ->map(fn ($label, $key) => ['label' => $label, 'count' => (int) ($counts[$key] ?? 0)])
            ->values()
            ->all();
    }

    /** Total proposed budget per office (active submissions only), for the office bar chart. */
    private function campusBudgetByOffice(): array
    {
        return Office::has('budgetProposals')
            ->withSum(['budgetProposals as totalBudget' => fn ($q) => $q->whereIn('status', ['submitted', 'endorsed', 'approved'])], 'total_estimated_cost')
            ->get()
            ->map(fn ($o) => ['office' => $o->code, 'total' => (float) ($o->totalBudget ?? 0)])
            ->filter(fn ($g) => $g['total'] > 0)
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * Submitted vs endorsed counts per calendar month (current year), from the
     * actual review-action log rather than `updated_at` (which can be touched
     * by unrelated edits and drifts from when the action really happened).
     * Both a past- and present-tense action value exist for endorsements
     * ('endorse' and 'endorsed') from different code paths over time — both
     * count toward the same bucket here.
     */
    private function monthlyReviewActivity(): array
    {
        $submitted = array_fill(1, 12, 0);
        $endorsed  = array_fill(1, 12, 0);

        BudgetProposalReview::whereYear('reviewed_at', now()->year)
            ->get(['action', 'reviewed_at'])
            ->each(function ($r) use (&$submitted, &$endorsed) {
                $month = $r->reviewed_at->month;
                if ($r->action === 'submitted') {
                    $submitted[$month]++;
                } elseif (in_array($r->action, ['endorse', 'endorsed'], true)) {
                    $endorsed[$month]++;
                }
            });

        return ['submitted' => array_values($submitted), 'endorsed' => array_values($endorsed)];
    }

    public function proposalReview(?string $proposal = null): View
    {
        // Only what Budget Office actually still needs to act on: fresh
        // submissions (first-time or revised-and-resubmitted — both land back
        // on status 'submitted'), plus proposals the Chancellor sent back to
        // *them* to reconsider. 'endorsed' proposals are with the Chancellor
        // now, not Budget Office's to keep reviewing; a 'returned' proposal
        // that Budget Office itself sent back to the Office Head is that
        // office's job to revise, not something to keep showing here either —
        // isActionableByFinance() (via the latest 'return' review's
        // status_from) is what actually distinguishes the two 'returned' cases.
        $proposals = BudgetProposal::with(['office', 'submittedBy', 'items.marketReferences'])
            ->whereIn('status', ['submitted', 'returned'])
            ->latest('submitted_at')
            ->get()
            ->filter(fn ($p) => $this->isActionableByFinance($p))
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
        // Once endorsed, the proposal is with the Chancellor — not Budget Office's to
        // touch again until it's either freshly submitted or the Chancellor returns it.
        abort_if(!$this->isActionableByFinance($item->budgetProposal), 403);

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
        // "Approved allocation" — the official, endorsed-or-better figure.
        // Deliberately narrower than the per-office table below, which needs
        // every office's real spending visible regardless of where their PPMP
        // currently sits in the approval pipeline (see campusBudgetByOffice()).
        $campusBudget = BudgetProposal::whereIn('status', ['endorsed', 'approved'])->sum('total_estimated_cost');

        // "Utilized" = real, non-cancelled procurement activity (in progress or
        // fully paid) — derived from the always-accurate tracking chain
        // (PurchaseRequest::lifecycleBucket()), not the raw `status` column.
        // That column only ever holds granular Procurement-Office pipeline
        // values ('new', 'for_alobs', 'po_confirmed', ...); the simple values
        // this report used to filter on ('approved', 'completed') are never
        // actually written by any real code path — only 2 hand-seeded demo
        // rows ever had them, so utilization was silently ~93% undercounted.
        $allPrs = PurchaseRequest::with('office')->get();
        $isUtilized = fn ($pr) => in_array($pr->lifecycleBucket(), ['in_progress', 'completed'], true);

        $totalUtilized  = $allPrs->filter($isUtilized)->sum('total_amount');
        $utilizationPct = $campusBudget > 0 ? round(($totalUtilized / $campusBudget) * 100) : 0;

        $utilizationRows = Office::has('budgetProposals')
            ->with(['budgetProposals.items', 'purchaseRequests'])
            ->get()
            ->flatMap(function ($office) use ($isUtilized) {
                // Every proposal regardless of status — an office whose only
                // PPMP is still 'returned' or 'submitted' can still have real,
                // active spending Budget Office needs visibility into; filtering
                // to endorsed/approved here used to drop those offices entirely.
                $items = $office->budgetProposals->pluck('items')->flatten();

                $quarters = $items->pluck('target_quarter')->filter()->unique()->sort()->values();
                if ($quarters->isEmpty()) $quarters = collect(['Q1', 'Q2', 'Q3', 'Q4']);

                $activePrs = $office->purchaseRequests->filter($isUtilized);

                return $quarters->map(function ($quarter) use ($items, $activePrs, $office) {
                    // Genuinely per-quarter now — was previously the office's
                    // whole-year total divided by 4 and repeated identically
                    // across all 4 rows regardless of $quarter.
                    $budget   = (float) $items->where('target_quarter', $quarter)->sum('estimated_total_cost');
                    $utilized = (float) $activePrs->filter(fn ($pr) => $pr->numberQuarter() === $quarter)->sum('total_amount');
                    $pct      = $budget > 0 ? min(100, round(($utilized / $budget) * 100)) : 0;
                    $risk     = $pct >= 70 ? 'On Track' : ($pct >= 40 ? 'Watch' : 'At Risk');

                    return [
                        'office'   => $office->code,
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
            'quarters'          => $quarters ?: ['Q1', 'Q2', 'Q3', 'Q4'],
            'offices'           => $offices,
            'utilizationRows'   => $utilizationRows,
            'utilByOfficeChart' => $this->utilizationByOfficeChart($utilizationRows),
            'riskDistribution'  => collect($utilizationRows)->countBy('risk')->all(),
        ]));
    }

    /** Budget vs utilized totals per office (summed across quarters), for the office bar chart. */
    private function utilizationByOfficeChart(array $utilizationRows): array
    {
        return collect($utilizationRows)
            ->groupBy('office')
            ->map(fn ($rows, $office) => [
                'office'   => $office,
                'budget'   => $rows->sum('budget'),
                'utilized' => $rows->sum('utilized'),
            ])
            ->values()
            ->all();
    }

    public function endorse(Request $request, BudgetProposal $proposal): RedirectResponse
    {
        abort_if(!$this->isActionableByFinance($proposal), 403);

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

        return $this->nextReviewRedirect($proposal, 'PPMP approved and forwarded to the Chancellor.');
    }

    public function returnProposal(Request $request, BudgetProposal $proposal): RedirectResponse
    {
        // A slow double-click/double-submit can fire this request twice; the first
        // already moves the proposal to 'returned', so the second no longer passes
        // isActionableByFinance(). Rather than surfacing a raw 403 for an action that
        // in fact already succeeded, treat it as a no-op and confirm the end state.
        if (!$this->isActionableByFinance($proposal)) {
            return redirect()->route('finance-office.proposal-review.show', $proposal->id)
                ->with('success', $proposal->status === 'returned'
                    ? 'This proposal has already been returned to the office.'
                    : 'This proposal is no longer available for this action.');
        }

        $request->validate(['remarks' => 'nullable|string']);

        // An empty textarea submits as '', but Laravel's ConvertEmptyStringsToNull
        // middleware turns that into null before it ever reaches here — input()'s
        // default only kicks in when the key is missing, not when it's present-but-null.
        $remarks    = (string) $request->input('remarks', '');
        $statusFrom = $proposal->status;

        $proposal->update(['status' => 'returned', 'reviewed_at' => now(), 'remarks' => $remarks]);

        BudgetProposalReview::create([
            'budget_proposal_id'  => $proposal->id,
            'reviewed_by_user_id' => auth()->id(),
            'action'              => 'return',
            'status_from'         => $statusFrom,
            'status_to'           => 'returned',
            'remarks'             => $remarks,
            'reviewed_at'         => now(),
        ]);

        NotificationService::proposalReturnedByFinance($proposal, $remarks);

        return $this->nextReviewRedirect($proposal, 'Proposal returned to the office with remarks.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * After acting on a proposal, jump straight to the next one still awaiting
     * endorsement instead of dropping back to a blank "select a proposal" state —
     * continues the review queue automatically. If nothing else is pending,
     * fall back to the proposal just acted on so its updated status (badge +
     * "no further action available" message) is the thing actually shown,
     * rather than a generic empty screen with no confirmation of what happened.
     */
    private function nextReviewRedirect(BudgetProposal $justActedOn, string $successMessage): RedirectResponse
    {
        // Budget Office reviews all offices, not just one — "next" means the next
        // proposal awaiting endorsement campus-wide. Matches the same ordering
        // proposalReview()'s own default-landing auto-select already uses.
        $next = BudgetProposal::where('status', 'submitted')->latest('submitted_at')->first();

        $targetId = $next?->id ?? $justActedOn->id;

        return redirect()->route('finance-office.proposal-review.show', $targetId)
            ->with('success', $successMessage);
    }

    /**
     * 'returned' is reused for two different points in the workflow — Budget
     * Office returning to the Office Head (status_from: submitted) AND the
     * Chancellor returning to Budget Office (status_from: endorsed) — so the
     * status alone can't tell which side is supposed to act next. Only the
     * latter needs Endorse/Return available to Budget Office again; the
     * former is waiting on the Office Head to revise and resubmit.
     */
    private function isActionableByFinance(BudgetProposal $proposal): bool
    {
        if ($proposal->status === 'submitted') {
            return true;
        }
        if ($proposal->status !== 'returned') {
            return false;
        }

        return $this->latestReturnReview($proposal)?->status_from === 'endorsed';
    }

    private function latestReturnReview(BudgetProposal $proposal): ?BudgetProposalReview
    {
        return $proposal->reviews()->where('action', 'return')->with('reviewedBy')->latest('reviewed_at')->first();
    }

    private function formatProposal(BudgetProposal $p): array
    {
        // The Chancellor's (or Budget Office's own) return reason lives in the review
        // log, not on the proposal itself — BudgetProposal.remarks isn't reliably kept
        // in sync with who returned it most recently, so read from the audit trail.
        $lastReturn = $p->status === 'returned' ? $this->latestReturnReview($p) : null;

        return [
            'id'         => $p->id,
            'code'       => $p->code,
            'title'      => $p->title,
            'status'     => ucfirst($p->status),
            'actionable' => $this->isActionableByFinance($p),
            'returnRemarks' => $lastReturn ? [
                'text' => $lastReturn->remarks,
                'from' => $lastReturn->status_from === 'endorsed' ? 'Chancellor' : 'Budget Office',
                'by'   => $lastReturn->reviewedBy?->name ?? '—',
                'date' => ($lastReturn->reviewed_at ?? $lastReturn->created_at)->format('M d, Y g:i A'),
            ] : null,
            // Full submit/return/endorse trail — not just the latest return —
            // so a reviewer can see the whole "submitted → returned → revised
            // → resubmitted" story for a proposal, not just its most recent step.
            'reviewHistory' => $p->reviews()->with('reviewedBy')->orderBy('reviewed_at')->get()
                ->map(fn ($r) => [
                    'action'  => ucfirst($r->action),
                    'from'    => $r->status_from ? ucfirst($r->status_from) : null,
                    'to'      => $r->status_to ? ucfirst($r->status_to) : null,
                    'by'      => $r->reviewedBy?->name ?? '—',
                    'date'    => ($r->reviewed_at ?? $r->created_at)->format('M d, Y g:i A'),
                    'remarks' => $r->remarks ?: null,
                ])->values()->all(),
            'office' => [
                'name'          => $p->office?->name ?? '—',
                'head'          => $p->submittedBy?->name ?? '—',
                'fiscalYear'    => (string) $p->fiscal_year,
                'submittedDate' => $p->submitted_at?->format('M d, Y') ?? '—',
                'totalAmount'   => (float) $p->total_estimated_cost,
                'proposedBudget' => (float) ($p->proposed_budget ?? $p->total_estimated_cost),
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
