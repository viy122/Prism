<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSignatureQueue;
use App\Models\DocumentUpload;
use App\Models\PurchaseOrder;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Smalot\PdfParser\Parser as PdfParser;

class PrismAccountingOfficeController extends Controller
{
    use HandlesSignatureQueue;

    protected function queueRoleCode(): string
    {
        return 'accounting-office';
    }

    protected function queueRoutePrefix(): string
    {
        return 'accounting-office';
    }

    /**
     * Accounting signs PO (fixed 'at_accounting') and, conditionally, PR (the
     * flexible 3rd/4th slot, whenever a VCAA picks Accounting to go first) —
     * never AOC. Shows EVERY PR/PO, not just currently-actionable ones.
     */
    public function forMySignature(): View
    {
        return view('prism.shared.for-my-signature', $this->withCommon('for-my-signature', [
            'pageTitle' => 'For My Signature',
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
        return ['pr', 'po'];
    }

    public function dashboard(): View
    {
        $mapPo = fn ($po) => [
            'fiscalYear'   => $po->abstractOfCanvass?->purchaseRequest?->fiscal_year,
            'id'           => $po->id,
            'poNumber'     => $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
            'aocCode'      => $po->abstractOfCanvass->code ?? '—',
            'office'       => $po->abstractOfCanvass->purchaseRequest->office?->code ?? '—',
            'title'        => $po->abstractOfCanvass->purchaseRequest->title ?? '—',
            'supplier'     => $po->supplier_name,
            'totalAmount'  => (float) $po->total_amount,
            'issuedAt'     => $po->issued_at?->format('M d, Y') ?? '—',
            'statusLabel'  => $po->status_label,
            // The signed PO PDF — Accounting's audit basis before marking
            // payment processed, not just the row's summary fields.
            'pdfFile'      => $po->file_path,
            'pdfUrl'       => $po->file_path ? Storage::url($po->file_path) : null,
        ];

        // Delivered POs waiting for Accounting to start processing payment
        $forProcessing = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'createdBy'])
            ->where('status', 'complete_delivery')
            ->where('signatory_stage', 'fully_signed')
            ->latest()
            ->get()
            ->map(fn ($po) => $mapPo($po) + [
                'processUrl' => route('accounting-office.po.process-payment', $po->id),
            ])
            ->all();

        // Already processing — waiting for the Cashier's receipt upload
        $awaitingCashier = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office'])
            ->where('status', 'processing_payment')
            ->latest('payment_processing_at')
            ->get()
            ->map(fn ($po) => $mapPo($po) + [
                'processingAt' => $po->payment_processing_at?->format('M d, Y') ?? '—',
            ])
            ->all();

        $paidRows = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'paidBy', 'documents'])
            ->where('status', 'paid')
            ->latest('paid_at')
            ->get()
            ->map(fn ($po) => [
                'fiscalYear'  => $po->abstractOfCanvass?->purchaseRequest?->fiscal_year,
                'id'          => $po->id,
                'poNumber'    => $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
                'office'      => $po->abstractOfCanvass->purchaseRequest->office?->code ?? '—',
                'title'       => $po->abstractOfCanvass->purchaseRequest->title ?? '—',
                'supplier'    => $po->supplier_name,
                'totalAmount' => (float) $po->total_amount,
                'paidAt'      => $po->paid_at?->format('M d, Y') ?? '—',
                'paidAtRaw'   => $po->paid_at?->toIso8601String(),
                'paidBy'      => $po->paidBy?->name ?? '—',
                // The signed PO itself, so the PO No. column can open the
                // full document — same as the "for processing" table above.
                'pdfFile'     => $po->file_path,
                'pdfUrl'      => $po->file_path ? Storage::url($po->file_path) : null,
                // Every proof attached along the way: Accounting's own
                // payment-processing proof plus the Cashier's payment
                // receipt, each opened in full in a new tab, not a cramped
                // preview.
                'attachments' => $po->documents
                    ->whereIn('document_type', ['payment_processing_proof', 'payment_receipt'])
                    ->sortBy('uploaded_at')
                    ->map(fn ($d) => [
                        'label'    => $d->document_type === 'payment_receipt' ? 'Receipt (Cashier)' : 'Processing Proof (Accounting)',
                        'filename' => $d->original_filename,
                        'url'      => Storage::url($d->file_path),
                    ])
                    ->values()
                    ->all(),
            ]);

        $recentlyPaid = $paidRows->take(10)->values()->all();
        $kpiDetails = $this->dashboardKpiDetails($forProcessing, $awaitingCashier, $paidRows);

