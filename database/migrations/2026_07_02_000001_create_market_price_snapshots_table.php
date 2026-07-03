<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Market price snapshots — audit trail of prices returned by the
     * PRISM Price Aggregator API (prism-price-api) during market scoping.
     *
     * Every fresh (non-cached) price API search performed by
     * MarketScopingService persists its raw results here via the
     * SaveMarketPriceSnapshots job (dispatched after the response is sent,
     * so users never wait on the insert). Gives procurement a historical,
     * queryable record of market reference prices per item searched.
     */
    public function up(): void
    {
        Schema::create('market_price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('product_name')->index();
            $table->decimal('price', 15, 2);
            $table->decimal('original_price', 15, 2)->nullable();
            $table->string('source_site')->index();
            $table->string('product_url', 2048)->nullable();
            $table->string('department', 30)->nullable()->index();
            $table->string('query_used')->index();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();

            $table->index(['query_used', 'scraped_at'], 'mps_query_scraped_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_price_snapshots');
    }
};
