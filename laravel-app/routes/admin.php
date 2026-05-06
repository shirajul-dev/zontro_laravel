<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NativeAdminActionController;
use App\Http\Controllers\Admin\NativeAdminPageController;
use Illuminate\Support\Facades\Route;

$adminPath = trim((string) config('piprapay.paths.admin', 'admin'), '/');

Route::group(['prefix' => $adminPath], function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication Routes (Native)
    |--------------------------------------------------------------------------
    | These routes handle the centralized admin authentication system.
    */
    Route::controller(AuthController::class)->group(function () {
        Route::get('/login', 'login')->name('native.auth.login');
        Route::post('/login', 'loginRequest')->name('native.login.post');
        Route::get('/logout', 'logout')->name('native.admin.logout');

        Route::get('/2fa', 'twoFactor')->name('admin.2fa.index');
        Route::post('/2fa', 'twoFactorVerify')->name('admin.2fa.verify');

        Route::get('/forgot', 'forgot')->name('native.auth.forgot');
        Route::post('/forgot', 'forgotRequest')->name('native.forgot.post');
    });

    /*
    |--------------------------------------------------------------------------
    | Core Dashboard & Native Modules
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
    Route::post('/api/dashboard/transaction-statistics', [DashboardController::class, 'transactionStatistics'])->name('admin.dashboard.transaction-stats');
    Route::post('/api/dashboard/gateway-statistics', [DashboardController::class, 'gatewayStatistics'])->name('admin.dashboard.gateway-stats');

    // Brand Management
    Route::get('/brands', [BrandController::class, 'index'])->name('admin.brands.index');
    Route::post('/api/brands/list', [BrandController::class, 'list'])->name('admin.brands.list.ajax');

    // Account & Profile
    Route::get('/my-account', [NativeAdminPageController::class, 'myAccount'])->name('native.admin.my-account');

    /*
    |--------------------------------------------------------------------------
    | Legacy Bridge Dispatcher
    |--------------------------------------------------------------------------
    | Catches all other admin pages and dispatches them to the legacy bridge.
    */
    Route::get('/{page_name?}', [NativeAdminPageController::class, 'page'])
        ->where('page_name', '.*')
        ->name('native.admin.page');

    Route::post('/dashboard', [NativeAdminActionController::class, 'handle'])->name('native.admin.dashboard.post');
    Route::post('/{page_name?}', [NativeAdminActionController::class, 'handle'])
        ->where('page_name', '.*')
        ->name('native.admin.post');
});

// The shell route (base admin URL)
Route::get('/' . $adminPath, [NativeAdminPageController::class, 'shell'])->name('native.admin.shell');
