<?php

use App\Http\Controllers\Merchant\AuthController;
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
    });

    // Authenticated Routes
    Route::middleware('auth:merchant')->group(function () {
        Route::get('/dashboard', function () {
            return "Merchant Dashboard (Native)";
        })->name('dashboard');
        
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
