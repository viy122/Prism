<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PrismAccountingOfficeController;
use App\Http\Controllers\PrismAdminController;
use App\Http\Controllers\PrismBacController;
use App\Http\Controllers\PrismCashierController;
use App\Http\Controllers\PrismChancellorController;
use App\Http\Controllers\PrismFinanceOfficeController;
use App\Http\Controllers\PrismOfficeHeadController;
use App\Http\Controllers\PrismProcurementOfficeController;
use App\Http\Controllers\PrismViceChancellorController;
use App\Http\Controllers\SignaturePhotoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'prism.landing')->name('prism.home');

// Authentication routes
Route::get('/login', fn() => view('prism.auth.login'))->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/demo-login/{role}', [AuthController::class, 'demoLogin'])->name('demo.login');

Route::middleware('auth')->group(function () {

    Route::prefix('office-head')->name('office-head.')->middleware('role:Office Head / Dean')->controller(PrismOfficeHeadController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/budget-proposal', 'budgetProposal')->name('budget-proposal');
        Route::post('/budget-proposal/item', 'storeItem')->name('budget-proposal.store-item');
        Route::put('/budget-proposal/item/{item}', 'updateItem')->name('budget-proposal.update-item');
        Route::delete('/budget-proposal/item/{item}', 'destroyItem')->name('budget-proposal.destroy-item');
        Route::post('/budget-proposal/item/{item}/attachment', 'storeItemAttachment')->name('budget-proposal.item-attachment');
        Route::delete('/budget-proposal/attachment/{document}', 'destroyItemAttachment')->name('budget-proposal.attachment.delete');
        Route::post('/budget-proposal/submit', 'submitProposal')->name('budget-proposal.submit');
        Route::post('/budget-proposal/start-new-cycle', 'startNewCycle')->name('budget-proposal.start-new-cycle');
        Route::get('/market-scoping', 'marketScoping')->name('market-scoping');
        Route::post('/market-scoping/run', 'runMarketScoping')->name('market-scoping.run');
        Route::get('/market-scoping/suggestions', 'marketScopingSuggestions')->name('market-scoping.suggestions');
        Route::post('/market-scoping/attach-to-proposal', 'attachToProposal')->name('market-scoping.attach');
        Route::post('/market-scoping/add-item-with-refs', 'addItemWithRefs')->name('market-scoping.add-item-with-refs');
        Route::delete('/market-scoping/ref/{ref}', 'deleteRef')->name('market-scoping.ref.delete');
        Route::get('/market-scoping/mps', 'previewMps')->name('market-scoping.mps');
        Route::post('/market-scoping/mps/submit', 'submitMps')->name('market-scoping.mps.submit');
        Route::get('/my-proposals', 'myProposals')->name('my-proposals');
        Route::get('/purchase-requests', 'purchaseRequests')->name('purchase-requests');
    });

    Route::prefix('finance-office')->name('finance-office.')->middleware('role:Finance Office')->controller(PrismFinanceOfficeController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/proposal-review', 'proposalReview')->name('proposal-review');
        Route::get('/proposal-review/{proposal}', 'proposalReview')->name('proposal-review.show');
        Route::post('/proposal-review/item/{item}/remark', 'saveItemRemark')->name('proposal-review.item-remark');
        Route::post('/proposal-review/{proposal}/endorse', 'endorse')->name('proposal-review.endorse');
        Route::post('/proposal-review/{proposal}/return', 'returnProposal')->name('proposal-review.return');
        Route::get('/budget-utilization-report', 'budgetUtilizationReport')->name('budget-utilization-report');
    });

    Route::prefix('procurement-office')->name('procurement-office.')->middleware('role:Procurement Office')->controller(PrismProcurementOfficeController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/annual-procurement-plan', 'annualProcurementPlan')->name('annual-procurement-plan');
        Route::post('/annual-procurement-plan/item/{item}/mode', 'saveProcurementMode')->name('annual-procurement-plan.save-mode');
        Route::get('/canvassing', 'canvassing')->name('canvassing');
        Route::post('/purchase-request/{pr}/canvass-document', 'uploadCanvassDocument')->name('purchase-request.canvass-document');
        Route::delete('/canvass-document/{document}', 'deleteCanvassDocument')->name('canvass-document.delete');
        Route::get('/purchase-request-management', 'purchaseRequestManagement')->name('purchase-request-management');
        Route::post('/purchase-request/{pr}/status', 'updatePrStatus')->name('purchase-request.update-status');
        Route::post('/purchase-request/{pr}/advance', 'advancePrStage')->name('purchase-request.advance');
        Route::post('/purchase-request/{pr}/return-pr', 'returnPr')->name('purchase-request.return');
        Route::post('/purchase-request/{pr}/canvassing', 'updateCanvassing')->name('purchase-request.canvassing');
        Route::get('/purchase-request/{pr}/market-prices', 'getMarketPrices')->name('purchase-request.market-prices');
        Route::post('/purchase-request/import-pdf', 'importPrFromPdf')->name('purchase-request.import-pdf');
        Route::post('/purchase-request/import-pdf/confirm', 'importPrConfirm')->name('purchase-request.import-confirm');
        Route::post('/purchase-request/{pr}/upload', 'uploadPurchaseRequest')->name('purchase-request.upload');
        Route::get('/abstract-of-canvass', 'abstractOfCanvass')->name('abstract-of-canvass');
        Route::post('/purchase-request/{pr}/create-aoc', 'createAoc')->name('aoc.create');
        Route::post('/aoc/{aoc}/advance', 'advanceAocStage')->name('aoc.advance');
        Route::post('/aoc/{aoc}/return-aoc', 'returnAoc')->name('aoc.return');
        Route::get('/purchase-orders', 'purchaseOrders')->name('purchase-orders');
        Route::post('/aoc/{aoc}/issue-po', 'issuePo')->name('po.issue');
        Route::post('/purchase-order/{po}/status', 'updatePoStatus')->name('po.update-status');
        Route::post('/purchase-order/{po}/advance', 'advancePoStage')->name('po.advance');
        Route::post('/purchase-order/{po}/return-po', 'returnPo')->name('po.return');
        Route::get('/procurement-status-tracking', 'procurementStatusTracking')->name('procurement-status-tracking');
        Route::get('/procurement-reports', 'procurementReports')->name('procurement-reports');
        Route::post('/signature-photo/{docType}/{logId}/reprocess', 'reprocessSignaturePhoto')->name('signature-photo.reprocess')->whereIn('docType', ['pr', 'aoc', 'po']);
    });

    Route::prefix('chancellor')->name('chancellor.')->middleware('role:Chancellor')->controller(PrismChancellorController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/budget-approval', 'budgetApproval')->name('budget-approval');
        Route::post('/budget-approval/{proposal}/approve', 'approve')->name('budget-approval.approve');
        Route::post('/budget-approval/{proposal}/return', 'returnProposal')->name('budget-approval.return');
        Route::get('/for-my-signature', 'forMySignature')->name('for-my-signature');
        Route::post('/sign/{docType}/{id}', 'signDocument')->name('sign')->whereIn('docType', ['pr', 'aoc', 'po']);
        Route::post('/sign/{docType}/{id}/confirm', 'confirmSignDocument')->name('sign.confirm')->whereIn('docType', ['pr', 'aoc', 'po']);
        Route::get('/procurement-reports', 'procurementReports')->name('procurement-reports');
    });

    Route::prefix('vice-chancellor')->name('vice-chancellor.')->middleware('role:Vice Chancellor')->controller(PrismViceChancellorController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/for-my-signature', 'forMySignature')->name('for-my-signature');
        Route::post('/sign/{docType}/{id}', 'signDocument')->name('sign')->whereIn('docType', ['pr', 'aoc', 'po']);
        Route::post('/sign/{docType}/{id}/confirm', 'confirmSignDocument')->name('sign.confirm')->whereIn('docType', ['pr', 'aoc', 'po']);
        Route::get('/division-procurement-status', 'divisionProcurementStatus')->name('division-procurement-status');
        Route::get('/division-performance-report', 'divisionPerformanceReport')->name('division-performance-report');
    });

    Route::prefix('accounting-office')->name('accounting-office.')->middleware('role:Accounting Office')->controller(PrismAccountingOfficeController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::post('/purchase-order/{po}/process-payment', 'startPaymentProcessing')->name('po.process-payment');
        Route::get('/for-my-signature', 'forMySignature')->name('for-my-signature');
        Route::post('/sign/{docType}/{id}', 'signDocument')->name('sign')->whereIn('docType', ['pr', 'aoc', 'po']);
        Route::post('/sign/{docType}/{id}/confirm', 'confirmSignDocument')->name('sign.confirm')->whereIn('docType', ['pr', 'aoc', 'po']);
    });

    Route::prefix('bac')->name('bac.')->middleware('role:BAC')->controller(PrismBacController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/for-my-signature', 'forMySignature')->name('for-my-signature');
        Route::post('/sign/{docType}/{id}', 'signDocument')->name('sign')->whereIn('docType', ['pr', 'aoc', 'po']);
        Route::post('/sign/{docType}/{id}/confirm', 'confirmSignDocument')->name('sign.confirm')->whereIn('docType', ['pr', 'aoc', 'po']);
    });

    Route::prefix('cashier')->name('cashier.')->middleware('role:Cashier')->controller(PrismCashierController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::post('/purchase-order/{po}/upload-receipt', 'uploadReceipt')->name('po.upload-receipt');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:System Administrator')->controller(PrismAdminController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/user-management', 'userManagement')->name('user-management');
        Route::post('/users', 'storeUser')->name('users.store');
        Route::put('/users/{user}', 'updateUser')->name('users.update');
        Route::post('/users/{user}/deactivate', 'deactivateUser')->name('users.deactivate');
        Route::post('/users/{user}/reactivate', 'reactivateUser')->name('users.reactivate');
        Route::post('/users/{user}/reset-password', 'resetPassword')->name('users.reset-password');
    });

    // Unblurred signed-document photo — procurement / admin / uploader only
    Route::get('/signature-photo/{docType}/{logId}/original', [SignaturePhotoController::class, 'original'])
        ->name('signature-photo.original')
        ->whereIn('docType', ['pr', 'aoc', 'po']);

    // ── Notifications (all authenticated users) ───────────────────────────────
    Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',           'index')->name('index');
        Route::get('/count',      'unreadCount')->name('count');
        Route::post('/{id}/read', 'markRead')->name('read');
        Route::post('/read-all',  'markAllRead')->name('read-all');
    });

});
