# Non-SaaS to SaaS Conversion Blueprint

**Project:** PipraPay / ZontroPay Payment Platform  
**Current State:** Single-owner, single-admin, hybrid Laravel + legacy system  
**Target State:** Multi-tenant SaaS with one merchant account per tenant, superadmin control, plan-based access, merchant verification, and controlled rollout  
**Audience:** Founder, product owner, architect, lead developer, QA, operations  
**Purpose:** Define the full SaaS conversion model, user flows, features, menus, permissions, onboarding, verification, billing, and development plan without removing current features

---

## 1. Executive Summary

The current product behaves like a single-tenant payment platform. One central admin controls the entire system, and all business logic, gateway settings, brands, and transactions are managed in one operational context. The SaaS conversion should preserve the existing feature set while adding a tenancy layer so that each merchant gets an isolated workspace with its own brand, settings, users, limits, billing, and verification status.

The core idea is simple:
- The platform owner becomes the **SuperAdmin**.
- Each merchant becomes a **Tenant**.
- Each tenant gets one default **Brand** automatically on signup.
- Merchants may later add more brands if the plan allows.
- All operational features stay, but access is restricted by plan, role, and tenant scope.
- SuperAdmin keeps global control over plans, pricing, approvals, gateways, feature toggles, platform settings, and compliance.

This means the product should evolve from a single admin payment system into a proper payment SaaS where:
- Merchant self-registration is supported.
- Merchant identity and documents are verified.
- The merchant can only see their own data.
- SuperAdmin can see all merchants, all transactions, all plans, all gateways, all logs, and all support events.
- Free, trial, starter, pro, business, and enterprise plans can control features and usage.
- The current payment gateway and admin features remain functional, but are delivered inside tenant boundaries.

---

## 2. Business Goals

### Primary goals
- Turn the platform into a scalable SaaS product.
- Support merchant self-registration from the public website.
- Enforce tenant isolation so merchant data is separated.
- Add subscription and plan management.
- Add merchant KYC/document verification.
- Keep all current payment features and admin capabilities.
- Give SuperAdmin full control of platform-wide access.

### Secondary goals
- Improve onboarding and activation rate.
- Enable free trial or free plan to reduce signup friction.
- Make approval flows auditable.
- Add billing, invoicing, and plan upgrade paths.
- Make the system ready for future teams and support staff.
- Keep migration safe and incremental.

---

## 3. SaaS Operating Model

### Platform roles
The SaaS version should operate with the following top-level roles:
- **SuperAdmin**: Owns the SaaS platform.
- **Merchant Owner**: The main account owner for one tenant.
- **Merchant Admin**: Secondary admin for the merchant tenant.
- **Merchant Staff**: Operational user with limited permissions.
- **Support Agent**: Internal staff with restricted support access.
- **Finance/Compliance Reviewer**: Internal staff who review merchant verification and payouts.

### Tenant concept
A tenant is the merchant account boundary. A tenant owns:
- One default brand created at signup.
- Users belonging to that merchant.
- Gateway access and configuration.
- Transactions, invoices, and payment links.
- Webhooks, API keys, logs, and reports.
- Plan and billing state.
- Verification and compliance status.

### Multi-tenancy rule
Every record that belongs to a merchant must be scoped to the merchant tenant. This is the most important SaaS rule.

Examples:
- A merchant sees only their transactions.
- A merchant sees only their invoices.
- A merchant sees only their payment links.
- A merchant sees only their gateways.
- A merchant sees only their reports and logs.
- A merchant sees only their own staff and permissions.

SuperAdmin can see everything.

---

## 4. Current Product Capabilities To Preserve

The SaaS version should preserve the existing product capability map. The migration should not reduce functionality; it should wrap functionality in tenant and plan logic.

### Core product areas to preserve
- Authentication and session handling.
- Merchant/admin dashboard.
- Brand management.
- Payment gateways.
- Checkout flow.
- Payment links.
- Invoice payments.
- IPN/webhook handling.
- Transaction verification.
- Reports and analytics.
- Customer management.
- Domain management.
- API access.
- Cron/job automation.
- Addons and optional modules.
- System settings.
- Staff/permission management.
- Notification system.
- Logs and activities.

### Preserve behavior, change access model
The SaaS conversion does not mean rewriting every function. It means:
- Preserve the business logic.
- Add tenant scoping.
- Add plan gating.
- Add approval state checks.
- Add role-based access.
- Add superadmin override.

---

## 5. SaaS Data Model Design

The data model should be expanded from a single-admin model to a tenant-aware SaaS model.

### New top-level entities
- **tenants** or **merchants**: one record per merchant account.
- **merchant_users** or role-based user assignments.
- **plans**: pricing and feature definitions.
- **subscriptions**: plan assignment and lifecycle.
- **verification_requests**: onboarding/KYC review records.
- **documents**: uploaded proof files.
- **tenant_settings**: tenant-specific configuration.
- **usage_counters**: plan limits and usage tracking.
- **billing_invoices**: subscription billing history.
- **support_tickets**: merchant support issues.
- **audit_events**: global audit trail.

### Existing models to tenant-scope
Most existing tables should gain a tenant reference, directly or indirectly:
- Brand
- Transaction
- Invoice
- PaymentLink
- Gateway
- Customer
- Api
- Domain
- Device
- Addon usage
- Reports snapshots
- Logs
- Webhooks
- Notifications
- Staff records

