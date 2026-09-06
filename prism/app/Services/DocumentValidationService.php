<?php

namespace App\Services;

use App\Models\BudgetProposal;
use App\Models\DocumentValidation;
use Illuminate\Database\Eloquent\Model;

/**
 * Decides whether an uploaded document's contents actually match the document
 * it came from, and records the verdict.
 *
 * Everything a controller needs is here: run a check, persist it, and look up
 * the latest one (which is what the routing gate in SignatoryActionService
 * reads before letting a document move forward).
 */
class DocumentValidationService
{
    public function __construct(private ItemMatchingService $matcher)
    {
    }

    /**
     * Compare the items read out of an uploaded PR against the items of the
     * approved PPMP it claims to come from.
     *
     * Failing conditions are deliberately narrow — only the two things that
     * mean somebody is asking for something that was never approved:
     *   • an item on the PR that has no counterpart in the PPMP
     *   • a quantity larger than the PPMP planned for
     * A PPMP item that this PR doesn't cover is normal (PRs are raised a few
     * items at a time), and price drift is expected between planning and
     * canvassing, so both are recorded as warnings rather than blocks.
     *
     * @param  array<int, array{name: string, quantity?: float|int, unit?: string, unitCost?: float}>  $extractedItems
     * @param  string|null  $quarter  Restrict the comparison to one PPMP quarter, e.g. 'Q1'.
     */
    public function validatePrAgainstPpmp(
        array $extractedItems,
        BudgetProposal $ppmp,
        ?string $quarter = null,
        ?string $parseError = null
    ): array {
        if ($parseError) {
            return $this->unreadable($parseError);
        }

        if (!$extractedItems) {
            return $this->unreadable(
                'No item rows could be read from this document. If it is a scanned image rather than a text PDF, re-upload a text-based copy.'
            );
        }

        $warnings = [];
        $allItems = $ppmp->items()->get();

        if ($allItems->isEmpty()) {
            return [
                'verdict'          => DocumentValidation::FAILED,
                'score'            => 0,
                'strategy'         => 'none',
                'matcherAvailable' => $this->matcher->available(),
                'items'            => array_map(fn ($i) => [
                    'name'     => $i['name'] ?? '',
                    'quantity' => (float) ($i['quantity'] ?? 0),
                    'unit'     => $i['unit'] ?? '',
                    'verdict'  => DocumentValidation::FAILED,
                    'reason'   => 'The selected PPMP has no items to check against.',
                    'score'    => 0.0,
                ], $extractedItems),
                'warnings'         => [],
                'summary'          => 'The selected PPMP has no encoded items.',
            ];
        }

        // The quarter is a planning target, not a deadline. Procurement slips —
        // something planned for Q1 is often only bought in Q4 — and that is
        // normal, not fraud. So the chosen quarter only decides which items are
        // tried FIRST; anything unmatched there is retried against the entire
        // PPMP, and a hit in another quarter passes with a note about the
        // timing rather than being refused.
        $ppmpItems = $quarter
            ? $allItems->where('target_quarter', $quarter)->values()
            : $allItems;

        if ($ppmpItems->isEmpty()) {
            $ppmpItems = $allItems;
            $warnings[] = "This PPMP has no items targeted for {$quarter}, so the whole PPMP was checked instead.";
            $quarterExhausted = true;
        }

        $toRow = fn ($i) => [
            'name'     => (string) $i->name,
            'quantity' => (float) $i->quantity,
            'unit'     => (string) $i->unit,
            'quarter'  => $i->target_quarter,
        ];

        $right       = $ppmpItems->map($toRow)->values()->all();
        $matchResult = $this->matcher->match($extractedItems, $right);
        $matches     = collect($matchResult['matches'])->keyBy('leftIndex');

        // Second pass for whatever the chosen quarter couldn't account for.
        $fallbackRight   = [];
        $fallbackMatches = collect();
        if ($quarter && empty($quarterExhausted)) {
            $unmatchedLeft = collect($matchResult['matches'])
                ->reject(fn ($m) => $m['matched'])
                ->pluck('leftIndex')
                ->all();

            if ($unmatchedLeft) {
                $otherQuarter  = $allItems->where('target_quarter', '!=', $quarter)->values();
                $fallbackRight = $otherQuarter->map($toRow)->values()->all();

                if ($fallbackRight) {
                    $subset   = array_map(fn ($li) => $extractedItems[$li], $unmatchedLeft);
                    $fbResult = $this->matcher->match($subset, $fallbackRight);
                    foreach ($fbResult['matches'] as $pos => $m) {
                        $fallbackMatches->put($unmatchedLeft[$pos], $m);
                    }
                }
            }
        }

        $items    = [];
        $failed   = 0;
        $scoreSum = 0.0;

        foreach (array_values($extractedItems) as $li => $item) {
            $name = (string) ($item['name'] ?? '');
            $qty  = (float) ($item['quantity'] ?? 0);

            // Prefer the chosen quarter; fall back to the rest of the PPMP.
            $match       = $matches->get($li);
            $ppmpItem    = null;
            $offQuarter  = false;

            if ($match && $match['matched']) {
                $ppmpItem = $right[$match['rightIndex']] ?? null;
            } else {
                $fb = $fallbackMatches->get($li);
                if ($fb && $fb['matched']) {
                    $ppmpItem   = $fallbackRight[$fb['rightIndex']] ?? null;
                    $match      = $fb;
                    $offQuarter = true;
                }
            }

            $score = (float) ($match['score'] ?? 0);
            $scoreSum += $score;

            if (!$ppmpItem) {
                $failed++;
                $items[] = [
                    'name'     => $name,
                    'quantity' => $qty,
                    'unit'     => $item['unit'] ?? '',
                    'verdict'  => DocumentValidation::FAILED,
                    // No qualifier about the quarter here: by this point the
                    // item was looked for across the whole PPMP, not just the
                    // selected quarter.
                    'reason'   => '"' . $this->shorten($name) . '" is not in the approved PPMP.',
                    'score'    => round($score, 3),
                ];
                continue;
            }

            $ppmpQty = (float) ($ppmpItem['quantity'] ?? 0);

            if ($qty > $ppmpQty) {
                $failed++;
                $items[] = [
                    'name'         => $name,
                    'quantity'     => $qty,
                    'unit'         => $item['unit'] ?? '',
                    'verdict'      => DocumentValidation::FAILED,
                    'reason'       => 'Quantity ' . $this->num($qty) . ' exceeds the ' . $this->num($ppmpQty)
                        . ' approved in the PPMP for "' . $this->shorten($ppmpItem['name']) . '".',
                    'matchedName'  => $ppmpItem['name'],
                    'ppmpQuantity' => $ppmpQty,
                    'score'        => round($score, 3),
                ];
                continue;
            }

            // Timing drift is noted, never blocked.
            $plannedFor = $ppmpItem['quarter'] ?? null;
            $items[] = [
                'name'         => $name,
                'quantity'     => $qty,
                'unit'         => $item['unit'] ?? '',
                'verdict'      => DocumentValidation::PASSED,
                'reason'       => $offQuarter && $plannedFor
                    ? 'Matches PPMP item "' . $this->shorten($ppmpItem['name']) . '", which was planned for '
                        . $plannedFor . ' rather than ' . $quarter . ' — timing differs from plan, which is allowed.'
                    : 'Matches PPMP item "' . $this->shorten($ppmpItem['name'] ?? '') . '".',
                'matchedName'  => $ppmpItem['name'] ?? null,
                'ppmpQuantity' => $ppmpQty,
                'plannedQuarter' => $plannedFor,
                'offQuarter'   => $offQuarter,
                'score'        => round($score, 3),
            ];

            if ($offQuarter && $plannedFor) {
                $warnings[] = '"' . $this->shorten($ppmpItem['name']) . '" was planned for ' . $plannedFor
                    . ' but this PR is for ' . $quarter . '. Allowed — noted only.';
            }
        }

        // Not blocking — a PR normally covers only part of a PPMP.
        foreach ($matchResult['unmatchedRight'] as $ri) {
            if (isset($right[$ri])) {
                $warnings[] = 'PPMP item "' . $this->shorten($right[$ri]['name']) . '" is not covered by this PR.';
            }
        }

        if (!empty($matchResult['truncated'])) {
            $warnings[] = 'This document has an unusually large number of items; only the first 500 were compared.';
        }

        if (!$this->matcher->available()) {
            $warnings[] = 'The semantic matcher was unavailable, so a simpler text comparison was used.';
        }

        $count   = max(1, count($extractedItems));
        $verdict = $failed > 0 ? DocumentValidation::FAILED : DocumentValidation::PASSED;

        return [
            'verdict'          => $verdict,
            'score'            => (int) round(($scoreSum / $count) * 100),
            'strategy'         => $matchResult['strategy'],
            'matcherAvailable' => $this->matcher->available(),
            'items'            => $items,
            'warnings'         => $warnings,
            'summary'          => $failed > 0
                ? $failed . ' of ' . $count . ' item(s) do not match the approved PPMP.'
                : 'All ' . $count . ' item(s) match the approved PPMP.',
        ];
    }

