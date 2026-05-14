<?php

namespace App\Services\Theme;

use App\Support\LegacyModuleGuard;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ThemeService - WordPress-style Dynamic Theme Engine
 *
 * Resolves the active theme for a brand, loads its class from the
 * pp-modules/pp-themes/{slug}/ directory, and delegates rendering
 * to the theme's appropriate method (renderPaymentLinkDefault, renderPaymentLink, etc.)
 *
 * The theme PHP templates run in a fully-bootstrapped legacy environment
 * (pp-functions.php loaded, globals set) so they continue to work untouched.
 */
class ThemeService
{
    private string $legacyRoot;
    private bool $bootstrapped = false;

    public function __construct()
    {
        $this->legacyRoot = base_path();
    }

    /**
     * Register a view namespace for the current theme.
     * This allows us to use view('theme::filename') to load theme-specific Blade templates.
     */
    private function registerThemeViewNamespace(string $slug): void
    {
        $themePath = resource_path('views/theme/' . $slug);
        view()->addNamespace('theme', $themePath);
    }

    // ─────────────────────────────────────────────
    // Public Render Methods (one per page type)
    // ─────────────────────────────────────────────

    public function renderPaymentLinkDefault(Request $request, string $brandId): Response
    {
        $this->bootstrap($request);

        global $db_prefix;

        $params = [':brand_id' => $brandId];
        $response_brand = json_decode(getData($db_prefix . 'brands', 'WHERE brand_id = :brand_id', '* FROM', $params), true);

        if ($response_brand['status'] !== true) {
            return $this->notFound();
        }

        $themeSlug = $response_brand['response'][0]['theme'] ?? 'twenty-six';
        $theme     = $this->loadTheme($themeSlug);

        if ($theme === null) {
            return response('Invalid theme slug', 403);
        }

        $brandRow = $response_brand['response'][0];
        $language = $this->resolveLanguage($theme, $brandRow);
        $options  = $this->resolveOptions($theme, $themeSlug, $brandRow['brand_id']);
        $lang     = $this->buildLang($theme, $language);

        $paymentLinkInfo = [
            'pid'      => $brandRow['brand_id'],
            'currency' => (($v = get_env('payment-link-default-currency', $brandRow['brand_id'])) && $v !== '--') ? $v : $brandRow['currency_code'],
            'brandId'  => $brandRow['brand_id'],
        ];

        $brandInfo = $this->buildBrandInfo($brandRow, $language);

        // Set global so pp_assets() can call theme head()/footer() hooks
        $GLOBALS['global_response_brand'] = $response_brand;

        $pageData = [
            'paymentLink' => $paymentLinkInfo,
            'brand'       => $brandInfo,
            'options'     => $options,
            'lang'        => $lang,
            'themeSlug'   => $themeSlug,
        ];

        $this->registerThemeViewNamespace($themeSlug);

        if (view()->exists('theme::payment-link-default')) {
            $viewData = $pageData;
            $viewData['pageData'] = $pageData;
            return response(view('theme::payment-link-default', $viewData)->render());
        }

        return $this->capture(function () use ($theme, $pageData) {
            $theme->renderPaymentLinkDefault($pageData);
        });
    }

