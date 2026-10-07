<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemReceipt extends Model
{
    protected $fillable = [
        'purchase_order_id', 'purchase_request_item_id', 'arrival_date', 'quantity',
        'received_by_name', 'recorded_by_user_id', 'remarks', 'submission_token',
        'attachment_path', 'attachment_name',
    ];

    protected function casts(): array
    {
        return ['arrival_date' => 'date', 'quantity' => 'decimal:2'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestItem::class, 'purchase_request_item_id');
    }

    public function officeAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OfficeAsset::class, 'item_receipt_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id')->withTrashed();
    }
}
