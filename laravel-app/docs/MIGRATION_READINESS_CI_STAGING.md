# Migration Readiness CI + Staging Burn-In

Date: 2026-04-20
Owner: Migration Track

## Objective

Continuously enforce the legacy deletion precondition:

- admin unknown-action legacy fallback count in the last 14 days must be zero.

## CI Workflow

Path:

- .github/workflows/migration-readiness-gate.yml

What it does:

1. Installs dependencies for laravel-app.
2. Runs migration safety feature tests.
3. Generates readiness JSON and markdown report artifacts.
4. Enforces 14-day fallback gate using:
   - laravel-app/scripts/ci/enforce_readiness_gate.php
5. Uploads report artifacts.

Gate condition:

- summary.admin_unknown_action_fallback_14d_count == 0
- check id admin_flow.unknown_action_fallback_zero_14d must pass.

## Local Validation Commands

Run from laravel-app:

1. php artisan test tests/Feature/AdminActionsMigrationToggleTest.php tests/Feature/MigrationReadinessReportCommandTest.php tests/Feature/PaymentLinkSubmitRedirectFlowTest.php tests/Feature/InvoiceWebhookMigrationToggleTest.php tests/Feature/ApiVerifyPaymentMigrationToggleTest.php tests/Feature/ApiMigrationToggleTest.php tests/Feature/HybridRouteWiringTest.php tests/Feature/ExampleTest.php
2. php artisan piprapay:migration-readiness-report --format=json > storage/app/reports/migration-readiness-report.json
3. php scripts/ci/enforce_readiness_gate.php storage/app/reports/migration-readiness-report.json

## Staging Burn-In Procedure (14 days)

1. Deploy with current migration toggles.
2. Keep readiness workflow enabled (push + scheduled + manual dispatch).
3. Trigger report generation daily.
4. Archive generated artifacts from CI for audit trail.
5. Do not approve deletion/cutover until the gate remains green for full 14 days.

## Notes

- Fallback event file used by the metric:
  - laravel-app/storage/app/reports/admin-legacy-fallback.ndjson
- Readiness report JSON output:
  - laravel-app/storage/app/reports/migration-readiness-report.json
