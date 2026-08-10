<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MarketScopingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PrScanController;
use App\Http\Controllers\Api\SignatoryController;
use App\Http\Controllers\Api\TrackingController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/password/forgot', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,10');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:5,10');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/pr/{id}/scan', [PrScanController::class, 'upload']);

    Route::get('/my-signatures', [SignatoryController::class, 'queue']);
    Route::get('/my-documents', [TrackingController::class, 'myDocuments']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/push-token', [NotificationController::class, 'registerPushToken']);
    Route::get('/sign/{docType}/{id}/document', [SignatoryController::class, 'document'])->whereIn('docType', ['pr', 'aoc', 'po']);
    Route::post('/sign/{docType}/{id}', [SignatoryController::class, 'sign'])->whereIn('docType', ['pr', 'aoc', 'po']);

    Route::post('/market-scoping/run', [MarketScopingController::class, 'run']);
});
