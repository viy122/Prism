<?php

use App\Http\Controllers\PrismChancellorController;
use App\Http\Controllers\PrismFinanceOfficeController;
use App\Http\Controllers\PrismOfficeHeadController;
use App\Http\Controllers\PrismProcurementOfficeController;
use App\Http\Controllers\PrismViceChancellorController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'prism.landing')->name('prism.home');
Route::view('/login', 'prism.auth.login')->name('login');

Route::prefix('office-head')->name('office-head.')->controller(PrismOfficeHeadController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/budget-proposal', 'budgetProposal')->name('budget-proposal');
    Route::get('/market-scoping', 'marketScoping')->name('market-scoping');
    Route::get('/my-proposals', 'myProposals')->name('my-proposals');
    Route::get('/purchase-requests', 'purchaseRequests')->name('purchase-requests');
});

Route::prefix('finance-office')->name('finance-office.')->controller(PrismFinanceOfficeController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/proposal-review', 'proposalReview')->name('proposal-review');
    Route::get('/proposal-review/{proposal}', 'proposalReview')->name('proposal-review.show');
    Route::get('/annual-procurement-plan', 'annualProcurementPlan')->name('annual-procurement-plan');
    Route::get('/budget-utilization-report', 'budgetUtilizationReport')->name('budget-utilization-report');
});

Route::prefix('procurement-office')->name('procurement-office.')->controller(PrismProcurementOfficeController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/purchase-request-management', 'purchaseRequestManagement')->name('purchase-request-management');
    Route::get('/procurement-status-tracking', 'procurementStatusTracking')->name('procurement-status-tracking');
    Route::get('/procurement-reports', 'procurementReports')->name('procurement-reports');
});

Route::prefix('chancellor')->name('chancellor.')->controller(PrismChancellorController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/budget-approval', 'budgetApproval')->name('budget-approval');
    Route::get('/procurement-reports', 'procurementReports')->name('procurement-reports');
});

Route::prefix('vice-chancellor')->name('vice-chancellor.')->controller(PrismViceChancellorController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/division-procurement-status', 'divisionProcurementStatus')->name('division-procurement-status');
    Route::get('/division-performance-report', 'divisionPerformanceReport')->name('division-performance-report');
});
