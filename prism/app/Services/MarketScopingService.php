<?php

namespace App\Services;

use App\Jobs\SaveMarketPriceSnapshots;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MarketScopingService
{
    private const CACHE_TTL    = 86400; // 24 hours
    private const SERPAPI_URL  = 'https://serpapi.com/search.json';
    private const MATCHER_URL  = 'http://localhost:5001';

    private bool $matcherAvailable = true;

    public function isQuotaExhausted(): bool
    {
        return Cache::get('serpapi_quota_exhausted', false);
    }

    public function matcherAvailable(): bool
    {
        return $this->matcherAvailable;
    }

    public function search(string $query, int $limit = 5, ?string $department = null): array
    {
        $cacheKey = 'market_scoping_' . md5(
            strtolower(trim($query)) . '|' . $limit . '|' . strtolower($department ?? '')
        );

        if (Cache::has($cacheKey)) {
            return array_map(
                fn ($item) => array_merge($item, ['cached' => true]),
                Cache::get($cacheKey)
            );
        }

        // Local price aggregator (PS-DBM + PH stores) — free/official prices.
        // Fail-soft: returns [] when the service is down, SerpApi still runs.
        $priceApi        = new PriceApiService();
        $priceApiResults = $priceApi->search($query, $limit, $department);

        $serpResults = $this->searchGoogleShopping($query, $limit);

        $results = $this->interleave($priceApiResults, $serpResults, $limit);

        if (!empty($results)) {
            Cache::put($cacheKey, $results, self::CACHE_TTL);
        }

        // Persist an audit snapshot of the fresh price API rows after the
        // response is sent — the user never waits on this insert.
        if (!empty($priceApi->rawResults())) {
            SaveMarketPriceSnapshots::dispatchAfterResponse(
                $priceApi->rawResults(),
                $query,
                $department
            );
        }

        return $results;
    }

    /**
     * Alternate items from both lists (price API first) so neither source
     * monopolizes the visible results, capped at $limit.
     */
    private function interleave(array $a, array $b, int $limit): array
    {
        $merged = [];
        $max = max(count($a), count($b));

        for ($i = 0; $i < $max && count($merged) < $limit; $i++) {
            if ($i < count($a)) {
                $merged[] = $a[$i];
            }
            if (count($merged) >= $limit) {
                break;
            }
            if ($i < count($b)) {
                $merged[] = $b[$i];
            }
        }

        return $merged;
    }

    public function matchSpecs(array $results, array $specs, string $query = ''): array
    {
        if (empty($results)) {
            return $results;
        }

        try {
            $payload = array_map(fn ($r) => array_merge($r, [
                'title'       => $r['name']       ?? '',
                'supplier'    => $r['source']     ?? '',
                'url'         => $r['source_url'] ?? '',
                'description' => $r['snippet']    ?? '',
            ]), $results);

            $response = Http::timeout(10)->post(self::MATCHER_URL . '/match', [
                'item'    => $query,
                'specs'   => $specs,
                'results' => $payload,
            ]);

            if (!$response->successful()) {
                $this->matcherAvailable = false;
                return $results;
            }

            // v2 response: { mode, matched, count }
            $json = $response->json();
            if (!is_array($json['matched'] ?? null)) {
                $this->matcherAvailable = false;
                return $results;
            }
            return $json['matched'];
        } catch (\Throwable) {
            $this->matcherAvailable = false;
            return $results;
        }
    }

    public function flagAdvantageous(array $results, float $budget): array
    {
        if ($budget <= 0 || empty($results)) {
            return $results;
        }

        try {
            $payload = array_map(fn ($r) => array_merge($r, [
                'title'       => $r['name']    ?? $r['title']       ?? '',
                'description' => $r['snippet'] ?? $r['description'] ?? '',
            ]), $results);

            $response = Http::timeout(10)->post(self::MATCHER_URL . '/advantageous', [
                'budget'  => $budget,
                'results' => $payload,
            ]);

            if (!$response->successful()) {
                $this->matcherAvailable = false;
                return $results;
            }
            return $response->json();
        } catch (\Throwable) {
            $this->matcherAvailable = false;
            return $results;
        }
    }

    private function searchGoogleShopping(string $query, int $limit): array
    {
        $apiKey = config('services.serpapi.key');

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
                $errorMsg = strtolower((string) ($response->json('error') ?? ''));
                if ($response->status() === 429
                    || str_contains($errorMsg, 'run out')
                    || str_contains($errorMsg, 'quota')
                    || str_contains($errorMsg, 'limit')) {
                    Cache::put('serpapi_quota_exhausted', true, 3600);
                }
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
