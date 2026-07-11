<?php

namespace App\Http\Controllers;

use App\Models\DocumentUpload;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PrismCashierController extends Controller
{
    public function dashboard(): View
    {
        // POs Accounting is processing — waiting for the Cashier's receipt
        $forPayment = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office'])
            ->where('status', 'processing_payment')
            ->latest('payment_processing_at')
            ->get()
            ->map(fn ($po) => [
                'id'           => $po->id,
                'poNumber'     => $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
                'office'       => $po->abstractOfCanvass->purchaseRequest->office?->name ?? '—',
                'title'        => $po->abstractOfCanvass->purchaseRequest->title ?? '—',
                'supplier'     => $po->supplier_name,
                'totalAmount'  => (float) $po->total_amount,
                'processingAt' => $po->payment_processing_at?->format('M d, Y') ?? '—',
                'uploadUrl'    => route('cashier.po.upload-receipt', $po->id),
            ])
            ->all();

        $paymentHistory = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'paidBy', 'documents'])
            ->where('status', 'paid')
            ->latest('paid_at')
            ->take(15)
            ->get()
            ->map(function ($po) {
                $receipt = $po->documents->firstWhere('document_type', 'payment_receipt');
                return [
                    'poNumber'    => $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
                    'office'      => $po->abstractOfCanvass->purchaseRequest->office?->name ?? '—',
                    'supplier'    => $po->supplier_name,
                    'totalAmount' => (float) $po->total_amount,
                    'paidAt'      => $po->paid_at?->format('M d, Y') ?? '—',
                    'paidBy'      => $po->paidBy?->name ?? '—',
                    'receiptUrl'  => $receipt ? Storage::url($receipt->file_path) : null,
                ];
            })
            ->all();

        return view('prism.cashier.dashboard', $this->withCommon('dashboard', [
            'pageTitle'      => 'Cashier Dashboard',
            'forPayment'     => $forPayment,
            'paymentHistory' => $paymentHistory,
            'summary'        => [
                'forPayment'  => count($forPayment),
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

        return response()->json([
            'success'    => true,
            'paidAt'     => now()->format('M d, Y'),
            'receiptUrl' => Storage::url($path),
        ]);
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
