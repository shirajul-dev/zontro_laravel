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

        // Plan Routes
        Route::prefix('plans')->group(function () {
            Route::get('/', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'index'])->name('superadmin.plans.index');
            Route::get('/create', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'create'])->name('superadmin.plans.create');
            Route::post('/', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'store'])->name('superadmin.plans.store');
            Route::get('/{plan}/edit', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'edit'])->name('superadmin.plans.edit');
            Route::put('/{plan}', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'update'])->name('superadmin.plans.update');
            Route::delete('/{plan}', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'destroy'])->name('superadmin.plans.destroy');
        });
    });

    Route::post('/login', [AuthController::class, 'login'])->name('superadmin.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('superadmin.logout');
});
