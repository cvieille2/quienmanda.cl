<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RankingController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/mercadopago', [PaymentController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/pagos', [PaymentController::class, 'start'])
    ->middleware(['throttle:10,60']);

Route::get('/pagos/return', [PaymentController::class, 'return']);
Route::get('/ranking/current', [RankingController::class, 'current'])
    ->middleware(['throttle:180,1', \App\Http\Middleware\NoCacheLiveRanking::class]);
