<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetProposal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'office_id',
        'created_by_user_id',
        'submitted_by_user_id',
        'reviewed_by_user_id',
        'approved_by_user_id',
        'code',
        'title',
        'description',
        'fiscal_year',
        'total_estimated_cost',
        'approved_budget',
        'status',
        'remarks',
        'form_data_json',
        'submitted_at',
        'reviewed_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'total_estimated_cost' => 'decimal:2',
            'approved_budget' => 'decimal:2',
            'form_data_json' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
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
        return $this->hasMany(BudgetProposalItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BudgetProposalReview::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(DocumentUpload::class, 'attachable');
    }
}
