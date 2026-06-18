<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MarketScopingService
{
    private const CACHE_TTL    = 86400; // 24 hours
    private const SERPAPI_URL  = 'https://serpapi.com/search.json';

    public function search(string $query, int $limit = 5): array
    {
        $cacheKey = 'market_scoping_' . md5(strtolower(trim($query)));

        if (Cache::has($cacheKey)) {
            return array_map(
                fn ($item) => array_merge($item, ['cached' => true]),
                Cache::get($cacheKey)
            );
        }

        $results = $this->searchGoogleShopping($query, $limit);

        if (!empty($results)) {
            Cache::put($cacheKey, $results, self::CACHE_TTL);
        }

        return $results;
    }

    private function searchGoogleShopping(string $query, int $limit): array
    {
        $apiKey = env('SERPAPI_KEY');

        if (!$apiKey) {
            return [];
        }

        try {
            $response = Http::timeout(15)->get(self::SERPAPI_URL, [
                'engine'   => 'google_shopping',
                'q'        => $query,
                'gl'       => 'ph',        // Philippines — prices in PHP peso
                'hl'       => 'en',
                'num'      => $limit,
                'api_key'  => $apiKey,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $items = $response->json('shopping_results', []);

            return collect($items)
                ->take($limit)
                ->map(function ($item) {
                    $name  = $item['title']           ?? null;
                    $price = $item['extracted_price'] ?? null;

                    if (!$name || !$price) {
                        return null;
                    }

                    return [
                        'name'            => $name,
                        'price'           => (float) $price,
                        'price_formatted' => '₱' . number_format((float) $price, 2),
                        'image_url'       => $item['thumbnail']    ?? null,
                        'source_icon'     => $item['source_icon']  ?? null,
                        'source_url'      => $item['product_link'] ?? 'https://shopping.google.com/',
                        'source'          => $item['source']       ?? 'Google Shopping',
                        'rating'          => $item['rating']       ?? null,
                        'reviews'         => $item['reviews']      ?? null,
                        'snippet'         => $item['snippet']      ?? null,
                        'date_retrieved'  => now()->format('M d, Y'),
                        'cached'          => false,
                    ];
                })
                ->filter()
                ->values()
                ->all();

        } catch (\Throwable) {
            return [];
        }
    }
}
