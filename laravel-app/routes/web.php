<?php

use App\Http\Controllers\Admin\CronController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Payment\CheckoutController;
use App\Http\Controllers\Payment\IpnController;
use App\Http\Controllers\Payment\InvoiceController;
use App\Http\Controllers\ModuleAssetController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Legacy\LegacyRouteDispatchController;
use Illuminate\Support\Facades\Route;

$paymentPath = trim((string) config('piprapay.paths.payment', 'payment'), '/');
$invoicePath = trim((string) config('piprapay.paths.invoice', 'invoice'), '/');
$paymentLinkPath = trim((string) config('piprapay.paths.payment_link', 'payment-link'), '/');
$cronPath = trim((string) config('piprapay.paths.cron', 'cron'), '/');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| These routes handle the frontend website and public-facing features.
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::post('/', [LegacyRouteDispatchController::class, 'handleRootPost'])->name('home.post');

/*
|--------------------------------------------------------------------------
| API & IPN Routes
|--------------------------------------------------------------------------
*/
Route::match(['get', 'post'], '/api/{api_type}/{api_subtype?}', [ApiController::class, 'handle'])
    ->where('api_type', '[A-Za-z0-9_-]+')
    ->where('api_subtype', '[A-Za-z0-9_-]+')
    ->middleware(['throttle:piprapay_api'])
    ->name('api.handle');

Route::match(['get', 'post'], '/ipn/{gateway_id}', [IpnController::class, 'handle'])
    ->where('gateway_id', '[A-Za-z0-9_-]+')
    ->name('payment.ipn');

/*
|--------------------------------------------------------------------------
| Public Payment & Invoice Routes
|--------------------------------------------------------------------------
*/
Route::match(['get', 'post'], '/' . $paymentPath . '/{ref}', [CheckoutController::class, 'show'])
    ->where('ref', '[A-Za-z0-9_-]+')
    ->name('payment.checkout');

Route::match(['get', 'post'], '/' . $invoicePath . '/webhook', [InvoiceController::class, 'webhook'])
    ->name('invoice.webhook');

Route::match(['get', 'post'], '/' . $invoicePath . '/{ref}', [InvoiceController::class, 'show'])
    ->where('ref', '[A-Za-z0-9_-]+')
    ->name('invoice.show');

Route::match(['get', 'post'], '/' . $paymentLinkPath . '/default/{brand_id}', [CheckoutController::class, 'paymentLinkDefault'])
    ->where('brand_id', '[A-Za-z0-9_-]+')
    ->name('payment-link.default');

Route::match(['get', 'post'], '/' . $paymentLinkPath . '/{ref}', [CheckoutController::class, 'paymentLink'])
    ->where('ref', '[A-Za-z0-9_-]+')
    ->name('payment-link.show');

/*
|--------------------------------------------------------------------------
| Utilities & Dynamic Assets
|--------------------------------------------------------------------------
*/
Route::get('/module-asset/{type}/{module}/{path}', [ModuleAssetController::class, 'serve'])
    ->where('type', 'theme|themes|gateway|gateways|addon|addons|checkout-theme|checkout-themes')
    ->where('path', '.*')
    ->name('module.asset.v2');

Route::get('/pp-{type}/{module}/assets/{path}', [ModuleAssetController::class, 'serve'])
    ->where('type', 'theme|gateway|addon|checkout-theme')
    ->where('path', '.*')
    ->name('module.asset');

/**
 * Addon Webhook Dispatcher
 * Supports direct access to webhook.php files inside the new module structure.
 */
Route::match(['get', 'post'], '/app/Modules/addons/{slug}/webhook.php', function($slug) {
    $path = app_path('Modules/addons/' . $slug . '/webhook.php');
    if (file_exists($path)) {
        require $path;
        return;
    }
    abort(404);
})->name('addon.webhook.direct');

Route::match(['get', 'post'], '/addon-webhook/{slug}', function($slug) {
    $path = app_path('Modules/addons/' . $slug . '/webhook.php');
    if (file_exists($path)) {
        require $path;
        return;
    }
    abort(404);
})->name('addon.webhook');

Route::match(['get', 'post'], '/' . $cronPath . '/{token?}', [CronController::class, 'handle'])
    ->where('token', '.*')
    ->name('admin.cron');

Route::get('/404', [LegacyRouteDispatchController::class, 'show404'])->name('legacy.404');

Route::fallback([LegacyRouteDispatchController::class, 'fallback']);

require __DIR__ . '/test_csrf.php';
