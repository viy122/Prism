<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'abstract_of_canvass_id',
        'created_by_user_id',
        'po_number',
        'supplier_name',
        'supplier_address',
        'total_amount',
        'status',
        'issued_at',
        'expected_delivery_date',
        'paid_by_user_id',
        'paid_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'           => 'decimal:2',
            'issued_at'              => 'datetime',
            'expected_delivery_date' => 'date',
            'paid_at'                => 'datetime',
        ];
    }

    public function abstractOfCanvass(): BelongsTo
    {
        return $this->belongsTo(AbstractOfCanvass::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'issued'            => 'PO Issued to Supplier',
            'awaiting_delivery' => 'Awaiting Delivery',
            'partial_delivery'  => 'Partial Delivery',
            'complete_delivery' => 'Complete Delivery',
            'receipt_uploaded'  => 'Delivery Receipt Uploaded',
            'paid'              => 'Payment Made',
            default             => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public static function statusChain(): array
    {
        return ['issued', 'awaiting_delivery', 'partial_delivery', 'complete_delivery', 'receipt_uploaded', 'paid'];
    }

    public function nextStatus(): ?string
    {
        $chain = self::statusChain();
        $idx   = array_search($this->status, $chain);
        return ($idx !== false && $idx < count($chain) - 1) ? $chain[$idx + 1] : null;
    }
}
