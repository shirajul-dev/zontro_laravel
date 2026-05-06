# PipraPay Phased Migration Plan (Legacy-First Safe Path)

Date: 2026-04-20
Strategy: Keep legacy helper/runtime active by default and migrate in controlled phases. If any phase causes instability, fall back to the current hybrid mode immediately.

## Migration Policy

- Default mode remains hybrid (Laravel + legacy root helpers/runtime).
- No raw PHP deletion until all phase exit criteria are met.
- Every phase must be reversible.
- Performance/security hardening starts first, feature rewrites later.

## Phase 1: Hardening Without Behavior Change (Start Now)

Goal: Reduce risk while keeping current runtime behavior.

Tasks:
- Add safe validation for dynamic module slugs (theme/gateway/addon asset paths).
- Add config-driven verbosity for LegacyRuntimeService logs.
- Keep existing helper calls and legacy include flow unchanged.

Exit criteria:
- Payment, payment-link, invoice, admin pages still render as before.
- No new runtime errors.
- Slug/path traversal attempts are blocked.

## Phase 2: Route Safety and Test Baseline

Goal: Build confidence before major rewrites.

Tasks:
- Add integration tests for:
  - payment-link default submit -> redirect -> payment gateway selection
  - payment/{ref}?gateway={id}
  - invoice page and webhook bridge route
- Split legacy-dependent tests from pure Laravel tests.
- Add a test bootstrap strategy for pp_ tables (seeded DB or test fixture DB).

Exit criteria:
- Critical hybrid flows have passing tests.
- CI no longer fails on default example assumptions.

## Phase 3: Native Migration of API Hot Paths

Goal: Replace highest-risk legacy-dispatched endpoints with Laravel services.

Tasks:
- Migrate /api checkout and verify-payment logic from root index.php into Laravel service classes.
- Keep legacy route available behind fallback toggle until parity is proven.

Exit criteria:
- API routes can run native path with parity checks.

## Phase 4: Native Migration of IPN and Invoice Webhook

Goal: Remove payment notification dependence on full legacy dispatch.

Tasks:
- Move gateway callback orchestration to Laravel service layer.
- Keep gateway-specific adapters but run through typed contracts.

Exit criteria:
- ipn/* and invoice/webhook no longer need LegacyRuntimeService.

## Phase 5: Admin Legacy Action Shrink

Goal: Reduce legacy dispatch usage in admin POST actions.

Tasks:
- Move top-used actions from legacy dispatch to native action handlers.
- Keep unported actions routed to legacy until complete.

Exit criteria:
- Most admin POST flows native, legacy bridge used only for uncommon paths.

## Phase 6: Legacy Deletion Readiness

Goal: Determine safe deletion scope.

Tasks:
- Build definitive no-legacy dependency report.
- Remove dead routes and compatibility shims.
- Archive and remove raw PHP folders only after staged validation.

Exit criteria:
- No production route requires root index.php or raw pp-content runtime.
- Full regression checklist passes.

## Rollback Rules (Important)

- If any phase introduces payment or admin instability:
  - revert only that phase's code changes
  - keep legacy bridge active
  - continue from last stable phase

## Current Status

- Phase 1: In Progress
  - Completed: strict slug validation guard in Laravel ThemeService and module asset route.
  - Completed: LegacyRuntimeService verbose logging toggle (default quiet).
  - Completed: same guard pattern applied to admin dynamic include points (gateway/addon/theme setting views).
- Phase 2: In Progress
  - Completed: critical hybrid route wiring tests for payment/payment-link/invoice and invoice webhook bridge.
  - Completed: test baseline fixed to avoid root legacy DB dependency in default example test.
  - Completed: E2E-like payment-link submit -> redirect -> gateway checkout contract test with fixture payload and route-level parity assertions.
  - Pending: extend this flow to DB-backed fixtures (transaction/payment_link persistence checks) when native submit path is introduced.
- Phase 3: In Progress (Starter)
  - Completed: native API starter path for checkout/health behind feature toggle with legacy fallback unchanged.
  - Completed: env/config toggles added for migration controls and logging.
  - Completed: first native business API operation implemented (verify-payment) behind toggle.
  - Completed: parity-oriented tests for native verify-payment success, scope error, and fallback behavior.
- Phase 4: In Progress (Starter)
  - Completed: native invoice webhook starter path added behind feature toggle.
  - Completed: webhook fallback remains legacy when toggle is disabled.
  - Completed: tests added for fallback, native success update, and invalid JSON handling.
- Phase 5: In Progress (Starter)
  - Completed: admin action handler now supports native/legacy cutover toggle.
  - Completed: tests added for admin action native mode and fallback mode.
- Phase 6: In Progress (Starter)
  - Completed: legacy deletion readiness checklist created.
  - Completed: automated readiness checks + route-by-route native/legacy usage report via Artisan command.
  - Completed: admin legacy fallback observability tracker added with 14-day unknown-action metric in readiness report.
  - Completed: tests for unknown-action fallback recording and metric exposure in report output.
  - Completed: CI workflow gate added to enforce zero unknown-action fallback in last 14 days.
  - Completed: staging burn-in runbook created for 14-day evidence collection.
  - Command: php artisan piprapay:migration-readiness-report
  - JSON mode: php artisan piprapay:migration-readiness-report --format=json
  - Export markdown: php artisan piprapay:migration-readiness-report --write
  - CI workflow: .github/workflows/migration-readiness-gate.yml
  - CI gate script: laravel-app/scripts/ci/enforce_readiness_gate.php
  - Staging runbook: laravel-app/docs/MIGRATION_READINESS_CI_STAGING.md
  - Next: begin 14-day staging burn-in and collect daily green artifacts before any deletion approval.
