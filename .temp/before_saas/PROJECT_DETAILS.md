# Project Documentation: PipraPay / ZontroPay (Laravel Version)

## 1. Project Overview
PipraPay is a comprehensive payment management system built on the Laravel framework. It is designed to handle multi-brand payment processing, invoicing, payment links, and transaction monitoring. The system features a hybrid architecture that bridges modern Laravel development with a legacy UI/logic structure, allowing for a robust yet flexible environment.

### Core Technologies
*   **Backend**: Laravel 10/11+ (PHP 8.x)
*   **Database**: MySQL/MariaDB with `pp_` prefix by default.
*   **Frontend**: Vanilla CSS, JavaScript (leveraging a legacy template system), and Vite for asset bundling.
*   **Architecture**: Hybrid (Laravel Core + Legacy UI Bridge).

---

## 2. Full Project Structure Breakdown

### 📂 Root Directory
*   `app/`: Core Laravel application logic (Models, Controllers, Services, Providers).
*   `bootstrap/`: Laravel's initialization files.
*   `config/`: Configuration files for PipraPay, database, and Laravel services.
*   `database/`: Migrations, seeders, and factories.
*   `pp-content/`: **Legacy Core**. Contains the legacy functions, includes, and themes that the Laravel app bridges to.
*   `public/`: Publicly accessible assets (images, CSS, JS).
*   `resources/`: Laravel views (Blade templates), including the `legacy` bridge views.
*   `routes/`: Entry points for the application (`web.php` for UI).
*   `storage/`: Logs, file uploads, and cached templates.

### 📂 Key Application Folders (`app/`)
*   `Http/Controllers/Admin/`: Contains `NativeAdminPageController` (renders views) and `NativeAdminActionController` (handles AJAX/POST actions).
*   `Http/Controllers/Payment/`: Manages the checkout flow, IPNs, and invoices.
*   `Models/`: Eloquent models (e.g., `PpTransaction`, `PpBrand`, `PpGateway`).
*   `Services/Admin/`: Encapsulated business logic for admin operations (e.g., `TransactionAdminActionService`).

---

## 3. Modules & Features Breakdown

### A. Dashboard Module
The central hub for system monitoring.
*   **Transaction Statistics**: Real-time charts showing total, completed, and pending transactions.
*   **Gateway Statistics**: Performance breakdown per payment gateway.
*   **Activity Logs**: System-wide event tracking.

### B. Brand & Merchant Management
Supports multi-tenancy within a single installation.
*   **Brand Listing**: View and manage all registered brands.
*   **Brand Switching**: Admins can switch context to manage specific brand data (currencies, logos, settings).
*   **Permissions**: Granular control over which admin can access which brand.

### C. Payment Processing
The core engine of the application.
*   **Gateways**: Integration with multiple providers (e.g., SSLCommerz, bKash, Nagad, Stripe, PayPal).
*   **Checkout Flow**: Secure, brand-customized checkout pages for customers.
*   **IPN (Instant Payment Notification)**: Handles background callbacks from payment providers to update transaction statuses automatically.
*   **Payment Links**: Generate shareable links for specific amounts or items. Includes support for custom fields and multiple currencies.

### D. Invoicing System
A professional billing solution.
*   **Invoice Creation**: Generate detailed invoices with multiple line items.
*   **Manage Status**: Track unpaid, paid, partially paid, and cancelled invoices.
*   **Customer Portal**: Secure links for customers to view and pay invoices.

### E. Customer & Transaction Management
*   **Customer Database**: Centralized storage of customer information and their payment history.
*   **Transaction Logs**: Deep-dive into every transaction, including raw IPN data, browser logs, and status history.

### F. SMS Data & Device Integration
A unique feature for manual payment verification.
*   **Device Management**: Connect mobile devices to the system.
*   **SMS Monitoring**: Capture incoming SMS notifications (e.g., from mobile banking apps) and automatically match them with pending transactions.

### G. Staff & Permission Management
*   **Admin Users**: Create and manage administrative accounts.
*   **Role-Based Access (RBAC)**: Detailed permission sets for various modules (e.g., "Can view transactions but cannot delete").
*   **2FA (Two-Factor Authentication)**: Mandatory or optional security layer using email/OTP.

### H. API & Integration Layer
The system provides a robust API for external integrations.
*   **Checkout API**: Allows external sites to initiate payments. Supports various checkout types (direct, hosted, etc.).
*   **Verify Payment API**: A secure endpoint to check the status of a transaction using a reference ID (`pp_id`).
*   **IPN Handlers**: Each gateway has a dedicated IPN listener for asynchronous status updates.
*   **API Scopes**: API keys can be restricted to specific actions (e.g., only verification, only checkout).

### I. System Settings & Utilities
*   **General Settings**: Application name, timezones, logo, and favicon.
*   **Currency Management**: Multi-currency support with automated rate synchronization.
*   **Cron Jobs**: Management of scheduled tasks (e.g., cleaning logs, syncing rates).
*   **Addon System**: Extend functionality with plug-and-play modules.
*   **Update Center**: Check for and install system updates directly from the dashboard.

---

## 4. How Everything is Connected (Step-by-Step Flow)

### 1. Request Handling (The Bridge)
When a user visits the admin panel:
1.  Laravel's `web.php` captures the route (e.g., `/admin/transactions`).
2.  `NativeAdminPageController` resolves the request and checks authentication.
3.  It sets up a "Legacy Environment" by loading functions from `app/Support/zp-functions.php`.
4.  It renders a Blade view located in `resources/views/legacy/pp-content/pp-admin/pp-root/`.

### 2. Form Submissions (The Action Dispatcher)
When an admin performs an action (e.g., creating a brand):
1.  A POST request is sent to `/{admin}/action`.
2.  `NativeAdminActionController@handle` receives the `action` parameter.
3.  The controller dispatches the task to a specific **Service** (e.g., `BrandAdminActionService`).
4.  The service interacts with **Eloquent Models** to update the database.
5.  A JSON response is returned to the UI for instant feedback.

### 3. Payment Flow
1.  **Initiation**: A payment link or invoice is accessed by a customer.
2.  **Checkout**: `CheckoutController` presents the payment options based on the brand's configured gateways.
3.  **Payment**: The customer is redirected to the gateway.
4.  **Callback**: After payment, the gateway sends an IPN to `/ipn/{gateway_id}`.
5.  **Verification**: `IpnController` verifies the signature and updates the `PpTransaction` status.

---

## 5. Security & Maintenance
*   **CSRF Protection**: Native Laravel CSRF protection is enforced across all forms.
*   **Session Security**: Ability to view and terminate other active browser sessions.
*   **Environment Isolation**: Sensitive credentials are stored in `.env`.
*   **Demo Mode**: Built-in restriction mode for public demonstrations.

---

## 6. Menu & Navigation Breakdown
Inside the Admin Panel, the sidebar typically contains:
*   **Dashboard**: System Overview.
*   **Transactions**: All Logs, Pending, Success, Failed.
*   **Payment Links**: Manage & Create.
*   **Invoices**: List, Create, Settings.
*   **Brands**: Management & Settings.
*   **Gateways**: Configuration.
*   **Customers**: Management.
*   **Reports**: Financial Analytics.
*   **Staff**: Users & Permissions.
*   **Devices & SMS**: Monitoring tools.
*   **System**: Settings, Currencies, Updates, Cron.