    public function renderPaymentLink(Request $request, string $ref): Response
    {
        $this->bootstrap($request);

        global $db_prefix;

        $params = [':ref' => $ref];
        $response_payment_link = json_decode(getData($db_prefix . 'payment_link', 'WHERE ref = :ref', '* FROM', $params), true);

        if ($response_payment_link['status'] !== true) {
            return $this->notFound();
        }

        $paymentRow = $response_payment_link['response'][0];

        $params         = [':brand_id' => $paymentRow['brand_id']];
        $response_brand = json_decode(getData($db_prefix . 'brands', 'WHERE brand_id = :brand_id', '* FROM', $params), true);

        if ($response_brand['status'] !== true) {
            return $this->notFound();
        }

        $themeSlug = $response_brand['response'][0]['theme'] ?? 'twenty-six';
        $theme     = $this->loadTheme($themeSlug);

        if ($theme === null) {
            return response('Invalid theme slug', 403);
        }

        $brandRow = $response_brand['response'][0];
        $language = $this->resolveLanguage($theme, $brandRow);
        $options  = $this->resolveOptions($theme, $themeSlug, $brandRow['brand_id']);
        $lang     = $this->buildLang($theme, $language);

        // Determine status (handle expiry)
        if ($paymentRow['expired_date'] === '--') {
            $status = $paymentRow['status'];
        } else {
            $status = isExpired($paymentRow['expired_date']) ? 'expired' : $paymentRow['status'];
        }

        $tz = ($brandRow['timezone'] === '--' || $brandRow['timezone'] === '') ? 'Asia/Dhaka' : $brandRow['timezone'];

        $product_info = json_decode($paymentRow['product_info'] ?? '{}', true) ?: [];

        $paymentLinkInfo = [
            'pid'          => $paymentRow['ref'],
            'iid'          => $paymentRow['ref'],
            'status'       => $status,
            'currency'     => $paymentRow['currency'],
            'total'        => money_round($paymentRow['amount']),
            'quantity'     => money_sanitize($paymentRow['quantity']),
            'expired_date' => ($paymentRow['expired_date'] === '' || $paymentRow['expired_date'] === '--') ? '--' : convertUTCtoUserTZ($paymentRow['expired_date'], $tz, 'M d, Y'),
            'created_date' => convertUTCtoUserTZ($paymentRow['created_date'], $tz, 'M d, Y'),
            'updated_date' => convertUTCtoUserTZ($paymentRow['updated_date'], $tz, 'M d, Y'),
            'product'      => [
                'title'       => $product_info['title']       ?? 'Product',
                'description' => $product_info['description'] ?? null,
            ],
            'brandId'      => $paymentRow['brand_id'],
        ];

        // Custom fields
        $customFields = [];
        $params       = [':paymentLinkID' => $paymentRow['ref']];
        $response_fields = json_decode(getData($db_prefix . 'payment_link_field', 'WHERE paymentLinkID = :paymentLinkID', '* FROM', $params), true);
        if ($response_fields['status'] === true) {
            foreach ($response_fields['response'] as $row) {
                $inputOptions = [];
                if (in_array($row['formType'], ['select', 'file', 'checkbox', 'radio']) && $row['value'] !== '--') {
                    $inputOptions = array_map('trim', explode(',', $row['value']));
                }
                $customFields[] = [
                    'type'     => $row['formType'],
                    'name'     => strtolower(preg_replace('/[^a-z0-9_]/i', '_', $row['fieldName'])),
                    'label'    => $row['fieldName'],
                    'options'  => $inputOptions,
                    'required' => $row['required'],
                ];
            }
        }
        $paymentLinkInfo['fields'] = $customFields;

        $brandInfo = $this->buildBrandInfo($brandRow, $language);

        // Set global so pp_assets() can call theme head()/footer() hooks
        $GLOBALS['global_response_brand'] = $response_brand;

        $pageData = [
            'paymentLink' => $paymentLinkInfo,
            'brand'       => $brandInfo,
            'options'     => $options,
            'lang'        => $lang,
            'themeSlug'   => $themeSlug,
        ];

        $this->registerThemeViewNamespace($themeSlug);

        if (view()->exists('theme::payment-link')) {
            $viewData = $pageData;
            $viewData['pageData'] = $pageData;
            return response(view('theme::payment-link', $viewData)->render());
        }

        return $this->capture(function () use ($theme, $pageData) {
            $theme->renderPaymentLink($pageData);
        });
    }

