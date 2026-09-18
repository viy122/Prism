<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two more columns of the official BSU PPMP form (Column 2: Type of the
 * Project, Column 5: Pre-Procurement Conference) that belong to the
 * encoding office, not to Procurement Office's later Annual Procurement
 * Plan stage — see procurement_mode / procurement_start_date / date_needed
 * (added in 2026_06_19_000001 and 2026_09_05_233022) for those.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->string('project_type', 30)->nullable()->after('item_classification');
            $table->boolean('pre_procurement_conference')->nullable()->after('project_type');
        });
    }

    public function down(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->dropColumn(['project_type', 'pre_procurement_conference']);
        });
    }
};
