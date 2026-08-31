<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSignatureQueue;
use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\BudgetProposalReview;
use App\Models\DocumentUpload;
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
    use HandlesSignatureQueue;

    protected function queueRoleCode(): string
    {
        return 'office-head';
    }

    protected function queueRoutePrefix(): string
    {
        return 'office-head';
    }

    protected function queueOfficeIds(): ?array
    {
        return [$this->officeId()];
    }

    /**
     * Office Head / Dean is "End User" — the 1st signatory on both PR and
     * AOC (never PO). Scoped strictly to their own office via
     * queueOfficeIds(), never another office's.
     */
    public function forMySignature(): View
    {
        return view('prism.shared.for-my-signature', $this->withCommon('office-head', 'for-my-signature', [
            'pageTitle' => 'For My Signature',
            'layout'    => 'prism.layouts.office-head',
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
        return ['pr', 'aoc'];
    }

    // ── Pages ─────────────────────────────────────────────────────────────────

    public function dashboard(): View
    {
        $officeId = $this->officeId();

        // budget_proposal_items.status is a vestigial column — nothing in the
        // app ever writes to it after creation, so every item sits at its
        // 'draft' default forever regardless of what actually happened to its
        // proposal. The real per-item lifecycle state lives on the parent
        // BudgetProposal's own status (draft/submitted/endorsed/returned/approved),
        // so item counts below are bucketed by that instead.
        $proposals = BudgetProposal::where('office_id', $officeId)
            ->withCount('items')
            ->withSum('items', 'estimated_total_cost')
            ->get();
        $itemCountByProposalStatus = $proposals->groupBy('status')
            ->map(fn ($group) => $group->sum('items_count'));

        $summary = [
            'totalProposedItems'     => $proposals->sum('items_count'),
            'totalProposedBudget'    => $proposals->sum('items_sum_estimated_total_cost'),
            'approvedItems'          => $itemCountByProposalStatus->get('approved', 0),
            'pendingItems'           => $itemCountByProposalStatus->get('submitted', 0) + $itemCountByProposalStatus->get('endorsed', 0),
            'returnedItems'          => $itemCountByProposalStatus->get('returned', 0),
            'draftItems'             => $itemCountByProposalStatus->get('draft', 0),
            'monthlyBudgetUsage'     => $this->monthlyBudgetUsage($officeId),
            'funnelStages'           => $this->prFunnelStages($officeId),
            'categoryBreakdown'      => $this->itemCategoryBreakdown($officeId),
            'budgetByQuarter'        => $this->itemBudgetByQuarter($officeId),
        ];

        $recentUpdates = BudgetProposalReview::with('budgetProposal')
            ->whereIn('budget_proposal_id', $proposals->pluck('id'))
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($r) => [
                'title'   => $r->budgetProposal?->title ?? 'PPMP',
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

    public function budgetProposal(Request $request): View
    {
        $officeId = $this->officeId();
        $office   = Office::find($officeId);

        // One office can have several PPMPs within the same fiscal year (e.g. a
        // supplemental proposal), so the selector lists individual proposals —
        // not just distinct years — and is keyed by proposal id.
        $proposalOptions = BudgetProposal::where('office_id', $officeId)
            ->orderByDesc('fiscal_year')->orderByDesc('created_at')
            ->get(['id', 'fiscal_year', 'code', 'status'])
            ->map(function ($p) {
                $label = 'FY ' . $p->fiscal_year . ' — ' . $p->code;
                if ($p->status === 'returned') {
                    // 'returned' covers two different senders — only a Budget Office
                    // return actually needs the office head to revise; a Chancellor
                    // return is sitting with Budget Office, not back here yet.
                    $label .= $this->isEditableByOfficeHead($p)
                        ? ' — Returned, needs revision'
                        : ' — Returned to Budget Office';
                } else {
                    $label .= ' — ' . ucfirst($p->status);
                }
                return ['id' => $p->id, 'label' => $label, 'status' => $p->status];
            })->all();

        // Proposal selector: ?proposal=ID shows that specific PPMP (read-only unless draft/returned).
        // ?fy=YYYY is kept as a fallback for old links — resolves to the latest proposal for that year.
        $requestedId        = (int) $request->query('proposal', 0);
        $requestedFy        = (int) $request->query('fy', 0);
        $explicitlySelected = (bool) ($requestedId || $requestedFy);
        $proposal    = null;
        $sessionKey  = "office_head.selected_proposal.{$officeId}";

        if ($requestedId) {
            $proposal = BudgetProposal::where('office_id', $officeId)->find($requestedId);
        } elseif ($requestedFy) {
            $proposal = BudgetProposal::where('office_id', $officeId)
                ->where('fiscal_year', $requestedFy)
                ->latest()
                ->first();
        } else {
            // No explicit selection on this request — fall back to whichever PPMP the
            // office head last picked in the selector, so navigating elsewhere (sidebar,
            // dashboard shortcuts, My PPMP, etc.) and back doesn't silently reset the
            // page to whatever happens to be the latest proposal.
            $rememberedId = $request->session()->get($sessionKey);
            if ($rememberedId) {
                $proposal = BudgetProposal::where('office_id', $officeId)->find($rememberedId);
            }
        }

        if (!$proposal) {
            $proposal = BudgetProposal::where('office_id', $officeId)
                ->whereIn('status', ['draft', 'returned'])
                ->latest()
                ->first();
        }

        // A 'returned' proposal is only actually editable here if it was Budget
        // Office who returned it — a Chancellor return leaves it with Budget Office
        // to reconsider first, even though the stored status looks identical.
        $isReadOnly     = $proposal
            ? !($proposal->status === 'draft' || ($proposal->status === 'returned' && $this->isEditableByOfficeHead($proposal)))
            : false;
        $proposalStatus = $proposal?->status ?? 'draft';

        if (!$proposal) {
            $proposal = BudgetProposal::where('office_id', $officeId)
                ->whereIn('status', ['submitted', 'endorsed', 'approved'])
                ->latest()
                ->first();
            if ($proposal) {
                $isReadOnly     = true;
                $proposalStatus = $proposal->status;
            }
        }

        // Landing on the tab with nothing currently editable auto-starts a fresh
        // blank PPMP for the office — no manual "Create New" / "Start FY" step.
        // Skipped only when the office head explicitly browsed to a specific
        // (necessarily read-only, since editable ones are already caught above)
        // past proposal via the selector — that deliberate choice is respected
        // as-is, not overridden by a new draft.
        if (!$explicitlySelected && (!$proposal || $isReadOnly)) {
            $nextYear = (int) (DB::table('budget_proposals')->where('office_id', $officeId)->max('fiscal_year') ?? now()->year) + 1;
            $proposal = BudgetProposal::where('office_id', $officeId)->where('fiscal_year', $nextYear)->first()
                ?? $this->createDraftProposal($officeId, $nextYear);
            $isReadOnly     = false;
            $proposalStatus = $proposal->status;
        }

        // Line items lock once a market study has been submitted for this proposal —
        // separate from $isReadOnly, which only tracks the proposal's own status.
        $itemsLocked = $isReadOnly || (bool) $proposal?->marketPriceSurvey;

        if ($proposal) {
            $request->session()->put($sessionKey, $proposal->id);
        }

        // Proposed Budget is a manually-set target/ceiling, separate from the auto-derived
        // item-sum total (total_estimated_cost) — falls back to that sum until the office
        // head explicitly sets their own figure.
        $proposalForm = [
            'officeName'          => $office?->name ?? 'Your Office',
            'title'               => $proposal?->title ?? '',
            'fiscalYear'          => $proposal?->fiscal_year ?? (now()->year + 1),
            // Once a PPMP has actually been submitted, "Date Prepared" should reflect
            // that — not when the draft was first started, which can be weeks earlier.
            'date'                => ($proposal?->submitted_at ?? $proposal?->created_at)?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'totalProposedBudget' => $proposal?->proposed_budget ?? $proposal?->total_estimated_cost ?? 0,
        ];

        $titleUpdateUrl          = $proposal ? route('office-head.budget-proposal.update-title', $proposal->id) : null;
        $proposedBudgetUpdateUrl = $proposal ? route('office-head.budget-proposal.update-proposed-budget', $proposal->id) : null;

        $encodedItems = $proposal
            ? $proposal->items()->with(['marketReferences', 'sourceFiles'])->get()
                ->map(fn ($item) => [
                    'id'                => (string) $item->id,
                    'description'       => $item->name,
                    'unit'              => $item->unit,
                    'quantity'          => (float) $item->quantity,
                    'estimatedUnitCost' => (float) $item->estimated_unit_cost,
                    'totalCost'         => (float) $item->estimated_total_cost,
                    'justification'     => $item->remarks ?? '',
                    'financeOk'         => $item->finance_ok,
                    'financeRemark'     => $item->finance_remark ?? '',
                    'targetQuarter'     => $item->target_quarter ?? 'Q1',
                    'category'          => $item->category ?? $item->ppmpCategoryLabel() ?? 'General',
                    'sourceOfFund'      => $item->source_of_fund,
                    'itemClassification' => $item->item_classification ?? 'Regular',
                    'attachUrl'         => route('office-head.budget-proposal.item-attachment', $item->id),
                    'attachments'       => $item->sourceFiles->map(fn ($doc) => [
                        'id'        => $doc->id,
                        'name'      => $doc->original_filename ?? $doc->title,
                        'url'       => str_starts_with($doc->file_path, 'http')
                            ? $doc->file_path
                            : \Illuminate\Support\Facades\Storage::url($doc->file_path),
                        'deleteUrl' => route('office-head.budget-proposal.attachment.delete', $doc->id),
                    ])->values()->all(),
                    'scoping'           => $item->marketReferences
                        ->where('is_selected', true)
                        ->map(fn ($ref) => [
                            'id'            => (string) $ref->id,
                            'title'         => $ref->title ?? $ref->supplier_name,
                            'supplierName'  => $ref->supplier_name,
                            'price'         => (float) $ref->price,
                            'source'        => $ref->source_type ?? 'Online',
                            'sourceLink'    => $ref->source_url ?? '',
                            'dateRetrieved' => $ref->date_accessed?->format('M d, Y') ?? '',
                        ])->values()->all(),
                ])->all()
            : [];

        // Readiness: an item is supported by market scoping refs OR an attached source file
        $itemCount              = count($encodedItems);
        $scopingReferenceCount  = collect($encodedItems)->sum(fn ($i) => count($i['scoping']));
        $missingScopingCount    = collect($encodedItems)
            ->filter(fn ($i) => empty($i['scoping']) && empty($i['attachments']))
            ->count();
        $proposalTotal          = collect($encodedItems)->sum('totalCost');

        return view('prism.office-head.budget-proposal', $this->withCommon('office-head', 'budget-proposal', [
            'pageTitle'             => 'PPMP — Project Procurement Management Plan',
            'proposalForm'          => $proposalForm,
            'encodedItems'          => $encodedItems,
            'isReadOnly'            => $isReadOnly,
            'itemsLocked'           => $itemsLocked,
            'proposalStatus'        => $proposalStatus,
            'proposalOptions'       => $proposalOptions,
            'selectedProposalId'    => $proposal?->id,
            'titleUpdateUrl'        => $titleUpdateUrl,
            'proposedBudgetUpdateUrl' => $proposedBudgetUpdateUrl,
            'itemCount'             => $itemCount,
            'scopingReferenceCount' => $scopingReferenceCount,
            'missingScopingCount'   => $missingScopingCount,
            'proposalTotal'         => $proposalTotal,
        ]));
    }

    /**
     * Explicit "Create New PPMP" action — offered once the office head is
     * viewing an approved PPMP with nothing left to edit (see the read-only
     * banner in budget-proposal.blade.php). Mirrors the same find-or-create
     * lookup the auto-start path uses, just triggered by a deliberate click
     * instead of silently on landing, and always lands on the new draft
     * explicitly (?proposal=) so it isn't ambiguous with whatever the
     * session happened to remember.
     */
    public function createNewProposal(): RedirectResponse
    {
        $officeId = $this->officeId();
        $nextYear = (int) (DB::table('budget_proposals')->where('office_id', $officeId)->max('fiscal_year') ?? now()->year) + 1;

        $proposal = BudgetProposal::where('office_id', $officeId)->where('fiscal_year', $nextYear)->first()
            ?? $this->createDraftProposal($officeId, $nextYear);

        return redirect()->route('office-head.budget-proposal', ['proposal' => $proposal->id]);
    }

    /**
     * A blank draft PPMP for the given office/fiscal year — no office/title
     * template baked in, so the office head starts from an empty form rather
     * than a presumptuous prefilled one. Used to auto-start a PPMP whenever the
     * office head lands on the tab with nothing currently editable.
     */
    private function createDraftProposal(int $officeId, int $fiscalYear): BudgetProposal
    {
        $baseCode = 'BP-' . str_pad($officeId, 3, '0', STR_PAD_LEFT) . '-' . $fiscalYear;
        $code     = $baseCode;
        $n        = 2;
        while (DB::table('budget_proposals')->where('code', $code)->exists()) {
            $code = $baseCode . '-' . $n++;
        }

        return BudgetProposal::create([
            'office_id'          => $officeId,
            'created_by_user_id' => auth()->id(),
            'code'               => $code,
            'title'              => '',
            'fiscal_year'        => $fiscalYear,
            'status'             => 'draft',
        ]);
    }

    public function updateTitle(Request $request, BudgetProposal $proposal): JsonResponse
    {
        abort_if($proposal->office_id !== $this->officeId(), 403);
        abort_if(!in_array($proposal->status, ['draft', 'returned'], true), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $proposal->update(['title' => $validated['title']]);

        return response()->json(['success' => true, 'title' => $proposal->title]);
    }

    public function updateProposedBudget(Request $request, BudgetProposal $proposal): JsonResponse
    {
        abort_if($proposal->office_id !== $this->officeId(), 403);
        abort_if(!in_array($proposal->status, ['draft', 'returned'], true), 403);

        $validated = $request->validate([
            'proposed_budget' => 'required|numeric|min:0',
        ]);

        $proposal->update(['proposed_budget' => $validated['proposed_budget']]);

        return response()->json(['success' => true, 'proposed_budget' => (float) $proposal->proposed_budget]);
    }

    public function marketScoping(Request $request): View
    {
        $officeId    = $this->officeId();
        $requestedId = (int) $request->query('proposal', 0);

        // Which proposal "owns" this scoping session — carried forward through
        // attach/add-item calls and back-links so navigating away and back
        // doesn't silently land on a different (e.g. newer) PPMP.
        $activeProposal = $requestedId
            ? BudgetProposal::where('office_id', $officeId)->where('id', $requestedId)
                ->whereIn('status', ['draft', 'returned'])->first()
            : null;
        if (!$activeProposal) {
            $activeProposal = BudgetProposal::where('office_id', $officeId)
                ->whereIn('status', ['draft', 'returned'])
                ->latest()->first();
        }

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

        $survey = $activeProposal?->marketPriceSurvey;

        // Arriving via an item's "Add reference" link: that item's already-selected
        // references must be preserved as the starting selection, not dropped —
        // otherwise attaching a new set silently unselects the ones already saved.
        $existingItemId   = null;
        $existingItemRefs = [];
        $requestedItemId  = (int) $request->query('item', 0);
        if ($requestedItemId) {
            $existingItem = BudgetProposalItem::where('id', $requestedItemId)
                ->whereHas('budgetProposal', fn ($q) => $q->where('office_id', $officeId))
                ->with(['marketReferences' => fn ($q) => $q->where('is_selected', true)])
                ->first();

            if ($existingItem) {
                $existingItemId   = $existingItem->id;
                $existingItemRefs = $existingItem->marketReferences->map(fn ($ref) => [
                    'id'        => (string) $ref->id,
                    'name'      => $ref->title ?? $ref->supplier_name,
                    'price'     => (float) $ref->price,
                    'priceStr'  => number_format((float) $ref->price, 2),
                    'supplier'  => $ref->supplier_name,
                    'source'    => $ref->source_type ?? 'Online',
                    'date'      => $ref->date_accessed?->format('M d, Y') ?? now()->format('M d, Y'),
                    'url'       => $ref->source_url ?? '',
                    'pageToken' => null,
                    'specs'     => '',
                    'itemId'    => (string) $existingItem->id,
                ])->values()->all();
            }
        }

        return view('prism.office-head.market-scoping', $this->withCommon('office-head', 'market-scoping', [
            'pageTitle'        => 'Market Scoping',
            'proposalItems'    => $proposalItems,
            'selectedRefs'     => $selectedRefs,
            'survey'           => $survey,
            'proposalId'       => $activeProposal?->id,
            'existingItemId'   => $existingItemId,
            'existingItemRefs' => $existingItemRefs,
        ]));
    }

    public function myProposals(): View
    {
        $proposals = BudgetProposal::with('reviews')
            ->where('office_id', $this->officeId())
            ->latest()
            ->get()
            // Returned PPMPs need action, so they surface first regardless of date —
            // stable sort keeps each group ordered by latest() above.
            ->sortBy(fn ($p) => $p->status === 'returned' ? 0 : 1)
            ->values()
            ->map(function ($proposal) {
                // Each "submitted" review is one full submission of this PPMP —
                // the 1st is Version 1, every resubmission after a return is the
                // next version. Numbered in actual chronological order (not
                // display order) since that's what "v1/v2/v3" needs to mean.
                $submittedInOrder = $proposal->reviews
                    ->where('action', 'submitted')
                    ->sortBy(fn ($r) => $r->reviewed_at ?? $r->created_at)
                    ->values();
                $versionByReviewId = $submittedInOrder
                    ->mapWithKeys(fn ($r, $i) => [$r->id => $i + 1]);

                return [
                    'id'              => $proposal->code ?: "bp-{$proposal->id}",
                    'proposalId'      => $proposal->id,
                    'title'           => $proposal->title,
                    'fiscalYear'      => (string) $proposal->fiscal_year,
                    'dateSubmitted'   => ($proposal->submitted_at ?? $proposal->created_at)->format('M d, Y'),
                    'totalAmount'     => (float) $proposal->total_estimated_cost,
                    'status'          => ucwords(str_replace('_', ' ', $proposal->status)),
                    'returnedRemarks' => $proposal->status === 'returned' ? $proposal->remarks : null,
                    'timeline'        => $proposal->reviews
                        ->map(fn ($r) => [
                            'step'          => ucwords(str_replace('_', ' ', $r->action)),
                            'timestamp'     => ($r->reviewed_at ?? $r->created_at)->format('M d, Y g:i A'),
                            'remarks'       => $r->remarks ?? '—',
                            'version'       => $versionByReviewId->get($r->id),
                            'itemsSnapshot' => $r->review_data_json,
                        ])->all(),
                ];
            })
            ->all();

        return view('prism.office-head.my-proposals', $this->withCommon('office-head', 'my-proposals', [
            'pageTitle'   => 'My PPMPs',
            'statuses'    => ['Draft', 'Submitted', 'Under Review', 'Endorsed', 'Returned', 'Approved'],
            'fiscalYears' => BudgetProposal::where('office_id', $this->officeId())
                                ->distinct()->orderByDesc('fiscal_year')
                                ->pluck('fiscal_year')->map(fn ($y) => (string) $y)->all(),
            'proposals'   => $proposals,
        ]));
    }

    public function purchaseRequests(): View
    {
        $purchaseItems = PurchaseRequest::with(['items', 'abstractOfCanvass.purchaseOrder'])
            ->where('office_id', $this->officeId())
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($pr) {
                $tracking = $pr->effectiveTrackingStatus();
                // End users don't need internal-office phrasing — once paid, their
                // request has simply reached its final, completed state.
                if (($tracking['key'] ?? null) === 'paid') {
                    $tracking['label'] = 'Completed';
                }
                $bucket = $this->bucketTrackingStage($tracking['key'] ?? '');

                return [
                    'dbId'        => $pr->id,
                    'number'      => $pr->number ?? "pr-{$pr->id}",
                    'title'       => $pr->title,
                    'quarter'     => $this->extractQuarter($pr->number ?? ''),
                    'fiscalYear'  => $pr->fiscal_year,
                    'totalAmount' => (float) ($pr->total_amount ?? 0),
                    // Drives the main status badge/KPI counts — derived from the
                    // always-accurate tracking chain (see bucketTrackingStage()),
                    // not the raw `status` column.
                    'statusBucket' => $bucket,
                    'statusLabel'  => ['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'delayed' => 'Delayed'][$bucket],
                    'trackingStatus' => $tracking,
                    'remarks'     => $pr->remarks ?? '',
                    'uploadedAt'  => $pr->uploaded_at?->format('M d, Y'),
                    'createdAt'   => $pr->created_at->toIso8601String(),
                    'pdfFile'     => $pr->file_path,
                    'itemCount'   => $pr->items->count(),
                    'items'       => $pr->items->map(fn ($item) => [
                        'name'      => $item->name,
                        'quantity'  => (int) $item->quantity,
                        'unit'      => $item->unit,
                        'unitCost'  => (float) $item->estimated_unit_cost,
                        'totalCost' => (float) $item->estimated_total_cost,
                    ])->all(),
                ];
            })
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

    /**
     * Simple 4-state lifecycle bucket (pending / in_progress / completed /
     * delayed) for the "My Purchase Requests" page's KPI cards, Queue Health
     * panel, and status filter — derived from the always-accurate
     * currentTrackingStage() chain (see PurchaseRequest::currentTrackingStage())
     * rather than the raw `status` column, which mixes two different,
     * inconsistent vocabularies across older vs newer records (plain
     * 'pending'/'approved' alongside granular procurement-pipeline values
     * like 'for_alobs'/'po_confirmed') and so can't be reliably matched
     * against on its own — most of those granular values were silently
     * falling out of every bucket entirely.
     */
    private function bucketTrackingStage(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'halted:') => 'delayed',
            $key === 'pr:not_yet_created'    => 'pending',
            $key === 'paid'                  => 'completed',
            default                          => 'in_progress',
        };
    }

    /** Sum of this office's PR totals per calendar month, current year, for the dashboard's bar chart. */
    private function monthlyBudgetUsage(int $officeId): array
    {
        $totalsByMonth = PurchaseRequest::where('office_id', $officeId)
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        return collect(range(1, 12))
            ->map(fn ($m) => (float) ($totalsByMonth[$m] ?? 0))
            ->all();
    }

    /**
     * Where this office's PRs actually sit across the full PR→AOC→PO→Payment
     * pipeline, bucketed from PurchaseRequest::currentTrackingStage()'s unified
     * journey key rather than re-deriving the stage logic here. Halted PRs
     * (cancelled/denied) aren't "at" a pipeline stage, so they're tallied
     * separately instead of silently padding one of the 5 buckets.
     */
    private function prFunnelStages(int $officeId): array
    {
        $buckets = [
            'PR Signing'         => 0,
            'Canvassing / AOC'   => 0,
            'PO Signing'         => 0,
            'Delivery / Payment' => 0,
            'Paid'               => 0,
        ];
        $halted = 0;

        PurchaseRequest::with('abstractOfCanvass.purchaseOrder')
            ->where('office_id', $officeId)
            ->get()
            ->each(function ($pr) use (&$buckets, &$halted) {
                $key = $pr->currentTrackingStage()['key'];

                if (str_starts_with($key, 'halted:')) {
                    $halted++;
                } elseif (str_starts_with($key, 'pr:')) {
                    $buckets['PR Signing']++;
                } elseif ($key === 'for_canvassing' || $key === 'awaiting_aoc' || str_starts_with($key, 'aoc:')) {
                    $buckets['Canvassing / AOC']++;
                } elseif ($key === 'awaiting_po' || str_starts_with($key, 'po:')) {
                    $buckets['PO Signing']++;
                } elseif (str_starts_with($key, 'po_status:')) {
                    $buckets['Delivery / Payment']++;
                } elseif ($key === 'paid') {
                    $buckets['Paid']++;
                }
            });

        return ['buckets' => $buckets, 'halted' => $halted];
    }

    /**
     * Spend by Schedule 9 category. BudgetProposalItem::ppmp_category (the
     * A–I letter code) is never actually written anywhere in the app, so
     * relying on it alone would show 100% "Uncategorized" — this mirrors the
     * fallback chain already used elsewhere in this controller (lines ~241,
     * 437, 712) that prefers the free-text `category` field first.
     */
    private function itemCategoryBreakdown(int $officeId): array
    {
        return BudgetProposalItem::whereHas('budgetProposal', fn ($q) => $q->where('office_id', $officeId))
            ->get(['category', 'ppmp_category', 'estimated_total_cost'])
            ->groupBy(fn ($item) => $item->category ?: ($item->ppmpCategoryLabel() ?: 'General'))
            ->map(fn ($group) => (float) $group->sum('estimated_total_cost'))
            ->sortDesc()
            ->all();
    }

    /** Planned spend per quarter at the PPMP stage — distinct from monthlyBudgetUsage(), which is actual PR spend by calendar month. */
    private function itemBudgetByQuarter(int $officeId): array
    {
        $sums = BudgetProposalItem::whereHas('budgetProposal', fn ($q) => $q->where('office_id', $officeId))
            ->selectRaw('target_quarter, SUM(estimated_total_cost) as total')
            ->groupBy('target_quarter')
            ->pluck('total', 'target_quarter');

        return collect(['Q1', 'Q2', 'Q3', 'Q4'])
            ->map(fn ($q) => (float) ($sums[$q] ?? 0))
            ->all();
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
            'sourceOfFund'      => 'nullable|string|max:100',
            'itemClassification' => 'nullable|string|max:50',
            'proposal_id'       => 'nullable|integer|exists:budget_proposals,id',
        ]);

        $officeId = $this->officeId();

        // A draft and a returned PPMP can now exist side by side, so which proposal
        // an item belongs to must come from the page the user is actually looking
        // at — never guessed — or it can silently land on the wrong one.
        $proposal = null;
        if (!empty($validated['proposal_id'])) {
            $proposal = BudgetProposal::where('id', $validated['proposal_id'])
                ->where('office_id', $officeId)
                ->whereIn('status', ['draft', 'returned'])
                ->first();

            if (!$proposal || $proposal->marketPriceSurvey) {
                return response()->json([
                    'success' => false,
                    'message' => 'This PPMP is no longer editable.',
                ], 422);
            }
        }

        if (!$proposal) {
            $proposal = BudgetProposal::where('office_id', $officeId)
                ->whereIn('status', ['draft', 'returned'])
                ->orderByDesc('fiscal_year')
                ->first();
        }

        if (!$proposal) {
            $nextYear = (int) (DB::table('budget_proposals')->where('office_id', $officeId)->max('fiscal_year') ?? now()->year) + 1;
            $proposal = $this->createDraftProposal($officeId, $nextYear);
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
            'source_of_fund'       => $validated['sourceOfFund'] ?? null,
            'item_classification'  => $validated['itemClassification'] ?? 'Regular',
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
                'category'          => $item->category ?? 'General',
                'sourceOfFund'      => $item->source_of_fund,
                'itemClassification' => $item->item_classification,
                'scoping'           => [],
                'attachments'       => [],
                'attachUrl'         => route('office-head.budget-proposal.item-attachment', $item->id),
            ],
        ]);
    }

    public function updateItem(Request $request, BudgetProposalItem $item): JsonResponse
    {
        $proposal = $item->budgetProposal;
        if (!in_array($proposal->status, ['draft', 'returned']) || $proposal->marketPriceSurvey) {
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
            'sourceOfFund'      => 'nullable|string|max:100',
            'itemClassification' => 'nullable|string|max:50',
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
            'source_of_fund'       => $validated['sourceOfFund'] ?? null,
            'item_classification'  => $validated['itemClassification'] ?? 'Regular',
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
                'sourceOfFund'      => $item->source_of_fund,
                'itemClassification' => $item->item_classification,
            ],
        ]);
    }

    public function destroyItem(BudgetProposalItem $item): JsonResponse
    {
        $proposal = $item->budgetProposal;
        if (!in_array($proposal->status, ['draft', 'returned']) || $proposal->marketPriceSurvey) {
            return response()->json([
                'success' => false,
                'message' => 'This PPMP is no longer editable.',
            ], 422);
        }

        $item->delete();
        $proposal->update([
            'total_estimated_cost' => $proposal->items()->sum('estimated_total_cost'),
        ]);

        return response()->json(['success' => true]);
    }

    /** Attach a source file (e.g. a saved market study) to a PPMP item. */
    public function storeItemAttachment(Request $request, BudgetProposalItem $item): JsonResponse
    {
        $proposal = $item->budgetProposal;
        if ($proposal->office_id !== $this->officeId()) {
            abort(403);
        }
        if (!in_array($proposal->status, ['draft', 'returned'], true)) {
            return response()->json(['error' => 'This PPMP is no longer editable.'], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpeg,jpg,png,xlsx,xls,docx|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('ppmp-sources/' . now()->year, 'public');

        $doc = DocumentUpload::create([
            'uploaded_by_user_id' => auth()->id(),
            'attachable_type'     => BudgetProposalItem::class,
            'attachable_id'       => $item->id,
            'document_type'       => 'market_study_source',
            'title'               => 'Source file for ' . $item->name,
            'original_filename'   => $file->getClientOriginalName(),
            'file_path'           => $path,
            'mime_type'           => $file->getClientMimeType(),
            'file_size'           => $file->getSize(),
            'status'              => 'uploaded',
            'uploaded_at'         => now(),
        ]);

        return response()->json([
            'success'    => true,
            'attachment' => [
                'id'        => $doc->id,
                'name'      => $doc->original_filename,
                'url'       => \Illuminate\Support\Facades\Storage::url($path),
                'deleteUrl' => route('office-head.budget-proposal.attachment.delete', $doc->id),
            ],
        ]);
    }

    public function destroyItemAttachment(DocumentUpload $document): JsonResponse
    {
        if ($document->document_type !== 'market_study_source') {
            return response()->json(['error' => 'Not a PPMP source attachment.'], 422);
        }

        $item = $document->attachable;
        if (!$item instanceof BudgetProposalItem || $item->budgetProposal->office_id !== $this->officeId()) {
            abort(403);
        }
        if (!in_array($item->budgetProposal->status, ['draft', 'returned'], true)) {
            return response()->json(['error' => 'This PPMP is no longer editable.'], 422);
        }

        if (!str_starts_with($document->file_path, 'http')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        return response()->json(['success' => true]);
    }

    public function submitProposal(Request $request): JsonResponse
    {
        $officeId = $this->officeId();

        // A returned proposal must be resubmittable too — it's a legitimate, editable
        // state (draft and returned can coexist), not just a one-way dead end. Prefer
        // whichever proposal the office head is actually looking at; only guess by
        // "latest draft" as a fallback for old callers that don't send an id.
        $requestedId = (int) $request->input('proposal_id', 0);
        $proposal    = $requestedId
            ? BudgetProposal::where('id', $requestedId)->where('office_id', $officeId)
                ->whereIn('status', ['draft', 'returned'])->first()
            : null;
        if (!$proposal) {
            $proposal = BudgetProposal::where('office_id', $officeId)
                ->whereIn('status', ['draft', 'returned'])
                ->latest()
                ->firstOrFail();
        }

        if ($proposal->items()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please add at least one item before submitting.',
            ], 422);
        }

        // Every item must be supported by market scoping refs or an attached source file
        $unsupported = $proposal->items()
            ->whereDoesntHave('marketReferences', fn ($q) => $q->where('is_selected', true))
            ->whereDoesntHave('documents', fn ($q) => $q->where('document_type', 'market_study_source'))
            ->count();

        if ($unsupported > 0) {
            return response()->json([
                'success' => false,
                'message' => "{$unsupported} item(s) still need market scoping references or an attached source file.",
            ], 422);
        }

        $statusFrom = $proposal->status;

        // A resubmission only needs Budget to re-review whatever was actually
        // flagged — clear finance_ok just on those items, since a stale "issued"
        // flag on an item the office head already fixed silently blocks Endorse
        // next time (it checks for any item still flagged false, regardless of
        // how old that flag is). Items Budget already approved (finance_ok true)
        // are left alone — nothing changed for them, so they shouldn't need
        // re-approving just because a *different* item on the same PPMP was
        // revised. The remark text itself is kept so Budget can still see what
        // was flagged before, as context while re-reviewing the revised item.
        if ($statusFrom === 'returned') {
            $proposal->items()->where('finance_ok', false)->update(['finance_ok' => null]);
        }

        $proposal->update([
            'status'                => 'submitted',
            'submitted_at'          => now(),
            'submitted_by_user_id'  => auth()->id(),
        ]);

        // Item edits after a return are destructive (updateItem()/destroyItem()
        // write/delete in place) — this is the one point that actually matters
        // to preserve: a snapshot of exactly what was submitted for review this
        // time, so returning here later shows what v1/v2/v3 each looked like.
        $itemsSnapshot = $proposal->items()->get([
            'id', 'name', 'description', 'quantity', 'unit',
            'estimated_unit_cost', 'estimated_total_cost', 'target_quarter',
        ])->toArray();

        BudgetProposalReview::create([
            'budget_proposal_id'  => $proposal->id,
            'reviewed_by_user_id' => auth()->id(),
            'action'              => 'submitted',
            'status_from'         => $statusFrom,
            'status_to'           => 'submitted',
            'remarks'             => $statusFrom === 'returned' ? 'Proposal revised and resubmitted for review.' : 'Proposal submitted for review.',
            'review_data_json'    => $itemsSnapshot,
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
            'item_id'    => 'required|string|max:100',
            'query'      => 'required|string|max:300',
            'specs'      => 'nullable|array|max:10',
            'specs.*'    => 'string|max:100',
            'budget'     => 'nullable|numeric|min:0',
            'department' => 'nullable|string|in:appliances,medical,office,it,janitorial,hardware,furniture,sports',
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
        $results = $service->search($query, 30, $request->input('department'));

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

    public function resolveMarketSource(Request $request): RedirectResponse
    {
        $request->validate([
            'page_token' => 'nullable|string|max:4000',
            'fallback'   => 'nullable|string|max:2000',
        ]);

        $fallback = (string) $request->input('fallback', '');
        if ($fallback === '' || !preg_match('#^https?://#i', $fallback)) {
            $fallback = 'https://shopping.google.com/';
        }

        $pageToken = (string) $request->input('page_token', '');
        if ($pageToken !== '') {
            $link = (new MarketScopingService())->resolveDirectLink($pageToken);
            if ($link) {
                return redirect()->away($link);
            }
        }

        return redirect()->away($fallback);
    }

    public function marketProductDetails(Request $request): JsonResponse
    {
        $request->validate([
            'page_token' => 'required|string|max:4000',
        ]);

        $details = (new MarketScopingService())->fetchProductDetails($request->input('page_token'));

        if (!$details) {
            return response()->json([
                'success' => false,
                'message' => 'Product details are not available for this result right now.',
            ], 404);
        }

        return response()->json(['success' => true, 'details' => $details]);
    }

    public function marketScopingSuggestions(Request $request): JsonResponse
    {
        // Strip unwanted characters before matching so "bond@@ paper!!" still suggests
        $q = $this->sanitizeSearchQuery((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $like     = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
        $officeId = $this->officeId();

        // Fast pass: substring matches on the office's item names + past searches
        $itemNames = DB::table('budget_proposal_items')
            ->join('budget_proposals', 'budget_proposals.id', '=', 'budget_proposal_items.budget_proposal_id')
            ->where('budget_proposals.office_id', $officeId)
            ->where('budget_proposal_items.name', 'like', $like)
            ->distinct()
            ->limit(5)
            ->pluck('budget_proposal_items.name');

        $pastQueries = DB::table('market_price_snapshots')
            ->where('query_used', 'like', $like)
            ->distinct()
            ->limit(5)
            ->pluck('query_used');

        $suggestions = $itemNames->map(fn ($n) => ['text' => $n, 'type' => 'item'])
            ->concat($pastQueries->map(fn ($s) => ['text' => $s, 'type' => 'search']))
            ->unique('text')
            ->take(8)
            ->values()
            ->all();

        // Fuzzy pass: when substring matching finds little, tolerate typos
        // ("bond papre" → "bond paper", "epso printer" → "epson printer")
        if (count($suggestions) < 8) {
            $taken     = collect($suggestions)->pluck('text')->map(fn ($t) => mb_strtolower($t))->all();
            $threshold = max(1, (int) floor(mb_strlen($q) * 0.34));

            $fuzzy = $this->suggestionPool($officeId)
                ->reject(fn ($c) => in_array(mb_strtolower($c['text']), $taken, true))
                ->map(fn ($c) => $c + ['score' => $this->fuzzyScore($q, $c['text'])])
                ->filter(fn ($c) => $c['score'] <= $threshold)
                ->sortBy([['score', 'asc'], fn ($a, $b) => mb_strlen($a['text']) <=> mb_strlen($b['text'])])
                ->take(8 - count($suggestions))
                ->map(fn ($c) => ['text' => $c['text'], 'type' => $c['type'], 'fuzzy' => true])
                ->values()
                ->all();

            $suggestions = array_merge($suggestions, $fuzzy);
        }

        return response()->json(['suggestions' => $suggestions]);
    }

    /** Bounded candidate pool for fuzzy matching: office item names + past searches. */
    private function suggestionPool(int $officeId): \Illuminate\Support\Collection
    {
        $items = DB::table('budget_proposal_items')
            ->join('budget_proposals', 'budget_proposals.id', '=', 'budget_proposal_items.budget_proposal_id')
            ->where('budget_proposals.office_id', $officeId)
            ->distinct()
            ->limit(300)
            ->pluck('budget_proposal_items.name')
            ->map(fn ($n) => ['text' => $n, 'type' => 'item']);

        $searches = DB::table('market_price_snapshots')
            ->distinct()
            ->limit(300)
            ->pluck('query_used')
            ->map(fn ($s) => ['text' => $s, 'type' => 'search']);

        return $items->concat($searches)->unique(fn ($c) => mb_strtolower($c['text']));
    }

    /**
     * Token-level typo distance: each query token is scored against the
     * closest candidate token (including its prefixes, so a partial word
     * like "epso" matches "epson" with distance 0). Lower is closer.
     */
    private function fuzzyScore(string $query, string $candidate): int
    {
        $qTokens = preg_split('/\s+/', mb_strtolower(trim($query)), -1, PREG_SPLIT_NO_EMPTY);
        $cTokens = preg_split('/\s+/', mb_strtolower(trim($candidate)), -1, PREG_SPLIT_NO_EMPTY);

        if (!$qTokens || !$cTokens) {
            return PHP_INT_MAX;
        }

        $total = 0;
        foreach ($qTokens as $qt) {
            $best = mb_strlen($qt);
            foreach ($cTokens as $ct) {
                if (str_starts_with($ct, $qt)) {
                    $best = 0;
                    break;
                }
                $dist = levenshtein($qt, $ct);
                // Also compare against a same-length prefix so long candidate
                // words don't get punished ("papre" vs "paper" in "paper A4")
                $prefix = mb_substr($ct, 0, min(mb_strlen($qt) + 1, mb_strlen($ct)));
                $dist   = min($dist, levenshtein($qt, $prefix));
                $best   = min($best, $dist);
            }
            $total += $best;
        }

        return $total;
    }

    private function sanitizeSearchQuery(string $raw): string
    {
        // Keep letters, numbers, spaces, and characters common in product specs
        $clean = preg_replace('/[^\p{L}\p{N}\s\-\+\.\"\/(),]/u', ' ', $raw);
        return trim(preg_replace('/\s+/', ' ', $clean));
    }

    private function didYouMean(string $query): ?string
    {
        $qLower   = mb_strtolower($query);
        $best     = null;
        $bestDist = PHP_INT_MAX;

        foreach ($this->suggestionPool($this->officeId()) as $candidate) {
            if (mb_strtolower($candidate['text']) === $qLower) {
                continue;
            }
            // Token-level scoring so long item names aren't punished
            $dist = $this->fuzzyScore($query, $candidate['text']);
            if ($dist < $bestDist) {
                $bestDist = $dist;
                $best     = $candidate['text'];
            }
        }

        // Only suggest when reasonably close (≤ 40% of query length)
        return ($best !== null && $bestDist <= max(2, (int) (mb_strlen($query) * 0.4))) ? $best : null;
    }

    public function attachToProposal(Request $request): JsonResponse
    {
        $request->validate([
            'query'       => 'required|string|max:300',
            'refs'        => 'required|array|min:3|max:3',
            'proposal_id' => 'nullable|integer|exists:budget_proposals,id',
            'item_id'     => 'nullable|integer|exists:budget_proposal_items,id',
        ]);

        $officeId = $this->officeId();
        $query    = trim($request->input('query'));
        $refs     = $request->input('refs');

        $draftProposal = null;
        if ($request->filled('proposal_id')) {
            $draftProposal = BudgetProposal::where('id', $request->input('proposal_id'))
                ->where('office_id', $officeId)
                ->whereIn('status', ['draft', 'returned'])
                ->first();
        }
        if (!$draftProposal) {
            $draftProposal = BudgetProposal::where('office_id', $officeId)
                ->whereIn('status', ['draft', 'returned'])
                ->latest()->first();
        }

        // Arriving via a specific item's "Add reference" link means we already know
        // exactly which item this is — use that directly instead of guessing from
        // the query text, which is fragile once several items share similar names.
        $existingItem = null;
        if ($draftProposal && $request->filled('item_id')) {
            $existingItem = $draftProposal->items()->where('id', $request->input('item_id'))->first();
        }
        if (!$existingItem && $draftProposal) {
            $existingItem = $this->findMatchingItem($draftProposal, $query);
        }

        if ($existingItem) {
            $this->saveRefsToItem($existingItem, $refs, $request);
            return response()->json([
                'success'     => true,
                'item_exists' => true,
                'item_name'   => $existingItem->name,
            ]);
        }

        $prices = collect($refs)->map(fn ($r) => (float) ($r['price'] ?? 0));

        return response()->json([
            'success'       => true,
            'item_exists'   => false,
            'query'         => $query,
            'lowest_price'  => $prices->min(),
            'average_price' => round($prices->avg(), 2),
            'refs_data'     => $refs,
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
            'proposal_id'       => 'nullable|integer|exists:budget_proposals,id',
        ]);

        $officeId = $this->officeId();

        $proposal = null;
        if (!empty($validated['proposal_id'])) {
            $proposal = BudgetProposal::where('id', $validated['proposal_id'])
                ->where('office_id', $officeId)
                ->whereIn('status', ['draft', 'returned'])
                ->first();
        }
        if (!$proposal) {
            // Fall back to the existing draft/returned proposal for any fiscal year
            $proposal = BudgetProposal::where('office_id', $officeId)
                ->whereIn('status', ['draft', 'returned'])
                ->orderByDesc('fiscal_year')
                ->first();
        }

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
                'title'                => 'FY ' . $year . ' PPMP',
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
            'redirect' => route('office-head.budget-proposal', ['proposal' => $proposal->id]),
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

    public function previewMps(Request $request): View
    {
        $officeId    = $this->officeId();
        $requestedId = (int) $request->query('proposal', 0);

        $withRelations = fn () => BudgetProposal::where('office_id', $officeId)->with([
            'items.marketReferences' => fn ($q) => $q->where('is_selected', true)->orderBy('price'),
            'items.sourceFiles',
            'marketPriceSurvey.submittedBy',
        ]);

        $proposal = $requestedId ? $withRelations()->where('id', $requestedId)->first() : null;
        if (!$proposal) {
            $proposal = $withRelations()->latest()->firstOrFail();
        }

        // An item can be supported by market-scoping references OR an uploaded source
        // file — only filtering on references left file-supported items out of the
        // Market Study entirely, even though they're a valid, already-supported way
        // to justify an item's price.
        $items = $proposal->items
            ->filter(fn ($i) => $i->marketReferences->isNotEmpty() || $i->sourceFiles->isNotEmpty())
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
        $officeId    = $this->officeId();
        $requestedId = (int) $request->input('proposal_id', 0);

        $proposal = $requestedId
            ? BudgetProposal::where('office_id', $officeId)->where('id', $requestedId)
                ->whereIn('status', ['draft', 'returned'])->first()
            : null;
        if (!$proposal) {
            $proposal = BudgetProposal::where('office_id', $officeId)
                ->whereIn('status', ['draft', 'returned'])
                ->latest()
                ->firstOrFail();
        }

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
                // Google Shopping refs only ever carry a Google intermediary link — resolve
                // to the actual merchant page once, here, so it's baked into the saved
                // reference and every later "View source" click (PPMP, Market Study
                // document, etc.) goes straight there without needing to re-resolve.
                $sourceUrl = $ref['url'] ?? '';
                if (!empty($ref['pageToken'])) {
                    $resolved  = (new MarketScopingService())->resolveDirectLink($ref['pageToken']);
                    $sourceUrl = $resolved ?: $sourceUrl;
                }

                MarketScopingReference::create([
                    'budget_proposal_item_id' => $item->id,
                    'created_by_user_id'     => $request->user()?->id,
                    'supplier_name'          => $ref['supplier'] ?? 'Unknown',
                    'source_type'            => $ref['source'] ?? 'Google Shopping',
                    'source_url'             => $sourceUrl,
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

    /**
     * 'returned' is shared by two different points in the workflow — Budget Office
     * returning it here for revision (status_from: submitted) vs. the Chancellor
     * returning it to Budget Office (status_from: endorsed), which the office head
     * can't touch until Budget Office decides to bounce it back further. Mirrors
     * PrismFinanceOfficeController::isActionableByFinance() for the other side.
     */
    private function isEditableByOfficeHead(BudgetProposal $proposal): bool
    {
        $lastReturn = $proposal->reviews()->where('action', 'return')->latest('reviewed_at')->first();

        // No review record found is treated as editable (safe default for older/legacy
        // data) — only a confirmed Chancellor-return locks the office head out.
        return $lastReturn?->status_from !== 'endorsed';
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
            'roleLabel'        => 'Office Head / Dean',
            'roleInitials'     => 'OH',
            'roleNavigation'   => \App\Support\PrismNav::roleNavigation(),
            'moduleNavLabel'   => 'Office Head / Dean pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',         'label' => 'Dashboard',         'href' => route('office-head.dashboard'),         'icon' => 'layout-dashboard'],
                ['slug' => 'for-my-signature',  'label' => 'For My Signature',  'href' => route('office-head.for-my-signature'),  'icon' => 'signature'],
                ['slug' => 'market-scoping',    'label' => 'Market Scoping',    'href' => route('office-head.market-scoping'),    'icon' => 'search'],
                ['slug' => 'budget-proposal',   'label' => 'PPMP',              'href' => route('office-head.budget-proposal'),   'icon' => 'file-plus'],
                ['slug' => 'my-proposals',      'label' => 'My PPMPs',          'href' => route('office-head.my-proposals'),      'icon' => 'folder'],
                ['slug' => 'purchase-requests', 'label' => 'Purchase Requests', 'href' => route('office-head.purchase-requests'), 'icon' => 'receipt'],
            ],
        ], $data);
    }
}
