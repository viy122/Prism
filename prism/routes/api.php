<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PrScanController;
use App\Http\Controllers\Api\SignatoryController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/pr/{id}/scan', [PrScanController::class, 'upload']);

    Route::get('/my-signatures', [SignatoryController::class, 'queue']);
    Route::post('/sign/{docType}/{id}', [SignatoryController::class, 'sign'])->whereIn('docType', ['pr', 'aoc', 'po']);
    Route::post('/sign/{docType}/{id}/confirm', [SignatoryController::class, 'confirm'])->whereIn('docType', ['pr', 'aoc', 'po']);
});
