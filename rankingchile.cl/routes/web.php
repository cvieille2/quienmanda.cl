<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalMercadoPagoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentStatusController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OutboundClickController;
use App\Http\Controllers\ProfileOnboardingController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\TermsController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/perfil/{slug}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/login', fn () => view('auth.placeholder', ['title' => 'Iniciar sesión', 'description' => 'Por ahora puedes explorar el ranking o sumarte.']))->name('login');
Route::get('/registro', fn () => view('auth.placeholder', ['title' => 'Súmate', 'description' => 'Crea tu presencia en Quién Manda y empieza a competir.']))->name('register');
Route::post('/logout', function () { auth()->logout(); request()->session()->invalidate(); request()->session()->regenerateToken(); return redirect()->route('home'); })->name('logout');

Route::get('/clic/{profile:slug}', [OutboundClickController::class, 'track'])->name('outbound.click');

Route::get('/entrar', [ProfileOnboardingController::class, 'index'])->name('entrar.index');
Route::post('/entrar/detectar', [ProfileOnboardingController::class, 'detect'])->name('entrar.detectar');
Route::get('/entrar/{submission}', [ProfileOnboardingController::class, 'show'])->name('entrar.show');
Route::post('/entrar/{submission}/confirmar', [ProfileOnboardingController::class, 'confirm'])->name('entrar.confirmar');
Route::match(['get', 'post'], '/entrar/{submission}/posicion', [ProfileOnboardingController::class, 'position'])->name('entrar.posicion');
Route::post('/entrar/{submission}/checkout', [ProfileOnboardingController::class, 'checkout'])->name('entrar.checkout');

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

Route::get('/categorias', CategoryController::class)->name('categories');

Route::get('/terminos', TermsController::class)->name('legal.terms');
Route::get('/privacidad', PrivacyController::class)->name('legal.privacy');
Route::get('/reglas', RulesController::class)->name('rules');

// Category mini-home (canonical)
Route::get('/categoria/{slug}', [\App\Http\Controllers\CategoryShowController::class, 'show'])->name('category.show');

// Legacy /categorias/{slug} → 301 to canonical
Route::get('/categorias/{slug}', [\App\Http\Controllers\CategoryShowController::class, 'legacy'])
    ->where('slug', '[a-z0-9-]+')
    ->name('category.show.legacy');
