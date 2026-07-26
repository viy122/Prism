<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `vc_signer` assumed VCAA/VCAF was a per-document choice — it isn't.
     * Each Vice-Chancellor-owned stage is statically fixed to one of them
     * (see SIGNATORY_STAGES on PurchaseRequest/AbstractOfCanvass/PurchaseOrder),
     * so the column is unused.
     */
    public function up(): void
    {
        foreach (['purchase_requests', 'abstract_of_canvasses', 'purchase_orders'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('vc_signer');
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
                $table->string('vc_signer', 10)->nullable()->after('signatory_stage');
            });
        }
    }
};
