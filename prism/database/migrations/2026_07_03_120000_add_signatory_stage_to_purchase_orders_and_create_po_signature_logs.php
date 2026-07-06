<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('signatory_stage', 20)->default('draft')->after('status')->index();
        });

        Schema::create('po_signature_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->unsignedTinyInteger('signatory_number')->comment('1–5');
            $table->foreignId('signed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 10); // 'signed' | 'returned'
            $table->text('remarks')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            $table->index(['purchase_order_id', 'signatory_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_signature_logs');
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('signatory_stage');
        });
    }
};
