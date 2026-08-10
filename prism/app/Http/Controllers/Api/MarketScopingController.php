<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MarketScopingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketScopingController extends Controller
{
    // Mobile-only price-checker version of PrismOfficeHeadController::runMarketScoping —
    // same search/match/flag pipeline, but Bearer-token (Sanctum) authenticated
    // instead of session+CSRF, and without the specs/attach-to-proposal flow
    // (this is a standalone lookup tool, not proposal authoring).
    public function run(Request $request, MarketScopingService $service): JsonResponse
    {
        $roleName = $request->user()->roles()->first()?->name;
        if ($roleName !== 'Office Head / Dean') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'query'      => 'required|string|max:300',
            'budget'     => 'nullable|numeric|min:0',
            'department' => 'nullable|string|in:appliances,medical,office,it,janitorial,hardware,furniture,sports',
        ]);

        $query = $this->sanitizeSearchQuery($request->input('query'));
        if ($query === '') {
            return response()->json([
                'success' => false,
                'message' => 'Search query is empty after removing invalid characters. Please type a valid item name.',
                'results' => [],
            ]);
        }

        $results = $service->search($query, 30, $request->input('department'));

        if (empty($results)) {
            return response()->json([
                'success'         => false,
                'message'         => 'No market references found for this item. Try adjusting the search keywords.',
                'results'         => [],
                'suggestion'      => null,
                'quota_exhausted' => $service->isQuotaExhausted(),
            ]);
        }

        $results = $service->matchSpecs($results, [], $query);

        $budget = (float) $request->input('budget', 0);
        if ($budget > 0) {
            $results = $service->flagAdvantageous($results, $budget);
        }

        return response()->json([
            'success'           => true,
            'results'           => $results,
            'query'             => $query,
            'count'             => count($results),
            'quota_exhausted'   => $service->isQuotaExhausted(),
            'matcher_available' => $service->matcherAvailable(),
        ]);
    }

    private function sanitizeSearchQuery(string $raw): string
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s\-\+\.\"\/(),]/u', ' ', $raw);
        return trim(preg_replace('/\s+/', ' ', $clean));
    }
}
