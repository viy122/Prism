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
use Illuminate\Database\UniqueConstraintViolationException;
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
        $totalPrsReceived      = PurchaseRequest::count();
        $prsInProgress         = PurchaseRequest::whereIn('status', ['in_progress', 'pending'])->count();
        $prsCompletedThisMonth = PurchaseRequest::where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        $overduePrs = PurchaseRequest::where('status', 'delayed')->count();

        $officeStatusGroups = Office::has('purchaseRequests')
            ->with('purchaseRequests')
            ->get()
            ->map(fn ($office) => [
                'office'     => $office->code,
                'completed'  => $office->purchaseRequests->where('status', 'completed')->count(),
                'inProgress' => $office->purchaseRequests->where('status', 'in_progress')->count(),
                'pending'    => $office->purchaseRequests->where('status', 'pending')->count(),
            ])
            ->filter(fn ($g) => $g['completed'] + $g['inProgress'] + $g['pending'] > 0)
            ->values()
            ->all();

        $urgentPrs = PurchaseRequest::with('office')
            ->whereIn('status', ['pending', 'in_progress', 'delayed'])
            ->latest()
            ->take(8)
            ->get()
            ->map(fn ($pr) => [
                'office'        => $pr->office?->code ?? '—',
                'prNumber'      => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
                'item'          => $pr->title,
                'targetQuarter' => '—',
                'dueDate'       => $pr->submitted_at?->format('M d, Y') ?? '—',
                'status'        => ucfirst(str_replace('_', ' ', $pr->status)),
                'dueThisMonth'  => $pr->submitted_at?->isCurrentMonth() ?? false,
            ])
            ->all();

        return view('prism.procurement-office.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Procurement Office Dashboard',
            'summary'   => [
                'totalPrsReceived'      => $totalPrsReceived,
                'prsInProgress'         => $prsInProgress,
                'prsCompletedThisMonth' => $prsCompletedThisMonth,
                'overduePrs'            => $overduePrs,
            ],
            'officeStatusGroups' => $officeStatusGroups,
            'urgentPrs'          => $urgentPrs,
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
            'offices'          => Office::select('id', 'name')->orderBy('name')->get()->toArray(),
            'importPdfUrl'     => route('procurement-office.purchase-request.import-pdf'),
            'importConfirmUrl' => route('procurement-office.purchase-request.import-confirm'),
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
            ->latest()
            ->get()
            ->map(fn ($pr) => [
                'id'              => $pr->id,
                'prNumber'        => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
                'office'          => $pr->office?->code ?? '—',
                'title'           => $pr->title,
                'canvassingStage' => $pr->canvassing_stage,
                'canvassingLabel' => $pr->canvassing_label,
                'readyForAoc'     => $pr->isReadyForAoc(),
                'quotations'      => $pr->documents->map(fn ($doc) => [
                    'id'        => $doc->id,
                    'supplier'  => $doc->title,
                    'filename'  => $doc->original_filename,
                    'url'       => Storage::url($doc->file_path),
                    'uploadedAt'=> $doc->uploaded_at?->format('M d, Y') ?? '—',
                    'deleteUrl' => route('procurement-office.canvass-document.delete', $doc->id),
                ])->all(),
                'uploadUrl' => route('procurement-office.purchase-request.canvass-document', $pr->id),
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
     * supplier name so Procurement doesn't have to retype it. Best-effort only:
     * scanned/image uploads or an unrecognized layout just return null, and the
     * field stays manually editable either way.
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
        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $text)),
            fn ($line) => $line !== ''
        ));

        foreach ($lines as $i => $line) {
            if (stripos($line, 'Company Name') === false || $i === 0) {
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
     * Canvassing has one step: upload a document. The upload itself completes
     * canvassing immediately — no separate "start"/"complete" action needed.
     */
    public function uploadCanvassDocument(Request $request, PurchaseRequest $pr): JsonResponse
    {
        if ($pr->signatory_stage !== 'fully_signed') {
            return response()->json(['error' => 'PR must be fully signed before canvassing.'], 422);
        }
        if ($pr->canvassing_stage === 'completed') {
            return response()->json(['error' => 'Canvassing is already completed for this PR.'], 422);
        }

        $request->validate([
            'document'      => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'supplier_name' => 'required|string|max:255',
        ]);

        $file = $request->file('document');
        $path = $file->store('canvass/' . now()->year, 'public');

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
        ]);

        $pr->update(['canvassing_stage' => 'completed']);

        ProcurementStatusUpdate::create([
            'purchase_request_id' => $pr->id,
            'updated_by_user_id'  => auth()->id(),
            'status'              => 'canvassing_completed',
            'remarks'             => 'Canvassing document uploaded — ready for AOC.',
        ]);

        return response()->json([
            'success'         => true,
            'documentId'      => $doc->id,
            'url'             => Storage::url($path),
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
        if ($pr instanceof PurchaseRequest && $pr->canvassing_stage === 'completed') {
            return response()->json(['error' => 'Canvassing is already completed — quotations are locked.'], 422);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['success' => true]);
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
            ->map(function ($aoc) {
                $pr = $aoc->purchaseRequest;

                return [
                    'id'             => $aoc->id,
                    'code'           => $aoc->code ?? 'AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT),
                    'prNumber'       => $pr->number ?? 'PR-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
                    'office'         => $pr->office?->code ?? '—',
                    'title'          => $pr->title,
                    'signatoryStage'   => $aoc->signatory_stage,
                    'signatoryLabel'   => $aoc->signatory_label,
                    'nextStage'        => $aoc->nextSignatoryStage(),
                    'currentStageType' => $aoc->stageMetaFor($aoc->signatory_stage)['type'] ?? 'signature',
                    'nextStageLabel'   => $aoc->stageMetaFor($aoc->nextSignatoryStage())['label'] ?? null,
                    'hasPo'            => $aoc->purchaseOrder !== null,
                    'poNumber'         => $aoc->purchaseOrder?->po_number,
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
                    'quotations'     => $pr->documents->where('document_type', 'canvass_quotation')->map(fn ($d) => [
                        'supplier'   => $d->title,
                        'filename'   => $d->original_filename,
                        'url'        => \Illuminate\Support\Facades\Storage::url($d->file_path),
                        'uploadedAt' => $d->uploaded_at?->format('M d, Y') ?? '—',
                    ])->values()->all(),
                    // Canvassing only ever keeps one quotation per PR (locked once
                    // uploaded — see uploadCanvassDocument()), so it's already the
                    // known supplier by the time Procurement issues the PO.
                    'supplierName'   => $pr->documents->firstWhere('document_type', 'canvass_quotation')?->title,
                ];
            })
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

    public function createAoc(Request $request, PurchaseRequest $pr): JsonResponse
    {
        if (!$pr->isReadyForAoc()) {
            return response()->json(['error' => 'PR must be fully signed and canvassing completed before creating an AOC.'], 422);
        }

        if ($pr->abstractOfCanvass) {
            return response()->json(['error' => 'AOC already exists for this PR.'], 422);
        }

        $aoc = AbstractOfCanvass::create([
            'purchase_request_id' => $pr->id,
            'created_by_user_id'  => auth()->id(),
            'code'                => 'AOC-' . now()->format('Ymd') . '-' . str_pad($pr->id, 4, '0', STR_PAD_LEFT),
            'signatory_stage'     => 'draft',
        ]);

        return response()->json(['success' => true, 'aocId' => $aoc->id]);
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
        ]));
    }

    public function purchaseOrdersRefresh(): JsonResponse
    {
        return response()->json($this->purchaseOrdersData());
    }

    private function purchaseOrdersData(): array
    {
        $chain = PurchaseOrder::statusChain();

        $pos = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'createdBy', 'paidBy', 'documents', 'signatureLogs.signedBy', 'signatureLogs.attachments'])
            ->latest()
            ->get()
            ->map(function ($po) use ($chain) {
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
            })
            ->all();

        // AOCs that are fully signed but don't have a PO yet
        $eligibleAocs = AbstractOfCanvass::with('purchaseRequest.office')
            ->where('signatory_stage', 'fully_signed')
            ->whereDoesntHave('purchaseOrder')
            ->latest()
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

        return response()->json(['success' => true, 'poId' => $po->id]);
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
        $offices = Office::has('purchaseRequests')
            ->with('purchaseRequests')
            ->get();

        $quarterlyRows = $offices->flatMap(function ($office) {
            $total    = $office->purchaseRequests->count();
            $procured = $office->purchaseRequests->where('status', 'completed')->count();
            $rate     = $total > 0 ? round(($procured / $total) * 100) : 0;

            return collect(['Q1', 'Q2', 'Q3', 'Q4'])->map(fn ($q) => [
                'office'         => $office->code,
                'quarter'        => $q,
                'targeted'       => (int) ceil($total / 4),
                'procured'       => (int) floor($procured / 4),
                'completionRate' => $rate,
            ])->all();
        })->filter(fn ($r) => $r['targeted'] > 0)->values()->all();

        $completedPurchases = PurchaseRequest::with('office')
            ->where('status', 'completed')
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->map(fn ($pr) => [
                'office'        => $pr->office?->code ?? '—',
                'item'          => $pr->title,
                'prNumber'      => $pr->number ?? '—',
                'completedDate' => $pr->updated_at->format('M d, Y'),
                'amount'        => (float) $pr->total_amount,
            ])
            ->all();

        $delayedItems = PurchaseRequest::with('office')
            ->where('status', 'delayed')
            ->latest()
            ->get()
            ->map(fn ($pr) => [
                'office'   => $pr->office?->code ?? '—',
                'item'     => $pr->title,
                'prNumber' => $pr->number ?? '—',
                'reason'   => $pr->remarks ?? 'No remarks provided.',
            ])
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
        // supplier — once available, it supersedes the name Procurement typed in
        // at canvassing time (see uploadCanvassDocument()) for any later reads
        // (e.g. pre-filling the Issue PO form's supplier name).
        $responsiveDealer = $this->extractResponsiveDealer($this->readPdfText($file));
        if ($responsiveDealer) {
            $aoc->purchaseRequest?->documents()
                ->where('document_type', 'canvass_quotation')
                ->update(['title' => $responsiveDealer]);
        }

        return response()->json([
            'success'      => true,
            'filePath'     => $path,
            'supplierName' => $responsiveDealer,
        ]);
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

    // ── BSU PDF Import ───────────────────────────────────────────────────────

    public function importPrFromPdf(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:pdf|max:20480']);

        $text = '';
        try {
            $parser = new PdfParser();
            $pdf    = $parser->parseContent($request->file('file')->get());
            $text   = $pdf->getText();
        } catch (\Exception $e) {
            $text = '';
        }

        $extracted = $this->parsePrFields($text);

        $year = now()->year;
        $name = 'bsu-import-' . now()->format('Ymd-His') . '.pdf';
        $path = $request->file('file')->storeAs("purchase-requests/{$year}", $name, 'public');

        return response()->json([
            'success'   => true,
            'filePath'  => $path,
            'hasText'   => strlen(trim($text)) > 10,
            'extracted' => $extracted,
        ]);
    }

    public function importPrConfirm(Request $request): JsonResponse
    {
        $data = $request->validate([
            'office_id'          => 'required|integer|exists:offices,id',
            'number'             => 'nullable|string|max:100|unique:purchase_requests,number',
            'title'              => 'required|string|max:500',
            'purpose'            => 'nullable|string|max:2000',
            'total_amount'       => 'nullable|numeric|min:0',
            'file_path'          => [
                'nullable', 'string', 'max:500', 'unique:purchase_requests,file_path',
                function ($attribute, $value, $fail) {
                    if (!$value) {
                        return;
                    }
                    if (!preg_match('#^purchase-requests/\d{4}/[A-Za-z0-9._-]+\.pdf$#i', $value)) {
                        $fail('The uploaded document reference is invalid.');
                        return;
                    }
                    if (!Storage::disk('public')->exists($value)) {
                        $fail('The uploaded document could not be found. Please re-upload the PDF.');
                    }
                },
            ],
            'items'              => 'nullable|array',
            'items.*.name'       => 'required_with:items|string|max:500',
            'items.*.qty'        => 'required_with:items.*.name|numeric|min:0.01',
            'items.*.unit'       => 'nullable|string|max:50',
            'items.*.unit_cost'  => 'required_with:items.*.name|numeric|min:0.01',
        ], [
            'number.unique'    => 'A purchase request with this PR number has already been imported.',
            'file_path.unique' => 'This document has already been attached to another purchase request.',
        ]);

        $items = $data['items'] ?? [];

        // Items are the source of truth for the amount — derive it from them
        // rather than trusting a client-editable total that can drift out of sync.
        $itemsTotal = round(array_sum(array_map(
            fn ($item) => (float) $item['qty'] * (float) $item['unit_cost'],
            $items
        )), 2);
        $totalAmount = $items ? $itemsTotal : ($data['total_amount'] ?? 0);

        try {
            $pr = PurchaseRequest::create([
                'office_id'             => $data['office_id'],
                'created_by_user_id'    => auth()->id(),
                'number'                => $data['number'] ?: ('BSU-' . now()->format('YmdHis')),
                'title'                 => $data['title'],
                'description'           => $data['purpose'] ?? null,
                'fiscal_year'           => now()->year,
                'total_amount'          => $totalAmount,
                'status'                => 'in_progress',
                'signatory_stage'       => 'draft',
                'canvassing_stage'      => 'completed',
                'file_path'             => $data['file_path'] ?? null,
                'extracted_fields_json' => ['imported_from_bsu' => true],
                'uploaded_at'           => now(),
                'remarks'               => 'Imported from BSU procurement system.',
            ]);
        } catch (UniqueConstraintViolationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'A purchase request with this PR number or document has already been imported.',
            ], 422);
        }

        foreach ($items as $item) {
            $qty      = (float) $item['qty'];
            $unitCost = (float) $item['unit_cost'];
            $pr->items()->create([
                'name'                => $item['name'],
                'quantity'            => $qty,
                'unit'                => $item['unit'] ?? 'pc',
                'estimated_unit_cost' => $unitCost,
                'estimated_total_cost'=> round($qty * $unitCost, 2),
            ]);
        }

        NotificationService::prUploaded($pr);

        return response()->json([
            'success' => true,
            'prId'    => $pr->id,
            'message' => 'PR imported successfully from BSU system.',
        ]);
    }

    private function parsePrFields(string $text): array
    {
        $clean = fn (string $s) => trim(preg_replace('/\s+/', ' ', $s));

        $prNumber = null;
        if (preg_match('/(?:P\.?\s*R\.?\s*No\.?|Purchase\s+Request\s+No\.?)\s*[:\|]?\s*([A-Z0-9\-\/]+)/i', $text, $m)) {
            $prNumber = $clean($m[1]);
        }

        $office = null;
        if (preg_match('/(?:Requesting\s+Office(?:\s*\/\s*Section)?|Department\s*\/?\s*Office|Office\s*\/\s*Department)\s*[:\|]\s*([^\n\r]+)/i', $text, $m)) {
            $office = $clean($m[1]);
        }

        $title = null;
        if (preg_match('/(?:Name\s+of\s+Project|Document\s+Title|Project\s+Title)\s*[:\|]\s*([^\n\r]+)/i', $text, $m)) {
            $title = $clean($m[1]);
        }

        $purpose = null;
        if (preg_match('/Purpose(?:\s*\/\s*Justification)?\s*[:\|]\s*([^\n\r]+)/i', $text, $m)) {
            $purpose = $clean($m[1]);
        }

        $date = null;
        if (preg_match('/Date\s*[:\|]\s*(\d{1,2}[\-\/]\d{1,2}[\-\/]\d{2,4}|\w+\s+\d{1,2},?\s*\d{4})/i', $text, $m)) {
            $date = $clean($m[1]);
        }

        $total = null;
        if (preg_match('/TOTAL\s+(?:AMOUNT|COST)[^0-9₱]*([0-9,]+\.?\d*)/i', $text, $m)) {
            $total = (float) str_replace(',', '', $m[1]);
        }

        $items = $this->parsePrItems($text);
        if ($total === null && $items) {
            $total = round(array_sum(array_map(fn ($i) => $i['qty'] * $i['unit_cost'], $items)), 2);
        }

        return [
            'pr_number' => $prNumber,
            'office'    => $office,
            'title'     => $title,
            'purpose'   => $purpose,
            'date'      => $date,
            'total'     => $total,
            'items'     => $items,
        ];
    }

    /**
     * Best-effort extraction of item rows from a PR's item table. PDF text
     * extraction flattens table cells into plain lines, so this matches lines
     * that end in a qty + unit cost + total cost column triplet (the shape
     * shared by the BatStateU-FO-PRO-02 form and this system's own PR print).
     */
    private function parsePrItems(string $text): array
    {
        $clean = fn (string $s) => trim(preg_replace('/\s+/', ' ', $s));
        $units = ['pc', 'pcs', 'piece', 'pieces', 'unit', 'units', 'set', 'sets', 'box', 'boxes',
            'ream', 'reams', 'bottle', 'bottles', 'pack', 'packs', 'roll', 'rolls', 'kg', 'kgs',
            'ltr', 'ltrs', 'liter', 'liters', 'gal', 'gallon', 'gallons', 'can', 'cans',
            'bundle', 'bundles', 'lot', 'lots', 'pair', 'pairs'];

        $items = [];

        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Trailing qty + unit cost + total cost triplet — the shape shared by the
            // BatStateU-FO-PRO-02 form and this system's own PR print table.
            if (!preg_match(
                '/^(?<prefix>.+?)\s+(?<qty>\d+(?:\.\d+)?)\s+(?:₱\s*)?(?<unitcost>[\d,]+\.\d{2})\s+(?:₱\s*)?(?<totalcost>[\d,]+\.\d{2})\s*$/',
                $line,
                $m
            )) {
                continue;
            }

            $qty       = (float) $m['qty'];
            $unitCost  = (float) str_replace(',', '', $m['unitcost']);
            $totalCost = (float) str_replace(',', '', $m['totalcost']);

            // qty * unitCost should roughly match totalCost, otherwise this line is
            // probably not an item row (e.g. a stray date or signature block).
            if ($qty <= 0 || $unitCost <= 0 || abs(($qty * $unitCost) - $totalCost) > max(1, $totalCost * 0.02)) {
                continue;
            }

            $prefix = trim($m['prefix']);
            // Drop a leading row number ("1", "12.", "3)").
            $prefix = preg_replace('/^\d+[\.\)]?\s+(?=\S)/', '', $prefix);
            // Drop a leading dash/N-A placeholder (empty stock/property no. column).
            $prefix = preg_replace('/^(?:—|-{1,2}|N\/?A)\s+(?=\S)/i', '', $prefix);
            // Drop a leading stock/property code — only if that token itself contains a digit,
            // so plain description words (no digit) are never mistaken for a code.
            $prefix = preg_replace('/^(?=\S*\d)[A-Za-z0-9\-\/]{1,15}\s+(?=\S)/', '', $prefix);

            $words = explode(' ', $prefix);
            $unit  = 'pc';
            if (count($words) > 1 && in_array(strtolower($words[0]), $units, true)) {
                $unit = array_shift($words);
            }

            $desc = $clean(implode(' ', $words));
            if ($desc === '' || stripos($desc, 'total') !== false || stripos($desc, 'grand') !== false) {
                continue;
            }

            $items[] = [
                'name'      => $desc,
                'qty'       => $qty,
                'unit'      => $unit,
                'unit_cost' => $unitCost,
            ];
        }

        return $items;
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
