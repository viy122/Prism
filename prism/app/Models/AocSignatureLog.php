<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AocSignatureLog extends Model
{
    protected $fillable = [
        'abstract_of_canvass_id',
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

    public function abstractOfCanvass(): BelongsTo
    {
        return $this->belongsTo(AbstractOfCanvass::class);
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }
}
