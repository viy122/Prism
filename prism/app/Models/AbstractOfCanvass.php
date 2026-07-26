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
        'file_path',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
        ];
    }

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

    /**
     * Overrides HasSignatoryChain's default: clicking "For AOC" only creates
     * this row so it can be routed for signatures — no document exists yet,
     * so "AOC Created" would be misleading until uploadAbstractOfCanvass()
     * actually attaches one. Mirrors PurchaseRequest::getSignatoryLabelAttribute().
     */
    public function getSignatoryLabelAttribute(): string
    {
        if ($this->signatory_stage === 'draft' && !$this->file_path) {
            return 'AOC to be Created';
        }

        $meta = $this->stageMetaFor($this->signatory_stage);
        if (!$meta) {
            return ucfirst(str_replace('_', ' ', $this->signatory_stage ?? 'draft'));
        }
        return match ($meta['key']) {
            'draft'        => static::SIGNATORY_DOC_PREFIX . ' Created',
            'fully_signed' => static::SIGNATORY_DOC_PREFIX . ' – Fully Signed',
            default        => static::SIGNATORY_DOC_PREFIX . ' – At ' . $meta['label'],
        };
    }

    // ── Signatory chain ──────────────────────────────────────────────────────

    public const SIGNATORY_DOC_PREFIX = 'AOC';

    public const SIGNATORY_STAGES = [
        ['key' => 'draft',             'label' => 'Created',                       'type' => 'routing'],
        ['key' => 'at_end_user',       'label' => 'End User',                      'type' => 'signature', 'role' => 'office-head'],
        ['key' => 'at_bac_member',     'label' => 'BAC Member',                    'type' => 'signature', 'role' => 'bac'],
        ['key' => 'at_bac_vice_chair', 'label' => 'BAC Vice Chairperson',          'type' => 'signature', 'role' => 'bac'],
        ['key' => 'at_bac_chair',      'label' => 'BAC Chairperson',               'type' => 'signature', 'role' => 'bac'],
        ['key' => 'at_vc_countersign', 'label' => 'Vice Chancellor – Countersign (VCAF)', 'type' => 'signature', 'role' => 'vcaf'],
        ['key' => 'at_chancellor',     'label' => 'Chancellor',                    'type' => 'signature', 'role' => 'chancellor'],
        ['key' => 'fully_signed',      'label' => 'Fully Signed',                  'type' => 'signature'],
    ];
}