    public function renderCheckout(Request $request, string $paymentId): Response
    {
        $this->bootstrap($request);

        // Legacy helpers like pp_checkout_address() depend on this global.
        $GLOBALS['paymentID124123412'] = $paymentId;

        global $db_prefix;

        $params             = [':ref' => $paymentId];
        $response_transaction = json_decode(getData($db_prefix . 'transaction', 'WHERE ref = :ref', '* FROM', $params), true);

        if ($response_transaction['status'] !== true) {
            return $this->notFound();
        }

        $transactionRow = $response_transaction['response'][0];
        $params         = [':brand_id' => $transactionRow['brand_id']];
        $response_brand = json_decode(getData($db_prefix . 'brands', 'WHERE brand_id = :brand_id', '* FROM', $params), true);

        if ($response_brand['status'] !== true) {
            return $this->notFound();
        }

        $themeSlug = $response_brand['response'][0]['theme'] ?? 'twenty-six';
        $theme     = $this->loadTheme($themeSlug);

        if ($theme === null) {
            return response('Invalid theme slug', 403);
        }

        $brandRow = $response_brand['response'][0];
        $language = $this->resolveLanguage($theme, $brandRow);
        $options  = $this->resolveOptions($theme, $themeSlug, $brandRow['brand_id']);
        $lang     = $this->buildLang($theme, $language);

        $tz       = ($brandRow['timezone'] === '--' || $brandRow['timezone'] === '') ? 'Asia/Dhaka' : $brandRow['timezone'];
        $customer = json_decode($transactionRow['customer_info'] ?? '{}', true) ?: [];

        $response_gateway = json_decode(getData($db_prefix . 'gateways', ' WHERE brand_id ="' . $brandRow['brand_id'] . '" AND gateway_id = "' . $transactionRow['gateway_id'] . '"'), true);
        $gateway          = $response_gateway['response'][0]['display'] ?? '';

        if ($transactionRow['status'] === 'initiated') {
            $finalUrl = '--';
        } elseif (empty($transactionRow['return_url']) || $transactionRow['return_url'] === '--') {
            $finalUrl = '--';
        } else {
            $finalUrl = addQueryParams($transactionRow['return_url'], [
                'pp_status'       => $transactionRow['status'],
                'transaction_ref' => $transactionRow['ref'],
            ]);
        }

        $response_faq = json_decode(getData($db_prefix . 'faq', ' WHERE brand_id ="' . $brandRow['brand_id'] . '" AND status ="active" ORDER BY 1 DESC'), true);
        $faqs         = [];
        foreach (($response_faq['response'] ?? []) as $faq) {
            $faqs[] = [
                'title'       => $faq['title'],
                'description' => $faq['description'],
            ];
        }

        $transactionInfo = [
            'ref'             => $transactionRow['ref'],
            'customer'        => [
                'id'     => $customer['id']     ?? null,
                'name'   => $customer['name']   ?? null,
                'email'  => $customer['email']  ?? null,
                'mobile' => $customer['mobile'] ?? null,
            ],
            'payment_method'  => $gateway,
            'currency'        => $transactionRow['currency'],
            'amount'          => money_round($transactionRow['amount']),
            'discount_amount' => money_round($transactionRow['discount_amount']),
            'processing_fee'  => money_round($transactionRow['processing_fee']),
            'local_net_amount'=> money_round($transactionRow['local_net_amount']),
            'local_currency'  => $transactionRow['local_currency'],
            'return_url'      => $finalUrl,
            'created_date'    => $transactionRow['created_date'],
            'updated_date'    => $transactionRow['updated_date'],
            'status'          => $transactionRow['status'],
            'brandId'         => $transactionRow['brand_id'],
        ];

        $brandInfo = $this->buildBrandInfo($brandRow, $language);

        // Set global so pp_assets() can call theme head()/footer() hooks
        $GLOBALS['global_response_brand'] = $response_brand;

        $pageData = [
            'transaction' => $transactionInfo,
            'brand'       => $brandInfo,
            'faqs'        => $faqs,
            'options'     => $options,
            'lang'        => $lang,
            'themeSlug'   => $themeSlug,
        ];

        $this->registerThemeViewNamespace($themeSlug);

        $viewName = 'theme::checkout';
        if ($transactionInfo['status'] === 'initiated') {
            if (request()->has('gateway')) {
                $gatewayId = (string) request()->get('gateway');
                $registry = app(\App\Services\Payment\Gateways\GatewayRegistry::class);
                $driver = $registry->resolveById($gatewayId);

                if ($driver) {
                    $transactionModel = \App\Models\PpTransaction::where('ref', $transactionInfo['ref'])->first();
                    if ($transactionModel) {
                        $initResult = $driver->initiate($transactionModel);

                        if (request()->has('ajax')) {
                            return response()->json($initResult);
                        }

                        if (isset($initResult['redirect_url'])) {
                            return redirect($initResult['redirect_url']);
                        }
                    }
                }
                $viewName = 'theme::gateway';
            }
        } else {
            $viewName = 'theme::checkout-status';
        }

        $viewData = $pageData;
        $viewData['pageData'] = $pageData;

        if (view()->exists($viewName)) {
            return response(view($viewName, $viewData)->render());
        } elseif (view()->exists('theme::checkout')) {
            // Fallback to the main checkout.blade.php if sub-views don't exist
            return response(view('theme::checkout', $viewData)->render());
        }

        return $this->capture(function () use ($theme, $pageData) {
            $theme->renderCheckout($pageData);
        });
    }

