<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->date('procurement_start_date')->nullable()->after('target_quarter');
            $table->date('date_needed')->nullable()->after('procurement_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->dropColumn(['procurement_start_date', 'date_needed']);
        });
    }
};
