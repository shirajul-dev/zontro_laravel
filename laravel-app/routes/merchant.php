<?php

use App\Http\Controllers\Merchant\AuthController;
use App\Http\Controllers\Merchant\DashboardController;
use App\Http\Controllers\Merchant\SettingsController;
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
        Route::get('/switch-brand/{id}', [DashboardController::class, 'switchBrand'])->name('switch-brand');
        
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::get('/settings/general', [SettingsController::class, 'general'])->name('settings.general');
        Route::get('/settings/branding', [SettingsController::class, 'branding'])->name('settings.branding');
        Route::get('/settings/social', [SettingsController::class, 'social'])->name('settings.social');
        Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
        Route::post('/settings/branding', [SettingsController::class, 'updateBranding'])->name('settings.branding.update');
        Route::post('/settings/social', [SettingsController::class, 'updateSocial'])->name('settings.social.update');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
