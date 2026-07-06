<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketPriceSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_proposal_id',
        'submitted_by_user_id',
        'ref_number',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function budgetProposal(): BelongsTo
    {
        return $this->belongsTo(BudgetProposal::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }
}
