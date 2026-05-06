# Legacy Deletion Readiness Checklist

Date: 2026-04-20
Owner: Migration Track

This checklist must be fully green before deleting raw PHP runtime files.

## 1) Route Dependency

- [ ] No production route calls LegacyRuntimeService.
- [ ] /api/* fully native.
- [ ] /ipn/* fully native.
- [ ] /invoice/webhook fully native (toggle on + legacy off in staging burn-in).
- [ ] /cron/* fully native.
- [ ] Root fallback no longer depends on root index.php.

## 2) Public Payment Flow

- [ ] /payment/{ref} fully native rendering stack.
- [ ] /payment-link/* fully native rendering stack.
- [ ] Theme rendering no longer requires pp-functions.php globals.
- [ ] Gateway selection and callback flow parity verified.

## 3) Admin Flow

- [ ] All critical admin POST actions native and tested.
- [ ] Unknown action fallback usage rate is zero in logs for 14 days.
- [ ] Admin pages no longer dynamically require raw module class files from root pp-content.

## 4) Module System

- [ ] Theme/gateway/addon loading moved to validated Laravel-managed module loader.
- [ ] No runtime include/require from raw pp-content paths.
- [ ] Asset serving no longer depends on root pp-content file tree.

## 5) Security Controls

- [ ] CSRF exclusions reduced to minimum required webhook routes only.
- [ ] Module slug validation enforced everywhere.
- [ ] API auth and scope checks parity-tested against legacy behavior.

## 6) Data + Test Confidence

- [ ] End-to-end tests cover payment-link submit -> checkout -> gateway verify path.
- [ ] Invoice status webhook updates covered by automated tests.
- [ ] API verify-payment native response parity snapshots approved.
- [ ] Staging burn-in period completed with no blocker incidents.

## 7) Cutover Plan

- [ ] Feature toggles default switched to native paths in staging.
- [ ] Rollback plan documented and tested.
- [ ] Maintenance window and backup plan approved.

## 8) Deletion Scope (Only after all above pass)

Candidates to delete/archive:
- root index.php legacy routing branches
- pp-content/pp-include runtime boot chain used only by legacy path
- raw php admin/runtime pages no longer referenced

Must keep until verified:
- any path still loaded by LegacyRuntimeService
- any module file loaded by theme/gateway legacy adapters

## Current Snapshot

- Native toggles added for starter paths:
  - PIPRAPAY_NATIVE_API_CHECKOUT_ENABLED
  - PIPRAPAY_NATIVE_API_VERIFY_PAYMENT_ENABLED
  - PIPRAPAY_NATIVE_INVOICE_WEBHOOK_ENABLED
  - PIPRAPAY_NATIVE_ADMIN_ACTIONS_ENABLED
- Legacy fallback preserved by default.
- Not deletion-ready yet.
- Added automated payment-link submit -> redirect -> gateway checkout contract test (fixture payload + route assertions).

## Automation

- Readiness reporter command (Laravel app):
  - php artisan piprapay:migration-readiness-report
  - php artisan piprapay:migration-readiness-report --format=json
  - php artisan piprapay:migration-readiness-report --write
- Export location for --write:
  - laravel-app/storage/app/reports/migration-readiness-report-YYYYmmdd_HHMMSS.md
- Admin legacy fallback event tracker:
  - laravel-app/storage/app/reports/admin-legacy-fallback.ndjson
- Readiness JSON metric for 14-day gate:
  - summary.admin_unknown_action_fallback_14d_count
  - check id: admin_flow.unknown_action_fallback_zero_14d
- CI workflow enforcement:
  - .github/workflows/migration-readiness-gate.yml
  - laravel-app/scripts/ci/enforce_readiness_gate.php
  - laravel-app/docs/MIGRATION_READINESS_CI_STAGING.md