### Tenant scoping strategies
There are three patterns:
1. Direct `tenant_id` on tenant-owned tables.
2. Indirect `brand_id` when brand is the tenant boundary.
3. Hybrid: tenant owns brands, brands own operational records.

For this project, the best approach is:
- **Tenant is the account boundary**.
- **Brand is the merchant-facing business identity inside the tenant**.
- Start with one default brand per tenant.
- For many flows, use `tenant_id` as the primary scoping key and keep `brand_id` for payment/business records.

This gives long-term SaaS clarity while still fitting the current structure.

---

## 6. Identity Model

### SuperAdmin identity
SuperAdmin is not a merchant.
SuperAdmin should have:
- Full platform access.
- Ability to manage all merchants.
- Ability to create/edit plans.
- Ability to set global gateway policy.
- Ability to review verifications.
- Ability to suspend or reactivate merchants.
- Ability to inspect audit logs.
- Ability to impersonate merchant support views if needed.

### Merchant identity
A merchant is a SaaS tenant owner or tenant operator.
A merchant should have:
- Tenant-bound login.
- Default brand.
- API credentials.
- Gateway settings.
- Verification flow.
- Subscription plan.
- Domain whitelisting.
- Transaction, invoice, and reporting access.

### Staff identity
Merchant staff should:
- Be scoped to one tenant.
- Have restricted access based on role.
- Be able to do operational tasks without plan administration.

---

## 7. Merchant Registration Flow

Merchant self-registration should be a first-class flow from the public website.

### Registration entry points
- Public landing page “Start Free”.
- Header “Create Merchant Account”.
- Pricing page CTA.
- Trial signup CTA.
- Referral/signup invite link if supported.

### Recommended registration steps
Use a guided multi-step flow.

#### Step 1: Basic account details
Collect:
- Full name.
- Business/merchant name.
- Email address.
- Phone number.
- Country.
- Password.
- Acceptance of terms and privacy policy.

State after submit:
- Create user record in pending/verification state.
- Send OTP/email verification.
- Create tenant shell.
- Create default brand shell.

#### Step 2: Email/phone verification
Verify one or more of:
- Email OTP.
- SMS OTP.
- Magic link.

State after verification:
- Activate the user account.
- Continue onboarding.

#### Step 3: Business profile
Collect:
- Legal business name.
- Trade name.
- Business category.
- Website URL.
- Business address.
- Country and city.
- Tax ID / trade license / incorporation number.
- Payout details summary.

#### Step 4: Default brand creation
Automatically create one default brand.
Fields:
- Brand name from merchant name or business name.
- Brand slug.
- Default currency.
- Timezone.
- Brand logo placeholder.
- Brand status = pending or active depending on policy.

#### Step 5: Document upload
Collect KYC / business verification documents.
Examples:
- National ID / passport.
- Business registration certificate.
- Tax/VAT certificate.
- Bank statement.
- Utility bill.
- Website ownership proof.
- Refund policy page screenshot or URL if needed.

#### Step 6: Plan assignment
Attach default free plan automatically.
If there is a free trial, assign free trial status instead.

#### Step 7: Onboarding completion
Show a checklist:
- Email verified.
- Phone verified.
- Business profile completed.
- Documents uploaded.
- Default brand created.
- API key generated.
- Gateway setup started.
- Domain whitelist added.
- Checkout tested.

### Recommended registration states
- `draft`
- `pending_verification`
- `pending_documents`
- `pending_kyc_review`
- `active_free_plan`
- `active_trial`
- `active_paid_plan`
- `rejected`
- `suspended`

---

## 8. Merchant Verification and KYC Flow

Merchant verification should be explicit and auditable.

### Verification goals
- Prevent fraud.
- Support compliance.
- Control payout eligibility.
- Control live gateway activation.
- Reduce platform risk.

### Verification levels
#### Level 0: Unverified
- Can sign up.
- Can explore dashboard.
- Cannot process real payments unless platform allows sandbox.
- Can complete setup steps.

#### Level 1: Basic verified
- Email and phone verified.
- Merchant can configure test mode.
- Limited feature access.

#### Level 2: Business verified
- Documents reviewed.
- Business profile approved.
- Gateway activation allowed depending on plan and policy.
- Live mode access can be enabled.

#### Level 3: Compliance approved
- Additional review passed.
- Higher volume or payout features unlocked.
- Enterprise-only or risk-sensitive capabilities allowed.

### Review workflow
#### Submitted by merchant
Merchant uploads documents and clicks “Submit for review”.

#### Internal review queue
SuperAdmin/compliance team sees queue with statuses:
- New submission.
- In review.
- More information requested.
- Approved.
- Rejected.

#### Actions available to reviewer
- Approve.
- Reject.
- Request more info.
- Suspend.
- Approve with conditions.
- Mark as high risk.

#### Notifications
Merchant receives:
- Submission received.
- More information requested.
- Approved.
- Rejected with reason.

### Document review details
Each document should capture:
- Upload type.
- Upload timestamp.
- Review status.
- Reviewer note.
- Expiration date if relevant.
- File checksum or signed storage reference.

### Screen states
Merchant sees one of these screens:
- Onboarding checklist screen.
- Pending review banner.
- Action required screen.
- Approved and ready screen.
- Rejected with resubmission CTA.
- Suspended account screen.

---

## 9. Plan and Billing Model

SaaS requires plan-based monetization.

### Suggested plans
- Free
- Trial
- Starter
- Pro
- Business
- Enterprise
- Custom negotiated plan

