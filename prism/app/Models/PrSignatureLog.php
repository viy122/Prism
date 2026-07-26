<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PrSignatureLog extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'signatory_number',
        'signed_by_user_id',
        'action',
        'remarks',
        'signed_at',
        'photo_path',
        'blurred_photo_path',
        'signature_boxes_json',
        'detection_status',
        'photo_uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'signatory_number'     => 'integer',
            'signed_at'            => 'datetime',
            'signature_boxes_json' => 'array',
            'photo_uploaded_at'    => 'datetime',
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

    public function attachments(): MorphMany
    {
        return $this->morphMany(SignatureAttachment::class, 'attachable');
    }
}
