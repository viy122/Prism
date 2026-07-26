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
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Effective tracking status = tracking_status_override ?? currentTrackingStage()
            // (computed live from PR/AOC/PO signatory + PO delivery-payment chains).
            // The override is a temporary manual pin — automatically cleared the next
            // time any real progress event fires, so it can never go silently stale.
            $table->string('tracking_status_override', 50)->nullable()->after('signatory_stage');
            $table->foreignId('tracking_status_overridden_by_user_id')->nullable()->after('tracking_status_override')->constrained('users')->nullOnDelete();
            $table->timestamp('tracking_status_overridden_at')->nullable()->after('tracking_status_overridden_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tracking_status_overridden_by_user_id');
            $table->dropColumn(['tracking_status_override', 'tracking_status_overridden_at']);
        });
    }
};
