# PipraPay Laravel Migration & Architecture Breakdown

This document serves as the master blueprint for the PipraPay legacy-to-Laravel migration. It details the current hybrid architecture, completed milestones, integration patterns, and the remaining migration roadmap. 

**IMPORTANT FOR AI AGENTS:** Always read this document before making architectural changes. Update this document continuously whenever a new phase is completed or a new core functionality is integrated.

### 🛑 Mandatory Git Workflow Rules for AI Agents
1. **Critical Module Pre-Commits**: Before starting *any* high-risk, breaking, or critical modifications (e.g., core schema migrations, complex refactoring), you **MUST** execute a Git commit acting as a backup. The commit message must include a professional breakdown explaining the security/stability reasons for the commit and what critical features are about to be altered.
2. **Phase Completion Commits**: You must **NOT** commit on every single response. Instead, you must run a professional, descriptive Git commit only *after* a section, phase, or module has been thoroughly completed and validated.

---

## 🏗️ 1. Architecture & Integration Flow
**Goal**: Migrate a legacy procedural PHP payment gateway (PipraPay) into a modern, native Laravel application while maintaining strict backward compatibility for active users and existing plugins.

### The "Hybrid Bridge" Pattern
Currently, the application runs in a Hybrid state, marrying legacy procedural code with Laravel's modern routing and MVC architecture.

*   **Legacy Runtime (`LegacyRuntimeService.php`)**: Bootstraps the old PHP environment. It includes legacy configuration files and sets up superglobals so that legacy functions inside `pp-content` and `pp-include` still work within Laravel's request lifecycle without crashing.
*   **Payment & Transaction Layer (`PaymentService.php`)**: Handles the core transaction logic natively in Laravel. Crucially, when inserting into legacy database tables (like `pp_transaction`), it provides strict fallback defaults (e.g., `'sender_type' => '--'`, `'processing_fee' => 0.00`) to prevent MySQL strict-mode `1364 Field doesn't have a default value` exceptions.
*   **Theme Engine (`ThemeService.php`)**: Bridges legacy database arrays into Laravel's Blade engine. Legacy helper scripts (like `pp_renderFormFields`) expect a literal `$pageData` array. `ThemeService` explicitly passes `$pageData` alongside its extracted variables to ensure these legacy layout renderers function normally inside modern Blade files.
*   **API Architecture (`ApiController.php` & `ApiCheckoutService.php`)**: Built natively in Laravel. Endpoints like `/api/v1/checkout` bypass legacy routers entirely. Validation is strict, payload structures are typed, and authorization uses the native `PpApi` scope-based model (`view_balance`, `create_payment`, etc.).

---

## ✅ 2. What We Have Completed (Current Session)

### Native API Porting
- [x] Implemented native `/api/v1/checkout`, `/api/v1/balance`, and `/api/v1/transactions` REST endpoints.
- [x] Built the `ApiCheckoutService` to securely validate incoming merchant payloads.
- [x] Fixed transaction insertion database crashes by mapping safe defaults inside `PaymentService::createTransaction`.

### Blade Theme & UI Stabilization
- [x] Resolved the critical `Undefined variable $pageData` HTTP 500 error on invoices and payment links by re-injecting the raw array payload into Blade views via the `ThemeService`.
- [x] Cleaned up legacy UI corruption (stray line numbers accidentally injected into Blade templates).
- [x] **Language Switcher Fix**: Resolved an infinite redirect loop caused by strict `null` evaluation in Laravel. Changed strict `!== ""` to loose `!= ""` across 10 different theme files (`commerz` and `twenty-six`).
- [x] **UX Enhancement**: Upgraded the language switcher to strip messy query parameters (e.g., `?lang=en`) using `url()->current()`, while injecting a `sessionStorage` flag to trigger a beautiful success Toast notification upon reload.

