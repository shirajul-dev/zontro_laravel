# Flow-by-Flow SaaS Migration Guide (Foundation First)

Project: PipraPay / ZontroPay  
Date: 12 May 2026  
Scope: Phase 1 foundation only (SuperAdmin separation + manual merchant onboarding from SuperAdmin)

## 1) Goal of This Document

This guide is intentionally limited to the first migration flow only.

What we will do now:
- Create a fully separate SuperAdmin system using Laravel standard structure.
- Keep existing merchant/admin product features, logic, and behavior unchanged.
- Add a SuperAdmin-only manual onboarding wizard to create a new merchant owner account.
- Make the newly created merchant owner able to log in and use the existing system as-is, but scoped to their own merchant/brand data.

What we will not do now:
- Full public self-signup flow.
- Full subscription billing engine.
- Full KYC review system.
- Full advanced multi-tenant feature gating.
- Full module rewrites.

This avoids high-risk big-bang migration and supports your preferred one-flow-at-a-time process.

## 2) Current-State Protection Rules (Must Not Break)

These rules are mandatory during this foundation phase:
- Existing merchant/admin flows must continue to work.
- Existing payment logic, gateway logic, and current modules must remain intact.
- Existing configuration behavior remains unchanged unless required for tenant safety.
- No destructive rewrite of existing controllers/views/services.
- Add isolation layers around the system, not deep replacement.

## 3) Target Foundation Architecture (Phase 1)

## 3.1 Personas
- SuperAdmin: platform owner, fully separate panel and auth.
- Merchant Owner: tenant-level owner account, created by SuperAdmin.
- Existing merchant-side admin/staff behavior: keep current behavior for now.

## 3.2 Boundary Rule
- SuperAdmin domain/area must be logically separate from merchant area.
- SuperAdmin can manage merchants globally.
- Merchant can only access own tenant data.

## 3.3 Recommended Laravel Standard Separation
- Separate route file: routes/superadmin.php
- Separate auth guard/provider for superadmin
- Separate middleware stack for superadmin routes
- Separate controller namespace/folder for superadmin
- Separate Blade layout stack for superadmin pages
- Separate permission namespace for superadmin actions

## 4) Database Foundation (Minimal, Safe)

Create only the minimum schema required for safe tenant foundation now.

## 4.1 New Core Tables (Phase 1)
- merchants (tenant root)
- merchant_profiles (optional split for business data)
- super_admins (or use users with role separation if already standardized)
- merchant_user_map (if current user model is shared and needs explicit mapping)

If your current schema already has merchant-like entities, reuse where possible and only add missing fields.

## 4.2 Minimum Columns Required

Merchants table (minimum):
- id
- uuid
- name
- slug
- status (draft, active, suspended)
- created_by_superadmin_id
- created_at, updated_at

Brand linkage (minimum for current ask):
- default_brand_id on merchants OR merchant_id on brands (preferred)

User linkage (minimum):
- users.merchant_id (or mapping table)
- users.role_type (owner/admin/staff or existing role model mapping)

## 4.3 Safe Additions on Existing Tables
Add merchant scoping key where needed for immediate safety on high-value tables first:
- transactions
- invoices
- payment_links
- gateways
- customers

Do this incrementally. Do not force every table in one release.

## 5) SuperAdmin Module (Fully Separate)

## 5.1 Route Design
Create routes/superadmin.php and register it in bootstrap/app.php or RouteServiceProvider based on your Laravel version.

Route groups:
- /superadmin/login
- /superadmin/logout
- /superadmin/dashboard
- /superadmin/merchants
- /superadmin/merchants/create (multi-step onboarding)
- /superadmin/merchants/{id}
- /superadmin/merchants/{id}/suspend
- /superadmin/merchants/{id}/reactivate

Middleware:
- guest:superadmin for login
- auth:superadmin for all protected routes
- permission checks for sensitive actions

## 5.2 Authentication Separation
Implement clean auth isolation:
- superadmin guard in config/auth.php
- superadmin provider/model
- dedicated login controller/service for superadmin
- dedicated password reset broker (recommended)
- dedicated session key namespace

