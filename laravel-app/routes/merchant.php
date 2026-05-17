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
        Route::get('/settings/currencies', [SettingsController::class, 'currencies'])->name('settings.currencies');
        Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
        Route::post('/settings/branding', [SettingsController::class, 'updateBranding'])->name('settings.branding.update');
        Route::post('/settings/currencies/update', [SettingsController::class, 'updateCurrency'])->name('settings.currencies.update');
        Route::post('/settings/currencies/import', [SettingsController::class, 'importCurrencies'])->name('settings.currencies.import');
        Route::post('/settings/currencies/sync', [SettingsController::class, 'syncRates'])->name('settings.currencies.sync');

        // Brand FAQ settings
        Route::get('/settings/faqs', [SettingsController::class, 'faqs'])->name('settings.faqs');
        Route::post('/settings/faqs/list', [SettingsController::class, 'faqList'])->name('settings.faqs.list');
        Route::post('/settings/faqs/create', [SettingsController::class, 'faqCreate'])->name('settings.faqs.create');
        Route::post('/settings/faqs/edit', [SettingsController::class, 'faqEdit'])->name('settings.faqs.edit');
        Route::get('/settings/faqs/{id}/info', [SettingsController::class, 'faqInfo'])->name('settings.faqs.info');
        Route::post('/settings/faqs/bulk', [SettingsController::class, 'faqBulkAction'])->name('settings.faqs.bulk');
        Route::post('/settings/faqs/{id}/delete', [SettingsController::class, 'faqDelete'])->name('settings.faqs.delete');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