    public function renderInvoice(Request $request, string $ref): Response
    {
        $this->bootstrap($request);

        global $db_prefix;

        $params          = [':ref' => $ref];
        $response_invoice = json_decode(getData($db_prefix . 'invoice', 'WHERE ref = :ref', '* FROM', $params), true);

        if ($response_invoice['status'] !== true) {
            return $this->notFound();
        }

        $invoiceRow = $response_invoice['response'][0];
        $params     = [':brand_id' => $invoiceRow['brand_id']];
        $response_brand = json_decode(getData($db_prefix . 'brands', 'WHERE brand_id = :brand_id', '* FROM', $params), true);

        if ($response_brand['status'] !== true) {
            return $this->notFound();
        }

        $themeSlug = $response_brand['response'][0]['theme'] ?? 'twenty-six';
        $theme     = $this->loadTheme($themeSlug);

        if ($theme === null) {
            return response('Invalid theme slug', 403);
        }

        $brandRow = $response_brand['response'][0];
        $language = $this->resolveLanguage($theme, $brandRow);
        $options  = $this->resolveOptions($theme, $themeSlug, $brandRow['brand_id']);
        $lang     = $this->buildLang($theme, $language);

        $tz = ($brandRow['timezone'] === '--' || $brandRow['timezone'] === '') ? 'Asia/Dhaka' : $brandRow['timezone'];

        // Invoice items
        $response_items    = json_decode(getData($db_prefix . 'invoice_items', 'WHERE brand_id ="' . $invoiceRow['brand_id'] . '" AND ref ="' . $invoiceRow['ref'] . '"'), true);
        $invoiceItemsArray = [];
        foreach (($response_items['response'] ?? []) as $rowItem) {
            $invoiceItemsArray[] = [
                'description' => $rowItem['description'],
                'amount'      => money_round($rowItem['amount']),
                'quantity'    => money_round($rowItem['quantity']),
                'discount'    => money_round($rowItem['discount']),
                'vat'         => money_round($rowItem['vat']),
            ];
        }

        // Totals
        $subTotal = '0';
        foreach ($invoiceItemsArray as $item) {
            $subTotal = money_add($subTotal, money_mul(money_round($item['amount']), money_sanitize($item['quantity'])));
        }
        $totalDiscount = '0';
        foreach ($invoiceItemsArray as $item) {
            $totalDiscount = money_add($totalDiscount, money_round($item['discount']));
        }
        $totalVat = '0';
        foreach ($invoiceItemsArray as $item) {
            $totalVat = money_add($totalVat, money_round($item['vat']));
        }
        $grandTotal = money_sub(money_add($subTotal, $totalVat), $totalDiscount);

        $invoiceTotals = [
            'sub_total'      => money_round($subTotal),
            'total_discount' => money_round($totalDiscount),
            'total_vat'      => money_round($totalVat),
            'grand_total'    => money_round($grandTotal),
        ];

        $invoiceInfo = [
            'invoice_id'   => $invoiceRow['ref'],
            'iid'          => $invoiceRow['ref'],
            'status'       => $invoiceRow['status'],
            'currency'     => $invoiceRow['currency'],
            'due_date'     => ($invoiceRow['due_date'] === '' || $invoiceRow['due_date'] === '--') ? '--' : convertUTCtoUserTZ($invoiceRow['due_date'], $tz, 'M d, Y'),
            'created_date' => convertUTCtoUserTZ($invoiceRow['created_date'], $tz, 'M d, Y'),
            'customer'     => json_decode($invoiceRow['customer_info'] ?? '{}', true) ?: [],
            'shippingFee'  => $invoiceRow['shipping'] ?? '0',
            'gateway'      => $invoiceRow['gateway'] ?? '',
            'note'         => $invoiceRow['note'] ?? '',
        ];

        $brandInfo = $this->buildBrandInfo($brandRow, $language);

        // Set global so pp_assets() can call theme head()/footer() hooks
        $GLOBALS['global_response_brand'] = $response_brand;

        $pageData = [
            'invoice' => $invoiceInfo,
            'items'   => $invoiceItemsArray,
            'totals'  => $invoiceTotals,
            'brand'   => $brandInfo,
            'options' => $options,
            'lang'    => $lang,
            'themeSlug' => $themeSlug,
        ];

        $this->registerThemeViewNamespace($themeSlug);

        if (view()->exists('theme::invoice')) {
            $viewData = $pageData;
            $viewData['pageData'] = $pageData;
            return response(view('theme::invoice', $viewData)->render());
        }

        return $this->capture(function () use ($theme, $pageData) {
            $theme->renderInvoice($pageData);
        });
    }

