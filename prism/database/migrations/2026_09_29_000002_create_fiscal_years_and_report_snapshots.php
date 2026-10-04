<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->unsignedInteger('year')->primary();
            $table->string('status')->default('open');
            $table->boolean('is_active')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('procurement_report_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fiscal_year');
            $table->unsignedInteger('version');
            $table->longText('payload');
            $table->string('checksum', 64);
            $table->foreignId('finalized_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamp('created_at');
            $table->unique(['fiscal_year', 'version']);
            $table->foreign('fiscal_year')->references('year')->on('fiscal_years');
        });
        $years = collect([now()->year, now()->year + 1]);
        foreach (['budget_proposals', 'purchase_requests', 'annual_procurement_plans'] as $table) {
            $years = $years->merge(DB::table($table)->whereNotNull('fiscal_year')->distinct()->pluck('fiscal_year'));
        }
        foreach ($years->unique() as $year) {
            DB::table('fiscal_years')->insert([
                'year' => $year, 'status' => 'open', 'is_active' => $year == now()->year,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_report_snapshots');
        Schema::dropIfExists('fiscal_years');
    }
};
