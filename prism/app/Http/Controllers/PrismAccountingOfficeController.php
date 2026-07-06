<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PrismAccountingOfficeController extends Controller
{
    public function dashboard(): View
    {
        $awaitingPayment = PurchaseOrder::with(['abstractOfCanvass.purchaseRequest.office', 'createdBy'])
            ->where('status', 'receipt_uploaded')
            ->latest()
            ->get()
            ->map(fn ($po) => [
                'id'           => $po->id,
                'poNumber'     => $po->po_number ?? 'PO-' . str_pad($po->id, 4, '0', STR_PAD_LEFT),
                'aocCode'      => $po->abstractOfCanvass->code ?? '—',
                'office'       => $po->abstractOfCanvass->purchaseRequest->office?->name ?? '—',
                'title'        => $po->abstractOfCanvass->purchaseRequest->title ?? '—',
                'supplier'     => $po->supplier_name,
                'totalAmount'  => (float) $po->total_amount,
                'issuedAt'     => $po->issued_at?->format('M d, Y') ?? '—',
                'confirmUrl'   => route('accounting-office.po.confirm-payment', $po->id),
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

        $totalPaid    = PurchaseOrder::where('status', 'paid')->count();
        $totalPending = PurchaseOrder::where('status', 'receipt_uploaded')->count();
        $totalAmount  = (float) PurchaseOrder::where('status', 'paid')->sum('total_amount');

        return view('prism.accounting-office.dashboard', $this->withCommon('dashboard', [
            'pageTitle'      => 'Accounting Office Dashboard',
            'awaitingPayment'=> $awaitingPayment,
            'recentlyPaid'   => $recentlyPaid,
            'summary'        => [
                'totalPaid'    => $totalPaid,
                'totalPending' => $totalPending,
                'totalAmount'  => $totalAmount,
            ],
        ]));
    }

    public function confirmPayment(PurchaseOrder $po): JsonResponse
    {
        if ($po->status !== 'receipt_uploaded') {
            return response()->json(['error' => 'Only POs with uploaded receipts can be confirmed as paid.'], 422);
        }

        $po->update([
            'status'          => 'paid',
            'paid_by_user_id' => auth()->id(),
            'paid_at'         => now(),
        ]);

        return response()->json([
            'success' => true,
            'paidAt'  => now()->format('M d, Y'),
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
            'roleNavigation'   => [
                ['slug' => 'office-head',        'label' => 'Office Head / Dean', 'href' => route('office-head.dashboard')],
                ['slug' => 'finance-office',     'label' => 'Finance Office',      'href' => route('finance-office.dashboard')],
                ['slug' => 'procurement-office', 'label' => 'Procurement Office',  'href' => route('procurement-office.dashboard')],
                ['slug' => 'chancellor',         'label' => 'Chancellor',           'href' => route('chancellor.dashboard')],
                ['slug' => 'vice-chancellor',    'label' => 'Vice Chancellor',      'href' => route('vice-chancellor.dashboard')],
                ['slug' => 'accounting-office',  'label' => 'Accounting Office',    'href' => route('accounting-office.dashboard')],
            ],
            'moduleNavLabel'   => 'Accounting Office pages',
            'moduleNavigation' => [
                ['slug' => 'dashboard', 'label' => 'Payment Confirmation', 'href' => route('accounting-office.dashboard'), 'icon' => 'cash'],
            ],
        ], $data);
    }
}
