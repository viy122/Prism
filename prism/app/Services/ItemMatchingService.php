<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pairs the line items read out of one document against the items of the
 * document it is supposed to correspond to.
 *
 * Primary path is PRISM's own local matcher microservice (the same Flask app
 * that already backs market scoping and signature detection), which compares
 * items semantically with sentence-transformer embeddings. It is local, so
 * there is no API key, no per-call cost and no internet dependency.
 *
 * When that service is down the whole thing degrades to a pure-PHP token and
 * edit-distance scorer instead of failing — same contract, slightly blunter
 * judgement — mirroring how MarketScopingService treats the same microservice.
 */
class ItemMatchingService
{
    /** Same local Flask service market scoping and signature detection already use. */
    private const MATCHER_URL = 'http://localhost:5001';

    /** Below this score two items are not considered the same thing. */
    public const DEFAULT_THRESHOLD = 0.55;

    /**
     * Hard ceiling on items compared per side. Pairing is inherently O(n*m);
     * this keeps a pathological upload from stalling a web request.
     */
    private const MAX_ITEMS = 500;

    /** Above this many pairs the PHP fallback prefilters by shared tokens. */
    private const PREFILTER_ABOVE_PAIRS = 20000;

    /** Remembers a down matcher briefly so uploads don't re-pay the connect timeout. */
    private const DOWN_CACHE_KEY     = 'item_matcher_unavailable';
    private const DOWN_CACHE_SECONDS = 60;

    private bool $matcherAvailable = true;

    public function available(): bool
    {
        return $this->matcherAvailable;
    }

    private function matcherUrl(): string
    {
        return rtrim((string) config('services.matcher.url', self::MATCHER_URL), '/');
    }

    /**
     * @param  array<int, array{name: string, quantity?: float|int, unit?: string}>  $left
     * @param  array<int, array{name: string, quantity?: float|int, unit?: string}>  $right
     * @return array{strategy: string, matches: array<int, array{leftIndex:int, rightIndex:?int, score:float, matched:bool}>, unmatchedRight: array<int, int>, truncated: bool}
     */
    public function match(array $left, array $right, float $threshold = self::DEFAULT_THRESHOLD): array
    {
        $truncated = count($left) > self::MAX_ITEMS || count($right) > self::MAX_ITEMS;
        $left      = array_slice(array_values($left), 0, self::MAX_ITEMS);
        $right     = array_slice(array_values($right), 0, self::MAX_ITEMS);

        if (!$left) {
            return [
                'strategy'       => 'none',
                'matches'        => [],
                'unmatchedRight' => array_keys($right),
                'truncated'      => $truncated,
            ];
        }

        $result = $this->matchViaMicroservice($left, $right, $threshold)
            ?? $this->matchInPhp($left, $right, $threshold);

        $result['truncated'] = $truncated;

        return $result;
    }

