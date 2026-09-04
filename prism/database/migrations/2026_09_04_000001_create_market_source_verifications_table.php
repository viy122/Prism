<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The price-api microservice owns the actual list of Market Scoping sources
 * (its own SQLite vendor_registry) — this table only records PRISM's own
 * "an admin manually confirmed this shop is a legitimate PhilGEPS-registered
 * supplier" judgment on top of that, keyed by the same `source` name the
 * price-api uses everywhere else (see PrismAdminController::marketSources()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_source_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('source_name')->unique();
            $table->string('seller_name')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_source_verifications');
    }
};
