<?php

namespace App\Http\Controllers;

use App\Models\{AuditLog, ItemReceipt, PurchaseOrder, PurchaseRequestItem};
use App\Services\ItemReceivingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ItemReceivingController extends Controller
{
    public function dates(Request $request, PurchaseOrder $po, ItemReceivingService $receiving)
    {
        abort_unless($receiving->canManageDates($request->user()), 403);
        $data = $request->validate([
            'procured_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'expected_delivery_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:procured_on'],
            'correction_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($po, $data, $request) {
            $po = PurchaseOrder::lockForUpdate()->findOrFail($po->id);
            $this->assertReceivable($po);
            if (($po->procured_on || $po->expected_delivery_date) && empty($data['correction_reason'])) {
                throw ValidationException::withMessages(['correction_reason' => 'Explain the date update so the change can be audited.']);
            }
            $earliest = $po->itemReceipts()->min('arrival_date');
            if ($earliest && $data['procured_on'] > $earliest) {
                throw ValidationException::withMessages(['procured_on' => 'Procured Date cannot be later than an existing arrival date.']);
            }
            $old = $po->only(['procured_on', 'expected_delivery_date']);
            $po->update(['procured_on' => $data['procured_on'], 'expected_delivery_date' => $data['expected_delivery_date'] ?? null]);
            $this->audit($request, $po, 'procurement_dates_updated', $old, $po->only(['procured_on', 'expected_delivery_date']), $data['correction_reason'] ?? null);
        });

        return back()->with('receiving_success', 'Procurement and expected delivery dates saved.');
    }

    public function store(Request $request, PurchaseRequestItem $item, ItemReceivingService $receiving)
    {
        abort_unless($item->purchaseRequest && $receiving->canReceive($request->user(), $item->purchaseRequest), 403);
        return $this->saveReceipt($request, $item);
    }

    public function update(Request $request, ItemReceipt $receipt, ItemReceivingService $receiving)
    {
        $item = $receipt->item;
        abort_unless($item?->purchaseRequest && $receiving->canReceive($request->user(), $item->purchaseRequest), 403);
        return $this->saveReceipt($request, $item, $receipt);
    }

    private function saveReceipt(Request $request, PurchaseRequestItem $item, ?ItemReceipt $receipt = null)
    {
        $data = $request->validate([
            'arrival_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'quantity' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'received_by_name' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'correction_reason' => [$receipt ? 'required' : 'nullable', 'string', 'max:1000'],
            'submission_token' => [$receipt ? 'nullable' : 'required', 'uuid', 'unique:item_receipts,submission_token'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
        $storedPath = null;
        try {
            DB::transaction(function () use ($request, $item, $receipt, $data, &$storedPath) {
                $poId = $item->purchaseRequest?->abstractOfCanvass?->purchaseOrder?->id;
                if (!$poId) throw ValidationException::withMessages(['arrival_date' => 'A fully signed PO is required before recording receipt.']);
                // Same lock as date editing; serializes partial receipts and chronology checks.
                $po = PurchaseOrder::lockForUpdate()->findOrFail($poId);
                $item = PurchaseRequestItem::lockForUpdate()->findOrFail($item->id);
                $this->assertReceivable($po);
                if (!$receipt && ItemReceipt::where('submission_token', $data['submission_token'])->exists()) {
                    throw ValidationException::withMessages(['submission_token' => 'This receipt was already submitted. Refresh the page to see it.']);
                }
                if ($receipt) {
                    $receipt = ItemReceipt::lockForUpdate()->findOrFail($receipt->id);
                    abort_unless((int) $receipt->purchase_order_id === (int) $po->id, 422);
                }
                if ($po->procured_on && $data['arrival_date'] < $po->procured_on->toDateString()) {
                    throw ValidationException::withMessages(['arrival_date' => 'Arrival Date cannot be earlier than Procured Date.']);
                }
                $received = $item->receipts()->when($receipt, fn ($q) => $q->where('id', '!=', $receipt->id))->sum('quantity');
                $remaining = (int) round((float) $item->quantity * 100) - (int) round((float) $received * 100);
                if ((int) round((float) $data['quantity'] * 100) > $remaining) {
                    throw ValidationException::withMessages(['quantity' => 'Received quantity exceeds the remaining ordered quantity.']);
                }
                $values = array_intersect_key($data, array_flip(['arrival_date', 'quantity', 'received_by_name', 'remarks']));
                if ($request->hasFile('attachment')) {
                    $storedPath = $request->file('attachment')->store('item-receipts', 'local');
                    abort_unless($storedPath, 500, 'The receipt attachment could not be stored.');
                    $values['attachment_path'] = $storedPath;
                    $values['attachment_name'] = $request->file('attachment')->getClientOriginalName();
                }
                $old = $receipt?->toArray();
                if ($receipt) {
                    $receipt->update($values);
                } else {
                    $receipt = ItemReceipt::create($values + [
                        'purchase_order_id' => $po->id, 'purchase_request_item_id' => $item->id,
                        'recorded_by_user_id' => $request->user()->id, 'submission_token' => $data['submission_token'],
                    ]);
                }
                $this->audit($request, $receipt, $old ? 'item_receipt_corrected' : 'item_receipt_recorded', $old, $receipt->fresh()->toArray(), $data['correction_reason'] ?? null);
            });
        } catch (\Throwable $e) {
            if ($storedPath) Storage::disk('local')->delete($storedPath);
            throw $e;
        }

        $message = $receipt ? 'Receipt corrected. The change is recorded in the audit trail.' : 'Item receipt recorded.';
        if ($request->expectsJson()) {
            $item = $item->fresh(['purchaseRequest.office', 'purchaseRequest.abstractOfCanvass.purchaseOrder', 'receipts.recordedBy']);
            $delivery = app(ItemReceivingService::class)->row($item, $request->user());

            return response()->json([
                'message' => $message,
                'delivery' => $delivery,
                'detailsHtml' => view('prism.shared.receiving-details', ['delivery' => $delivery])->render(),
            ]);
        }

        return back()->with('receiving_success', $message);
    }

    public function attachment(Request $request, ItemReceipt $receipt, ItemReceivingService $receiving)
    {
        $query = PurchaseRequestItem::query()->whereKey($receipt->purchase_request_item_id);
        $receiving->scopeFor($query, $request->user());
        abort_unless($query->exists(), 403);
        abort_unless($receipt->attachment_path && Storage::disk('local')->exists($receipt->attachment_path), 404);
        return Storage::disk('local')->download($receipt->attachment_path, $receipt->attachment_name);
    }

    private function assertReceivable(PurchaseOrder $po): void
    {
        if ($po->signatory_stage !== 'fully_signed' || in_array($po->abstractOfCanvass?->purchaseRequest?->status, ['cancelled', 'cancelled_system_error', 'pr_denied'], true)) {
            throw ValidationException::withMessages(['arrival_date' => 'Receiving requires an active, fully signed purchase order.']);
        }
    }

    private function audit(Request $request, $model, string $action, ?array $old, array $new, ?string $reason): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id, 'action' => $action,
            'auditable_type' => $model::class, 'auditable_id' => $model->id,
            'old_values_json' => $old, 'new_values_json' => $new,
            'metadata_json' => ['reason' => $reason], 'ip_address' => $request->ip(),
        ]);
    }
}
