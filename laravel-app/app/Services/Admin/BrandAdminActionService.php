<?php

namespace App\Services\Admin;

// Ensure legacy money_sanitize and related functions are available
if (!function_exists('money_sanitize')) {
    require_once base_path('app/Support/zp-functions.php');
}

use App\Models\PpBrand;
use Illuminate\Support\Facades\DB;

class BrandAdminActionService
{
    public function list(array $input, string $currentBrandId, string $brandTimezone, string $userType = 'staff'): array
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

        // Multi-tenant filtering: Merchants only see their own brand
        if ($userType !== 'superadmin') {
            $query->where('brand_id', $currentBrandId);
        }

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

        $response = $rows->map(static function (PpBrand $row) use ($brandTimezone): array {
            return [
                'id' => (string) $row->brand_id,
                'identify_name' => (string) $row->identify_name,
                'brand_name' => (string) $row->name,
                'created_date' => convertUTCtoUserTZ((string) $row->created_date, $brandTimezone, 'M d, Y h:i A'),
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

    private function buildPaginationHtml(int $currentPage, int $totalPages): string
    {
        $html = '<ul class="pagination m-0 ms-auto">';

        $html .= '<li class="page-item' . ($currentPage <= 1 ? ' disabled' : '') . '">'
            . '<button class="page-link" ' . ($currentPage > 1 ? 'data-page="' . ($currentPage - 1) . '"' : '') . '>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">'
            . '<path d="M15 6l-6 6l6 6"></path>'
            . '</svg>'
            . '</button>'
            . '</li>';

        for ($i = 1; $i <= $totalPages; $i++) {
            $html .= '<li class="page-item' . ($i === $currentPage ? ' active' : '') . '">'
                . '<button class="page-link" data-page="' . $i . '">' . $i . '</button>'
                . '</li>';
        }

        $html .= '<li class="page-item' . ($currentPage >= $totalPages ? ' disabled' : '') . '">'
            . '<button class="page-link" ' . ($currentPage < $totalPages ? 'data-page="' . ($currentPage + 1) . '"' : '') . '>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">'
            . '<path d="M9 6l6 6l-6 6"></path>'
            . '</svg>'
            . '</button>'
            . '</li>';

        $html .= '</ul>';

        return $html;
    }

    public function infoById(int $itemId): array
    {
        $brand = PpBrand::query()->where('id', $itemId)->first();
        if ($brand === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        return [
            'status' => 'true',
            'response' => $brand->toArray(),
        ];
    }

    public function create(array $postData, string $siteUrl): array
    {
        $brandName = trim((string) ($postData['brand_name'] ?? ''));
        $identifyName = trim((string) ($postData['identify_name'] ?? ''));

        if ($brandName === '' || $identifyName === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields.',
            ];
        }

        if (PpBrand::query()->where('identify_name', $identifyName)->exists()) {
            return [
                'status' => 'false',
                'title' => 'Identify Name Exists',
                'message' => 'The identify name already exists.',
            ];
        }

        $brandId = 'BRD' . strtoupper(\Illuminate\Support\Str::random(10));

        PpBrand::create([
            'brand_id' => $brandId,
            'name' => $brandName,
            'identify_name' => $identifyName,
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
            'theme' => 'twenty-six',
            'timezone' => 'Asia/Dhaka',
            'language' => 'en',
            'currency_code' => 'BDT',
        ]);

        return [
            'status' => 'true',
            'title' => 'Brand Created',
            'message' => 'The brand has been created successfully.',
        ];
    }

    public function edit(array $postData): array
    {
        $itemId = (int) ($postData['ItemID'] ?? 0);
        $brandName = trim((string) ($postData['brand_name'] ?? ''));

        if ($brandName === '' || $itemId === 0) {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields.',
            ];
        }

        $brand = PpBrand::query()->where('id', $itemId)->first();
        if ($brand === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $brand->update([
            'name' => $brandName,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Brand Updated',
            'message' => 'The brand has been updated successfully.',
        ];
    }

    public function delete(int $itemId): array
    {
        $brand = PpBrand::query()->where('id', $itemId)->first();
        if ($brand === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $brandId = (string) $brand->brand_id;

        // Check if there are any admins or transactions for this brand
        if (DB::table('pp_permission')->where('brand_id', $brandId)->exists()) {
            return [
                'status' => 'false',
                'title' => 'Delete Failed',
                'message' => 'This brand has associated users and cannot be deleted.',
            ];
        }

        $brand->delete();

        return [
            'status' => 'true',
            'title' => 'Brand Deleted',
            'message' => 'The brand has been deleted successfully.',
        ];
    }

    public function bulkAction(string $actionId, array $selectedIds): array
    {
        if (empty($selectedIds)) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No brands selected.',
            ];
        }

        if ($actionId === 'delete') {
            foreach ($selectedIds as $id) {
                $this->delete((int) $id);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Bulk Action Successful',
            'message' => 'The selected brands have been updated.',
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
                    $path = storage_path('app/public/media');
                    $faviconFile->move($path, $filename);
                    $brandingFavicon = rtrim($siteUrl, '/') . '/storage/media/' . $filename;
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
                    $path = storage_path('app/public/media');
                    $logoFile->move($path, $filename);
                    $brandingLogo = rtrim($siteUrl, '/') . '/storage/media/' . $filename;
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
