<?php

namespace App\Services;

use App\Models\BudgetProposal;
use App\Models\DocumentValidation;
use App\Models\PurchaseRequest;
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
     * @param  array{officeCode?: ?string, fiscalYear?: ?int, totalCost?: ?float, prNumber?: ?string, excludePrId?: ?int}  $documentFields
     *         Header fields read off the PR document itself — checked against
     *         the PPMP/system alongside the item table. Unlike the item and
     *         quarter checks above, these exist to catch "wrong file, wrong
     *         PPMP, or already-uploaded" rather than a planning disagreement,
     *         so a mismatch here blocks the same way an unapproved item does.
     *         excludePrId excuses one PR's own number from the uniqueness
     *         check — set it when re-validating a document being re-uploaded
     *         onto the PR it already belongs to, so its own existing number
     *         isn't flagged as a duplicate of itself.
     */
    public function validatePrAgainstPpmp(
        array $extractedItems,
        BudgetProposal $ppmp,
        ?string $quarter = null,
        ?string $parseError = null,
        array $documentFields = []
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

                // The matcher pairs each PPMP line to at most one PR row, so a
                // second row for the same item comes back unmatched even
                // though it strongly resembles a PPMP line — it's just that an
                // earlier row already claimed it. The matcher still reports
                // the best score it *would* have gotten in that case (see
                // microservice/app.py's match_items()), so a high score here
                // means "already claimed", not "never approved" — worth a
                // different message so the reason isn't misleading.
                $reason = $score >= ItemMatchingService::DEFAULT_THRESHOLD
                    ? '"' . $this->shorten($name) . '" was already matched to another row on this PR — combine them into a single line instead of repeating the item.'
                    // No qualifier about the quarter here: by this point the
                    // item was looked for across the whole PPMP, not just the
                    // selected quarter.
                    : '"' . $this->shorten($name) . '" is not in the approved PPMP.';

                $items[] = [
                    'name'     => $name,
                    'quantity' => $qty,
                    'unit'     => $item['unit'] ?? '',
                    'verdict'  => DocumentValidation::FAILED,
                    'reason'   => $reason,
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

        $fieldChecks = $this->checkDocumentFields($documentFields, $ppmp, $extractedItems);
        $fieldsFailed = collect($fieldChecks)->contains(fn ($c) => !$c['ok']);

        $count   = max(1, count($extractedItems));
        $verdict = ($failed > 0 || $fieldsFailed) ? DocumentValidation::FAILED : DocumentValidation::PASSED;

        $summaryParts = [];
        if ($failed > 0) {
            $summaryParts[] = $failed . ' of ' . $count . " item(s) do not match the approved PPMP";
        }
        if ($fieldsFailed) {
            $badFields = collect($fieldChecks)->reject(fn ($c) => $c['ok'])->pluck('field')->implode(', ');
            $summaryParts[] = "document field(s) don't check out ({$badFields})";
        }

        return [
            'verdict'          => $verdict,
            'score'            => (int) round(($scoreSum / $count) * 100),
            'strategy'         => $matchResult['strategy'],
            'matcherAvailable' => $this->matcher->available(),
            'items'            => $items,
            'warnings'         => $warnings,
            'fieldChecks'      => $fieldChecks,
            'summary'          => $summaryParts
                ? ucfirst(implode('; ', $summaryParts)) . '.'
                : 'All ' . $count . ' item(s) match the approved PPMP.',
        ];
    }

    /**
     * Compare the items read out of a supplier's quotation against the items
     * of the PR it's meant to be canvassing for.
     *
     * Deliberately the mirror image of validatePrAgainstPpmp()'s item check:
     * there, an unmatched PPMP item is fine (a PR only covers part of a
     * PPMP); here, an unmatched PR item is fine too (a supplier can quote
     * fewer than all of them) — but an unmatched QUOTATION item is not,
     * because that means the supplier is quoting for something this PR
     * never asked for. Quantity and price aren't checked here — canvassing
     * is exactly where suppliers are expected to differ on those.
     *
     * @param  array<int, array{name: string, quantity?: float|int, unit?: string, unitPrice?: float}>  $quotationItems
     */
    public function validateQuotationAgainstPr(array $quotationItems, PurchaseRequest $pr): array
    {
        if (!$quotationItems) {
            return $this->unreadable(
                'No item rows could be read from this quotation. If it is a scanned image rather than a text PDF, re-upload a text-based copy.'
            );
        }

        $prItems = $pr->items()->get();
        if ($prItems->isEmpty()) {
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
                    'reason'   => 'This PR has no items to check against.',
                    'score'    => 0.0,
                ], $quotationItems),
                'warnings'         => [],
                'summary'          => 'This PR has no encoded items.',
            ];
        }

        $right       = $prItems->map(fn ($i) => [
            'name'     => (string) $i->name,
            'quantity' => (float) $i->quantity,
            'unit'     => (string) $i->unit,
        ])->values()->all();
        $matchResult = $this->matcher->match($quotationItems, $right);
        $matches     = collect($matchResult['matches'])->keyBy('leftIndex');

        $items    = [];
        $failed   = 0;
        $scoreSum = 0.0;

        foreach (array_values($quotationItems) as $li => $item) {
            $name  = (string) ($item['name'] ?? '');
            $qty   = (float) ($item['quantity'] ?? 0);
            $match = $matches->get($li);
            $score = (float) ($match['score'] ?? 0);
            $scoreSum += $score;

            $prItem = ($match && $match['matched']) ? ($right[$match['rightIndex']] ?? null) : null;

            if (!$prItem) {
                $failed++;
                $items[] = [
                    'name'     => $name,
                    'quantity' => $qty,
                    'unit'     => $item['unit'] ?? '',
                    'verdict'  => DocumentValidation::FAILED,
                    'reason'   => '"' . $this->shorten($name) . '" is not one of this PR\'s items.',
                    'score'    => round($score, 3),
                ];
                continue;
            }

            $items[] = [
                'name'        => $name,
                'quantity'    => $qty,
                'unit'        => $item['unit'] ?? '',
                'verdict'     => DocumentValidation::PASSED,
                'reason'      => 'Matches PR item "' . $this->shorten($prItem['name']) . '".',
                'matchedName' => $prItem['name'],
                'score'       => round($score, 3),
            ];
        }

        $warnings = [];
        if (!$this->matcher->available()) {
            $warnings[] = 'The semantic matcher was unavailable, so a simpler text comparison was used.';
        }

        $count   = max(1, count($quotationItems));
        $verdict = $failed > 0 ? DocumentValidation::FAILED : DocumentValidation::PASSED;

        return [
            'verdict'          => $verdict,
            'score'            => (int) round(($scoreSum / $count) * 100),
            'strategy'         => $matchResult['strategy'],
            'matcherAvailable' => $this->matcher->available(),
            'items'            => $items,
            'warnings'         => $warnings,
            'summary'          => $failed > 0
                ? $failed . ' of ' . $count . ' item(s) in this quotation are not part of this PR.'
                : 'All ' . $count . ' item(s) in this quotation match items on this PR.',
        ];
    }

    /**
     * Cross-checks an Abstract of Canvass against the supplier quotations
     * already on file for its PR — the AOC is meant to be a faithful summary
     * of those quotations, condensed for easier side-by-side comparison, so:
     *
     *   - every price it lists for an item must be the EXACT price some
     *     supplier's quotation actually offered for a matching item. Price
     *     is compared as-is (peso-rounding tolerance only); the item name is
     *     matched the intelligent way everything else in this service does,
     *     since a quotation rarely phrases an item identically to the AOC —
     *     candidates are narrowed to the exact price first, then confirmed
     *     by name, which is what makes "exact on price, fuzzy on identity"
     *     work as one check instead of two conflicting ones.
     *   - its declared Responsive Dealer has to be one of the suppliers who
     *     actually submitted a quotation for this PR — matched the same
     *     intelligent way, not by an exact company-name string.
     *
     * @param  array<int, array{name: string, unit?: string, quantity?: float, supplierPrices: list<float>}>  $aocItems
     * @param  array<int, array{supplier: string, items: array<int, array{name: string, unit?: string, quantity?: float, unitPrice: float}>}>  $quotations
     */
    public function validateAocAgainstQuotations(array $aocItems, ?string $declaredResponsiveDealer, array $quotations): array
    {
        if (!$aocItems) {
            return $this->unreadable(
                'No item rows could be read from this Abstract of Canvass. If it is a scanned image rather than a text PDF, re-upload a text-based copy.'
            );
        }

        if (!$quotations) {
            return [
                'verdict'          => DocumentValidation::FAILED,
                'score'            => 0,
                'strategy'         => 'none',
                'matcherAvailable' => $this->matcher->available(),
                'items'            => [],
                'warnings'         => [],
                'summary'          => 'No supplier quotations are on file for this PR to check the Abstract of Canvass against.',
            ];
        }

        // Every quoted item from every supplier, flattened into one pool —
        // an AOC price is checked against whichever supplier actually
        // offered it, not against one quotation in isolation.
        $pool = [];
        foreach ($quotations as $q) {
            foreach ($q['items'] as $it) {
                $pool[] = [
                    'supplier'  => (string) ($q['supplier'] ?? ''),
                    'name'      => (string) ($it['name'] ?? ''),
                    'unitPrice' => (float) ($it['unitPrice'] ?? 0),
                ];
            }
        }

        $items    = [];
        $failed   = 0;
        $scoreSum = 0.0;
        $checks   = 0;
        $strategy = 'none';

        foreach ($aocItems as $aocItem) {
            $name   = (string) ($aocItem['name'] ?? '');
            $prices = $aocItem['supplierPrices'] ?? [];

            // A row nobody actually canvassed (no supplier price filled in)
            // has nothing to check — not a failure, just nothing to say.
            if (!$prices) {
                continue;
            }

            foreach ($prices as $price) {
                $checks++;
                $priceMatches = array_values(array_filter(
                    $pool,
                    fn ($p) => abs($p['unitPrice'] - (float) $price) < 1.0
                ));

                if (!$priceMatches) {
                    $failed++;
                    $items[] = [
                        'name'    => $name,
                        'price'   => (float) $price,
                        'verdict' => DocumentValidation::FAILED,
                        'reason'  => 'No supplier quotation on file offers "' . $this->shorten($name) . '" at ₱' . $this->num((float) $price) . '.',
                        'score'   => 0.0,
                    ];
                    continue;
                }

                $nameMatch = $this->matcher->match([['name' => $name]], $priceMatches);
                $strategy  = $nameMatch['strategy'];
                $m         = $nameMatch['matches'][0] ?? null;
                $score     = (float) ($m['score'] ?? 0);
                $scoreSum += $score;

                if ($m && $m['matched']) {
                    $matchedPool = $priceMatches[$m['rightIndex']];
                    $items[] = [
                        'name'    => $name,
                        'price'   => (float) $price,
                        'verdict' => DocumentValidation::PASSED,
                        'reason'  => 'Matches ' . $matchedPool['supplier'] . '\'s quotation for "' . $this->shorten($matchedPool['name']) . '" at ₱' . $this->num((float) $price) . '.',
                        'score'   => round($score, 3),
                    ];
                } else {
                    $failed++;
                    $items[] = [
                        'name'    => $name,
                        'price'   => (float) $price,
                        'verdict' => DocumentValidation::FAILED,
                        'reason'  => 'A quotation offers ₱' . $this->num((float) $price) . ', but not for "' . $this->shorten($name) . '" — check that this price is on the right row.',
                        'score'   => round($score, 3),
                    ];
                }
            }
        }

        // The declared winner has to be one of the suppliers who actually
        // submitted a quotation for this PR.
        $dealerCheck = null;
        if ($declaredResponsiveDealer) {
            $supplierNames = array_values(array_unique(array_column($quotations, 'supplier')));
            $right         = array_map(fn ($s) => ['name' => $s], $supplierNames);
            $dealerResult  = $this->matcher->match([['name' => $declaredResponsiveDealer]], $right);
            $strategy      = $dealerResult['strategy'];
            $dm            = $dealerResult['matches'][0] ?? null;
            $ok            = (bool) ($dm['matched'] ?? false);

            $dealerCheck = [
                'field'    => 'Responsive Dealer',
                'found'    => $declaredResponsiveDealer,
                'expected' => implode(', ', $supplierNames),
                'ok'       => $ok,
                'reason'   => $ok ? null : 'The declared Responsive Dealer "' . $declaredResponsiveDealer
                    . '" does not match any supplier who submitted a quotation for this PR (' . implode(', ', $supplierNames) . ').',
            ];

            $checks++;
            $scoreSum += (float) ($dm['score'] ?? 0);
            if (!$ok) {
                $failed++;
            }
        }

        $warnings = [];
        if (!$this->matcher->available()) {
            $warnings[] = 'The semantic matcher was unavailable, so a simpler text comparison was used.';
        }

        $count   = max(1, $checks);
        $verdict = $failed > 0 ? DocumentValidation::FAILED : DocumentValidation::PASSED;

        return [
            'verdict'          => $verdict,
            'score'            => (int) round(($scoreSum / $count) * 100),
            'strategy'         => $strategy,
            'matcherAvailable' => $this->matcher->available(),
            'items'            => $items,
            'dealerCheck'      => $dealerCheck,
            'warnings'         => $warnings,
            'summary'          => $failed > 0
                ? $failed . ' of ' . $count . ' checked price/supplier detail(s) could not be traced back to an actual quotation on file.'
                : 'All ' . $count . ' checked price/supplier detail(s) match the quotations on file.',
        ];
    }

    /**
     * Cross-checks a Purchase Order against the Abstract of Canvass it was
     * issued from — the mirror image of validateAocAgainstQuotations() one
     * step further down the procurement chain: there, an AOC's prices had to
     * trace back to a quotation; here, a PO's items have to trace back to
     * the AOC.
     *
     *   - every unit cost the PO lists for an item must be the EXACT price
     *     the AOC listed for a matching item (across whichever of its
     *     Supplier N columns had it — the AOC doesn't literally name which
     *     column belongs to which company, so a PO price is checked against
     *     all of them, same as an AOC price was checked against every
     *     supplier's quotation). Price is compared as-is; the item name is
     *     matched the intelligent way, exact price first then confirmed by
     *     name, for the same reason as everywhere else in this service.
     *   - the PO's External Provider has to be the AOC's own declared
     *     Responsive Dealer — matched intelligently, not by an exact
     *     company-name string.
     *
     * @param  array<int, array{name: string, unit?: string, quantity?: float, unitCost: float}>  $poItems
     * @param  array<int, array{name: string, unit?: string, quantity?: float, supplierPrices: list<float>}>  $aocItems
     */
    public function validatePoAgainstAoc(array $poItems, ?string $externalProvider, array $aocItems, ?string $aocResponsiveDealer): array
    {
        if (!$poItems) {
            return $this->unreadable(
                'No item rows could be read from this Purchase Order. If it is a scanned image rather than a text PDF, re-upload a text-based copy.'
            );
        }

        if (!$aocItems) {
            return [
                'verdict'          => DocumentValidation::FAILED,
                'score'            => 0,
                'strategy'         => 'none',
                'matcherAvailable' => $this->matcher->available(),
                'items'            => [],
                'warnings'         => [],
                'summary'          => 'The Abstract of Canvass for this PO has no readable item table to check against.',
            ];
        }

        // Every price the AOC listed for an item, across all of its
        // supplier columns — a PO's unit cost is checked against this whole
        // pool, not against one column picked out in advance.
        $pool = [];
        foreach ($aocItems as $ai) {
            foreach (($ai['supplierPrices'] ?? []) as $price) {
                $pool[] = ['name' => (string) ($ai['name'] ?? ''), 'price' => (float) $price];
            }
        }

        $items    = [];
        $failed   = 0;
        $scoreSum = 0.0;
        $checks   = 0;
        $strategy = 'none';

        foreach ($poItems as $poItem) {
            $name = (string) ($poItem['name'] ?? '');
            $cost = (float) ($poItem['unitCost'] ?? 0);
            $checks++;

            $priceMatches = array_values(array_filter($pool, fn ($p) => abs($p['price'] - $cost) < 1.0));

            if (!$priceMatches) {
                $failed++;
                $items[] = [
                    'name'    => $name,
                    'price'   => $cost,
                    'verdict' => DocumentValidation::FAILED,
                    'reason'  => 'The Abstract of Canvass lists no price of ₱' . $this->num($cost) . ' for "' . $this->shorten($name) . '".',
                    'score'   => 0.0,
                ];
                continue;
            }

            $nameMatch = $this->matcher->match([['name' => $name]], $priceMatches);
            $strategy  = $nameMatch['strategy'];
            $m         = $nameMatch['matches'][0] ?? null;
            $score     = (float) ($m['score'] ?? 0);
            $scoreSum += $score;

            if ($m && $m['matched']) {
                $matchedPool = $priceMatches[$m['rightIndex']];
                $items[] = [
                    'name'    => $name,
                    'price'   => $cost,
                    'verdict' => DocumentValidation::PASSED,
                    'reason'  => 'Matches the Abstract of Canvass\'s "' . $this->shorten($matchedPool['name']) . '" at ₱' . $this->num($cost) . '.',
                    'score'   => round($score, 3),
                ];
            } else {
                $failed++;
                $items[] = [
                    'name'    => $name,
                    'price'   => $cost,
                    'verdict' => DocumentValidation::FAILED,
                    'reason'  => 'The Abstract of Canvass lists ₱' . $this->num($cost) . ', but not for "' . $this->shorten($name) . '" — check that this price is on the right row.',
                    'score'   => round($score, 3),
                ];
            }
        }

        // The external provider has to be the AOC's declared winner.
        $dealerCheck = null;
        if ($externalProvider && $aocResponsiveDealer) {
            $dealerResult = $this->matcher->match([['name' => $externalProvider]], [['name' => $aocResponsiveDealer]]);
            $strategy     = $dealerResult['strategy'];
            $dm           = $dealerResult['matches'][0] ?? null;
            $ok           = (bool) ($dm['matched'] ?? false);

            $dealerCheck = [
                'field'    => 'External Provider',
                'found'    => $externalProvider,
                'expected' => $aocResponsiveDealer,
                'ok'       => $ok,
                'reason'   => $ok ? null : 'This PO is for "' . $externalProvider
                    . '", but the Abstract of Canvass declared "' . $aocResponsiveDealer . '" as the Responsive Dealer.',
            ];

            $checks++;
            $scoreSum += (float) ($dm['score'] ?? 0);
            if (!$ok) {
                $failed++;
            }
        }

        $warnings = [];
        if (!$this->matcher->available()) {
            $warnings[] = 'The semantic matcher was unavailable, so a simpler text comparison was used.';
        }

        $count   = max(1, $checks);
        $verdict = $failed > 0 ? DocumentValidation::FAILED : DocumentValidation::PASSED;

        return [
            'verdict'          => $verdict,
            'score'            => (int) round(($scoreSum / $count) * 100),
            'strategy'         => $strategy,
            'matcherAvailable' => $this->matcher->available(),
            'items'            => $items,
            'dealerCheck'      => $dealerCheck,
            'warnings'         => $warnings,
            'summary'          => $failed > 0
                ? $failed . ' of ' . $count . ' checked item/supplier detail(s) could not be traced back to the Abstract of Canvass.'
                : 'All ' . $count . ' checked item/supplier detail(s) match the Abstract of Canvass.',
        ];
    }

    /**
     * Header fields read off the PR document itself, checked against the PPMP
     * it's being raised against (and, for the PR number, against every other
     * PR in the system). These exist to catch "wrong file, wrong PPMP, or
     * this was already uploaded" — a different failure mode than an
     * unapproved item, but one that deserves the same hard block, not a
     * dismissible "heads up" a user can just click past.
     *
     * @param  array{officeCode?: ?string, fiscalYear?: ?int, totalCost?: ?float, prNumber?: ?string, excludePrId?: ?int}  $documentFields
     * @return array<int, array{field: string, found: ?string, expected: ?string, ok: bool, reason: ?string}>
     */
    private function checkDocumentFields(array $documentFields, BudgetProposal $ppmp, array $extractedItems): array
    {
        $checks = [];

        // Office — almost always means the wrong file was picked, or the
        // wrong PPMP, rather than a real procurement disagreement.
        if (!empty($documentFields['officeCode'])) {
            $ppmpOfficeCode = $ppmp->office?->code;
            $ok = $ppmpOfficeCode && strcasecmp(trim($documentFields['officeCode']), trim($ppmpOfficeCode)) === 0;
            $checks[] = [
                'field'    => 'Office',
                'found'    => $documentFields['officeCode'],
                'expected' => $ppmpOfficeCode,
                'ok'       => $ok,
                'reason'   => $ok ? null : 'This document is for "' . $documentFields['officeCode']
                    . '", but the selected PPMP belongs to "' . ($ppmpOfficeCode ?? 'an office with no code on record')
                    . '". Wrong file, or wrong PPMP.',
            ];
        }

        // Fiscal Year — a PR is expected to be raised within the same fiscal
        // year as the PPMP it draws from.
        if (!empty($documentFields['fiscalYear'])) {
            $ok = (int) $documentFields['fiscalYear'] === (int) $ppmp->fiscal_year;
            $checks[] = [
                'field'    => 'Fiscal Year',
                'found'    => (string) $documentFields['fiscalYear'],
                'expected' => (string) $ppmp->fiscal_year,
                'ok'       => $ok,
                'reason'   => $ok ? null : 'This document is dated in FY' . $documentFields['fiscalYear']
                    . ', but the selected PPMP is for FY' . $ppmp->fiscal_year . '.',
            ];
        }

        // Total Cost — an internal consistency check against the document's
        // OWN item rows, never against the PPMP's total: a PR normally
        // covers only part of a PPMP (see the split-PR workflow), so it must
        // never be forced to equal the PPMP's full amount.
        if (!empty($documentFields['totalCost'])) {
            $itemSum = array_sum(array_map(
                fn ($i) => (float) ($i['quantity'] ?? 0) * (float) ($i['unitCost'] ?? 0),
                $extractedItems
            ));
            $printed = (float) $documentFields['totalCost'];
            $ok = abs($itemSum - $printed) < 1.0; // peso-rounding tolerance
            $checks[] = [
                'field'    => 'Total Cost',
                'found'    => $this->num($printed),
                'expected' => $this->num($itemSum),
                'ok'       => $ok,
                'reason'   => $ok ? null : 'The document\'s own printed total (₱' . $this->num($printed)
                    . ') doesn\'t match the sum of its listed items (₱' . $this->num($itemSum)
                    . '). The file may have been misread or altered.',
            ];
        }

        // PR Number — global uniqueness, surfaced here at review time instead
        // of only failing later when the user clicks "Create".
        if (!empty($documentFields['prNumber'])) {
            $existing = PurchaseRequest::with('office')
                ->where('number', $documentFields['prNumber'])
                ->when(!empty($documentFields['excludePrId']), fn ($q) => $q->where('id', '!=', $documentFields['excludePrId']))
                ->first();
            $checks[] = [
                'field'    => 'PR Number',
                'found'    => $documentFields['prNumber'],
                'expected' => null,
                'ok'       => !$existing,
                'reason'   => $existing ? 'PR number "' . $documentFields['prNumber'] . '" is already used by an existing Purchase Request'
                    . ' (Office: ' . ($existing->office?->code ?? '—')
                    . ', uploaded ' . ($existing->uploaded_at?->format('M d, Y') ?? '—')
                    . '). Choose a different number, or check whether this document was already uploaded.' : null,
            ];
        }

        return $checks;
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
                'items'       => $result['items'] ?? [],
                'warnings'    => $result['warnings'] ?? [],
                'fieldChecks' => $result['fieldChecks'] ?? [],
                'summary'     => $result['summary'] ?? '',
                'strategy'    => $result['strategy'] ?? null,
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
