<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PrismChancellorController;
use App\Http\Controllers\PrismFinanceOfficeController;
use App\Http\Controllers\PrismOfficeHeadController;
use App\Http\Controllers\PrismProcurementOfficeController;
use App\Http\Controllers\PrismViceChancellorController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'prism.landing')->name('prism.home');

// Authentication routes
Route::get('/login', fn() => view('prism.auth.login'))->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::prefix('office-head')->name('office-head.')->middleware('role:Office Head / Dean')->controller(PrismOfficeHeadController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/budget-proposal', 'budgetProposal')->name('budget-proposal');
        Route::post('/budget-proposal/item', 'storeItem')->name('budget-proposal.store-item');
        Route::delete('/budget-proposal/item/{item}', 'destroyItem')->name('budget-proposal.destroy-item');
        Route::post('/budget-proposal/submit', 'submitProposal')->name('budget-proposal.submit');
        Route::get('/market-scoping', 'marketScoping')->name('market-scoping');
        Route::post('/market-scoping/run', 'runMarketScoping')->name('market-scoping.run');
        Route::post('/market-scoping/attach-to-proposal', 'attachToProposal')->name('market-scoping.attach');
        Route::post('/market-scoping/add-item-with-refs', 'addItemWithRefs')->name('market-scoping.add-item-with-refs');
        Route::delete('/market-scoping/ref/{ref}', 'deleteRef')->name('market-scoping.ref.delete');
        Route::get('/my-proposals', 'myProposals')->name('my-proposals');
        Route::get('/purchase-requests', 'purchaseRequests')->name('purchase-requests');
        Route::post('/purchase-requests/{pr}/upload', 'uploadPurchaseRequest')->name('purchase-requests.upload');
    });

    Route::prefix('finance-office')->name('finance-office.')->middleware('role:Finance Office')->controller(PrismFinanceOfficeController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/proposal-review', 'proposalReview')->name('proposal-review');
        Route::get('/proposal-review/{proposal}', 'proposalReview')->name('proposal-review.show');
        Route::post('/proposal-review/{proposal}/endorse', 'endorse')->name('proposal-review.endorse');
        Route::post('/proposal-review/{proposal}/return', 'returnProposal')->name('proposal-review.return');
        Route::get('/annual-procurement-plan', 'annualProcurementPlan')->name('annual-procurement-plan');
        Route::post('/annual-procurement-plan/item/{item}/mode', 'saveProcurementMode')->name('annual-procurement-plan.save-mode');
        Route::get('/budget-utilization-report', 'budgetUtilizationReport')->name('budget-utilization-report');
    });

    Route::prefix('procurement-office')->name('procurement-office.')->middleware('role:Procurement Office')->controller(PrismProcurementOfficeController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/purchase-request-management', 'purchaseRequestManagement')->name('purchase-request-management');
        Route::post('/purchase-request/{pr}/status', 'updatePrStatus')->name('purchase-request.update-status');
        Route::get('/procurement-status-tracking', 'procurementStatusTracking')->name('procurement-status-tracking');
        Route::get('/procurement-reports', 'procurementReports')->name('procurement-reports');
    });

    Route::prefix('chancellor')->name('chancellor.')->middleware('role:Chancellor')->controller(PrismChancellorController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/budget-approval', 'budgetApproval')->name('budget-approval');
        Route::post('/budget-approval/{proposal}/approve', 'approve')->name('budget-approval.approve');
        Route::post('/budget-approval/{proposal}/return', 'returnProposal')->name('budget-approval.return');
        Route::get('/procurement-reports', 'procurementReports')->name('procurement-reports');
    });

    Route::prefix('vice-chancellor')->name('vice-chancellor.')->middleware('role:Vice Chancellor')->controller(PrismViceChancellorController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/division-procurement-status', 'divisionProcurementStatus')->name('division-procurement-status');
        Route::get('/division-performance-report', 'divisionPerformanceReport')->name('division-performance-report');
    });

    // ── Notifications (all authenticated users) ───────────────────────────────
    Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',           'index')->name('index');
        Route::get('/count',      'unreadCount')->name('count');
        Route::post('/{id}/read', 'markRead')->name('read');
        Route::post('/read-all',  'markAllRead')->name('read-all');
    });

});
