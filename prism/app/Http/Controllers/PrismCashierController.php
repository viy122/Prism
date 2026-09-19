<?php

namespace App\Http\Controllers;

use App\Models\DocumentUpload;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Smalot\PdfParser\Parser as PdfParser;

class PrismCashierController extends Controller
{
    /**
     * Mirrors the Accounting Office dashboard's layout: a flat "needs action"
     * table with a check-icon "Payment Made" column that opens an attach-receipt
     * popup, plus a "Recently Paid" history table — instead of the previous
     * search + click-to-select master/detail panel.
     */
    public function dashboard(): View
    {
        $pos = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'paidBy', 'documents'])
            ->whereIn('status', ['processing_payment', 'paid'])
            ->latest('id')
            ->get()
            ->map(function ($po) {
                $pr = $po->abstractOfCanvass?->purchaseRequest;
                $processingDoc = $po->documents
                    ->where('document_type', 'payment_processing_proof')
                    ->sortByDesc('uploaded_at')
                    ->first();

                return [
                    'id'            => $po->id,
                    'poNumber'      => $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
                    'office'        => $pr?->office?->code ?? '—',
                    'title'         => $pr?->title ?? '—',
                    'supplier'      => $po->supplier_name,
                    'totalAmount'   => (float) $po->total_amount,
                    'status'        => $po->status,
                    'statusLabel'   => $po->status_label,
                    'processingAt'  => $po->payment_processing_at?->format('M d, Y') ?? '—',
                    'paidAt'        => $po->paid_at?->format('M d, Y') ?? '—',
                    'paidAtRaw'     => $po->paid_at?->toIso8601String(),
                    'paidBy'        => $po->paidBy?->name ?? '—',
                    'uploadUrl'     => route('cashier.po.upload-receipt', $po->id),
                    // The signed PO itself — the Cashier's audit basis before
                    // releasing payment, same as Accounting sees it.
                    'pdfFile'              => $po->file_path,
                    'processingAttachment' => $processingDoc?->file_path,
                    'processingAttachmentName' => $processingDoc?->original_filename,
                    // Every proof attached along the way: Accounting's own
                    // payment-processing proof plus the Cashier's own
                    // receipt, each opened in full in a new tab, not a
                    // cramped preview.
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
                ];
            });

        return view('prism.cashier.dashboard', $this->withCommon('dashboard', [
            'pageTitle'    => 'Cashier Dashboard',
            'forPayment'   => $pos->where('status', 'processing_payment')->values()->all(),
            'recentlyPaid' => $pos->where('status', 'paid')->values()->all(),
            'summary'   => [
                'forPayment'  => $pos->where('status', 'processing_payment')->count(),
                'totalPaid'   => PurchaseOrder::where('status', 'paid')->count(),
                'totalAmount' => (float) PurchaseOrder::where('status', 'paid')->sum('total_amount'),
            ],
        ]));
    }

    /** Upload the payment receipt and mark the PO as paid — the final step of the flow. */
    public function uploadReceipt(Request $request, PurchaseOrder $po): JsonResponse
    {
        if ($po->status !== 'processing_payment') {
            return response()->json(['error' => 'Only POs in payment processing can receive a receipt.'], 422);
        }

        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('receipt');

        // No fixed receipt template exists (could be a formal OR, a bank
        // slip, a photographed receipt, anything) — so item/price lines
        // can't be reliably parsed. What can be checked for a PDF with a
        // real text layer: the receipt should show a total that COVERS the
        // PO's amount (more is fine — that's just change/sukli handed back
        // — less means this isn't proof the full payment went through), and
        // the supplier's name should actually appear on it. A scanned/image
        // receipt (or a PDF with no text at all) is let through leniently,
        // the same "can't validate what can't be read" rule applied
        // everywhere else in this system.
        $isPdf = $file->getClientMimeType() === 'application/pdf' || $file->getClientOriginalExtension() === 'pdf';
        if ($isPdf) {
            $text = $this->readPdfText($file);
            if (trim($text) !== '') {
                if (!$this->receiptCoversAmount($text, (float) $po->total_amount)) {
                    return response()->json([
                        'error' => 'This receipt doesn\'t appear to show a total covering the PO\'s amount (₱'
                            . number_format((float) $po->total_amount, 2)
                            . '). Attach the receipt for this PO\'s full payment.',
                    ], 422);
                }
                if ($po->supplier_name && !$this->textMentionsSupplier($text, $po->supplier_name)) {
                    return response()->json([
                        'error' => 'This receipt doesn\'t appear to mention the supplier "' . $po->supplier_name
                            . '". Attach the receipt issued by this PO\'s supplier.',
                    ], 422);
                }
            }
        }

        $path = $file->store('receipts/' . now()->year, 'public');

        DocumentUpload::create([
            'uploaded_by_user_id' => auth()->id(),
            'attachable_type'     => PurchaseOrder::class,
            'attachable_id'       => $po->id,
            'document_type'       => 'payment_receipt',
            'title'               => 'Payment receipt for ' . ($po->po_number ?? 'PO-' . $po->id),
            'original_filename'   => $file->getClientOriginalName(),
            'file_path'           => $path,
            'mime_type'           => $file->getClientMimeType(),
            'file_size'           => $file->getSize(),
            'status'              => 'uploaded',
            'remarks'             => $request->input('remarks'),
            'uploaded_at'         => now(),
        ]);

        $po->update([
            'status'          => 'paid',
            'paid_by_user_id' => auth()->id(),
            'paid_at'         => now(),
        ]);
        $po->abstractOfCanvass?->purchaseRequest?->clearTrackingOverride();

        return response()->json([
            'success'    => true,
            'paidAt'     => now()->format('M d, Y'),
            'receiptUrl' => Storage::url($path),
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
     * Whether $text contains a currency figure >= $target (within a small
     * rounding tolerance). Showing MORE than the PO's total is fine — that's
     * just change/sukli handed back — showing less means this receipt isn't
     * proof the full amount was actually paid.
     */
    private function receiptCoversAmount(string $text, float $target): bool
    {
        if (!preg_match_all('/(?:₱|Php|PHP)?\s*([\d,]{1,3}(?:,\d{3})*\.\d{2}|\d+\.\d{2})/u', $text, $matches)) {
            return false;
        }

        foreach ($matches[1] as $raw) {
            $amount = (float) str_replace(',', '', $raw);
            if ($amount >= $target - 1.0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Supplier-name check: matches only if EVERY distinctive word (3+
     * letters, excluding pure legal-entity suffixes like "corp"/"inc") from
     * the supplier's name shows up somewhere in the receipt text.
     *
     * Matching on ANY single word was tried first but false-positived on an
     * unrelated document that happened to share one common word — e.g. some
     * other PO's paperwork mentioning "Accounting Office" was enough to
     * "match" a supplier named "... Office Supplies Center" purely because
     * both contain "office". Requiring ALL of the supplier's words closes
     * that gap: dropping a word from the requirement only makes an accidental
     * match easier, so words are kept in unless they're a generic suffix
     * that's genuinely uninformative (adds no distinguishing power) —
     * everything else stays required, even ordinary-sounding words like
     * "office" or "center", because combined with the rest of the name they
     * make coincidental collisions very unlikely.
     */
    private function textMentionsSupplier(string $text, string $supplierName): bool
    {
        $normalizedText = strtolower($text);
        $stopWords = ['corp', 'corporation', 'incorporated', 'inc', 'ltd', 'llc', 'co', 'trading', 'enterprises', 'enterprise', 'company', 'general', 'merchandise', 'and', 'the'];

        $words = preg_split('/[^a-z0-9]+/i', strtolower($supplierName), -1, PREG_SPLIT_NO_EMPTY);
        $significant = array_filter($words, fn ($w) => strlen($w) >= 3 && !in_array($w, $stopWords, true));

        if (empty($significant)) {
            return true;
        }

        foreach ($significant as $word) {
            if (!str_contains($normalizedText, $word)) {
                return false;
            }
        }

        return true;
    }

    private function withCommon(string $activePage, array $data): array
    {
        return array_merge([
            'activeRole'       => 'cashier',
            'activeModulePage' => $activePage,
            'brandHref'        => route('cashier.dashboard'),
            'roleLabel'        => 'Cashier',
            'roleInitials'     => 'CA',
            'roleNavigation'   => \App\Support\PrismNav::roleNavigation(),
            'moduleNavLabel'   => 'Cashier pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard', 'label' => 'Payments & Receipts', 'href' => route('cashier.dashboard'), 'icon' => 'receipt-2'],
            ],
        ], $data);
    }
}
