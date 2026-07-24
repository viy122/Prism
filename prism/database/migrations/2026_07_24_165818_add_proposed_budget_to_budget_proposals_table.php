<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budget_proposals', function (Blueprint $table) {
            // Office head's own target/ceiling budget — separate from total_estimated_cost
            // (auto-derived from the sum of encoded items) so it can be set before or
            // independent of item encoding, and from approved_budget (never wired up yet,
            // reserved for a future chancellor-set final amount).
            $table->decimal('proposed_budget', 15, 2)->nullable()->after('total_estimated_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_proposals', function (Blueprint $table) {
            $table->dropColumn('proposed_budget');
        });
    }
};
