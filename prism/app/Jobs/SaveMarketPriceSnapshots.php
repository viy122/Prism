<?php

namespace App\Jobs;

use App\Models\MarketPriceSnapshot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Persists price API results into market_price_snapshots. Dispatched with
 * dispatchAfterResponse() so the user receives their search results first
 * and the insert happens after the response is sent.
 */
class SaveMarketPriceSnapshots implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array<int, array<string, mixed>> $rawResults  Raw rows from the price API
     */
    public function __construct(
        private array $rawResults,
        private string $query,
        private ?string $department = null,
    ) {}

    public function handle(): void
    {
        $now = now();

        $rows = collect($this->rawResults)
            ->map(function ($item) use ($now) {
                $name  = trim((string) ($item['description'] ?? ''));
                $price = $item['price_php'] ?? null;

                if ($name === '' || !is_numeric($price)) {
                    return null;
                }

                return [
                    'product_name'   => mb_substr($name, 0, 255),
                    'price'          => (float) $price,
                    'original_price' => null,
                    'source_site'    => mb_substr((string) ($item['source'] ?? 'Unknown'), 0, 255),
                    'product_url'    => mb_substr((string) ($item['url'] ?? ''), 0, 2048) ?: null,
                    'department'     => $this->department,
                    'query_used'     => mb_substr($this->query, 0, 255),
                    'scraped_at'     => isset($item['scraped_at'])
                        ? \Illuminate\Support\Carbon::parse($item['scraped_at'])
                        : $now,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if (!empty($rows)) {
            MarketPriceSnapshot::insert($rows);
        }
    }
}
