<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetProposalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_proposal_id',
        'created_by_user_id',
        'code',
        'name',
        'description',
        'category',
        'quantity',
        'unit',
        'estimated_unit_cost',
        'estimated_total_cost',
        'approved_budget',
        'target_quarter',
        'status',
        'remarks',
        'specifications_json',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'estimated_unit_cost' => 'decimal:2',
            'estimated_total_cost' => 'decimal:2',
            'approved_budget' => 'decimal:2',
            'specifications_json' => 'array',
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