### What plans should control
- Number of brands.
- Number of staff users.
- Transaction limits per month.
- Revenue limits per month.
- Number of gateways enabled.
- API access availability.
- Payment link count.
- Invoice count.
- Webhook history retention.
- Report export access.
- White-label options.
- Custom domain support.
- Notification features.
- Support priority.
- KYC level required.

### Billing states
- `trial_active`
- `trial_expired`
- `payment_due`
- `active`
- `past_due`
- `grace_period`
- `suspended`
- `cancelled`

### Plan assignment logic
At registration:
- Merchant gets Free or Trial plan.
- Automatic feature caps apply.
- Upgrade CTA displayed in dashboard.

At admin action:
- SuperAdmin can change a merchant plan.
- SuperAdmin can offer trial extensions.
- SuperAdmin can apply discounts, promotions, and manual overrides.

### Billing events
- New signup.
- Trial started.
- Trial ending soon.
- Trial expired.
- Subscription renewed.
- Payment failed.
- Plan upgraded.
- Plan downgraded.
- Plan cancelled.
- Manual override applied.

### Billing UI
Merchant sees:
- Current plan.
- Usage meter.
- Billing history.
- Payment method.
- Upgrade options.
- Renewal date.
- Limits and overages.

SuperAdmin sees:
- All subscriptions.
- Active trials.
- Churn.
- Revenue totals.
- Failed payments.
- Manual billing changes.

---

## 10. Tenant and Merchant Access Model

### Access principle
Every screen and function should answer:
- Who can see it?
- Who can edit it?
- Which tenant does it belong to?
- Is it plan-gated?
- Is it verification-gated?
- Is it superadmin-only?

### Access layers
#### Public access
- Landing page.
- Pricing page.
- Signup.
- Login.
- Password reset.
- Terms, privacy, help pages.

#### Merchant access
- Dashboard.
- Brands.
- Transactions.
- Invoices.
- Payment links.
- Customers.
- Domains.
- API keys.
- Webhooks.
- Reports.
- Team/staff.
- Billing.
- Verification.
- Settings.
- Support.

#### SuperAdmin access
- Merchant directory.
- Merchant approval queue.
- Merchant detail and impersonation.
- Plans and pricing.
- Global gateways.
- Feature flags.
- Platform metrics.
- Audit logs.
- Support tickets.
- Compliance reviews.
- Global notifications.
- Maintenance mode and platform controls.

---

## 11. Menu Structure and Access Matrix

This section describes which menu should exist and who should access it.

### Public menus
- Home
- Features
- Pricing
- Documentation
- Contact
- Login
- Register
- Forgot Password

Access:
- Everyone

### Merchant menus

#### Dashboard
Purpose:
- Show business health, payment summary, and onboarding status.

Access:
- Merchant Owner
- Merchant Admin
- Merchant Staff if allowed

#### Brands
Purpose:
- View default brand.
- Add brand if plan allows.
- Edit brand identity, logo, currency, timezone.

Access:
- Merchant Owner
- Merchant Admin

#### Transactions
Purpose:
- Browse transactions.
- Filter by status, date, gateway, amount.
- View transaction detail.
- Retry or reference related records.

Access:
- Merchant Owner
- Merchant Admin
- Read-only staff if allowed

#### Invoices
Purpose:
- Create and manage invoices.
- View payment status.
- Send reminders.

Access:
- Merchant Owner
- Merchant Admin
- Billing staff if allowed

#### Payment Links
Purpose:
- Create payment links.
- Share links to customers.
- Track link status and conversions.

Access:
- Merchant Owner
- Merchant Admin
- Sales staff if allowed

#### Customers
Purpose:
- Store customer profile and transaction history.

Access:
- Merchant Owner
- Merchant Admin
- Support staff if allowed

#### Gateways
Purpose:
- Configure gateway settings.
- Enable/disable gateways.
- Test gateway connection.

Access:
- Merchant Owner
- Merchant Admin
- Restricted by verification and plan

#### Domains
Purpose:
- Whitelist checkout domains and webhook domains.

Access:
- Merchant Owner
- Merchant Admin

#### API
Purpose:
- Generate and rotate API keys.
- View request history.
- Set allowed scopes.

Access:
- Merchant Owner
- Merchant Admin
- Technical staff if allowed

#### Webhooks
Purpose:
- View IPN/webhook delivery history.
- Retry failed deliveries.

Access:
- Merchant Owner
- Merchant Admin

#### Reports
Purpose:
- Revenue, gateway mix, daily volume, settlements, failure rates.

Access:
- Merchant Owner
- Merchant Admin
- Finance staff if allowed

#### Team / Staff
Purpose:
- Invite staff users.
- Assign roles and permissions.

Access:
- Merchant Owner
- Merchant Admin with permission

#### Verification
Purpose:
- Upload documents.
- Track review state.
- Resubmit if rejected.

Access:
- Merchant Owner
- Merchant Admin

#### Billing
Purpose:
- View plan, invoices, payment methods, renewal.

Access:
- Merchant Owner
- Merchant Admin if allowed

#### Settings
Purpose:
- Profile, security, notifications, checkout preferences.

Access:
- Merchant Owner
- Merchant Admin
- Personal settings for staff

#### Support
Purpose:
- Tickets, chat, help articles, status updates.

Access:
- All authenticated merchant users

### SuperAdmin menus

