<?php

namespace App\Services;

use App\Models\{AuditLog, PurchaseOrder};
use Illuminate\Support\Facades\DB;

class DeliveryStatusService
{
    /** Re-read under the same PO lock used when recording or correcting receipts. */
    public function sync(int $poId, ?int $userId = null, ?string $ip = null, bool $dryRun = false): array
    {
        return DB::transaction(function () use ($poId, $userId, $ip, $dryRun) {
            $po = PurchaseOrder::lockForUpdate()->findOrFail($poId);
            $pr = $po->abstractOfCanvass?->purchaseRequest;
            $result = ['id' => $po->id, 'number' => $po->po_number, 'from' => $po->status, 'to' => $po->status, 'review' => false];
            if (!$pr || $po->signatory_stage !== 'fully_signed'
                || in_array($pr->status, ['cancelled', 'cancelled_system_error', 'pr_denied'], true)) {
                return $result;
            }

            $items = $pr->items()->with(['receipts' => fn ($q) => $q->where('purchase_order_id', $po->id)])->get();
            // Historical delivery/payment states without receipt evidence cannot be reconstructed.
            if ($items->isEmpty() || !$items->contains(fn ($item) => $item->receipts->isNotEmpty())) {
                return $result;
            }
            $complete = $items->every(function ($item) {
                $ordered = (int) round((float) $item->quantity * 100);
                $received = $item->receipts->sum(fn ($receipt) => (int) round((float) $receipt->quantity * 100));
                return $ordered > 0 && $received >= $ordered;
            });
            $deliveryStatus = $complete ? 'complete_delivery' : 'partial_delivery';
            $paymentStarted = in_array($po->status, ['processing_payment', 'paid'], true);
            $result['review'] = $paymentStarted && !$complete;
            if (!$paymentStarted && in_array($po->status, ['issued', 'awaiting_delivery', 'partial_delivery', 'complete_delivery'], true)) {
                $result['to'] = $deliveryStatus;
            }
            if ($dryRun) return $result;

            if ($result['from'] !== $result['to']) {
                $po->update(['status' => $result['to']]);
                $pr->clearTrackingOverride();
                AuditLog::create([
                    'user_id' => $userId, 'ip_address' => $ip,
                    'action' => 'po_delivery_synced', 'auditable_type' => PurchaseOrder::class, 'auditable_id' => $po->id,
                    'old_values_json' => ['status' => $result['from']],
                    'new_values_json' => ['status' => $result['to']],
                    'metadata_json' => ['source' => 'item_receipts', 'reason' => 'Delivery status recalculated from all ordered items.'],
                ]);
            }

            // Log each transition into/out of a payment discrepancy, without duplicating backfill logs.
            $lastReview = AuditLog::where('auditable_type', PurchaseOrder::class)->where('auditable_id', $po->id)
                ->whereIn('action', ['po_receiving_review_required', 'po_receiving_review_resolved'])->latest('id')->first();
            $wasFlagged = $lastReview?->action === 'po_receiving_review_required';
            if ($result['review'] !== $wasFlagged) {
                AuditLog::create([
                    'user_id' => $userId, 'ip_address' => $ip,
                    'action' => $result['review'] ? 'po_receiving_review_required' : 'po_receiving_review_resolved',
                    'auditable_type' => PurchaseOrder::class, 'auditable_id' => $po->id,
                    'old_values_json' => ['review_required' => $wasFlagged],
                    'new_values_json' => ['review_required' => $result['review']],
                    'metadata_json' => ['source' => 'item_receipts', 'payment_status' => $po->status, 'receiving_status' => $deliveryStatus],
                ]);
            }
            return $result;
        });
    }
}
