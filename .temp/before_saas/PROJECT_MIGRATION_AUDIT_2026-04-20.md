# PipraPay Project Audit and Migration Status

Date: 2026-04-20
Scope: Full workspace audit focused on migration health, runtime errors, misimplementations, remaining tasks, optimization backlog, and raw PHP dependency inventory.

## Executive Summary

Current architecture is hybrid.

- Laravel-native: authentication pages and logic, admin shell routing/rendering, selected admin actions.
- Laravel + legacy bridge: API, IPN, cron, root homepage, fallback routes, some admin POST actions, invoice webhook.
- Laravel theme engine with legacy templates/helpers: payment, payment-link, invoice rendering.

Conclusion:
- You cannot safely delete all raw PHP code yet.
- Core runtime still depends on root index.php and legacy include/module directories.

## Findings (Ordered by Severity)

## Critical

1) Legacy core router is still required for multiple live routes
- Evidence:
  - laravel-app/routes/web.php:57,62,100,105,106,108
  - laravel-app/app/Services/Legacy/LegacyRuntimeService.php:18-22,70
  - index.php:13-17,43-47
- Why it matters:
  - Deleting raw PHP core files will break API, IPN, cron, root page, and fallback behavior immediately.

2) Production routes and test environment are incompatible by default
- Evidence:
  - php artisan test currently fails Feature test because route / executes legacy runtime and expects pp_ tables in DB.
  - Failure trace points to app/Support/helpers.php:119 via pp-content/pp-include/pp-adapter.php:165.
- Why it matters:
  - CI reliability is weak. Default test environment (:memory: sqlite) cannot satisfy legacy runtime assumptions.

## High

3) Broad CSRF exclusions increase attack surface
- Evidence:
  - laravel-app/bootstrap/app.php:26-39 excludes ipn/*, api/*, payment/*, invoice/*, payment-link/*, admin/*, cron/*, login, forgot, 2fa, and /.
- Why it matters:
  - Some exclusions are intentional for legacy compatibility, but the current scope is very wide and should be reduced or justified route-by-route.

4) Dynamic module loading based on DB slug without strict whitelist
- Evidence:
  - Theme load: laravel-app/app/Services/Theme/ThemeService.php:449,456
  - Gateway/theme load in legacy runtime: index.php:188,977,1234,1417,1513
- Why it matters:
  - If slug data is corrupted or manipulated, runtime behavior depends on filesystem path construction from DB values.
  - There is no central allow-list validation before include/require.

5) Legacy runtime logs at INFO on nearly every request
- Evidence:
  - laravel-app/app/Services/Legacy/LegacyRuntimeService.php:49-60
  - Logs show repeated session and CSRF token lines.
- Why it matters:
  - Log noise hides real incidents and increases storage/IO cost.

## Medium

6) Superglobal mutation pattern is fragile under complexity
- Evidence:
  - laravel-app/app/Services/Legacy/LegacyRuntimeService.php:31-47,82-86
  - laravel-app/app/Http/Controllers/Admin/NativeAdminPageController.php:43-46,112-126
- Why it matters:
  - Hybrid code depends heavily on mutable global state. This increases regression risk and makes behavior harder to reason about.

7) Legacy helper static analysis warnings indicate maintainability debt
- Evidence:
  - get_errors reported issues in app/Support/zp-functions.php:18,1706,1710,1715,1717,2327.
- Notes:
  - url()->full() appears valid at runtime in this app context (method exists).
  - Imagick and FPDF warnings are environment/type-resolution related and can be runtime-safe if extension/classes are available.
- Why it matters:
  - Tooling confidence is reduced; contributors may misread true vs false positives.

8) TODO debt remains in API controller
- Evidence:
  - laravel-app/app/Http/Controllers/Api/ApiController.php:45,54
- Why it matters:
  - Confirms incomplete native migration for checkout and verify-payment API logic.

## Low

9) Empty public legacy folder can cause confusion
- Evidence:
  - laravel-app/public/pp-content exists but currently contains no files.
- Why it matters:
  - Not a runtime bug, but can mislead maintainers into thinking modules are copied there.

## Current Migrated Flow (How it Works Now)

## Route-level flow map

1) Admin panel
- GET admin pages:
  - laravel-app/routes/web.php:40-46
  - Controller: laravel-app/app/Http/Controllers/Admin/NativeAdminPageController.php
  - Rendering: Blade views under laravel-app/resources/views/legacy/pp-content/pp-admin
  - Still loads legacy helpers from app/Support/zp-functions.php.

- POST admin actions:
  - laravel-app/routes/web.php:47-50
  - Controller: laravel-app/app/Http/Controllers/Admin/NativeAdminActionController.php
  - Some actions are native, unknown/legacy actions are dispatched to LegacyRuntimeService.

2) Authentication (login/forgot/2fa)
- Routes: laravel-app/routes/web.php:28-33
- Controllers: NativeAuthPageController and NativeAdminAuthController
- 2FA helper dependency: pp-media/sdk/GoogleAuthenticator.php (via public path).

3) Payment, payment-link, invoice page rendering
- Routes: laravel-app/routes/web.php:71-88
- Controllers:
  - CheckoutController uses ThemeService.
  - InvoiceController uses ThemeService for show, LegacyRuntimeService for webhook.
- ThemeService loads:
  - app/Support/zp-functions.php
  - pp-content/pp-modules/pp-themes/{slug}/class.php
- Templates execute in legacy-compatible context.

4) API, IPN, cron, root, fallback
- Routes:
  - API: laravel-app/routes/web.php:57-60
  - IPN: laravel-app/routes/web.php:62-64
  - Cron: laravel-app/routes/web.php:100-102
  - Root: laravel-app/routes/web.php:105-106
  - Fallback and /404: laravel-app/routes/web.php:104,108
- Execution path:
  - Controllers -> LegacyRuntimeService -> require root index.php.

5) Dynamic module assets
- Route: laravel-app/routes/web.php:95-98
- Controller: laravel-app/app/Http/Controllers/ModuleAssetController.php
- Source path: ../pp-content/pp-modules/*/assets

