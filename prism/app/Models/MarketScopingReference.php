<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketScopingReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_proposal_item_id',
        'created_by_user_id',
        'selected_by_user_id',
        'supplier_name',
        'source_type',
        'source_url',
        'title',
        'description',
        'price',
        'currency',
        'date_accessed',
        'match_status',
        'status',
        'is_selected',
        'remarks',
        'specifications_json',
        'source_snapshot_json',
        'ai_analysis_json',
        'selected_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'date_accessed' => 'date',
            'is_selected' => 'boolean',
            'specifications_json' => 'array',
            'source_snapshot_json' => 'array',
            'ai_analysis_json' => 'array',
            'selected_at' => 'datetime',
        ];
    }

    public function budgetProposalItem(): BelongsTo
    {
        return $this->belongsTo(BudgetProposalItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function selectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by_user_id');
    }
}
