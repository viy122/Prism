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
        'tracking_status_override',
        'tracking_status_overridden_by_user_id',
        'tracking_status_overridden_at',
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
            'tracking_status_overridden_at' => 'datetime',
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

    public function canvassDocuments(): MorphMany
    {
        return $this->documents()->where('document_type', 'canvass_quotation');
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
        ['key' => 'at_end_user',        'label' => 'End User',                     'type' => 'signature', 'role' => 'office-head'],
        ['key' => 'at_vice_chancellor', 'label' => 'Vice Chancellor (VCAA)',              'type' => 'signature', 'role' => 'vcaa'],
        ['key' => 'at_third_sign',      'label' => 'Accounting / Vice Chancellor (VCAF)', 'type' => 'signature'],
        ['key' => 'at_fourth_sign',     'label' => 'Accounting / Vice Chancellor (VCAF)', 'type' => 'signature'],
        ['key' => 'at_chancellor',      'label' => 'Chancellor',                   'type' => 'signature', 'role' => 'chancellor'],
        ['key' => 'fully_signed',       'label' => 'Fully Signed',                 'type' => 'signature'],
    ];

    protected function resolveStageLabel(array $meta): array
    {
        if (!$this->third_signer || !in_array($meta['key'], ['at_third_sign', 'at_fourth_sign'], true)) {
            return $meta;
        }
        $third         = $this->third_signer === 'accounting' ? 'Accounting' : 'Vice Chancellor (VCAF)';
        $fourth        = $this->third_signer === 'accounting' ? 'Vice Chancellor (VCAF)' : 'Accounting';
        $meta['label'] = $meta['key'] === 'at_third_sign' ? $third : $fourth;
        return $meta;
    }

    /**
     * 3rd/4th slots are Accounting + Vice Chancellor in flexible order —
     * `third_signer` (validated when the VC completes their own stage)
     * determines whose queue owns each slot.
     */
    public function stageOwnerRole(?string $stageKey): ?string
    {
        if (in_array($stageKey, ['at_third_sign', 'at_fourth_sign'], true)) {
            if (!$this->third_signer) {
                return null; // undetermined — procurement resolves it
            }
            $thirdRole = $this->third_signer === 'accounting' ? 'accounting-office' : 'vcaf';
            $fourthRole = $this->third_signer === 'accounting' ? 'vcaf' : 'accounting-office';
            return $stageKey === 'at_third_sign' ? $thirdRole : $fourthRole;
        }

        return $this->stageMetaFor($stageKey)['role'] ?? null;
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

    /**
     * Overrides HasSignatoryChain's default: a draft-stage PR with no attached
     * document was never actually imported from BSU (the only real creation
     * path always sets file_path) — likely placeholder/planned data, so it
     * reads "PR to be Created" rather than the misleading "PR Created".
     */
    public function getSignatoryLabelAttribute(): string
    {
        if ($this->signatory_stage === 'draft' && !$this->file_path) {
            return 'PR to be Created';
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

    // ── Unified tracking status (PR → AOC → PO → Payment) ──────────────────────
    // Derived live from the chain rather than stored, so it can never drift out
    // of sync with the real signatory/delivery state the way `status` can.

    /** Terminal states set via the manual "Update Processing Status" dropdown that
     *  halt the journey outright — these override the chain, since a cancelled or
     *  denied PR isn't "still progressing" no matter what stage it stopped at. */
    private const HALTED_STATUS_LABELS = [
        'pr_denied'              => 'PR Denied',
        'cancelled'              => 'Cancelled',
        'cancelled_system_error' => 'Cancelled – System Error',
    ];

    /** Where this PR's whole journey currently stands, computed from the chain. */
    public function currentTrackingStage(): array
    {
        if (isset(self::HALTED_STATUS_LABELS[$this->status])) {
            return ['key' => 'halted:' . $this->status, 'label' => self::HALTED_STATUS_LABELS[$this->status]];
        }

        if ($this->signatory_stage === 'draft' && !$this->file_path) {
            return ['key' => 'pr:not_yet_created', 'label' => 'PR to be Created'];
        }

        if ($this->signatory_stage !== 'fully_signed') {
            return ['key' => 'pr:' . $this->signatory_stage, 'label' => $this->signatory_label];
        }

        // Fully signed doesn't mean canvassing happened yet — that's a distinct
        // step of its own (Canvassing tab upload), tracked separately via
        // canvassing_stage rather than folded into the AOC branch below.
        if ($this->canvassing_stage !== 'completed') {
            return ['key' => 'for_canvassing', 'label' => 'For Canvassing'];
        }

        $aoc = $this->abstractOfCanvass;
        if (!$aoc) {
            return ['key' => 'awaiting_aoc', 'label' => 'Canvassing Done, Waiting for AOC'];
        }

        if ($aoc->signatory_stage === 'draft' && !$aoc->file_path) {
            return ['key' => 'aoc:not_yet_created', 'label' => 'AOC to be Created'];
        }

        if ($aoc->signatory_stage !== 'fully_signed') {
            return ['key' => 'aoc:' . $aoc->signatory_stage, 'label' => $aoc->signatory_label];
        }

        $po = $aoc->purchaseOrder;
        if (!$po) {
            return ['key' => 'awaiting_po', 'label' => 'Awaiting Purchase Order'];
        }

        if ($po->signatory_stage === 'draft' && !$po->file_path) {
            return ['key' => 'po:not_yet_created', 'label' => 'PO to be Created'];
        }

        if ($po->signatory_stage !== 'fully_signed') {
            return ['key' => 'po:' . $po->signatory_stage, 'label' => $po->signatory_label];
        }

        if ($po->status === 'paid') {
            return ['key' => 'paid', 'label' => 'Paid – Payment Made (Cashier)'];
        }

        return ['key' => 'po_status:' . $po->status, 'label' => $po->status_label];
    }

    /** Invalidate a manual tracking-status pin — called whenever real progress happens. */
    public function clearTrackingOverride(): void
    {
        if ($this->tracking_status_override !== null) {
            $this->update([
                'tracking_status_override'             => null,
                'tracking_status_overridden_by_user_id' => null,
                'tracking_status_overridden_at'         => null,
            ]);
        }
    }

    /** Effective displayed tracking status — manual override wins when set. */
    public function effectiveTrackingStatus(): array
    {
        if ($this->tracking_status_override) {
            $option = collect(static::allTrackingStageOptions())
                ->firstWhere('key', $this->tracking_status_override);

            return [
                'key'      => $this->tracking_status_override,
                'label'    => $option['label'] ?? $this->tracking_status_override,
                'override' => true,
            ];
        }

        return $this->currentTrackingStage() + ['override' => false];
    }

    /** Full ordered list of every possible tracking-stage key/label, for the dropdown. */
    public static function allTrackingStageOptions(): array
    {
        $options = [
            ['key' => 'pr:not_yet_created', 'label' => 'PR to be Created'],
        ];

        foreach (static::SIGNATORY_STAGES as $stage) {
            if ($stage['key'] === 'fully_signed') {
                continue;
            }
            $options[] = ['key' => 'pr:' . $stage['key'], 'label' => static::formatStageLabel(static::SIGNATORY_DOC_PREFIX, $stage)];
        }

        $options[] = ['key' => 'for_canvassing', 'label' => 'For Canvassing'];
        $options[] = ['key' => 'awaiting_aoc', 'label' => 'Canvassing Done, Waiting for AOC'];
        $options[] = ['key' => 'aoc:not_yet_created', 'label' => 'AOC to be Created'];

        foreach (AbstractOfCanvass::SIGNATORY_STAGES as $stage) {
            if ($stage['key'] === 'fully_signed') {
                continue;
            }
            $options[] = ['key' => 'aoc:' . $stage['key'], 'label' => static::formatStageLabel(AbstractOfCanvass::SIGNATORY_DOC_PREFIX, $stage)];
        }

        $options[] = ['key' => 'awaiting_po', 'label' => 'Awaiting Purchase Order'];
        $options[] = ['key' => 'po:not_yet_created', 'label' => 'PO to be Created'];

        foreach (PurchaseOrder::SIGNATORY_STAGES as $stage) {
            if ($stage['key'] === 'fully_signed') {
                continue;
            }
            $options[] = ['key' => 'po:' . $stage['key'], 'label' => static::formatStageLabel(PurchaseOrder::SIGNATORY_DOC_PREFIX, $stage)];
        }

        foreach (array_diff(PurchaseOrder::statusChain(), ['paid']) as $status) {
            $options[] = ['key' => 'po_status:' . $status, 'label' => (new PurchaseOrder(['status' => $status]))->status_label];
        }

        $options[] = ['key' => 'paid', 'label' => 'Paid – Payment Made (Cashier)'];

        foreach (self::HALTED_STATUS_LABELS as $status => $label) {
            $options[] = ['key' => 'halted:' . $status, 'label' => $label];
        }

        return $options;
    }

    private static function formatStageLabel(string $prefix, array $stage): string
    {
        return match ($stage['key']) {
            'draft'        => $prefix . ' Created',
            'fully_signed' => $prefix . ' – Fully Signed',
            default        => $prefix . ' – At ' . $stage['label'],
        };
    }
}
