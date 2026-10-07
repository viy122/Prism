<?php

namespace App\Services;

use App\Models\{PurchaseOrder, PurchaseRequest, PurchaseRequestItem, User};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ItemReceivingService
{
    public function hasRole(User $user, string $role): bool
    {
        return $user->roles->contains('name', $role);
    }

    public function canReceive(User $user, PurchaseRequest $pr): bool
    {
        return $this->hasRole($user, 'Office Head / Dean')
            && $user->office_id && (int) $user->office_id === (int) $pr->office_id;
    }

    public function canManageDates(User $user): bool
    {
        return $this->hasRole($user, 'Procurement Office');
    }

    public function scopeFor(Builder $query, User $user): void
    {
        if ($user->roles->whereIn('name', ['Procurement Office', 'Chancellor', 'Accounting Office', 'Cashier', 'System Administrator'])->isNotEmpty()) {
            return;
        }
        if ($this->hasRole($user, 'Vice Chancellor')) {
            $codes = match ($user->vc_type) {
                'vcaa' => ['CICS', 'COE', 'CBA', 'CAS', 'CCJE', 'CHS', 'CTE', 'LS', 'RS', 'SDS', 'LIB', 'NSTP', 'CULT', 'SPORTS', 'SCHOL', 'HLTHO', 'SCILAB'],
                'vcaf' => ['HRMO', 'ACCT', 'BUD', 'CASH', 'PFM', 'PROC', 'PS', 'RMO', 'GS', 'EMU'],
                default => [],
            };
            $query->whereHas('purchaseRequest.office', fn ($q) => $q->whereIn('code', $codes));
            return;
        }
        if ($this->hasRole($user, 'Office Head / Dean') && $user->office_id) {
            $query->whereHas('purchaseRequest', fn ($q) => $q->where('office_id', $user->office_id));
            return;
        }
        $query->whereRaw('1 = 0');
    }

    public function rows(?User $viewer = null, ?string $office = null, ?string $quarter = null): array
    {
        $query = PurchaseRequestItem::with(['purchaseRequest.office', 'purchaseRequest.abstractOfCanvass.purchaseOrder', 'receipts.recordedBy'])
            ->whereHas('purchaseRequest.abstractOfCanvass.purchaseOrder');
        if ($viewer) $this->scopeFor($query, $viewer);
        if ($office) $query->whereHas('purchaseRequest.office', fn ($q) => $q->where('code', $office));
        if (in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'], true)) {
            $query->where(function ($q) use ($quarter) {
                $q->whereHas('annualProcurementPlanItem', fn ($plan) => $plan->where('target_quarter', $quarter))
                    ->orWhere(function ($unlinked) use ($quarter) {
                        $unlinked->whereDoesntHave('annualProcurementPlanItem')
                            ->whereHas('purchaseRequest', fn ($pr) => $pr->where(function ($number) use ($quarter) {
                                $number->where('number', 'like', '%-'.$quarter.'-%')->orWhere('number', 'like', '%-'.$quarter);
                            }));
                    });
            });
        }

        return $query->orderByDesc('id')->get()->map(fn ($item) => $this->row($item, $viewer))->all();
    }

    public function row(PurchaseRequestItem $item, ?User $viewer = null): array
    {
        $pr = $item->purchaseRequest;
        $po = $pr?->abstractOfCanvass?->purchaseOrder;
        $receipts = $item->receipts->where('purchase_order_id', $po?->id)->sortBy('arrival_date')->values();
        // Integer hundredths avoid decimal quantity rounding at the completion boundary.
        $ordered = (int) round((float) $item->quantity * 100);
        $received = $receipts->sum(fn ($r) => (int) round((float) $r->quantity * 100));
        $complete = $ordered > 0 && $received >= $ordered;
        $last = $receipts->last()?->arrival_date;
        $arrival = $complete ? $last : null;
        $expected = $po?->expected_delivery_date;
        $active = $po && $po->signatory_stage === 'fully_signed' && !in_array($pr->status, ['cancelled', 'cancelled_system_error', 'pr_denied'], true);
        $referenceDate = $complete ? $arrival : Carbon::today();
        $delay = $active && $expected ? max(0, (int) $expected->diffInDays($referenceDate, false)) : null;
        $duration = $arrival && $po?->procured_on ? (int) $po->procured_on->diffInDays($arrival, false) : null;

        return [
            'id' => $item->id, 'poId' => $po?->id, 'office' => $pr?->office?->code ?? '',
            'fiscalYear' => $pr?->fiscal_year, 'item' => $item->name, 'unit' => $item->unit,
            'prNumber' => $pr?->number, 'poNumber' => $po?->po_number,
            'quantity' => $ordered / 100, 'receivedQuantity' => $received / 100,
            'remainingQuantity' => max(0, $ordered - $received) / 100,
            'procuredDate' => $po?->procured_on?->toDateString(),
            'expectedDelivery' => $expected?->toDateString(),
            'arrivalDate' => $arrival?->toDateString(), 'lastArrivalDate' => $last?->toDateString(),
            'receivingStatus' => $complete ? 'Fully Received' : ($received > 0 ? 'Partially Received' : ($po?->procured_on ? 'Awaiting Receipt' : 'Arrival not recorded')),
            'paymentStatus' => $po?->status_label ?? 'No PO',
            'receivingReviewRequired' => !$complete && in_array($po?->status, ['processing_payment', 'paid'], true),
            'daysToReceive' => $duration, 'daysDelayed' => $delay,
            'delayLabel' => !$active ? 'Not applicable' : (!$expected ? 'No target date' : ($delay > 0 ? ($complete ? "{$delay} days late" : "Overdue by {$delay} days") : ($complete ? 'On time' : 'Within target'))),
            'canReceive' => $active && $viewer && $this->canReceive($viewer, $pr),
            'canManageDates' => $active && $viewer && $this->canManageDates($viewer),
            'receipts' => $receipts->map(fn ($r) => [
                'id' => $r->id, 'arrivalDate' => $r->arrival_date->toDateString(), 'quantity' => (float) $r->quantity,
                'receivedBy' => $r->received_by_name, 'recordedBy' => $r->recordedBy?->name ?? 'Former user',
                'recordedAt' => $r->created_at->format('Y-m-d H:i'), 'updatedAt' => $r->updated_at->format('Y-m-d H:i'),
                'remarks' => $r->remarks, 'attachmentName' => $r->attachment_name,
            ])->all(),
        ];
    }
}
