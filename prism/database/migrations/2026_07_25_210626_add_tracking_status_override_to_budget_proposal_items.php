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
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            // Effective tracking status = tracking_status_override ?? best-effort matched PR's
            // tracking status (matched by office + item name — no reliable stored link exists
            // yet between a budget item and the PR eventually raised for it).
            $table->string('tracking_status_override', 50)->nullable()->after('status');
            $table->foreignId('tracking_status_overridden_by_user_id')->nullable()->after('tracking_status_override')
                ->constrained('users', 'id', 'bpi_tracking_overridden_by_fk')->nullOnDelete();
            $table->timestamp('tracking_status_overridden_at')->nullable()->after('tracking_status_overridden_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->dropForeign('bpi_tracking_overridden_by_fk');
            $table->dropColumn(['tracking_status_override', 'tracking_status_overridden_by_user_id', 'tracking_status_overridden_at']);
        });
    }
};
