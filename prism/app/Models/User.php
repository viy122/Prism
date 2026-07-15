<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'employee_number',
        'office_id',
        'position_title',
        'password',
        'account_status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot(['assigned_by_user_id', 'assigned_at']);
    }

    public function officeAssignments(): BelongsToMany
    {
        return $this->belongsToMany(Office::class, 'office_user_assignments')
            ->withPivot(['assigned_by_user_id', 'role_in_office', 'starts_on', 'ends_on', 'is_primary'])
            ->withTimestamps();
    }

    public function preparedBudgetProposals(): HasMany
    {
        return $this->hasMany(BudgetProposal::class, 'created_by_user_id');
    }

    public function submittedBudgetProposals(): HasMany
    {
        return $this->hasMany(BudgetProposal::class, 'submitted_by_user_id');
    }

    public function prismNotifications(): HasMany
    {
        return $this->hasMany(PrismNotification::class);
    }
}
