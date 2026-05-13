<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\MerchantController;

$domain = env('APP_DOMAIN') ?: parse_url(env('APP_URL', 'https://zontropay.local'), PHP_URL_HOST);

Route::domain('root.' . $domain)->group(function () {
    Route::middleware('guest:superadmin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('superadmin.login');
    });

    Route::middleware('auth:superadmin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('superadmin.dashboard');

        // Merchant Routes
        Route::prefix('merchants')->group(function () {
            Route::get('/', [MerchantController::class, 'index'])->name('superadmin.merchants.index');
            Route::get('/create', [MerchantController::class, 'create'])->name('superadmin.merchants.create');
            Route::post('/', [MerchantController::class, 'store'])->name('superadmin.merchants.store');
            Route::get('/{merchant}', [MerchantController::class, 'show'])->name('superadmin.merchants.show');
            Route::get('/{merchant}/edit', [MerchantController::class, 'edit'])->name('superadmin.merchants.edit');
            Route::put('/{merchant}', [MerchantController::class, 'update'])->name('superadmin.merchants.update');
            Route::post('/{merchant}/suspend', [MerchantController::class, 'suspend'])->name('superadmin.merchants.suspend');
            Route::post('/{merchant}/reactivate', [MerchantController::class, 'reactivate'])->name('superadmin.merchants.reactivate');
        });
    });

    Route::post('/login', [AuthController::class, 'login'])->name('superadmin.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('superadmin.logout');
});
