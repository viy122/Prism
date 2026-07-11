<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->timestamp('payment_processing_at')->nullable()->after('paid_at');
        });

        DB::table('purchase_orders')
            ->where('status', 'receipt_uploaded')
            ->update(['status' => 'processing_payment']);
    }

    public function down(): void
    {
        DB::table('purchase_orders')
            ->where('status', 'processing_payment')
            ->update(['status' => 'receipt_uploaded']);

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('payment_processing_at');
        });
    }
};