    // ─────────────────────────────────────────────
    // Internal Helpers
    // ─────────────────────────────────────────────

    /**
     * Bootstrap the legacy environment (pp-config, pp-functions, globals).
     * Idempotent — only runs once per request.
     */
    private function bootstrap(Request $request): void
    {
        if ($this->bootstrapped) {
            return;
        }

        // Sync superglobals so legacy functions that read $_GET/$_POST work
        foreach ($request->query() as $key => $value) {
            $_GET[$key] = $value;
            $_REQUEST[$key] = $value;
        }
        foreach ($request->post() as $key => $value) {
            $_POST[$key] = $value;
            $_REQUEST[$key] = $value;
        }
        $_SERVER['REQUEST_METHOD'] = $request->method();
        $_SERVER['REQUEST_URI']    = $request->getRequestUri();
        $_SERVER['HTTP_HOST']      = $request->getHost();
        $_SERVER['HTTPS']          = $request->isSecure() ? 'on' : 'off';
        $_SERVER['REMOTE_ADDR']    = (string) ($request->ip() ?? '127.0.0.1');

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        // Define the constant so legacy files don't bail
        if (!defined('PipraPay_INIT')) {
            define('PipraPay_INIT', true);
        }

        // Load pp-functions (includes DB setup, all helper functions)
        if (!isset($GLOBALS['pp_functions_loaded'])) {
            $functionsFile = $this->legacyRoot . '/app/Support/zp-functions.php';
            if (file_exists($functionsFile)) {
                require_once $functionsFile;
            }
        }

        // Set db_prefix global
        $GLOBALS['db_prefix'] = env('DB_PREFIX', 'pp_');

        // ── Critical: set $site_url so pp_assets() generates correct absolute URLs ──
        // pp_assets() outputs <link href="$site_url . 'assets/css/...'">
        // Without this, URLs become relative and resolve against the current page path.
        $GLOBALS['site_url'] = rtrim(config('app.url'), '/') . '/';

        // ── Set path globals so pp_checkout_address() and pp_payment_link_address() work ──
        $GLOBALS['path_payment']      = env('PATH_PAYMENT', 'payment');
        $GLOBALS['path_invoice']      = env('PATH_INVOICE', 'invoice');
        $GLOBALS['path_payment_link'] = env('PATH_PAYMENT_LINK', 'payment-link');

        $this->bootstrapped = true;
    }