#### Overview
Purpose:
- Platform health, revenue, merchants, approvals, risk, support.

#### Merchants
Purpose:
- Merchant list.
- Merchant profile.
- Tenant state.
- Plan assignment.
- Verification status.
- Login/impersonation if enabled.

#### Plans
Purpose:
- Create/edit plans.
- Limits.
- Feature flags.
- Pricing.
- Trial policy.

#### Verifications
Purpose:
- Review merchant documents.
- Approve/reject/escalate.

#### Gateways
Purpose:
- Global gateway settings.
- Provider availability.
- Platform-level routing policy.

#### Billing
Purpose:
- Platform invoices.
- Merchant subscriptions.
- Payment collection.

#### Analytics
Purpose:
- Total GMV.
- Active merchants.
- Churn.
- Conversion rate.
- Gateway success rates.

#### Support
Purpose:
- Tickets.
- Merchant help.
- Platform notices.

#### Audit Logs
Purpose:
- Security and compliance history.

#### Feature Flags
Purpose:
- Turn features on/off globally or by plan.

#### System Settings
Purpose:
- Branding.
- Email.
- SMS.
- Webhooks.
- Maintenance mode.

---

## 12. Feature Access Rules

### Access control logic
Every feature should evaluate in this order:
1. Is the user authenticated?
2. Is the user role allowed?
3. Does the tenant own the resource?
4. Is the tenant active?
5. Is the merchant verified enough for this feature?
6. Does the current plan allow it?
7. Is the feature globally enabled?
8. Is the action allowed by scope or permission?

### Example rules
- Payment links only for merchants on a plan that supports payment links.
- Live gateways only for merchants with approved verification.
- API access only for verified or paid plans, depending on policy.
- Staff invitations only if plan supports team seats.
- Additional brands only if plan allows multi-brand.
- White-label only for higher plans.
- Custom domains only for business or enterprise plans.

---

## 13. Merchant Dashboard Experience

The merchant dashboard should become the operational center of the tenant.

### Dashboard blocks
- Today’s revenue.
- Total transactions.
- Success rate.
- Pending settlements.
- Failed payments.
- Active gateways.
- Payment links generated.
- Invoice count.
- Customers count.
- Verification progress.
- Plan usage meter.
- Recent activity.
- Alerts and action items.

### Dashboard states
#### New merchant
- Big onboarding checklist.
- No transactions yet.
- CTA to complete verification.
- CTA to configure gateway.
- CTA to add domain.

#### Active merchant
- Live statistics.
- Recent transactions.
- Failures and trends.
- Gateway performance.

#### Limited merchant
- Warning for plan usage.
- Locked features.
- Upgrade CTA.

#### Suspended merchant
- Blocked operations.
- Read-only screen.
- Support contact CTA.

---

## 14. Brand Management In SaaS

### Brand role in SaaS
Brand should remain important, but now it belongs to a tenant.

### Brand creation on signup
When a merchant registers:
- Create tenant.
- Create one default brand automatically.
- Populate brand name from merchant profile.
- Use merchant email/domain defaults if available.
- Set brand currency and timezone.

### Brand workflow
Merchant can:
- Edit brand profile.
- Upload brand logo.
- Set support contact.
- Configure checkout appearance.
- Add brand domain.
- Enable gateway methods per brand.

### Brand limits
Plans may control:
- 1 brand on free plan.
- Multiple brands on paid plan.
- Enterprise brand groups.

---

## 15. Payment Gateway Access In SaaS

Existing gateway support should remain a platform feature, but access must become tenant-safe.

### Global gateway policy
SuperAdmin should define:
- Which gateways are available on the platform.
- Which gateways need manual approval.
- Which gateways are restricted by country or plan.
- Which gateways require document approval.

### Merchant gateway policy
For each merchant:
- Gateways may be enabled or disabled by plan.
- Gateway credentials are stored per tenant/brand.
- Gateway testing should be isolated.
- Gateway success/failure logs belong to the tenant.

### Gateway setup flow
Merchant selects gateway:
1. Choose gateway.
2. Read requirements.
3. Enter API credentials.
4. Validate credentials.
5. Enable test mode.
6. Complete live activation after approval.

### Gateway states
- Disabled
- Test mode only
- Pending verification
- Active
- Restricted
- Suspended
- Error / needs attention

---

## 16. Checkout and Payment Flow In SaaS

### Public checkout flow
- Merchant shares payment link or checkout page.
- Customer opens checkout.
- Amount, currency, invoice/reference, and merchant branding load.
- Customer selects gateway.
- Payment is processed.
- Webhook/IPN updates transaction state.
- Merchant and customer receive confirmation.

### SaaS-specific rules
- The checkout must resolve tenant from domain, brand, or link reference.
- Only allowed gateways for that tenant/brand should be shown.
- Brand theme and support contact should be used.
- All logs and webhooks should be tied to tenant ID.

### Checkout states
- Pending
- Redirecting to gateway
- Processing
- Paid
- Failed
- Cancelled
- Expired
- Refunded
- Partially refunded

---

## 17. Invoice Flow In SaaS

Merchant invoices are still a core feature.

### Invoice features to preserve
- Create invoice.
- Line-item breakdown.
- Send invoice link.
- Payment status tracking.
- Partial or full completion handling.
- Reminder notifications.
- Audit trail.

### SaaS-specific controls
- Invoice creation count may be plan-limited.
- Invoice templates may be plan-limited.
- Custom branding may be plan-limited.
- Additional staff permissions may control invoice creation.

