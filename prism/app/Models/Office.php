<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'campus_id',
        'parent_office_id',
        'code',
        'name',
        'office_type',
        'email',
        'phone',
        'location',
        'status',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function parentOffice(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_office_id');
    }

    public function childOffices(): HasMany
    {
        return $this->hasMany(self::class, 'parent_office_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'office_user_assignments')
            ->withPivot(['assigned_by_user_id', 'role_in_office', 'starts_on', 'ends_on', 'is_primary'])
            ->withTimestamps();
    }

    public function budgetProposals(): HasMany
    {
        return $this->hasMany(BudgetProposal::class);
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }
}