        return view('prism.accounting-office.dashboard', $this->withCommon('dashboard', [
            'pageTitle'       => 'Accounting Office Dashboard',
            'forProcessing'   => $forProcessing,
            'awaitingCashier' => $awaitingCashier,
            'recentlyPaid'    => $recentlyPaid,
            'kpiDetails'      => $kpiDetails,
            'summary'         => [
                'forProcessing'   => count($forProcessing),
                'awaitingCashier' => count($awaitingCashier),
                'totalPaid'       => $paidRows->count(),
                'totalAmount'     => (float) $paidRows->sum('totalAmount'),
            ],
        ]));
    }

    /** Delivered → Accounting attaches proof and starts payment processing; the Cashier finishes it. */
    private function dashboardKpiDetails(array $forProcessing, array $awaitingCashier, $paidRows): array
    {
        $forProcessingRows = collect($forProcessing)
            ->map(fn ($po) => $this->dashboardPoKpiRow(
                $po,
                'Complete Delivery',
                'badge-delivered',
                'Issued',
                $po['issuedAt'] ?? 'â€”',
                'Ready for Accounting payment processing.',
                $po['pdfUrl'] ?? null,
                'View PO'
            ))
            ->values();

        $awaitingCashierRows = collect($awaitingCashier)
            ->map(fn ($po) => $this->dashboardPoKpiRow(
                $po,
                $po['statusLabel'] ?? 'Processing Payment',
                'badge-processing',
                'Processing since',
                $po['processingAt'] ?? 'â€”',
                'Waiting for the Cashier receipt upload.',
                $po['pdfUrl'] ?? null,
                'View PO'
            ))
            ->values();

        $paidKpiRows = $paidRows
            ->map(fn ($po) => $this->dashboardPoKpiRow(
                $po,
                'Payment Made',
                'badge-paid',
                'Paid',
                $po['paidAt'] ?? 'â€”',
                'Paid by ' . ($po['paidBy'] ?? 'Cashier'),
                $po['pdfUrl'] ?? null,
                'View PO'
            ))
            ->values();

        return [
            'forProcessing' => [
                'title'      => 'Delivered - For Processing',
                'lead'       => 'Delivered and fully signed Purchase Orders waiting for Accounting to start payment processing.',
                'rows'       => $forProcessingRows->all(),
                'empty'      => 'No delivered POs are waiting for payment processing.',
                'countLabel' => 'delivered PO(s)',
            ],
            'awaitingCashier' => [
                'title'      => 'Processing - At Cashier',
                'lead'       => 'Purchase Orders already marked as processing payment and waiting for Cashier receipt upload.',
                'rows'       => $awaitingCashierRows->all(),
                'empty'      => 'No POs are currently in payment processing.',
                'countLabel' => 'processing PO(s)',
            ],
            'totalPaid' => [
                'title'      => 'Total POs Paid',
                'lead'       => 'All Purchase Orders already completed by Cashier payment release.',
                'rows'       => $paidKpiRows->all(),
                'empty'      => 'No paid Purchase Orders yet.',
                'countLabel' => 'paid PO(s)',
            ],
            'totalAmount' => [
                'title'      => 'Total Amount Released',
                'lead'       => 'Paid Purchase Orders contributing to the released amount total, sorted by highest amount.',
                'rows'       => $paidKpiRows->sortByDesc('amount')->values()->all(),
                'empty'      => 'No released payment amount yet.',
                'countLabel' => 'released PO(s)',
            ],
        ];
    }

    private function dashboardPoKpiRow(array $po, string $status, string $statusClass, string $dateLabel, string $date, string $remarks, ?string $url, string $urlLabel): array
    {
        return [
            'title'       => ($po['poNumber'] ?? 'PO') . ' - ' . ($po['title'] ?? 'Untitled Purchase Order'),
            'poNumber'    => $po['poNumber'] ?? 'PO',
            'office'      => $po['office'] ?? 'â€”',
            'supplier'    => $po['supplier'] ?? 'â€”',
            'amount'      => (float) ($po['totalAmount'] ?? 0),
            'status'      => $status,
            'statusClass' => $statusClass,
            'dateLabel'   => $dateLabel,
            'date'        => $date,
            'remarks'     => $remarks,
            'url'         => $url,
            'urlLabel'    => $urlLabel,
        ];
    }

    public function startPaymentProcessing(Request $request, PurchaseOrder $po): JsonResponse
    {
        if ($po->status !== 'complete_delivery') {
            return response()->json(['error' => 'Only fully delivered POs can enter payment processing.'], 422);
        }
        if ($po->signatory_stage !== 'fully_signed') {
            return response()->json(['error' => 'PO must be fully signed before payment processing.'], 422);
        }

        $request->validate([
            'attachment' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'remarks'    => 'nullable|string|max:1000',
        ]);

        $file = $request->file('attachment');

        // There's no standardized template for this attachment — it could be
        // a bank transfer slip, an ALOBS printout, an internal memo, anything
        // that stands as proof the payment is actually being processed — so
        // there's nothing to reliably pull a specific field from. The one
        // thing that has to hold regardless of format: the PO's own total
        // amount has to actually appear somewhere in the document. Only
        // checked for a PDF with a real text layer — a scanned/image
        // attachment (or a PDF with no text at all) is let through
        // leniently, the same "can't validate what can't be read" rule
        // applied everywhere else in this system.
        $isPdf = $file->getClientMimeType() === 'application/pdf' || $file->getClientOriginalExtension() === 'pdf';
        if ($isPdf) {
            $text = $this->readPdfText($file);
            if (trim($text) !== '' && !$this->amountAppearsInDocument($text, (float) $po->total_amount)) {
                return response()->json([
                    'error' => 'This document doesn\'t appear to mention the PO\'s total amount (₱'
                        . number_format((float) $po->total_amount, 2)
                        . '). Attach proof that actually shows this PO\'s amount being processed.',
                ], 422);
            }
        }

        $path = $file->store('payment-processing/' . now()->year, 'public');

        DocumentUpload::create([
            'uploaded_by_user_id' => auth()->id(),
            'attachable_type'     => PurchaseOrder::class,
            'attachable_id'       => $po->id,
            'document_type'       => 'payment_processing_proof',
            'title'               => 'Payment processing attachment for ' . ($po->po_number ?? 'PO-' . $po->id),
            'original_filename'   => $file->getClientOriginalName(),
            'file_path'           => $path,
            'mime_type'           => $file->getClientMimeType(),
            'file_size'           => $file->getSize(),
            'status'              => 'uploaded',
            'remarks'             => $request->input('remarks'),
            'uploaded_at'         => now(),
        ]);

        $po->update([
            'status'                => 'processing_payment',
            'payment_processing_at' => now(),
        ]);
        $po->abstractOfCanvass?->purchaseRequest?->clearTrackingOverride();

        NotificationService::paymentProcessingStarted($po->fresh());

        return response()->json([
            'success'      => true,
            'statusLabel'  => $po->fresh()->status_label,
            'processingAt' => now()->format('M d, Y'),
        ]);
    }

    /** Best-effort PDF text-layer read — scanned/image-only uploads just yield ''. */
    private function readPdfText(\Illuminate\Http\UploadedFile $file): string
    {
        try {
            return (new PdfParser())->parseContent($file->get())->getText();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Whether $target shows up anywhere in $text as a currency figure —
     * matched loosely (any ₱/Php-prefixed or bare comma-grouped decimal
     * number that's numerically equal, within peso-rounding tolerance) since
     * there's no fixed template dictating how this document formats it.
     */
    private function amountAppearsInDocument(string $text, float $target): bool
    {
        if (!preg_match_all('/(?:₱|Php|PHP)?\s*([\d,]{1,3}(?:,\d{3})*\.\d{2}|\d+\.\d{2})/u', $text, $matches)) {
            return false;
        }

        foreach ($matches[1] as $raw) {
            $amount = (float) str_replace(',', '', $raw);
            if (abs($amount - $target) < 1.0) {
                return true;
            }
        }

        return false;
    }

    private function withCommon(string $activePage, array $data): array
    {
        return array_merge([
            'activeRole'       => 'accounting-office',
            'activeModulePage' => $activePage,
            'brandHref'        => route('accounting-office.dashboard'),
            'roleLabel'        => 'Accounting Office',
            'roleInitials'     => 'AO',
            'roleNavigation'   => \App\Support\PrismNav::roleNavigation(),
            'moduleNavLabel'   => 'Accounting Office pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard',        'label' => 'Payment Processing', 'href' => route('accounting-office.dashboard'),        'icon' => 'cash'],
                ['slug' => 'for-my-signature', 'label' => 'For My Signature',   'href' => route('accounting-office.for-my-signature'), 'icon' => 'signature'],
            ],
        ], $data);
    }
}
