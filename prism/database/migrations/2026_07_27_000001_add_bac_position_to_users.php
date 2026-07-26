<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AOC's three BAC-owned stages (at_bac_member, at_bac_vice_chair,
     * at_bac_chair) all share the generic 'bac' role code — this column
     * distinguishes which specific seat a BAC-role user actually holds,
     * mirroring how vc_type distinguishes VCAA/VCAF.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bac_position', 20)->nullable()->after('vc_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bac_position');
        });
    }
};
