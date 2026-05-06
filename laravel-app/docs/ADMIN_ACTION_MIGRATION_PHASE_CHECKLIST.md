# Admin Action Migration Phase Checklist

Last updated: 2026-04-20

Goal: migrate legacy admin POST `action` handlers from `pp-adapter.php` into native Laravel, without changing behavior/contracts.

## Migration Rules (must keep)

- Keep exact action names used by frontend AJAX.
- Keep response shape and keys (`status`, `title`, `message`, `response`, `datatableInfo`, `pagination`, `csrf_token`, etc.).
- Keep permission checks and page access logic same as legacy.
- Keep legacy side effects and message text as-is.
- Keep unknown actions fallback active until all actions are migrated and burn-in is complete.

## Progress Summary

- Native-migrated actions: 85
- Remaining actions: 0

## Completed (already native)

- [x] set-default-brand
- [x] my-account-profile-information
- [x] my-account-account-browser-sessions
- [x] my-account-account-two-factor-authentication
- [x] dashboard-transaction-statistics
- [x] dashboard-gateway-statistics
- [x] device-list
- [x] device-connect-info
- [x] device-delete
- [x] device-bulk-action
- [x] sms-data-list
- [x] sms-data-create
- [x] sms-data-info-byID
- [x] sms-data-edit
- [x] sms-data-delete
- [x] sms-data-bulk-action
- [x] balance-verification-list
- [x] balance-verification-bulk-action
- [x] balance-verification-delete
- [x] balance-verification-create
- [x] balance-verification-iupdate
- [x] balance-verification-info-byID
- [x] balance-verification-update
- [x] faq-list
- [x] faq-create
- [x] faq-info-byID
- [x] faq-edit
- [x] faq-bulk-action
- [x] faq-delete
- [x] all-domain-list
- [x] domains-info-byID
- [x] create-domains
- [x] domains-edit
- [x] domains-delete
- [x] domain-bulk-action
- [x] customer-list
- [x] customers-create
- [x] customers-bulk-action
- [x] customers-delete
- [x] customers-info-byID
- [x] customers-edit
- [x] all-brand-list
- [x] brand-delete
- [x] brand-bulk-action
- [x] api-list
- [x] api-create
- [x] api-info-byID
- [x] api-edit
- [x] api-delete
- [x] api-bulk-action
- [x] currency-list
- [x] currency-info-byID
- [x] currency-edit
- [x] currency-bulkImport
- [x] currency-rateSync
- [x] currency-bulk-rateSync
- [x] gateways-list
- [x] gateway-create
- [x] gateways-delete
- [x] gateways-bulk-action
- [x] reports
- [x] invoice-list
- [x] invoice-delete
- [x] invoice-bulk-action
- [x] paymentLink-list
- [x] paymentLink-delete
- [x] paymentLink-bulk-action
- [x] paymentLink-defaultLinkCurrency
- [x] staff-management-list
- [x] staff-delete
- [x] staff-bulk-action
- [x] staff-permissions
- [x] staff-permission-delete
- [x] staff-permission-bulk-action
- [x] activities-list
- [x] addons-list
- [x] addons-create
- [x] addons-delete
- [x] addons-bulk-action
- [x] themes-new-active
- [x] geneal-application-settings
- [x] cron-job-command-generate
- [x] system-settings-update-check
- [x] system-settings-update-download
- [x] system-settings-update-install
- [x] system-settings-update-setting

## Phase 1: Fast / Low-Risk (highest speed)

- [x] Transactions module
- [x] Transaction actions: `transaction-list`, `transaction-delete`, `transaction-bulk-action`, `transaction-ipn`
- [ ] File anchors: `resources/views/legacy/pp-content/pp-admin/pp-root/transaction/index.blade.php`, `resources/views/legacy/pp-content/pp-admin/pp-root/transaction/edit.blade.php`

- [x] Brands list module
- [x] Brand actions: `all-brand-list`, `brand-delete`, `brand-bulk-action`
- [ ] File anchor: `resources/views/legacy/pp-content/pp-admin/pp-root/brands/index.blade.php`

