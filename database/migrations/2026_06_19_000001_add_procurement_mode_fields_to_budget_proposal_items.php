<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->string('recommended_mode')->nullable()->after('target_quarter');
            $table->string('procurement_mode')->nullable()->after('recommended_mode');
            $table->text('override_reason')->nullable()->after('procurement_mode');
            $table->boolean('is_overridden')->default(false)->after('override_reason');
        });
    }

    public function down(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->dropColumn(['recommended_mode', 'procurement_mode', 'override_reason', 'is_overridden']);
        });
    }
};
