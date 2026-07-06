<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetProposalItem extends Model
{
    use HasFactory;

    // schedule_type values
    const SCHED9_SUPPLIES       = 'sched9_supplies';
    const SCHED16_CAPITAL       = 'sched16_capital_outlay';

    // ppmp_category values (Schedule 9 sections A–I)
    const PPMP_CATEGORIES = [
        'A' => 'Office Supplies Expenses',
        'B' => 'Accountable Forms Expenses',
        'C' => 'Drugs and Medicines Expenses',
        'D' => 'Medical, Dental and Laboratory Supplies',
        'E' => 'Fuel, Oil and Lubricants Expenses',
        'F' => 'Textbooks and Instructional Materials',
        'G' => 'Other Supplies and Materials',
        'H' => 'Semi-expendable Machinery and Equipment',
        'I' => 'Semi-expendable Furnitures, Fixtures and Books',
    ];

    // iar_flag values (Schedule 16)
    const IAR_INITIAL       = 'I';
    const IAR_AUGMENTATION  = 'A';
    const IAR_REPLACEMENT   = 'R';

    protected $fillable = [
        'budget_proposal_id',
        'created_by_user_id',
        'code',
        'name',
        'description',
        'category',
        'schedule_type',
        'ppmp_category',
        'iar_flag',
        'serviceable_units',
        'unserviceable_units',
        'quantity',
        'unit',
        'estimated_unit_cost',
        'estimated_total_cost',
        'approved_budget',
        'target_quarter',
        'recommended_mode',
        'procurement_mode',
        'override_reason',
        'is_overridden',
        'status',
        'remarks',
        'finance_ok',
        'finance_remark',
        'specifications_json',
    ];

    protected function casts(): array
    {
        return [
            'quantity'              => 'decimal:2',
            'estimated_unit_cost'   => 'decimal:2',
            'estimated_total_cost'  => 'decimal:2',
            'approved_budget'       => 'decimal:2',
            'serviceable_units'     => 'integer',
            'unserviceable_units'   => 'integer',
            'specifications_json'   => 'array',
            'is_overridden'         => 'boolean',
            'finance_ok'            => 'boolean',
        ];
    }

    public function isSched9(): bool { return $this->schedule_type === self::SCHED9_SUPPLIES; }
    public function isSched16(): bool { return $this->schedule_type === self::SCHED16_CAPITAL; }

    public function ppmpCategoryLabel(): ?string
    {
        return self::PPMP_CATEGORIES[$this->ppmp_category] ?? null;
    }

    public function iarLabel(): ?string
    {
        return match ($this->iar_flag) {
            'I' => 'Initial Purchase',
            'A' => 'Augmentation',
            'R' => 'Replacement of Unserviceable Units',
            default => null,
        };
    }

    /** Returns the per-quarter distribution stored in specifications_json. */
    public function quarterDistribution(): array
    {
        return $this->specifications_json['quarter_distribution'] ?? [
            'Q1' => ['qty' => 0, 'amount' => 0],
            'Q2' => ['qty' => 0, 'amount' => 0],
            'Q3' => ['qty' => 0, 'amount' => 0],
            'Q4' => ['qty' => 0, 'amount' => 0],
        ];
    }

    public function budgetProposal(): BelongsTo
    {
        return $this->belongsTo(BudgetProposal::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function marketReferences(): HasMany
    {
        return $this->hasMany(MarketScopingReference::class);
    }

    public function annualProcurementPlanItems(): HasMany
    {
        return $this->hasMany(AnnualProcurementPlanItem::class);
    }
}
