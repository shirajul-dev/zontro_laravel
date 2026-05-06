# PipraPay Theme Development Guide (Native Blade)

Welcome to the PipraPay Theme Development Guide. This document explains how to create modern, native Laravel-Blade themes for the PipraPay platform.

## 1. Directory Structure
Themes are located in: `/pp-content/pp-modules/pp-themes/{theme-slug}/`

A standard theme should have the following files:
- `class.php`: The main theme configuration and logic class.
- `checkout.blade.php`: The main checkout page (gateway selection).
- `gateway.blade.php`: The payment gateway processing page.
- `checkout-status.blade.php`: The success/fail/pending status page.
- `invoice.blade.php`: The public invoice view.
- `payment-link.blade.php`: The public payment link landing page.
- `assets/`: A directory for CSS, JS, and Images.

---

## 2. Theme Logic (`class.php`)
Every theme must have a class named `{Slug}Theme`. 
Example for a theme named `commerz`:
```php
class CommerzTheme {
    public function info() {
        return [
            'title' => 'Commerz Theme',
            'logo'  => 'assets/logo.jpg'
        ];
    }

    public function fields() {
        return [
            ['name' => 'primary_color', 'label' => 'Primary Color', 'type' => 'color', 'value' => '#000000'],
            // Add more dynamic settings here...
        ];
    }
}
```

---

## 3. Using Blade Templates
PipraPay automatically resolves Blade templates from your theme folder.

### Data Injection
The following variables are automatically passed to your `.blade.php` files:
- `$pageData`: The entire data array (for legacy hook compatibility).
- `$brand`: Array containing brand name, logo, support info, and locale.
- `$options`: Array of dynamic settings defined in your `class.php`.
- `$lang`: Array of translated strings based on the user's language.
- `$transaction`: (In checkout/gateway) Current transaction details.
- `$invoice`: (In invoice) Current invoice details.

### Standard Hooks (Required)
To ensure compatibility with plugins and gateways, you **must** include these hooks:
- `{!! pp_assets('head') !!}`: Inside your `<head>` tag.
- `{!! pp_assets('footer') !!}`: Before the closing `</body>` tag.
- `{!! pp_renderFormFields('type', $pageData) !!}`: To render custom fields in forms.

---

## 4. Asset Management
Do not hardcode paths to assets. Use the `pp_theme_asset()` helper:
```html
<link rel="stylesheet" href="{{ pp_theme_asset('css/style.css') }}">
<script src="{{ pp_theme_asset('js/main.js') }}"></script>
```

---

## 5. Design Guidelines
- **Use CSS Variables**: Utilize the `$options` data to allow users to customize colors from the admin panel.
- **Responsive Design**: Ensure your theme works on all device sizes.
- **No Global Dependencies**: Keep all styles and scripts within your theme folder to ensure portability.
