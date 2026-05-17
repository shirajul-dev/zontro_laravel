<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\ZpCurrency;
use DateTimeZone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    /**
     * Display the settings dashboard.
     */
    public function index()
    {
        return view('m::pages.settings.index');
    }

    /**
     * Get the currently active brand for the merchant.
     */
    protected function getActiveBrand()
    {
        $merchant = auth()->guard('merchant')->user();
        if (!$merchant) return null;

        $activeBrandId = session('active_brand_id');
        $brand = null;

        if ($activeBrandId) {
            $brand = $merchant->brands()->where('zp_brands.id', $activeBrandId)->first();
        }

        if (!$brand) {
            $brand = $merchant->brands()->where('is_default', true)->first() ?? $merchant->brands()->first();
            if ($brand) {
                session(['active_brand_id' => $brand->id]);
            }
        }

        return $brand;
    }

    /**
     * Display the General Settings page.
     */
    public function general()
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return redirect()->route('merchant.dashboard')->with('error', 'No active brand found.');
        }

        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $languages = [
            'en' => 'English',
            'bn' => 'Bangla',
            'hi' => 'Hindi',
            'ur' => 'Urdu',
            'ar' => 'Arabic',
        ];
        $currencies = ZpCurrency::where('brand_id', $brand->brand_id)->get();

        if (request()->ajax()) {
            return view('m::pages.settings.sections.general', compact('brand', 'timezones', 'languages', 'currencies'))->render();
        }

        return view('m::pages.settings.general', compact('brand', 'timezones', 'languages', 'currencies'));
    }

    /**
     * Display the Branding & Logos page.
     */
    public function branding()
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return redirect()->route('merchant.dashboard')->with('error', 'No active brand found.');
        }

        if (request()->ajax()) {
            return view('m::pages.settings.sections.branding', compact('brand'))->render();
        }

        return view('m::pages.settings.branding', compact('brand'));
    }

    /**
     * Display the FAQ Settings index view or handle AJAX datatable requests.
     */
    public function faqs(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) abort(401);

        if ($request->ajax()) {
            return $this->handleFaqList($brand);
        }

        return view('merchant.default.pages.settings.faqs', compact('brand'));
    }

    /**
     * Handle AJAX FAQ list for the datatable.
     */
    protected function handleFaqList($brand)
    {
        $search = request('search_input');
        $status = request('filter_status');
        $limit = request('show_limit', 10);

        $query = \App\Models\ZpFaq::where('brand_id', $brand->brand_id);

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $faqs = $query->orderBy('id', 'desc')->paginate($limit);

        $response = $faqs->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => \Illuminate\Support\Str::limit(strip_tags($item->description), 60),
                'status' => $item->status,
                'updated_date' => $item->updated_at->format('M d, Y h:i A'),
            ];
        });

        return response()->json([
            'status' => 'true',
            'response' => $response,
            'datatableInfo' => "Showing <strong>" . ($faqs->total() > 0 ? $faqs->firstItem() : 0) . " to " . ($faqs->total() > 0 ? $faqs->lastItem() : 0) . "</strong> of <strong>{$faqs->total()} entries</strong>",
            'pagination' => $this->buildPagination($faqs)
        ]);
    }

    /**
     * Route handler for faqList AJAX request.
     */
    public function faqList()
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);
        return $this->handleFaqList($brand);
    }


    /**
     * Update General Settings.
     */
    public function updateGeneral(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return response()->json(['status' => 'error', 'message' => 'No active brand found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'support_email' => 'nullable|email',
            'support_phone' => 'nullable|string|max:20',
            'support_website' => 'nullable|url',
            'currency_code' => 'required|string|max:3',
            'timezone' => 'required|string',
            'language' => 'required|string|max:5',
            'street_address' => 'nullable|string|max:255',
            'city_town' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'whatsapp_number' => 'nullable|string|max:20',
            'telegram' => 'nullable|string|max:50',
            'facebook_messenger' => 'nullable|string|max:100',
            'facebook_page' => 'nullable|url',
            'auto_exchange' => 'nullable|boolean',
            'payment_tolerance' => 'nullable|numeric|min:0',
        ]);

        $brand->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'General settings updated successfully.',
            'brand' => $brand
        ]);
    }
    /**
     * Update Branding Settings.
     */
    public function updateBranding(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return response()->json(['status' => 'error', 'message' => 'No active brand found.'], 404);
        }

        // Note: Actual file upload logic would go here
        // For now, validating existing paths or placeholders
        $validated = $request->validate([
            'logo' => 'nullable|string|max:255',
            'favicon' => 'nullable|string|max:255',
        ]);

        $brand->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Branding assets updated successfully.',
            'brand' => $brand
        ]);
    }

    /**
     * Create a new brand FAQ.
     */
    public function faqCreate(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $validated = $request->validate([
            'faq_title' => 'required|string|max:255',
            'faq_description' => 'required|string',
            'faq_status' => 'required|in:active,inactive',
        ]);

        \App\Models\ZpFaq::create([
            'brand_id' => $brand->brand_id,
            'title' => $validated['faq_title'],
            'description' => $validated['faq_description'],
            'status' => $validated['faq_status'],
        ]);

        return response()->json([
            'status' => 'true',
            'title' => 'FAQ Created',
            'message' => 'The FAQ has been created successfully.'
        ]);
    }

    /**
     * Get single FAQ info by ID for editing.
     */
    public function faqInfo($id)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $faq = \App\Models\ZpFaq::where('brand_id', $brand->brand_id)->find($id);
        if (!$faq) {
            return response()->json(['status' => 'false', 'message' => 'FAQ not found.'], 404);
        }

        return response()->json([
            'status' => 'true',
            'title' => $faq->title,
            'description' => $faq->description,
            'fstatus' => $faq->status,
        ]);
    }

    /**
     * Edit / Update an existing FAQ.
     */
    public function faqEdit(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $validated = $request->validate([
            'faq_id' => 'required|integer',
            'faq_title' => 'required|string|max:255',
            'faq_description' => 'required|string',
            'faq_status' => 'required|in:active,inactive',
        ]);

        $faq = \App\Models\ZpFaq::where('brand_id', $brand->brand_id)->find($validated['faq_id']);
        if (!$faq) {
            return response()->json(['status' => 'false', 'title' => 'Request Failed', 'message' => 'Invalid FAQ ID']);
        }

        $faq->update([
            'title' => $validated['faq_title'],
            'description' => $validated['faq_description'],
            'status' => $validated['faq_status'],
        ]);

        return response()->json([
            'status' => 'true',
            'title' => 'FAQ Updated',
            'message' => 'The FAQ has been updated successfully.'
        ]);
    }

    /**
     * Delete a single FAQ.
     */
    public function faqDelete($id)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $faq = \App\Models\ZpFaq::where('brand_id', $brand->brand_id)->find($id);
        if (!$faq) {
            return response()->json(['status' => 'false', 'title' => 'Delete Failed', 'message' => 'FAQ not found.']);
        }

        $faq->delete();

        return response()->json([
            'status' => 'true',
            'title' => 'FAQ Deleted',
            'message' => 'The FAQ has been deleted successfully.'
        ]);
    }

    /**
     * Handle bulk actions for selected FAQs.
     */
    public function faqBulkAction(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $action = $request->input('action');
        $ids = $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['status' => 'false', 'title' => 'Action Failed', 'message' => 'No FAQs selected.']);
        }

        $query = \App\Models\ZpFaq::where('brand_id', $brand->brand_id)->whereIn('id', $ids);

        if ($action === 'delete') {
            $query->delete();
            $msg = 'selected FAQs have been deleted successfully.';
        } elseif ($action === 'active') {
            $query->update(['status' => 'active']);
            $msg = 'selected FAQs have been activated successfully.';
        } elseif ($action === 'inactive') {
            $query->update(['status' => 'inactive']);
            $msg = 'selected FAQs have been inactivated successfully.';
        } else {
            return response()->json(['status' => 'false', 'title' => 'Action Failed', 'message' => 'Invalid bulk action.']);
        }

        return response()->json([
            'status' => 'true',
            'title' => 'Bulk Action Executed',
            'message' => 'The ' . $msg
        ]);
    }
    /**
     * Display the Currencies page.
     */
    public function currencies(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) abort(401);

        // Handle both AJAX and requests with data table parameters
        if ($request->ajax() || $request->has('search_input') || $request->has('page') || $request->has('show_limit')) {
            return $this->handleCurrencyList($brand);
        }

        return view('merchant.default.pages.settings.currencies', compact('brand'));
    }

    /**
     * Update a specific currency's details.
     */
    public function updateCurrency(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $currencyId = $request->input('currency_id');
        $currency = ZpCurrency::where('brand_id', $brand->brand_id)->where('id', $currencyId)->first();

        if (!$currency) {
            return response()->json(['status' => 'false', 'title' => 'Request Failed', 'message' => 'Invalid Currency ID']);
        }

        $currency->update([
            'symbol' => $request->input('currency_symbol'),
            'rate' => $request->input('currency_rate'),
        ]);

        return response()->json([
            'status' => 'true',
            'title' => 'Currency Updated',
            'message' => 'The currency has been updated successfully.'
        ]);
    }

    /**
     * Import all global currencies for the brand.
     */
    public function importCurrencies()
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $response = Http::withoutVerifying()->timeout(10)->get('https://gist.githubusercontent.com/ksafranski/2973986/raw/');
        if (!$response->ok()) {
            return response()->json(['status' => 'false', 'title' => 'Request Failed', 'message' => 'Unable to fetch global currencies.']);
        }

        $currencies = $response->json();
        if (!is_array($currencies)) {
            return response()->json(['status' => 'false', 'title' => 'Request Failed', 'message' => 'Invalid currency data format.']);
        }

        foreach ($currencies as $code => $details) {
            ZpCurrency::firstOrCreate(
                ['brand_id' => $brand->brand_id, 'code' => $code],
                [
                    'symbol' => $details['symbol_native'] ?? '',
                    'rate' => '0',
                ]
            );
        }

        return response()->json([
            'status' => 'true',
            'title' => 'Currencies Imported',
            'message' => 'All currency data has been imported successfully.'
        ]);
    }

    /**
     * Sync exchange rates from an external API.
     */
    public function syncRates(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $itemId = $request->input('ItemID');
        $base = strtolower($brand->currency_code);

        $response = Http::withoutVerifying()
            ->timeout(10)
            ->get('https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/' . $base . '.json');

        if (!$response->ok()) {
            return response()->json(['status' => 'false', 'title' => 'Sync Failed', 'message' => 'Unable to fetch latest exchange rates.']);
        }

        $rates = $response->json()[$base] ?? null;
        if (!$rates) {
            return response()->json(['status' => 'false', 'title' => 'Sync Failed', 'message' => 'Invalid base currency.']);
        }

        if ($itemId) {
            // Single currency sync
            $currency = ZpCurrency::where('brand_id', $brand->brand_id)->where('id', $itemId)->first();
            if ($currency && isset($rates[strtolower($currency->code)])) {
                $rate = $rates[strtolower($currency->code)];
                if ($rate > 0) {
                    $currency->update(['rate' => 1 / $rate]);
                }
            }
        } else {
            // Bulk sync
            foreach ($rates as $code => $rate) {
                if ($rate > 0) {
                    ZpCurrency::where('brand_id', $brand->brand_id)
                        ->where('code', strtoupper($code))
                        ->update(['rate' => 1 / $rate]);
                }
            }
        }

        return response()->json([
            'status' => 'true',
            'title' => 'Rates Updated',
            'message' => 'Currency exchange rates have been updated successfully.'
        ]);
    }

    /**
     * Handle AJAX currency list for datatable.
     */
    protected function handleCurrencyList($brand)
    {
        $search = request('search_input');
        $limit = request('show_limit', 10);

        $query = ZpCurrency::where('brand_id', $brand->brand_id);

        if ($search !== null && $search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
            });
        }

        // Sort base currency first, then by code
        $baseCode = $brand->currency_code;
        $currencies = $query->orderByRaw("CASE WHEN code = '{$baseCode}' THEN 0 ELSE 1 END")
                            ->orderBy('code')
                            ->paginate($limit);

        $response = $currencies->map(function($item) use ($brand) {
            $isDefault = $item->code === $brand->currency_code;
            return [
                'id' => $item->id,
                'code' => $item->code,
                'symbol' => $item->symbol,
                'rate' => $isDefault ? '1.00 ' . $item->code . ' = 1.00 ' . $item->code : '1.00 ' . $item->code . ' = ' . number_format((float)$item->rate, 4) . ' ' . $brand->currency_code,
                'updated_date' => $item->updated_at->format('M d, Y h:i A'),
                'default' => $isDefault ? 'true' : 'false',
                'is_base' => $isDefault
            ];
        });

        return response()->json([
            'status' => 'true',
            'response' => $response,
            'search_term' => $search, // For debugging
            'datatableInfo' => "Showing <strong>" . ($currencies->total() > 0 ? $currencies->firstItem() : 0) . " to " . ($currencies->total() > 0 ? $currencies->lastItem() : 0) . "</strong> of <strong>{$currencies->total()} entries</strong>",
            'pagination' => $this->buildPagination($currencies)
        ]);
    }

    protected function buildPagination($paginator)
    {
        if ($paginator->lastPage() <= 1) return '';

        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        $html = '<div class="flex w-full items-center justify-between gap-2 rounded-lg bg-gray-50 p-4 sm:w-auto sm:justify-normal sm:rounded-none sm:bg-transparent sm:p-0 dark:bg-gray-900 dark:sm:bg-transparent">';
        
        // Previous Button
        $html .= '<button data-page="' . ($currentPage - 1) . '" ' . ($paginator->onFirstPage() ? 'disabled' : '') . ' class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 disabled:cursor-not-allowed disabled:opacity-50 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">';
        $html .= '<span><svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.58203 9.99868C2.58174 10.1909 2.6549 10.3833 2.80152 10.53L7.79818 15.5301C8.09097 15.8231 8.56584 15.8233 8.85883 15.5305C9.15183 15.2377 9.152 14.7629 8.85921 14.4699L5.13911 10.7472L16.6665 10.7472C17.0807 10.7472 17.4165 10.4114 17.4165 9.99715C17.4165 9.58294 17.0807 9.24715 16.6665 9.24715L5.14456 9.24715L8.85919 5.53016C9.15199 5.23717 9.15184 4.7623 8.85885 4.4695C8.56587 4.1767 8.09099 4.17685 7.79819 4.46984L2.84069 9.43049C2.68224 9.568 2.58203 9.77087 2.58203 9.99715C2.58203 9.99766 2.58203 9.99817 2.58203 9.99868Z"></path></svg></span>';
        $html .= '</button>';

        // Mobile Page Info
        $html .= '<span class="block text-sm font-medium text-gray-700 sm:hidden dark:text-gray-400">Page <span>' . $currentPage . '</span> of <span>' . $lastPage . '</span></span>';

        // Desktop Page List
        $html .= '<ul class="hidden items-center gap-0.5 sm:flex">';
        
        $elements = $paginator->onEachSide(1)->linkCollection();
        
        foreach ($elements as $element) {
            // Skip prev/next as they are handled manually
            if ($element['label'] == '&laquo; Previous' || $element['label'] == 'Next &raquo;') {
                continue;
            }

            if ($element['label'] == '...') {
                $html .= '<li><span class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium text-gray-500">...</span></li>';
            } else {
                $i = $element['label'];
                $activeClass = ($element['active']) ? 'bg-brand-500 text-white' : 'hover:bg-brand-500 text-gray-700 dark:text-gray-400 hover:text-white dark:hover:text-white';
                $html .= '<li><button data-page="' . $i . '" class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium ' . $activeClass . '"><span>' . $i . '</span></button></li>';
            }
        }
        $html .= '</ul>';

        // Next Button
        $html .= '<button data-page="' . ($currentPage + 1) . '" ' . (!$paginator->hasMorePages() ? 'disabled' : '') . ' class="shadow-theme-xs flex items-center gap-2 rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 hover:text-gray-800 disabled:cursor-not-allowed disabled:opacity-50 sm:p-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">';
        $html .= '<span><svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.4165 9.9986C17.4168 10.1909 17.3437 10.3832 17.197 10.53L12.2004 15.5301C11.9076 15.8231 11.4327 15.8233 11.1397 15.5305C10.8467 15.2377 10.8465 14.7629 11.1393 14.4699L14.8594 10.7472L3.33203 10.7472C2.91782 10.7472 2.58203 10.4114 2.58203 9.99715C2.58203 9.58294 2.91782 9.24715 3.33203 9.24715L14.854 9.24715L11.1393 5.53016C10.8465 5.23717 10.8467 4.7623 11.1397 4.4695C11.4327 4.1767 11.9075 4.17685 12.2003 4.46984L17.1578 9.43049C17.3163 9.568 17.4165 9.77087 17.4165 9.99715C17.4165 9.99763 17.4165 9.99812 17.4165 9.9986Z"></path></svg></span>';
        $html .= '</button>';

        $html .= '</div>';
        return $html;
    }

    /**
     * API Credentials management view / AJAX routing
     */
    public function apiKeys(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) abort(401);

        if ($request->ajax()) {
            return $this->apiKeyList();
        }

        return view('m::pages.settings.api-keys', compact('brand'));
    }

    /**
     * Get AJAX formatted API keys list
     */
    public function apiKeyList()
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $service = app(\App\Services\Admin\ApiAdminActionService::class);
        $result = $service->list(request()->all(), $brand->brand_id, $brand->timezone ?? 'Asia/Dhaka');
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Create new API Key
     */
    public function apiKeyCreate(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $mappedInput = [
            'api_name' => $request->input('api_name'),
            'apiExpiryDate' => $request->input('apiExpiryDate'),
            'api_status' => $request->input('api_status', 'active'),
            'scopes' => $request->input('api_scopes', []),
        ];

        $service = app(\App\Services\Admin\ApiAdminActionService::class);
        $result = $service->create($mappedInput, $brand->brand_id);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Get single API Key info for editing
     */
    public function apiKeyInfo($id)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $service = app(\App\Services\Admin\ApiAdminActionService::class);
        $result = $service->infoById($id, $brand->brand_id);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Edit API Key
     */
    public function apiKeyEdit(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $mappedInput = [
            'api_id' => $request->input('api_id'),
            'api_name' => $request->input('api_name'),
            'apiExpiryDate' => $request->input('apiExpiryDate'),
            'api_status' => $request->input('api_status'),
            'scopes' => $request->input('api_scopes', []),
        ];

        $service = app(\App\Services\Admin\ApiAdminActionService::class);
        $result = $service->edit($mappedInput, $brand->brand_id);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Delete individual API key
     */
    public function apiKeyDelete($id)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $service = app(\App\Services\Admin\ApiAdminActionService::class);
        $result = $service->delete($id, $brand->brand_id);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Bulk actions for API keys (activate, deactivate, delete)
     */
    public function apiKeyBulkAction(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $actionId = $request->input('actionID');
        $selectedIds = json_decode($request->input('selected_ids', '[]'), true) ?: [];

        $service = app(\App\Services\Admin\ApiAdminActionService::class);
        $result = $service->bulkAction($actionId, $selectedIds, $brand->brand_id, true, true);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Whitelisted Domains management view / AJAX routing
     */
    public function domains(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) abort(401);

        if ($request->ajax()) {
            return $this->domainList();
        }

        return view('m::pages.settings.domains', compact('brand'));
    }

    /**
     * Get AJAX whitelisted domains list
     */
    public function domainList()
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $service = app(\App\Services\Admin\DomainAdminActionService::class);
        $result = $service->list(request()->all(), $brand->timezone ?? 'Asia/Dhaka');
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Whitelist a new domain
     */
    public function domainCreate(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $mappedInput = [
            'domain_name' => $request->input('domain_name'),
            'domain_status' => $request->input('domain_status', 'active'),
        ];

        $service = app(\App\Services\Admin\DomainAdminActionService::class);
        $result = $service->create($mappedInput);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Fetch whitelisted domain detail for edit modal
     */
    public function domainInfo($id)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $service = app(\App\Services\Admin\DomainAdminActionService::class);
        $result = $service->infoById((int)$id);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Edit whitelisted domain
     */
    public function domainEdit(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $mappedInput = [
            'domain_id' => $request->input('domain_id'),
            'domain_name' => $request->input('domain_name'),
            'domain_status' => $request->input('domain_status'),
        ];

        $service = app(\App\Services\Admin\DomainAdminActionService::class);
        $result = $service->edit($mappedInput);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Remove domain from whitelist
     */
    public function domainDelete($id)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $service = app(\App\Services\Admin\DomainAdminActionService::class);
        $result = $service->delete((int)$id);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Bulk actions for whitelisted domains (activate, deactivate, delete)
     */
    public function domainBulkAction(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);

        $actionId = $request->input('actionID');
        $selectedIds = json_decode($request->input('selected_ids', '[]'), true) ?: [];

        $service = app(\App\Services\Admin\DomainAdminActionService::class);
        $result = $service->bulkAction($actionId, $selectedIds, true, true);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }
}