    /**
     * Load a theme class from the pp-themes module directory.
     * Returns the instantiated theme object or null on failure.
     */
    private function loadTheme(string $slug): ?object
    {
        if (config('piprapay.migration.strict_module_slug_validation', true) && !LegacyModuleGuard::isSafeSlug($slug)) {
            \Illuminate\Support\Facades\Log::warning("ThemeService: blocked unsafe theme slug [{$slug}]");
            return null;
        }

        $classFile = LegacyModuleGuard::resolveModuleClassFile($this->legacyRoot, 'pp-themes', $slug);

        if ($classFile === null) {
            \Illuminate\Support\Facades\Log::warning("ThemeService: class.php not found for theme [{$slug}]");
            return null;
        }

        require_once $classFile;

        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))) . 'Theme';

        if (!class_exists($className)) {
            \Illuminate\Support\Facades\Log::warning("ThemeService: class [{$className}] not found in [{$classFile}]");
            return null;
        }

        return new $className();
    }

    /**
     * Resolve the active language for the brand using the theme's supported languages.
     */
    private function resolveLanguage(object $theme, array $brandRow): string
    {
        $supported = method_exists($theme, 'supported_languages') ? $theme->supported_languages() : [];
        return resolveModuleLanguage($brandRow['language'] ?? '', $supported);
    }

    /**
     * Build the $lang array for templates from the theme's lang_text().
     */
    private function buildLang(object $theme, string $language): array
    {
        $langText = method_exists($theme, 'lang_text') ? $theme->lang_text() : [];
        return buildLangArray($langText, $language);
    }

    /**
     * Resolve all theme options from the pp_env table for a given brand.
     */
    private function resolveOptions(object $theme, string $themeSlug, string $brandId): array
    {
        $fields  = method_exists($theme, 'fields') ? $theme->fields() : [];
        $options = [];

        foreach ($fields as $field) {
            $optionName = $themeSlug . '-' . $field['name'];
            $value      = get_env($optionName, $brandId);

            if (!empty($field['multiple']) && !empty($value)) {
                $value = is_array($value) ? $value : json_decode($value, true);
            }

            $options[$field['name']] = $value;
        }

        return $options;
    }

    /**
     * Build the standard brand info array passed to all theme templates.
     */
    private function buildBrandInfo(array $brandRow, string $language): array
    {
        return [
            'id'          => $brandRow['brand_id'],
            'brand_id'    => $brandRow['brand_id'],
            'name'        => ($brandRow['name'] === '--') ? $brandRow['identify_name'] : $brandRow['name'],
            'identifyName'=> $brandRow['identify_name'],
            'logo'        => $this->resolveAssetUrl($brandRow['logo'], 'https://help.piprapay.com/storage/branding_media/8a5c6ee4-8eba-401d-bffb-c43006d5f65d.png'),
            'favicon'     => $this->resolveAssetUrl($brandRow['favicon'], 'https://help.piprapay.com/favicon/icon-144x144.png'),

            'support' => [
                'email'    => $brandRow['support_email_address'],
                'phone'    => $brandRow['support_phone_number'],
                'website'  => $brandRow['support_website'],
                'whatsapp' => $brandRow['whatsapp_number'],
                'telegram' => $brandRow['telegram'],
            ],

            'address' => [
                'street'  => $brandRow['street_address'],
                'city'    => $brandRow['city_town'],
                'postal'  => $brandRow['postal_code'],
                'country' => $brandRow['country'],
            ],

            'locale' => [
                'timezone' => $brandRow['timezone'],
                'language' => $language,
                'currency' => $brandRow['currency_code'],
            ],
        ];
    }

    private function resolveAssetUrl(?string $path, string $default): string
    {
        if (!$path || $path === '--') {
            return $default;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return rtrim(config('app.url'), '/') . '/pp-media/storage/branding_media/' . ltrim($path, '/');
    }

    /**
     * Capture output from theme's render method and return a Laravel Response.
     */
    private function capture(callable $callback): Response
    {
        $statusBefore = http_response_code() ?: 200;

        ob_start();
        try {
            $callback();
        } catch (\Throwable $e) {
            ob_end_clean();
            \Illuminate\Support\Facades\Log::error('ThemeService render error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
        $html = (string) ob_get_clean();

        $statusAfter = http_response_code() ?: $statusBefore;

        return response($html, $statusAfter);
    }

    /**
     * Return a standard 404 response.
     */
    private function notFound(): Response
    {
        $page404 = $this->legacyRoot . '/pp-404.php';
        if (file_exists($page404)) {
            ob_start();
            require $page404;
            return response((string) ob_get_clean(), 404);
        }
        return response('Not Found', 404);
    }
}
