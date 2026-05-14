# PipraPay Migration Current State And Access

Date: 17 April 2026

## What Has Been Completed

- Laravel application scaffolded in laravel-app.
- Database schema mirrored from demo.sql into Laravel migration and model layer.
- Legacy UI templates migrated to Blade copies under resources/views/legacy.
- Route map migrated to Laravel route definitions and path-prefix config.
- Controller entrypoints migrated to Laravel controller methods.
- Shared DB helper layer in legacy functions converted to Query Builder-backed execution paths.
- Auth/session bridge implemented for pp_admin and pp_2fa cookie/session behavior.
- Complex runtime/auth behavior isolated into Laravel service classes.
- Cross-route validation smoke checks executed successfully after parity adjustments.

## What You Can Access Now

The app is accessible through Laravel routes that dispatch into legacy runtime behavior.

Core accessible endpoints:

- GET or POST /
- GET /404
- GET or POST /login
- GET or POST /forgot
- GET or POST /2fa
- GET or POST /admin/{page_name?}
- GET or POST /payment/{ref}
- GET or POST /invoice/{ref}
- GET or POST /invoice/webhook
- GET or POST /payment-link/default/{brand_id}
- GET or POST /payment-link/{ref}
- GET or POST /api/{api_type}/{api_subtype?}
- GET or POST /ipn/{gateway_id}
- GET or POST /cron/{token?}
- GET or POST /homepageRedirect
- Fallback route for unknown paths

These are defined in laravel-app/routes/web.php.

## How To Access

1. Open terminal in laravel-app.
2. Run: php artisan serve
3. Open browser at: http://127.0.0.1:8000
4. Use legacy paths directly:
   - http://127.0.0.1:8000/login
   - http://127.0.0.1:8000/admin/dashboard
   - http://127.0.0.1:8000/payment/1234567890

Notes:

- Path prefixes are configurable in laravel-app/config/piprapay.php.
- Current database is SQLite for this migration run.

## Authentication And Session Behavior Now

- Laravel guard/provider now uses pp_admin model/table mapping.
- Legacy auth cookies are still honored:
  - pp_admin
  - pp_2fa
- Browser session validation still checks pp_browser_log.
- Middleware syncs legacy cookie state into Laravel session/auth context.

## What Still Depends On Raw PHP

These areas still execute legacy runtime code directly for parity:

- Root legacy dispatcher file at index.php.
- Legacy include runtime files:
  - app/Support/zp-functions.php
  - pp-content/pp-include/pp-adapter.php
- Theme gateway and addon class loading via legacy require paths.
- Legacy page rendering and branch logic under pp-content and pp-modules.

Important:

- This is currently a Laravel-hosted compatibility bridge with migrated route/auth/schema structure.
- It is functionally routed through Laravel, but core domain execution still uses legacy runtime includes to preserve exact behavior.

## What Is Native Laravel Already

- Route registration and endpoint mapping.
- Guard/provider auth configuration and middleware wiring.
- Controller dispatch layer.
- Service layer for runtime dispatch and auth sync.
- Database schema migration and Eloquent model definitions.
- Blade mirror of legacy views.

## Remaining Work If You Want Fully Native Laravel Internals (No Legacy Runtime Execution)

- Replace legacy index.php runtime dispatch with native Laravel request handlers per domain flow.
- Port pp-adapter.php action branches into native Laravel controllers/services.
- Port remaining procedural helper logic from pp-functions.php into dedicated Laravel service classes.
- Replace dynamic require-based module loading with Laravel service contracts/registries.
- Move legacy webhook, cron, payment, invoice, and admin action internals to fully native service methods.
- Remove dependency on legacy global state and superglobals.

## Current Status Summary

- Migration phases 1 through 9 are marked complete in laravel_migration_plan.md.
- Operationally, routes are accessible through Laravel and preserve legacy behavior.
- Architecturally, some core execution still relies on legacy raw PHP runtime for strict parity.
