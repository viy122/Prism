<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tags a notification with the specific position it's relevant to
     * (office-head, vcaa, vcaf, bac-member, bac-vice-chair, bac-chair,
     * chancellor, accounting-office) so an account holding more than one
     * position (e.g. Dean + BAC + Vice Chancellor on one login) can be
     * shown a count scoped to whichever position is currently active.
     * Null = generic notification, always shown regardless of position.
     */
    public function up(): void
    {
        Schema::table('prism_notifications', function (Blueprint $table) {
            $table->string('position_key')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('prism_notifications', function (Blueprint $table) {
            $table->dropColumn('position_key');
        });
    }
};
