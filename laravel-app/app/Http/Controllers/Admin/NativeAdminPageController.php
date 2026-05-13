<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasLegacyEnvironment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

if (!defined('PipraPay_INIT')) {
    define('PipraPay_INIT', true);
}

// Load legacy functions once
$functionsPath = base_path('pp-content/pp-include/pp-functions.php');
if (file_exists($functionsPath)) {
    require_once $functionsPath;
}

class NativeAdminPageController extends Controller
{
    use HasLegacyEnvironment;
    public function shell(Request $request): View|RedirectResponse
    {
        return $this->renderShellPage($request);
    }

    public function page(Request $request, ?string $pageName = null): View|RedirectResponse
    {
        $pageName = trim((string) $pageName, '/');

        if ($pageName === '') {
            return $this->renderShellPage($request);
        }

        $info = $this->resolvePageInfo($pageName);
        if ($info === null) {
            abort(404);
        }

        // Sync parameters from URL segments (if any) to $_GET
        foreach ($info['injected_params'] as $key => $value) {
            $_GET[$key] = $value;
            $_REQUEST[$key] = $value;
        }

        return $this->renderPage($request, $info['view_name']);
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        $info = $this->resolvePageInfo('dashboard');
        if ($info === null) {
            abort(404);
        }

        return $this->renderPage($request, $info['view_name']);
    }

    public function myAccount(Request $request): View|RedirectResponse
    {
        $info = $this->resolvePageInfo('my-account');
        if ($info === null) {
            abort(404);
        }

        return $this->renderPage($request, $info['view_name']);
    }

    private function renderShellPage(Request $request): View|RedirectResponse
    {
        if (!$this->isAuthenticated()) {
            return $this->redirectToAuth();
        }

        if ($this->isTwoFactorPending()) {
            return redirect()->to(url('2fa'));
        }

        return view('legacy.pp-content.pp-admin.index', $this->viewData($request));
    }

    private function renderPage(Request $request, string $viewName): View|RedirectResponse
    {
        if (!$this->isAuthenticated()) {
            return $this->redirectToAuth();
        }

        if ($this->isTwoFactorPending()) {
            return redirect()->to(url('2fa'));
        }

        // Sync Laravel Request to PHP Superglobals for legacy compatibility
        $this->syncRequestToSuperglobals($request);

        $data = $this->viewData($request);

        if ($request->boolean('content')) {
            return view($viewName, $data);
        }

        return view('legacy.pp-content.pp-admin.index', $data);
    }

    /**
     * Synchronizes Laravel Request data to PHP superglobals ($_GET, $_POST, $_REQUEST).
     * This is critical for legacy code parity.
     */
    private function syncRequestToSuperglobals(Request $request): void
    {
        foreach ($request->query() as $key => $value) {
            $_GET[$key] = $value;
            $_REQUEST[$key] = $value;
        }

        foreach ($request->post() as $key => $value) {
            $_POST[$key] = $value;
            $_REQUEST[$key] = $value;
        }

        // Specifically handle legacy 'params' expectation
        if (!isset($_POST['params'])) {
            $_POST['params'] = json_encode($request->all());
        }
    }

    private function resolvePageInfo(string $pageName): ?array
    {
        $segments = explode('/', trim($pageName, '/'));
        $count = count($segments);

        // Try matching from longest to shortest path
        for ($i = $count; $i > 0; $i--) {
            $baseSegments = array_slice($segments, 0, $i);
            $extraSegments = array_slice($segments, $i);

            $path = implode('.', $baseSegments);
            $candidates = [
                'legacy.pp-content.pp-admin.pp-root.' . $path,
                'legacy.pp-content.pp-admin.pp-root.' . $path . '.index',
            ];

            foreach ($candidates as $candidate) {
                if (view()->exists($candidate)) {
                    // Map extra segments to generic parameter keys if they exist
                    $injected = [];
                    if (!empty($extraSegments)) {
                        $injected['id'] = $extraSegments[0];
                        $injected['slug'] = $extraSegments[0];
                        $injected['ref'] = $extraSegments[0];

                        // Map multiple segments if needed
                        foreach ($extraSegments as $idx => $val) {
                            $injected['segment_' . ($idx + 1)] = $val;
                        }
                    }

                    return [
                        'view_name' => $candidate,
                        'injected_params' => $injected
                    ];
                }
            }
        }

        return null;
    }

    private function viewData(Request $request): array
    {
        $legacy = $this->setupLegacyGlobals($request);
        $admin = $legacy['admin'];
        $brand = $legacy['brand'];

        return [
            'params' => $request->all(),
            'csrfToken' => $this->csrfToken(),
            'csrf_token' => $this->csrfToken(),
            'demoMode' => (bool) config('piprapay.demo_mode', false),
            'site_url' => rtrim(url('/'), '/') . '/',
            'path_admin' => trim((string) config('piprapay.paths.admin', 'admin'), '/'),
            'path_cron' => trim((string) config('piprapay.paths.cron', 'cron'), '/'),
            'piprapay_favicon' => asset('assets/images/favicon-light.png'),
            'piprapay_logo_light' => asset('assets/images/logo-light.png'),
            'db_prefix' => env('DB_PREFIX', 'pp_'),
            'global_brand_currency_code' => $brand !== null ? (string) ($brand->currency_code ?? 'BDT') : 'BDT',
            'global_brand_currency_symbol' => $brand !== null ? (string) ($brand->currency_symbol ?? $brand->currency_code ?? 'BDT') : 'BDT',
            'path_invoice' => trim((string) config('piprapay.paths.invoice', 'invoice'), '/'),
            'path_payment' => trim((string) config('piprapay.paths.payment', 'payment'), '/'),
            'path_payment_link' => trim((string) config('piprapay.paths.payment_link', 'payment-link'), '/'),
            'path_payment_link_default' => trim((string) config('piprapay.paths.payment_link', 'payment-link'), '/') . '/default',
            'global_user_login' => $this->isAuthenticated(),
            'global_user_2fa' => $this->isTwoFactorPending(),
            'global_user_response' => $legacy['global_user_response'],
            'global_response_brand' => $legacy['global_response_brand'],
            'global_response_permission' => $legacy['global_response_permission'],
            'piprapay_current_version' => $legacy['piprapay_current_version'],
        ];
    }


    private function csrfToken(): string
    {
        return function_exists('csrf_token') ? csrf_token() : '';
    }

    private function isAuthenticated(): bool
    {
        return session('piprapay.authenticated') === true;
    }

    private function isTwoFactorPending(): bool
    {
        return session('piprapay.twofa_pending') === true;
    }

    private function redirectToAuth(): RedirectResponse
    {
        if ($this->isTwoFactorPending()) {
            return redirect()->to(url('2fa'));
        }

        return redirect()->to(url('login'));
    }
}
