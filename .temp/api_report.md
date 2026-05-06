# API & Laravel Migration Validation Report

Date: 2026-04-20
Workspace: PipraPay-main
Target: Laravel side vs legacy raw PHP parity, API behavior, and security posture

## 1) Executive Summary

- Laravel-side test suite is green: **23 passed, 93 assertions**.
- Core API and payment surfaces are currently **hybrid/legacy-dependent**, not fully native.
- Live API probes were executed with:
  - no API key
  - invalid API key
  - valid API key
- API authentication gates are working for tested paths.
- Positive verify-payment path works with a valid key and existing transaction reference.
- Post-audit hardening was implemented with backward-compatible toggles:
  - API rate limiting middleware (per API key/IP)
  - optional strict API method enforcement (POST-only for verify-payment)
  - optional strict CSRF scope mode (webhook/IPN-focused)
- System is **not yet fully parity-proven** against all raw PHP features/endpoints because substantial runtime still delegates through legacy dispatch.

## 2) Scope and Method

### Automated validation
- Ran full Laravel tests: `php artisan test`
- Generated readiness report and evaluated gate data.
- Reviewed route map and key controller/middleware logic.

### Live API probes (HTTP)
- Server started on `http://127.0.0.1:8090`
- Tested endpoints with and without keys:
  - `GET /api/checkout/health`
  - `POST /api/verify-payment`
  - `GET /api/verify-payment`
- Used one active DB API key (masked in this report).

## 3) Automated Test Results

- Result: **PASS**
- Command: `php artisan test`
- Summary: **23 passed (93 assertions)**

Passing feature tests include:
- Admin action migration toggles and fallback observability
- API migration toggles
- Verify-payment migration toggles
- Invoice webhook migration toggles
- Hybrid route wiring
- Readiness report command
- Payment-link submit -> redirect -> checkout contract flow

## 4) Live API Execution Matrix

## 4.1 Without API key / invalid key

1. `GET /api/checkout/health` (no key)
- HTTP: 400
- Response: `INVALID_API_KEY`

2. `POST /api/verify-payment` (no key, JSON body)
- HTTP: 400
- Response: `INVALID_API_KEY`

3. `POST /api/verify-payment` (invalid key)
- HTTP: 400
- Response: `INVALID_API_KEY`

4. `POST /api/verify-payment` (invalid key, invalid JSON)
- HTTP: 400
- Response: `INVALID_API_KEY`

## 4.2 With valid API key

Active key was pulled from local DB and masked in logs (example mask style: `cc111f...9a8114`).

5. `POST /api/verify-payment` (valid key, `{}`)
- HTTP: 400
- Response: `INVALID_JSON_PAYLOAD`

6. `POST /api/verify-payment` (valid key, nonexistent `pp_id`)
- HTTP: 400
- Response: `INVALID_PP_ID`

7. `POST /api/verify-payment` (valid key, real transaction ref)
- HTTP: 200
- Response: transaction payload returned (pp_id, customer info, amount, status, date)

8. `GET /api/verify-payment` (valid key, no body)
- HTTP: 400
- Response: `INVALID_JSON_PAYLOAD`

9. `POST /api/verify-payment` (valid key, form body/non-JSON)
- HTTP: 400
- Response: `INVALID_JSON_PAYLOAD`

## 4.3 Behavioral conclusion from live probes

- Authentication and payload checks are active and functioning for tested cases.
- Positive verify-payment path is operational with valid credentials and existing data.
- Endpoint behavior indicates significant logic still legacy-driven under current toggle defaults.

## 5) Migration/Parity Status vs Raw PHP

Based on readiness report output:
- in_scope_routes: 18
- fully_native_routes: 0
- toggle_gated_routes: 7
- legacy_bound_routes: 18

Interpretation:
- The system is stable in hybrid mode.
- It is **not yet fully migrated** and cannot be declared 100% parity-complete for every raw PHP flow by native Laravel logic.
- Current strategy is controlled migration with safe legacy fallback.

