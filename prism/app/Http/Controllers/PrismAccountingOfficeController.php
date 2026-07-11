<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesSignatureQueue;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

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

    public function forMySignature(): View
    {
        return view('prism.shared.for-my-signature', $this->withCommon('for-my-signature', [
            'pageTitle' => 'For My Signature',
            'queueRows' => $this->signatureQueueRows(),
        ]));
    }

    public function dashboard(): View
    {
        $mapPo = fn ($po) => [
            'id'           => $po->id,
            'poNumber'     => $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
            'aocCode'      => $po->abstractOfCanvass->code ?? '—',
            'office'       => $po->abstractOfCanvass->purchaseRequest->office?->name ?? '—',
            'title'        => $po->abstractOfCanvass->purchaseRequest->title ?? '—',
            'supplier'     => $po->supplier_name,
            'totalAmount'  => (float) $po->total_amount,
            'issuedAt'     => $po->issued_at?->format('M d, Y') ?? '—',
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

        $recentlyPaid = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'paidBy'])
            ->where('status', 'paid')
            ->latest('paid_at')
            ->take(10)
            ->get()
            ->map(fn ($po) => [
                'poNumber'    => $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
                'office'      => $po->abstractOfCanvass->purchaseRequest->office?->name ?? '—',
                'title'       => $po->abstractOfCanvass->purchaseRequest->title ?? '—',
                'supplier'    => $po->supplier_name,
                'totalAmount' => (float) $po->total_amount,
                'paidAt'      => $po->paid_at?->format('M d, Y') ?? '—',
                'paidBy'      => $po->paidBy?->name ?? '—',
            ])
            ->all();

        return view('prism.accounting-office.dashboard', $this->withCommon('dashboard', [
            'pageTitle'       => 'Accounting Office Dashboard',
            'forProcessing'   => $forProcessing,
            'awaitingCashier' => $awaitingCashier,
            'recentlyPaid'    => $recentlyPaid,
            'summary'         => [
                'forProcessing'   => count($forProcessing),
                'awaitingCashier' => count($awaitingCashier),
                'totalPaid'       => PurchaseOrder::where('status', 'paid')->count(),
                'totalAmount'     => (float) PurchaseOrder::where('status', 'paid')->sum('total_amount'),
            ],
        ]));
    }

    /** Delivered → Accounting starts processing payment; the Cashier finishes it. */
    public function startPaymentProcessing(PurchaseOrder $po): JsonResponse
    {
        if ($po->status !== 'complete_delivery') {
            return response()->json(['error' => 'Only fully delivered POs can enter payment processing.'], 422);
        }
        if ($po->signatory_stage !== 'fully_signed') {
            return response()->json(['error' => 'PO must be fully signed before payment processing.'], 422);
        }

        $po->update([
            'status'                => 'processing_payment',
            'payment_processing_at' => now(),
        ]);

        return response()->json([
            'success'      => true,
            'statusLabel'  => $po->fresh()->status_label,
            'processingAt' => now()->format('M d, Y'),
        ]);
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
