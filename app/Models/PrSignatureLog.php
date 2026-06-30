<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrSignatureLog extends Model
{
    protected $fillable = [
        'purchase_request_id',
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

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }
}
