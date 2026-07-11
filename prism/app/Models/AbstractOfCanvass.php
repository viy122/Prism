<?php

namespace App\Models;

use App\Models\Concerns\HasSignatoryChain;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbstractOfCanvass extends Model
{
    use HasSignatoryChain, SoftDeletes;

    protected $fillable = [
        'purchase_request_id',
        'created_by_user_id',
        'code',
        'signatory_stage',
        'remarks',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function signatureLogs(): HasMany
    {
        return $this->hasMany(AocSignatureLog::class);
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    // ── Signatory chain ──────────────────────────────────────────────────────
    // Internal Audit reviews the AOC but does not sign (routing step).

    public const SIGNATORY_DOC_PREFIX = 'AOC';

    public const SIGNATORY_STAGES = [
        ['key' => 'draft',             'label' => 'Created',                       'type' => 'routing'],
        ['key' => 'at_end_user',       'label' => 'End User',                      'type' => 'signature'],
        ['key' => 'at_bac_member',     'label' => 'BAC Member',                    'type' => 'signature', 'role' => 'bac'],
        ['key' => 'at_bac_vice_chair', 'label' => 'BAC Vice Chairperson',          'type' => 'signature', 'role' => 'bac'],
        ['key' => 'at_bac_chair',      'label' => 'BAC Chairperson',               'type' => 'signature', 'role' => 'bac'],
        ['key' => 'at_vc_countersign', 'label' => 'Vice Chancellor – Countersign', 'type' => 'signature', 'role' => 'vice-chancellor'],
        ['key' => 'at_audit',          'label' => 'Internal Audit – Review',       'type' => 'routing'],
        ['key' => 'at_chancellor',     'label' => 'Chancellor',                    'type' => 'signature', 'role' => 'chancellor'],
        ['key' => 'fully_signed',      'label' => 'Fully Signed',                  'type' => 'signature'],
    ];
}