    /** @return array|null null when the microservice can't answer, so the caller falls back. */
    private function matchViaMicroservice(array $left, array $right, float $threshold): ?array
    {
        // A refused connection still costs seconds to discover on Windows, and
        // validation runs on an interactive upload. Once the service is known
        // to be down, stop paying that cost on every subsequent item list for
        // a while — the PHP scorer answers immediately instead.
        if (Cache::get(self::DOWN_CACHE_KEY)) {
            $this->matcherAvailable = false;
            return null;
        }

        try {
            $response = Http::connectTimeout(2)->timeout(15)->post($this->matcherUrl() . '/match-items', [
                'left'      => array_map(fn ($i) => ['name' => (string) ($i['name'] ?? '')], $left),
                'right'     => array_map(fn ($i) => ['name' => (string) ($i['name'] ?? '')], $right),
                'threshold' => $threshold,
            ]);

            if (!$response->successful()) {
                $this->matcherAvailable = false;
                return null;
            }

            $json = $response->json();
            if (!is_array($json) || !isset($json['matches'])) {
                $this->matcherAvailable = false;
                return null;
            }

            $this->matcherAvailable = true;

            return [
                'strategy'       => $json['strategy'] ?? 'semantic',
                'matches'        => array_map(fn ($m) => [
                    'leftIndex'  => (int) $m['left_index'],
                    'rightIndex' => isset($m['right_index']) ? (int) $m['right_index'] : null,
                    'score'      => (float) ($m['score'] ?? 0),
                    'matched'    => (bool) ($m['matched'] ?? false),
                ], $json['matches']),
                'unmatchedRight' => array_map('intval', $json['unmatched_right'] ?? []),
            ];
        } catch (\Throwable $e) {
            $this->matcherAvailable = false;
            Cache::put(self::DOWN_CACHE_KEY, true, self::DOWN_CACHE_SECONDS);
            Log::warning('Item matcher microservice unavailable, using PHP fallback: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Pure-PHP fallback. Same greedy best-first pairing as the microservice so
     * the shape of the answer never depends on which path produced it.
     */
    private function matchInPhp(array $left, array $right, float $threshold): array
    {
        $leftTokens  = array_map(fn ($i) => $this->tokens((string) ($i['name'] ?? '')), $left);
        $rightTokens = array_map(fn ($i) => $this->tokens((string) ($i['name'] ?? '')), $right);

        // On large inputs, only score right items that share at least one token —
        // any pair with real overlap must share one, so this drops the bulk of
        // hopeless comparisons without changing which pairs can win.
        $prefilter = (count($left) * count($right)) > self::PREFILTER_ABOVE_PAIRS;
        $index     = [];
        if ($prefilter) {
            foreach ($rightTokens as $ri => $tokens) {
                foreach ($tokens as $token) {
                    $index[$token][$ri] = true;
                }
            }
        }

        $pairs = [];
        foreach ($leftTokens as $li => $lTokens) {
            $candidates = range(0, max(0, count($right) - 1));
            if ($prefilter) {
                $hits = [];
                foreach ($lTokens as $token) {
                    foreach (array_keys($index[$token] ?? []) as $ri) {
                        $hits[$ri] = true;
                    }
                }
                $candidates = array_keys($hits);
            }

            foreach ($candidates as $ri) {
                if (!isset($rightTokens[$ri])) {
                    continue;
                }
                $score = $this->scorePair($lTokens, $rightTokens[$ri]);
                if ($score > 0) {
                    $pairs[] = [$score, $li, $ri];
                }
            }
        }

        usort($pairs, fn ($a, $b) => $b[0] <=> $a[0]);

        $chosen = $takenLeft = $takenRight = [];
        foreach ($pairs as [$score, $li, $ri]) {
            if ($score < $threshold) {
                break;
            }
            if (isset($takenLeft[$li]) || isset($takenRight[$ri])) {
                continue;
            }
            $chosen[$li]      = ['rightIndex' => $ri, 'score' => round($score, 4)];
            $takenLeft[$li]   = true;
            $takenRight[$ri]  = true;
        }

        // Best score each unmatched left item did reach — lets the caller say
        // "close but not accepted" rather than a flat "no match".
        $bestFor = [];
        foreach ($pairs as [$score, $li, $ri]) {
            if (!isset($bestFor[$li]) || $score > $bestFor[$li]) {
                $bestFor[$li] = $score;
            }
        }

        $matches = [];
        foreach ($left as $li => $_) {
            $matches[] = isset($chosen[$li])
                ? [
                    'leftIndex'  => $li,
                    'rightIndex' => $chosen[$li]['rightIndex'],
                    'score'      => $chosen[$li]['score'],
                    'matched'    => true,
                ]
                : [
                    'leftIndex'  => $li,
                    'rightIndex' => null,
                    'score'      => round($bestFor[$li] ?? 0.0, 4),
                    'matched'    => false,
                ];
        }

        return [
            'strategy'       => 'token',
            'matches'        => $matches,
            'unmatchedRight' => array_values(array_diff(array_keys($right), array_keys($takenRight))),
        ];
    }

    /**
     * Containment-biased token overlap, with normalized edit distance as a
     * typo-tolerant floor. Dividing by the SHORTER token set is deliberate: a
     * PPMP line often reads just "monitor" where the PR spells out "Monitor,
     * 24-inch LED Full HD Display", and the short name being fully contained in
     * the long one should score as a match, not be punished for the detail.
     *
     * @param  list<string>  $a
     * @param  list<string>  $b
     */
    private function scorePair(array $a, array $b): float
    {
        if (!$a || !$b) {
            return 0.0;
        }

        $overlap = count(array_intersect($a, $b)) / min(count($a), count($b));

        // levenshtein() is byte-capped at 255 in PHP, so compare trimmed forms.
        $sa     = substr(implode(' ', $a), 0, 255);
        $sb     = substr(implode(' ', $b), 0, 255);
        $maxLen = max(strlen($sa), strlen($sb));
        $lev    = $maxLen > 0 ? 1 - (levenshtein($sa, $sb) / $maxLen) : 0.0;

        return max($overlap, max(0.0, $lev));
    }

    /** @return list<string> */
    private function tokens(string $name): array
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s.\/-]/u', ' ', mb_strtolower($name));
        $parts = preg_split('/\s+/', trim($clean ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_filter($parts, fn ($t) => mb_strlen($t) > 1)));
    }
}
