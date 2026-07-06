<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnualProcurementPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'annual_procurement_plan_id',
        'budget_proposal_item_id',
        'created_by_user_id',
        'code',
        'name',
        'description',
        'quantity',
        'unit',
        'estimated_unit_cost',
        'estimated_total_cost',
        'approved_budget',
        'target_quarter',
        'procurement_mode',
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

    public function annualProcurementPlan(): BelongsTo
    {
        return $this->belongsTo(AnnualProcurementPlan::class);
    }

    public function budgetProposalItem(): BelongsTo
    {
        return $this->belongsTo(BudgetProposalItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function purchaseRequestItems()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
}
