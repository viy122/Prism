<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetProposalReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_proposal_id',
        'reviewed_by_user_id',
        'role_id',
        'action',
        'status_from',
        'status_to',
        'remarks',
        'review_data_json',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'review_data_json' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function budgetProposal(): BelongsTo
    {
        return $this->belongsTo(BudgetProposal::class);
    }

    public function reviewerRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
