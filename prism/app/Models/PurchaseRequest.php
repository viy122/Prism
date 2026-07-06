<?php

namespace App\Models;

use App\Models\Concerns\HasSignatoryChain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use HasFactory, HasSignatoryChain, SoftDeletes;

    protected $fillable = [
        'annual_procurement_plan_id',
        'office_id',
        'created_by_user_id',
        'submitted_by_user_id',
        'reviewed_by_user_id',
        'approved_by_user_id',
        'number',
        'title',
        'description',
        'fiscal_year',
        'total_amount',
        'status',
        'signatory_stage',
        'third_signer',
        'canvassing_stage',
        'remarks',
        'file_path',
        'extracted_fields_json',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'total_amount' => 'decimal:2',
            'extracted_fields_json' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'uploaded_at' => 'datetime',
        ];
    }

    public function annualProcurementPlan(): BelongsTo
    {
        return $this->belongsTo(AnnualProcurementPlan::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function statusUpdates(): HasMany
    {
        return $this->hasMany(ProcurementStatusUpdate::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(DocumentUpload::class, 'attachable');
    }

    public function signatureLogs(): HasMany
    {
        return $this->hasMany(PrSignatureLog::class);
    }

    public function abstractOfCanvass(): HasOne
    {
        return $this->hasOne(AbstractOfCanvass::class);
    }

    // ── Signatory chain ──────────────────────────────────────────────────────
    // 3rd/4th signers are Accounting and Vice Chancellor in flexible order;
    // `third_signer` records who actually signed 3rd once chosen.

    public const SIGNATORY_DOC_PREFIX = 'PR';

    public const SIGNATORY_STAGES = [
        ['key' => 'draft',              'label' => 'Created',                      'type' => 'routing'],
        ['key' => 'at_end_user',        'label' => 'End User',                     'type' => 'signature'],
        ['key' => 'at_vice_chancellor', 'label' => 'Vice Chancellor',              'type' => 'signature'],
        ['key' => 'at_third_sign',      'label' => 'Accounting / Vice Chancellor', 'type' => 'signature'],
        ['key' => 'at_fourth_sign',     'label' => 'Accounting / Vice Chancellor', 'type' => 'signature'],
        ['key' => 'at_chancellor',      'label' => 'Chancellor',                   'type' => 'signature'],
        ['key' => 'fully_signed',       'label' => 'Fully Signed',                 'type' => 'signature'],
    ];

    protected function resolveStageLabel(array $meta): array
    {
        if (!$this->third_signer || !in_array($meta['key'], ['at_third_sign', 'at_fourth_sign'], true)) {
            return $meta;
        }
        $third         = $this->third_signer === 'accounting' ? 'Accounting' : 'Vice Chancellor';
        $fourth        = $this->third_signer === 'accounting' ? 'Vice Chancellor' : 'Accounting';
        $meta['label'] = $meta['key'] === 'at_third_sign' ? $third : $fourth;
        return $meta;
    }

    public function getCanvassingLabelAttribute(): string
    {
        return match ($this->canvassing_stage) {
            'not_started' => 'Canvassing Not Started',
            'in_progress' => 'Canvassing In Progress',
            'completed'   => 'Canvassing Completed',
            default       => '—',
        };
    }

    public function isReadyForAoc(): bool
    {
        return $this->signatory_stage === 'fully_signed'
            && $this->canvassing_stage === 'completed';
    }
}