## 6) Security & Reliability Findings (Professional Review)

### Status Update (after remediation)

- Implemented now:
  - API rate limiter via `throttle:piprapay_api`
  - strict API method toggle (`PIPRAPAY_STRICT_API_METHODS_ENABLED`)
  - strict CSRF scope toggle (`PIPRAPAY_STRICT_CSRF_SCOPE_ENABLED`)
- These controls are intentionally toggle-based for safe migration rollout.

## High Priority

1. Broad CSRF exclusions still active by default (compatibility mode)
- File: `laravel-app/bootstrap/app.php`
- Risk: broad request-forgery exposure on cookie-authenticated routes, especially admin/session flows.
- Current mitigation: strict mode implemented behind `PIPRAPAY_STRICT_CSRF_SCOPE_ENABLED`.
- Recommendation: enable strict mode in staging, validate flows, then promote to production.

2. API route still allows GET/POST at route level
- File: `laravel-app/routes/web.php`
- Risk: broader method surface than required.
- Current mitigation: verify-payment can reject non-POST when `PIPRAPAY_STRICT_API_METHODS_ENABLED=true`.
- Recommendation: once migration risk is lower, constrain route methods natively per endpoint.

## Medium Priority

3. API key lookup appears plaintext-match based
- File: `laravel-app/app/Http/Controllers/Api/ApiController.php`
- Risk: if DB is exposed, raw key reuse becomes immediate.
- Recommendation: migrate to hashed API keys + key prefix lookup + constant-time verification.

4. Debug instrumentation visible in live API response
- Evidence: `phpdebugbar-id` response header seen during live probe.
- Risk: response metadata leakage and unnecessary overhead in non-dev environments.
- Recommendation: ensure APP_DEBUG=false and debugbar disabled outside local.

5. Rate limiting introduced, but policy tuning is still needed
- Files: `laravel-app/app/Providers/AppServiceProvider.php`, `laravel-app/config/piprapay.php`, `laravel-app/routes/web.php`
- Risk: limits may be too strict/lenient depending on merchant traffic.
- Recommendation: baseline in staging and tune `PIPRAPAY_API_RATE_LIMIT_PER_MINUTE`.

## Low Priority

6. Validation message typo and ambiguity
- File: `laravel-app/app/Http/Controllers/Api/ApiController.php` (lines 137-152)
- Message: `A valid bp id is required.`
- Recommendation: normalize to `pp_id` consistently for API consumers.

## 7) What Is Working Well

- Critical migration tests are comprehensive and currently green.
- Legacy fallback controls and toggles are implemented and tested.
- Readiness automation and gate logic exist, including admin unknown-action fallback observability.
- API rejects invalid/missing credentials in tested scenarios.

## 8) Gaps Before Claiming Full Parity/Full Native

- API, IPN, cron, and major payment/invoice flows are still legacy-bound by design under current toggles.
- End-to-end parity across all raw PHP business branches (including module-specific edge paths) is not fully provable yet.
- Full production-hardening of middleware boundaries (CSRF/rate-limit/API middleware split) remains pending.

## 9) Immediate Action Plan

1. Enable `PIPRAPAY_STRICT_CSRF_SCOPE_ENABLED=true` in staging and run full admin/payment smoke tests.
2. Enable `PIPRAPAY_STRICT_API_METHODS_ENABLED=true` in staging and verify API client compatibility.
3. Tune `PIPRAPAY_API_RATE_LIMIT_PER_MINUTE` using real traffic telemetry.
4. Start API key hashing migration path (backward compatible rollout).
5. Continue staged migration of legacy-bound routes while keeping fallback controls.

## 10) Final Answer to User Question

- Are all features and raw-PHP logics fully and properly implemented in Laravel? **No, not fully yet.**
- Is the current hybrid system working for tested paths? **Yes**, based on passing test suite and live API probes.
- Is it safe as-is? **Partially**. Core auth checks work, but there are medium/high hardening items to address before claiming production-grade migration completeness.
