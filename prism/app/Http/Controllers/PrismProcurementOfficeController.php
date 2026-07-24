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
use App\Services\MarketScopingService;
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
                'office'     => $office->name,
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
                'office'        => $pr->office?->name ?? '—',
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
        $prs = PurchaseRequest::with(['office', 'items', 'statusUpdates' => fn ($q) => $q->latest(), 'signatureLogs.signedBy'])
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
                'canvassingStage'  => $pr->canvassing_stage,
                'canvassingLabel'  => $pr->canvassing_label,
                'readyForAoc'      => $pr->isReadyForAoc(),
                'canvassingUrl'    => route('procurement-office.purchase-request.canvassing', $pr->id),
                'pdfFile'          => $pr->file_path,
                'remarks'          => $pr->remarks ?? '—',
                'ocr'            => $pr->extracted_fields_json ?? [],
                'activityLog'    => $pr->statusUpdates->map(fn ($u) => [
                    'timestamp' => $u->created_at->format('M d, Y g:i A'),
                    'status'    => ucfirst(str_replace('_', ' ', $u->status)),
                    'remarks'   => $u->remarks ?? '—',
                ])->all(),
                'signatureLogs'  => $pr->signatureLogs->map(fn ($l) => [
                    'display'   => $pr->describeSignatureLog($l),
                    'action'    => $l->action,
                    'by'        => $l->signedBy?->name ?? '—',
                    'at'        => $l->signed_at?->format('M d, Y g:i A') ?? '—',
                    'remarks'   => $l->remarks ?? '',
                    'photoUrl'      => $l->blurred_photo_path ? \Illuminate\Support\Facades\Storage::url($l->blurred_photo_path) : null,
                    'photoStatus'   => $l->detection_status,
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
                'marketPricesUrl'   => route('procurement-office.purchase-request.market-prices', $pr->id),
                'prItems'           => $pr->items->map(fn ($i) => [
                    'name'    => $i->name,
                    'budget'  => (float) $i->estimated_unit_cost,
                ])->all(),
            ])
            ->all();

        return view('prism.procurement-office.purchase-request-management', $this->withCommon('purchase-request-management', [
            'pageTitle'        => 'Purchase Request Management',
            'purchaseRequests' => $prs,
            'stageMeta'        => PurchaseRequest::signatoryStageMeta(),
            'offices'          => Office::select('id', 'name')->orderBy('name')->get()->toArray(),
            'importPdfUrl'     => route('procurement-office.purchase-request.import-pdf'),
            'importConfirmUrl' => route('procurement-office.purchase-request.import-confirm'),
        ]));
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

        return response()->json($signatory->returnToDraft($pr, $request->input('remarks')));
    }

    public function updateCanvassing(Request $request, PurchaseRequest $pr): JsonResponse
    {
        if ($pr->signatory_stage !== 'fully_signed') {
            return response()->json(['error' => 'PR must be fully signed before canvassing.'], 422);
        }

        $request->validate(['action' => 'required|in:start,complete']);

        $newStage = $request->input('action') === 'start' ? 'in_progress' : 'completed';

        // Three-quotation rule: canvassing needs at least 3 supplier quotations
        if ($newStage === 'completed' && $pr->canvassDocuments()->count() < 3) {
            return response()->json(['error' => 'At least 3 supplier quotations must be uploaded before completing canvassing. Use the Canvassing tab to attach them.'], 422);
        }

        $pr->update(['canvassing_stage' => $newStage]);

        ProcurementStatusUpdate::create([
            'purchase_request_id' => $pr->id,
            'updated_by_user_id'  => auth()->id(),
            'status'              => 'canvassing_' . $newStage,
            'remarks'             => $request->input('remarks', ''),
        ]);

        return response()->json([
            'success'           => true,
            'canvassingStage'   => $newStage,
            'canvassingLabel'   => $pr->fresh()->canvassing_label,
            'readyForAoc'       => $pr->fresh()->isReadyForAoc(),
        ]);
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

    // ── Annual Procurement Plan (moved from Budget Office) ──────────────────

    public function annualProcurementPlan(): View
    {
        $items = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->get()
            ->map(function ($item) {
                $abc             = (float) $item->estimated_total_cost;
                $recommendedMode = ProcurementModeService::recommend($abc);

                return [
                    'itemId'          => $item->id,
                    'office'          => $item->budgetProposal?->office?->name ?? '—',
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
                ];
            })
            ->all();

        return view('prism.procurement-office.annual-procurement-plan', $this->withCommon('annual-procurement-plan', [
            'pageTitle'        => 'Annual Procurement Plan',
            'appItems'         => $items,
            'offices'          => collect($items)->pluck('office')->unique()->values()->all(),
            'quarters'         => ['Q1', 'Q2', 'Q3', 'Q4'],
            'procurementModes' => ProcurementModeService::MODES,
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
                'office'          => $pr->office?->name ?? '—',
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
                'updateUrl' => route('procurement-office.purchase-request.canvassing', $pr->id),
                'uploadUrl' => route('procurement-office.purchase-request.canvass-document', $pr->id),
            ])
            ->all();

        return view('prism.procurement-office.canvassing', $this->withCommon('canvassing', [
            'pageTitle' => 'Canvassing',
            'prs'       => $prs,
        ]));
    }

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

        // First quotation implicitly starts canvassing
        if ($pr->canvassing_stage === 'not_started') {
            $pr->update(['canvassing_stage' => 'in_progress']);
        }

        return response()->json([
            'success'         => true,
            'documentId'      => $doc->id,
            'url'             => Storage::url($path),
            'canvassingStage' => $pr->fresh()->canvassing_stage,
            'quotationCount'  => $pr->canvassDocuments()->count(),
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
        $aocs = AbstractOfCanvass::with(['purchaseRequest.office', 'signatureLogs.signedBy', 'purchaseOrder'])
            ->latest()
            ->get()
            ->map(fn ($aoc) => [
                'id'             => $aoc->id,
                'code'           => $aoc->code ?? 'AOC-' . str_pad($aoc->id, 4, '0', STR_PAD_LEFT),
                'prNumber'       => $aoc->purchaseRequest->number ?? 'PR-' . str_pad($aoc->purchaseRequest->id, 4, '0', STR_PAD_LEFT),
                'office'         => $aoc->purchaseRequest->office?->name ?? '—',
                'title'          => $aoc->purchaseRequest->title,
                'signatoryStage'   => $aoc->signatory_stage,
                'signatoryLabel'   => $aoc->signatory_label,
                'nextStage'        => $aoc->nextSignatoryStage(),
                'currentStageType' => $aoc->stageMetaFor($aoc->signatory_stage)['type'] ?? 'signature',
                'nextStageLabel'   => $aoc->stageMetaFor($aoc->nextSignatoryStage())['label'] ?? null,
                'hasPo'            => $aoc->purchaseOrder !== null,
                'remarks'        => $aoc->remarks ?? '—',
                'createdAt'      => $aoc->created_at->format('M d, Y'),
                'advanceUrl'     => route('procurement-office.aoc.advance', $aoc->id),
                'returnUrl'      => route('procurement-office.aoc.return', $aoc->id),
                'issuePo'        => route('procurement-office.po.issue', $aoc->id),
            ])
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
                'office'   => $pr->office?->name ?? '—',
                'title'    => $pr->title,
                'createUrl'=> route('procurement-office.aoc.create', $pr->id),
            ])
            ->all();

        return view('prism.procurement-office.abstract-of-canvass', $this->withCommon('abstract-of-canvass', [
            'pageTitle'   => 'Abstract of Canvass',
            'aocs'        => $aocs,
            'eligiblePrs' => $eligiblePrs,
            'stageMeta'   => AbstractOfCanvass::signatoryStageMeta(),
        ]));
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

        return response()->json($signatory->returnToDraft($aoc, $request->input('remarks')));
    }

    // ── Phase 3: Purchase Order ───────────────────────────────────────────────

    public function purchaseOrders(): View
    {
        $pos = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'createdBy', 'paidBy', 'documents'])
            ->latest()
            ->get()
            ->map(fn ($po) => [
                'receiptUrl'   => ($receipt = $po->documents->firstWhere('document_type', 'payment_receipt'))
                    ? \Illuminate\Support\Facades\Storage::url($receipt->file_path)
                    : null,
                'id'           => $po->id,
                'poNumber'     => $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
                'aocCode'      => $po->abstractOfCanvass->code ?? '—',
                'prNumber'     => $po->abstractOfCanvass->purchaseRequest->number ?? '—',
                'office'       => $po->abstractOfCanvass->purchaseRequest->office?->name ?? '—',
                'title'        => $po->abstractOfCanvass->purchaseRequest->title ?? '—',
                'supplier'     => $po->supplier_name,
                'totalAmount'  => (float) $po->total_amount,
                'status'       => $po->status,
                'statusLabel'  => $po->status_label,
                'nextStatus'   => $po->nextStatus(),
                'issuedAt'     => $po->issued_at?->format('M d, Y') ?? '—',
                'expectedDate' => $po->expected_delivery_date?->format('M d, Y') ?? '—',
                'paidAt'       => $po->paid_at?->format('M d, Y') ?? null,
                'updateUrl'    => route('procurement-office.po.update-status', $po->id),
                'signatoryStage'   => $po->signatory_stage ?? 'draft',
                'signatoryLabel'   => $po->signatory_label,
                'nextStage'        => $po->nextSignatoryStage(),
                'currentStageType' => $po->stageMetaFor($po->signatory_stage)['type'] ?? 'signature',
                'nextStageLabel'   => $po->stageMetaFor($po->nextSignatoryStage())['label'] ?? null,
                'advanceUrl'       => route('procurement-office.po.advance', $po->id),
                'returnUrl'        => route('procurement-office.po.return', $po->id),
            ])
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
                'office'  => $aoc->purchaseRequest->office?->name ?? '—',
                'title'   => $aoc->purchaseRequest->title,
                'amount'  => (float) $aoc->purchaseRequest->total_amount,
                'issueUrl'=> route('procurement-office.po.issue', $aoc->id),
            ])
            ->all();

        return view('prism.procurement-office.purchase-order', $this->withCommon('purchase-orders', [
            'pageTitle'    => 'Purchase Orders',
            'purchaseOrders' => $pos,
            'eligibleAocs' => $eligibleAocs,
            'statusChain'  => PurchaseOrder::statusChain(),
        ]));
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

        return response()->json($signatory->returnToDraft($po, $request->input('remarks')));
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

    public function procurementStatusTracking(): View
    {
        $items = BudgetProposalItem::with('budgetProposal.office')
            ->whereHas('budgetProposal', fn ($q) => $q->whereIn('status', ['endorsed', 'approved']))
            ->latest()
            ->get()
            ->map(fn ($item) => [
                'id'             => $item->id,
                'office'         => $item->budgetProposal?->office?->name ?? '—',
                'item'           => $item->name,
                'approvedAmount' => (float) $item->estimated_total_cost,
                'targetQuarter'  => $item->target_quarter ?? '—',
                'currentStatus'  => 'Pending',
                'remarks'        => $item->remarks ?? '—',
            ])
            ->all();

        return view('prism.procurement-office.procurement-status-tracking', $this->withCommon('procurement-status-tracking', [
            'pageTitle'     => 'Procurement Status Tracking',
            'trackingItems' => $items,
            'offices'       => collect($items)->pluck('office')->unique()->values()->all(),
            'quarters'      => collect($items)->pluck('targetQuarter')->filter(fn ($q) => $q !== '—')->unique()->sort()->values()->all(),
            'statuses'      => ['Pending', 'In Progress', 'Completed', 'Delayed'],
        ]));
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
                'office'         => $office->name,
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
                'office'        => $pr->office?->name ?? '—',
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
                'office'   => $pr->office?->name ?? '—',
                'item'     => $pr->title,
                'prNumber' => $pr->number ?? '—',
                'reason'   => $pr->remarks ?? 'No remarks provided.',
            ])
            ->all();

        return view('prism.procurement-office.procurement-reports', $this->withCommon('procurement-reports', [
            'pageTitle'          => 'Procurement Reports',
            'quarterlyRows'      => $quarterlyRows,
            'completedPurchases' => $completedPurchases,
            'delayedItems'       => $delayedItems,
        ]));
    }

    public function getMarketPrices(Request $request, PurchaseRequest $pr): JsonResponse
    {
        $item   = trim($request->input('item', $pr->title));
        $budget = (float) $request->input('budget', 0);

        $service = new MarketScopingService();
        $results = $service->search($item);

        if (empty($results)) {
            return response()->json([
                'results'         => [],
                'cached'          => false,
                'source'          => 'none',
                'quota_exhausted' => $service->isQuotaExhausted(),
            ]);
        }

        $results = $service->matchSpecs($results, [], $item);
        if ($budget > 0) {
            $results = $service->flagAdvantageous($results, $budget);
        }

        return response()->json([
            'results'         => $results,
            'cached'          => !empty($results[0]['cached']),
            'source'          => 'serpapi',
            'quota_exhausted' => $service->isQuotaExhausted(),
        ]);
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
                ['slug' => 'procurement-status-tracking', 'label' => 'Status Tracking',             'href' => route('procurement-office.procurement-status-tracking'), 'icon' => 'list-check'],
                ['slug' => 'procurement-reports',         'label' => 'Reports',                     'href' => route('procurement-office.procurement-reports'),         'icon' => 'trending-up'],
            ],
        ], $data);
    }
}
