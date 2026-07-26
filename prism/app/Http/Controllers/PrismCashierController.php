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
    /**
     * Cashier isn't part of the signatory chain — POs pass through a separate
     * delivery/payment `status` chain, and the only action here is uploading
     * a receipt once a PO reaches `processing_payment`. Mirrors the other
     * roles' "For My Signature" list + detail-panel UI: shows EVERY issued
     * PO (not just those currently awaiting a receipt), with `canAct` gating
     * the upload action to the ones actually at Cashier's step right now.
     */
    public function dashboard(): View
    {
        $chain = PurchaseOrder::statusChain();

        $pos = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'paidBy', 'documents'])
            ->latest()
            ->get()
            ->map(function ($po) use ($chain) {
                $pr      = $po->abstractOfCanvass?->purchaseRequest;
                $current = array_search($po->status, $chain);
                $current = $current === false ? 0 : $current;

                $receipt = $po->documents->firstWhere('document_type', 'payment_receipt');

                return [
                    'id'          => $po->id,
                    'poNumber'    => $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
                    'office'      => $pr?->office?->name ?? '—',
                    'title'       => $pr?->title ?? '—',
                    'supplier'    => $po->supplier_name,
                    'totalAmount' => (float) $po->total_amount,
                    'remarks'     => $po->remarks ?: '—',
                    'status'      => $po->status,
                    'statusLabel' => $po->status_label,
                    'canAct'      => $po->status === 'processing_payment',
                    'chain'       => collect($chain)->map(function ($key, $idx) use ($current, $po) {
                        return [
                            'key'    => $key,
                            'label'  => (clone $po)->fill(['status' => $key])->status_label,
                            'status' => $idx < $current ? 'done' : ($idx === $current ? 'active' : 'pending'),
                        ];
                    })->values()->all(),
                    'receiptUrl'  => $receipt ? Storage::url($receipt->file_path) : null,
                    'paidAt'      => $po->paid_at?->format('M d, Y g:i A') ?? '—',
                    'paidBy'      => $po->paidBy?->name ?? '—',
                    'uploadUrl'   => route('cashier.po.upload-receipt', $po->id),
                ];
            })
            ->all();

        return view('prism.cashier.dashboard', $this->withCommon('dashboard', [
            'pageTitle' => 'Cashier Dashboard',
            'purchaseOrders' => $pos,
            'summary'   => [
                'forPayment'  => collect($pos)->where('status', 'processing_payment')->count(),
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
        $po->abstractOfCanvass?->purchaseRequest?->clearTrackingOverride();

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
