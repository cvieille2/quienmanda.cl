<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\MetaPreviewController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/mercadopago', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/pagos', [PaymentController::class, 'start'])
    ->middleware(['throttle:10,60']);

Route::get('/pagos/return', [PaymentController::class, 'return']);
Route::get('/ranking/current', [RankingController::class, 'current'])
    ->middleware(['throttle:180,1', \App\Http\Middleware\NoCacheLiveRanking::class]);

Route::post('/meta-preview', MetaPreviewController::class)->middleware(['throttle:30,1']);
