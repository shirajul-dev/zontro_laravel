# PipraPay Native Laravel Migration Status & Action Plan

This document provides a highly detailed breakdown of the leg-by-leg status of the **PipraPay procedural-to-native Laravel migration**. It is designed to trace what is completed, check our native migration rules against current work, analyze the legacy admin controllers and views, and map out the exact modules to migrate next.

---

## 🏗️ 1. Migration Rules & Philosophy Review

The native Laravel migration is governed by the principles laid out in [NATIVE_MIGRATION_BLUEPRINT.md](file:///Volumes/Project/Personal%20Project/ZontroPay/PipraPay-Laravel%20%28Non%20SaaS%29/laravel-app/NATIVE_MIGRATION_BLUEPRINT.md). Let's review the rules and cross-reference them with current implementations:

### 🔄 The "Parallel" Principle
* **Legacy Layer**: Lives under `/admin`, uses the `pp_` database prefix, and uses `Pp*` models. It remains 100% untouched to ensure active merchants and old payment/checkout integrations don't crash.
* **Native Layer**: Lives under `/merchant`, uses the new `zp_` database prefix, and uses `Zp*` models. It uses modern, standard Laravel MVC.
* **Verification**: We strictly followed this! All new tables use `zp_` (e.g., `zp_admins`, `zp_brands`, `zp_currencies`) and models are cleanly encapsulated under the `App\Models` namespace.

### 🔒 Absolute Legacy Isolation Rule (100% Untouchable)
* **Rule**: All legacy `Admin` views, controllers, models, and service files inside legacy areas (such as `app/Http/Controllers/Admin/`, `app/Services/Admin/`, `resources/views/admin/`) are **100% untouchable and must remain completely pristine and unmodified**.
* **Merchant-Only Separation**: We must perform all migrations, customizations, and upgrades strictly inside the merchant panel namespace (e.g., `app/Http/Controllers/Merchant/`, `resources/views/merchant/`, etc.). If legacy service layers are called, any visual customization (such as refactoring pagination styling or table formatting) must be intercepted and formatted cleanly inside the merchant controllers/views without modifying a single line of legacy code.

### 💾 Dual-Prefix DB & Model Strategy
* Models like `ZpAdmin.php`, `ZpBrand.php`, and `ZpCurrency.php` map directly to standard Laravel-migrated tables with auto-increment IDs and standard `timestamps()`.
* Schema modifications are done via granular Laravel migrations (e.g., `add_is_default_to_zp_brands_table.php` and `add_additional_fields_to_zp_brands_table.php`) rather than single legacy sql schemas.

### 🌐 Routing and Namespaces
* All native routes are grouped under the `merchant` prefix inside `routes/merchant.php` with names prefixed with `merchant.`.
* Legacy routes remain in `routes/admin.php` and still map to the legacy bridge controllers (`NativeAdminPageController` & `NativeAdminActionController`).
* Native controllers live in `App\Http\Controllers\Merchant\*`, providing a perfect namespace separation.

### ⚡ SPA-Lite UX Experience
* Views are designed using the elegant Tailwind-based **TailAdmin** template (`Template Original/tailadmin`).
* Full page reloads are replaced with AJAX forms and Fetch API actions.
* Actions return standard JSON responses:
  ```json
  {
      "status": "true/success",
      "title": "Action Title",
      "message": "Dynamic feedback message",
      "redirect": "optional_redirect_url"
  }
  ```
* UI actions include micro-animations, loading spinner states on buttons, and professional toast notifications (e.g., Toastr) without breaking page content.

---

## ✅ 2. Current Migration Progress (What We Done)

The primary native merchant setup, dashboard foundation, and core brand configurations are **100% completed and fully functional**. Below is the detailed breakdown:

### 🔑 A. Authentication & Onboarding
* **Controllers & Logic**: [AuthController.php](file:///Volumes/Project/Personal%20Project/ZontroPay/PipraPay-Laravel%20%28Non%20SaaS%29/laravel-app/app/Http/Controllers/Merchant/AuthController.php) has been built with native guard support using the custom authenticatable model `ZpAdmin` mapped to the `zp_admins` database table.
* **Security & Session**: Configured `merchant` guard inside Laravel's native `config/auth.php`.
* **Guest Views**:
  * Standalone premium Sign In page (`/merchant/login`).
  * Forgot Password email request UI (`/merchant/forgot-password`).
  * Reset Password link form (`/merchant/reset-password/{token}`).
* **AJAX Parity**: Submissions are fully AJAXified, returning JSON redirects and validations with inline loading states.

### 📊 B. Shell Layout & Dashboard Template
* **Layouts**: Integrated the customized **TailAdmin** shell (`resources/views/merchant/default/layouts/app.blade.php`).
* **Sidebar & Header**: Custom [MerchantSidebarComposer.php](file:///Volumes/Project/Personal%20Project/ZontroPay/PipraPay-Laravel%20%28Non%20SaaS%29/laravel-app/app/Http/View/Composers/MerchantSidebarComposer.php) handles active merchant brands and lists them inside the sidebar dropdown.
* **Dashboard Page**: Rendered via [DashboardController.php](file:///Volumes/Project/Personal%20Project/ZontroPay/PipraPay-Laravel%20%28Non%20SaaS%29/laravel-app/app/Http/Controllers/Merchant/DashboardController.php) at `/merchant/dashboard`.
  > [!NOTE]
  > As per instructions, the dashboard UI is currently **static and template-driven**, containing placeholder charts and logs. No transaction analytics or charts are dynamic yet.

### ⚙️ C. Brand Settings Pages (SettingsController)
All brand, profile, and system configuration sections for a merchant are centralized inside [SettingsController.php](file:///Volumes/Project/Personal%20Project/ZontroPay/PipraPay-Laravel%20%28Non%20SaaS%29/laravel-app/app/Http/Controllers/Merchant/SettingsController.php) and are fully dynamic:

#### 1. General Settings
* **Path**: `/merchant/settings/general`
* **Features**: Full CRUD for basic brand attributes:
  * Brand Name & Identity
  * Support Email, Phone, Website
  * Operational Address, City, Postal Code, Country
  * Operational timezone selection (pulled from standard `DateTimeZone` lists)
  * Default system language (English, Bangla, Hindi, Urdu, Arabic)
  * Base brand currency code
  * Auto exchange rate toggles and payment tolerance margin (extremely crucial for crypto or payment drift values)

#### 2. Branding & Assets
* **Path**: `/merchant/settings/branding`
* **Features**: Form placeholders and fields to update Logo, Favicon, and visual theme settings.

#### 3. FAQ Settings
* **Path**: `/merchant/settings/faqs`
* **Features**: Extremely feature-rich AJAX-driven FAQ management engine:
  * **Interactive Datatable**: Lists all brand FAQs with search and status filters.
  * **Dynamic Pagination**: Custom AJAX paginated navigation matching the Currencies interface.
  * **Rich Modals**: Dynamic Alpine.js modal interfaces for creating and editing FAQs.
  * **Interactive Row Checkboxes & Bulk Actions**: Select multiple FAQs to activate, deactivate, or delete in bulk with standard Fetch AJAX actions.
  * **Social Fields Integration**: WhatsApp number, Telegram, Messenger, and Facebook page inputs have been integrated directly into the **General Settings** tab to unify and streamline customer connection channels.

#### 4. Currencies Settings
* **Path**: `/merchant/settings/currencies`
* **Features**: Extremely feature-rich AJAX-driven exchange engine:
  * **Interactive Datatable**: Lists all activated currencies. Includes client-side and server-side searches, limits, and order lists (making base currency always bubble to the top).
  * **Dynamic Pagination**: Built-in AJAX pagination system utilizing Eloquent's paginator rendered through custom TailAdmin structures.
  * **Global Bulk Import**: Direct REST import connecting with high-reliability GitHub currency repositories to pull all native symbols and names (e.g., `$`, `৳`, `€`) instantly.
  * **Automated Sync Engine**: Integrated fawazahmed0 Currency API endpoint to pull the latest global market rates for the active brand's base currency and bulk update the exchange rates in one-click.
  * **Single Rate Update**: Dynamic edit popup to manually override specific symbols and rate values.

#### 5. API Credentials Settings
* **Path**: `/merchant/settings/api-keys`
* **Features**: State-of-the-art developer API credentials management interface:
  * **Endpoint Quick Copy Widgets**: Live integration links (Base API URL, checkout URL, validation, refund URL) featuring real-time green clipboard toast micro-animations.
  * **Scoped Permissions**: State-of-the-art developer visual checkboxes mapped inside interactive card grids, featuring read/write/admin scope badges and clear descriptions of allowed actions.
  * **Interactive Expiry Engine**: Auto-checks credential expiration dates, formatting reactive UI badges (`active` / `inactive` / `expired`) instantly. The date input triggers the native browser date picker directly on field click/focus with smooth pointer cursor animations.
  * **Robust Modals & Bulk Actions**: Uses custom Alpine.js prompt warnings and multi-row selected bulk updates/deletions.

#### 6. Whitelisted Domains Settings
* **Path**: `/merchant/settings/domains`
* **Features**: Dynamic site checkout safety management deck:
  * **Smart Domain Normalization**: Converts incoming domain names and store URLs (e.g., `https://myawesome shop.com/checkout/`) automatically into validated hostnames (`myawesome_shop.com`).
  * **Polished AJAX Datatable**: AJAX search, horizontal limit/status filters, customizable showing info block, and customized paginated AJAX links.
  * **Unified Grid UI Parity**: Clean grid borders, checkbox selection indicators, and HSL custom-designed active/inactive badges matching FAQs and API credentials.
  * **Bulk and Individual Control**: Fully secure warning prompt dialogues for individual deletions and multiple rows checkout domains removals. Standardized dropdown widths (`260px`).

#### 7. Payment Gateways Settings
* **Path**: `/merchant/system/gateways`
* **Features**: High-performance payment processing configuration center:
  * **Modern Architecture**: Clean separation utilizing native `ZpGateway` and `ZpGatewayParameter` models, completely bypassing old procedural/admin code blocks.
  * **AJAX Creation & Setup**: Add dynamic gateway engines (bKash, Stripe, Nagad, etc.) or set up manual bank channels with instant, zero-reload AJAX callbacks.
  * **Custom Branding & Theme Customizer**: Set custom gateway logos with live previewing, and visually adjust primary, text, button, and button-text colors using reactive inline color Pickers.
  * **Transaction boundaries, limits, fees, and discounts**: Built-in money sanitization sanitizing currency values for flat rates, percentage fees, and allowed settlement currencies.
  * **Interactive Custom Modals & Vertically Stacked Card layouts**: Replaced native browser confirm/alert popups with rich Alpine-driven `<x-m::modal>` dialog confirmation cards for single gateway deletion and bulk actions (Bulk Delete, Bulk Activate, Bulk Deactivate). Restructured the Gateway Edit screen into a premium, unified stacked vertical card single-column layout (`max-w-4xl`) with branded custom SVG card headers and helper descriptions, matching General Settings UI.

---

## 🔍 3. Legacy Project Analysis & Next Modules to Move

To determine which modules/sections are next, we checkout the legacy admin project. Legacy modules are driven by:
1. Views located inside legacy view paths [resources/views/admin/pages/](file:///Volumes/Project/Personal%20Project/ZontroPay/PipraPay-Laravel%20%28Non%20SaaS%29/laravel-app/resources/views/admin/pages/).
2. Incoming actions processed by [NativeAdminActionController.php](file:///Volumes/Project/Personal%20Project/ZontroPay/PipraPay-Laravel%20%28Non%20SaaS%29/laravel-app/app/Http/Controllers/Admin/NativeAdminActionController.php).

Below is the analysis of these modules, their priority, and recommended migration details.

| Legacy Module | Legacy Views | Legacy Actions | Recommended Priority | Native Namespace |
| :--- | :--- | :--- | :--- | :--- |
| **Transactions** | `transaction/` | `transaction-list`, `transaction-delete`, `transaction-ipn` | 🔴 **High** | `Merchant\TransactionController` |
| **Payment Gateways** | `gateways/` | `gateways-list`, `gateway-create`, `gateways-delete` | 🔴 **High** | `Merchant\GatewayController` |
| **API Keys & Domains**| `domains/` | `api-list`, `api-create`, `all-domain-list`, `create-domains`| 🔴 **High** | `Merchant\ApiKeyController` |
| **Invoices** | `invoice/` | `invoice-list`, `invoice-create`, `invoice-edit`, `invoice-delete`| 🟡 **Medium** | `Merchant\InvoiceController` |
| **Payment Links** | `payment-link/`| `paymentLink-list`, `paymentLink-create`, `paymentLink-edit`| 🟡 **Medium** | `Merchant\PaymentLinkController` |
| **Customers** | `customers.blade.php`| `customer-list`, `customers-create`, `customers-edit` | 🟡 **Medium** | `Merchant\CustomerController` |
| **Staff & Roles** | `staff-management/`| `staff-management-list`, `staff-create`, `staff-permissions`| 🟢 **Low** | `Merchant\StaffController` |
| **Logs & Auditing** | `activities.blade.php`| `activities-list`, `device-list`, `sms-data-list` | 🟢 **Low** | `Merchant\LogController` |
| **System Settings** | `system-settings/`| `system-settings-update-setting`, `addons-list` | 🚫 **Do Not Move** | SuperAdmin/Root Panel Only |

---

## 🚀 4. Detailed Migration Roadmap

Based on the analysis, here is the step-by-step action plan to move these modules into the new standard standard `/merchant` native layer:

### Phase 1: Transactions Module (🔴 High Priority)
The heart of the merchant experience is seeing their payments. This must be the absolute next step.
* **Models**: Use/create modern native transactions logic (refer to `ZpTransaction.php` or legacy `PpTransaction`).
* **Controller**: Create `Merchant\TransactionController` handling `index()`, `show()`, and AJAX paginated data lists.
* **Views**:
  * `resources/views/merchant/default/pages/transactions/index.blade.php` (AJAX Datatable matching general TailAdmin layout).
  * `resources/views/merchant/default/pages/transactions/show.blade.php` (Slide-out panel or clean modal showing detailed customer, currency, gateway parameters, fees, and exchange rates).
* **Actions**:
  * Refund processing (with background confirmation Toast).
  * Manual IPN resend trigger (initiating the native `PaymentVerificationService` webhook callback).

### Phase 2: Payment Gateways Configuration (✅ 100% Completed & Migrated)
Merchants can now completely manage checkout payment gateways natively:
* **Models**: Migrated fully to standard Laravel Eloquent models `ZpGateway` and `ZpGatewayParameter` (mapping to native database structures). Removed legacy `PpGateway` references entirely from the merchant context.
* **Controller & Services**: Updated `SettingsController.php` gateway endpoints to call the newly created [GatewayMerchantActionService.php](file:///Volumes/Project/Personal%20Project/ZontroPay/PipraPay-Laravel%20%28Non%20SaaS%29/laravel-app/app/Services/Merchant/GatewayMerchantActionService.php).
* **Views**:
  * **Gateways Index**: Premium AJAX card grid showing active/inactive payment channels and supported settlement currencies. Includes rich Alpine-driven dropdown actions menus and dynamically loaded row details.
  * **Gateways Edit**: Clean visual forms to manage credentials, visual theme customization (primary, button, and text colors), custom logo uploads, and transaction boundaries (min/max allow limits, charges, and discounts).
  * **Custom Confirmation Modals**: Successfully wired `<x-m::modal>` Alpine component instances for Single Gateway Deletion and Bulk Actions, removing native `confirm()` and `alert()` triggers entirely. Dynamically compiled nested Alpine bindings using `Alpine.initTree` to preserve transition and click lifecycle actions on fetch requests.
  * **Layout Redesign**: Restructured `gateways-edit.blade.php` from two columns to a single-column, `max-w-4xl` stacked layout mimicking the General Settings hub, utilizing curated SVG card icons, description headers, and rounded layout containers.
  * **Interactive Gateway Category Cards & Searchable Dropdown**: Enhanced the gateway creation modal by offering side-by-side selectable cards (Manual Bank Transfer and Payment Gateway) equipped with dynamic active borders, tick-indicator badges, and hover animations. Selecting the payment category dynamically unfolds a beautiful Alpine-based combobox with real-time text-filtering search input, enabling instant engine selection and direct AJAX setups without any full-page reload.

### Phase 3: API Credentials, Domains, and Checkout Themes (✅ 100% Completed & Visually Polished)
All routes, controllers, views, database schema alignments, clipboard copy toast actions, Alpine.js modals, and selected bulk check actions have been fully migrated to Laravel standards inside the Merchant space. 
* **API Credentials & Whitelisted Domains**: Both have been 100% redesigned and polished to match the modern visual aesthetics, checkboxes, bulk confirmation modals, and action dropdown mechanics established by the FAQ module.
* **Checkout Themes & Dynamic Theme Settings**: Added fully standard, scanned theme scan actions, dynamic color inputs, text fields, textareas, dynamic selects, and image uploading zones into the premium TailAdmin layout with absolute isolation from the old Admin code.
* **Unified Pagination Component**: A premium, reusable Blade component `<x-pagination>` has been implemented under `resources/views/merchant/default/components/pagination.blade.php`.
* **Zero-Touch Admin Policy**: Standard Eloquent dynamic paginators and setting observers are safely intercepted and rendered inside the merchant-side `SettingsController` to feed the new pagination component. Not a single line of the legacy `app/Services/Admin/` files is touched, maintaining absolute legacy code isolation.
* **Themes List Visual Polish**: Unified preview thumbnail dimensions to exactly `240px` utilizing inline CSS overrides for guaranteed cross-browser height consistency. Rendered active badges with a solid `bg-brand-500` background, white text, a pulsing white status dot, and elegant `top-6 right-6` margins. Isolated theme preview assets using a dedicated `checkout-theme` routing channel to keep the merchant theme assets completely unaffected.

### Phase 4: Invoices & Payment Links (🟡 Medium Priority)
Enables direct billing and quick payment buttons.
* **Controller**: Create `Merchant\InvoiceController` and `Merchant\PaymentLinkController`.
* **Views**:
  * **Invoices**: Lists, creation builder (item description, quantity, price, tax, discount calculations), and automated email invoice sender.
  * **Payment Links**: Create simple payment URLs (e.g. `/payment-link/{slug}`) with a dynamic drag-and-drop form field builder (Name, Mobile, Email, custom text fields).

### Phase 5: Customer Database (🟡 Medium Priority)
Lists customers who have paid.
* **Controller**: Create `Merchant\CustomerController`.
* **Views**: AJAX lists of checkout customers, contact records, lifetime payment values, and lists of transactions matching that specific customer record.

---

## 🚫 5. Administrative Modules Excluded from Merchant Portal

While checkouts from the legacy project show `system-settings/` and `addons/` modules inside `NativeAdminActionController.php`, **they must NOT be migrated to the individual Merchant panel**.
* **Why**: PipraPay Non-SaaS is technically a brand-based multi-tenant engine where one admin/merchant owns their brand, but system configurations (running CRON command generator, core updates, installing modules, Telegram addon management) belong strictly to the **SuperAdmin System Panel**.
* **Rule**: Keep `system-settings`, `addons`, and `merchants` (super-merchant list) strictly outside of `/merchant` routes to ensure complete panel security.

---

## 📅 6. Immediate Next Steps

1. **Dashboard Analytics (Static to Dynamic)**: Map the static dashboard template at `/merchant/dashboard` to standard `ZpTransaction` query sums to display live counts:
   * Total Completed Transaction Volume (in default brand currency)
   * Total Completed Count, Pending Count, Failed Count
   * Live transaction chart (AJAX-loaded monthly transaction summary using Chart.js)
2. **Begin Phase 1 (Transactions)**: Create the routes for transactions and start building the AJAX datatable for transaction records.
3. **Upgrade Sidebar Navigation (Deferred / On Hold)**: As per design feedback, the generic placeholder template menu items inside [sidebar.blade.php](file:///Volumes/Project/Personal%20Project/ZontroPay/PipraPay-Laravel%20%28Non%20SaaS%29/laravel-app/resources/views/merchant/default/partials/sidebar.blade.php) are preserved. All advanced brand configurations remain centralized inside the unified settings hub.