## 5.3 SuperAdmin UI
Requirement from you:
- Use Metronic template for full SuperAdmin panel.

Recommended implementation approach:
- Keep merchant-side UI untouched.
- Build separate SuperAdmin frontend shell.
- Start with server-rendered blade + assets integration OR React app mounted under SuperAdmin area.
- If using React Vite Metronic starter, isolate build entry points for superadmin only.

## 6) Merchant Creation Wizard (Manual by SuperAdmin)

SuperAdmin creates merchant owner via a 3-step onboarding form.

## 6.1 Step 1: Brand and Business Setup
Collect:
- Brand name (required)
- Brand logo (optional)
- Brand website URL (required if policy says)
- Default currency (recommended)
- Timezone (recommended)
- Merchant business meta fields needed by platform

Creates/updates:
- merchant record (draft)
- default brand record (draft/active per policy)

## 6.2 Step 2: Owner Personal/Professional Info
Collect:
- Owner full name
- Phone
- Country
- Designation/profession
- Optional compliance-oriented profile fields

Creates/updates:
- owner profile data linked to merchant

## 6.3 Step 3: Merchant Admin Credentials
Collect:
- Username
- Email
- Password
- Password confirmation

Creates:
- merchant owner user account
- role assignment as merchant owner
- activation status

Final action:
- transactionally commit merchant + brand + owner user
- show success page with login instructions

## 6.4 Validation Rules (Baseline)
- Unique email globally
- Unique username by policy
- Strong password policy
- URL validation for website
- Safe file validation for logo upload

## 6.5 Post-Creation Hooks
After successful creation:
- send owner welcome email (optional now, recommended)
- log audit event (mandatory)
- create baseline merchant settings snapshot

## 7) Merchant Access Behavior (Now)

After creation, merchant owner should:
- login through current merchant login flow (or dedicated merchant auth route if already separate)
- access existing modules as currently implemented
- see only their own data by merchant scope

Important:
- Keep current features unchanged.
- Apply scoping guardrails in middleware/query layers to prevent cross-merchant leaks.

## 8) Tenant Isolation Strategy for This Phase

Use a practical, low-risk strategy first.

## 8.1 Request Context Resolver
Introduce middleware/service that resolves current merchant context from authenticated merchant user.

## 8.2 Query Safety Pattern
For merchant-facing data reads/writes:
- always include merchant scope condition
- enforce in repository/service/global scope where feasible

## 8.3 SuperAdmin Bypass
SuperAdmin routes can query globally but must remain in superadmin namespace only.

## 8.4 High-Risk Data Leak Tests (Mandatory)
Add feature tests that verify:
- merchant A cannot access merchant B transaction
- merchant A cannot access merchant B gateways
- merchant A cannot access merchant B customer records

## 9) Security and Compliance Baseline to Include Now

Include these now even in foundation phase:
- Dedicated guards for superadmin and merchant users.
- CSRF on all forms.
- Rate limit login endpoints.
- 2FA-ready hooks for superadmin login (can be feature-flagged now).
- Audit logs for merchant creation, suspend/reactivate, credential reset.
- Authorization policies/gates for superadmin actions.
- Strict file upload validation and storage isolation for logos/documents.
- Prevent mass assignment risks with explicit fillable/DTO validation.

## 10) Metronic Integration Plan (SuperAdmin Only)

You said you will add Metronic React Vite starter in project root first.

Recommended sequence:
1. Place Metronic source in a dedicated frontend directory for superadmin.
2. Create separate Vite entry and build output for superadmin assets.
3. Integrate layout shell first (sidebar, header, auth pages).
4. Connect dashboard and merchant onboarding wizard screens.
5. Keep merchant-side theme unchanged.

Do not merge merchant and superadmin UI bundles in this step.

## 11) Flow-by-Flow Delivery Plan (Only Foundation)

