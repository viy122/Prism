<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add Schedule 9 (PPMP Supplies) and Schedule 16 (Capital Outlay)
     * classification columns to budget_proposal_items.
     *
     * schedule_type   — distinguishes which DBM schedule the item belongs to
     * ppmp_category   — Schedule 9 category code: A (Office Supplies) … I (Furniture/Books)
     * iar_flag        — Schedule 16: I=Initial, A=Augmentation, R=Replacement
     * serviceable_units   — Schedule 16: existing serviceable inventory
     * unserviceable_units — Schedule 16: existing unserviceable inventory
     *
     * Quarter distribution (Q1qty/Q1amt … Q4qty/Q4amt) is stored in the
     * existing specifications_json column as { "quarter_distribution": { "Q1": {qty, amount}, … } }
     */
    public function up(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->string('schedule_type', 30)->nullable()->after('category')->index();
            // sched9_supplies | sched16_capital_outlay

            $table->string('ppmp_category', 10)->nullable()->after('schedule_type');
            // A B C D E F G H I  (as per Schedule 9 section labels)

            $table->char('iar_flag', 1)->nullable()->after('ppmp_category');
            // I A R  (Schedule 16 only)

            $table->unsignedSmallInteger('serviceable_units')->nullable()->after('iar_flag');
            $table->unsignedSmallInteger('unserviceable_units')->nullable()->after('serviceable_units');
        });
    }

    public function down(): void
    {
        Schema::table('budget_proposal_items', function (Blueprint $table) {
            $table->dropColumn([
                'schedule_type',
                'ppmp_category',
                'iar_flag',
                'serviceable_units',
                'unserviceable_units',
            ]);
        });
    }
};
