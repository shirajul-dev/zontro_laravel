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
        return view('m::components.pagination', compact('paginator'))->render();
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
        
        if (isset($result['status']) && $result['status'] === 'true') {
            $showLimit = request('show_limit');
            $showLimit = $showLimit === '' || $showLimit === null ? 8 : (int)$showLimit;
            if ($showLimit <= 0) {
                $showLimit = 8;
            }
            $page = max(1, (int)request('page', 1));
            
            $query = \App\Models\PpApi::query()->where('brand_id', $brand->brand_id);
            
            $searchInput = trim((string)request('search_input', ''));
            $filterStatus = trim((string)request('filter_status', ''));
            $filterStart = trim((string)request('filter_start', ''));
            $filterEnd = trim((string)request('filter_end', ''));
            
            if ($filterStart !== '') {
                $query->where('created_date', '>=', $filterStart . ' 00:00:00');
            }
            if ($filterEnd !== '') {
                $query->where('created_date', '<=', $filterEnd . ' 23:59:59');
            }
            if ($filterStatus !== '') {
                $query->where('status', $filterStatus);
            }
            if ($searchInput !== '') {
                $query->where('name', 'like', "%{$searchInput}%");
            }
            
            $paginator = $query->orderByDesc('id')->paginate($showLimit, ['*'], 'page', $page);
            $result['pagination'] = $this->buildPagination($paginator);
        }

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
        
        if (isset($result['status']) && $result['status'] === 'true') {
            $showLimit = request('show_limit');
            $showLimit = $showLimit === '' || $showLimit === null ? 8 : (int)$showLimit;
            if ($showLimit <= 0) {
                $showLimit = 8;
            }
            $page = max(1, (int)request('page', 1));
            
            $query = \App\Models\PpDomain::query()->where('status', '!=', '');
            
            $searchInput = trim((string)request('search_input', ''));
            $filterStatus = trim((string)request('filter_status', ''));
            $filterStart = trim((string)request('filter_start', ''));
            $filterEnd = trim((string)request('filter_end', ''));
            
            if ($filterStart !== '') {
                $query->where('created_date', '>=', $filterStart . ' 00:00:00');
            }
            if ($filterEnd !== '') {
                $query->where('created_date', '<=', $filterEnd . ' 23:59:59');
            }
            if ($filterStatus !== '') {
                $query->where('status', $filterStatus);
            }
            if ($searchInput !== '') {
                $query->where('domain', 'like', "%{$searchInput}%");
            }
            
            $paginator = $query->orderByDesc('id')->paginate($showLimit, ['*'], 'page', $page);
            $result['pagination'] = $this->buildPagination($paginator);
        }

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

    /**
     * Display all checkout themes.
     */
    public function themes()
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return redirect()->route('merchant.dashboard')->with('error', 'No active brand found.');
        }

        $themes = [];
        $themeDirs = glob(resource_path('views/theme/*'), GLOB_ONLYDIR);

        foreach ($themeDirs as $dir) {
            $classFile = $dir . '/class.php';
            if (!file_exists($classFile)) {
                continue;
            }

            require_once $classFile;

            $slug = basename($dir);
            $class = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))) . 'Theme';

            if (!class_exists($class)) {
                continue;
            }

            $themeObj = new $class();
            $themes[$slug] = $themeObj->info();
            $themes[$slug]['supported_languages'] = method_exists($themeObj, 'supported_languages') ? $themeObj->supported_languages() : [];
        }

        if (request()->ajax()) {
            return view('merchant.default.pages.settings.sections.themes', compact('brand', 'themes'))->render();
        }

        return view('merchant.default.pages.settings.themes', compact('brand', 'themes'));
    }

    /**
     * Activate a checkout theme.
     */
    public function activeTheme(Request $request)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return response()->json(['status' => 'false', 'title' => 'Unauthorized', 'message' => 'Unauthorized access.'], 401);
        }

        $slug = trim((string)$request->input('slug', ''));
        if ($slug === '') {
            return response()->json(['status' => 'false', 'title' => 'Error', 'message' => 'Invalid theme selected.', 'csrf_token' => csrf_token()]);
        }

        $themeDir = resource_path('views/theme/' . $slug);
        if (!is_dir($themeDir) || !file_exists($themeDir . '/class.php')) {
            return response()->json(['status' => 'false', 'title' => 'Error', 'message' => 'Selected theme class was not found.', 'csrf_token' => csrf_token()]);
        }

        // Update brand theme natively
        $brand->update(['theme' => $slug]);

        // Parallel sync to legacy table
        \DB::table('pp_brands')->where('brand_id', $brand->brand_id)->update([
            'theme' => $slug,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'status' => 'true',
            'title' => 'Theme Activated',
            'message' => 'The checkout theme has been activated successfully.',
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * Display configuration form for a specific active theme.
     */
    public function themeSettings(string $slug)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return redirect()->route('merchant.dashboard')->with('error', 'No active brand found.');
        }

        if ($brand->theme !== $slug) {
            return redirect()->route('merchant.settings.themes')->with('error', 'You can only configure your active checkout theme.');
        }

        $themeDir = resource_path('views/theme/' . $slug);
        if (!is_dir($themeDir) || !file_exists($themeDir . '/class.php')) {
            return redirect()->route('merchant.settings.themes')->with('error', 'Theme settings file not found.');
        }

        require_once $themeDir . '/class.php';
        $class = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))) . 'Theme';
        if (!class_exists($class)) {
            return redirect()->route('merchant.settings.themes')->with('error', 'Theme class definition not found.');
        }

        $themeObj = new $class();
        $fields = method_exists($themeObj, 'fields') ? $themeObj->fields() : [];
        $supportedLanguages = method_exists($themeObj, 'supported_languages') ? $themeObj->supported_languages() : [];
        $themeInfo = $themeObj->info();

        foreach ($fields as &$field) {
            $optionName = $slug . '-' . $field['name'];
            $storedValue = get_env($optionName, $brand->brand_id);
            if ($storedValue !== '') {
                $field['value'] = $storedValue;
            }

            if (!empty($field['multiple']) && !empty($field['value'])) {
                $field['value'] = is_array($field['value']) ? $field['value'] : (json_decode($field['value'], true) ?: $field['value']);
            }
        }

        if (request()->ajax()) {
            return view('merchant.default.pages.settings.sections.themes-setting', compact('brand', 'slug', 'fields', 'supportedLanguages', 'themeInfo'))->render();
        }

        return view('merchant.default.pages.settings.themes-setting', compact('brand', 'slug', 'fields', 'supportedLanguages', 'themeInfo'));
    }

    /**
     * Update configuration form values for a specific active theme.
     */
    public function updateThemeSettings(Request $request, string $slug)
    {
        $brand = $this->getActiveBrand();
        if (!$brand) {
            return response()->json(['status' => 'false', 'title' => 'Unauthorized', 'message' => 'Unauthorized access.'], 401);
        }

        if ($brand->theme !== $slug) {
            return response()->json(['status' => 'false', 'title' => 'Error', 'message' => 'You can only configure your active theme.', 'csrf_token' => csrf_token()]);
        }

        $themeDir = resource_path('views/theme/' . $slug);
        if (!is_dir($themeDir) || !file_exists($themeDir . '/class.php')) {
            return response()->json(['status' => 'false', 'title' => 'Error', 'message' => 'Theme settings file not found.', 'csrf_token' => csrf_token()]);
        }

        require_once $themeDir . '/class.php';
        $class = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))) . 'Theme';
        if (!class_exists($class)) {
            return response()->json(['status' => 'false', 'title' => 'Error', 'message' => 'Theme class definition not found.', 'csrf_token' => csrf_token()]);
        }

        $themeObj = new $class();
        $fields = method_exists($themeObj, 'fields') ? $themeObj->fields() : [];

        // Save normal input fields
        foreach ($fields as $field) {
            $name = $field['name'];
            $optionName = $slug . '-' . $name;

            if ($field['type'] === 'image') {
                continue;
            }

            $value = $request->input($name);

            if ($value === null) {
                if ($field['type'] === 'checkbox') {
                    $value = '0';
                } else {
                    $value = '';
                }
            }

            if (is_array($value)) {
                $value = json_encode($value);
            }

            $this->setThemeEnvValue($optionName, (string)$value, $brand->brand_id);
        }

        // Save uploaded files/images
        foreach ($request->files as $key => $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                $optionName = $slug . '-' . $key;
                
                $maxSize = 5 * 1024 * 1024;
                if ($file->getSize() > $maxSize) {
                    continue;
                }

                $extension = strtolower($file->getClientOriginalExtension());
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $allowed, true)) {
                    continue;
                }

                $filename = strtolower(\Illuminate\Support\Str::random(10) . '_' . time() . '.' . $extension);
                
                $uploadPath = storage_path('app/public/media');
                if (!is_dir($uploadPath)) {
                    @mkdir($uploadPath, 0755, true);
                }

                $file->move($uploadPath, $filename);

                $siteUrl = rtrim((string) config('app.url', '/'), '/') . '/';
                $value = rtrim($siteUrl, '/') . '/storage/media/' . $filename;

                $this->setThemeEnvValue($optionName, $value, $brand->brand_id);
            }
        }

        return response()->json([
            'status' => 'true',
            'title' => 'Settings Updated',
            'message' => 'The theme settings have been updated successfully.',
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * Set environmental option helper for theme settings.
     */
    private function setThemeEnvValue(string $optionName, string $value, string $brandId): void
    {
        $row = \App\Models\PpEnv::query()
            ->where('brand_id', $brandId)
            ->where('option_name', $optionName)
            ->first();

        if ($row === null) {
            \App\Models\PpEnv::query()->create([
                'brand_id' => $brandId,
                'option_name' => $optionName,
                'value' => $value,
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
            return;
        }

        $row->update([
            'value' => $value,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
