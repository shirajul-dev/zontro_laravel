<?php

return [
    'demo_mode' => env('PIPRAPAY_DEMO_MODE', false),
    'paths' => [
        'payment' => env('PIPRAPAY_PAYMENT_PATH', 'payment'),
        'invoice' => env('PIPRAPAY_INVOICE_PATH', 'invoice'),
        'payment_link' => env('PIPRAPAY_PAYMENT_LINK_PATH', 'payment-link'),
        'admin' => env('PIPRAPAY_ADMIN_PATH', 'admin'),
        'cron' => env('PIPRAPAY_CRON_PATH', 'cron'),
    ],

    // Keep legacy runtime default-on, but make diagnostics controllable.
    'legacy_runtime' => [
        'verbose_logs' => env('PIPRAPAY_LEGACY_VERBOSE_LOGS', false),
    ],

    // Feature toggles for gradual migration and safe rollback.
    'migration' => [
        'strict_module_slug_validation' => env('PIPRAPAY_STRICT_MODULE_SLUG_VALIDATION', true),
        'native_api_checkout_enabled' => env('PIPRAPAY_NATIVE_API_CHECKOUT_ENABLED', false),
        'native_api_verify_payment_enabled' => env('PIPRAPAY_NATIVE_API_VERIFY_PAYMENT_ENABLED', false),
        'native_invoice_webhook_enabled' => env('PIPRAPAY_NATIVE_INVOICE_WEBHOOK_ENABLED', false),
        'native_admin_actions_enabled' => env('PIPRAPAY_NATIVE_ADMIN_ACTIONS_ENABLED', true),
    ],

    // Security hardening toggles (default conservative to preserve hybrid compatibility).
    'security' => [
        'api_rate_limit_per_minute' => (int) env('PIPRAPAY_API_RATE_LIMIT_PER_MINUTE', 120),
        'strict_api_methods_enabled' => env('PIPRAPAY_STRICT_API_METHODS_ENABLED', false),
        'strict_csrf_scope_enabled' => env('PIPRAPAY_STRICT_CSRF_SCOPE_ENABLED', false),
    ],

    // Merchant Theme configuration
    'merchant_theme' => env('PIPRAPAY_MERCHANT_THEME', 'default'),
];