### WooCommerce & UX Optimization (Current Session)
- [x] **Order Status Sync Logic**: Fixed a critical logic bug where WooCommerce orders were marked as "Complete" for pending payments. Implemented a strict mapping: `pending`/`initiated` -> `on-hold`.
- [x] **Zero-Delay Redirection**: Eliminated redundant 3-second "waiting" screens on both the Gateway (Laravel) and WordPress sides. Redirection is now instantaneous.
- [x] **Contextful Status UI**: Re-designed the `checkout-status` page to show a brief (1.5s) "contextful" summary with a spinner, balancing visual feedback with speed.
- [x] **Plugin Stabilization**: Standardized the `WC_API` global hook to ensure the PipraPay plugin takes control early, preventing conflict-related 404/blank page errors.
- [x] **Internal Fixes**: Resolved a `TypeError` in `ThemeService` that occurred during direct PHP redirects, ensuring compatibility with the legacy theme engine.

### Phase 2: Gateway Driver Modernization (Completed)
- [x] **Universal Driver Architecture**: Implemented `AbstractBaseDriver`, `MfsAutomationDriver`, and `ManualPaymentDriver` to handle all non-API gateway types.
- [x] **Bulk Migration**: Registered all 46+ legacy slugs (bKash, Nagad, Rocket, Cellfin, Wise, etc.) in the `GatewayRegistry`.
- [x] **Native Verification Engine**: Refactored `CheckoutController` to use a native `PaymentVerificationService`, completely removing the dependency on legacy procedural code for user-submitted transaction verification.
- [x] **Modern Gateway UI**: Implemented a state-of-the-art selection-based UI in the `twenty-six` theme. Users now select a gateway card to enable a sleek, AJAX-driven "Pay Now" action.
- [x] **AJAX Initiation Flow**: Standardized the initiation process to use background requests. This allows for smooth loading states and instant error reporting via Toasts, followed by a professional redirect to the gateway's native page.
- [x] **Driver Stabilization**: Verified and fixed bKash (Tokenized API) and SSLCommerz drivers to support the new AJAX flow, including strict amount formatting (2 decimal places) for bKash compatibility.
- [x] **Legacy Bridge Synchronization**: Successfully bootstrapped legacy globals (`db_prefix`, `PipraPay_INIT`) inside native services to allow seamless usage of legacy database functions.

### Phase 3: IPN & Webhook Security (Completed)
- [x] **Hardened IPN Verification**: Shifted from passive callback trust to a strict **API-to-API validation model** for bKash and SSLCommerz. Transactions are now verified directly with the gateway servers before status updates.
- [x] **Merchant Webhook System**: Implemented a secure, real-time notification engine for merchants. 
- [x] **HMAC-SHA256 Signing**: All webhooks are now cryptographically signed using the merchant's `api_key` as a secret, ensuring notifications are tamper-proof.
- [x] **Webhook Delivery Logging**: Integrated a detailed logging system in the `pp_webhook_log` table to track every delivery attempt, response code, and payload for easier merchant troubleshooting.
- [x] **Documentation**: Created a comprehensive [payment_verification_guide.md](file:///Volumes/Project/Personal%20Project/ZontroPay/PipraPay-Laravel%20%28Non%20SaaS%29/laravel-app/payment_verification_guide.md) for developers.

---

## 🚀 3. Remaining Migration Plan & Recommendations

### Immediate Next Steps (Priority)
1.  **Production Audit**: 
    *   Re-enable `sslverify => true` in the WordPress plugin before deployment.
    *   Switch off local debug flags in `wp-config.php` and `.env`.
2.  **Admin Panel UI Completion**: Finalize the re-initialization of Bootstrap dropdowns and tooltips within the AJAX navigation flow to ensure 100% UI stability across all management modules.

### Long-Term Recommendations
1.  **Centralized Logging**: Implement a unified logging service in Laravel (using Monolog) that captures both Laravel errors and legacy `pp-content` logs in one place for easier debugging.
2.  **Automated Testing Suite**: Build a series of Laravel Dusk tests to simulate the full A-Z checkout flow (Success, Fail, Cancel, Pending) to prevent regressions in future updates.
3.  **Admin Panel UI Migration**: Gradually migrate the legacy Admin Panel into a modern TailAdmin/Blade dashboard for better maintainability.
