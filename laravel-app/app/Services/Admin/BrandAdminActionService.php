<?php

namespace App\Services\Admin;

// Ensure legacy money_sanitize and related functions are available
if (!function_exists('money_sanitize')) {
    require_once base_path('pp-content/pp-include/pp-functions.php');
}

use App\Models\PpBrand;
use Illuminate\Support\Facades\DB;

class BrandAdminActionService
{
    public function list(array $input, string $currentBrandId, string $brandTimezone): array
    {
        $searchInput = trim((string) ($input['search_input'] ?? ''));
        $filterStart = trim((string) ($input['filter_start'] ?? ''));
        $filterEnd = trim((string) ($input['filter_end'] ?? ''));

        $page = max(1, (int) ($input['page'] ?? 1));
        $rawShowLimit = (string) ($input['show_limit'] ?? '');
        $showLimit = $rawShowLimit === '' ? 999999 : (int) $rawShowLimit;
        if ($showLimit <= 0) {
            $showLimit = 8;
        }

        $query = PpBrand::query()->where('identify_name', '!=', '');

        if ($filterStart !== '') {
            $query->where('created_date', '>=', $filterStart . ' 00:00:00');
        }

        if ($filterEnd !== '') {
            $query->where('created_date', '<=', $filterEnd . ' 23:59:59');
        }

        if ($searchInput !== '') {
            $query->where(function ($q) use ($searchInput): void {
                $q->where('identify_name', 'like', "%{$searchInput}%")
                    ->orWhere('name', 'like', "%{$searchInput}%");
            });
        }

        $totalRecords = (clone $query)->count();
        $offset = ($page - 1) * $showLimit;

        $rows = (clone $query)
            ->orderByDesc('id')
            ->offset($offset)
            ->limit($showLimit)
            ->get();

        if ($rows->isEmpty()) {
            return [
                'status' => 'false',
                'title' => 'Nothing Here Yet',
                'message' => 'No data is available at the moment.',
            ];
        }

        $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;

        $response = $rows->map(function (PpBrand $row) use ($timezone, $currentBrandId): array {
            $deletable = 'true';
            if ((int) $row->id === 1 || (string) $row->brand_id === $currentBrandId) {
                $deletable = 'false';
            }

            return [
                'id' => (string) $row->brand_id,
                'db_id' => (int) $row->id,
                'deleteable' => $deletable,
                'identify_name' => (string) $row->identify_name,
                'name' => (string) $row->name,
                'created_date' => convertUTCtoUserTZ((string) $row->created_date, $timezone, 'M d, Y h:i A'),
                'updated_date' => convertUTCtoUserTZ((string) $row->updated_date, $timezone, 'M d, Y h:i A'),
            ];
        })->values()->all();

        $totalPages = max(1, (int) ceil($totalRecords / $showLimit));
        $start = $offset + 1;
        $end = min($offset + $showLimit, $totalRecords);

        return [
            'status' => 'true',
            'response' => $response,
            'datatableInfo' => "Showing <strong>{$start} to {$end}</strong> of <strong>{$totalRecords} entries</strong>",
            'pagination' => $this->buildPaginationHtml($page, $totalPages),
        ];
    }

