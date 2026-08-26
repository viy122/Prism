<?php

namespace App\Services;

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

        // Price history is now recorded by the price API itself (one row per
        // real scrape, including background pre-warms), so nothing is
        // persisted here anymore — see prism-price-api app/db.py.

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

    /**
     * Resolve a Google Shopping result's actual merchant link, on demand.
     *
     * shopping_results only ever gives a Google Shopping intermediary link
     * (product_link) — the real store URL requires a separate call to
     * SerpApi's Product API. Only called when a result is actually clicked,
     * so the extra quota cost is bounded by real clicks, not by every
     * result of every search. Fail-soft: returns null on any failure, and
     * the caller falls back to the original Google Shopping link.
     */
    public function resolveDirectLink(string $pageToken): ?string
    {
        $product = $this->fetchImmersiveProduct($pageToken);
        $link    = $product['stores'][0]['link'] ?? null;

        return ($link && preg_match('#^https?://#i', $link)) ? $link : null;
    }

    /**
     * Essential product info (brand, price range, spec features) for the
     * "click to see details" popup on a result card — kept intentionally
     * separate from resolveDirectLink()'s job of sending the user to the
     * merchant site; this stays inside the app.
     */
    public function fetchProductDetails(string $pageToken): ?array
    {
        $product = $this->fetchImmersiveProduct($pageToken);
        if (!$product) {
            return null;
        }

        return [
            'title'       => $product['title']      ?? null,
            'brand'       => $product['brand']       ?? null,
            'price_range' => $product['price_range'] ?? null,
            'thumbnail'   => $product['thumbnails'][0] ?? null,
            'features'    => collect($product['about_the_product']['features'] ?? [])
                ->map(fn ($f) => ['title' => $f['title'] ?? '', 'value' => $f['value'] ?? ''])
                ->filter(fn ($f) => $f['title'] !== '' && $f['value'] !== '')
                ->values()
                ->all(),
            'store_count' => count($product['stores'] ?? []),
        ];
    }

    /**
     * Shared fetch behind resolveDirectLink() and fetchProductDetails() —
     * both need the same google_immersive_product response, just different
     * slices of it. Caching the raw product_results once means clicking
     * both "Source" and "details" on the same result only costs one call.
     */
    private function fetchImmersiveProduct(string $pageToken): ?array
    {
        $apiKey = config('services.serpapi.key');
        if (!$apiKey || $pageToken === '') {
            return null;
        }

        $cacheKey = 'market_scoping_immersive_' . md5($pageToken);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey) ?: null;
        }

        try {
            // shopping_results only carries an opaque `immersive_product_page_token` —
            // that (not product_id) is what this engine needs to return the same
            // "Buying options" / spec detail Google's own product page shows.
            $response = Http::timeout(10)->get(self::SERPAPI_URL, [
                'engine'     => 'google_immersive_product',
                'page_token' => $pageToken,
                'api_key'    => $apiKey,
            ]);

            $product = $response->successful() ? $response->json('product_results') : null;

            // Cache the miss too (null → stored as empty array), so a bad/expired
            // page token doesn't hit SerpApi again on every click.
            Cache::put($cacheKey, $product ?? [], self::CACHE_TTL);

            return $product;
        } catch (\Throwable) {
            return null;
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

                    // Google Shopping's gl=ph/hl=en params only bias which results
                    // and interface language are returned — they don't guarantee an
                    // overseas seller's own listing title is in English. Drop any
                    // title carrying non-Latin script rather than show a Procurement
                    // user an untranslated Chinese/Korean/Arabic/etc. product name.
                    if ($this->looksNonLatin($name)) {
                        return null;
                    }

                    return [
                        'name'            => $name,
                        'price'           => (float) $price,
                        'price_formatted' => '₱' . number_format((float) $price, 2),
                        'image_url'       => $item['thumbnail']    ?? null,
                        'source_icon'     => $item['source_icon']  ?? null,
                        'source_url'      => $item['product_link'] ?? 'https://shopping.google.com/',
                        // Used to resolve the actual merchant link on click (see resolveDirectLink) —
                        // shopping_results only ever gives a Google Shopping intermediary link.
                        'page_token'      => $item['immersive_product_page_token'] ?? null,
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

    /** True if $text contains CJK, Hangul, Cyrillic, Thai, or Arabic script. */
    private function looksNonLatin(string $text): bool
    {
        return (bool) preg_match(
            '/[\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}\x{AC00}-\x{D7A3}\x{0400}-\x{04FF}\x{0E00}-\x{0E7F}\x{0600}-\x{06FF}]/u',
            $text
        );
    }
}
