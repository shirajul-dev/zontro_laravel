# PipraPay Native Laravel Migration: Task List

This document tracks the progress of the migration from legacy procedural PHP to a native Laravel architecture.

## Phase 1: Database & Models Foundation
- [x] Create Eloquent Models for all `pp_` tables
    - [x] `Brand` (Table: `pp_brands`)
    - [x] `Transaction` (Table: `pp_transaction`)
    - [x] `Invoice` (Table: `pp_invoice`)
    - [x] `InvoiceItem` (Table: `pp_invoice_items`)
    - [x] `PaymentLink` (Table: `pp_payment_link`)
    - [x] `PaymentLinkField` (Table: `pp_payment_link_field`)
    - [x] `Gateway` (Table: `pp_gateways`)
    - [x] `ApiCredential` (Table: `pp_api`)
    - [x] `Domain` (Table: `pp_domain`)
    - [x] `Currency` (Table: `pp_currency`)
- [x] Implement `BaseModel` or Trait to handle legacy data quirks
    - [x] Accessor to convert `--` to `null`
    - [x] Mutator to convert `null` to `--` (for backward compatibility if needed)
    - [x] Date casting for custom date formats
- [x] Define Eloquent Relationships
    - [x] Brand -> Transactions
    - [x] Brand -> Invoices
    - [x] Invoice -> InvoiceItems

## Phase 2: Core Service Layer
- [x] **MoneyService**: Encapsulate all `bcmath` operations from `pp-functions.php`
- [x] **BrandingService**: Resolve brand settings, logos, and active themes natively
- [x] **PaymentService**: 
    - [x] Migration of `verifyPaymentTolerance` logic
    - [x] Migration of transaction creation logic
    - [x] Migration of status update hooks
- [x] **GatewayRegistry**:
    - [x] Create `PaymentGatewayInterface`
    - [x] Create native drivers for core gateways (SSLCommerz done)

## Phase 3: Controller & Route Migration
- [x] **API Migration**
    - [x] Rewrite `ApiController@handle` to use native services
    - [x] Implement Laravel API Resources for JSON responses (integrated in Controller/Service)
    - [x] Move API authentication to Laravel Middleware (`AuthenticateApiKey`)
- [x] **IPN & Webhook Migration**
    - [x] Create native `WebhookController` (handled by `IpnController` refactor)
    - [x] Route `/ipn/{gateway}` to native drivers
- [x] **Checkout Migration**
    - [x] Handle `POST` actions (payment submission) natively in `CheckoutController`
    - [x] Remove `action-v2` legacy dispatching logic (Replaced with native `handleAction`)
- [x] **Cron Migration**
    - [x] Convert legacy cron scripts to Laravel Scheduled Commands (`piprapay:cron`)

## Phase 4: UI & Theme Migration (Blade)
- [x] **Theme Engine Refactor**
    - [x] Implement dynamic `theme::` namespace registration in `ThemeService`
    - [x] Add `pp_theme_asset()` helper for dynamic asset resolution
    - [x] Support self-contained, full-HTML Blade themes (WordPress style)
- [/] **Theme Migration (TwentySixTheme)**
    - [x] Convert `checkout.php` -> `checkout.blade.php`
    - [x] Convert `gateway.php` -> `gateway.blade.php`
    - [x] Convert `checkout-status.php` -> `checkout-status.blade.php`
    - [ ] Convert `invoice.php` -> `invoice.blade.php`
    - [ ] Convert `payment-link.php` -> `payment-link.blade.php`
- [ ] Standardize Asset Loading
    - [ ] Move `pp-modules/pp-themes/*/assets` to `public/themes/`
    - [ ] Use Laravel `asset()` helper in templates

## Phase 5: Security & Cleanup
- [ ] Enable CSRF protection on all public forms (add `@csrf`)
- [ ] Remove `LegacyRuntimeService.php`
- [ ] Delete `resources/legacy-index.php`
- [ ] Delete `pp-content/pp-include/pp-functions.php`
- [ ] Remove `index.php` from project root (if still present)
- [ ] Final end-to-end testing of the payment flow

---
*Note: Mark tasks as [x] once completed and verified.*
