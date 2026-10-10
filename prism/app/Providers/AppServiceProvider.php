<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\FiscalYearContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $paths = [
            \App\Models\BudgetProposal::class => null,
            \App\Models\AnnualProcurementPlan::class => null,
            \App\Models\PurchaseRequest::class => null,
            \App\Models\BudgetProposalItem::class => 'budgetProposal',
            \App\Models\BudgetProposalReview::class => 'budgetProposal',
            \App\Models\PersonnelBudgetItem::class => 'budgetProposal',
            \App\Models\MarketScopingReference::class => 'budgetProposalItem.budgetProposal',
            \App\Models\MarketPriceSurvey::class => 'budgetProposal',
            \App\Models\AnnualProcurementPlanItem::class => 'annualProcurementPlan',
            \App\Models\PurchaseRequestItem::class => 'purchaseRequest',
            \App\Models\AbstractOfCanvass::class => 'purchaseRequest',
            \App\Models\PurchaseOrder::class => 'abstractOfCanvass.purchaseRequest',
        ];
        foreach ($paths as $model => $relation) {
            $model::addGlobalScope('selectedFiscalYear', function ($query) use ($relation) {
                $context = app(\App\Services\FiscalYearContext::class);
                if (!$context->filterReads || !$context->year) return;
                if ($relation) {
                    $query->whereHas($relation, fn ($parent) => $parent->where($parent->qualifyColumn('fiscal_year'), $context->year));
                } else {
                    $query->where($query->qualifyColumn('fiscal_year'), $context->year);
                }
            });
        }
        foreach ([\App\Models\BudgetProposal::class, \App\Models\BudgetProposalItem::class,
            \App\Models\AnnualProcurementPlan::class, \App\Models\AnnualProcurementPlanItem::class,
            \App\Models\PersonnelBudgetItem::class, \App\Models\DocumentUpload::class,
            \App\Models\MarketScopingReference::class, \App\Models\MarketPriceSurvey::class] as $model) {
            $guard = fn ($record) => app(\App\Services\FiscalYearGuard::class)->check($record);
            $model::saving($guard);
            $model::deleting($guard);
            if (method_exists($model, 'restoring')) $model::restoring($guard);
        }
        \App\Models\PurchaseRequest::saving(function ($pr) {
            if ($pr->exists && $pr->isDirty('fiscal_year')) {
                throw \Illuminate\Validation\ValidationException::withMessages(['fiscal_year' => 'An existing PR must retain its original fiscal year.']);
            }
            if ($pr->budget_proposal_id && (!$pr->exists || $pr->isDirty(['budget_proposal_id', 'fiscal_year']))) {
                $year = \App\Models\BudgetProposal::withoutGlobalScope('selectedFiscalYear')->find($pr->budget_proposal_id)?->fiscal_year;
                if ($year && (int) $pr->fiscal_year !== (int) $year) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['fiscal_year' => 'PR and PPMP fiscal years must match.']);
                }
            }
        });
        \Illuminate\Support\Facades\View::composer('prism.partials.fiscal-year', function ($view) {
            $year = app(\App\Services\FiscalYearContext::class)->year;
            $view->with('fyYears', \App\Models\FiscalYear::where(function ($q) use ($year) {
                $q->where('year', '<=', now()->year);
                if (request()->routeIs('office-head.budget-proposal*', 'office-head.market-scoping*')) $q->orWhere('year', $year);
            })->orderByDesc('year')->get());
            $view->with('fySelected', $year);
            $view->with('fyLocked', \App\Models\FiscalYear::find($year)?->status === 'locked');
        });
        \Illuminate\Support\Facades\View::composer('prism.partials.create-ppmp-modal', function ($view) {
            $view->with('planningYears', \App\Models\FiscalYear::where('status', 'open')
                ->where('year', '<=', now()->year + 1)->orderByDesc('year')->get());
            $view->with('defaultPlanningYear', app(\App\Services\FiscalYearContext::class)->year);
        });
        \Illuminate\Support\Facades\View::composer([
            'prism.procurement-office.purchase-order',
            'prism.vice-chancellor.division-procurement-status',
            'prism.accounting-office.dashboard',
            'prism.cashier.dashboard',
        ], function ($view) {
            $view->with('deliveryRows', app(\App\Services\ItemReceivingService::class)->rows(auth()->user()));
        });
        // APP_URL is fixed to http://localhost for local web use, but the mobile
        // app reaches this same backend through a Cloudflare quick tunnel whose
        // hostname changes on every restart. If route()/URL::signedRoute() always
        // built off APP_URL, every link handed to the mobile client would point at
        // "localhost" — unreachable from a phone. Building the root off the actual
        // incoming request instead makes generated (and signed) URLs correct for
        // whichever host the request actually arrived on, web or tunneled.
        if (!$this->app->runningInConsole()) {
            URL::forceRootUrl(request()->getSchemeAndHttpHost());
        }
    }
}
