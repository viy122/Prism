<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->string('source_of_fund', 100)->nullable()->after('category');
            // Whether the item is part of the office's originally-approved PPMP
            // or was added afterward as a supplemental item — distinct from the
            // (separately tracked) supply-type `category` column.
            $table->string('item_classification', 20)->nullable()->after('source_of_fund');
        });
    }

    public function down(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->dropColumn(['source_of_fund', 'item_classification']);
        });
    }
};
