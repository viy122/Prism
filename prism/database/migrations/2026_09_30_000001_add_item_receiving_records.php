<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Actual supplier order confirmation, independent of draft creation/payment.
            $table->date('procured_on')->nullable();
        });

        Schema::create('item_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('purchase_request_item_id')->constrained()->restrictOnDelete();
            $table->date('arrival_date');
            $table->decimal('quantity', 12, 2);
            $table->string('received_by_name');
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->uuid('submission_token')->unique();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->timestamps();
            $table->index(['purchase_request_item_id', 'arrival_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_receipts');
        Schema::table('purchase_orders', fn (Blueprint $table) => $table->dropColumn('procured_on'));
    }
};
