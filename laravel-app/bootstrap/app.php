<?php

use App\Http\Middleware\SyncLegacyAdminSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

if (!defined('PipraPay_INIT')) {
    define('PipraPay_INIT', true);
}

require_once dirname(__DIR__) . '/app/Support/helpers.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
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

        $middleware->appendToGroup('web', SyncLegacyAdminSession::class);
        $middleware->alias([
            'api_auth' => \App\Http\Middleware\AuthenticateApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
