# Laravel Integration Handoff

Last updated: 2026-04-17
Project: PipraPay (Raw PHP to Laravel migration)

## 1) Requirement Summary

Primary requirement:
- Fully separate the app from raw runtime routing and move to Laravel-native routing/rendering.
- Keep existing UI/UX and behavior parity while migrating.
- Preserve compatibility with existing cookies/session style used by legacy admin flow.
- Remove raw dispatcher dependency from admin pages step-by-step, not by breaking functionality.

Secondary requirement:
- Maintain functionality during migration (auth, dashboard, admin shell/sidebar/assets, actions).
- Keep legacy helpers available only as compatibility layer, without function redeclaration conflicts.

## 2) Migration Strategy Being Used

This migration is done as a bridge-to-native pattern:

1. Keep legacy assets/views, but render through Laravel.
2. Replace route families incrementally (auth GET, auth POST, admin POST, admin GET).
3. Recreate required legacy global helper functions in Laravel helper layer.
4. Resolve view variable contract differences centrally in controller view data.
5. Keep old runtime only for non-admin/non-migrated flows until full replacement.

Key rule:
- Avoid mass rewrite of Blade pages unless unavoidable.
- Prefer central compatibility fixes (controller data + helper functions + bootstrap flags).

## 3) Current Architecture Context

Codebase has two systems:
- Raw app root: main project root with original php runtime.
- Laravel app: laravel-app folder acting as migration target.

