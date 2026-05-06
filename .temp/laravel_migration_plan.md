# PipraPay Raw PHP to Laravel Migration Plan

Goal: build a complete Laravel clone of PipraPay in `/laravel-app` with identical behavior, routes, responses, UI, and business logic.

## Non-Negotiable Rules
- [ ] Keep all business logic exactly as-is
- [ ] Do not optimize/refactor logic unless required by Laravel runtime
- [ ] Keep UI/UX, HTML structure, CSS classes, and JS behavior unchanged
- [ ] Preserve route flow and output behavior exactly
- [ ] Use Laravel architecture (MVC, services, Blade)
- [ ] Preserve database schema exactly as in SQL dump
- [ ] Convert DB access to Eloquent or Query Builder without behavior changes
- [ ] Keep authentication compatible with existing password/session behavior

## Phase Checklist (Mandatory Execution Order)

### Phase 1: Laravel Setup
- [x] Create fresh Laravel app in `/laravel-app`
- [x] Configure `.env` to use SQLite temporarily
- [x] Create SQLite file and verify Laravel boots/runs
- [x] Document Phase 1 completion status

### Phase 2: Database Integration
- [x] Analyze `demo.sql` fully (tables, columns, keys, indexes, defaults)
- [x] Mirror schema in Laravel migrations without structural changes
- [x] Create Eloquent models mapped to each table
- [x] Disable timestamps on models where table has no Laravel timestamps
- [x] Validate migration parity against SQL dump

### Phase 3: View Migration
- [x] Move current UI files into `resources/views`
- [x] Convert PHP views to Blade views (`.blade.php`)
- [x] Replace include/require with Blade includes/layouts
- [x] Keep HTML markup and asset references behavior-identical

### Phase 4: Routing System
- [x] Inventory all existing URL entry points and query-based routing
- [x] Map raw routes to Laravel web/api routes
- [x] Preserve route names/paths/flow behavior exactly
- [x] Validate redirects and fallback handling parity

### Phase 5: Controller Migration
- [x] Convert functional PHP endpoints into controllers
- [x] Keep validation, branching, and output logic identical
- [x] Keep response payload/message/status behavior unchanged
- [x] Validate each migrated controller against original behavior

### Phase 6: Database Queries Conversion
- [x] Convert raw SQL calls to Eloquent/Query Builder
- [x] Preserve where/order/group/limit semantics exactly
- [x] Preserve transactional behavior and locking semantics
- [x] Verify query results match original code paths

### Phase 7: Authentication and Session
- [x] Recreate current auth flow in Laravel guard/session pipeline
- [x] Ensure existing password hash compatibility
- [x] Preserve login/logout/session timeout behavior
- [x] Preserve 2FA and auth-related checks exactly

### Phase 8: Business Logic Isolation
- [x] Move complex routines into service classes
- [x] Keep method behavior logic-identical to raw PHP
- [x] Keep side effects/order of operations unchanged
- [x] Wire controllers to services without changing outcomes

### Phase 9: Full System Validation
- [x] Validate all payment flows
- [x] Validate wallet and balance workflows
- [x] Validate transactions and reporting flows
- [x] Validate admin flows and permissions
- [x] Validate APIs and webhook behavior
- [x] Confirm 1:1 functional parity checklist signoff

## Execution Protocol
- Work phase-by-phase in order only
- Stop after each phase and report what was completed
- Continue only after user confirmation
- If any logic is ambiguous, pause and ask before implementing