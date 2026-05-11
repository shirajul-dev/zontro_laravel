# PipraPay Admin Panel Migration Plan (Native Laravel)

This document outlines the professional and safe migration of the PipraPay Admin Panel to a native Laravel-Blade architecture.

## Core Objectives
1. **Security**: Implement CSRF protection and Laravel Auth for the admin layer.
2. **Performance**: Native Blade rendering and optimized Eloquent queries.
3. **User Experience**: Maintain the SPA-like (no-reload) navigation using modern HTMX/Turbo patterns.
4. **Themability**: Design the admin to support dynamic skins/themes in the future.

---

## Phase 1: Admin Infrastructure (Foundation)
- [x] Create `AdminBaseController` to handle common admin logic.
- [x] Implement `AdminThemeService` to support dynamic admin layout selection.
- [x] Create the **Main Master Layout** (`admin-layout.blade.php`) with Sidebar and Header.
- [x] Set up **HTMX/Turbo integration** for smooth, no-reload content loading.
- [x] Implement the **Admin Middleware** for secure session management.

## Phase 2: Core Dashboard & Metrics
- [x] Migrate the **Dashboard Overview** (Stats cards, Chart.js integrations).
- [x] Port the **Recent Activities** and **System Notifications** logic.
- [x] Implement the "Quick Action" menus.

## Phase 3: Transaction & Invoice Management
- [x] Migrate the **Transaction List** with advanced filtering and pagination.
- [x] Port the **Transaction Detail View** (Edit, Approve, Refund, Cancel actions).
- [x] Migrate the **Invoice List** and **Invoice Creator** tool.
- [x] Port the **Public Payment Link** management interface.

## Phase 4: Gateway & System Settings
- [x] Migrate the **Gateway Management** (Enable/Disable, Config drivers).
- [x] Port the **Brand Settings** (Branding, SEO, Support info).
- [x] Migrate **Currency & Exchange Rate** settings.
- [x] Port **API & Webhook** configuration.

## Phase 5: Brand & Staff Management
- [x] Migrate **Brand Management** (Create/Edit/Delete brands).
- [x] Port the **Staff Management** system (RBAC - Role Based Access Control).
- [x] Port the **Customer Directory**.

## Phase 6: Final Cleanup & Testing
- [/] Conduct deep security audit (XSS/CSRF).
- [ ] Remove legacy `pp-admin` bridge files.
- [x] Finalize the "Admin Theme" development documentation.

---

## Migration Safety Rules
1. **Parallel Execution**: Keep the legacy admin accessible during migration for verification.
2. **Data Integrity**: Use Eloquent models for all updates to ensure database consistency.
3. **No Placeholders**: Every migrated page must be fully functional before moving to the next.
4. **Error Handling**: Use Laravel's native Exception Handler for professional error reporting.
