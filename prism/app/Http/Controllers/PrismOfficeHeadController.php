<?php

namespace App\Http\Controllers;

use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\BudgetProposalReview;
use App\Models\MarketPriceSurvey;
use App\Models\MarketScopingReference;
use App\Models\Office;
use App\Models\PurchaseRequest;
use App\Services\MarketScopingService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PrismOfficeHeadController extends Controller
{
    // ── Pages ─────────────────────────────────────────────────────────────────

    public function dashboard(): View
    {
        $officeId    = $this->officeId();
        $proposalIds = BudgetProposal::where('office_id', $officeId)->pluck('id');
        $items       = BudgetProposalItem::whereIn('budget_proposal_id', $proposalIds)->get();

        $summary = [
            'totalProposedItems'     => $items->count(),
            'totalProposedBudget'    => $items->sum('estimated_total_cost'),
            'approvedItems'          => $items->where('status', 'approved')->count(),
            'pendingItems'           => $items->whereIn('status', ['draft', 'pending'])->count(),
            'purchasedThisQuarter'   => PurchaseRequest::where('office_id', $officeId)
                                            ->where('status', 'approved')->count(),
            'unpurchasedThisQuarter' => PurchaseRequest::where('office_id', $officeId)
                                            ->whereNotIn('status', ['approved', 'completed'])->count(),
        ];

        $recentUpdates = BudgetProposalReview::with('budgetProposal')
            ->whereIn('budget_proposal_id', $proposalIds)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($r) => [
                'title'   => $r->budgetProposal?->title ?? 'Budget Proposal',
                'status'  => ucfirst(str_replace('_', ' ', $r->status_to ?? $r->action)),
                'time'    => ($r->reviewed_at ?? $r->created_at)->format('M d, Y, g:i A'),
                'details' => $r->remarks ?? '—',
            ])
            ->all();

        return view('prism.office-head.dashboard', $this->withCommon('office-head', 'dashboard', [
            'pageTitle'     => 'Office Head / Dean Dashboard',
            'summary'       => $summary,
            'recentUpdates' => $recentUpdates,
        ]));
    }

    public function budgetProposal(): View
    {
        $officeId = $this->officeId();
        $office   = Office::find($officeId);

        $proposal = BudgetProposal::where('office_id', $officeId)
            ->whereIn('status', ['draft', 'returned'])
            ->latest()
            ->first();

        $isReadOnly     = false;
        $proposalStatus = $proposal?->status ?? 'draft';
        $canStartNew    = false;
        $nextFiscalYear = null;

        if (!$proposal) {
            $proposal = BudgetProposal::where('office_id', $officeId)
                ->whereIn('status', ['submitted', 'endorsed', 'approved'])
                ->latest()
                ->first();
            if ($proposal) {
                $isReadOnly     = true;
                $proposalStatus = $proposal->status;
                $nextFiscalYear = $proposal->fiscal_year + 1;
                $canStartNew    = !DB::table('budget_proposals')
                    ->where('office_id', $officeId)
                    ->where('fiscal_year', $nextFiscalYear)
                    ->exists();
            }
        }

        $proposalForm = [
            'officeName'          => $office?->name ?? 'Your Office',
            'fiscalYear'          => $proposal?->fiscal_year ?? (now()->year + 1),
            'date'                => $proposal?->created_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'totalProposedBudget' => $proposal?->total_estimated_cost ?? 0,
        ];

        $encodedItems = $proposal
            ? $proposal->items()->with('marketReferences')->get()
                ->map(fn ($item) => [
                    'id'                => (string) $item->id,
                    'description'       => $item->name,
                    'unit'              => $item->unit,
                    'quantity'          => (float) $item->quantity,
                    'estimatedUnitCost' => (float) $item->estimated_unit_cost,
                    'totalCost'         => (float) $item->estimated_total_cost,
                    'justification'     => $item->remarks ?? '',
                    'targetQuarter'     => $item->target_quarter ?? 'Q1',
                    'scoping'           => $item->marketReferences
                        ->where('is_selected', true)
                        ->map(fn ($ref) => [
                            'title'         => $ref->title ?? $ref->supplier_name,
                            'supplierName'  => $ref->supplier_name,
                            'price'         => (float) $ref->price,
                            'source'        => $ref->source_type ?? 'Online',
                            'sourceLink'    => $ref->source_url ?? '',
                            'dateRetrieved' => $ref->date_accessed?->format('M d, Y') ?? '',
                        ])->values()->all(),
                ])->all()
            : [];

        return view('prism.office-head.budget-proposal', $this->withCommon('office-head', 'budget-proposal', [
            'pageTitle'      => 'Annual Budget Proposal',
            'proposalForm'   => $proposalForm,
            'encodedItems'   => $encodedItems,
            'isReadOnly'     => $isReadOnly,
            'proposalStatus' => $proposalStatus,
            'canStartNew'    => $canStartNew,
            'nextFiscalYear' => $nextFiscalYear,
        ]));
    }

    public function startNewCycle(Request $request): RedirectResponse
    {
        $officeId = $this->officeId();

        // Find the highest fiscal year for this office (raw query bypasses all model scopes)
        $latestYear = DB::table('budget_proposals')
            ->where('office_id', $officeId)
            ->max('fiscal_year');

        if (!$latestYear) {
            return redirect()->route('office-head.budget-proposal')
                ->with('error', 'No existing proposal found. Create your first budget proposal first.');
        }

        $nextYear = (int) $latestYear + 1;

        // Guard: should not already exist
        if (DB::table('budget_proposals')->where('office_id', $officeId)->where('fiscal_year', $nextYear)->exists()) {
            return redirect()->route('office-head.budget-proposal')
                ->with('error', "A proposal for FY {$nextYear} already exists.");
        }

        $baseCode = 'BP-' . str_pad($officeId, 3, '0', STR_PAD_LEFT) . '-' . $nextYear;
        $code = $baseCode;
        $n = 2;
        while (DB::table('budget_proposals')->where('code', $code)->exists()) {
            $code = $baseCode . '-' . $n++;
        }

        $office = auth()->user()->office;
        BudgetProposal::create([
            'office_id'          => $officeId,
            'created_by_user_id' => auth()->id(),
            'code'               => $code,
            'title'              => ($office?->name ?? 'Office') . ' Budget Proposal FY' . $nextYear,
            'fiscal_year'        => $nextYear,
            'status'             => 'draft',
        ]);

        return redirect()->route('office-head.budget-proposal')
            ->with('success', "FY {$nextYear} Budget Proposal started.");
    }

    public function marketScoping(): View
    {
        $officeId    = $this->officeId();
        $draftIds    = BudgetProposal::where('office_id', $officeId)
                           ->whereIn('status', ['draft', 'returned'])
                           ->pluck('id');
        $itemIds     = BudgetProposalItem::whereIn('budget_proposal_id', $draftIds)->pluck('id');

        $proposalItems = BudgetProposalItem::whereIn('budget_proposal_id', $draftIds)
            ->get()
            ->map(fn ($item) => [
                'id'                => (string) $item->id,
                'proposalCode'      => $item->code ?? "BP-{$item->budget_proposal_id}-{$item->id}",
                'itemName'          => $item->name,
                'category'          => $item->category ?? $item->ppmpCategoryLabel() ?? 'General',
                'brandPreference'   => $item->specifications_json['brand_preference'] ?? '',
                'specification'     => $item->description ?? '',
                'estimatedUnitCost' => (float) $item->estimated_unit_cost,
                'quantity'          => (int) $item->quantity,
                'unit'              => $item->unit,
            ])
            ->all();

        $selectedRefs = MarketScopingReference::whereIn('budget_proposal_item_id', $itemIds)
            ->where('is_selected', true)
            ->with('budgetProposalItem')
            ->get()
            ->map(fn ($ref) => $this->formatMarketReference($ref))
            ->all();

        $survey = BudgetProposal::where('office_id', $officeId)
            ->with('marketPriceSurvey')
            ->latest()
            ->first()
            ?->marketPriceSurvey;

        return view('prism.office-head.market-scoping', $this->withCommon('office-head', 'market-scoping', [
            'pageTitle'     => 'Market Scoping',
            'proposalItems' => $proposalItems,
            'selectedRefs'  => $selectedRefs,
            'survey'        => $survey,
        ]));
    }

    public function myProposals(): View
    {
        $proposals = BudgetProposal::with('reviews')
            ->where('office_id', $this->officeId())
            ->latest()
            ->get()
            ->map(fn ($proposal) => [
                'id'              => $proposal->code ?: "bp-{$proposal->id}",
                'title'           => $proposal->title,
                'fiscalYear'      => (string) $proposal->fiscal_year,
                'dateSubmitted'   => ($proposal->submitted_at ?? $proposal->created_at)->format('M d, Y'),
                'totalAmount'     => (float) $proposal->total_estimated_cost,
                'status'          => ucwords(str_replace('_', ' ', $proposal->status)),
                'returnedRemarks' => $proposal->status === 'returned' ? $proposal->remarks : null,
                'timeline'        => $proposal->reviews
                    ->map(fn ($r) => [
                        'step'      => ucwords(str_replace('_', ' ', $r->action)),
                        'timestamp' => ($r->reviewed_at ?? $r->created_at)->format('M d, Y g:i A'),
                        'remarks'   => $r->remarks ?? '—',
                    ])->all(),
            ])
            ->all();

        return view('prism.office-head.my-proposals', $this->withCommon('office-head', 'my-proposals', [
            'pageTitle'   => 'My Proposals',
            'statuses'    => ['Draft', 'Submitted', 'Under Review', 'Endorsed', 'Returned', 'Approved'],
            'fiscalYears' => BudgetProposal::where('office_id', $this->officeId())
                                ->distinct()->orderByDesc('fiscal_year')
                                ->pluck('fiscal_year')->map(fn ($y) => (string) $y)->all(),
            'proposals'   => $proposals,
        ]));
    }

    public function purchaseRequests(): View
    {
        $purchaseItems = PurchaseRequest::with('items')
            ->where('office_id', $this->officeId())
            ->orderByRaw("FIELD(status, 'in_progress', 'pending', 'approved', 'completed', 'delayed')")
            ->orderBy('number')
            ->get()
            ->map(fn ($pr) => [
                'dbId'        => $pr->id,
                'number'      => $pr->number ?? "pr-{$pr->id}",
                'title'       => $pr->title,
                'quarter'     => $this->extractQuarter($pr->number ?? ''),
                'fiscalYear'  => $pr->fiscal_year,
                'totalAmount' => (float) ($pr->total_amount ?? 0),
                'status'      => $pr->status,
                'statusLabel' => ucwords(str_replace('_', ' ', $pr->status)),
                'remarks'     => $pr->remarks ?? '',
                'uploadedAt'  => $pr->uploaded_at?->format('M d, Y'),
                'itemCount'   => $pr->items->count(),
                'items'       => $pr->items->map(fn ($item) => [
                    'name'      => $item->name,
                    'quantity'  => (int) $item->quantity,
                    'unit'      => $item->unit,
                    'unitCost'  => (float) $item->estimated_unit_cost,
                    'totalCost' => (float) $item->estimated_total_cost,
                ])->all(),
            ])
            ->all();

        return view('prism.office-head.purchase-requests', $this->withCommon('office-head', 'purchase-requests', [
            'pageTitle'     => 'Purchase Requests',
            'purchaseItems' => $purchaseItems,
        ]));
    }

    private function extractQuarter(string $number): string
    {
        return preg_match('/-(Q[1-4])(?:-|$)/', $number, $m) ? $m[1] : '';
    }

    // ── Budget Proposal CRUD ─────────────────────────────────────────────────

    public function storeItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description'       => 'required|string|max:500',
            'unit'              => 'required|string|max:50',
            'quantity'          => 'required|numeric|min:0.01',
            'estimatedUnitCost' => 'required|numeric|min:0',
            'justification'     => 'nullable|string|max:1000',
            'targetQuarter'     => 'required|in:Q1,Q2,Q3,Q4',
        ]);

        $officeId = $this->officeId();

        $proposal = BudgetProposal::where('office_id', $officeId)
            ->whereIn('status', ['draft', 'returned'])
            ->orderByDesc('fiscal_year')
            ->first();

        if (!$proposal) {
            $year     = now()->year + 1;
            $baseCode = 'BP-' . str_pad($officeId, 3, '0', STR_PAD_LEFT) . '-' . $year;
            $code     = $baseCode;
            $n        = 2;
            while (DB::table('budget_proposals')->where('code', $code)->exists()) {
                $code = $baseCode . '-' . $n++;
            }
            $proposal = BudgetProposal::create([
                'office_id'            => $officeId,
                'created_by_user_id'   => auth()->id(),
                'code'                 => $code,
                'title'                => 'FY ' . $year . ' Annual Budget Proposal',
                'fiscal_year'          => $year,
                'total_estimated_cost' => 0,
                'status'               => 'draft',
            ]);
        }

        $total = $validated['quantity'] * $validated['estimatedUnitCost'];

        $item = $proposal->items()->create([
            'created_by_user_id'   => auth()->id(),
            'name'                 => $validated['description'],
            'description'          => $validated['description'],
            'quantity'             => $validated['quantity'],
            'unit'                 => $validated['unit'],
            'estimated_unit_cost'  => $validated['estimatedUnitCost'],
            'estimated_total_cost' => $total,
            'target_quarter'       => $validated['targetQuarter'],
            'remarks'              => $validated['justification'] ?? null,
            'status'               => 'draft',
        ]);

        $proposal->update([
            'total_estimated_cost' => $proposal->items()->sum('estimated_total_cost'),
        ]);

        return response()->json([
            'success' => true,
            'item'    => [
                'id'                => (string) $item->id,
                'description'       => $item->name,
                'unit'              => $item->unit,
                'quantity'          => (float) $item->quantity,
                'estimatedUnitCost' => (float) $item->estimated_unit_cost,
                'totalCost'         => (float) $item->estimated_total_cost,
                'justification'     => $item->remarks ?? '',
                'targetQuarter'     => $item->target_quarter,
                'scoping'           => [],
            ],
        ]);
    }

    public function updateItem(Request $request, BudgetProposalItem $item): JsonResponse
    {
        $proposal = $item->budgetProposal;
        if (!in_array($proposal->status, ['draft', 'returned'])) {
            return response()->json([
                'success' => false,
                'message' => 'This PPMP is no longer editable.',
            ], 422);
        }

        $validated = $request->validate([
            'description'       => 'required|string|max:500',
            'unit'              => 'required|string|max:50',
            'quantity'          => 'required|numeric|min:0.01',
            'estimatedUnitCost' => 'required|numeric|min:0',
            'justification'     => 'nullable|string|max:1000',
            'targetQuarter'     => 'required|in:Q1,Q2,Q3,Q4',
        ]);

        $total = $validated['quantity'] * $validated['estimatedUnitCost'];

        $item->update([
            'name'                 => $validated['description'],
            'description'          => $validated['description'],
            'quantity'             => $validated['quantity'],
            'unit'                 => $validated['unit'],
            'estimated_unit_cost'  => $validated['estimatedUnitCost'],
            'estimated_total_cost' => $total,
            'target_quarter'       => $validated['targetQuarter'],
            'remarks'              => $validated['justification'] ?? null,
        ]);

        $proposal->update([
            'total_estimated_cost' => $proposal->items()->sum('estimated_total_cost'),
        ]);

        return response()->json([
            'success' => true,
            'item'    => [
                'id'                => (string) $item->id,
                'description'       => $item->name,
                'unit'              => $item->unit,
                'quantity'          => (float) $item->quantity,
                'estimatedUnitCost' => (float) $item->estimated_unit_cost,
                'totalCost'         => (float) $item->estimated_total_cost,
                'justification'     => $item->remarks ?? '',
                'targetQuarter'     => $item->target_quarter,
            ],
        ]);
    }

    public function destroyItem(BudgetProposalItem $item): JsonResponse
    {
        $proposal = $item->budgetProposal;
        $item->delete();
        $proposal->update([
            'total_estimated_cost' => $proposal->items()->sum('estimated_total_cost'),
        ]);

        return response()->json(['success' => true]);
    }

    public function submitProposal(Request $request): JsonResponse
    {
        $proposal = BudgetProposal::where('office_id', $this->officeId())
            ->where('status', 'draft')
            ->latest()
            ->firstOrFail();

        if ($proposal->items()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please add at least one item before submitting.',
            ], 422);
        }

        $proposal->update([
            'status'                => 'submitted',
            'submitted_at'          => now(),
            'submitted_by_user_id'  => auth()->id(),
        ]);

        BudgetProposalReview::create([
            'budget_proposal_id'  => $proposal->id,
            'reviewed_by_user_id' => auth()->id(),
            'action'              => 'submitted',
            'status_from'         => 'draft',
            'status_to'           => 'submitted',
            'remarks'             => 'Proposal submitted for review.',
            'reviewed_at'         => now(),
        ]);

        NotificationService::proposalSubmitted($proposal);

        return response()->json([
            'success'  => true,
            'redirect' => route('office-head.my-proposals') . '?submitted=1',
        ]);
    }

    // ── API ───────────────────────────────────────────────────────────────────

    public function runMarketScoping(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|string|max:100',
            'query'   => 'required|string|max:300',
            'specs'   => 'nullable|array|max:10',
            'specs.*' => 'string|max:100',
            'budget'  => 'nullable|numeric|min:0',
        ]);

        $query = $this->sanitizeSearchQuery($request->input('query'));
        if ($query === '') {
            return response()->json([
                'success' => false,
                'message' => 'Search query is empty after removing invalid characters. Please type a valid item name.',
                'results' => [],
            ]);
        }

        $service = new MarketScopingService();
        $results = $service->search($query, 30);

        if (empty($results)) {
            return response()->json([
                'success'         => false,
                'message'         => 'No market references found for this item. Try adjusting the search keywords.',
                'results'         => [],
                'suggestion'      => $this->didYouMean($query),
                'quota_exhausted' => $service->isQuotaExhausted(),
            ]);
        }

        $specs   = array_values(array_filter($request->input('specs', [])));
        $results = $service->matchSpecs($results, $specs, $query);

        $budget = (float) $request->input('budget', 0);
        if ($budget > 0) {
            $results = $service->flagAdvantageous($results, $budget);
        }

        return response()->json([
            'success'           => true,
            'results'           => $results,
            'query'             => $query,
            'count'             => count($results),
            'specs_filtered'    => !empty($specs),
            'quota_exhausted'   => $service->isQuotaExhausted(),
            'matcher_available' => $service->matcherAvailable(),
        ]);
    }

    public function marketScopingSuggestions(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $like     = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
        $officeId = $this->officeId();

        // Item names already encoded by this office
        $itemNames = DB::table('budget_proposal_items')
            ->join('budget_proposals', 'budget_proposals.id', '=', 'budget_proposal_items.budget_proposal_id')
            ->where('budget_proposals.office_id', $officeId)
            ->where('budget_proposal_items.name', 'like', $like)
            ->distinct()
            ->limit(5)
            ->pluck('budget_proposal_items.name');

        // Past search queries from cached market snapshots
        $pastQueries = DB::table('market_price_snapshots')
            ->where('query_used', 'like', $like)
            ->distinct()
            ->limit(5)
            ->pluck('query_used');

        $suggestions = $itemNames->map(fn ($n) => ['text' => $n, 'type' => 'item'])
            ->concat($pastQueries->map(fn ($s) => ['text' => $s, 'type' => 'search']))
            ->unique('text')
            ->take(8)
            ->values();

        return response()->json(['suggestions' => $suggestions]);
    }

    private function sanitizeSearchQuery(string $raw): string
    {
        // Keep letters, numbers, spaces, and characters common in product specs
        $clean = preg_replace('/[^\p{L}\p{N}\s\-\+\.\"\/(),]/u', ' ', $raw);
        return trim(preg_replace('/\s+/', ' ', $clean));
    }

    private function didYouMean(string $query): ?string
    {
        $known = DB::table('budget_proposal_items')
            ->join('budget_proposals', 'budget_proposals.id', '=', 'budget_proposal_items.budget_proposal_id')
            ->where('budget_proposals.office_id', $this->officeId())
            ->distinct()
            ->limit(200)
            ->pluck('budget_proposal_items.name')
            ->concat(DB::table('market_price_snapshots')->distinct()->limit(200)->pluck('query_used'));

        $qLower = mb_strtolower($query);
        $best     = null;
        $bestDist = PHP_INT_MAX;

        foreach ($known as $candidate) {
            $cLower = mb_strtolower($candidate);
            if ($cLower === $qLower) continue;
            $dist = levenshtein($qLower, mb_substr($cLower, 0, 255));
            if ($dist < $bestDist) {
                $bestDist = $dist;
                $best     = $candidate;
            }
        }

        // Only suggest when reasonably close (≤ 40% of query length)
        return ($best !== null && $bestDist <= max(2, (int) (mb_strlen($query) * 0.4))) ? $best : null;
    }

    public function attachToProposal(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|max:300',
            'refs'  => 'required|array|min:3|max:3',
        ]);

        $officeId = $this->officeId();
        $query    = trim($request->input('query'));
        $refs     = $request->input('refs');

        $draftProposal = BudgetProposal::where('office_id', $officeId)
            ->whereIn('status', ['draft', 'returned'])
            ->latest()->first();

        $existingItem = $draftProposal
            ? $this->findMatchingItem($draftProposal, $query)
            : null;

        if ($existingItem) {
            $this->saveRefsToItem($existingItem, $refs, $request);
            return response()->json([
                'success'     => true,
                'item_exists' => true,
                'item_name'   => $existingItem->name,
            ]);
        }

        $lowestPrice = collect($refs)->min(fn ($r) => (float) ($r['price'] ?? 0));

        return response()->json([
            'success'      => true,
            'item_exists'  => false,
            'query'        => $query,
            'lowest_price' => $lowestPrice,
            'refs_data'    => $refs,
        ]);
    }

    public function addItemWithRefs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description'       => 'required|string|max:500',
            'unit'              => 'required|string|max:50',
            'quantity'          => 'required|numeric|min:0.01',
            'estimatedUnitCost' => 'required|numeric|min:0',
            'targetQuarter'     => 'required|in:Q1,Q2,Q3,Q4',
            'category'          => 'required|string|max:200',
            'refs'              => 'required|array|min:1|max:3',
        ]);

        $officeId = $this->officeId();

        // Use the existing draft/returned proposal for any fiscal year
        $proposal = BudgetProposal::where('office_id', $officeId)
            ->whereIn('status', ['draft', 'returned'])
            ->orderByDesc('fiscal_year')
            ->first();

        if (!$proposal) {
            $year     = now()->year + 1;
            $baseCode = 'BP-' . str_pad($officeId, 3, '0', STR_PAD_LEFT) . '-' . $year;
            $code     = $baseCode;
            $n        = 2;
            while (DB::table('budget_proposals')->where('code', $code)->exists()) {
                $code = $baseCode . '-' . $n++;
            }
            $proposal = BudgetProposal::create([
                'office_id'            => $officeId,
                'created_by_user_id'   => $request->user()?->id,
                'code'                 => $code,
                'title'                => 'FY ' . $year . ' Annual Budget Proposal',
                'fiscal_year'          => $year,
                'total_estimated_cost' => 0,
                'status'               => 'draft',
            ]);
        }

        $total = $validated['quantity'] * $validated['estimatedUnitCost'];

        $item = $proposal->items()->create([
            'created_by_user_id'   => $request->user()?->id,
            'name'                 => $validated['description'],
            'description'          => $validated['description'],
            'category'             => $validated['category'],
            'quantity'             => $validated['quantity'],
            'unit'                 => $validated['unit'],
            'estimated_unit_cost'  => $validated['estimatedUnitCost'],
            'estimated_total_cost' => $total,
            'target_quarter'       => $validated['targetQuarter'],
            'status'               => 'draft',
        ]);

        $proposal->update([
            'total_estimated_cost' => $proposal->items()->sum('estimated_total_cost'),
        ]);

        $this->saveRefsToItem($item, $validated['refs'], $request);

        return response()->json([
            'success'  => true,
            'redirect' => route('office-head.budget-proposal'),
        ]);
    }

    public function deleteRef(MarketScopingReference $ref): JsonResponse
    {
        $officeId = $this->officeId();
        $itemOffice = $ref->budgetProposalItem?->budgetProposal?->office_id;

        if ($itemOffice !== $officeId) {
            return response()->json(['success' => false], 403);
        }

        $ref->delete();

        return response()->json(['success' => true]);
    }

    public function previewMps(): View
    {
        $officeId = $this->officeId();
        $proposal = BudgetProposal::where('office_id', $officeId)
            ->with([
                'items.marketReferences' => fn ($q) => $q->where('is_selected', true)->orderBy('price'),
                'marketPriceSurvey.submittedBy',
            ])
            ->latest()
            ->firstOrFail();

        $items = $proposal->items
            ->filter(fn ($i) => $i->marketReferences->isNotEmpty())
            ->values();

        return view('prism.office-head.market-price-survey', $this->withCommon('office-head', 'market-scoping', [
            'pageTitle' => 'Market Study',
            'proposal'  => $proposal,
            'items'     => $items,
            'survey'    => $proposal->marketPriceSurvey,
        ]));
    }

    public function submitMps(Request $request): JsonResponse
    {
        $officeId = $this->officeId();
        $proposal = BudgetProposal::where('office_id', $officeId)
            ->whereIn('status', ['draft', 'returned'])
            ->latest()
            ->firstOrFail();

        if ($proposal->marketPriceSurvey) {
            return response()->json(['error' => 'Market Study already submitted.'], 409);
        }

        $survey = $proposal->marketPriceSurvey()->create([
            'submitted_by_user_id' => auth()->id(),
            'ref_number'           => 'MPS-' . now()->format('Y') . '-' . str_pad($proposal->id, 5, '0', STR_PAD_LEFT),
            'submitted_at'         => now(),
        ]);

        return response()->json(['success' => true, 'ref_number' => $survey->ref_number]);
    }

    public function placeholder(string $role): View
    {
        $titles = ['vice-chancellor' => 'Vice Chancellor'];

        return view('prism.placeholder', $this->withCommon($role, null, [
            'pageTitle'    => $titles[$role],
            'sectionTitle' => $titles[$role],
        ]));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function findMatchingItem(BudgetProposal $proposal, string $query): ?BudgetProposalItem
    {
        $words = array_filter(
            array_map('strtolower', preg_split('/\s+/', trim($query))),
            fn ($w) => strlen($w) >= 3
        );
        if (empty($words)) return null;

        return $proposal->items()->get()->first(function (BudgetProposalItem $item) use ($words) {
            $name = strtolower($item->name);
            foreach ($words as $w) {
                if (str_contains($name, $w)) return true;
            }
            return false;
        });
    }

    private function saveRefsToItem(BudgetProposalItem $item, array $refs, Request $request): void
    {
        MarketScopingReference::where('budget_proposal_item_id', $item->id)
            ->update(['is_selected' => false]);

        foreach ($refs as $ref) {
            $dbId = isset($ref['id']) && is_numeric($ref['id']) ? (int) $ref['id'] : null;

            if ($dbId) {
                MarketScopingReference::where('id', $dbId)
                    ->where('budget_proposal_item_id', $item->id)
                    ->update(['is_selected' => true, 'status' => 'approved']);
            } else {
                MarketScopingReference::create([
                    'budget_proposal_item_id' => $item->id,
                    'created_by_user_id'     => $request->user()?->id,
                    'supplier_name'          => $ref['supplier'] ?? 'Unknown',
                    'source_type'            => $ref['source'] ?? 'Google Shopping',
                    'source_url'             => $ref['url'] ?? '',
                    'title'                  => $ref['name'] ?? '',
                    'price'                  => (float) ($ref['price'] ?? 0),
                    'currency'               => 'PHP',
                    'date_accessed'          => now()->toDateString(),
                    'is_selected'            => true,
                    'status'                 => 'approved',
                    'match_status'           => 'Verified',
                ]);
            }
        }
    }

    private function officeId(): int
    {
        return auth()->user()?->office_id ?? 1;
    }

    private function formatMarketReference(MarketScopingReference $ref): array
    {
        $snapshot = $ref->source_snapshot_json ?? [];
        $specs    = array_filter(explode(',', $ref->description ?? ''));
        $specs    = array_map('trim', $specs);

        return [
            'id'              => (string) $ref->id,
            'itemId'          => (string) $ref->budget_proposal_item_id,
            'supplierName'    => $ref->supplier_name,
            'productTitle'    => $ref->title ?? $ref->supplier_name,
            'price'           => (float) $ref->price,
            'sourceLink'      => $ref->source_url ?? '',
            'dateAccessed'    => $ref->date_accessed?->format('M d, Y') ?? '',
            'specs'           => $ref->description ?? '',
            'availability'    => $snapshot['availability'] ?? 'Available',
            'credibility'     => $ref->source_type ?? 'Online Listing',
            'sourceType'      => $ref->source_type ?? 'Online Listing',
            'brand'           => $snapshot['brand'] ?? ($ref->budgetProposalItem?->category ?? ''),
            'category'        => $ref->budgetProposalItem?->category ?? '',
            'keywords'        => $ref->specifications_json['keywords'] ?? [],
            'matchLevel'      => $ref->match_status ?? 'Unverified',
            'isValidMatch'    => $ref->status === 'approved',
            'imageLabel'      => $snapshot['brand'] ?? $ref->supplier_name,
            'shortSpecs'      => implode(' | ', array_slice($specs, 0, 4)),
            'fullSpecs'       => $specs,
            'warrantySupport' => str_contains(strtolower($ref->description ?? ''), 'warrant')
                ? 'Warranty/support detail is included and should be verified during procurement review.'
                : 'Warranty/support detail should be confirmed with the supplier before final documentation.',
            'credibilityInfo' => $this->credibilityInfo($ref->source_type),
            'requestedSpecs'  => $ref->specifications_json['requested_specs'] ?? [],
            'matchedSpecs'    => $specs,
            'missingSpecs'    => $ref->specifications_json['missing_specs'] ?? [],
            'matchScore'      => $ref->specifications_json['match_score'] ?? 75,
            'matchStatus'     => $ref->match_status ?? 'Needs Review',
        ];
    }

    private function credibilityInfo(?string $sourceType): string
    {
        return match ($sourceType) {
            'PhilGEPS'                => 'Listed on the Philippine Government Electronic Procurement System.',
            'Lazada PH', 'Shopee PH' => 'E-commerce listing — use as price reference only; confirm with supplier quotation.',
            'Supplier Quote'          => 'Reference is treated as an accredited quotation source for market scoping documentation.',
            default                   => 'Source credibility should be reviewed before attaching the reference.',
        };
    }

    private function withCommon(string $activeRole, ?string $activeOfficePage, array $data): array
    {
        return array_merge([
            'activeRole'       => $activeRole,
            'activeOfficePage' => $activeOfficePage,
            'activeModulePage' => $activeOfficePage,
            'brandHref'        => route('office-head.dashboard'),
            'roleNavigation'   => [
                ['slug' => 'office-head',       'label' => 'Office Head / Dean',   'href' => route('office-head.dashboard')],
                ['slug' => 'finance-office',    'label' => 'Finance Office',        'href' => route('finance-office.dashboard')],
                ['slug' => 'procurement-office','label' => 'Procurement Office',    'href' => route('procurement-office.dashboard')],
                ['slug' => 'chancellor',        'label' => 'Chancellor',            'href' => route('chancellor.dashboard')],
                ['slug' => 'vice-chancellor',   'label' => 'Vice Chancellor',       'href' => route('vice-chancellor.dashboard')],
            ],
            'moduleNavLabel'   => 'Office Head / Dean pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',         'label' => 'Dashboard',         'href' => route('office-head.dashboard'),         'icon' => 'layout-dashboard'],
                ['slug' => 'market-scoping',    'label' => 'Market Scoping',    'href' => route('office-head.market-scoping'),    'icon' => 'search'],
                ['slug' => 'budget-proposal',   'label' => 'Budget Proposal',   'href' => route('office-head.budget-proposal'),   'icon' => 'file-plus'],
                ['slug' => 'my-proposals',      'label' => 'My Proposals',      'href' => route('office-head.my-proposals'),      'icon' => 'folder'],
                ['slug' => 'purchase-requests', 'label' => 'Purchase Requests', 'href' => route('office-head.purchase-requests'), 'icon' => 'receipt'],
            ],
        ], $data);
    }
}