---

## 18. Payment Link Flow In SaaS

Payment links should support merchant self-service.

### Payment link creation flow
- Merchant creates a link.
- Chooses brand.
- Sets amount, currency, description.
- Sets expiration date.
- Optionally adds customer email or prefill data.
- Link is generated and shared.

### Payment link lifecycle
- Draft.
- Active.
- Expired.
- Paid.
- Disabled.
- Archived.

### SaaS plan gating
Plans may limit:
- Number of active links.
- Link expiration rules.
- Custom branding.
- Domain restrictions.

---

## 19. API Access In SaaS

API access should become a major monetizable feature.

### API key management
- Create API key.
- Rotate API key.
- Revoke key.
- Scope assignment.
- IP allowlist if needed.
- Request logs.

### API scopes
Potential scopes:
- Create payment
- Verify payment
- Read transactions
- Read invoices
- Manage payment links
- Read customers
- Webhook management
- Reports access

### API plan rules
- Free plan may have read-only or limited API access.
- Paid plans may have full API access.
- Enterprise may have higher rate limits.

### API dashboard screens
- Key list.
- Usage chart.
- Last used time.
- Error rate.
- Audit history.

---

## 20. Domain Management In SaaS

Each merchant should manage trusted domains.

### Domain types
- Checkout return domains.
- Webhook callback domains.
- Merchant website domain.
- Custom branded domain.

### Domain verification states
- Unverified
- Pending DNS/HTTP challenge
- Verified
- Active
- Suspended

### Domain actions
- Add domain.
- Verify domain.
- Activate domain.
- Remove domain.
- Request review.

### Plan gating
- Free plan: one domain.
- Starter: multiple domains.
- Pro+: custom domain support.

---

## 21. Reporting and Analytics In SaaS

Reports should be tenant-aware and plan-aware.

### Merchant analytics
- Revenue by day/week/month.
- Gateway success rate.
- Failed payment reasons.
- Top customers.
- Customer repeat rate.
- Invoice conversion rate.
- Link conversion rate.
- API usage.
- Settlement pending totals.

### SuperAdmin analytics
- Merchant signups.
- Activation funnel.
- Verification funnel.
- Subscription revenue.
- Churn rate.
- Gateway usage across the platform.
- Error and failure trends.
- Support volume.

---

## 22. Notifications In SaaS

Notifications are critical for onboarding and operations.

### Merchant notifications
- Account approved.
- Document requested.
- Plan about to expire.
- Payment successful.
- Gateway error.
- New invoice paid.
- New payment link paid.
- Staff invitation.
- Domain approved.

### SuperAdmin notifications
- New merchant signup.
- Pending verification.
- Suspicious activity.
- Payment failures.
- Support escalations.
- Plan churn risk.

### Notification channels
- In-app.
- Email.
- SMS if enabled.
- Optional webhook.

---

## 23. Support and Compliance Workflow

### Support model
Merchants should be able to open support tickets for:
- Verification issues.
- Gateway failures.
- Billing issues.
- API problems.
- Payout questions.
- Feature requests.

### Compliance model
SuperAdmin/compliance can:
- Review documents.
- Track approval history.
- Flag suspicious merchants.
- Suspend risky accounts.
- Lock high-risk features.

### Audit trail
Everything sensitive should be logged:
- Login events.
- Permission changes.
- Plan changes.
- Verification decisions.
- Gateway changes.
- API key rotations.
- Domain approvals.
- Transaction modifications.

---

## 24. SuperAdmin Control Model

SuperAdmin is the platform operator. This role should remain powerful but safe.

### SuperAdmin can:
- Create/edit merchant accounts.
- Approve/reject merchants.
- Create/assign plans.
- Adjust limits.
- Force plan upgrades/downgrades.
- Suspend merchants.
- Reset credentials.
- View all logs and metrics.
- Enable/disable platform features.
- Override tenant restrictions.
- Manage gateway availability.
- Configure email/SMS/system templates.
- See all support tickets.

### SuperAdmin cannot accidentally:
- Break tenant isolation.
- Alter merchant data without audit logs.
- Disable platform safety checks globally without trace.

---

## 25. Recommended Merchant Status Lifecycle

A merchant should move through predictable states.

### Lifecycle
1. Signed up.
2. Email/phone verified.
3. Business profile completed.
4. Default brand created.
5. Documents uploaded.
6. Review pending.
7. Approved.
8. Free plan active.
9. Trial or paid plan active.
10. Gateway live.
11. Merchant operational.
12. Renewal due / upgrade prompt.
13. Suspended / churned if necessary.

### Merchant action screen for each state
#### Signed up
- Show “verify account”.

#### Verified but incomplete
- Show onboarding checklist.

#### Pending review
- Show review waiting screen.

#### Approved but free
- Show dashboard plus upgrade prompts.

#### Active paid
- Show full dashboard and plan usage.

#### Suspended
- Show explanation and support contact.

---

## 26. Development Plan For SaaS Migration

This is the implementation roadmap.

### Phase 1: SaaS foundation
- Add tenant tables and relationships.
- Add merchant registration flow.
- Add merchant roles and permissions.
- Add default brand auto-creation.
- Add tenant-aware auth guards.
- Add SuperAdmin menu structure.

### Phase 2: Merchant onboarding
- Build public signup pages.
- Build email/phone verification.
- Build business profile screens.
- Build document upload flow.
- Build onboarding checklist.
- Build verification queue for SuperAdmin.

