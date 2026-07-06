<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'annual_procurement_plan_item_id',
        'name',
        'description',
        'quantity',
        'unit',
        'estimated_unit_cost',
        'estimated_total_cost',
        'approved_budget',
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

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function annualProcurementPlanItem(): BelongsTo
    {
        return $this->belongsTo(AnnualProcurementPlanItem::class);
    }
}