## Flow 1: SuperAdmin Auth + Separate Dashboard
Deliverables:
- superadmin guard/provider
- superadmin login/logout
- superadmin dashboard route and page
- base layout with Metronic shell

Acceptance:
- superadmin can login and logout
- merchant/admin cannot enter superadmin pages

## Flow 2: SuperAdmin Merchant List + Detail
Deliverables:
- merchant listing
- merchant detail page
- status badges and timestamps

Acceptance:
- superadmin can view all merchants
- detail page opens correctly

## Flow 3: SuperAdmin 3-Step Merchant Creation Wizard
Deliverables:
- step 1 brand/business
- step 2 owner personal/professional
- step 3 credentials
- final transactional create

Acceptance:
- merchant + brand + owner user created successfully
- validation and error states handled
- audit record generated

## Flow 4: Merchant Login + Tenant Scope Guardrails
Deliverables:
- created owner can login
- owner sees current system features
- key modules filtered by merchant scope

Acceptance:
- no cross-merchant visibility in protected modules

## Flow 5: Suspend/Reactivate Merchant Controls
Deliverables:
- superadmin merchant status actions
- status-based access restriction at merchant side

Acceptance:
- suspended merchant blocked appropriately
- reactivated merchant restored

## 12) Suggested Folder Structure (Laravel Standard)

SuperAdmin backend:
- app/Http/Controllers/SuperAdmin
- app/Http/Requests/SuperAdmin
- app/Services/SuperAdmin
- app/Models/SuperAdmin (if separate model)
- resources/views/superadmin
- routes/superadmin.php

Merchant onboarding domain:
- app/Services/SuperAdmin/MerchantOnboardingService.php
- app/Actions/SuperAdmin/CreateMerchantAction.php (optional)
- app/DTOs/SuperAdmin/MerchantOnboardingData.php (optional)

## 13) Non-Functional Requirements for Foundation

- All create operations for merchant onboarding must be DB transaction wrapped.
- Add idempotency guard on create submit to prevent double create.
- Keep logs for each onboarding step state.
- Use migration rollback-safe patterns.
- Keep old routes untouched unless explicit redirect needed.

## 14) Open Decisions You Should Finalize Before Coding

1. SuperAdmin identity model:
- Separate table/model vs shared users table with role.

2. Merchant login endpoint:
- Keep existing login route or split to explicit merchant portal.

3. Brand ownership model:
- merchant_id direct on brands (recommended now).

4. Username policy:
- global unique or tenant-scoped unique.

5. Merchant activation policy:
- active immediately on create vs pending until review.

## 15) Immediate Build Checklist (Start Here)

1. Create routes/superadmin.php and wire bootstrap.
2. Implement superadmin guard/provider and login flow.
3. Build superadmin dashboard shell with Metronic.
4. Build merchant management list/detail pages.
5. Build 3-step merchant onboarding wizard.
6. Create merchant, default brand, owner user transactionally.
7. Add merchant scoping middleware and first critical query protections.
8. Add feature tests for auth boundary and tenant leakage.
9. Perform manual QA on each flow before moving next.

## 16) Manual QA Checklist for This Phase

- SuperAdmin login/logout works.
- SuperAdmin area fully separated from merchant area.
- Merchant can be created from 3-step wizard.
- Merchant owner credentials work.
- Merchant sees current system screens.
- Merchant cannot view other merchant data.
- Suspend/reactivate works correctly.
- Audit logs exist for all sensitive operations.

## 17) What Changed from the Big Blueprint for This Document

This guide intentionally narrows your larger blueprint to a practical foundation sprint:
- Prioritizes SuperAdmin separation first.
- Uses manual merchant creation before public registration.
- Keeps all existing merchant functionality unchanged.
- Delays advanced billing, full KYC lifecycle, and full feature gating.

This matches your requested implementation style: one complete flow, manual test, then continue.

## 18) Recommended Next Document After This One

After this foundation is implemented and manually verified, the next focused document should be:
- public merchant self-registration flow (wizard + verification basics)

Not before this phase passes QA.
