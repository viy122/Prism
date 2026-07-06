<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementStatusUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'annual_procurement_plan_item_id',
        'updated_by_user_id',
        'status',
        'remarks',
        'update_data_json',
    ];

    protected function casts(): array
    {
        return ['update_data_json' => 'array'];
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function annualProcurementPlanItem(): BelongsTo
    {
        return $this->belongsTo(AnnualProcurementPlanItem::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
