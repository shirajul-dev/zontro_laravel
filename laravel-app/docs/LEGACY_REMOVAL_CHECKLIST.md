# PipraPay Legacy Removal Checklist

This checklist tracks the remaining work to remove the raw PHP bridge layer and keep the Laravel app fully functional with the same visible behavior.

## Current Status

- [x] Laravel app boots and serves the migrated clone
- [x] Schema migration mirrors the legacy SQL dump
- [x] Admin seed data is available in MySQL
- [x] Native `POST /login` is working
- [x] Native `POST /2fa` is working
- [x] Native `POST /forgot` is working
- [ ] Native admin actions are still partly routed through the bridge
- [ ] Full native dashboard/page actions are still incomplete

## Phase A: Auth Removal

- [x] Replace legacy login POST with native Laravel auth
- [x] Keep the legacy login page UI intact
- [x] Preserve login JSON response keys and redirect targets
- [x] Preserve `pp_admin`, `pp_2fa`, and `pp_brand` cookie behavior
- [x] Replace legacy 2FA verification POST with native Laravel handling
- [x] Replace legacy forgot-password POST with native Laravel handling
- [x] Confirm 2FA and forgot flows still work from the existing forms
- [ ] Remove remaining legacy auth dispatch once all auth endpoints are native

## Phase B: Admin Actions Removal

- [x] Replace `set-default-brand` with a native controller action
- [x] Replace profile update actions with native controller methods
- [x] Replace browser session logout actions
- [x] Replace 2FA toggle actions
- [ ] Replace password change / temp password update actions
- [ ] Replace staff management create/edit/delete actions
- [ ] Replace permissions and brand settings actions
- [ ] Replace device/domain/report/transaction action handlers
- [ ] Keep every JSON payload and redirect target identical

## Phase C: Admin Page Controllers

- [ ] Create native controllers for each admin page group
- [ ] Move dashboard data queries to native Laravel services
- [ ] Move transaction and reporting queries to native Laravel services
- [ ] Move brand, gateway, invoice, and payment-link flows off the bridge
- [ ] Preserve legacy markup and visible page structure until the last page is migrated

## Phase D: API and Payment Removal

- [ ] Replace raw API endpoints with native Laravel controllers
- [ ] Replace payment page rendering with native Laravel controllers
- [ ] Replace invoice webhook handling with native Laravel controllers
- [ ] Replace cron entry points with native Laravel commands/jobs
- [ ] Validate IPN, payment link, and invoice edge cases

## Phase E: Session and Compatibility Cleanup

- [ ] Remove the legacy runtime dispatch from the request path
- [ ] Remove any remaining direct root `index.php` dependency
- [ ] Keep session/cookie compatibility until the final bridge removal
- [ ] Remove temporary compatibility code once no legacy page depends on it

## Phase F: Verification

- [ ] Login works with email and username
- [ ] 2FA login works for enabled accounts
- [ ] Forgot password updates the account and sends the reset email
- [ ] Brand switching works after login
- [ ] Dashboard loads without legacy redirects
- [ ] Admin pages load with the native routes only
- [ ] Payment and invoice flows still return the same responses
- [ ] API/webhook/cron behavior matches the legacy app
- [ ] No route depends on the legacy bridge anymore

## Suggested Order of Execution

1. Finish auth endpoints: 2FA, forgot, and logout cleanup.
2. Move the top-level admin actions that affect login state and brand context.
3. Convert dashboard data and core admin CRUD pages.
4. Migrate payment, invoice, webhook, API, and cron endpoints.
5. Remove the legacy dispatch layer only after parity checks pass.