### Phase 3: Plan and billing engine
- Define plans.
- Add usage counters.
- Add subscription records.
- Add renewal logic.
- Add upgrade/downgrade flow.
- Add payment/billing history.

### Phase 4: Tenant scoping
- Scope every merchant model by tenant.
- Add policy checks.
- Add tenant middleware.
- Add tenant-aware queries.
- Add API scoping.
- Add audit logs.

### Phase 5: Feature gating
- Gate gateways by plan and verification.
- Gate API access by plan.
- Gate brand count by plan.
- Gate staff count by plan.
- Gate custom domains by plan.
- Gate reports/export features by plan.

### Phase 6: Merchant experience polish
- Build dashboard KPI widgets.
- Add notifications.
- Add support tickets.
- Add billing UI.
- Add profile settings.
- Add invitation workflows.

### Phase 7: SuperAdmin controls
- Merchant management.
- Plan management.
- Verification management.
- Billing ops.
- Platform analytics.
- Feature flags.

### Phase 8: Security and compliance
- Audit logs.
- Rate limits.
- IP restrictions.
- Session rules.
- Document retention rules.
- Backup/restore plan.

### Phase 9: Testing and rollout
- Unit tests for tenant logic.
- Feature tests for merchant onboarding.
- Integration tests for billing.
- End-to-end tests for payment flows.
- Soft launch with pilot merchants.

---

## 27. Suggested Database Changes

### New tables
- tenants
- tenant_users
- subscriptions
- plans
- plan_features
- usage_counters
- verification_requests
- verification_documents
- billing_invoices
- support_tickets
- audit_events
- platform_notifications

### Add columns to existing tables
- tenant_id
- merchant_status
- verification_status
- plan_id
- subscription_id
- billing_state
- feature_flags
- last_activity_at
- approved_at
- rejected_at
- suspended_at

---

## 28. Suggested Migration Order

### Order of work
1. Create tenant and plan schema.
2. Create merchant onboarding and auth.
3. Auto-create default brand on signup.
4. Add verification and document upload.
5. Add plan assignment and usage limits.
6. Scope dashboard and transaction data.
7. Scope APIs, webhooks, and payment links.
8. Add SuperAdmin management screens.
9. Add billing and renewal.
10. Add analytics and support tools.
11. Add tests.
12. Roll out in phases.

---

## 29. Product Principles For The SaaS Version

### Principle 1: Keep feature parity
The SaaS version must not lose the current capabilities.

### Principle 2: Tenant isolation first
Every query and action must resolve to a tenant.

### Principle 3: Verification before risk
High-risk payment capabilities should require approval.

### Principle 4: Plan before permission
If the plan does not allow it, the UI and API should both enforce it.

### Principle 5: SuperAdmin override with audit
Central control must be possible but always logged.

### Principle 6: Incremental migration only
Build SaaS layers around the current logic rather than rewriting everything at once.

---

## 30. Practical Example User Journey

### Merchant journey example
1. Merchant clicks “Start Free”.
2. Creates account and verifies email/phone.
3. Completes business profile.
4. Default brand is created.
5. Documents are uploaded.
6. Merchant lands on checklist dashboard.
7. Merchant enables payment gateway in test mode.
8. Merchant adds allowed domains.
9. Merchant creates payment link and invoice.
10. Merchant tests checkout.
11. SuperAdmin approves business verification.
12. Merchant upgrades to paid plan.
13. Merchant goes live.
14. Merchant monitors transactions and reports.

### SuperAdmin journey example
1. Receives signup notification.
2. Reviews merchant docs.
3. Approves or requests more info.
4. Assigns default plan or trial.
5. Monitors merchant usage.
6. Handles escalations.
7. Reviews billing failures.
8. Suspends or restores merchant if needed.

---

## 31. Recommended Screens For The SaaS Product

### Public screens
- Landing page
- Pricing page
- Sign up page
- Login page
- Forgot password page
- Terms/privacy pages

### Merchant screens
- Onboarding checklist
- Dashboard
- Brand settings
- Gateway settings
- Transactions
- Invoices
- Payment links
- Customers
- Domains
- API keys
- Webhooks
- Reports
- Team & permissions
- Verification
- Billing
- Notifications
- Support

### SuperAdmin screens
- Platform dashboard
- Merchant list
- Merchant detail
- Verification review queue
- Plans management
- Subscriptions
- Platform analytics
- Support queue
- Audit logs
- Feature flags
- System settings

---

## 32. What Should Be Locked By Default

To protect the SaaS platform, the following should be locked until conditions are met:
- Live payment gateways.
- Custom domains.
- API production scopes.
- High-volume transaction limits.
- Additional team seats.
- White-label branding.
- Payout-sensitive features.
- Advanced reports and exports.

---

## 33. What Can Be Free

A well-designed free plan should allow the merchant to experience value.

Suggested free features:
- One merchant account.
- One default brand.
- Limited test transactions or sandbox mode.
- Basic dashboard.
- Limited payment links.
- Limited invoices.
- Basic reporting.
- Verification onboarding.

This gives the merchant a reason to upgrade without blocking discovery.

---

## 34. Risks And Mitigation

### Risk: Tenancy leaks
Mitigation:
- Tenant middleware.
- Global scopes.
- Policies.
- Tests.

### Risk: Overcomplicated onboarding
Mitigation:
- Keep signup short.
- Push advanced checks to post-signup checklist.
- Allow save-and-resume.