Current admin rendering design:
- GET /admin and GET /admin/* now route to Laravel native controller.
- Admin shell is rendered by Laravel.
- Content panels are loaded via content=1 query mode.
- JS in shell fetches same Laravel admin URL with content=1 for partial content rendering.

## 4) Important Files and Their Roles

Routing and controllers:
- laravel-app/routes/web.php
  - Native auth routes (login/forgot/2fa GET+POST)
  - Native admin GET routes including wildcard admin page resolver
  - Native admin POST action routes
- laravel-app/app/Http/Controllers/Admin/NativeAdminPageController.php
  - Admin shell rendering
  - content=1 partial rendering
  - page_name -> view resolver for pp-root views
  - injects legacy-like globals and view data
- laravel-app/app/Http/Controllers/Admin/NativeAdminActionController.php
  - handles admin POST actions
- laravel-app/app/Http/Controllers/Auth/NativeAdminAuthController.php
  - native login/forgot/2fa POST handling
- laravel-app/app/Http/Controllers/Auth/NativeAuthPageController.php
  - native auth GET pages

Compatibility layer:
- laravel-app/app/Support/helpers.php
  - Laravel-native legacy helper implementations
- laravel-app/bootstrap/app.php
  - loads helper file early
  - defines PipraPay_INIT compatibility constant for legacy guard checks

Legacy helper collision protection:
- app/Support/zp-functions.php
  - now guarded with function_exists wrappers for overlapping helper names

Shell and page fragments:
- laravel-app/resources/views/legacy/pp-content/pp-admin/index.blade.php
  - shell/sidebar/assets/JS loader
- laravel-app/resources/views/legacy/pp-content/pp-admin/pp-root/*.blade.php
  - admin content fragments being rendered via content=1

## 5) What Has Been Completed

### Auth and session flow
- Native GET pages for:
  - /login
  - /forgot
  - /2fa
- Native POST handlers for auth actions are active.

### Admin shell and routing
- Native admin shell renders from Laravel.
- Native wildcard GET routing for /admin/{page_name?} is active.
- Legacy admin GET dispatcher route has been removed from active GET handling.

### Content loading pattern
- Shell JS now fetches the same admin URL with content=1 instead of posting to raw root dispatcher.
- Direct admin URLs render shell, content endpoints render fragment.

### Helper compatibility
- Added Laravel helper implementations for critical legacy functions.
- Added collision guards in legacy pp-functions.php to prevent FatalError redeclarations.
- Resolved known collisions including:
  - timeAgo
  - getData
  - get_env
  - canAccessPage
  - hasPermission
  - getNameChars
  - convertUTCtoUserTZ
  - pp_parse_sql_segments
  - getCurrentDatetime

### Legacy guard compatibility
- PipraPay_INIT constant defined in Laravel bootstrap to satisfy guarded legacy Blade fragments.

### Variable and CSRF compatibility
- Added both csrfToken and csrf_token in view payload.
- Added version payload for footer/version references.
- CSRF is fully managed by Laravel natively (`VerifyCsrfToken` and view payload), older PHP `bin2hex` implementations have been removed to prevent token staleness/mismatches across POST requests handled by fallback dispatchers.

## 6) Current State (Functional Snapshot)

Verified working examples:
- /admin/dashboard shell and content pattern works.
- /admin/gateways route works through native shell.
- /admin/brands/create?content=1 returns 200 after CSRF alias fix.

Current status of pp-root route sweep (content=1):
- Many pages already 200.
- Remaining failures were traced to missing helper functions or missing legacy variables in view data.

Known failure classes found in logs:
1. Missing helper functions in Laravel helper layer:
   - getParam
   - senderWhitelist
   - permissionSchema
2. Missing legacy variables in NativeAdminPageController payload:
   - global_brand_currency_code
   - global_brand_currency_symbol
   - path_invoice
   - path_payment_link
3. Edit pages depending on params query parsing (getParam) currently fail until helper parity is complete.

## 7) Integration Pattern (Do Not Change Without Reason)

For admin GET pages:
1. Route all /admin/* GET to NativeAdminPageController.
2. If content=1:
   - resolve target fragment view in pp-root
   - return only content fragment
3. If content is absent:
   - return shell index view
4. Keep shell JS navigation on same URLs and only append content=1 for fetch.

For admin POST pages:
- Keep NativeAdminActionController as central action endpoint.
- Keep existing form action payload compatibility (action keys, csrf token name).

For helper compatibility:
- Prefer implementing helper in laravel-app/app/Support/helpers.php.
- Keep legacy pp-functions.php guarded with function_exists to avoid redeclarations.

For legacy view variables:
- Inject compatibility variables from NativeAdminPageController viewData().
- Avoid touching dozens of Blade files if one controller payload can solve it.

## 8) Remaining Work

High priority (parity blockers):
1. Add missing helper functions into Laravel helper layer:
   - getParam
   - senderWhitelist
   - permissionSchema
2. Add missing legacy view vars into NativeAdminPageController view payload:
   - global_brand_currency_code
   - global_brand_currency_symbol
   - path_invoice
   - path_payment_link
   - (and any additional path_* variables required by logs)
3. Re-run full pp-root content=1 sweep and close all 500s.
4. Verify each shell-linked navigation item loads correctly.

Medium priority:
1. Validate edit pages with query params:
   - brands/edit
   - gateways/edit
   - invoice/edit
   - payment-link/edit
   - staff-management/* edit/permission pages
2. Validate SMS/device pages that depend on senderWhitelist.

Low priority / future:
1. Replace remaining non-admin legacy dispatchers (payment/invoice/public module flows) with Laravel-native controllers.
2. Reduce use of global arrays and move toward service/view-model pattern.
3. Add automated feature tests for admin content route matrix.

## 9) Suggested Next Execution Order

1. Implement missing helper functions in laravel-app/app/Support/helpers.php (copy behavior from legacy where needed).
2. Expand NativeAdminPageController viewData() with missing currency/path variables.
3. Run admin content sweep for all pp-root routes and collect failures.
4. Fix residual page-specific missing vars only if central fix cannot cover.
5. Add/update migration note after each batch.

## 10) Quick Validation Commands

Use these to verify progress quickly:

1) Auth + sweep content pages from pp-root
- Login with demo admin cookie jar
- Iterate all pp-root blade files
- Call /admin/{route}?content=1
- Record status codes

2) Route sanity
- php artisan route:list --path=admin --except-vendor

3) Latest failure tail
- tail -n 40 laravel-app/storage/logs/laravel.log

## 11) Current Ground Rules for Continuation

- Keep migration incremental and functional first.
- Prefer central compatibility over mass file edits.
- Do not reintroduce raw admin dispatcher for GET routes.
- Keep shell + content=1 pattern as canonical admin rendering path.
- Keep helper collision guards in legacy file while mixed mode exists.

## 12) Current Objective for Next AI

Continue from this exact state and complete admin parity until all pp-root routes return 200 in content mode and are navigable through shell, then proceed to migrate remaining non-admin legacy dispatch surfaces.
