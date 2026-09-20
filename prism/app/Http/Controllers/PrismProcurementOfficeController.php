<?php

namespace App\Http\Controllers;

use App\Models\AbstractOfCanvass;
use Smalot\PdfParser\Parser as PdfParser;
use App\Models\AocSignatureLog;
use App\Models\BudgetProposal;
use App\Models\BudgetProposalItem;
use App\Models\DocumentUpload;
use App\Models\DocumentValidation;
use App\Models\Office;
use App\Models\PoSignatureLog;
use App\Models\ProcurementStatusUpdate;
use App\Models\PrSignatureLog;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Services\DocumentValidationService;
use App\Services\ItemMatchingService;
use App\Services\NotificationService;
use App\Services\ProcurementModeService;
use App\Services\SignatoryActionService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PrismProcurementOfficeController extends Controller
{
    public function dashboard(Request $request): View
    {
        // Every 3-state bucket below (pending / in_progress / completed) is the
        // same signatory_stage + file_path read PurchaseRequest::signingStatusBucket()
        // already used — AOC and PO share those exact columns via HasSignatoryChain,
        // so the same rule is inlined for them rather than reading their raw
        // `status`/`signatory_stage` columns directly, which hold granular
        // values ('for_alobs', 'awaiting_delivery', ...) this 3-way summary
        // was never meant to filter on.
        $bucketOf = fn ($doc) => ($doc->signatory_stage === 'draft' && !$doc->file_path)
            ? 'pending'
            : ($doc->signatory_stage === 'fully_signed' ? 'completed' : 'in_progress');

        // ── Filters: Office + Fiscal Year, both optional, default to "all" ──────
        $officeId   = $request->integer('office') ?: null;
        $fiscalYear = $request->integer('year') ?: null;

        $officeOptions = Office::whereHas('purchaseRequests')
            ->orderBy('code')->get(['id', 'code', 'name']);
        $yearOptions = PurchaseRequest::whereNotNull('fiscal_year')
            ->distinct()->orderByDesc('fiscal_year')->pluck('fiscal_year');

        $prQuery = PurchaseRequest::with('office')
            ->when($officeId, fn ($q) => $q->where('office_id', $officeId))
            ->when($fiscalYear, fn ($q) => $q->where('fiscal_year', $fiscalYear));
        $allPrs = $prQuery->get();

        $aocQuery = AbstractOfCanvass::with('purchaseRequest.office')
            ->when($officeId, fn ($q) => $q->whereHas('purchaseRequest', fn ($q2) => $q2->where('office_id', $officeId)))
            ->when($fiscalYear, fn ($q) => $q->whereHas('purchaseRequest', fn ($q2) => $q2->where('fiscal_year', $fiscalYear)));
        $allAocs = $aocQuery->get();

        $poQuery = PurchaseOrder::with('abstractOfCanvass.purchaseRequest.office')
            ->when($officeId, fn ($q) => $q->whereHas('abstractOfCanvass.purchaseRequest', fn ($q2) => $q2->where('office_id', $officeId)))
            ->when($fiscalYear, fn ($q) => $q->whereHas('abstractOfCanvass.purchaseRequest', fn ($q2) => $q2->where('fiscal_year', $fiscalYear)));
        $allPos = $poQuery->get();

        $prBuckets  = $allPrs->countBy($bucketOf);
        $aocBuckets = $allAocs->countBy($bucketOf);
        $poBuckets  = $allPos->countBy($bucketOf);

        // No due-date/deadline column exists on any of the three documents —
        // "overdue" is the closest real, honest signal: still open (not fully
        // signed) N+ days after the PR's own submission date (the one date
        // that anchors the whole PR → AOC → PO chain).
        $overdueThresholdDays = 30;
        $isOverdue = fn ($doc, $bucket) => $bucket !== 'completed'
            && $doc->submitted_at
            && $doc->submitted_at->diffInDays(now()) > $overdueThresholdDays;
        $overdueCount = $allPrs->filter(fn ($pr) => $isOverdue($pr, $bucketOf($pr)))->count()
            + $allAocs->filter(fn ($aoc) => $isOverdue($aoc->purchaseRequest, $bucketOf($aoc)))->count()
            + $allPos->filter(fn ($po) => $isOverdue($po->abstractOfCanvass?->purchaseRequest, $bucketOf($po)))->count();

        // ── Per-office volume — PR/AOC/PO counts together, one grouped chart
        //    instead of three, so it can't silently stay PR-only again. ───────
        $officeVolume = $allPrs->countBy(fn ($pr) => $pr->office?->code ?? '—');
        $aocOfficeVolume = $allAocs->countBy(fn ($aoc) => $aoc->purchaseRequest?->office?->code ?? '—');
        $poOfficeVolume  = $allPos->countBy(fn ($po) => $po->abstractOfCanvass?->purchaseRequest?->office?->code ?? '—');
        $officeVolumeChart = collect($officeVolume->keys())
            ->merge($aocOfficeVolume->keys())->merge($poOfficeVolume->keys())
            ->unique()->sort()->values()
            ->map(fn ($code) => [
                'office' => $code,
                'pr'     => $officeVolume[$code] ?? 0,
                'aoc'    => $aocOfficeVolume[$code] ?? 0,
                'po'     => $poOfficeVolume[$code] ?? 0,
            ])
            ->sortByDesc(fn ($r) => $r['pr'] + $r['aoc'] + $r['po'])
            ->values()->all();

        $officeStatusGroups = Office::whereHas('purchaseRequests', fn ($q) =>
                $q->when($officeId, fn ($q2) => $q2->where('office_id', $officeId))
                  ->when($fiscalYear, fn ($q2) => $q2->where('fiscal_year', $fiscalYear))
            )
            ->when($officeId, fn ($q) => $q->where('id', $officeId))
            ->with(['purchaseRequests' => fn ($q) =>
                $q->when($fiscalYear, fn ($q2) => $q2->where('fiscal_year', $fiscalYear))
            ])
            ->get()
            ->map(function ($office) use ($bucketOf) {
                $counts = $office->purchaseRequests->countBy($bucketOf);
                return [
                    'office'     => $office->code,
                    'completed'  => $counts['completed'] ?? 0,
                    'inProgress' => $counts['in_progress'] ?? 0,
                    'pending'    => $counts['pending'] ?? 0,
                ];
            })
            ->filter(fn ($g) => $g['completed'] + $g['inProgress'] + $g['pending'] > 0)
            ->values()
            ->all();

        // "Urgent" = still open and has been waiting the longest since the PR
        // was submitted — merged across all three document types so this is
        // an actual worklist, not just a PR-only view of it.
        $urgentDocs = collect()
            ->concat($allPrs->map(fn ($pr) => ['doc' => $pr, 'docType' => 'PR', 'number' => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT), 'title' => $pr->title, 'office' => $pr->office, 'anchor' => $pr->submitted_at, 'bucket' => $bucketOf($pr)]))
            ->concat($allAocs->map(fn ($aoc) => ['doc' => $aoc, 'docType' => 'AOC', 'number' => $aoc->code, 'title' => $aoc->purchaseRequest?->title, 'office' => $aoc->purchaseRequest?->office, 'anchor' => $aoc->purchaseRequest?->submitted_at, 'bucket' => $bucketOf($aoc)]))
            ->concat($allPos->map(fn ($po) => ['doc' => $po, 'docType' => 'PO', 'number' => $po->po_number, 'title' => $po->abstractOfCanvass?->purchaseRequest?->title, 'office' => $po->abstractOfCanvass?->purchaseRequest?->office, 'anchor' => $po->abstractOfCanvass?->purchaseRequest?->submitted_at, 'bucket' => $bucketOf($po)]))
            ->filter(fn ($r) => $r['bucket'] !== 'completed' && $r['anchor'])
            ->sortBy('anchor')
            ->take(8)
            ->map(fn ($r) => [
                'docType'       => $r['docType'],
                'office'        => $r['office']?->code ?? '—',
                'number'        => $r['number'],
                'item'          => $r['title'] ?? '—',
                'daysPending'   => (int) $r['anchor']->diffInDays(now()),
                'status'        => ucfirst(str_replace('_', ' ', $r['bucket'])),
            ])
            ->values()
            ->all();

        return view('prism.procurement-office.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Procurement Office Dashboard',
            'filters'   => [
                'officeOptions' => $officeOptions,
                'yearOptions'   => $yearOptions,
                'selectedOffice' => $officeId,
                'selectedYear'   => $fiscalYear,
            ],
            'summary'   => [
                'totalPrs'  => $allPrs->count(),
                'totalAocs' => $allAocs->count(),
                'totalPos'  => $allPos->count(),
                'overdueCount'         => $overdueCount,
                'overdueThresholdDays' => $overdueThresholdDays,
            ],
            'officeStatusGroups' => $officeStatusGroups,
            'urgentDocs'         => $urgentDocs,
            // One chart, three document types — a stacked bar reads their
            // status mix at a glance without resorting to three separate pies.
            'docStatusChart' => [
                ['doc' => 'PR',  'pending' => $prBuckets['pending'] ?? 0,  'in_progress' => $prBuckets['in_progress'] ?? 0,  'completed' => $prBuckets['completed'] ?? 0],
                ['doc' => 'AOC', 'pending' => $aocBuckets['pending'] ?? 0, 'in_progress' => $aocBuckets['in_progress'] ?? 0, 'completed' => $aocBuckets['completed'] ?? 0],
                ['doc' => 'PO',  'pending' => $poBuckets['pending'] ?? 0,  'in_progress' => $poBuckets['in_progress'] ?? 0,  'completed' => $poBuckets['completed'] ?? 0],
            ],
            // Pie/donut is legitimate here: 3 categories, and the question it
            // answers ("what share of the pipeline is PR vs AOC vs PO?") is
            // genuinely part-to-whole, not a precise-comparison ask.
            'docMixChart' => [
                'pr'  => $allPrs->count(),
                'aoc' => $allAocs->count(),
                'po'  => $allPos->count(),
            ],
            'officeVolumeChart' => $officeVolumeChart,
        ]));
    }

    public function purchaseRequestManagement(): View
    {
        $prs = $this->purchaseRequestManagementRows();

        return view('prism.procurement-office.purchase-request-management', $this->withCommon('purchase-request-management', [
            'pageTitle'        => 'Purchase Request Management',
            'purchaseRequests' => $prs,
            'stageMeta'        => PurchaseRequest::signatoryStageMeta(),
            'trackingStageOptions' => PurchaseRequest::allTrackingStageOptions(),
            'offices'          => Office::whereHas('purchaseRequests')->select('id', 'code', 'name')->orderBy('code')->get()->toArray(),
            'approvedPpmps'    => $this->approvedPpmpsForUpload(),
            'extractPrUrl'     => route('procurement-office.purchase-request-management.extract-pr'),
            'createPrUrl'      => route('procurement-office.purchase-request-management.create-pr'),
        ]));
    }

    public function purchaseRequestManagementRefresh(): JsonResponse
    {
        return response()->json(['purchaseRequests' => $this->purchaseRequestManagementRows()]);
    }

    private function purchaseRequestManagementRows(): array
    {
        $prs = PurchaseRequest::with(['office', 'budgetProposal', 'items', 'statusUpdates' => fn ($q) => $q->latest(), 'signatureLogs.signedBy', 'signatureLogs.attachments', 'abstractOfCanvass.purchaseOrder'])
            ->latest()
            ->get();

        // One table row per PPMP group, not one per PR — several PRs against
        // the same PPMP are usually uploaded at different times (see Upload
        // Purchase Request), and listing every one of them as its own row
        // would clutter the queue with near-duplicate entries. $prs is
        // already newest-first, so the first PR encountered per
        // budget_proposal_id here is the most recent — that's the one shown;
        // the rest stay reachable via "Next PR from this PPMP" in the detail
        // panel (updatePpmpNav() client-side) instead.
        $siblingCounts = $prs->filter(fn ($pr) => $pr->budget_proposal_id)
            ->groupBy('budget_proposal_id')
            ->map->count();
        $seenProposalIds = [];

        return $prs
            ->map(function ($pr) use (&$seenProposalIds, $siblingCounts) {
                $isTableRow = true;
                if ($pr->budget_proposal_id) {
                    $isTableRow = !in_array($pr->budget_proposal_id, $seenProposalIds, true);
                    if ($isTableRow) {
                        $seenProposalIds[] = $pr->budget_proposal_id;
                    }
                }

                return [
                'id'             => $pr->id,
                'office'         => $pr->office?->code ?? $pr->office?->name ?? '—',
                'prNumber'       => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
                'item'           => $pr->title,
                'itemCount'      => $pr->items->count(),
                'items'          => $pr->items->map(fn ($item) => [
                    'name'      => $item->name,
                    'quantity'  => (int) $item->quantity,
                    'unit'      => $item->unit,
                    'unitCost'  => (float) $item->estimated_unit_cost,
                    'totalCost' => (float) $item->estimated_total_cost,
                ])->all(),
                'isTableRow'     => $isTableRow,
                'siblingCount'   => $pr->budget_proposal_id ? ($siblingCounts[$pr->budget_proposal_id] ?? 1) : 1,
                // Lets the detail panel offer a "Next PR from this PPMP" nav —
                // PRs against the same PPMP are usually uploaded at different
                // times (see Upload Purchase Request), not all at once.
                'budgetProposalId'   => $pr->budget_proposal_id,
                'budgetProposalCode' => $pr->budgetProposal?->code,
                'dateSubmitted'  => $pr->submitted_at?->format('M d, Y') ?? $pr->created_at->format('M d, Y'),
                'currentStatus'  => $pr->status,
                'signatoryStage'   => $pr->signatory_stage,
                'signatoryLabel'   => $pr->signatory_label,
                'statusBucket'     => $pr->signingStatusBucket(),
                'createdAt'        => $pr->created_at->toIso8601String(),
                'nextStage'        => $pr->nextSignatoryStage(),
                'trackingStatus'      => $pr->effectiveTrackingStatus(),
                'trackingStatusAuto'  => $pr->currentTrackingStage(),
                'trackingStatusUrl'   => route('procurement-office.purchase-request.update-tracking-status', $pr->id),
                'pdfFile'          => $pr->file_path,
                'remarks'          => $pr->remarks ?? '—',
                'ocr'            => $pr->extracted_fields_json ?? [],
                'activityLog'    => $pr->statusUpdates->map(fn ($u) => [
                    'timestamp'    => $u->created_at->format('M d, Y g:i A'),
                    'timestampRaw' => $u->created_at->toIso8601String(),
                    'status'    => ucfirst(str_replace('_', ' ', $u->status)),
                    'remarks'   => $u->remarks ?? '—',
                ])->all(),
                'signatureLogs'  => $pr->signatureLogs->map(fn ($l) => [
                    'display'   => $pr->describeSignatureLog($l),
                    'action'    => $l->action,
                    'by'        => $l->signedBy?->name ?? '—',
                    'at'        => $l->signed_at?->format('M d, Y g:i A') ?? '—',
                    'atRaw'     => $l->signed_at?->toIso8601String(),
                    'remarks'   => $l->remarks ?? '',
                    'photoUrl'      => $l->blurred_photo_path ? \Illuminate\Support\Facades\Storage::url($l->blurred_photo_path) : null,
                    'photoStatus'   => $l->detection_status,
                    'attachments'   => $l->attachments->map(fn ($a) => [
                        'filename' => $a->original_filename,
                        'isImage'  => str_starts_with($a->mime_type ?? '', 'image/'),
                        'url'      => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'signature-attachment.show', now()->addDay(), ['id' => $a->id]
                        ),
                    ])->all(),
                    'reprocessUrl'  => in_array($l->detection_status, ['pending', 'failed'], true)
                        ? route('procurement-office.signature-photo.reprocess', ['pr', $l->id])
                        : null,
                ])->all(),
                'stageMeta'     => $pr->resolvedStageMeta(),
                'thirdSigner'   => $pr->third_signer,
                'advanceUrl'    => route('procurement-office.purchase-request.advance', $pr->id),
                'returnUrl'     => route('procurement-office.purchase-request.return', $pr->id),
                'updateUrl'     => route('procurement-office.purchase-request.update-status', $pr->id),
                'uploadUrl'         => route('procurement-office.purchase-request.upload', $pr->id),
                // Only offered when the PR is linked to a PPMP — re-scanning
                // items needs something to validate them against. Legacy PRs
                // without one fall back to the plain file-swap above.
                'reuploadUrl'       => $pr->budget_proposal_id ? route('procurement-office.purchase-request.reupload', $pr->id) : null,
                ];
            })
            ->all();
    }

    public function advancePrStage(Request $request, PurchaseRequest $pr, SignatoryActionService $signatory): JsonResponse
    {
        if ($pr->nextSignatoryStage() === 'at_third_sign') {
            $request->validate(['third_signer' => 'required|in:accounting,vice_chancellor']);
        }

        $result = $signatory->advance($pr, $request->input('remarks'), $request->input('third_signer'));

        return response()->json($result, $result['status'] ?? 200);
    }

    public function returnPr(Request $request, PurchaseRequest $pr, SignatoryActionService $signatory): JsonResponse
    {
        $request->validate(['remarks' => 'required|string|max:1000']);

        return response()->json($signatory->returnOneStep($pr, $request->input('remarks')));
    }

    public function updatePrStatus(Request $request, PurchaseRequest $pr): JsonResponse
    {
        $request->validate([
            'status'  => 'required|in:new,approved_pr_received,forwarded_to_bac,canvassing,abstract_of_canvass_made,for_po,po_made,po_confirmed,for_alobs,forwarded_to_rgo,forwarded_to_end_user,for_reimbursement,for_consolidation,pr_denied,cancelled,cancelled_system_error',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $pr->update(['status' => $request->input('status'), 'remarks' => $request->input('remarks')]);

        ProcurementStatusUpdate::create([
            'purchase_request_id' => $pr->id,
            'updated_by_user_id'  => auth()->user()?->id,
            'status'              => $request->input('status'),
            'remarks'             => $request->input('remarks', ''),
        ]);

        NotificationService::prStatusUpdated($pr);

        return response()->json(['success' => true]);
    }

    /**
     * Manually pin the unified Tracking Status (PR→AOC→PO→Payment). Passing an
     * empty value clears the pin and reverts to the auto-computed status — the
     * pin itself is also auto-cleared the next time any real progress happens
     * (see SignatoryActionService::clearTrackingOverride and the PO delivery/
     * payment endpoints).
     */
    public function updateTrackingStatus(Request $request, PurchaseRequest $pr): JsonResponse
    {
        $request->validate(['trackingStatus' => 'nullable|string|max:50']);

        $value = $request->input('trackingStatus') ?: null;

        if ($value !== null) {
            $valid = collect(PurchaseRequest::allTrackingStageOptions())->pluck('key')->all();
            if (!in_array($value, $valid, true)) {
                return response()->json(['error' => 'Invalid tracking status.'], 422);
            }
        }

        $pr->update([
            'tracking_status_override'              => $value,
            'tracking_status_overridden_by_user_id'  => $value !== null ? auth()->id() : null,
            'tracking_status_overridden_at'          => $value !== null ? now() : null,
        ]);

        return response()->json([
            'success'        => true,
            'trackingStatus' => $pr->fresh()->effectiveTrackingStatus(),
        ]);
    }

    // ── Annual Procurement Plan (moved from Budget Office) ──────────────────

    public function annualProcurementPlan(): View
    {
        $items = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->get()
            // Most recently approved first; endorsed-but-not-yet-approved items
            // fall back to their proposal's last update so they still sort by recency.
            ->sortByDesc(fn ($item) => $item->budgetProposal?->approved_at ?? $item->budgetProposal?->updated_at)
            ->values();

        $officeIds = $items->pluck('budgetProposal.office_id')->filter()->unique()->values();
        $prItemMatches = $this->matchPrItemsByOfficeAndName($officeIds);

        // Pre-PR stages come from the item's own PPMP (Budget Proposal) journey —
        // this page only ever shows items whose proposal is already 'endorsed' or
        // 'approved', so that distinction is real progress, not a guess.
        $trackingOptions = collect([
                ['key' => 'bp:endorsed', 'label' => 'PPMP Endorsed — Awaiting Chancellor Approval'],
                ['key' => 'bp:approved', 'label' => 'PPMP Approved — Not Yet Requested'],
            ])
            ->concat(PurchaseRequest::allTrackingStageOptions())
            ->values()
            ->all();

        $mapped = $items->map(function ($item) use ($prItemMatches, $trackingOptions) {
            $abc             = (float) $item->estimated_total_cost;
            $recommendedMode = ProcurementModeService::recommend($abc);
            $officeId        = $item->budgetProposal?->office_id;
            $matchedPr       = $prItemMatches->get($officeId . '|' . strtolower(trim($item->name)))?->purchaseRequest;

            $trackingStatusAuto = $matchedPr
                ? $matchedPr->effectiveTrackingStatus()
                : ($item->budgetProposal?->status === 'approved'
                    ? ['key' => 'bp:approved', 'label' => 'PPMP Approved — Not Yet Requested']
                    : ['key' => 'bp:endorsed', 'label' => 'PPMP Endorsed — Awaiting Chancellor Approval']);

            if ($item->tracking_status_override) {
                $label = collect($trackingOptions)->firstWhere('key', $item->tracking_status_override)['label'] ?? $item->tracking_status_override;
                $trackingStatus = ['key' => $item->tracking_status_override, 'label' => $label, 'override' => true];
            } else {
                $trackingStatus = $trackingStatusAuto + ['override' => false];
            }

            return [
                'itemId'          => $item->id,
                'office'          => $item->budgetProposal?->office?->code ?? "—",
                'fiscalYear'      => $item->budgetProposal?->fiscal_year,
                'item'            => $item->name,
                'unit'            => $item->unit,
                'quantity'        => (int) $item->quantity,
                'abcAmount'       => $abc,
                'targetQuarter'   => $item->target_quarter ?? 'Q1',
                'recommendedMode' => $recommendedMode,
                'rationale'       => ProcurementModeService::rationale($abc),
                'procurementMode' => $item->procurement_mode ?? $recommendedMode,
                'isOverridden'    => (bool) $item->is_overridden,
                'overrideReason'  => $item->override_reason ?? '',
                'saveUrl'         => route('procurement-office.annual-procurement-plan.save-mode', $item->id),
                'sourceOfFund'         => $item->source_of_fund ?: '—',
                'procurementStartDate' => $item->procurement_start_date?->format('Y-m-d'),
                'dateNeeded'           => $item->date_needed?->format('Y-m-d'),
                'datesSaveUrl'         => route('procurement-office.annual-procurement-plan.update-dates', $item->id),
                'trackingStatus'     => $trackingStatus,
                'trackingStatusAuto' => $trackingStatusAuto,
                'trackingStatusUrl'  => route('procurement-office.annual-procurement-plan.update-tracking-status', $item->id),
            ];
        })->all();

        $fiscalYears = collect($mapped)->pluck('fiscalYear')->filter()->unique()->sort()->values();

        return view('prism.procurement-office.annual-procurement-plan', $this->withCommon('annual-procurement-plan', [
            'pageTitle'        => 'Annual Procurement Plan',
            'appItems'         => $mapped,
            'offices'          => collect($mapped)->pluck('office')->unique()->values()->all(),
            'fiscalYears'      => $fiscalYears->all(),
            'quarters'         => ['Q1', 'Q2', 'Q3', 'Q4'],
            'procurementModes' => ProcurementModeService::MODES,
            'trackingStageOptions' => $trackingOptions,
        ]));
    }

    /**
     * Approved PPMPs that still have at least one item without a PR, sorted
     * most-recently-approved first — the Upload Purchase Request picker's
     * row list. Only 'approved' (Chancellor-signed) PPMPs are eligible; an
     * endorsed-but-not-yet-approved one isn't something Procurement should
     * be raising a PR against yet.
     *
     * "Already has a PR" is checked two ways: the real budget_proposal_id
     * link (what every new upload sets) and, for PRs that predate that
     * column, the same office+item-name match used everywhere else in this
     * app (matchPrItemsByOfficeAndName) — so already-covered legacy items
     * don't show up as "missing" just because they were never linked.
     */
    private function approvedPpmpsForUpload(): array
    {
        $proposals = BudgetProposal::with(['office', 'items', 'purchaseRequests.items'])
            ->where('status', 'approved')
            ->orderByDesc('approved_at')
            ->get();

        $officeIds = $proposals->pluck('office_id')->filter()->unique()->values();
        $legacyMatches = $this->matchPrItemsByOfficeAndName($officeIds);

        return $proposals->map(function ($proposal) use ($legacyMatches) {
            $coveredNames = $proposal->purchaseRequests
                ->flatMap->items
                ->map(fn ($i) => strtolower(trim($i->name)))
                ->filter()
                ->unique();

            $missing = $proposal->items
                ->filter(function ($item) use ($legacyMatches, $proposal, $coveredNames) {
                    $name = strtolower(trim($item->name));
                    // A PR already linked to this exact PPMP (budget_proposal_id)
                    // still won't necessarily spell an item's name identically —
                    // the PPMP might say "monitor" while the real PR document
                    // (typed or read off the upload) says "Monitor, 24-inch LED
                    // Full HD Display". A substring match either direction
                    // covers that without needing the human to explicitly link
                    // each PR row back to a specific PPMP item.
                    if ($coveredNames->contains(fn ($n) => str_contains($n, $name) || str_contains($name, $n))) {
                        return false;
                    }
                    // Same office + same item name isn't enough on its own —
                    // a generic name like "laptop" or "monitor" can coincide
                    // across completely unrelated PPMP cycles years apart.
                    // Requiring the matched legacy PR's fiscal year to equal
                    // this proposal's is what keeps that from wrongly marking
                    // a brand-new PPMP's item as "already has a PR" just
                    // because some old, unrelated PR happened to share a name.
                    $legacyMatch = $legacyMatches->get($proposal->office_id . '|' . $name);
                    if (!$legacyMatch) {
                        return true;
                    }
                    return $legacyMatch->purchaseRequest?->fiscal_year !== $proposal->fiscal_year;
                })
                ->map(fn ($item) => [
                    'itemId'    => $item->id,
                    'name'      => $item->name,
                    'quantity'  => (int) $item->quantity,
                    'abcAmount' => (float) $item->estimated_total_cost,
                    'unit'      => $item->unit,
                ])
                ->values()
                ->all();

            return [
                'id'           => $proposal->id,
                'code'         => $proposal->code,
                'officeId'     => $proposal->office_id,
                'officeCode'   => $proposal->office?->code ?? '—',
                'officeName'   => $proposal->office?->name ?? '—',
                'title'        => $proposal->title,
                'fiscalYear'   => $proposal->fiscal_year,
                'approvedAt'   => $proposal->approved_at?->format('M d, Y') ?? '—',
                'totalItems'   => $proposal->items->count(),
                'missingItems' => $missing,
            ];
        })
        ->filter(fn ($p) => count($p['missingItems']) > 0)
        ->values()
        ->all();
    }

    public function saveProcurementMode(Request $request, BudgetProposalItem $item): JsonResponse
    {
        $validated = $request->validate([
            'procurement_mode' => 'required|string|in:' . implode(',', ProcurementModeService::MODES),
            'override_reason'  => 'nullable|string|max:1000',
        ]);

        $recommended  = ProcurementModeService::recommend((float) $item->estimated_total_cost);
        $isOverridden = $validated['procurement_mode'] !== $recommended;

        if ($isOverridden && empty(trim($validated['override_reason'] ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'A reason is required when overriding the system recommendation.',
            ], 422);
        }

        $item->update([
            'recommended_mode' => $recommended,
            'procurement_mode' => $validated['procurement_mode'],
            'is_overridden'    => $isOverridden,
            'override_reason'  => $isOverridden ? trim($validated['override_reason']) : null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Manually pin an APP item's Tracking Status. Passing an empty value clears
     * the pin and reverts to the auto-computed value (best-effort matched PR's
     * tracking status, or the item's own PPMP endorsed/approved stage when no
     * PR has been matched yet).
     */
    public function updateAppItemTrackingStatus(Request $request, BudgetProposalItem $item): JsonResponse
    {
        $request->validate(['trackingStatus' => 'nullable|string|max:50']);

        $value = $request->input('trackingStatus') ?: null;

        if ($value !== null) {
            $valid = collect([['key' => 'bp:endorsed'], ['key' => 'bp:approved']])
                ->concat(PurchaseRequest::allTrackingStageOptions())
                ->pluck('key')
                ->all();
            if (!in_array($value, $valid, true)) {
                return response()->json(['error' => 'Invalid tracking status.'], 422);
            }
        }

        $item->update([
            'tracking_status_override'             => $value,
            'tracking_status_overridden_by_user_id' => $value !== null ? auth()->id() : null,
            'tracking_status_overridden_at'         => $value !== null ? now() : null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Start of Procurement Activity / Date Needed — the two APP Item Matrix
     * fields Procurement sets by hand (everything else on that row is
     * auto-fetched from the item's own PPMP data).
     */
    public function updateAppItemDates(Request $request, BudgetProposalItem $item): JsonResponse
    {
        $validated = $request->validate([
            'procurement_start_date' => 'nullable|date',
            'date_needed'            => 'nullable|date|after_or_equal:procurement_start_date',
        ]);

        $item->update($validated);

        return response()->json([
            'success'              => true,
            'procurementStartDate' => $item->procurement_start_date?->format('Y-m-d'),
            'dateNeeded'           => $item->date_needed?->format('Y-m-d'),
        ]);
    }

    // ── Manual PR creation from an approved PPMP's still-uncovered items ─────
    // Replaces the old auto-generated-on-approval PR: Procurement now uploads
    // the real PR document themselves, for however many items they're ready
    // to act on right now — the rest of a PPMP simply waits for a later upload.

    /**
     * Best-effort read of a Purchase Request PDF — shown for review, never trusted blindly.
     *
     * When the caller says which PPMP (and optionally which quarter) this PR is
     * being raised against, the extracted items are also checked against that
     * PPMP right here, so the modal can show a per-item verdict before anything
     * is created. Nothing is persisted on this path.
     */
    public function extractPurchaseRequestFields(Request $request, DocumentValidationService $validator): JsonResponse
    {
        $request->validate([
            'file'               => 'required|file|mimes:pdf|max:10240',
            'budget_proposal_id' => 'nullable|integer|exists:budget_proposals,id',
            'quarter'            => 'nullable|in:Q1,Q2,Q3,Q4',
            'exclude_pr_id'      => 'nullable|integer|exists:purchase_requests,id',
        ]);

        $text   = $this->readPdfText($request->file('file'));
        $parsed = $this->parsePurchaseRequestForm($text);

        $payload = ['success' => true] + $parsed;

        if ($request->filled('budget_proposal_id')) {
            $proposal = BudgetProposal::with('office')->find($request->input('budget_proposal_id'));
            if ($proposal) {
                $payload['validation'] = $validator->validatePrAgainstPpmp(
                    $parsed['items'],
                    $proposal,
                    $request->input('quarter'),
                    $parsed['parseError'] ?? null,
                    [
                        'officeCode'  => $parsed['officeCode'] ?? null,
                        'fiscalYear'  => $parsed['fiscalYear'] ?? null,
                        'totalCost'   => $parsed['totalCost'] ?? null,
                        'prNumber'    => $parsed['prNumber'] ?? null,
                        'excludePrId' => $request->input('exclude_pr_id'),
                    ]
                );
            }
        }

        return response()->json($payload);
    }

    /**
     * Parses the standardized BatStateU-FO-PRO-02 Purchase Request Form.
     * Tested against a real filled-out sample: extraction of the item table
     * (6 rows, correct qty/unit-cost/total-cost each) and header fields
     * (PR No, Department/Office, Date) all matched exactly.
     *
     * The item table is read as a repeating pattern (unit-of-measure word,
     * description, qty, ₱unit cost, ₱total cost) scanned across the whole
     * table section — not a per-line split — because smalot/pdfparser's
     * getText() glues adjacent table cells together inconsistently (no space
     * between a short description and the next column, a literal tab
     * elsewhere, a real line break when a description wraps to 2 lines).
     * Scanning for the pattern instead of relying on line boundaries is also
     * what makes this naturally tolerant of a table that runs onto a 2nd
     * page — the row pattern doesn't care which page its text came from,
     * only that it sits between the column header and the grand-total line.
     */
    private function parsePurchaseRequestForm(string $text): array
    {
        // Real forms wrap label text itself mid-phrase depending on column
        // width ("Name of\nProject:", "TOTAL\nCOST") — not just the value
        // after a label. Collapsing every run of whitespace (space, tab,
        // newline) to a single space up front, before anything else runs,
        // makes every literal-label match below robust to that by
        // construction, on top of (not instead of) labelPattern()'s \s+ and
        // the \s+ already used around "UNIT COST"/"TOTAL COST" — belt and
        // suspenders, since a form export nobody has tested against yet
        // could still wrap some other label the same way.
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        $prNumber = null;
        if (preg_match('/PR\s*No:?\s*([A-Za-z0-9\-\/]+)/u', $text, $m)) {
            $prNumber = $m[1];
        }

        $office = $this->parseLabeledBlock($text, 'Department /Office:', 'Project Location:');
        $officeCode = null;
        if ($office && preg_match('/\(([A-Z]{2,10})\)\s*$/u', $office, $m)) {
            $officeCode = $m[1];
        }

        $projectName = $this->parseLabeledBlock($text, 'Name of Project:', 'Department /Office:');

        $date = null;
        if (preg_match('/(?<!Effectivity )Date:\s*([A-Za-z]+ \d{1,2},\s*\d{4})/u', $text, $m)) {
            $date = $m[1];
        }

        // No standalone "Fiscal Year" field exists on this form — the PR's own
        // Date line is the closest real, honest signal for which fiscal year
        // it was actually raised in.
        $fiscalYear = null;
        if ($date && preg_match('/(\d{4})\s*$/', $date, $ym)) {
            $fiscalYear = (int) $ym[1];
        }

        // "TOTAL COST" (and "UNIT COST" below) use \s+ between the two words,
        // not a literal space — a narrow grand-total cell commonly wraps this
        // exact label to "TOTAL\nCOST" in the extracted text, same reason
        // labelPattern() exists for the labels above.
        $totalCost = null;
        if (preg_match('/TOTAL\s+COST\s*Php\s*([\d,]+\.\d{2})/u', $text, $m)) {
            $totalCost = (float) str_replace(',', '', $m[1]);
        }

        $items = [];
        if (preg_match('/QTY\s*UNIT\s+COST\s*TOTAL\s+COST(.*?)TOTAL\s+COST\s*Php/su', $text, $tableMatch)) {
            // Per-item costs are usually prefixed with "₱", but some forms
            // (or this same form filled via different software) spell it out
            // as "Php"/"PHP" instead — same as this form's own grand-total
            // row always does. Accept either.
            // Rows are found by their TAIL — a quantity followed by two currency
            // amounts — rather than by their leading unit of measure. The tail is
            // the only part of a row whose shape ordinary prose never produces,
            // and anchoring on it is what makes long multi-page tables safe.
            //
            // Leading on the unit instead (the previous approach) meant matching
            // against a fixed vocabulary, because pdfparser frequently glues the
            // unit straight onto the description with no space at all — real
            // output from the BatStateU form looks like "unitAir Conditioner,
            // Split Type Inverter, 1.5HP, with installation kit2 ₱36,998.00".
            // Any unit outside that vocabulary silently dropped its whole row,
            // and "pad" was missing from it — a unit these very forms use
            // ("Manila Paper Pad"). For content validation that is not a
            // cosmetic gap: an item the parser never sees is an item nobody can
            // check against the PPMP, so a smuggled line item would sail through
            // simply by carrying an unusual unit.
            $currency = '(?:₱|Php|PHP)';
            $matched  = preg_match_all(
                '/(\d+(?:\.\d+)?)\s*' . $currency . '\s*([\d,]+\.\d{2})\s*' . $currency . '\s*([\d,]+\.\d{2})/u',
                $tableMatch[1],
                $rows,
                PREG_SET_ORDER | PREG_OFFSET_CAPTURE
            );

            // A long, many-page table can exhaust PCRE's backtrack limit, and
            // preg_* reports that by returning false rather than throwing. Left
            // unchecked it reads as "this PR has no items" — the most dangerous
            // possible misreading here — so surface it as a parse error instead
            // and let the caller treat the document as unreadable.
            if ($matched === false) {
                return [
                    'prNumber'    => $prNumber,
                    'office'      => $office,
                    'officeCode'  => $officeCode,
                    'projectName' => $projectName,
                    'date'        => $date,
                    'fiscalYear'  => $fiscalYear,
                    'totalCost'   => $totalCost,
                    'items'       => [],
                    'parseError'  => 'The item table was too large or complex to read reliably (PCRE error ' . preg_last_error() . ').',
                ];
            }

            // Known units are still used — not to find rows, but to split a glued
            // "unitAir Conditioner" back into its unit and its description.
            $uom = $this->unitVocabularyPattern();

            $cursor = 0;
            foreach ($rows as $row) {
                // Everything between the previous row's tail and this one's is
                // this row's unit + description, however many lines it spans.
                [$qtyRaw, $qtyOffset] = $row[1];
                $head   = trim(substr($tableMatch[1], $cursor, $qtyOffset - $cursor));
                $cursor = $row[3][1] + strlen($row[3][0]);

                // On a multi-page PR the whole form header is re-printed between
                // two rows, so it lands inside the next row's head. Cut everything
                // up to and including the last repeated column-header line rather
                // than discarding the head wholesale — the first item on every
                // page after the first is a real item and must survive.
                if (preg_match('/^.*QTY\s*UNIT\s+COST\s*TOTAL\s+COST(.*)$/su', $head, $hm)) {
                    $head = trim($hm[1]);
                }

                if ($head === '') {
                    continue;
                }

                if (preg_match('/^(' . $uom . ')\s*(.+)$/isu', $head, $split)) {
                    $unit = $split[1];
                    $name = $split[2];
                } elseif (preg_match('/^(\S{1,15})\s+(.+)$/su', $head, $split)) {
                    $unit = $split[1];   // unknown but space-separated unit
                    $name = $split[2];
                } else {
                    $unit = '';
                    $name = $head;
                }

                $items[] = [
                    'name'      => trim(preg_replace('/\s+/', ' ', $name)),
                    'unit'      => trim($unit),
                    'quantity'  => (float) $qtyRaw,
                    'unitCost'  => (float) str_replace(',', '', $row[2][0]),
                    'totalCost' => (float) str_replace(',', '', $row[3][0]),
                ];
            }
        }

        return [
            'prNumber'    => $prNumber,
            'office'      => $office,
            'officeCode'  => $officeCode,
            'projectName' => $projectName,
            'date'        => $date,
            'fiscalYear'  => $fiscalYear,
            'totalCost'   => $totalCost,
            'items'       => $items,
        ];
    }

    /** Shared between parsePurchaseRequestForm() and parseQuotationItems() — both split a glued "unitSomething" back into its unit and its description off the same vocabulary. */
    private function unitVocabularyPattern(): string
    {
        return 'units?|reams?|boxe?s?|packs?|sets?|lots?|rolls?|kgs?|liters?|gallons?|pcs?|pieces?|bottles?|cans?|dozens?|pairs?|bundles?|sacks?|sheets?|tubes?|pads?|trays?|cartons?|ctns?|jars?|tins?|bags?|drums?|cases?|kits?|spools?|meters?|m|ea|each|unit\/s';
    }

    /**
     * Parses the item table of the standardized BatStateU-FO-PRO-01
     * Quotation/Canvass Form: ITEM NO. | UNIT | ITEM AND DESCRIPTION |
     * QUANTITY | UNIT PRICE. Same row-by-tail scanning approach as
     * parsePurchaseRequestForm() (see its docblock for why), just with a
     * single trailing currency amount per row instead of two — this form has
     * one price column, not a unit-cost/total-cost pair.
     *
     * @return array<int, array{name: string, unit: string, quantity: float, unitPrice: float}>
     */
    private function parseQuotationItems(string $text): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        $items = [];
        if (!preg_match('/QUANTITY\s+UNIT\s+PRICE(.*?)Brand\s+Model\s*:/su', $text, $tableMatch)) {
            return $items;
        }

        $currency = '(?:₱|Php|PHP)';
        $matched  = preg_match_all(
            '/(\d+(?:\.\d+)?)\s*' . $currency . '\s*([\d,]+\.\d{2})/u',
            $tableMatch[1],
            $rows,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );

        if ($matched === false || $matched === 0) {
            return $items;
        }

        $uom    = $this->unitVocabularyPattern();
        $cursor = 0;
        foreach ($rows as $row) {
            [$qtyRaw, $qtyOffset] = $row[1];
            $head   = trim(substr($tableMatch[1], $cursor, $qtyOffset - $cursor));
            $cursor = $row[2][1] + strlen($row[2][0]);

            // Drop the leading "ITEM NO." column value (e.g. "1 ", "2 ").
            if (preg_match('/^\d+\s*(.+)$/su', $head, $hm)) {
                $head = trim($hm[1]);
            }

            if ($head === '') {
                continue;
            }

            if (preg_match('/^(' . $uom . ')\s*(.+)$/isu', $head, $split)) {
                $unit = $split[1];
                $name = $split[2];
            } elseif (preg_match('/^(\S{1,15})\s+(.+)$/su', $head, $split)) {
                $unit = $split[1];
                $name = $split[2];
            } else {
                $unit = '';
                $name = $head;
            }

            $items[] = [
                'name'      => trim(preg_replace('/\s+/', ' ', $name)),
                'unit'      => trim($unit),
                'quantity'  => (float) $qtyRaw,
                'unitPrice' => (float) str_replace(',', '', $row[2][0]),
            ];
        }

        return $items;
    }

    /**
     * Parses the item table of the standardized BatStateU-FO-PRO-04 Abstract
     * of Canvass form: QUANTITY | UNIT | NAME AND DESCRIPTION OF ARTICLE/S |
     * SUPPLIER 1 | SUPPLIER 2 | SUPPLIER 3 | Previous Price | Date Purchased
     * | RESPONSIVE DEALER.
     *
     * Anchored on each row's Date Purchased rather than on its currency
     * amounts, unlike the PR/quotation parsers above — how many of the 3
     * supplier-price cells are actually filled genuinely varies row to row
     * (not every item gets canvassed from all 3 suppliers), so there's no
     * fixed count of amounts per row to anchor on the way "qty then two
     * costs" works for a PR row. A date, in contrast, is reliably present
     * once per row and doesn't occur anywhere else in this form's prose.
     *
     * The column order is fixed, though: whatever currency amounts appear
     * right before that date, the LAST one is always Previous Price (a
     * historical reference figure, not a live quotation) and is dropped;
     * everything earlier is a real Supplier N price kept for checking
     * against this PR's actual quotations.
     *
     * @return array<int, array{name: string, unit: string, quantity: float, supplierPrices: list<float>, responsiveDealer: string}>
     */
    private function parseAbstractOfCanvassItems(string $text): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        $items = [];
        if (!preg_match('/RESPONSIVE\s+DEALER(.*?)I\s+HEREBY\s+CERTIFY/su', $text, $tableMatch)) {
            return $items;
        }

        $table = $tableMatch[1];
        // Boilerplate ("...ARTICLE/S End-user") between the column headers
        // and the first real row — best-effort, only stripped if present.
        $table = preg_replace('/^.*?End-user\s*/su', '', $table) ?? $table;

        if (!preg_match_all('/\d{1,2}\/\d{1,2}\/\d{4}/u', $table, $dates, PREG_OFFSET_CAPTURE)) {
            return $items;
        }

        $currency = '(?:₱|Php|PHP)';
        $uom      = $this->unitVocabularyPattern();
        $cursor   = 0;

        foreach ($dates[0] as $i => [$dateStr, $dateOffset]) {
            $head = substr($table, $cursor, $dateOffset - $cursor);

            // Everything after this date up to the next row's leading
            // "qty unit" (or the table's end, for the last row) is this
            // row's Responsive Dealer name — kept as a fallback for
            // extractResponsiveDealer() below, for AOCs where the
            // recommendation paragraph's blank was left unfilled.
            $tailStart   = $dateOffset + strlen($dateStr);
            $rowEnd      = strlen($table);
            if (preg_match('/\d+\s*(?:' . $uom . ')/isu', $table, $nm, PREG_OFFSET_CAPTURE, $tailStart)) {
                $rowEnd = $nm[0][1];
            }
            $responsiveDealer = trim(substr($table, $tailStart, $rowEnd - $tailStart));
            $cursor = $rowEnd;

            // Every currency amount in the head, in column order — the last
            // is Previous Price (excluded), any earlier ones are Supplier
            // N prices actually being canvassed right now.
            preg_match_all('/' . $currency . '\s*([\d,]+\.\d{2})/u', $head, $priceMatches);
            $prices = array_map(fn ($p) => (float) str_replace(',', '', $p), $priceMatches[1] ?? []);
            $supplierPrices = $prices ? array_slice($prices, 0, -1) : [];

            // The item's own qty/unit/description is everything in the head
            // before its first price.
            $itemHead = preg_match('/^(.*?)' . $currency . '/su', $head, $ihm) ? $ihm[1] : $head;
            $itemHead = trim($itemHead);

            if (preg_match('/^(\d+(?:\.\d+)?)\s*(.+)$/su', $itemHead, $qm)) {
                $qty  = (float) $qm[1];
                $rest = trim($qm[2]);
            } else {
                $qty  = 0.0;
                $rest = $itemHead;
            }

            if ($rest === '') {
                continue;
            }

            if (preg_match('/^(' . $uom . ')\s*(.+)$/isu', $rest, $split)) {
                $unit = $split[1];
                $name = $split[2];
            } else {
                $unit = '';
                $name = $rest;
            }

            $items[] = [
                'name'             => trim(preg_replace('/\s+/', ' ', $name)),
                'unit'             => trim($unit),
                'quantity'         => $qty,
                'supplierPrices'   => $supplierPrices,
                'responsiveDealer' => $responsiveDealer,
            ];
        }

        return $items;
    }

    /**
     * Parses the item table of the standardized BatStateU-FO-PRO-03 Purchase
     * Order form: Stock No. | Unit | Item Description | Qty | Unit Cost |
     * Amount. Same row-by-tail scanning as parsePurchaseRequestForm() — a
     * qty followed by two consecutive currency amounts (Unit Cost then
     * Amount) is exactly the same row shape as a PR's item table, just under
     * different column headers.
     *
     * @return array<int, array{name: string, unit: string, quantity: float, unitCost: float}>
     */
    private function parsePoItems(string $text): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        $items = [];
        if (!preg_match('/Qty\s+Unit\s+Cost\s+Amount(.*?)\(TOTAL\s+AMOUNT\s+IN\s+WORDS\)/su', $text, $tableMatch)) {
            return $items;
        }

        $currency = '(?:₱|Php|PHP)';
        $matched  = preg_match_all(
            '/(\d+(?:\.\d+)?)\s*' . $currency . '\s*([\d,]+\.\d{2})\s*' . $currency . '\s*([\d,]+\.\d{2})/u',
            $tableMatch[1],
            $rows,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );

        if ($matched === false || $matched === 0) {
            return $items;
        }

        $uom    = $this->unitVocabularyPattern();
        $cursor = 0;
        foreach ($rows as $row) {
            [$qtyRaw, $qtyOffset] = $row[1];
            $head   = trim(substr($tableMatch[1], $cursor, $qtyOffset - $cursor));
            $cursor = $row[3][1] + strlen($row[3][0]);

            // Drop the leading "Stock No." column value (e.g. "1 ", "2 ").
            if (preg_match('/^\d+\s*(.+)$/su', $head, $hm)) {
                $head = trim($hm[1]);
            }

            if ($head === '') {
                continue;
            }

            if (preg_match('/^(' . $uom . ')\s*(.+)$/isu', $head, $split)) {
                $unit = $split[1];
                $name = $split[2];
            } elseif (preg_match('/^(\S{1,15})\s+(.+)$/su', $head, $split)) {
                $unit = $split[1];
                $name = $split[2];
            } else {
                $unit = '';
                $name = $head;
            }

            $items[] = [
                'name'     => trim(preg_replace('/\s+/', ' ', $name)),
                'unit'     => trim($unit),
                'quantity' => (float) $qtyRaw,
                'unitCost' => (float) str_replace(',', '', $row[2][0]),
            ];
        }

        return $items;
    }

    /** A form label followed by a value that may span 1-2 lines before the next label starts. */
    /**
     * A multi-word label ("Name of Project:", "Project Location:") isn't
     * safe to match literally — a narrow form column wraps the label text
     * itself mid-phrase in the extracted text ("Name of\nProject:"), not
     * just the value after it. Quoting first (so punctuation like "/" and
     * ":" stays literal) and then loosening each of the label's own
     * internal spaces into \s+ is what makes this tolerant of that.
     */
    private function labelPattern(string $label): string
    {
        return str_replace(' ', '\s+', preg_quote($label, '/'));
    }

    private function parseLabeledBlock(string $text, string $label, string $stopLabel): ?string
    {
        $pattern = '/' . $this->labelPattern($label) . '\s*(.*?)\s*' . $this->labelPattern($stopLabel) . '/su';
        if (preg_match($pattern, $text, $m)) {
            return trim(preg_replace('/\s+/', ' ', $m[1])) ?: null;
        }
        return null;
    }

    /**
     * Creates the PR from Procurement's reviewed/edited item list — the
     * counterpart to extractPurchaseRequestFields(). Items don't have to be
     * every item in the PPMP; whatever's left over just waits for the next
     * upload against the same (or a later) approved PPMP.
     */
    public function createPurchaseRequestFromApp(Request $request, DocumentValidationService $validator): JsonResponse
    {
        $validated = $request->validate([
            'budget_proposal_id' => 'required|exists:budget_proposals,id',
            'pr_number'          => 'nullable|string|max:100',
            'title'              => 'nullable|string|max:255',
            'quarter'            => 'nullable|in:Q1,Q2,Q3,Q4',
            'file'               => 'required|file|mimes:pdf|max:10240',
            'items'              => 'required|array|min:1',
            'items.*.name'       => 'required|string|max:255',
            'items.*.unit'       => 'nullable|string|max:50',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_cost'  => 'required|numeric|min:0',
        ]);

        $proposal = BudgetProposal::with('office')->findOrFail($validated['budget_proposal_id']);
        abort_if($proposal->status !== 'approved', 422, 'This PPMP is not approved.');
        $office = $proposal->office;
        abort_if(!$office, 422, 'This PPMP has no office on record.');

        $number = trim((string) ($validated['pr_number'] ?? '')) ?: ('PR-' . $office->code . '-' . now()->format('Ymd-His'));

        if (PurchaseRequest::where('number', $number)->exists()) {
            return response()->json(['error' => "A Purchase Request numbered \"{$number}\" already exists."], 422);
        }

        // Authoritative content check. The modal already showed a verdict for
        // the extracted text, but what actually gets saved is the reviewed list
        // — which the user can edit — so the decision is re-made here against
        // exactly what is about to be written, and refused outright if it
        // doesn't hold up. Document fields (office/fiscal year/total) are
        // likewise re-read off the freshly uploaded file rather than trusted
        // from the modal's earlier round-trip — same reasoning, just for the
        // header instead of the item table. PR number isn't included here;
        // it's already the exact value just checked above.
        $file       = $request->file('file');
        $parsed     = $this->parsePurchaseRequestForm($this->readPdfText($file));
        $quarter    = $validated['quarter'] ?? null;
        $forCheck   = array_map(fn ($i) => [
            'name'     => $i['name'],
            'quantity' => (float) $i['quantity'],
            'unit'     => $i['unit'] ?? '',
            'unitCost' => (float) $i['unit_cost'],
        ], $validated['items']);
        $validation = $validator->validatePrAgainstPpmp($forCheck, $proposal, $quarter, null, [
            'officeCode' => $parsed['officeCode'] ?? null,
            'fiscalYear' => $parsed['fiscalYear'] ?? null,
            'totalCost'  => $parsed['totalCost'] ?? null,
        ]);

        if ($validation['verdict'] !== DocumentValidation::PASSED) {
            return response()->json([
                'error'      => $validation['summary'],
                'validation' => $validation,
            ], 422);
        }

        $path = $file->storeAs('purchase-requests/' . now()->year, Str::slug($number) . '-' . now()->format('His') . '.pdf', 'public');

        $pr = PurchaseRequest::create([
            'budget_proposal_id' => $proposal->id,
            'office_id'          => $office->id,
            'created_by_user_id' => auth()->id(),
            'number'             => $number,
            'title'              => trim((string) ($validated['title'] ?? '')) ?: "Purchase Request – {$office->name}",
            'fiscal_year'        => $proposal->fiscal_year,
            'status'             => 'new',
            'signatory_stage'    => 'draft',
            'canvassing_stage'   => 'not_started',
            'file_path'          => $path,
            'uploaded_at'        => now(),
        ]);

        foreach ($validated['items'] as $item) {
            $unitCost = (float) $item['unit_cost'];
            $qty      = (float) $item['quantity'];
            $pr->items()->create([
                'name'                 => $item['name'],
                'quantity'             => $qty,
                'unit'                 => $item['unit'] ?? null,
                'estimated_unit_cost'  => $unitCost,
                'estimated_total_cost' => round($unitCost * $qty, 2),
            ]);
        }

        $pr->update(['total_amount' => $pr->items()->sum('estimated_total_cost')]);

        // Recorded against the PR itself so the routing gate — and anyone
        // reviewing later — can see exactly what was checked and why it passed.
        $validator->record($pr, $proposal, DocumentValidation::PAIR_PPMP_PR, $validation, $quarter);

        return response()->json(['success' => true, 'prId' => $pr->id, 'prNumber' => $pr->number]);
    }

    /**
     * Re-uploads the PR document for a PR that already exists, going through
     * the same Step 2/3 read-and-review flow as creating one (see
     * createPurchaseRequestFromApp()) instead of the plain file-swap in
     * uploadPurchaseRequest() below — a corrected/re-scanned document is
     * re-checked against the linked PPMP and its item rows replace the old
     * ones, rather than trusting whatever was scanned the first time.
     *
     * Only offered while nothing downstream depends on the current item
     * list yet: once an Abstract of Canvass exists, quotations were already
     * gathered against those specific items, so re-scanning would silently
     * invalidate them.
     */
    public function reuploadPurchaseRequestFromApp(Request $request, PurchaseRequest $pr, DocumentValidationService $validator): JsonResponse
    {
        abort_if($pr->abstractOfCanvass, 422, 'This PR already has an Abstract of Canvass, so its items can no longer be re-scanned. Contact an administrator if the uploaded document itself needs correcting.');

        $proposal = BudgetProposal::with('office')->find($pr->budget_proposal_id);
        abort_if(!$proposal, 422, 'This PR is not linked to a PPMP and cannot be re-validated this way.');
        $office = $proposal->office;
        abort_if(!$office, 422, 'This PPMP has no office on record.');

        $validated = $request->validate([
            'pr_number'          => 'nullable|string|max:100',
            'title'              => 'nullable|string|max:255',
            'quarter'            => 'nullable|in:Q1,Q2,Q3,Q4',
            'file'               => 'required|file|mimes:pdf|max:10240',
            'items'              => 'required|array|min:1',
            'items.*.name'       => 'required|string|max:255',
            'items.*.unit'       => 'nullable|string|max:50',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_cost'  => 'required|numeric|min:0',
        ]);

        $number = trim((string) ($validated['pr_number'] ?? '')) ?: $pr->number;
        if ($number !== $pr->number && PurchaseRequest::where('number', $number)->where('id', '!=', $pr->id)->exists()) {
            return response()->json(['error' => "A Purchase Request numbered \"{$number}\" already exists."], 422);
        }

        // Same authoritative re-check as creation — see the comment on
        // createPurchaseRequestFromApp() above. excludePrId keeps this PR's
        // own (unchanged) number from being flagged as a duplicate of itself.
        $file       = $request->file('file');
        $parsed     = $this->parsePurchaseRequestForm($this->readPdfText($file));
        $quarter    = $validated['quarter'] ?? null;
        $forCheck   = array_map(fn ($i) => [
            'name'     => $i['name'],
            'quantity' => (float) $i['quantity'],
            'unit'     => $i['unit'] ?? '',
            'unitCost' => (float) $i['unit_cost'],
        ], $validated['items']);
        $validation = $validator->validatePrAgainstPpmp($forCheck, $proposal, $quarter, null, [
            'officeCode'  => $parsed['officeCode'] ?? null,
            'fiscalYear'  => $parsed['fiscalYear'] ?? null,
            'totalCost'   => $parsed['totalCost'] ?? null,
            'excludePrId' => $pr->id,
        ]);

        if ($validation['verdict'] !== DocumentValidation::PASSED) {
            return response()->json([
                'error'      => $validation['summary'],
                'validation' => $validation,
            ], 422);
        }

        $path = $file->storeAs('purchase-requests/' . now()->year, Str::slug($number) . '-' . now()->format('His') . '.pdf', 'public');

        $pr->items()->delete();
        foreach ($validated['items'] as $item) {
            $unitCost = (float) $item['unit_cost'];
            $qty      = (float) $item['quantity'];
            $pr->items()->create([
                'name'                 => $item['name'],
                'quantity'             => $qty,
                'unit'                 => $item['unit'] ?? null,
                'estimated_unit_cost'  => $unitCost,
                'estimated_total_cost' => round($unitCost * $qty, 2),
            ]);
        }

        $pr->update([
            'number'       => $number,
            'title'        => trim((string) ($validated['title'] ?? '')) ?: $pr->title,
            'file_path'    => $path,
            'uploaded_at'  => now(),
            'total_amount' => $pr->items()->sum('estimated_total_cost'),
        ]);

        $validator->record($pr, $proposal, DocumentValidation::PAIR_PPMP_PR, $validation, $quarter);

        NotificationService::prUploaded($pr);

        return response()->json(['success' => true, 'prId' => $pr->id, 'prNumber' => $pr->number, 'filePath' => $path]);
    }

    // ── Canvassing tab (quotation uploads + stage tracking) ──────────────────

    public function canvassing(): View
    {
        $prs = PurchaseRequest::with(['office', 'budgetProposal', 'items', 'documents' => fn ($q) => $q->where('document_type', 'canvass_quotation')])
            ->where('signatory_stage', 'fully_signed')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($pr) => [
                'id'              => $pr->id,
                'prNumber'        => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
                'office'          => $pr->office?->code ?? '—',
                'title'           => $pr->title,
                'itemCount'       => $pr->items->count(),
                'items'           => $pr->items->map(fn ($item) => [
                    'name'      => $item->name,
                    'quantity'  => (int) $item->quantity,
                    'unit'      => $item->unit,
                    'unitCost'  => (float) $item->estimated_unit_cost,
                    'totalCost' => (float) $item->estimated_total_cost,
                ])->all(),
                'canvassingStage'  => $pr->canvassing_stage,
                'canvassingLabel'  => $pr->canvassing_label,
                'readyForAoc'      => $pr->isReadyForAoc(),
                'quotationsLocked' => $pr->abstractOfCanvass !== null,
                'budgetProposalId'   => $pr->budget_proposal_id,
                'budgetProposalCode' => $pr->budgetProposal?->code,
                'quotations'      => $pr->documents->map(fn ($doc) => [
                    'id'        => $doc->id,
                    'supplier'  => $doc->title,
                    'filename'  => $doc->original_filename,
                    'url'       => Storage::url($doc->file_path),
                    'uploadedAt'=> $doc->uploaded_at?->format('M d, Y') ?? '—',
                    'deleteUrl' => route('procurement-office.canvass-document.delete', $doc->id),
                ])->all(),
                'uploadUrl'   => route('procurement-office.purchase-request.canvass-document', $pr->id),
                'finalizeUrl' => route('procurement-office.purchase-request.canvassing-finalize', $pr->id),
            ]);

        // Cards for PRs raised against the same PPMP sit together under one
        // section header (office + PPMP code + count) — every card still
        // shows, nothing collapsed or hidden; this is purely about scanning
        // a long list faster once several PRs share a PPMP, unlike the
        // single-row-plus-Next collapse used on Purchase Request Management
        // (canvassing is itself the actionable task per PR, not a summary
        // list, so nothing here should require a click to even see).
        $sections = $prs
            ->groupBy(fn ($pr) => $pr['budgetProposalId'] ?? ('solo-' . $pr['id']))
            ->map(function ($group) {
                $first = $group->first();
                // Shown whenever the PPMP link is actually known — not just
                // for groups of 2+. A PR that's the only one raised so far
                // against its PPMP still has a real, known origin; hiding
                // that until a second PR shows up would be an arbitrary cutoff.
                $label = $first['budgetProposalId']
                    ? "{$first['budgetProposalCode']} — {$first['office']} · {$group->count()} " . ($group->count() === 1 ? 'PR' : 'PRs')
                    : null;

                return [
                    'label'   => $label,
                    'prs'     => $group->values()->all(),
                    'sortKey' => $group->max('id'),
                ];
            })
            ->sortByDesc('sortKey')
            ->values()
            ->all();

        return view('prism.procurement-office.canvassing', $this->withCommon('canvassing', [
            'pageTitle'          => 'Canvassing',
            'prs'                => $prs->values()->all(),
            'sections'           => $sections,
            'extractSupplierUrl' => route('procurement-office.canvassing.extract-supplier'),
        ]));
    }

    /**
     * Reads the standardized BatStateU-FO-PRO-01 Quotation/Canvass Form the
     * supplier signs — "Company Name" always sits on the line right after the
     * supplier's own name in the signature block — and returns a best-guess
     * supplier name so Procurement never has to type it — the field is read-only
     * and populated only from this extraction (falling back to the uploaded
     * filename client-side when a scanned/image upload or unrecognized layout
     * makes extraction return null).
     */
    public function extractCanvassSupplier(Request $request, DocumentValidationService $validator): JsonResponse
    {
        $request->validate([
            'document'            => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'purchase_request_id' => 'nullable|integer|exists:purchase_requests,id',
        ]);

        $file = $request->file('document');
        if ($file->getClientMimeType() !== 'application/pdf' && $file->getClientOriginalExtension() !== 'pdf') {
            // Can't read an image for its item table — best-effort supplier
            // name only; item-vs-PR validation is skipped, not failed.
            return response()->json(['supplierName' => null]);
        }

        $text  = $this->readPdfText($file);
        $items = $this->parseQuotationItems($text);

        $payload = [
            'supplierName' => $this->parseSupplierNameFromQuotation($text),
            'items'        => $items,
        ];

        if ($request->filled('purchase_request_id')) {
            $pr = PurchaseRequest::find($request->input('purchase_request_id'));
            if ($pr) {
                $payload['validation'] = $validator->validateQuotationAgainstPr($items, $pr);
            }
        }

        return response()->json($payload);
    }

    private function parseSupplierNameFromQuotation(string $text): ?string
    {
        return $this->parseLabeledLineFromQuotation($text, 'Company Name');
    }

    /**
     * Same standardized quotation form as parseSupplierNameFromQuotation(),
     * just reading the "Company Address" line instead of "Company Name".
     */
    private function parseSupplierAddressFromQuotation(string $text): ?string
    {
        return $this->parseLabeledLineFromQuotation($text, 'Company Address');
    }

    /**
     * The BatStateU-FO-PRO-01 Quotation/Canvass Form's signature block can
     * extract in two different orders depending on how the PDF was produced:
     *
     *   - interleaved: each value sits on the line right above its own label
     *     ("Toy Ride Company" / "Company Name" / ...), or
     *   - blocked: every label in the block extracts first (as the form's
     *     static template text), followed by every filled-in value in the
     *     same relative order ("Printed / Signature" / "Company Name" /
     *     "Company Address" / "Contact No." / "Juan Toy Ride" / "Toy Ride
     *     Company" / "Batangas City" / "+63912345678") — this happens when
     *     the filled values were added as a separate text/annotation layer
     *     on top of the template, which is what typed-and-signed uploads
     *     from suppliers commonly produce.
     *
     * The blocked case previously broke extraction: reading "the line right
     * above the label" for "Company Name" landed on "Printed / Signature"
     * (a label itself, not a value) whenever that label read as "Printed /
     * Signature" rather than "Printed Name" — the only variant the old
     * label-detection regex recognized — so it was returned as if it were
     * the actual supplier name. Both layouts are handled here: block
     * position-matching is tried first (only fires when multiple labels
     * genuinely run together), falling back to the original
     * line-right-above-the-label check for the interleaved layout.
     */
    private function parseLabeledLineFromQuotation(string $text, string $label): ?string
    {
        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $text)),
            fn ($line) => $line !== ''
        ));

        $isLabelLine = fn (string $line): bool => (bool) preg_match(
            '/^(Printed(\s*Name)?\s*\/?\s*Signature|Signature|Company\s*(Name|Address)|Contact\s*No\.?|Canvasser|Procurement Officer)/i',
            $line
        );

        foreach ($lines as $i => $line) {
            if (stripos($line, $label) === false) {
                continue;
            }

            // Blocked layout: expand to the full run of consecutive label
            // lines this one sits in, then read the value at the same
            // position within the run of lines immediately following it.
            $blockStart = $i;
            while ($blockStart > 0 && $isLabelLine($lines[$blockStart - 1])) {
                $blockStart--;
            }
            $blockEnd = $i;
            while ($blockEnd < count($lines) - 1 && $isLabelLine($lines[$blockEnd + 1])) {
                $blockEnd++;
            }

            if ($blockEnd > $blockStart) {
                $offset       = $i - $blockStart;
                $valueLineIdx = $blockEnd + 1 + $offset;
                $blocked      = $lines[$valueLineIdx] ?? null;

                if ($blocked !== null && $blocked !== '' && !$isLabelLine($blocked)) {
                    return $blocked;
                }
            }

            // Interleaved layout: the value sits on the line right above
            // this (isolated) label line.
            if ($i > 0) {
                $candidate = $lines[$i - 1];
                if ($candidate !== '' && !$isLabelLine($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * Canvassing accepts one quotation per supplier — multiple quotations can be
     * uploaded to compare suppliers. Attaching a document uploads it immediately
     * (no separate confirm step); this only moves the PR to "in progress" — it
     * does NOT mark it ready for AOC on its own. The office finalizes explicitly
     * via finalizeCanvassing() once they're done comparing suppliers. Quotations
     * stay addable/removable until an Abstract of Canvass is actually created for
     * the PR, at which point they lock (see abstractOfCanvass()).
     */
    public function uploadCanvassDocument(Request $request, PurchaseRequest $pr, DocumentValidationService $validator): JsonResponse
    {
        if ($pr->signatory_stage !== 'fully_signed') {
            return response()->json(['error' => 'PR must be fully signed before canvassing.'], 422);
        }
        if ($pr->abstractOfCanvass()->exists()) {
            return response()->json(['error' => 'Quotations are locked — an Abstract of Canvass has already been created for this PR.'], 422);
        }

        $request->validate([
            'document'      => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'supplier_name' => 'required|string|max:255',
        ]);

        $file  = $request->file('document');
        $isPdf = $file->getClientMimeType() === 'application/pdf' || $file->getClientOriginalExtension() === 'pdf';

        // Authoritative re-check, same reasoning as createPurchaseRequestFromApp():
        // the earlier extract-and-preview round trip already showed a verdict,
        // but the file actually being saved is re-read and re-validated here
        // rather than trusted from that round trip. Can't be run on a plain
        // image (no text table to read), so those skip straight through —
        // same limitation the supplier-name/address extraction already has.
        $text       = $isPdf ? $this->readPdfText($file) : '';
        $items      = $isPdf ? $this->parseQuotationItems($text) : [];
        $validation = $isPdf ? $validator->validateQuotationAgainstPr($items, $pr) : null;

        if ($validation && $validation['verdict'] === DocumentValidation::FAILED) {
            return response()->json([
                'error'      => $validation['summary'],
                'validation' => $validation,
            ], 422);
        }

        $path = $file->store('canvass/' . now()->year, 'public');

        $supplierAddress = $isPdf ? $this->parseSupplierAddressFromQuotation($text) : null;

        $doc = DocumentUpload::create([
            'uploaded_by_user_id' => auth()->id(),
            'attachable_type'     => PurchaseRequest::class,
            'attachable_id'       => $pr->id,
            'document_type'       => 'canvass_quotation',
            'title'               => $request->input('supplier_name'),
            'original_filename'   => $file->getClientOriginalName(),
            'file_path'           => $path,
            'mime_type'           => $file->getClientMimeType(),
            'file_size'           => $file->getSize(),
            'status'              => 'uploaded',
            'uploaded_at'         => now(),
            'extracted_fields_json' => $supplierAddress ? ['supplier_address' => $supplierAddress] : null,
        ]);

        if ($validation) {
            $validator->record($doc, $pr, DocumentValidation::PAIR_PR_CANVASS, $validation);
        }

        if ($pr->canvassing_stage === 'not_started') {
            $pr->update(['canvassing_stage' => 'in_progress']);
        }

        ProcurementStatusUpdate::create([
            'purchase_request_id' => $pr->id,
            'updated_by_user_id'  => auth()->id(),
            'status'              => 'canvass_quotation_uploaded',
            'remarks'             => 'Quotation from ' . $doc->title . ' uploaded.',
        ]);

        return response()->json([
            'success'          => true,
            'documentId'       => $doc->id,
            'supplierName'     => $doc->title,
            'filename'         => $doc->original_filename,
            'url'              => Storage::url($path),
            'uploadedAt'       => $doc->uploaded_at->format('M d, Y'),
            'deleteUrl'        => route('procurement-office.canvass-document.delete', $doc->id),
            'canvassingStage'  => $pr->fresh()->canvassing_stage,
            'canvassingLabel'  => $pr->fresh()->canvassing_label,
            'readyForAoc'      => $pr->fresh()->isReadyForAoc(),
        ]);
    }

    /**
     * Explicit "done comparing suppliers" action — the office clicks this once
     * they've attached all the quotations they want, which is what actually
     * marks the PR ready for AOC (uploading a quotation no longer does this
     * automatically, since more suppliers may still be added).
     */
    public function finalizeCanvassing(PurchaseRequest $pr): JsonResponse
    {
        if ($pr->signatory_stage !== 'fully_signed') {
            return response()->json(['error' => 'PR must be fully signed before canvassing.'], 422);
        }
        if ($pr->abstractOfCanvass()->exists()) {
            return response()->json(['error' => 'Canvassing is already finalized for this PR.'], 422);
        }
        if (!$pr->documents()->where('document_type', 'canvass_quotation')->exists()) {
            return response()->json(['error' => 'Attach at least one supplier quotation before finalizing.'], 422);
        }

        $pr->update(['canvassing_stage' => 'completed']);

        ProcurementStatusUpdate::create([
            'purchase_request_id' => $pr->id,
            'updated_by_user_id'  => auth()->id(),
            'status'              => 'canvassing_completed',
            'remarks'             => 'Canvassing finalized — ready for AOC.',
        ]);

        return response()->json([
            'success'         => true,
            'canvassingStage' => 'completed',
            'canvassingLabel' => $pr->fresh()->canvassing_label,
            'readyForAoc'     => $pr->fresh()->isReadyForAoc(),
        ]);
    }

    public function deleteCanvassDocument(DocumentUpload $document): JsonResponse
    {
        if ($document->document_type !== 'canvass_quotation') {
            return response()->json(['error' => 'Not a canvass quotation.'], 422);
        }

        $pr = $document->attachable;
        if ($pr instanceof PurchaseRequest && $pr->abstractOfCanvass()->exists()) {
            return response()->json(['error' => 'Quotations are locked — an Abstract of Canvass has already been created for this PR.'], 422);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        $quotationsRemaining = 0;
        if ($pr instanceof PurchaseRequest) {
            $quotationsRemaining = $pr->documents()->where('document_type', 'canvass_quotation')->count();
            if ($quotationsRemaining === 0) {
                $pr->update(['canvassing_stage' => 'not_started']);
            }
        }

        return response()->json([
            'success'              => true,
            'quotationsRemaining'  => $quotationsRemaining,
            'canvassingStage'      => $pr instanceof PurchaseRequest ? $pr->fresh()->canvassing_stage : null,
            'canvassingLabel'      => $pr instanceof PurchaseRequest ? $pr->fresh()->canvassing_label : null,
            'readyForAoc'          => $pr instanceof PurchaseRequest ? $pr->fresh()->isReadyForAoc() : false,
        ]);
    }

    // ── Phase 2: Abstract of Canvass ─────────────────────────────────────────

    public function abstractOfCanvass(): View
    {
        $data = $this->abstractOfCanvassData();

        return view('prism.procurement-office.abstract-of-canvass', $this->withCommon('abstract-of-canvass', [
            'pageTitle'   => 'Abstract of Canvass',
            'aocs'        => $data['aocs'],
            'eligiblePrs' => $data['eligiblePrs'],
            'stageMeta'   => AbstractOfCanvass::signatoryStageMeta(),
            'offices'     => Office::whereHas('purchaseRequests.abstractOfCanvass')->select('id', 'code', 'name')->orderBy('code')->get()->toArray(),
        ]));
    }

    public function abstractOfCanvassRefresh(): JsonResponse
    {
        return response()->json($this->abstractOfCanvassData());
    }

    private function abstractOfCanvassData(): array
    {
        $aocs = AbstractOfCanvass::with(['purchaseRequest.office', 'purchaseRequest.budgetProposal', 'purchaseRequest.items', 'purchaseRequest.documents', 'signatureLogs.signedBy', 'signatureLogs.attachments', 'purchaseOrder'])
            ->latest()
            ->get()
            ->map(fn ($aoc) => $this->mapAocForFrontend($aoc));

        // Same "one row per PPMP group" collapse as Purchase Request
        // Management — an AOC has no PPMP link of its own, it inherits its
        // parent PR's, so several AOCs can share one just like several PRs
        // can (see purchaseRequestManagementRows()).
        $aocSiblingCounts = $aocs->filter(fn ($a) => $a['budgetProposalId'])
            ->groupBy('budgetProposalId')
            ->map->count();
        $seenAocProposalIds = [];

        $aocs = $aocs->map(function ($a) use (&$seenAocProposalIds, $aocSiblingCounts) {
            $isTableRow = true;
            if ($a['budgetProposalId']) {
                $isTableRow = !in_array($a['budgetProposalId'], $seenAocProposalIds, true);
                if ($isTableRow) {
                    $seenAocProposalIds[] = $a['budgetProposalId'];
                }
            }
            return array_merge($a, [
                'isTableRow'   => $isTableRow,
                'siblingCount' => $a['budgetProposalId'] ? ($aocSiblingCounts[$a['budgetProposalId']] ?? 1) : 1,
            ]);
        })->all();

        // PRs that completed canvassing but don't have an AOC yet
        $eligiblePrs = PurchaseRequest::with('office')
            ->where('signatory_stage', 'fully_signed')
            ->where('canvassing_stage', 'completed')
            ->whereDoesntHave('abstractOfCanvass')
            ->latest()
            ->get()
            ->map(fn ($pr) => [
                'id'       => $pr->id,
                'prNumber' => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
                'office'   => $pr->office?->code ?? '—',
                'title'    => $pr->title,
                'createUrl'=> route('procurement-office.aoc.create', $pr->id),
            ])
            ->all();

        return ['aocs' => $aocs, 'eligiblePrs' => $eligiblePrs];
    }

    /**
     * Shared shape for one AOC row as the frontend needs it — used both for
     * the full list/refresh payload and for the single freshly-created AOC
     * returned by createAoc(), so the list can append it in place instead of
     * reloading the page.
     */
    private function mapAocForFrontend(AbstractOfCanvass $aoc): array
    {
        $pr = $aoc->purchaseRequest;
        $quotations = $pr->documents->where('document_type', 'canvass_quotation');

        // The AOC's "Responsive Dealer" declaration (extracted from the signed
        // AOC PDF — see uploadAbstractOfCanvass()) is the authoritative winning
        // supplier once available. Match it back to its canvass quotation to
        // also pull that supplier's extracted address; before the AOC PDF is
        // uploaded, or if no match is found, fall back to the first quotation
        // on file so the Issue PO form still has something to prefill.
        $winningName = $aoc->winning_supplier_name;
        $matchedQuotation = $winningName
            ? $quotations->first(fn ($d) => strcasecmp(trim((string) $d->title), trim($winningName)) === 0)
            : null;
        $matchedQuotation ??= $quotations->first();

        $supplierAddress = null;
        if ($matchedQuotation && is_array($matchedQuotation->extracted_fields_json)) {
            $supplierAddress = $matchedQuotation->extracted_fields_json['supplier_address'] ?? null;
        }

        $winningTotal = $this->computeWinningSupplierTotal($pr, $matchedQuotation);

        return [
            'id'             => $aoc->id,
            'code'           => $aoc->code ?? 'AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT),
            'prNumber'       => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
            'office'         => $pr->office?->code ?? '—',
            'title'          => $pr->title,
            // Inherited from the parent PR — an AOC has no PPMP link of its
            // own, it's whichever PPMP its PR was raised against. Real
            // grouping (isTableRow/siblingCount) needs the full AOC list, so
            // it's only computed in abstractOfCanvassData(); these are safe
            // solo-item defaults for mapAocForFrontend()'s other two callers
            // (createAoc()/signing actions), which only ever return one AOC
            // in isolation and would otherwise leave them undefined.
            'budgetProposalId'   => $pr->budget_proposal_id,
            'budgetProposalCode' => $pr->budgetProposal?->code,
            'isTableRow'         => true,
            'siblingCount'       => 1,
            'signatoryStage'   => $aoc->signatory_stage,
            'signatoryLabel'   => $aoc->signatory_label,
            'statusBucket'     => match ($aoc->signatory_stage) {
                'fully_signed' => 'fully_signed',
                'draft'        => 'draft',
                default        => 'in_progress',
            },
            'nextStage'        => $aoc->nextSignatoryStage(),
            'currentStageType' => $aoc->stageMetaFor($aoc->signatory_stage)['type'] ?? 'signature',
            'nextStageLabel'   => $aoc->stageMetaFor($aoc->nextSignatoryStage())['label'] ?? null,
            'hasPo'            => $aoc->purchaseOrder !== null,
            'poNumber'         => $aoc->purchaseOrder?->po_number,
            'poId'             => $aoc->purchaseOrder?->id,
            'signatureLogs'  => $aoc->signatureLogs->map(fn ($l) => [
                'display' => $aoc->describeSignatureLog($l),
                'by'      => $l->signedBy?->name ?? '—',
                'at'      => $l->signed_at?->format('M d, Y g:i A') ?? '—',
                'atRaw'   => $l->signed_at?->toIso8601String(),
                'remarks' => $l->remarks ?? '',
                'attachments' => $l->attachments->map(fn ($a) => [
                    'filename' => $a->original_filename,
                    'isImage'  => str_starts_with($a->mime_type ?? '', 'image/'),
                    'url'      => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                        'signature-attachment.show', now()->addDay(), ['id' => $a->id]
                    ),
                ])->all(),
            ])->all(),
            'remarks'        => $aoc->remarks ?? '—',
            'createdAt'      => $aoc->created_at->format('M d, Y'),
            'createdAtRaw'   => $aoc->created_at->toIso8601String(),
            'advanceUrl'     => route('procurement-office.aoc.advance', $aoc->id),
            'returnUrl'      => route('procurement-office.aoc.return', $aoc->id),
            'issuePoUrl'     => route('procurement-office.po.issue', $aoc->id),
            'uploadUrl'      => route('procurement-office.aoc.upload', $aoc->id),
            'extractUrl'     => route('procurement-office.aoc.extract', $aoc->id),
            'pdfFile'        => $aoc->file_path,
            'prTotal'        => (float) ($pr->total_amount ?? 0),
            // What the Issue PO form's Total Amount should actually default
            // to — the winning supplier's own quoted unit prices times the
            // PR's item quantities, not the PPMP's estimate (prTotal above,
            // still used elsewhere for the PR items preview). Null when it
            // can't be computed (no quotation on file, or unreadable), so
            // the frontend falls back to prTotal rather than prefilling ₱0.
            'winningTotal'   => $winningTotal,
            'prItems'        => $pr->items->map(fn ($i) => [
                'name'      => $i->name,
                'quantity'  => (float) $i->quantity,
                'unit'      => $i->unit,
                'unitCost'  => (float) $i->estimated_unit_cost,
                'totalCost' => (float) $i->estimated_total_cost,
            ])->all(),
            'quotations'     => $quotations->map(fn ($d) => [
                'supplier'   => $d->title,
                'filename'   => $d->original_filename,
                'url'        => \Illuminate\Support\Facades\Storage::url($d->file_path),
                'uploadedAt' => $d->uploaded_at?->format('M d, Y') ?? '—',
            ])->values()->all(),
            'supplierName'    => $winningName ?? $matchedQuotation?->title,
            'supplierAddress' => $supplierAddress,
        ];
    }

    /**
     * What a Purchase Order for this AOC should actually total — the
     * winning supplier's own quoted unit price for each PR item, times that
     * item's actual quantity, summed up. Deliberately not the PR's
     * PPMP-estimated total: canvassing exists precisely because the real
     * price a supplier charges is expected to differ from the plan, so the
     * PO amount should reflect what was actually quoted, not what was
     * budgeted for.
     *
     * Returns null (not 0) when there's nothing to compute from — no
     * matched quotation, or its item table couldn't be read — so the
     * caller can fall back to the PPMP estimate instead of prefilling ₱0.
     */
    private function computeWinningSupplierTotal(PurchaseRequest $pr, ?DocumentUpload $matchedQuotation): ?float
    {
        if (!$matchedQuotation) {
            return null;
        }

        $quotedItems = $this->parseQuotationItems($this->readStoredPdfText($matchedQuotation->file_path));
        if (!$quotedItems) {
            return null;
        }

        $prItems = $pr->items()->get();
        if ($prItems->isEmpty()) {
            return null;
        }

        $matcher = app(ItemMatchingService::class);
        $left    = $prItems->map(fn ($i) => ['name' => $i->name])->all();
        $right   = array_map(fn ($i) => ['name' => $i['name']], $quotedItems);
        $result  = $matcher->match($left, $right);

        $total   = 0.0;
        $matched = 0;
        foreach ($result['matches'] as $m) {
            if (!$m['matched']) {
                continue;
            }
            $prItem      = $prItems[$m['leftIndex']];
            $quotedPrice = (float) $quotedItems[$m['rightIndex']]['unitPrice'];
            $total      += (float) $prItem->quantity * $quotedPrice;
            $matched++;
        }

        return $matched > 0 ? round($total, 2) : null;
    }

    public function createAoc(Request $request, PurchaseRequest $pr): JsonResponse
    {
        if (!$pr->isReadyForAoc()) {
            return response()->json(['error' => $pr->abstractOfCanvass
                ? 'AOC already exists for this PR.'
                : 'PR must be fully signed and canvassing completed before creating an AOC.'], 422);
        }

        $aoc = AbstractOfCanvass::create([
            'purchase_request_id' => $pr->id,
            'created_by_user_id'  => auth()->id(),
            'code'                => 'AOC-' . now()->format('Ymd') . '-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
            'signatory_stage'     => 'draft',
        ]);

        $aoc->load(['purchaseRequest.office', 'purchaseRequest.items', 'purchaseRequest.documents', 'signatureLogs.signedBy', 'signatureLogs.attachments', 'purchaseOrder']);

        return response()->json(['success' => true, 'aoc' => $this->mapAocForFrontend($aoc)]);
    }

    public function advanceAocStage(Request $request, AbstractOfCanvass $aoc, SignatoryActionService $signatory): JsonResponse
    {
        $result = $signatory->advance($aoc, $request->input('remarks'));

        return response()->json($result, $result['status'] ?? 200);
    }

    public function returnAoc(Request $request, AbstractOfCanvass $aoc, SignatoryActionService $signatory): JsonResponse
    {
        $request->validate(['remarks' => 'required|string|max:1000']);

        return response()->json($signatory->returnOneStep($aoc, $request->input('remarks')));
    }

    // ── Phase 3: Purchase Order ───────────────────────────────────────────────

    public function purchaseOrders(): View
    {
        $data = $this->purchaseOrdersData();

        return view('prism.procurement-office.purchase-order', $this->withCommon('purchase-orders', [
            'pageTitle'    => 'Purchase Orders',
            'purchaseOrders' => $data['purchaseOrders'],
            'eligibleAocs' => $data['eligibleAocs'],
            'statusChain'  => PurchaseOrder::statusChain(),
            'offices'      => Office::whereHas('purchaseRequests.abstractOfCanvass.purchaseOrder')->select('id', 'code', 'name')->orderBy('code')->get()->toArray(),
        ]));
    }

    public function purchaseOrdersRefresh(): JsonResponse
    {
        return response()->json($this->purchaseOrdersData());
    }

    private function purchaseOrdersData(): array
    {
        $pos = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'abstractOfCanvass.purchaseRequest.budgetProposal', 'createdBy', 'paidBy', 'documents', 'signatureLogs.signedBy', 'signatureLogs.attachments'])
            ->orderByDesc('id')
            ->get()
            ->map(fn ($po) => $this->mapPoForFrontend($po));

        // Same "one row per PPMP group" collapse as Purchase Request
        // Management/Abstract of Canvass — a PO inherits its PPMP link from
        // its AOC's PR, so several POs can share one just like several PRs can.
        $poSiblingCounts = $pos->filter(fn ($p) => $p['budgetProposalId'])
            ->groupBy('budgetProposalId')
            ->map->count();
        $seenPoProposalIds = [];

        $pos = $pos->map(function ($p) use (&$seenPoProposalIds, $poSiblingCounts) {
            $isTableRow = true;
            if ($p['budgetProposalId']) {
                $isTableRow = !in_array($p['budgetProposalId'], $seenPoProposalIds, true);
                if ($isTableRow) {
                    $seenPoProposalIds[] = $p['budgetProposalId'];
                }
            }
            return array_merge($p, [
                'isTableRow'   => $isTableRow,
                'siblingCount' => $p['budgetProposalId'] ? ($poSiblingCounts[$p['budgetProposalId']] ?? 1) : 1,
            ]);
        })->all();

        // AOCs that are fully signed but don't have a PO yet
        $eligibleAocs = AbstractOfCanvass::with('purchaseRequest.office')
            ->where('signatory_stage', 'fully_signed')
            ->whereDoesntHave('purchaseOrder')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($aoc) => [
                'id'      => $aoc->id,
                'code'    => $aoc->code ?? 'AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT),
                'office'  => $aoc->purchaseRequest->office?->code ?? '—',
                'title'   => $aoc->purchaseRequest->title,
                'amount'  => (float) $aoc->purchaseRequest->total_amount,
                'issueUrl'=> route('procurement-office.po.issue', $aoc->id),
            ])
            ->all();

        return ['purchaseOrders' => $pos, 'eligibleAocs' => $eligibleAocs];
    }

    /**
     * Shared shape for one PO row as the frontend needs it — used both for
     * the full list/refresh payload and for the single freshly-issued PO
     * returned by issuePo(), so the list can append it in place instead of
     * reloading the page.
     */
    private function mapPoForFrontend(PurchaseOrder $po): array
    {
        $chain   = PurchaseOrder::statusChain();
        $pr      = $po->abstractOfCanvass?->purchaseRequest;
        $current = array_search($po->status, $chain);
        $current = $current === false ? 0 : $current;

        return [
            'receiptUrl'   => ($receipt = $po->documents->firstWhere('document_type', 'payment_receipt'))
                ? \Illuminate\Support\Facades\Storage::url($receipt->file_path)
                : null,
            'id'           => $po->id,
            // No placeholder number synthesized here on purpose — a PO with
            // nothing uploaded yet genuinely has no number, and showing a
            // fake one would misleadingly suggest it does.
            'poNumber'     => $po->po_number ?? '—',
            'aocCode'      => $po->abstractOfCanvass->code ?? '—',
            'prNumber'     => $pr->number ?? '—',
            'office'       => $pr?->office?->code ?? '—',
            'title'        => $pr->title ?? '—',
            // Inherited from the parent PR via its AOC. Solo-item defaults
            // here (isTableRow/siblingCount) are for mapPoForFrontend()'s
            // other caller (issuePo(), a single-PO response) — the real
            // grouping is only computed in purchaseOrdersData(), which needs
            // the full list.
            'budgetProposalId'   => $pr?->budget_proposal_id,
            'budgetProposalCode' => $pr?->budgetProposal?->code,
            'isTableRow'         => true,
            'siblingCount'       => 1,
            'supplier'     => $po->supplier_name,
            'supplierAddress' => $po->supplier_address ?? '—',
            'totalAmount'  => (float) $po->total_amount,
            'remarks'      => $po->remarks ?: '—',
            'status'       => $po->status,
            'statusLabel'  => $po->status_label,
            'nextStatus'   => $po->nextStatus(),
            'deliveryChain' => collect($chain)->map(function ($key, $idx) use ($current, $po, $chain) {
                // 'paid' is the terminal state — once reached, there's nothing left
                // "in progress", so every step (including 'paid' itself) reads as
                // done rather than leaving the last dot stuck on the "active" style.
                $isComplete = $current === count($chain) - 1;
                // status stays 'issued' as a placeholder default from the moment
                // the PO record is created (see issuePo()), well before signing
                // is even done — updatePoStatus() already refuses to advance it
                // any further until signatory_stage is 'fully_signed', so the
                // display shouldn't show delivery progress that early either.
                // Every step reads as not-yet-started until then.
                $signingDone = $po->signatory_stage === 'fully_signed';
                return [
                    'key'    => $key,
                    'label'  => (clone $po)->fill(['status' => $key])->status_label,
                    'status' => !$signingDone ? 'pending' : ($isComplete ? 'done' : ($idx < $current ? 'done' : ($idx === $current ? 'active' : 'pending'))),
                ];
            })->values()->all(),
            'issuedAt'     => $po->issued_at?->format('M d, Y') ?? '—',
            'expectedDate' => $po->expected_delivery_date?->format('M d, Y') ?? '—',
            'paidAt'       => $po->paid_at?->format('M d, Y') ?? null,
            'updateUrl'    => route('procurement-office.po.update-status', $po->id),
            'signatoryStage'   => $po->signatory_stage ?? 'draft',
            'signatoryLabel'   => $po->signatory_label,
            'statusBucket'     => match ($po->signatory_stage ?? 'draft') {
                'fully_signed' => 'fully_signed',
                'draft'        => 'draft',
                default        => 'in_progress',
            },
            'nextStage'        => $po->nextSignatoryStage(),
            'currentStageType' => $po->stageMetaFor($po->signatory_stage)['type'] ?? 'signature',
            'nextStageLabel'   => $po->stageMetaFor($po->nextSignatoryStage())['label'] ?? null,
            'stageMeta'        => $po->resolvedStageMeta(),
            'advanceUrl'       => route('procurement-office.po.advance', $po->id),
            'returnUrl'        => route('procurement-office.po.return', $po->id),
            'uploadUrl'        => route('procurement-office.po.upload', $po->id),
            'extractUrl'       => route('procurement-office.po.extract', $po->id),
            'pdfFile'          => $po->file_path,
            'alobsNo'          => $po->alobs_no ?: '—',
            'fundSource'       => $po->fund_source ?: '—',
            'createdAtRaw'     => $po->created_at->toIso8601String(),
            'signatureLogs'    => $po->signatureLogs->map(fn ($l) => [
                'display' => $po->describeSignatureLog($l),
                'by'      => $l->signedBy?->name ?? '—',
                'at'      => $l->signed_at?->format('M d, Y g:i A') ?? '—',
                'atRaw'   => $l->signed_at?->toIso8601String(),
                'attachments' => $l->attachments->map(fn ($a) => [
                    'filename' => $a->original_filename,
                    'isImage'  => str_starts_with($a->mime_type ?? '', 'image/'),
                    'url'      => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                        'signature-attachment.show', now()->addDay(), ['id' => $a->id]
                    ),
                ])->all(),
            ])->all(),
        ];
    }

    public function issuePo(Request $request, AbstractOfCanvass $aoc): JsonResponse
    {
        if ($aoc->signatory_stage !== 'fully_signed') {
            return response()->json(['error' => 'AOC must be fully signed before issuing a PO.'], 422);
        }

        if ($aoc->purchaseOrder) {
            return response()->json(['error' => 'A Purchase Order already exists for this AOC.'], 422);
        }

        $request->validate([
            'supplier_name'          => 'required|string|max:255',
            'supplier_address'       => 'nullable|string',
            'total_amount'           => 'required|numeric|min:0',
            'expected_delivery_date' => 'nullable|date',
        ]);

        $po = PurchaseOrder::create([
            'abstract_of_canvass_id' => $aoc->id,
            'created_by_user_id'     => auth()->id(),
            // Left unset on purpose — this is just the row that lets the PO
            // be routed for signatures; its real number only exists once
            // uploadPurchaseOrder() reads one off the actual signed document.
            'supplier_name'          => $request->input('supplier_name'),
            'supplier_address'       => $request->input('supplier_address'),
            'total_amount'           => $request->input('total_amount'),
            'status'                 => 'issued',
            'signatory_stage'        => 'draft',
            'issued_at'              => now(),
            'expected_delivery_date' => $request->input('expected_delivery_date'),
            // Known as soon as the PR is known — doesn't need to wait for the
            // signed PO document to be uploaded (see resolveFundSourceFromPpmp()).
            'fund_source'            => $this->resolveFundSourceFromPpmp($aoc->purchaseRequest),
        ]);

        NotificationService::prStatusUpdated($aoc->purchaseRequest);

        $po->load(['abstractOfCanvass.purchaseRequest.office', 'createdBy', 'paidBy', 'documents', 'signatureLogs.signedBy', 'signatureLogs.attachments']);

        return response()->json(['success' => true, 'po' => $this->mapPoForFrontend($po)]);
    }

    public function updatePoStatus(Request $request, PurchaseOrder $po): JsonResponse
    {
        if ($po->signatory_stage !== 'fully_signed') {
            return response()->json(['error' => 'PO must be fully signed before updating delivery status.'], 422);
        }

        $next = $po->nextStatus();
        if (!$next || $po->status === 'paid') {
            return response()->json(['error' => 'No further status available.'], 422);
        }

        // Payment steps belong to Accounting (start processing) and the Cashier (receipt → paid)
        if ($next === 'processing_payment') {
            return response()->json(['error' => 'Delivery is complete — the Accounting Office takes over payment processing from here.'], 422);
        }

        $po->update([
            'status'  => $next,
            'remarks' => $request->input('remarks'),
        ]);
        $po->abstractOfCanvass?->purchaseRequest?->clearTrackingOverride();

        return response()->json([
            'success'     => true,
            'status'      => $next,
            'statusLabel' => $po->fresh()->status_label,
        ]);
    }

    public function advancePoStage(Request $request, PurchaseOrder $po, SignatoryActionService $signatory): JsonResponse
    {
        $result = $signatory->advance($po, $request->input('remarks'));

        return response()->json($result, $result['status'] ?? 200);
    }

    public function returnPo(Request $request, PurchaseOrder $po, SignatoryActionService $signatory): JsonResponse
    {
        $request->validate(['remarks' => 'required|string|max:1000']);

        return response()->json($signatory->returnOneStep($po, $request->input('remarks')));
    }

    /** Re-run signature detection for a log whose photo is pending/failed. */
    public function reprocessSignaturePhoto(Request $request, string $docType, int $logId, SignatoryActionService $signatory): JsonResponse
    {
        [$log, $doc] = match ($docType) {
            'pr'    => [($l = PrSignatureLog::findOrFail($logId)), $l->purchaseRequest],
            'aoc'   => [($l = AocSignatureLog::findOrFail($logId)), $l->abstractOfCanvass],
            'po'    => [($l = PoSignatureLog::findOrFail($logId)), $l->purchaseOrder],
            default => abort(404),
        };

        $result = $signatory->reprocessPhoto($doc, $log);

        return response()->json($result, $result['status'] ?? 200);
    }

    public function procurementReports(Request $request): View
    {
        // Every table on this page can be narrowed to one office — "all
        // offices" (no filter) stays the default so opening the page fresh
        // always shows the whole picture; a report reader only reaches for
        // this once they already know which office they're checking on.
        $officeCode = $request->query('office') ?: null;

        $quarterlyRows      = $this->buildQuarterlyAccomplishment($officeCode);
        $prReportRows       = $this->buildPrReportRows($officeCode);
        $ppmpValidationRows = $this->buildPpmpValidationRows($officeCode);

        return view('prism.procurement-office.procurement-reports', $this->withCommon('procurement-reports', [
            'pageTitle'          => 'Procurement Reports',
            'quarterlyRows'      => $quarterlyRows,
            'completedPurchases' => $prReportRows['completed'],
            'delayedItems'       => $prReportRows['delayed'],
            'ppmpValidationRows' => $ppmpValidationRows,
            'offices'            => Office::orderBy('code')->get(['id', 'code', 'name']),
            'selectedOffice'     => $officeCode,
            'exportUrl'          => route('procurement-office.procurement-reports.export', $officeCode ? ['office' => $officeCode] : []),
        ]));
    }

    /** CSV of the same four report tables procurementReports() renders, honoring the same office filter. */
    public function exportProcurementReportsCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $officeCode = $request->query('office') ?: null;

        $quarterlyRows      = $this->buildQuarterlyAccomplishment($officeCode);
        $prReportRows       = $this->buildPrReportRows($officeCode);
        $ppmpValidationRows = $this->buildPpmpValidationRows($officeCode);

        $filename = 'procurement-report-' . ($officeCode ?: 'all-offices') . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($quarterlyRows, $prReportRows, $ppmpValidationRows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads ₱/accented text correctly

            fputcsv($out, ['Quarterly Accomplishment — Items Targeted vs Procured']);
            fputcsv($out, ['Office', 'Quarter', 'Targeted', 'Procured', 'Completion Rate']);
            foreach ($quarterlyRows as $r) {
                fputcsv($out, [$r['office'], $r['quarter'], $r['targeted'], $r['procured'], $r['completionRate'] . '%']);
            }
            fputcsv($out, []);

            fputcsv($out, ['Completed Purchases']);
            fputcsv($out, ['Office', 'Item', 'PR No.', 'Date', 'Amount']);
            foreach ($prReportRows['completed'] as $r) {
                fputcsv($out, [$r['office'], $r['item'], $r['prNumber'], $r['completedDate'], $r['amount']]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Delayed Items']);
            fputcsv($out, ['Office', 'Item', 'PR No.', 'Reason']);
            foreach ($prReportRows['delayed'] as $r) {
                fputcsv($out, [$r['office'], $r['item'], $r['prNumber'], $r['reason']]);
            }
            fputcsv($out, []);

            fputcsv($out, ['PPMP Validation — Planned vs. Actually Purchased']);
            fputcsv($out, ['Office', 'PPMP Item', 'Planned Qty', 'Planned Amount', 'Matched PR Item', 'Purchased Qty', 'Purchased Amount', 'Tracking Status', 'Flag']);
            foreach ($ppmpValidationRows as $r) {
                fputcsv($out, [
                    $r['office'], $r['item'], $r['plannedQty'], $r['plannedTotal'],
                    $r['matchedItem'] ?? '—', $r['purchasedQty'] ?? '—', $r['purchasedTotal'] ?? '—',
                    $r['trackingStatus']['label'], $r['flag'],
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Completed-purchases and delayed-items tables — shared by
     * procurementReports() and exportProcurementReportsCsv() so the page
     * and its export are always built from exactly the same rows.
     */
    private function buildPrReportRows(?string $officeCode = null): array
    {
        // Same "fully signed" journey used everywhere else on this page's
        // report-driven data, plus a real completion signal (paid = actually
        // done) instead of the raw `status` column, which Procurement itself
        // almost never sets to the literal value 'completed'.
        $allPrs = PurchaseRequest::with(['office', 'abstractOfCanvass.purchaseOrder'])
            ->when($officeCode, fn ($q) => $q->whereHas('office', fn ($q2) => $q2->where('code', $officeCode)))
            ->get();

        $completedPurchases = $allPrs
            ->filter(fn ($pr) => $pr->lifecycleBucket() === 'completed')
            ->sortByDesc('updated_at')
            ->take(10)
            ->map(fn ($pr) => [
                'office'        => $pr->office?->code ?? '—',
                'item'          => $pr->title,
                'prNumber'      => $pr->number ?? '—',
                'completedDate' => $pr->updated_at->format('M d, Y'),
                'amount'        => (float) $pr->total_amount,
            ])
            ->values()
            ->all();

        // "Delayed" here means still open and past a reasonable turnaround
        // time — the same overdue definition used on the Dashboard's Urgent
        // PRs list — not the raw `status` column, which never actually holds
        // the literal value 'delayed' in real data.
        $overdueThresholdDays = 30;
        $delayedItems = $allPrs
            ->filter(fn ($pr) =>
                $pr->signingStatusBucket() !== 'completed'
                && $pr->submitted_at
                && $pr->submitted_at->diffInDays(now()) > $overdueThresholdDays
            )
            ->sortByDesc(fn ($pr) => $pr->submitted_at->diffInDays(now()))
            ->map(fn ($pr) => [
                'office'   => $pr->office?->code ?? '—',
                'item'     => $pr->title,
                'prNumber' => $pr->number ?? '—',
                'reason'   => $pr->remarks ?: ((int) $pr->submitted_at->diffInDays(now()) . " days pending — past the {$overdueThresholdDays}-day target."),
            ])
            ->values()
            ->all();

        return ['completed' => $completedPurchases, 'delayed' => $delayedItems];
    }

    /**
     * Real per-office, per-quarter targeted-vs-procured accomplishment,
     * built from the same endorsed/approved PPMP items and office+name PR
     * match used by buildPpmpValidationRows() — grouped by each item's own
     * target_quarter instead of splitting PR totals evenly across quarters.
     * "Procured" means the matched PR's full journey actually reached paid.
     */
    private function buildQuarterlyAccomplishment(?string $officeCode = null): array
    {
        $items = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->when($officeCode, fn ($q) => $q->whereHas('budgetProposal.office', fn ($q2) => $q2->where('code', $officeCode)))
            ->whereNotNull('target_quarter')
            ->get();

        $officeIds     = $items->pluck('budgetProposal.office_id')->filter()->unique()->values();
        $prItemMatches = $this->matchPrItemsByOfficeAndName($officeIds);

        return $items
            ->groupBy(fn ($item) => ($item->budgetProposal?->office?->code ?? '—') . '|' . $item->target_quarter)
            ->map(function ($group) use ($prItemMatches) {
                $first    = $group->first();
                $targeted = $group->count();
                $procured = $group->filter(function ($item) use ($prItemMatches) {
                    $officeId = $item->budgetProposal?->office_id;
                    $matched  = $prItemMatches->get($officeId . '|' . strtolower(trim($item->name)));

                    return $matched?->purchaseRequest?->lifecycleBucket() === 'completed';
                })->count();

                return [
                    'office'         => $first->budgetProposal?->office?->code ?? '—',
                    'quarter'        => $first->target_quarter,
                    'targeted'       => $targeted,
                    'procured'       => $procured,
                    'completionRate' => $targeted > 0 ? round(($procured / $targeted) * 100) : 0,
                ];
            })
            ->sortBy(fn ($r) => $r['office'] . $r['quarter'])
            ->values()
            ->all();
    }

    /**
     * Compare each endorsed/approved PPMP item against what was actually
     * requested for it (via the best-effort office+name match), flagging
     * quantity/amount discrepancies so Procurement can spot-check that
     * purchases matched the plan — not just track process stage.
     */
    private function buildPpmpValidationRows(?string $officeCode = null): array
    {
        $items = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->when($officeCode, fn ($q) => $q->whereHas('budgetProposal.office', fn ($q2) => $q2->where('code', $officeCode)))
            ->get();

        $officeIds     = $items->pluck('budgetProposal.office_id')->filter()->unique()->values();
        $prItemMatches = $this->matchPrItemsByOfficeAndName($officeIds);

        return $items->map(function ($item) use ($prItemMatches) {
            $officeId  = $item->budgetProposal?->office_id;
            $matched   = $prItemMatches->get($officeId . '|' . strtolower(trim($item->name)));
            $matchedPr = $matched?->purchaseRequest;

            $plannedQty   = (float) $item->quantity;
            $plannedTotal = (float) $item->estimated_total_cost;

            if (!$matched) {
                return [
                    'office'          => $item->budgetProposal?->office?->code ?? '—',
                    'item'            => $item->name,
                    'plannedQty'      => $plannedQty,
                    'plannedTotal'    => $plannedTotal,
                    'matchedItem'     => null,
                    'purchasedQty'    => null,
                    'purchasedTotal'  => null,
                    'trackingStatus'  => ['key' => 'not_requested', 'label' => 'Not Yet Requested'],
                    'flag'            => 'pending',
                ];
            }

            $purchasedQty   = (float) $matched->quantity;
            $purchasedTotal = (float) $matched->estimated_total_cost;
            $qtyMismatch    = abs($purchasedQty - $plannedQty) > 0.01;
            $overBudget     = $purchasedTotal > $plannedTotal;

            $flag = match (true) {
                $qtyMismatch && $overBudget => 'qty_and_over_budget',
                $qtyMismatch                => 'qty_mismatch',
                $overBudget                 => 'over_budget',
                default                     => 'ok',
            };

            return [
                'office'         => $item->budgetProposal?->office?->code ?? '—',
                'item'           => $item->name,
                'plannedQty'     => $plannedQty,
                'plannedTotal'   => $plannedTotal,
                'matchedItem'    => $matched->name,
                'purchasedQty'   => $purchasedQty,
                'purchasedTotal' => $purchasedTotal,
                'trackingStatus' => $matchedPr?->effectiveTrackingStatus() ?? ['key' => 'unknown', 'label' => '—'],
                'flag'           => $flag,
            ];
        })->all();
    }

    public function uploadPurchaseRequest(Request $request, PurchaseRequest $pr): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $year = now()->year;
        $slug = Str::slug($pr->number ?? 'pr-' . $pr->id);
        $name = $slug . '-' . now()->format('Ymd-His') . '.pdf';
        $path = $request->file('file')->storeAs("purchase-requests/{$year}", $name, 'public');

        $newStatus = $pr->status === 'pending' ? 'in_progress' : $pr->status;

        $pr->update([
            'file_path'   => $path,
            'uploaded_at' => now(),
            'status'      => $newStatus,
        ]);

        NotificationService::prUploaded($pr);

        return response()->json([
            'success'  => true,
            'filePath' => $path,
            'status'   => ucwords(str_replace('_', ' ', $newStatus)),
        ]);
    }

    /**
     * Preview-only counterpart to uploadAbstractOfCanvass() — reads and
     * validates a candidate AOC file exactly the same way, but saves
     * nothing. Lets the upload button show a review (pass or fail) before
     * the file is actually attached, the same way PR Step 2/3 and the
     * canvass-quotation upload both review before committing, rather than
     * uploading immediately and only finding out after the fact.
     */
    public function extractAocValidation(Request $request, AbstractOfCanvass $aoc, DocumentValidationService $validator): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $text             = $this->readPdfText($request->file('file'));
        $responsiveDealer = $this->extractResponsiveDealer($text);
        $validation       = $this->validateAocDocument($aoc, $text, $validator);

        return response()->json([
            'success'          => true,
            'responsiveDealer' => $responsiveDealer,
            'validation'       => $validation,
        ]);
    }

    /**
     * Shared by extractAocValidation() (preview, saves nothing) and
     * uploadAbstractOfCanvass() (the real thing) so the same document only
     * ever gets judged one way. Returns null — not a failing verdict — when
     * none of the PR's quotations are text PDFs to check against; see the
     * leniency note on uploadAbstractOfCanvass() below.
     */
    private function validateAocDocument(AbstractOfCanvass $aoc, string $text, DocumentValidationService $validator): ?array
    {
        $quotations = [];
        foreach ($aoc->purchaseRequest->documents()->where('document_type', 'canvass_quotation')->get() as $doc) {
            $isPdf = $doc->mime_type === 'application/pdf' || str_ends_with(strtolower($doc->file_path), '.pdf');
            if (!$isPdf) {
                continue; // can't read prices out of a phone-photo quotation
            }
            $qItems = $this->parseQuotationItems($this->readStoredPdfText($doc->file_path));
            if ($qItems) {
                $quotations[] = ['supplier' => $doc->title, 'items' => $qItems];
            }
        }

        if (!$quotations) {
            return null;
        }

        $aocItems         = $this->parseAbstractOfCanvassItems($text);
        $responsiveDealer = $this->extractResponsiveDealer($text);

        return $validator->validateAocAgainstQuotations($aocItems, $responsiveDealer, $quotations);
    }

    /**
     * Upload/re-upload the scanned, physically-signed AOC document.
     *
     * Same "scan it before it's attached" principle as PR Step 2: the AOC is
     * meant to be a faithful summary of the supplier quotations already on
     * file for this PR, condensed for easier price comparison, so before the
     * file is saved its own item table is checked against them — every price
     * it states has to be traceable to an actual quotation, and its declared
     * Responsive Dealer has to be one of the suppliers who actually
     * submitted one. A document that fails that check is refused outright,
     * the same way an unapproved PR item is. Re-uploading an existing AOC's
     * document goes through this exact same check — there is no separate,
     * unvalidated path for that, on either the frontend (see the review
     * modal wired to extractUrl above) or here.
     *
     * Skipped (not blocked) when none of this PR's quotations are text PDFs
     * to begin with (e.g. every supplier's quotation was a phone-photo
     * image) — there is nothing to check against, the same leniency already
     * applied to quotation uploads themselves for that case.
     */
    public function uploadAbstractOfCanvass(Request $request, AbstractOfCanvass $aoc, DocumentValidationService $validator): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file = $request->file('file');
        $text = $this->readPdfText($file);
        $pr   = $aoc->purchaseRequest;

        $responsiveDealer = $this->extractResponsiveDealer($text);
        $validation       = $this->validateAocDocument($aoc, $text, $validator);

        if ($validation && $validation['verdict'] !== DocumentValidation::PASSED) {
            return response()->json([
                'error'      => $validation['summary'],
                'validation' => $validation,
            ], 422);
        }

        $year = now()->year;
        $slug = Str::slug($aoc->code ?? 'aoc-' . $aoc->id);
        $name = $slug . '-' . now()->format('Ymd-His') . '.pdf';
        $path = $file->storeAs("abstract-of-canvass/{$year}", $name, 'public');

        $aoc->update([
            'file_path'   => $path,
            'uploaded_at' => now(),
        ]);

        // The AOC's "Responsive Dealer" declaration is the authoritative winning
        // supplier — once available, it supersedes whichever quotation
        // Procurement happened to attach first (see mapAocForFrontend()) for
        // any later reads, e.g. pre-filling the Issue PO form's supplier name.
        // Stored on the AOC itself rather than on the quotation documents,
        // since a PR can now carry several suppliers' quotations at once.
        if ($responsiveDealer) {
            $aoc->update(['winning_supplier_name' => $responsiveDealer]);
        }

        if ($validation) {
            $validator->record($aoc, $pr, DocumentValidation::PAIR_CANVASS_AOC, $validation);
        }

        $aoc->load(['purchaseRequest.office', 'purchaseRequest.items', 'purchaseRequest.documents', 'signatureLogs.signedBy', 'signatureLogs.attachments', 'purchaseOrder']);

        return response()->json(['success' => true, 'aoc' => $this->mapAocForFrontend($aoc)]);
    }

    /**
     * Preview-only counterpart to uploadPurchaseOrder() — reads and
     * validates a candidate PO file exactly the same way, but saves
     * nothing. Lets the upload button show a review (pass or fail) before
     * the file is actually attached, the same way the AOC upload does.
     */
    public function extractPoValidation(Request $request, PurchaseOrder $po, DocumentValidationService $validator): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $text = $this->readPdfText($request->file('file'));

        return response()->json(['success' => true] + $this->validatePoDocument($po, $text, $validator));
    }

    /**
     * Shared by extractPoValidation() (preview, saves nothing) and
     * uploadPurchaseOrder() (the real thing) so the same document only ever
     * gets judged one way.
     *
     * Two independent things are checked:
     *   - the PO's own number, read off the document — issuePo() leaves it
     *     unset when the row is first created (nothing signed exists yet at
     *     that point), so this is the only place it's ever known. A
     *     document with no readable "P.O. No." value, or one already used
     *     by a different PO, fails this half.
     *   - its contents against the AOC (see validatePoAgainstAoc()) —
     *     skipped, not failed, when the AOC's own file isn't on hand or
     *     wasn't text-readable, the same leniency the AOC upload applies to
     *     its quotations.
     */
    private function validatePoDocument(PurchaseOrder $po, string $text, DocumentValidationService $validator): array
    {
        $poNumber       = $this->extractPoNumber($text);
        $poNumberOk     = true;
        $poNumberReason = null;

        if (!$poNumber) {
            $poNumberOk     = false;
            $poNumberReason = 'Could not read a "P.O. No." from this document. Re-upload a text-based PDF of the actual signed PO form.';
        } elseif (PurchaseOrder::where('po_number', $poNumber)->where('id', '!=', $po->id)->exists()) {
            $poNumberOk     = false;
            $poNumberReason = "A Purchase Order numbered \"{$poNumber}\" already exists.";
        }

        $aoc              = $po->abstractOfCanvass;
        $externalProvider = $this->extractExternalProvider($text);
        $validation       = null;

        if ($aoc && $aoc->file_path) {
            $aocText  = $this->readStoredPdfText($aoc->file_path);
            $aocItems = $this->parseAbstractOfCanvassItems($aocText);

            if ($aocItems) {
                $poItems          = $this->parsePoItems($text);
                $responsiveDealer = $this->extractResponsiveDealer($aocText);
                $validation       = $validator->validatePoAgainstAoc($poItems, $externalProvider, $aocItems, $responsiveDealer);
            }
        }

        return [
            'poNumber'       => $poNumber,
            'poNumberOk'     => $poNumberOk,
            'poNumberReason' => $poNumberReason,
            'validation'     => $validation,
        ];
    }

    /**
     * Upload/re-upload the scanned, physically-signed PO document. Covers
     * the first upload and any re-upload alike — there is no separate,
     * unvalidated shortcut for either; both go through validatePoDocument()
     * the same way extractPoValidation() (the frontend's review-before-
     * confirm preview) already showed.
     */
    public function uploadPurchaseOrder(Request $request, PurchaseOrder $po, DocumentValidationService $validator): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file = $request->file('file');
        $text = $this->readPdfText($file);

        ['poNumber' => $poNumber, 'poNumberOk' => $poNumberOk, 'poNumberReason' => $poNumberReason, 'validation' => $validation]
            = $this->validatePoDocument($po, $text, $validator);

        if (!$poNumberOk) {
            return response()->json(['error' => $poNumberReason], 422);
        }
        if ($validation && $validation['verdict'] !== DocumentValidation::PASSED) {
            return response()->json([
                'error'      => $validation['summary'],
                'validation' => $validation,
            ], 422);
        }

        $year = now()->year;
        $slug = Str::slug($poNumber);
        $name = $slug . '-' . now()->format('Ymd-His') . '.pdf';
        $path = $file->storeAs("purchase-orders/{$year}", $name, 'public');

        $poFields = $this->extractPoFundingFields($text);

        $po->update([
            'po_number'   => $poNumber,
            'file_path'   => $path,
            'uploaded_at' => now(),
            'alobs_no'    => $poFields['alobsNo'] ?? $po->alobs_no,
            // Falls back here too in case issuePo() couldn't resolve it yet
            // (e.g. the PPMP item's Source of Funds was filled in afterward)
            // — never overwrites an already-known value.
            'fund_source' => $po->fund_source ?: $this->resolveFundSourceFromPpmp($po->abstractOfCanvass?->purchaseRequest),
        ]);

        if ($validation) {
            $validator->record($po, $po->abstractOfCanvass, DocumentValidation::PAIR_AOC_PO, $validation);
        }

        // Full mapPoForFrontend() payload, not just the fields this endpoint
        // itself changed — signatoryLabel in particular now reads "PO
        // Created" instead of "PO to be Created" the moment file_path is
        // set (see PurchaseOrder::getSignatoryLabelAttribute()), and the
        // frontend needs that to update the row's badge without a reload.
        $po->load(['abstractOfCanvass.purchaseRequest.office', 'createdBy', 'paidBy', 'documents', 'signatureLogs.signedBy', 'signatureLogs.attachments']);

        return response()->json(['success' => true, 'po' => $this->mapPoForFrontend($po)]);
    }

    /** Best-effort PDF text-layer read — scanned/image-only uploads just yield ''. */
    private function readPdfText(\Illuminate\Http\UploadedFile $file): string
    {
        try {
            $parser = new PdfParser();
            return $parser->parseContent($file->get())->getText();
        } catch (\Exception $e) {
            return '';
        }
    }

    /** Same as readPdfText(), for a file already on the public disk (e.g. a previously-uploaded quotation) rather than one just submitted with this request. */
    private function readStoredPdfText(string $diskPath): string
    {
        $contents = Storage::disk('public')->get($diskPath);
        if ($contents === null) {
            return ''; // file missing from disk (e.g. a test fixture's fake upload)
        }

        try {
            $parser = new PdfParser();
            return $parser->parseContent($contents)->getText();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * The Abstract of Canvass form's recommendation paragraph usually names
     * the winning bidder the same way: "...prices offered by <Supplier>
     * is/are considered reasonable and most advantageous...". That blank is
     * often left unfilled, though (just underscores), even when the item
     * table's own RESPONSIVE DEALER column does name the winning supplier —
     * in that case, fall back to the table instead of declaring the blank
     * placeholder itself as the dealer name. Best-effort only.
     */
    private function extractResponsiveDealer(string $text): ?string
    {
        $normalized = preg_replace('/\s+/', ' ', $text);
        if (preg_match('/offered by (.+?) is\s*\/?\s*are considered/i', $normalized, $m)) {
            $declared = trim($m[1]);
            if ($declared !== '' && !preg_match('/^_+$/', $declared)) {
                return $declared;
            }
        }

        foreach ($this->parseAbstractOfCanvassItems($text) as $item) {
            $dealer = trim($item['responsiveDealer'] ?? '');
            if ($dealer !== '') {
                return $dealer;
            }
        }

        return null;
    }

    /**
     * The Purchase Order form's "Funds Available" box lists the ALOBS No. on
     * its own labeled line. Best-effort only — a scanned, hand-filled PO may
     * have no text layer at all for this value.
     *
     * Fund Source is deliberately NOT read from this document — that field
     * on the PO form is routinely left blank there (Accounting fills the
     * ALOBS box, not this one); the actual source of funds was already
     * declared per item back in the PR's own PPMP, so it's resolved from
     * there instead — see resolveFundSourceFromPpmp().
     */
    private function extractPoFundingFields(string $text): array
    {
        $result = ['alobsNo' => null];

        // Colon is required (not "?") and the capture allows zero length — this
        // keeps the match confined to the same line and stops the regex engine
        // from "giving back" the colon into the capture group when the field is
        // left blank on the form (which would otherwise wrongly extract ":").
        if (preg_match('/ALOBS No\.?[ \t]*:[ \t]*([^\r\n]*)/i', $text, $m, PREG_OFFSET_CAPTURE) && trim($m[1][0]) !== '') {
            $result['alobsNo'] = trim($m[1][0]);
        } elseif (preg_match('/ALOBS No\.?[ \t]*:?[ \t]*$/im', $text, $m, PREG_OFFSET_CAPTURE)) {
            // Some PDFs extract the label and its filled-in value on separate
            // lines (the value was added as a layer on top of the template
            // rather than typed inline) — take the next non-blank line as a
            // fallback, unless it's actually the start of the following
            // labeled row rather than a value.
            $after = substr($text, $m[0][1] + strlen($m[0][0]));
            if (preg_match('/\s*([^\r\n]+)/u', $after, $nm) && trim($nm[1]) !== '' && !preg_match('/^(Amount|Fund Source)\b/i', trim($nm[1]))) {
                $result['alobsNo'] = trim($nm[1]);
            }
        }

        return $result;
    }

    /**
     * The PO's Fund Source is the "Source of Funds" already declared per
     * item in the PR's own PPMP (Column 9 of the PPMP document — see
     * BudgetProposalItem::source_of_fund), matched by item name the same way
     * matchPrItemsByOfficeAndName() matches in the other direction. Not read
     * off the PO document itself — see extractPoFundingFields() above.
     */
    private function resolveFundSourceFromPpmp(?PurchaseRequest $pr): ?string
    {
        $budgetItems = $pr?->budgetProposal?->items;
        if (!$budgetItems || $budgetItems->isEmpty()) {
            return null;
        }

        $byName = $budgetItems->keyBy(fn ($item) => strtolower(trim($item->name)));

        $sources = $pr->items
            ->map(fn ($item) => $byName->get(strtolower(trim($item->name)))?->source_of_fund)
            ->filter()
            ->unique()
            ->values();

        return $sources->isEmpty() ? null : $sources->implode(', ');
    }

    /**
     * The Purchase Order form's External Provider is the supplier name — read
     * the same way "Name of Project:"/"Department /Office:" are read off the
     * PR form (see parseLabeledBlock()), between the "External Provider:"
     * label and the next field's own label.
     */
    private function extractExternalProvider(string $text): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        return $this->parseLabeledBlock($text, 'External Provider:', 'P.O. No.:');
    }

    /**
     * The PO's own number, read the same way as any other labeled field on
     * this form — between "P.O. No.:" and the next field's own label
     * ("Address:", right below it). This is the PO's real identity, only
     * knowable once an actual signed document exists — see
     * uploadPurchaseOrder(), which is the only place it's ever set; issuePo()
     * deliberately leaves it unset when the row is first created.
     */
    private function extractPoNumber(string $text): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        return $this->parseLabeledBlock($text, 'P.O. No.:', 'Address:');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Best-effort match from a BudgetProposalItem (PPMP) to the downstream PR
     * item eventually raised for it. No stored link exists yet between the two
     * (the BSU PDF import flow doesn't record annual_procurement_plan_item_id),
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

    private function prStatusLabel(string $status): string
    {
        return match ($status) {
            'new'                    => 'New',
            'approved_pr_received'   => 'Approved PR Received',
            'forwarded_to_bac'       => 'Approved PR Received – Forwarded to BAC',
            'canvassing'             => 'Canvassing',
            'abstract_of_canvass_made' => 'Abstract of Canvass Made',
            'for_po'                 => 'For PO',
            'po_made'                => 'PO Made',
            'po_confirmed'           => 'PO Confirmed',
            'for_alobs'              => 'For ALOBS',
            'forwarded_to_rgo'       => 'Approved PR Received – Forwarded to RGO',
            'forwarded_to_end_user'  => 'Approved PR Received – Forwarded to End-User',
            'for_reimbursement'      => 'For Reimbursement',
            'for_consolidation'      => 'For CONSOLIDATION',
            'pr_denied'              => 'PR Denied',
            'cancelled'              => 'Cancelled',
            'cancelled_system_error' => 'Cancelled – System Error',
            default                  => ucwords(str_replace('_', ' ', $status)),
        };
    }

    private function withCommon(string $activeProcurementPage, array $data): array
    {
        return array_merge([
            'activeRole'       => 'procurement-office',
            'activeModulePage' => $activeProcurementPage,
            'brandHref'        => route('procurement-office.dashboard'),
            'roleLabel'        => 'Procurement Office',
            'roleInitials'     => 'PO',
            'roleNavigation'   => \App\Support\PrismNav::roleNavigation(),
            'moduleNavLabel'   => 'Procurement Office pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',                   'label' => 'Dashboard',                  'href' => route('procurement-office.dashboard'),                   'icon' => 'layout-dashboard'],
                ['slug' => 'annual-procurement-plan',     'label' => 'Annual Procurement Plan',     'href' => route('procurement-office.annual-procurement-plan'),     'icon' => 'calendar-stats'],
                ['slug' => 'purchase-request-management', 'label' => 'Purchase Requests',           'href' => route('procurement-office.purchase-request-management'), 'icon' => 'receipt'],
                ['slug' => 'canvassing',                  'label' => 'Canvassing',                  'href' => route('procurement-office.canvassing'),                  'icon' => 'clipboard-list'],
                ['slug' => 'abstract-of-canvass',         'label' => 'Abstract of Canvass',         'href' => route('procurement-office.abstract-of-canvass'),         'icon' => 'file-text'],
                ['slug' => 'purchase-orders',             'label' => 'Purchase Orders',             'href' => route('procurement-office.purchase-orders'),             'icon' => 'shopping-cart'],
                ['slug' => 'procurement-reports',         'label' => 'Reports',                     'href' => route('procurement-office.procurement-reports'),         'icon' => 'trending-up'],
            ],
        ], $data);
    }
}