### Risk: Free plan abuse
Mitigation:
- Rate limits.
- Verification checkpoints.
- Usage counters.
- Fraud monitoring.

### Risk: Gateway support explosion
Mitigation:
- Gateway registry.
- Standard contract.
- SuperAdmin enablement per gateway.

### Risk: Billing edge cases
Mitigation:
- Subscription state machine.
- Grace periods.
- Retry logic.
- Audit trail.

---

## 35. Final Recommendation

The best SaaS conversion strategy is not to create a separate product from scratch. Instead, build a SaaS shell around the current platform and gradually make every merchant-facing feature tenant-aware.

The safest and most professional path is:
- Add tenant architecture.
- Add merchant signup.
- Auto-create one default brand.
- Add verification and document review.
- Add plan-based limits and billing.
- Preserve all current payment features.
- Keep SuperAdmin as the central platform owner.
- Enforce access by role, tenant, plan, and verification.

That gives you a real SaaS payment platform while protecting the current business logic and allowing controlled rollout.

---

## 36. Immediate Next Build Priority

If you want to start implementation, the order should be:
1. Tenant and plan data model.
2. Merchant registration and onboarding screens.
3. Default brand auto-creation.
4. Verification workflow.
5. Subscription and usage logic.
6. Tenant scoping of merchant data.
7. SuperAdmin merchant management.
8. Billing and renewal.
9. Feature gating and tests.

---

**Status:** Draft blueprint ready for implementation planning  
**Next action:** Convert this blueprint into the actual SaaS architecture backlog and database migration plan

---

## 37. Implementation Backlog

This backlog converts the SaaS blueprint into a practical delivery sequence. The order below is designed to reduce rework: foundation first, then onboarding, then billing, then tenant scoping, then admin control, then reporting and hardening.

### Epic 1: SaaS foundation
Goal:
- Establish the tenant-aware core model without changing existing merchant behavior.

Deliverables:
- Create `tenants` and `tenant_users` tables.
- Add `tenant_id` to all merchant-owned records.
- Introduce `plans`, `plan_features`, `subscriptions`, and `usage_counters`.
- Add tenant-aware authentication and middleware.
- Define SuperAdmin and merchant role boundaries.

Acceptance criteria:
- A merchant record can be created independently of platform admin data.
- Every merchant-owned resource can be resolved to one tenant.
- SuperAdmin can bypass tenant scoping safely and with audit logging.

### Epic 2: Merchant signup and onboarding
Goal:
- Let merchants register themselves from the public site.

Deliverables:
- Public signup screen.
- Email and phone verification flow.
- Business profile screen.
- Document upload screen.
- Onboarding checklist screen.
- Automatic default brand creation.
- Default free or trial plan assignment.

Acceptance criteria:
- A new merchant can register without internal admin intervention.
- One default brand is created automatically.
- The merchant lands on a guided onboarding flow after signup.

### Epic 3: Verification and approval workflow
Goal:
- Make merchant verification explicit and reviewable.

Deliverables:
- `verification_requests` table.
- `verification_documents` table.
- Review queue for SuperAdmin/compliance.
- Approve, reject, request-more-info, and suspend actions.
- Notification events for every review state.

Acceptance criteria:
- Uploads are linked to one merchant tenant.
- Review decisions are auditable.
- The merchant sees the correct status at every stage.

### Epic 4: Billing and subscriptions
Goal:
- Monetize the SaaS with plan-based access.

Deliverables:
- Plan management screens.
- Subscription lifecycle logic.
- Billing invoices and renewal history.
- Trial expiration and grace period logic.
- Manual SuperAdmin plan overrides.

Acceptance criteria:
- Plan changes affect feature access immediately.
- Billing state can suspend or limit tenant actions.
- SuperAdmin can see all subscription records.

### Epic 5: Tenant scoping for merchant features
Goal:
- Ensure merchants only see their own operational data.

Deliverables:
- Tenant-aware queries for brands, transactions, invoices, payment links, customers, gateways, webhooks, domains, API keys, reports, and notifications.
- Tenant policies and permission checks.
- Resource ownership checks in controllers and services.
- Audit logging for sensitive operations.

Acceptance criteria:
- One merchant cannot access another merchant’s records.
- All major merchant screens work with tenant-scoped data.
- Cross-tenant access is blocked by default.

### Epic 6: Merchant operational screens
Goal:
- Make the dashboard useful for daily payment operations.

Deliverables:
- Dashboard widgets.
- Transactions screen.
- Invoice screen.
- Payment link screen.
- Customer screen.
- Gateway setup screen.
- Domain management screen.
- API key management screen.
- Webhook history screen.
- Reports screen.

Acceptance criteria:
- The merchant can process payments, manage links, and review reporting in one tenant workspace.
- Locked features are clearly shown with upgrade prompts.

### Epic 7: SuperAdmin control center
Goal:
- Keep central platform control while preserving tenant isolation.

Deliverables:
- Merchant directory.
- Merchant detail and status view.
- Verification queue.
- Plan editor.
- Gateway policy screen.
- Support queue.
- Audit log viewer.
- Feature flag management.

Acceptance criteria:
- SuperAdmin can manage the whole platform from one console.
- Every platform-level action is logged.

### Epic 8: Reporting, support, and compliance
Goal:
- Make the platform operationally safe and supportable.

Deliverables:
- Platform analytics.
- Merchant analytics.
- Support ticket flow.
- Compliance actions and risk flags.
- Export/report permissions.

