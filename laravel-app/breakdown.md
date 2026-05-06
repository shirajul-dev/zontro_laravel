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

### Phase 1: Database Schema & Eloquent Porting
- [x] Generated formal Laravel Migrations for legacy tables (`pp_transaction`, `pp_invoice`, `pp_api`, etc.)
- [x] Built the respective Eloquent Models (`PpTransaction`, `PpInvoice`, `PpBrand`, `PpGateway`) with their proper relationships inside `app/Models/`.
- [x] Refactored all core legacy query functions inside `pp-content/pp-include/pp-functions.php` (such as `pp_gateways()`, `limit_checker()`, `pp_gateway_info()`, and `pp_check_transaction()`) to utilize modern Laravel Eloquent models instead of raw `getData()` calls.

---

## 🚀 3. Remaining Migration Plan (What's Next)

### Phase 2: Gateway Driver Modernization (Current Target)
*(Note: Admin Panel UI Migration has been completely skipped for now.)*
There are ~40+ smaller local payment gateways (Nagad, Rocket, etc.) still utilizing legacy PHP classes. 
1.  Port the most highly-trafficked gateways to native Laravel Payment Drivers using modern HTTP clients (`Http::post()`).
2.  Standardize the IPN (Instant Payment Notification) callback routing.
