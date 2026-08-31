<?php

namespace App\Http\Controllers;

use App\Models\AbstractOfCanvass;
use Smalot\PdfParser\Parser as PdfParser;
use App\Models\AocSignatureLog;
use App\Models\BudgetProposalItem;
use App\Models\DocumentUpload;
use App\Models\Office;
use App\Models\PoSignatureLog;
use App\Models\ProcurementStatusUpdate;
use App\Models\PrSignatureLog;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
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
    public function dashboard(): View
    {
        // Every bucket below is derived from PurchaseRequest::signingStatusBucket()
        // (signatory_stage + file_path), never the raw `status` column — that
        // column only ever holds granular values Procurement Office itself
        // writes ('new', 'for_alobs', 'po_confirmed', ...); the simple values
        // this dashboard used to filter on ('completed', 'pending', 'delayed')
        // are never actually written by any real code path.
        $totalPrsReceived = PurchaseRequest::count();
        $allPrs            = PurchaseRequest::with('office')->get();
        $bucketCounts       = $allPrs->countBy(fn ($pr) => $pr->signingStatusBucket());

        $prsInProgress = $bucketCounts['in_progress'] ?? 0;
        $prsCompleted  = $bucketCounts['completed'] ?? 0;

        // No due-date/deadline column exists anywhere on purchase_requests —
        // "overdue" here means the closest real, honest signal available:
        // still not fully signed, N+ days after it was submitted.
        $overdueThresholdDays = 30;
        $overduePrs = $allPrs->filter(fn ($pr) =>
            $pr->signingStatusBucket() !== 'completed'
            && $pr->submitted_at
            && $pr->submitted_at->diffInDays(now()) > $overdueThresholdDays
        )->count();

        $officeStatusGroups = Office::has('purchaseRequests')
            ->with('purchaseRequests')
            ->get()
            ->map(function ($office) {
                $counts = $office->purchaseRequests->countBy(fn ($pr) => $pr->signingStatusBucket());
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

        // "Urgent" = still open (not fully signed) and has been waiting the
        // longest since submission — the only real, honest urgency signal
        // available (no target-quarter or due-date column exists on this
        // model; "Quarter" below is a best-effort parse of the PR number,
        // blank when the number doesn't embed a "-Q#" tag).
        $urgentPrs = $allPrs
            ->filter(fn ($pr) => $pr->signingStatusBucket() !== 'completed' && $pr->submitted_at)
            ->sortBy('submitted_at')
            ->take(8)
            ->map(fn ($pr) => [
                'office'        => $pr->office?->code ?? '—',
                'prNumber'      => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
                'item'          => $pr->title,
                'targetQuarter' => $pr->numberQuarter() ?? '—',
                'daysPending'   => (int) $pr->submitted_at->diffInDays(now()),
                'status'        => ucfirst(str_replace('_', ' ', $pr->signingStatusBucket())),
            ])
            ->values()
            ->all();

        return view('prism.procurement-office.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Procurement Office Dashboard',
            'summary'   => [
                'totalPrsReceived' => $totalPrsReceived,
                'prsInProgress'    => $prsInProgress,
                'prsCompleted'     => $prsCompleted,
                'overduePrs'       => $overduePrs,
                'overdueThresholdDays' => $overdueThresholdDays,
            ],
            'officeStatusGroups' => $officeStatusGroups,
            'urgentPrs'          => $urgentPrs,
            'statusChart'        => [
                'pending'     => $bucketCounts['pending'] ?? 0,
                'in_progress' => $prsInProgress,
                'completed'   => $prsCompleted,
            ],
            'officeVolumeChart' => Office::has('purchaseRequests')
                ->withCount('purchaseRequests')
                ->get()
                ->map(fn ($o) => ['office' => $o->code, 'count' => $o->purchase_requests_count])
                ->sortByDesc('count')
                ->values()
                ->all(),
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
        ]));
    }

    public function purchaseRequestManagementRefresh(): JsonResponse
    {
        return response()->json(['purchaseRequests' => $this->purchaseRequestManagementRows()]);
    }

    private function purchaseRequestManagementRows(): array
    {
        return PurchaseRequest::with(['office', 'statusUpdates' => fn ($q) => $q->latest(), 'signatureLogs.signedBy', 'signatureLogs.attachments', 'abstractOfCanvass.purchaseOrder'])
            ->latest()
            ->get()
            ->map(fn ($pr) => [
                'id'             => $pr->id,
                'office'         => $pr->office?->code ?? $pr->office?->name ?? '—',
                'prNumber'       => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
                'item'           => $pr->title,
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
            ])
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
                'quantity'        => (int) $item->quantity,
                'abcAmount'       => $abc,
                'targetQuarter'   => $item->target_quarter ?? 'Q1',
                'recommendedMode' => $recommendedMode,
                'rationale'       => ProcurementModeService::rationale($abc),
                'procurementMode' => $item->procurement_mode ?? $recommendedMode,
                'isOverridden'    => (bool) $item->is_overridden,
                'overrideReason'  => $item->override_reason ?? '',
                'saveUrl'         => route('procurement-office.annual-procurement-plan.save-mode', $item->id),
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

    // ── Canvassing tab (quotation uploads + stage tracking) ──────────────────

    public function canvassing(): View
    {
        $prs = PurchaseRequest::with(['office', 'documents' => fn ($q) => $q->where('document_type', 'canvass_quotation')])
            ->where('signatory_stage', 'fully_signed')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($pr) => [
                'id'              => $pr->id,
                'prNumber'        => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
                'office'          => $pr->office?->code ?? '—',
                'title'           => $pr->title,
                'canvassingStage'  => $pr->canvassing_stage,
                'canvassingLabel'  => $pr->canvassing_label,
                'readyForAoc'      => $pr->isReadyForAoc(),
                'quotationsLocked' => $pr->abstractOfCanvass !== null,
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
            ])
            ->all();

        return view('prism.procurement-office.canvassing', $this->withCommon('canvassing', [
            'pageTitle'          => 'Canvassing',
            'prs'                => $prs,
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
    public function extractCanvassSupplier(Request $request): JsonResponse
    {
        $request->validate(['document' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240']);

        $file = $request->file('document');
        if ($file->getClientMimeType() !== 'application/pdf' && $file->getClientOriginalExtension() !== 'pdf') {
            return response()->json(['supplierName' => null]);
        }

        $text = '';
        try {
            $parser = new PdfParser();
            $pdf    = $parser->parseContent($file->get());
            $text   = $pdf->getText();
        } catch (\Exception $e) {
            $text = '';
        }

        return response()->json(['supplierName' => $this->parseSupplierNameFromQuotation($text)]);
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
     * The BatStateU-FO-PRO-01 Quotation/Canvass Form always puts the value for
     * a given field on the line right above that field's own label (both sit
     * in the supplier's signature block). Best-effort only.
     */
    private function parseLabeledLineFromQuotation(string $text, string $label): ?string
    {
        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $text)),
            fn ($line) => $line !== ''
        ));

        foreach ($lines as $i => $line) {
            if (stripos($line, $label) === false || $i === 0) {
                continue;
            }

            $candidate = $lines[$i - 1];
            $looksLikeLabel = preg_match('/^(Printed Name|Signature|Company (Name|Address)|Contact No|Canvasser|Procurement Officer)/i', $candidate);

            if ($candidate !== '' && !$looksLikeLabel) {
                return $candidate;
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
    public function uploadCanvassDocument(Request $request, PurchaseRequest $pr): JsonResponse
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

        $file = $request->file('document');
        $path = $file->store('canvass/' . now()->year, 'public');

        $isPdf = $file->getClientMimeType() === 'application/pdf' || $file->getClientOriginalExtension() === 'pdf';
        $supplierAddress = $isPdf ? $this->parseSupplierAddressFromQuotation($this->readPdfText($file)) : null;

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
        $aocs = AbstractOfCanvass::with(['purchaseRequest.office', 'purchaseRequest.items', 'purchaseRequest.documents', 'signatureLogs.signedBy', 'signatureLogs.attachments', 'purchaseOrder'])
            ->latest()
            ->get()
            ->map(fn ($aoc) => $this->mapAocForFrontend($aoc))
            ->all();

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

        return [
            'id'             => $aoc->id,
            'code'           => $aoc->code ?? 'AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT),
            'prNumber'       => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
            'office'         => $pr->office?->code ?? '—',
            'title'          => $pr->title,
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
            'pdfFile'        => $aoc->file_path,
            'prTotal'        => (float) ($pr->total_amount ?? 0),
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
        $pos = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'createdBy', 'paidBy', 'documents', 'signatureLogs.signedBy', 'signatureLogs.attachments'])
            ->orderByDesc('id')
            ->get()
            ->map(fn ($po) => $this->mapPoForFrontend($po))
            ->all();

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
            'poNumber'     => $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
            'aocCode'      => $po->abstractOfCanvass->code ?? '—',
            'prNumber'     => $pr->number ?? '—',
            'office'       => $pr?->office?->code ?? '—',
            'title'        => $pr->title ?? '—',
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
                return [
                    'key'    => $key,
                    'label'  => (clone $po)->fill(['status' => $key])->status_label,
                    'status' => $isComplete ? 'done' : ($idx < $current ? 'done' : ($idx === $current ? 'active' : 'pending')),
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
            'po_number'              => 'PO-' . now()->format('Ymd') . '-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT),
            'supplier_name'          => $request->input('supplier_name'),
            'supplier_address'       => $request->input('supplier_address'),
            'total_amount'           => $request->input('total_amount'),
            'status'                 => 'issued',
            'signatory_stage'        => 'draft',
            'issued_at'              => now(),
            'expected_delivery_date' => $request->input('expected_delivery_date'),
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

    public function procurementReports(): View
    {
        $quarterlyRows = $this->buildQuarterlyAccomplishment();

        // Same "fully signed" journey used everywhere else on this page's
        // report-driven data, plus a real completion signal (paid = actually
        // done) instead of the raw `status` column, which Procurement itself
        // almost never sets to the literal value 'completed'.
        $allPrs = PurchaseRequest::with(['office', 'abstractOfCanvass.purchaseOrder'])->get();

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

        $ppmpValidationRows = $this->buildPpmpValidationRows();

        return view('prism.procurement-office.procurement-reports', $this->withCommon('procurement-reports', [
            'pageTitle'          => 'Procurement Reports',
            'quarterlyRows'      => $quarterlyRows,
            'completedPurchases' => $completedPurchases,
            'delayedItems'       => $delayedItems,
            'ppmpValidationRows' => $ppmpValidationRows,
        ]));
    }

    /**
     * Real per-office, per-quarter targeted-vs-procured accomplishment,
     * built from the same endorsed/approved PPMP items and office+name PR
     * match used by buildPpmpValidationRows() — grouped by each item's own
     * target_quarter instead of splitting PR totals evenly across quarters.
     * "Procured" means the matched PR's full journey actually reached paid.
     */
    private function buildQuarterlyAccomplishment(): array
    {
        $items = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
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
    private function buildPpmpValidationRows(): array
    {
        $items = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
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

    /** Upload/re-upload the scanned, physically-signed AOC document. */
    public function uploadAbstractOfCanvass(Request $request, AbstractOfCanvass $aoc): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $year = now()->year;
        $slug = Str::slug($aoc->code ?? 'aoc-' . $aoc->id);
        $name = $slug . '-' . now()->format('Ymd-His') . '.pdf';
        $file = $request->file('file');
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
        $responsiveDealer = $this->extractResponsiveDealer($this->readPdfText($file));
        if ($responsiveDealer) {
            $aoc->update(['winning_supplier_name' => $responsiveDealer]);
        }

        $aoc->load(['purchaseRequest.office', 'purchaseRequest.items', 'purchaseRequest.documents', 'signatureLogs.signedBy', 'signatureLogs.attachments', 'purchaseOrder']);

        return response()->json(['success' => true, 'aoc' => $this->mapAocForFrontend($aoc)]);
    }

    /** Upload/re-upload the scanned, physically-signed PO document. */
    public function uploadPurchaseOrder(Request $request, PurchaseOrder $po): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $year = now()->year;
        $slug = Str::slug($po->po_number ?? 'po-' . $po->id);
        $name = $slug . '-' . now()->format('Ymd-His') . '.pdf';
        $file = $request->file('file');
        $path = $file->storeAs("purchase-orders/{$year}", $name, 'public');

        $poFields = $this->extractPoFundingFields($this->readPdfText($file));

        $po->update([
            'file_path'   => $path,
            'uploaded_at' => now(),
            'alobs_no'    => $poFields['alobsNo'] ?? $po->alobs_no,
            'fund_source' => $poFields['fundSource'] ?? $po->fund_source,
        ]);

        return response()->json([
            'success'    => true,
            'filePath'   => $path,
            'alobsNo'    => $po->fresh()->alobs_no,
            'fundSource' => $po->fresh()->fund_source,
        ]);
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

    /**
     * The Abstract of Canvass form's recommendation paragraph always names the
     * winning bidder the same way: "...prices offered by <Supplier> is/are
     * considered reasonable and most advantageous...". Best-effort only.
     */
    private function extractResponsiveDealer(string $text): ?string
    {
        $normalized = preg_replace('/\s+/', ' ', $text);
        if (preg_match('/offered by (.+?) is\s*\/?\s*are considered/i', $normalized, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    /**
     * The Purchase Order form's "Funds Available" box lists the ALOBS No. and
     * Fund Source on their own labeled lines. Best-effort only — a scanned,
     * hand-filled PO may have no text layer at all for these values.
     */
    private function extractPoFundingFields(string $text): array
    {
        $result = ['alobsNo' => null, 'fundSource' => null];

        // Colon is required (not "?") and the capture allows zero length — this
        // keeps the match confined to the same line and stops the regex engine
        // from "giving back" the colon into the capture group when the field is
        // left blank on the form (which would otherwise wrongly extract ":").
        if (preg_match('/ALOBS No\.?[ \t]*:[ \t]*([^\r\n]*)/i', $text, $m) && trim($m[1]) !== '') {
            $result['alobsNo'] = trim($m[1]);
        }
        if (preg_match('/Fund Source[ \t]*:[ \t]*([^\r\n]*)/i', $text, $m) && trim($m[1]) !== '') {
            $result['fundSource'] = trim($m[1]);
        }

        return $result;
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
