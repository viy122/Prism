<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Personnel Services budget items covering:
     *   Schedule 2  — Faculty/staff positions (salaries + statutory benefits)
     *   Schedule 4  — Faculty honoraria (overload pay)
     *
     * Each row is ONE position title / rank entry.
     * All monetary columns are NULL by default — the frontend (or a service)
     * computes them from monthly_salary × no_of_positions and stores them here
     * so the Schedule 2 report can be generated without re-computing.
     *
     * Salary rates reference SSL V (Salary Standardization Law V, 4th Tranche 2019).
     * An optional 8% provision for salary increase can be applied (Schedule 2 note).
     */
    public function up(): void
    {
        Schema::create('personnel_budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // 'sched2_ps' = Personnel Services (Schedule 2)
            // 'sched4_honoraria' = Faculty overload honoraria (Schedule 4)
            $table->string('schedule_type', 20)->default('sched2_ps')->index();

            // ── Schedule 2 fields ──────────────────────────────────────────────
            $table->string('position_title');               // e.g. "Associate Professor II"
            $table->tinyInteger('salary_grade')->nullable();// 1–33 (SSL V)
            $table->unsignedSmallInteger('no_of_positions')->default(1);
            $table->decimal('monthly_salary', 15, 2)->default(0);
            $table->decimal('salary_increase_pct', 5, 2)->default(0); // 8% provision default

            // Computed benefit columns — stored for report generation
            $table->decimal('adjusted_monthly_salary', 15, 2)->nullable();
            $table->decimal('annual_salary', 15, 2)->nullable();       // adj_monthly × 12 × positions
            $table->decimal('pera', 15, 2)->nullable();                // ₱2,000 × 12 × positions
            $table->decimal('clothing_allowance', 15, 2)->nullable();  // ₱7,000 × positions (Instructor I = ₱6,000)
            $table->decimal('year_end_bonus', 15, 2)->nullable();      // 1 month adj. monthly × positions
            $table->decimal('mid_year_bonus', 15, 2)->nullable();      // 1 month adj. monthly × positions
            $table->decimal('pei', 15, 2)->nullable();                 // ₱5,000 × positions
            $table->decimal('cash_gift', 15, 2)->nullable();           // ₱5,000 × positions
            $table->decimal('rlip', 15, 2)->nullable();                // 12% of annual_salary
            $table->decimal('ecip', 15, 2)->nullable();                // ₱100 × 12 × positions
            $table->decimal('pagibig', 15, 2)->nullable();             // ₱100 × 12 × positions
            $table->decimal('philhealth', 15, 2)->nullable();          // 5% of annual_salary / 2

            $table->decimal('total_compensation', 15, 2)->nullable();  // sum of all above

            // ── Schedule 4 fields (stored in same row when schedule_type = sched4_honoraria) ──
            $table->string('employee_name')->nullable();
            $table->string('academic_rank')->nullable();
            $table->decimal('regular_load', 8, 2)->nullable();
            $table->decimal('total_teaching_load', 8, 2)->nullable();
            $table->decimal('overload_per_week', 8, 2)->nullable();
            $table->unsignedSmallInteger('no_of_weeks')->nullable();
            $table->decimal('total_hours_overload', 10, 2)->nullable();
            $table->decimal('rate_per_hour', 10, 2)->nullable();
            $table->decimal('total_honoraria_pay', 15, 2)->nullable();
            $table->decimal('honoraria_increase_provision', 15, 2)->nullable(); // 8-10% of total_honoraria_pay
            $table->decimal('total_adjusted_honoraria', 15, 2)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['budget_proposal_id', 'schedule_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_budget_items');
    }
};
