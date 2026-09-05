<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentStatusController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/perfil/{slug}', [ProfileController::class, 'show'])->name('profile.show');

Route::get('/pagos/{slug}/pendiente', [PaymentStatusController::class, 'pending'])->name('payments.pending');
