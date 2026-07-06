<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelBudgetItem extends Model
{
    protected $fillable = [
        'budget_proposal_id',
        'created_by_user_id',
        'schedule_type',
        'position_title',
        'salary_grade',
        'no_of_positions',
        'monthly_salary',
        'salary_increase_pct',
        'adjusted_monthly_salary',
        'annual_salary',
        'pera',
        'clothing_allowance',
        'year_end_bonus',
        'mid_year_bonus',
        'pei',
        'cash_gift',
        'rlip',
        'ecip',
        'pagibig',
        'philhealth',
        'total_compensation',
        'employee_name',
        'academic_rank',
        'regular_load',
        'total_teaching_load',
        'overload_per_week',
        'no_of_weeks',
        'total_hours_overload',
        'rate_per_hour',
        'total_honoraria_pay',
        'honoraria_increase_provision',
        'total_adjusted_honoraria',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'salary_grade'                  => 'integer',
            'no_of_positions'               => 'integer',
            'monthly_salary'                => 'decimal:2',
            'salary_increase_pct'           => 'decimal:2',
            'adjusted_monthly_salary'       => 'decimal:2',
            'annual_salary'                 => 'decimal:2',
            'pera'                          => 'decimal:2',
            'clothing_allowance'            => 'decimal:2',
            'year_end_bonus'                => 'decimal:2',
            'mid_year_bonus'                => 'decimal:2',
            'pei'                           => 'decimal:2',
            'cash_gift'                     => 'decimal:2',
            'rlip'                          => 'decimal:2',
            'ecip'                          => 'decimal:2',
            'pagibig'                       => 'decimal:2',
            'philhealth'                    => 'decimal:2',
            'total_compensation'            => 'decimal:2',
            'regular_load'                  => 'decimal:2',
            'total_teaching_load'           => 'decimal:2',
            'overload_per_week'             => 'decimal:2',
            'no_of_weeks'                   => 'integer',
            'total_hours_overload'          => 'decimal:2',
            'rate_per_hour'                 => 'decimal:2',
            'total_honoraria_pay'           => 'decimal:2',
            'honoraria_increase_provision'  => 'decimal:2',
            'total_adjusted_honoraria'      => 'decimal:2',
        ];
    }

    // ── Computed helpers ──────────────────────────────────────────────────────

    public function isPs(): bool
    {
        return $this->schedule_type === 'sched2_ps';
    }

    public function isHonoraria(): bool
    {
        return $this->schedule_type === 'sched4_honoraria';
    }

    /**
     * Recompute all Sched 2 benefit fields from monthly_salary and no_of_positions.
     * Call before saving when those two values change.
     */
    public function computeBenefits(): static
    {
        $n   = $this->no_of_positions;
        $adj = $this->monthly_salary * (1 + $this->salary_increase_pct / 100);

        $this->adjusted_monthly_salary = round($adj, 2);
        $this->annual_salary           = round($adj * 12 * $n, 2);
        $this->pera                    = round(2000 * 12 * $n, 2);
        $this->clothing_allowance      = round(7000 * $n, 2);
        $this->year_end_bonus          = round($adj * $n, 2);
        $this->mid_year_bonus          = round($adj * $n, 2);
        $this->pei                     = round(5000 * $n, 2);
        $this->cash_gift               = round(5000 * $n, 2);
        $this->rlip                    = round($this->annual_salary * 0.12, 2);
        $this->ecip                    = round(100 * 12 * $n, 2);
        $this->pagibig                 = round(100 * 12 * $n, 2);
        $this->philhealth              = round($this->annual_salary * 0.05 / 2, 2);

        $this->total_compensation = round(
            $this->annual_salary + $this->pera + $this->clothing_allowance
            + $this->year_end_bonus + $this->mid_year_bonus
            + $this->pei + $this->cash_gift
            + $this->rlip + $this->ecip + $this->pagibig + $this->philhealth,
            2
        );

        return $this;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function budgetProposal(): BelongsTo
    {
        return $this->belongsTo(BudgetProposal::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
