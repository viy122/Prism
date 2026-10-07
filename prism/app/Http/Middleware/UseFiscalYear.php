<?php

namespace App\Http\Middleware;

use App\Models\FiscalYear;
use App\Services\FiscalYearContext;
use App\Services\FiscalYearGuard;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UseFiscalYear
{
    public function handle(Request $request, Closure $next)
    {
        $context = app(FiscalYearContext::class);
        $fallback = $request->session()->get('fiscal_year');
        // A form/AJAX request belongs to the year shown in its tab, even if a
        // different tab has since changed the shared session selection.
        $referer = (string) $request->headers->get('referer', '');
        if (!$request->isMethod('GET') && parse_url($referer, PHP_URL_HOST) === $request->getHost()) {
            parse_str(parse_url($referer, PHP_URL_QUERY) ?? '', $query);
            $fallback = $query['year'] ?? $fallback;
        }
        $raw = $request->query('year', $request->query('fy', $fallback));
        if ($raw === null || $raw === 'all') {
            $raw = FiscalYear::where('is_active', true)->value('year') ?? now()->year;
        }
        abort_unless(filter_var($raw, FILTER_VALIDATE_INT) && FiscalYear::find($raw), 422, 'Select a registered fiscal year.');
        $context->year = (int) $raw;
        $request->session()->put('fiscal_year', $context->year);
        $request->query->set('year', $context->year);

        $name = (string) $request->route()?->getName();
        // Task queues and their supporting lookups must include pending older years.
        $queue = str_contains($name, 'for-my-signature') || str_contains($name, '.sign')
            || str_starts_with($name, 'accounting-office.') || str_starts_with($name, 'cashier.')
            || str_starts_with($name, 'bac.') || str_starts_with($name, 'notifications.')
            || str_starts_with($name, 'profile.') || str_starts_with($name, 'fiscal-years.')
            || str_starts_with($name, 'admin.') || str_contains($name, 'office-assets');
        $context->filterReads = $request->isMethod('GET') && !$queue;
        $planning = str_contains($name, 'budget-proposal') || str_contains($name, 'proposal-review')
            || str_contains($name, 'budget-approval') || str_contains($name, 'annual-procurement-plan')
            || str_contains($name, 'market-scoping');
        if ($planning) $context->filterReads = true;

        try {
            if ($request->isMethod('GET') && $context->filterReads) {
                // Implicit route binding can precede this middleware. Recheck detail
                // records through the selected-year scope before rendering them.
                foreach ($request->route()->parameters() as $model) {
                    if ($model instanceof \Illuminate\Database\Eloquent\Model && $model->hasGlobalScope('selectedFiscalYear')) {
                        abort_unless($model->newQuery()->whereKey($model->getKey())->exists(), 404);
                    }
                }
            }
            if (!$request->isMethod('GET') || str_contains($name, 'budget-proposal.new')) {
                return DB::transaction(function () use ($request, $next, $name, $context) {
                    // Serialize planning changes with finalization/reopening.
                    FiscalYear::orderBy('year')->lockForUpdate()->get();
                    if (str_contains($name, 'budget-proposal') || str_contains($name, 'proposal-review')
                        || str_contains($name, 'budget-approval') || str_contains($name, 'annual-procurement-plan')
                        || str_contains($name, 'market-scoping')) {
                        app(FiscalYearGuard::class)->assertOpen($context->year);
                        foreach ($request->route()->parameters() as $model) {
                            if ($model instanceof \Illuminate\Database\Eloquent\Model) {
                                app(FiscalYearGuard::class)->check($model);
                            }
                        }
                    }
                    return $next($request);
                });
            }
            return $next($request);
        } finally {
            $context->filterReads = false;
            $context->year = null;
        }
    }
}
