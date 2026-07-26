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
        Schema::table('users', function (Blueprint $table) {
            // Only meaningful for users holding the 'vice-chancellor' role — which
            // specific VC they are (Academic Affairs vs Administration & Finance),
            // since only these two actually sign procurement documents.
            $table->string('vc_type', 10)->nullable()->after('office_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('vc_type');
        });
    }
};
