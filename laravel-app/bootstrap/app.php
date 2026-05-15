<?php

use App\Http\Middleware\SyncLegacyAdminSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

if (!defined('PipraPay_INIT')) {
    define('PipraPay_INIT', true);
}

require_once dirname(__DIR__) . '/app/Support/helpers.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // SuperAdmin routes FIRST so they intercept the domain properly
            Route::middleware('web')
                ->group(base_path('routes/superadmin.php'));

            // Standard web routes second
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Admin routes third
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            // Merchant routes (Native)
            Route::middleware('web')
                ->group(base_path('routes/merchant.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $strictCsrfScope = filter_var(env('PIPRAPAY_STRICT_CSRF_SCOPE_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
        $invoicePath = trim((string) env('PIPRAPAY_INVOICE_PATH', 'invoice'), '/');

        $csrfExcept = [
            'ipn/*',
            $invoicePath . '/webhook',
        ];

        if (!$strictCsrfScope) {
            $csrfExcept = [
                'ipn/*',
                'api/*',
                'payment/*',
                'invoice/*',
                'payment-link/*',
                'admin/*',
                'cron/*',
                'login',
                'forgot',
                '2fa',
                'homepageRedirect',
                '/',   // payment form AJAX (action-v2) uses legacy CSRF via pp-adapter
                '',    // empty path also matches root
            ];
        }

        $middleware->encryptCookies(except: [
            'pp_admin',
            'pp_2fa',
        ]);

        $middleware->validateCsrfTokens(except: $csrfExcept);

        $middleware->redirectGuestsTo(function (Request $request) {
            $host = $request->getHost();

            if (str_starts_with($host, 'root.')) {
                return route('superadmin.login');
            }

            return '/admin/login';
        });

        $middleware->appendToGroup('web', SyncLegacyAdminSession::class);
        $middleware->alias([
            'api_auth' => \App\Http\Middleware\AuthenticateApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