Acceptance criteria:
- Support staff can help merchants without exposing unrelated tenant data.
- Risk events are visible to SuperAdmin.

### Epic 9: Security and hardening
Goal:
- Protect tenant data and reduce abuse.

Deliverables:
- Rate limiting.
- Session and login protection.
- IP/domain checks where needed.
- Audit trail coverage for all sensitive actions.
- Data retention rules.

Acceptance criteria:
- Tenant isolation is enforced at the policy and query level.
- Sensitive actions are traceable.

---

## 38. Suggested Laravel Build Map

The current Laravel app should be extended in layers rather than replaced. The list below describes the build areas that should be created or refactored.

### Models to add or refactor
- Tenant
- TenantUser
- Plan
- PlanFeature
- Subscription
- UsageCounter
- VerificationRequest
- VerificationDocument
- BillingInvoice
- SupportTicket
- AuditEvent

### Existing models to tenant-scope
- Brand
- Transaction
- Invoice
- PaymentLink
- Gateway
- Customer
- ApiKey or Api credential model
- Domain
- Notification
- Webhook
- Staff or team member model

### Services to add
- TenantResolver service
- MerchantOnboarding service
- VerificationWorkflow service
- SubscriptionService
- PlanLimitService
- TenantAccessService
- TenantAuditService
- MerchantNotificationService

### Policies and middleware to add
- TenantScope middleware
- SuperAdmin bypass policy
- Merchant ownership policy
- Plan access policy
- Verification state policy
- Domain access policy
- API scope policy

### Controllers to add or expand
- Public registration controller
- Merchant onboarding controller
- Verification controller
- Billing controller
- Plan selection controller
- Tenant dashboard controller
- Merchant settings controller
- SuperAdmin merchant controller
- SuperAdmin verification controller
- SuperAdmin plan controller
- SuperAdmin audit controller

### Routes to organize
- Public auth and registration routes
- Merchant portal routes under one authenticated tenant group
- SuperAdmin routes under an admin-only prefix
- API routes that resolve tenant context explicitly
- Webhook/IPN routes that verify tenant ownership before processing

### Views or screens to add
- Public signup and pricing pages
- Merchant onboarding wizard
- Merchant dashboard widgets
- Verification upload and status pages
- Billing and subscription pages
- SuperAdmin merchant overview pages
- SuperAdmin verification queue pages
- SuperAdmin plan management pages

### Jobs and commands to add
- Tenant provisioning job
- Verification reminder job
- Trial expiration reminder job
- Subscription renewal job
- Usage limit enforcement job
- Audit log cleanup job
- Notification dispatch job

### Tests to add
- Merchant signup and onboarding tests
- Tenant isolation tests
- Verification workflow tests
- Plan limit tests
- Subscription lifecycle tests
- SuperAdmin access tests
- Merchant dashboard visibility tests
- API tenant scope tests

---

## 39. Screen-By-Screen Build Order

Build in this sequence so the product becomes usable early while the deeper SaaS controls are still being added.

### Stage 1: Public acquisition
- Landing page.
- Pricing page.
- Signup page.
- Login page.
- Forgot password page.

### Stage 2: Onboarding
- Account verification page.
- Business profile page.
- Document upload page.
- Default brand setup page.
- Onboarding checklist page.

### Stage 3: Core merchant operations
- Dashboard.
- Transactions.
- Payment links.
- Invoices.
- Customers.
- Gateways.

### Stage 4: Growth and controls
- Domains.
- API keys.
- Webhooks.
- Reports.
- Team and permissions.
- Billing.
- Settings.

### Stage 5: Platform admin
- Merchant directory.
- Merchant detail.
- Verification queue.
- Plans.
- Subscriptions.
- Analytics.
- Support.
- Audit logs.
- Feature flags.

---

## 40. Delivery Milestones

### Milestone A: SaaS skeleton
- Tenant tables exist.
- Merchant signup works.
- Default brand auto-creates.
- Merchant can log in and see a scoped dashboard.

### Milestone B: Merchant activation
- Verification upload and review work.
- Plan assignment works.
- Merchant can configure a gateway and create test transactions.

### Milestone C: Monetization
- Billing and subscriptions work.
- Upgrade/downgrade logic works.
- Usage limits and feature gating are enforced.

### Milestone D: Platform administration
- SuperAdmin can manage all merchants.
- SuperAdmin can review verification.
- SuperAdmin can control plans and feature flags.

### Milestone E: Hardening and scale
- Tenant isolation is tested.
- Audit logs are complete.
- Notifications and support are production ready.

---

## 41. Implementation Rule Set

Use these rules while building the SaaS version:
- Never expose merchant data without tenant resolution.
- Never enable a paid feature without checking plan and verification state.
- Never create a merchant without creating the default tenant context.
- Never approve risky features without an auditable decision.
- Never skip tests for tenant boundaries.
- Never remove existing functionality unless the SaaS replacement is already verified.

---

## 42. Build Outcome Target

When this backlog is complete, the product should support this end state:
- A merchant can register themselves on the public site.
- A tenant and default brand are created automatically.
- The merchant uploads documents and passes verification.
- The merchant subscribes to a plan.
- The merchant uses gateways, invoices, links, APIs, and reports inside one isolated workspace.
- SuperAdmin can manage the whole platform without breaking tenant boundaries.
- The platform is ready to scale as a real SaaS payment product.
