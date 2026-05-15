# PipraPay Native Laravel Migration Blueprint (Parallel Architecture)

## 1. Overview
This document serves as the master guide for the secondary, high-fidelity migration of PipraPay. The goal is to build a 100% Laravel-standard application layer that runs in parallel with the current legacy-bridge system without touching or breaking any existing code.

### The "Parallel" Principle
- **Legacy Layer**: Lives under `/admin`, uses `pp_` database prefix, and `Pp*` models. It remains 100% untouched.
- **Native Layer**: Lives under `/merchant` (and other standard paths), uses `zp_` database prefix, and `Zp*` models. It is built using modern Laravel best practices.

---

## 2. Database Strategy: The Dual-Prefix Approach
To ensure zero interference, we are moving from `pp_` to `zp_`.

| Feature | Legacy (Keep) | Native (New) |
| :--- | :--- | :--- |
| **Prefix** | `pp_` | `zp_` |
| **Models** | `App\Models\PpTransaction` | `App\Models\ZpTransaction` |
| **Migrations** | `create_piprapay_schema.php` | Dedicated granular migrations per table |
| **Timestamps** | `string(20)` (created_date) | Laravel `timestamps()` (created_at/updated_at) |

### Migration Workflow
1. Create a new migration for a `zp_` table (e.g., `zp_admins`).
2. Define the schema using Laravel standard types (increments, timestamps, foreign keys).
3. If data needs to be synced from `pp_` to `zp_`, use a Seeder or a dedicated Sync Command.

---

## 3. Routing & Namespace Architecture
We are separating the "Bridge" logic from the "Native" logic at the directory level.

### Controllers
- **Legacy**: `App\Http\Controllers\Admin\NativeAdminActionController` (The "Mega Controller").
- **Native**: `App\Http\Controllers\Merchant\*` (Dedicated controllers like `Merchant\AuthController`, `Merchant\DashboardController`).

### Route Groups
```php
// Existing Legacy Bridge (Do Not Touch)
Route::group(['prefix' => 'admin'], function() { ... });

// New Native Standard (Step-by-Step)
Route::group(['prefix' => 'merchant', 'as' => 'merchant.'], function() {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    Route::middleware(['auth:merchant'])->group(function() {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});
```

---

## 4. UI/UX Philosophy: SPA-Lite Experience
The Native layer will move away from full-page reloads for actions.

- **Frontend Tech**: Vanilla JS (Fetch API) or Alpine.js for lightweight reactivity.
- **Interactions**:
    - **Loading States**: Buttons must show a spinner or loading text upon click.
    - **Feedback**: All requests must return JSON and show professional Toast notifications (e.g., Toastr or a custom UI component).
    - **Error Handling**: 422 validation errors must be displayed inline or via toasts without reloading the page.

---

## 5. Implementation Roadmap (Step-by-Step)

### Phase 1: Authentication (The Foundation)
1. **Database**: Create `zp_admins` table migration.
2. **Model**: Create `ZpAdmin` model with `HasApiTokens` and `Authenticatable`.
3. **Auth Guard**: Define a new `merchant` guard in `config/auth.php`. 
4. **Views**: Build the blade file with a sinle text and the login ui and ask for design the developer manually., standalone login page at `/merchant/login`.
5. **Logic**: Implement `AuthController` with JSON response support and loading-state UI.

### Phase 2: Dashboard & Shell
1. **Layout**: Create a new `resources/views/merchant/layouts/app.blade.php` (completely different from the legacy layout).
2. **Dashboard UI**: Ask the user to Design dashboard ui by the developer manually.
2. **Dashboard**: Build the `DashboardController` using `Zp*` models to fetch data.

### Phase 3: Module Migration (One by One)
- Transactions
- Customers
- Gateways
- System Settings

---

## 6. Guidelines for Future AI Agents
1. **NEVER** modify any file prefixed with `Pp` or any code inside `App\Http\Controllers\Admin`.
2. **ALWAYS** write new logic inside `App\Http\Controllers\Merchant` or `App\Http\Controllers\Api\v2`.
3. **ALWAYS** use `Zp*` models for the new migration.
4. **UI**: Ensure after create all blade file for ui ask the developer manually to implement ui, then ai agent will implement logic. (loading, toasts, data fetch based on that ui).
5. **Standard**: Follow PSR-12, use strict typing, and favor Dependency Injection over globals.
