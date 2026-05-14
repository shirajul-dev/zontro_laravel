# PipraPay Laravel Migration Handoff

Date: 17 April 2026
Scope: Full raw PHP to Laravel migration phases 1 to 9 completed in-folder at laravel-app.

## 1. Run Commands

From project root:

- cd laravel-app
- composer install
- cp .env.example .env
- php artisan key:generate
- mkdir -p database
- touch database/database.sqlite
- php artisan migrate
- php artisan route:list --except-vendor
- php artisan serve

Optional quick runtime check:

- php -l app/Services/Legacy/LegacyAuthSessionService.php
- php artisan about

## 2. Critical Files Changed By Phase

### Phase 1: Laravel setup

- laravel-app/.env
- laravel-app/database/database.sqlite

### Phase 2: Database integration

- laravel-app/database/migrations/2026_04_17_000100_create_piprapay_schema.php
- laravel-app/app/Models/PpAddon.php
- laravel-app/app/Models/PpAddonParameter.php
- laravel-app/app/Models/PpAdmin.php
- laravel-app/app/Models/PpApi.php
- laravel-app/app/Models/PpBalanceVerification.php
- laravel-app/app/Models/PpBrand.php
- laravel-app/app/Models/PpBrowserLog.php
- laravel-app/app/Models/PpCurrency.php
- laravel-app/app/Models/PpCustomer.php
- laravel-app/app/Models/PpDevice.php
- laravel-app/app/Models/PpDomain.php
- laravel-app/app/Models/PpEnv.php
- laravel-app/app/Models/PpFaq.php
- laravel-app/app/Models/PpGateway.php
- laravel-app/app/Models/PpGatewayParameter.php
- laravel-app/app/Models/PpInvoice.php
- laravel-app/app/Models/PpInvoiceItem.php
- laravel-app/app/Models/PpPaymentLink.php
- laravel-app/app/Models/PpPaymentLinkField.php
- laravel-app/app/Models/PpPermission.php
- laravel-app/app/Models/PpSmsData.php
- laravel-app/app/Models/PpTransaction.php
- laravel-app/app/Models/PpWebhookLog.php

### Phase 3: View migration

- laravel-app/resources/views/legacy/ (all migrated legacy Blade view files)

### Phase 4: Routing system

- laravel-app/config/piprapay.php
- laravel-app/routes/web.php
- laravel-app/app/Http/Controllers/Legacy/LegacyRouteDispatchController.php

### Phase 5: Controller migration

- laravel-app/app/Http/Controllers/Legacy/LegacyRouteDispatchController.php
- laravel-app/resources/views/legacy/dispatch.blade.php

### Phase 6: Query conversion

- app/Support/zp-functions.php

### Phase 7: Authentication and session

- laravel-app/app/Models/PpAdmin.php
- laravel-app/config/auth.php
- laravel-app/app/Http/Middleware/SyncLegacyAdminSession.php
- laravel-app/bootstrap/app.php

### Phase 8: Business logic isolation

- laravel-app/app/Services/Legacy/LegacyRuntimeService.php
- laravel-app/app/Services/Legacy/LegacyAuthSessionService.php
- laravel-app/app/Http/Controllers/Legacy/LegacyRouteDispatchController.php
- laravel-app/app/Http/Middleware/SyncLegacyAdminSession.php

### Phase 9: Full system validation and parity middleware fix

- laravel-app/bootstrap/app.php
- laravel_migration_plan.md

## 3. Known Production Assumptions

- Current migrated environment is configured for SQLite for development bootstrap.
- Legacy runtime dispatch still executes legacy index.php from project root while running inside Laravel request lifecycle.
- Legacy cookies remain authoritative for admin login state:
  - pp_admin
  - pp_2fa
- Legacy browser session table pp_browser_log is still required for auth/session state reconciliation.
- Legacy CSRF and webhook/API behavior is preserved by excluding legacy endpoints from Laravel CSRF middleware.
- Existing password hashes from pp_admin are expected to stay bcrypt-compatible.
- Some third-party classes in legacy helper code (for example Imagick and FPDF references) remain environment dependent and may require corresponding PHP extensions/packages in production.
- Route path prefixes can be overridden through config/env mapping in laravel-app/config/piprapay.php.
- Functional parity currently relies on legacy runtime bridge services, not a fully native rewritten domain module set.

## 4. Final Validation Snapshot

- Route map compiled successfully.
- Core route smoke checks returned successful responses in isolated request runs for:
  - login, forgot, 2fa
  - admin route
  - payment, invoice, payment-link
  - api and ipn endpoints
  - homepageRedirect and fallback

## 5. Tracking File

- laravel_migration_plan.md includes all phases marked complete.
