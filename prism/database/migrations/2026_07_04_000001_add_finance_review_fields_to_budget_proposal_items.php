<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->boolean('finance_ok')->nullable()->after('remarks');
            $table->text('finance_remark')->nullable()->after('finance_ok');
        });
    }

    public function down(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->dropColumn(['finance_ok', 'finance_remark']);
        });
    }
};
