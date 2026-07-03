<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Client for the PRISM Price Aggregator API (prism-price-api) — a local
 * FastAPI service that fans out to PS-DBM catalogues and PH e-commerce
 * stores and returns normalized price rows.
 *
 * Fail-soft by design: if the service is down or errors, search() returns
 * an empty array and the SerpApi flow continues unaffected.
 */
class PriceApiService
{
    /**
     * Raw rows exactly as returned by the price API (for snapshot persistence).
     *
     * @var array<int, array<string, mixed>>
     */
    private array $rawResults = [];

    /**
     * Search the price API and map results to the same array shape produced
     * by MarketScopingService::searchGoogleShopping().
     */
    public function search(string $query, int $limit = 30, ?string $department = null): array
    {
        $this->rawResults = [];

        $params = ['item' => $query, 'limit' => min($limit, 100)];
        if ($department !== null) {
            $params['department'] = $department;
        }

        try {
            $response = Http::timeout(12)->get(
                rtrim(config('services.price_api.url'), '/') . '/search',
                $params
            );

            if (!$response->successful()) {
                return [];
            }

            $this->rawResults = $response->json('results', []);

            return collect($this->rawResults)
                ->map(function ($item) {
                    $name  = $item['description'] ?? null;
                    $price = $item['price_php']   ?? null;

                    if (!$name || !$price) {
                        return null;
                    }

                    return [
                        'name'            => $name,
                        'price'           => (float) $price,
                        'price_formatted' => '₱' . number_format((float) $price, 2),
                        'image_url'       => $item['image_url'] ?? null,
                        'source_icon'     => null,
                        'source_url'      => $item['url'] ?? '',
                        'source'          => $item['seller'] ?? ($item['source'] ?? 'Price API'),
                        'rating'          => null,
                        'reviews'         => null,
                        'snippet'         => null,
                        'is_official'     => (bool) ($item['is_official'] ?? false),
                        'date_retrieved'  => now()->format('M d, Y'),
                        'cached'          => false,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        } catch (\Throwable) {
            $this->rawResults = [];
            return [];
        }
    }

    /**
     * Raw price API rows from the most recent search() call.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rawResults(): array
    {
        return $this->rawResults;
    }
}