## Phase 2: Medium Complexity

- [x] API Settings module
- [x] Actions: `api-list`, `api-create`, `api-info-byID`, `api-edit`, `api-delete`, `api-bulk-action`
- [ ] File anchor: `resources/views/legacy/pp-content/pp-admin/pp-root/brand-setting/api-setting.blade.php`

- [x] Currency Settings module
- [x] Actions: `currency-list`, `currency-info-byID`, `currency-edit`, `currency-bulkImport`, `currency-rateSync`, `currency-bulk-rateSync`
- [ ] File anchor: `resources/views/legacy/pp-content/pp-admin/pp-root/brand-setting/currency-setting.blade.php`

- [x] Gateways module
- [x] Actions: `gateways-list`, `gateway-create`, `gateways-delete`, `gateways-bulk-action`
- [ ] File anchor: `resources/views/legacy/pp-content/pp-admin/pp-root/gateways/index.blade.php`

- [x] Reports module
- [x] Actions: `reports`
- [ ] File anchor: `resources/views/legacy/pp-content/pp-admin/pp-root/reports.blade.php`

## Phase 3: Higher Complexity

- [x] Invoice module
- [x] Actions: `invoice-list`, `invoice-delete`, `invoice-bulk-action`
- [ ] File anchors: `resources/views/legacy/pp-content/pp-admin/pp-root/invoice/index.blade.php`, `resources/views/legacy/pp-content/pp-admin/pp-root/invoice/edit.blade.php`

- [x] Payment Link module
- [x] Actions: `paymentLink-list`, `paymentLink-delete`, `paymentLink-bulk-action`, `paymentLink-defaultLinkCurrency`
- [ ] File anchors: `resources/views/legacy/pp-content/pp-admin/pp-root/payment-link/index.blade.php`, `resources/views/legacy/pp-content/pp-admin/pp-root/payment-link/edit.blade.php`

- [x] Staff module
- [x] Actions: `staff-management-list`, `staff-delete`, `staff-bulk-action`, `staff-permissions`, `staff-permission-delete`, `staff-permission-bulk-action`
- [ ] File anchors: `resources/views/legacy/pp-content/pp-admin/pp-root/staff-management/index.blade.php`, `resources/views/legacy/pp-content/pp-admin/pp-root/staff-management/permissions-list.blade.php`

- [x] System Settings module
- [x] Actions: `geneal-application-settings`, `cron-job-command-generate`, `system-settings-update-check`, `system-settings-update-download`, `system-settings-update-install`, `system-settings-update-setting`
- [ ] File anchors: `resources/views/legacy/pp-content/pp-admin/pp-root/system-settings/geneal.blade.php`, `resources/views/legacy/pp-content/pp-admin/pp-root/system-settings/cron-job.blade.php`, `resources/views/legacy/pp-content/pp-admin/pp-root/system-settings/update.blade.php`

## Phase 4: Optional / Lower Impact

- [x] Activities module
- [x] Actions: `activities-list`
- [ ] File anchor: `resources/views/legacy/pp-content/pp-admin/pp-root/activities.blade.php`

- [x] Addons module
- [x] Actions: `addons-list`, `addons-create`, `addons-delete`, `addons-bulk-action`
- [ ] File anchor: `resources/views/legacy/pp-content/pp-admin/pp-root/addons/index.blade.php`

- [x] Theme switch module
- [x] Actions: `themes-new-active`
- [ ] File anchor: `resources/views/legacy/pp-content/pp-admin/pp-root/brand-setting/themes.blade.php`

## Validation Checklist Per Module

- [ ] Action added to `NativeAdminActionController` mapping.
- [ ] Native service methods implemented.
- [ ] Permission checks match legacy for each action.
- [ ] Response keys/messages match legacy.
- [ ] `csrf_token` included in all responses.
- [ ] Migration toggle test updated.
- [ ] `php -l` + focused feature tests pass.
- [ ] Browser smoke test done for list/create/edit/delete/bulk flow.