    /** Persist a validation result against the document it describes. */
    public function record(Model $document, ?Model $source, string $pair, array $result, ?string $scope = null): DocumentValidation
    {
        return DocumentValidation::create([
            'validatable_type'     => $document->getMorphClass(),
            'validatable_id'       => $document->getKey(),
            'source_type'          => $source?->getMorphClass(),
            'source_id'            => $source?->getKey(),
            'pair'                 => $pair,
            'verdict'              => $result['verdict'],
            'score'                => $result['score'] ?? 0,
            'scope'                => $scope,
            'details_json'         => [
                'items'    => $result['items'] ?? [],
                'warnings' => $result['warnings'] ?? [],
                'summary'  => $result['summary'] ?? '',
                'strategy' => $result['strategy'] ?? null,
            ],
            'validated_by_user_id' => auth()->id(),
            'validated_at'         => now(),
        ]);
    }

    /** The most recent validation recorded for a document, if any. */
    public function latestFor(Model $document, ?string $pair = null): ?DocumentValidation
    {
        return DocumentValidation::query()
            ->where('validatable_type', $document->getMorphClass())
            ->where('validatable_id', $document->getKey())
            ->when($pair, fn ($q) => $q->where('pair', $pair))
            ->latest('validated_at')
            ->latest('id')
            ->first();
    }

    private function unreadable(string $reason): array
    {
        return [
            'verdict'          => DocumentValidation::UNREADABLE,
            'score'            => 0,
            'strategy'         => 'none',
            'matcherAvailable' => $this->matcher->available(),
            'items'            => [],
            'warnings'         => [],
            'summary'          => $reason,
        ];
    }

    private function shorten(string $name, int $limit = 60): string
    {
        return mb_strlen($name) > $limit ? mb_substr($name, 0, $limit - 1) . '…' : $name;
    }

    private function num(float $n): string
    {
        return rtrim(rtrim(number_format($n, 2), '0'), '.');
    }
}
