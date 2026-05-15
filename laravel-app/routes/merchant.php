<?php

use App\Http\Controllers\Merchant\AuthController;
use App\Http\Controllers\Merchant\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Merchant Routes (Native Laravel Standard)
|--------------------------------------------------------------------------
*/

Route::prefix('merchant')->name('merchant.')->group(function () {

    // Guest Routes
    Route::middleware('guest:merchant')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');

        Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    // Authenticated Routes
    Route::middleware('auth:merchant')->group(function () {
        Route::get('/', function () {
            return redirect()->route('merchant.dashboard');
        });

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
