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
            // 'not_started' | 'in_progress' | 'completed'
            // Set to null until PR reaches fully_signed stage
            $table->string('canvassing_stage', 20)->nullable()->after('signatory_stage')->index();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('canvassing_stage');
        });
    }
};