    public function bulkAction(string $actionId, array $selectedIds, string $currentBrandId, bool $canDelete): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No brands selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (string) $id, $selectedIds), static fn ($id) => $id !== ''));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No brands selected.',
            ];
        }

        foreach ($ids as $itemId) {
            $brand = PpBrand::query()->where('brand_id', $itemId)->first();
            if ($brand === null) {
                continue;
            }

            if ($actionId === 'deleted') {
                if ((int) $brand->id === 1 || (string) $brand->brand_id === $currentBrandId) {
                    continue;
                }

                if (!$canDelete) {
                    continue;
                }

                $this->deleteBrandWithDependencies((string) $brand->brand_id);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Brands ' . $actionId,
            'message' => 'The selected brands have been ' . $actionId . ' successfully.',
        ];
    }

    public function delete(string $itemId, string $currentBrandId): array
    {
        $brand = PpBrand::query()->where('brand_id', $itemId)->first();
        if ($brand === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        if ((int) $brand->id === 1 || (string) $brand->brand_id === $currentBrandId) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $this->deleteBrandWithDependencies((string) $brand->brand_id);

        return [
            'status' => 'true',
            'title' => 'Brands Deleted',
            'message' => 'The selected brand have been deleted successfully.',
        ];
    }

    private function deleteBrandWithDependencies(string $brandId): void
    {
        DB::table('pp_brands')->where('brand_id', $brandId)->delete();
        DB::table('pp_api')->where('brand_id', $brandId)->delete();
        DB::table('pp_currency')->where('brand_id', $brandId)->delete();
        DB::table('pp_customer')->where('brand_id', $brandId)->delete();
        DB::table('pp_env')->where('brand_id', $brandId)->delete();
        DB::table('pp_faq')->where('brand_id', $brandId)->delete();
        DB::table('pp_gateways')->where('brand_id', $brandId)->delete();
        DB::table('pp_gateways_parameter')->where('brand_id', $brandId)->delete();
        DB::table('pp_invoice')->where('brand_id', $brandId)->delete();
        DB::table('pp_invoice_items')->where('brand_id', $brandId)->delete();

        $paymentLinkRefs = DB::table('pp_payment_link')
            ->where('brand_id', $brandId)
            ->pluck('ref')
            ->map(static fn ($ref) => (string) $ref)
            ->all();

        foreach ($paymentLinkRefs as $ref) {
            DB::table('pp_payment_link_field')->where('paymentLinkID', $ref)->delete();
        }

        DB::table('pp_payment_link')->where('brand_id', $brandId)->delete();
        DB::table('pp_permission')->where('brand_id', $brandId)->delete();
        DB::table('pp_transaction')->where('brand_id', $brandId)->delete();
        DB::table('pp_webhook_log')->where('brand_id', $brandId)->delete();
    }

    private function buildPaginationHtml(int $page, int $totalPages): string
    {
        $html = '<ul class="pagination m-0 ms-auto">';

        $html .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '">'
            . '<button class="page-link" ' . ($page > 1 ? 'data-page="' . ($page - 1) . '"' : '') . '>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">'
            . '<path d="M15 6l-6 6l6 6"></path>'
            . '</svg>'
            . '</button>'
            . '</li>';

        for ($i = 1; $i <= $totalPages; $i++) {
            $html .= '<li class="page-item' . ($i === $page ? ' active' : '') . '">'
                . '<button class="page-link" data-page="' . $i . '">' . $i . '</button>'
                . '</li>';
        }

        $html .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '">'
            . '<button class="page-link" ' . ($page < $totalPages ? 'data-page="' . ($page + 1) . '"' : '') . '>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">'
            . '<path d="M9 6l6 6l-6 6"></path>'
            . '</svg>'
            . '</button>'
            . '</li>';

        return $html;
    }

    public function createBrand(array $input, string $currentAdminRole, string $currentAdminAId): array|object
    {
        $brandName = trim((string) ($input['brand-name'] ?? ''));

        if ($brandName === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        if (PpBrand::query()->where('identify_name', $brandName)->exists()) {
            return [
                'status' => 'false',
                'title' => 'Duplicate Brand',
                'message' => 'A brand with this name already exists. Please choose a different name.',
            ];
        }

        $brandId = generateItemID();

        PpBrand::query()->create([
            'brand_id' => $brandId,
            'identify_name' => $brandName,
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        $permissionSchemaJson = json_encode(permissionSchema());

        DB::table('pp_permission')->insert([
            'brand_id' => $brandId,
            'a_id' => $currentAdminAId,
            'permission' => $permissionSchemaJson,
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('pp_currency')->insert([
            'brand_id' => $brandId,
            'code' => 'BDT',
            'symbol' => '৳',
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        if ($currentAdminRole !== 'admin') {
            $admins = DB::table('pp_admin')->where('role', 'admin')->get(['a_id']);
            foreach ($admins as $admin) {
                DB::table('pp_permission')->insert([
                    'brand_id' => $brandId,
                    'a_id' => (string) $admin->a_id,
                    'permission' => $permissionSchemaJson,
                    'created_date' => now()->format('Y-m-d H:i:s'),
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return \Illuminate\Support\Facades\Response::json([
            'status' => 'true',
            'title' => 'Brand Created',
            'message' => 'The brand has been created successfully.',
        ])->cookie('pp_brand', $brandId, 60 * 24 * 365, '/');
    }

    public function editBrand(array $input, string $currentGlobalBrandId, bool $isSuper = false): array
    {
        $brandName = trim((string) ($input['brand-name'] ?? ''));
        $brandId = trim((string) ($input['b_id'] ?? ''));

        if ($brandName === '' || $brandId === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $brand = PpBrand::query()->where('brand_id', $brandId)->first();
        if ($brand === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid brand id',
            ];
        }

        if (((int) $brand->id === 1 && !$isSuper) || (string) $brand->brand_id === $currentGlobalBrandId) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        if ((string) $brand->identify_name !== $brandName) {
            if (PpBrand::query()->where('identify_name', $brandName)->exists()) {
                return [
                    'status' => 'false',
                    'title' => 'Duplicate Brand',
                    'message' => 'A brand with this name already exists. Please choose a different name.',
                ];
            }
        }

        $brand->update([
            'identify_name' => $brandName,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Brand Updated',
            'message' => 'The brand has been updated successfully.',
        ];
    }

    public function updateGeneralSetting(array $postData, array $filesData, string $brandId, string $siteUrl): array
    {
        $siteName = trim((string) ($postData['site_name'] ?? ''));
        $defaultTimezone = trim((string) ($postData['default_timezone'] ?? ''));
        $defaultLanguage = trim((string) ($postData['default_language'] ?? ''));
        $defaultCurrency = trim((string) ($postData['default_currency'] ?? ''));
        $paymentTolerance = trim((string) ($postData['payment_tolerance'] ?? ''));

        $streetAddress = trim((string) ($postData['street_address'] ?? ''));
        $cityTown = trim((string) ($postData['city_town'] ?? ''));
        $postalCode = trim((string) ($postData['postal_code'] ?? ''));
        $country = trim((string) ($postData['country'] ?? ''));

        $supportPhoneNumber = trim((string) ($postData['support_phone_number'] ?? ''));
        $supportEmailAddress = trim((string) ($postData['support_email_address'] ?? ''));
        $supportWebsite = trim((string) ($postData['support_website'] ?? ''));
        $whatsappNumber = trim((string) ($postData['whatsapp_number'] ?? ''));
        $telegram = trim((string) ($postData['telegram'] ?? ''));
        $facebookMessenger = trim((string) ($postData['facebook_messenger'] ?? ''));
        $facebookPage = trim((string) ($postData['facebook_page'] ?? ''));
        $autoExchange = trim((string) ($postData['autoExchange'] ?? ''));

        if ($autoExchange === '' || $siteName === '' || $defaultTimezone === '' || $defaultLanguage === '' || $defaultCurrency === '' || $paymentTolerance === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $brand = PpBrand::query()->where('brand_id', $brandId)->first();
        if ($brand === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $brandingFavicon = (string) $brand->favicon;
        $faviconFile = $filesData['favicon'] ?? null;
        if ($faviconFile instanceof \Illuminate\Http\UploadedFile) {
            $maxSize = 2 * 1024 * 1024;
            if ($faviconFile->getSize() <= $maxSize) {
                $ext = strtolower($faviconFile->getClientOriginalExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $filename = strtolower(\Illuminate\Support\Str::random(10) . '_' . time() . '.' . $ext);
                    $path = public_path('pp-media/storage');
                    $faviconFile->move($path, $filename);
                    $brandingFavicon = rtrim($siteUrl, '/') . '/pp-media/storage/' . $filename;
                }
            }
        }

        $brandingLogo = (string) $brand->logo;
        $logoFile = $filesData['primary_logo'] ?? null;
        if ($logoFile instanceof \Illuminate\Http\UploadedFile) {
            $maxSize = 2 * 1024 * 1024;
            if ($logoFile->getSize() <= $maxSize) {
                $ext = strtolower($logoFile->getClientOriginalExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $filename = strtolower(\Illuminate\Support\Str::random(10) . '_' . time() . '.' . $ext);
                    $path = public_path('pp-media/storage');
                    $logoFile->move($path, $filename);
                    $brandingLogo = rtrim($siteUrl, '/') . '/pp-media/storage/' . $filename;
                }
            }
        }

        $brand->update([
            'autoExchange' => $autoExchange,
            'favicon' => $brandingFavicon,
            'logo' => $brandingLogo,
            'name' => $siteName,
            'timezone' => $defaultTimezone,
            'language' => $defaultLanguage,
            'currency_code' => $defaultCurrency,
            'payment_tolerance' => money_sanitize($paymentTolerance),
            'street_address' => $streetAddress,
            'city_town' => $cityTown,
            'postal_code' => $postalCode,
            'country' => $country,
            'support_phone_number' => $supportPhoneNumber,
            'support_email_address' => $supportEmailAddress,
            'support_website' => $supportWebsite,
            'whatsapp_number' => $whatsappNumber,
            'telegram' => $telegram,
            'facebook_messenger' => $facebookMessenger,
            'facebook_page' => $facebookPage,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Brand Setting Updated',
            'message' => 'The brand setting has been updated successfully.',
        ];
    }
}
