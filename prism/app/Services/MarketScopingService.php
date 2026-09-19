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
        $hasAnyProvider = $this->primaryKey() !== null || $this->backupKey() !== null
            || $this->serperKey() !== null || $this->lazadaKey() !== null;
        if (!$hasAnyProvider) {
            return false;
        }

        $serpApiUsable = $this->activeApiKey() !== null;
        $serperUsable  = $this->serperKey() !== null && !$this->isSlotExhausted('serper');
        $lazadaUsable  = $this->lazadaKey() !== null && !$this->isSlotExhausted('lazada');

        return !$serpApiUsable && !$serperUsable && !$lazadaUsable;
    }

    private function primaryKey(): ?string
    {
        return config('services.serpapi.key') ?: null;
    }

    private function backupKey(): ?string
    {
        return config('services.serpapi.backup_key') ?: null;
    }

    private function serperKey(): ?string
    {
        return config('services.serper.key') ?: null;
    }

    private function lazadaKey(): ?string
    {
        return config('services.lazada_rapidapi.key') ?: null;
    }

    private function isSlotExhausted(string $slot): bool
    {
        return (bool) Cache::get("serpapi_quota_exhausted_{$slot}", false);
    }

    /**
     * Quota resets monthly, so there's no point retrying a dead key every
     * hour — a day-long cooldown just avoids wasting calls on it.
     */
    private function markSlotExhausted(string $slot): void
    {
        Cache::put("serpapi_quota_exhausted_{$slot}", true, 86400);
    }

    /**
     * The SerpApi key to use right now: the primary key unless its quota is
     * known to be exhausted, in which case the backup key (if configured)
     * takes over automatically. Returns null when no usable key remains.
     *
     * @return array{key: string, slot: string}|null
     */
    private function activeApiKey(): ?array
    {
        if ($this->primaryKey() !== null && !$this->isSlotExhausted('primary')) {
            return ['key' => $this->primaryKey(), 'slot' => 'primary'];
        }
        if ($this->backupKey() !== null && !$this->isSlotExhausted('backup')) {
            return ['key' => $this->backupKey(), 'slot' => 'backup'];
        }

        return null;
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

        // Local price aggregator (PH retailer stores) — free prices, no quota.
        // Fail-soft: returns [] when the service is down, SerpApi still runs.
        $priceApi        = new PriceApiService();
        $priceApiResults = $priceApi->search($query, $limit, $department);

        $serpResults   = $this->searchGoogleShopping($query, $limit);
        $lazadaResults = $this->searchViaLazada($query, $limit);

        $results = $this->interleaveMany([$priceApiResults, $lazadaResults, $serpResults], $limit);

        if (!empty($results)) {
            Cache::put($cacheKey, $results, self::CACHE_TTL);
        }

        // Price history is now recorded by the price API itself (one row per
        // real scrape, including background pre-warms), so nothing is
        // persisted here anymore — see prism-price-api app/db.py.

        return $results;
    }

    /**
     * Alternate items across however many source lists are given (price API
     * first, in the order passed in) so no single source monopolizes the
     * visible results, capped at $limit.
     */
    private function interleaveMany(array $lists, int $limit): array
    {
        $merged = [];
        $max    = $lists ? max(array_map('count', $lists)) : 0;

        for ($i = 0; $i < $max; $i++) {
            foreach ($lists as $list) {
                if ($i >= count($list)) {
                    continue;
                }
                $merged[] = $list[$i];
                if (count($merged) >= $limit) {
                    return $merged;
                }
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
        $active = $this->activeApiKey();
        if ($active === null || $pageToken === '') {
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
                'api_key'    => $active['key'],
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

    /**
     * External Google Shopping data, tried across independent providers in
     * order: SerpApi primary key, SerpApi backup key, then Serper.dev — each
     * with its own separate free quota. Falls through to the next provider
     * only once the current one is confirmed exhausted/unconfigured, not
     * just because a query happened to return zero results.
     */
    private function searchGoogleShopping(string $query, int $limit): array
    {
        if ($this->activeApiKey() !== null) {
            $results = $this->searchViaSerpApi($query, $limit);
            if (!empty($results) || $this->activeApiKey() !== null) {
                return $results;
            }
            // Both SerpApi keys just got marked exhausted by that call —
            // fall through to Serper below.
        }

        return $this->searchViaSerper($query, $limit);
    }

    private function searchViaSerpApi(string $query, int $limit): array
    {
        $active = $this->activeApiKey();
        if ($active === null) {
            return [];
        }

        try {
            $response = Http::timeout(15)->get(self::SERPAPI_URL, [
                'engine'   => 'google_shopping',
                'q'        => $query,
                'gl'       => 'ph',        // Philippines — prices in PHP peso
                'hl'       => 'en',
                'num'      => $limit,
                'api_key'  => $active['key'],
            ]);

            if (!$response->successful()) {
                $errorMsg = strtolower((string) ($response->json('error') ?? ''));
                if ($response->status() === 429
                    || str_contains($errorMsg, 'run out')
                    || str_contains($errorMsg, 'quota')
                    || str_contains($errorMsg, 'limit')) {
                    $this->markSlotExhausted($active['slot']);

                    // The key we just used ran dry — retry once immediately on
                    // whichever key is next in line instead of making the user
                    // re-run the search.
                    if ($this->activeApiKey() !== null) {
                        return $this->searchViaSerpApi($query, $limit);
                    }
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

    /**
     * Serper.dev — a second, independent Google Shopping scraping provider
     * with its own free quota, used once every SerpApi key is exhausted.
     * No immersive-product equivalent is wired up for it, so its results
     * carry no page_token (resolveDirectLink/fetchProductDetails simply
     * fall back to the plain source_url for these, same as any result
     * without a token today).
     */
    private function searchViaSerper(string $query, int $limit): array
    {
        $apiKey = $this->serperKey();
        if ($apiKey === null || $this->isSlotExhausted('serper')) {
            return [];
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(['X-API-KEY' => $apiKey])
                ->post('https://google.serper.dev/shopping', [
                    'q'   => $query,
                    'gl'  => 'ph',
                    'hl'  => 'en',
                    'num' => $limit,
                ]);

            if (!$response->successful()) {
                $errorMsg = strtolower((string) ($response->json('message') ?? $response->json('error') ?? ''));
                if (in_array($response->status(), [401, 403, 429], true)
                    || str_contains($errorMsg, 'quota')
                    || str_contains($errorMsg, 'credit')
                    || str_contains($errorMsg, 'limit')) {
                    $this->markSlotExhausted('serper');
                }
                return [];
            }

            $items = $response->json('shopping', []);

            return collect($items)
                ->take($limit)
                ->map(function ($item) {
                    $name  = $item['title'] ?? null;
                    $price = $this->parsePriceString($item['price'] ?? null);

                    if (!$name || !$price) {
                        return null;
                    }

                    if ($this->looksNonLatin($name)) {
                        return null;
                    }

                    return [
                        'name'            => $name,
                        'price'           => $price,
                        'price_formatted' => '₱' . number_format($price, 2),
                        'image_url'       => $item['imageUrl'] ?? null,
                        'source_icon'     => null,
                        'source_url'      => $item['link'] ?? 'https://shopping.google.com/',
                        'page_token'      => null,
                        'source'          => $item['source']      ?? 'Google Shopping',
                        'rating'          => $item['rating']      ?? null,
                        'reviews'         => $item['ratingCount'] ?? null,
                        'snippet'         => null,
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

    /**
     * Lazada PH product listings (via RapidAPI) — a genuinely different
     * source from SerpApi/Serper (both just Google Shopping wrappers): real
     * marketplace listings, native PHP pricing (no conversion needed), and
     * built-in ratings/review counts. Independent of the SerpApi/Serper
     * quota chain — this runs regardless of whether either of those is
     * exhausted, since it's a separate provider with its own free quota.
     */
    private function searchViaLazada(string $query, int $limit): array
    {
        $apiKey = $this->lazadaKey();
        if ($apiKey === null || $this->isSlotExhausted('lazada')) {
            return [];
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'x-rapidapi-host' => config('services.lazada_rapidapi.host'),
                    'x-rapidapi-key'  => $apiKey,
                ])
                ->get('https://' . config('services.lazada_rapidapi.host') . '/lazada/search/items', [
                    'keywords' => $query,
                    'site'     => 'ph',
                    'sort'     => 'pop',
                    'page'     => 1,
                ]);

            if (!$response->successful()) {
                if (in_array($response->status(), [401, 403, 429], true)) {
                    $this->markSlotExhausted('lazada');
                }
                return [];
            }

            $items = $response->json('data.items', []);

            return collect($items)
                ->reject(fn ($item) => ($item['is_ad'] ?? false) || !($item['is_in_stock'] ?? true))
                ->take($limit)
                ->map(function ($item) {
                    $name  = $item['title'] ?? null;
                    $price = isset($item['price']) ? (float) $item['price'] : null;

                    if (!$name || !$price) {
                        return null;
                    }

                    if ($this->looksNonLatin($name)) {
                        return null;
                    }

                    $reviewInfo = $item['review_info'] ?? [];

                    return [
                        'name'            => $name,
                        'price'           => $price,
                        'price_formatted' => '₱' . number_format($price, 2),
                        'image_url'       => $item['img'] ?? null,
                        'source_icon'     => null,
                        'source_url'      => $item['product_url'] ?? 'https://www.lazada.com.ph/',
                        'page_token'      => null,
                        'source'          => $item['shop_info']['shop_name'] ?? 'Lazada PH',
                        'rating'          => isset($reviewInfo['average_score']) ? round((float) $reviewInfo['average_score'], 1) : null,
                        'reviews'         => isset($reviewInfo['review_count']) ? (int) $reviewInfo['review_count'] : null,
                        'snippet'         => null,
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

    /** Serper prices arrive as formatted strings (e.g. "₱34,999.00"), not floats. */
    private function parsePriceString(?string $raw): ?float
    {
        if ($raw === null) {
            return null;
        }
        $cleaned = preg_replace('/[^0-9.]/', '', $raw);

        return ($cleaned === null || $cleaned === '') ? null : (float) $cleaned;
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
