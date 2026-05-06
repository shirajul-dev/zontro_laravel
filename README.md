# PipraPay - Modern Payment Gateway (Laravel Non-SaaS)

![PipraPay Architecture](https://img.shields.io/badge/Architecture-Hybrid_Bridge-blue)
![Laravel Version](https://img.shields.io/badge/Laravel-10.x/11.x-red)
![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4)

PipraPay is a high-performance, self-hosted payment gateway platform. This project represents the systematic modernization of a legacy procedural PHP application into a robust, secure, and scalable **native Laravel** ecosystem. 

It is designed to serve as a standalone payment processor for merchants, offering seamless checkout links, invoice generation, dynamic theme rendering, and native REST APIs, all while maintaining strict backward compatibility with existing legacy plugins and gateways via a custom "Hybrid Bridge" architecture.

## ✨ Core Features

*   **Hybrid Bridge Architecture**: Securely runs legacy procedural code within Laravel's modern request lifecycle, preventing downtime during the active migration process.
*   **Native REST API**: Offers lightning-fast `/api/v1/checkout`, `/api/v1/balance`, and transaction endpoints fully secured with scope-based API keys.
*   **Eloquent ORM Data Layer**: Completely mapped legacy database schema utilizing Laravel Migrations and Eloquent Models (`PpTransaction`, `PpGateway`, etc.) for secure, object-oriented data interaction.
*   **Dynamic Theme Engine**: A powerful Blade-powered theme system (`twenty-six`, `commerz`) that seamlessly injects legacy configuration payloads into modern, responsive checkout UIs.
*   **Extensive Payment Gateways**: Support for 40+ local and international payment gateways including MFS (Nagad, Rocket) and Bank integrations.

## 📁 Project Structure

*   `/laravel-app/`: The core modern application built on Laravel.
    *   `app/Models/`: Native Eloquent schema mapping.
    *   `app/Services/`: Core business logic (e.g., `PaymentService`, `ThemeService`, `LegacyRuntimeService`).
    *   `app/Http/Controllers/Api/`: Native REST API controllers.
*   `/laravel-app/pp-content/`: Legacy integration directory.
    *   `pp-modules/pp-themes/`: Blade templates for checkouts and invoices.
    *   `pp-modules/pp-gateways/`: Payment gateway processing scripts.
    *   `pp-include/`: Procedural helper scripts (`pp-functions.php`) heavily refactored to utilize Eloquent ORM.
*   `/SDK/`: Integration SDKs and plugins for platforms like WooCommerce.

## 🛠️ Development & Migration Workflow

We are actively migrating this project in distinct phases. Our workflow is managed by AI Agents (like Antigravity) strictly adhering to the following rules:

1.  **Professional Git Management**: All major milestones and module completions are committed with professional, highly descriptive Git messages.
2.  **Pre-Flight Security Commits**: Before touching critical or potentially breaking modules (e.g., core database migrations, routing overhauls), a mandatory security commit is made documenting exactly *why* the upcoming change is high-risk.
3.  **Living Documentation**: The architectural state of the project is continuously updated inside `laravel-app/breakdown.md` to ensure any new developer or AI agent understands the exact current state of the integration flow.

## 🚀 Getting Started

1.  Clone the repository.
2.  Navigate to `/laravel-app` and install dependencies via `composer install`.
3.  Copy `.env.example` to `.env` and configure your database and APP_URL.
4.  Run `php artisan key:generate`.
5.  Execute migrations via `php artisan migrate` (Note: Schema is predefined in `2026_04_17_000100_create_piprapay_schema.php`).
6.  Serve the application `php artisan serve`.

---

*This application is strictly Non-SaaS. It is intended for single-tenant installation by independent merchants or payment service providers.*
