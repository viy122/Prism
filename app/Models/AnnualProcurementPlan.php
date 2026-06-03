<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnualProcurementPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'office_id',
        'created_by_user_id',
        'approved_by_user_id',
        'code',
        'title',
        'description',
        'fiscal_year',
        'total_estimated_cost',
        'approved_budget',
        'status',
        'remarks',
        'template_data_json',
        'approved_at',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'total_estimated_cost' => 'decimal:2',
            'approved_budget' => 'decimal:2',
            'template_data_json' => 'array',
            'approved_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AnnualProcurementPlanItem::class);
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(DocumentUpload::class, 'attachable');
    }
}
