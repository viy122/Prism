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
        // Same shape as third_signer — set once when the document enters its own
        // Vice-Chancellor-owned stage, so stageOwnerRole() knows whether VCAA or
        // VCAF actually owns that stage instance.
        foreach (['purchase_requests', 'abstract_of_canvasses', 'purchase_orders'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('vc_signer', 10)->nullable()->after('signatory_stage');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['purchase_requests', 'abstract_of_canvasses', 'purchase_orders'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('vc_signer');
            });
        }
    }
};
