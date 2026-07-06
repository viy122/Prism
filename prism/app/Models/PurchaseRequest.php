<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

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

    // Human-readable label for the current signatory stage
    public function getSignatoryLabelAttribute(): string
    {
        return match ($this->signatory_stage) {
            'draft'           => 'PR Created',
            'at_end_user'     => 'PR – At End User (1st Signatory)',
            'at_signatory_2'  => 'PR – At 2nd Signatory',
            'at_signatory_3'  => 'PR – At 3rd Signatory',
            'at_signatory_4'  => 'PR – At 4th Signatory',
            'at_chancellor'   => 'PR – At Chancellor',
            'fully_signed'    => 'PR – Fully Signed',
            default           => ucfirst(str_replace('_', ' ', $this->signatory_stage)),
        };
    }

    public static function signatoryStages(): array
    {
        return ['draft', 'at_end_user', 'at_signatory_2', 'at_signatory_3', 'at_signatory_4', 'at_chancellor', 'fully_signed'];
    }

    public function nextSignatoryStage(): ?string
    {
        $stages = self::signatoryStages();
        $idx    = array_search($this->signatory_stage, $stages);
        return ($idx !== false && $idx < count($stages) - 1) ? $stages[$idx + 1] : null;
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
