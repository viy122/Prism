<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoSignatureLog extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'signatory_number',
        'signed_by_user_id',
        'action',
        'remarks',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'signatory_number' => 'integer',
            'signed_at'        => 'datetime',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }
}
