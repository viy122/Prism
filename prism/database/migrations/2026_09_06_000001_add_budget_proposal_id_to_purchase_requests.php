<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Real traceability from a PR straight back to the specific approved PPMP it
 * was raised against — the office+item-name matching (matchPrItemsByOfficeAndName)
 * everywhere else in the app is a fallback for PRs that predate this column
 * (imported/seeded data), not something new uploads should still rely on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->foreignId('budget_proposal_id')->nullable()->after('annual_procurement_plan_id')
                ->constrained('budget_proposals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('budget_proposal_id');
        });
    }
};