## Can You Delete All Raw PHP Code Now?

Short answer: No.

If you remove raw PHP now, these parts will break:
- Legacy bridge routes (API/IPN/cron/root/fallback and some admin post actions).
- Theme rendering for payment/payment-link/invoice pages (uses legacy helper + theme classes).
- Dynamic gateway/addon/theme metadata loading in several admin Blade pages.
- 2FA helper dependency under pp-media/sdk.

## Raw PHP Files/Folders Still in Active Use

## Definitely required now

- index.php
- pp-config.php
- pp-404.php
- app/Support/zp-functions.php
- pp-content/pp-include/pp-adapter.php
- pp-content/pp-modules/pp-gateways (gateway class.php files)
- pp-content/pp-modules/pp-themes (theme class.php files)
- pp-content/pp-modules/pp-addons (class metadata and addon assets/hooks)
- pp-media/sdk/GoogleAuthenticator.php

## Conditionally required (depends on runtime path/situation)

- pp-content/pp-admin (legacy admin pages may still be hit via legacy runtime routes/fallback)
- pp-content/pp-install/index.php (install/reinstall flow)
- pp-requirement.php and pp-maintenance.php (requirement/maintenance branches in root index.php)

## Probably safe to archive only after verification

- root-level migration notes and ad-hoc test scripts (non-runtime docs/tools).
- Any test helper scripts not referenced in deployment flow.

## Remaining Tasks (Migration Completion Backlog)

Priority P0
1) Introduce strict module slug validation
- Add central allow-list checks for gateway/theme/addon slug before include/require.
- Apply both ThemeService and legacy dispatch paths.

2) Reduce CSRF exclusions to minimum viable scope
- Keep only true webhook and unavoidable legacy endpoints excluded.
- Re-enable protection for routes that can safely use Laravel CSRF.

3) Add route-level integration tests for hybrid critical paths
- payment-link default submit -> payment redirect -> gateway selection.
- payment/{ref}?gateway={id} for all active gateway categories.
- invoice show and invoice webhook behavior.

Priority P1
4) Split LegacyRuntimeService logs into debug level or behind env flag
- Keep incident-grade logs only on warn/error.

5) Migrate API hot paths from legacy dispatcher
- Replace ApiController::handle bridge logic for checkout and verify-payment with native service methods.

6) Migrate invoice webhook from legacy dispatcher
- Move webhook handling into Laravel-native service layer and keep gateway-specific adapters.

Priority P2
7) Minimize direct superglobal writes
- Encapsulate request context adapter object for legacy expectations.

8) Add migration safety matrix
- Per-route source of truth (native vs legacy) and required filesystem dependencies.

## Recommended Optimizations

1) Cache module metadata and options lookups
- ThemeService currently calls get_env per field; cache per request and optionally short-term cache per brand/theme.

2) Add opcache-friendly include strategy
- Centralized module loader with validated, normalized absolute paths.

3) Strengthen asset serving
- Extend MIME map and add ETag/Last-Modified for module assets in ModuleAssetController.

4) Add health checks for legacy dependencies at boot
- Verify required legacy files are present and readable, fail fast with actionable message.

5) Improve test harness for hybrid runtime
- Feature tests should use seeded MySQL test DB (or sqlite schema emulation for pp_* tables) instead of default ExampleTest assumptions.

## Validation Performed for This Audit

- Route map captured with php artisan route:list.
- Runtime log scan reviewed from laravel.log.
- Static diagnostics collected via workspace error scan.
- php artisan test executed; failure reproduced and documented.
- Dependency tracing done through controller/service code and include paths.
- Filesystem checks run for legacy public folders and module class counts.

## Migration Readiness to Delete Raw PHP (Decision Gate)

Do not delete raw PHP until all are true:

- No route uses LegacyRuntimeService for live traffic.
- ThemeService no longer depends on pp-functions.php or raw theme class files.
- Gateway integrations are fully native adapters.
- Admin pages no longer load or inspect raw module class files.
- 2FA helper migrated to composer package or native library.
- End-to-end tests pass without requiring root index.php.

Current status: Not ready for raw PHP deletion.
