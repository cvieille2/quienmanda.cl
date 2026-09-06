<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalMercadoPagoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentStatusController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\TermsController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/perfil/{slug}', [ProfileController::class, 'show'])->name('profile.show');

Route::get('/pagos/return', [PaymentController::class, 'return'])->name('payments.return');
Route::get('/pagos/resultado', [PaymentController::class, 'result'])->name('payments.resultado');
Route::get('/pagos/{slug}/pendiente', [PaymentStatusController::class, 'pending'])->name('payments.pending');

if (! app()->environment('production')) {
    Route::get('/local/mercadopago/checkout/{token}', [LocalMercadoPagoController::class, 'checkout'])
        ->name('local.mercadopago.checkout');
    Route::post('/local/mercadopago/checkout/{token}/approve', [LocalMercadoPagoController::class, 'approve'])
        ->name('local.mercadopago.approve');
    Route::post('/local/mercadopago/checkout/{token}/reject', [LocalMercadoPagoController::class, 'reject'])
        ->name('local.mercadopago.reject');
}

Route::get('/categorias', \App\Http\Controllers\CategoryController::class)->name('categories');

Route::get('/terminos', TermsController::class)->name('legal.terms');
Route::get('/privacidad', PrivacyController::class)->name('legal.privacy');
Route::get('/reglas', RulesController::class)->name('rules');
