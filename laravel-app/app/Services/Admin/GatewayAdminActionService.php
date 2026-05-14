<?php

namespace App\Services\Admin;

// Ensure legacy money_sanitize and related functions are available
if (!function_exists('money_sanitize')) {
    require_once base_path('app/Support/zp-functions.php');
}

use App\Models\PpGateway;
use App\Models\PpGatewayParameter;

class GatewayAdminActionService
{
    public function create(string $gateway, string $brandId): array
    {
        if ($gateway === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $moduleFile = app_path('Modules/gateways/' . $gateway . '/class.php');
        if (!file_exists($moduleFile)) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        require_once $moduleFile;

        $slug = basename(app_path('Modules/gateways/' . $gateway));
        $class = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))) . 'Gateway';

        if (!class_exists($class)) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $gatewayObj = new $class();
        $gatewayInfo = $gatewayObj->info();
        $gatewayColor = $gatewayObj->color();

        $gatewayId = function_exists('generateItemID') ? (string) generateItemID() : bin2hex(random_bytes(10));
        $siteUrl = rtrim((string) config('app.url', '/'), '/') . '/';

        PpGateway::query()->create([
            'gateway_id' => $gatewayId,
            'brand_id' => $brandId,
            'slug' => $slug,
            'name' => (string) ($gatewayInfo['title'] ?? ''),
            'display' => (string) ($gatewayInfo['title'] ?? ''),
            'logo' => $siteUrl . 'module-asset/gateways/' . $slug . '/' . (string) ($gatewayInfo['logo'] ?? ''),
            'currency' => (string) ($gatewayInfo['currency'] ?? ''),
            'primary_color' => (string) ($gatewayColor['primary_color'] ?? ''),
            'text_color' => (string) ($gatewayColor['text_color'] ?? ''),
            'btn_color' => (string) ($gatewayColor['btn_color'] ?? ''),
            'btn_text_color' => (string) ($gatewayColor['btn_text_color'] ?? ''),
            'tab' => (string) ($gatewayInfo['tab'] ?? ''),
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Gateway Created',
            'message' => 'The gateway has been created successfully.',
        ];
    }

    public function list(array $input, string $brandId): array
    {
        $searchInput = trim((string) ($input['search_input'] ?? ''));
        $tabType = trim((string) ($input['tabType'] ?? ''));
        $filterStatus = trim((string) ($input['filter_status'] ?? ''));
        $filterStart = trim((string) ($input['filter_start'] ?? ''));
        $filterEnd = trim((string) ($input['filter_end'] ?? ''));

        $page = max(1, (int) ($input['page'] ?? 1));
        $rawShowLimit = (string) ($input['show_limit'] ?? '');
        $showLimit = $rawShowLimit === '' ? 999999 : (int) $rawShowLimit;
        if ($showLimit <= 0) {
            $showLimit = 8;
        }

        $query = PpGateway::query()->where('brand_id', $brandId);

        if ($tabType !== '' && $tabType !== 'all') {
            $query->where('tab', $tabType);
        }

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
            $query->where(function ($q) use ($searchInput): void {
                $q->where('name', 'like', "%{$searchInput}%")
                    ->orWhere('display', 'like', "%{$searchInput}%");
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

        $response = $rows->map(static function (PpGateway $row): array {
            return [
                'id' => (string) $row->gateway_id,
                'name' => (string) $row->name,
                'display' => (string) $row->display,
                'currency' => (string) $row->currency,
                'status' => (string) $row->status,
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

    public function delete(string $itemId, string $brandId): array
    {
        $exists = PpGateway::query()
            ->where('gateway_id', $itemId)
            ->where('brand_id', $brandId)
            ->exists();

        if ($exists) {
            PpGateway::query()->where('gateway_id', $itemId)->delete();
            PpGatewayParameter::query()->where('gateway_id', $itemId)->delete();
        }

        return [
            'status' => 'true',
            'title' => 'Gateway Deleted',
            'message' => 'The selected gateway have been deleted successfully.',
        ];
    }

    public function bulkAction(string $actionId, array $selectedIds, string $brandId, bool $canDelete, bool $canEdit): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No gateways selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (string) $id, $selectedIds), static fn ($id) => $id !== ''));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No gateways selected.',
            ];
        }

        foreach ($ids as $itemId) {
            $row = PpGateway::query()
                ->where('gateway_id', $itemId)
                ->where('brand_id', $brandId)
                ->first();

            if ($row === null) {
                continue;
            }

            if ($actionId === 'deleted' && $canDelete) {
                PpGateway::query()->where('gateway_id', $itemId)->delete();
                PpGatewayParameter::query()->where('gateway_id', $itemId)->delete();
                continue;
            }

            if ($actionId === 'activated' && $canEdit) {
                $row->update([
                    'status' => 'active',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
                continue;
            }

            if ($actionId === 'inactivated' && $canEdit) {
                $row->update([
                    'status' => 'inactive',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Gateways ' . $actionId,
            'message' => 'The selected gateways have been ' . $actionId . ' successfully.',
        ];
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

        $html .= '</ul>';

        return $html;
    }

    public function createGatewaySetting(array $postData, array $filesData, string $brandId, string $siteUrl): array
    {
        $gatewayName = trim((string) ($postData['gateway_name'] ?? ''));
        $displayName = trim((string) ($postData['display_name'] ?? ''));
        $minAmount = trim((string) ($postData['min_amount'] ?? ''));
        $maxAmount = trim((string) ($postData['max_amount'] ?? ''));
        $fixedCharge = trim((string) ($postData['fixed_charge'] ?? ''));
        $percentageCharge = trim((string) ($postData['percentage_charge'] ?? ''));
        $fixedDiscount = trim((string) ($postData['fixed_discount'] ?? ''));
        $percentageDiscount = trim((string) ($postData['percentage_discount'] ?? ''));
        $primaryColor = trim((string) ($postData['primary_color'] ?? ''));
        $textColor = trim((string) ($postData['text_color'] ?? ''));
        $btnColor = trim((string) ($postData['btn_color'] ?? ''));
        $btnTextColor = trim((string) ($postData['btn_text_color'] ?? ''));
        $status = trim((string) ($postData['status'] ?? ''));
        $currency = trim((string) ($postData['currency'] ?? ''));

        if ($displayName === '' || $minAmount === '' || $maxAmount === '' || $fixedCharge === '' || $percentageCharge === '' || $fixedDiscount === '' || $percentageDiscount === '' || $primaryColor === '' || $textColor === '' || $btnColor === '' || $btnTextColor === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $gatewayId = generateItemID();
        $logo = '--';

        $logoFile = $filesData['gateway_logo'] ?? null;
        if ($logoFile instanceof \Illuminate\Http\UploadedFile) {
            $maxSize = 2 * 1024 * 1024;
            if ($logoFile->getSize() <= $maxSize) {
                $ext = strtolower($logoFile->getClientOriginalExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $filename = strtolower(\Illuminate\Support\Str::random(10) . '_' . time() . '.' . $ext);
                    $path = storage_path('app/public/media');
                    $logoFile->move($path, $filename);
                    $logo = rtrim($siteUrl, '/') . '/storage/media/' . $filename;
                }
            }
        }

        PpGateway::query()->create([
            'gateway_id' => $gatewayId,
            'brand_id' => $brandId,
            'name' => $gatewayName,
            'slug' => strtolower(str_replace(' ', '-', $gatewayName)),
            'tab' => 'bank',
            'display' => $displayName,
            'logo' => $logo,
            'currency' => $currency,
            'min_allow' => money_sanitize($minAmount),
            'max_allow' => money_sanitize($maxAmount),
            'fixed_discount' => money_sanitize($fixedDiscount),
            'percentage_discount' => money_sanitize($percentageDiscount),
            'fixed_charge' => money_sanitize($fixedCharge),
            'percentage_charge' => money_sanitize($percentageCharge),
            'primary_color' => $primaryColor,
            'text_color' => $textColor,
            'btn_color' => $btnColor,
            'btn_text_color' => $btnTextColor,
            'status' => $status,
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        $configData = [];
        $skipFields = [
            'action', 'csrf_token', 'gateway_name', 'display_name',
            'min_amount', 'max_amount', 'fixed_charge', 'percentage_charge',
            'fixed_discount', 'percentage_discount', 'currency', 'status',
            'primary_color', 'text_color', 'btn_color', 'btn_text_color'
        ];

        foreach ($postData as $key => $value) {
            if (in_array($key, $skipFields, true)) {
                continue;
            }
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $configData[$key] = $value;
        }

        foreach ($filesData as $key => $file) {
            if ($key === 'gateway_logo' || !($file instanceof \Illuminate\Http\UploadedFile)) {
                continue;
            }
            $maxSize = 5 * 1024 * 1024;
            if ($file->getSize() <= $maxSize) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $filename = strtolower(\Illuminate\Support\Str::random(10) . '_' . time() . '.' . $ext);
                    $path = storage_path('app/public/media');
                    $file->move($path, $filename);
                    $configData[$key] = rtrim($siteUrl, '/') . '/storage/media/' . $filename;
                }
            }
        }

        foreach ($configData as $optName => $optVal) {
            $val = (string) $optVal;
            PpGatewayParameter::query()->create([
                'brand_id' => $brandId,
                'gateway_id' => $gatewayId,
                'option_name' => $optName,
                'value' => $val === '' ? '--' : $val,
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        return [
            'status' => 'true',
            'title' => 'Gateway Created',
            'message' => 'The gateway has been created successfully.',
        ];
    }

    public function updateGatewaySetting(array $postData, array $filesData, string $brandId, string $siteUrl): array
    {
        $gatewayId = trim((string) ($postData['gateway-id'] ?? ''));
        $displayName = trim((string) ($postData['display_name'] ?? ''));
        $minAmount = trim((string) ($postData['min_amount'] ?? ''));
        $maxAmount = trim((string) ($postData['max_amount'] ?? ''));
        $fixedCharge = trim((string) ($postData['fixed_charge'] ?? ''));
        $percentageCharge = trim((string) ($postData['percentage_charge'] ?? ''));
        $fixedDiscount = trim((string) ($postData['fixed_discount'] ?? ''));
        $percentageDiscount = trim((string) ($postData['percentage_discount'] ?? ''));
        $primaryColor = trim((string) ($postData['primary_color'] ?? ''));
        $textColor = trim((string) ($postData['text_color'] ?? ''));
        $btnColor = trim((string) ($postData['btn_color'] ?? ''));
        $btnTextColor = trim((string) ($postData['btn_text_color'] ?? ''));
        $status = trim((string) ($postData['status'] ?? ''));
        $currency = trim((string) ($postData['currency'] ?? ''));

        if ($gatewayId === '' || $displayName === '' || $minAmount === '' || $maxAmount === '' || $fixedCharge === '' || $percentageCharge === '' || $fixedDiscount === '' || $percentageDiscount === '' || $primaryColor === '' || $textColor === '' || $btnColor === '' || $btnTextColor === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $gateway = PpGateway::query()->where('gateway_id', $gatewayId)->where('brand_id', $brandId)->first();
        if ($gateway === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid Gateway ID',
            ];
        }

        $logo = (string) $gateway->logo;
        $logoFile = $filesData['gateway_logo'] ?? null;
        if ($logoFile instanceof \Illuminate\Http\UploadedFile) {
            $maxSize = 2 * 1024 * 1024;
            if ($logoFile->getSize() <= $maxSize) {
                $ext = strtolower($logoFile->getClientOriginalExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $filename = strtolower(\Illuminate\Support\Str::random(10) . '_' . time() . '.' . $ext);
                    $path = storage_path('app/public/media');
                    $logoFile->move($path, $filename);
                    $logo = rtrim($siteUrl, '/') . '/storage/media/' . $filename;
                }
            }
        }

        $gateway->update([
            'display' => $displayName,
            'logo' => $logo,
            'currency' => $currency,
            'min_allow' => money_sanitize($minAmount),
            'max_allow' => money_sanitize($maxAmount),
            'fixed_discount' => money_sanitize($fixedDiscount),
            'percentage_discount' => money_sanitize($percentageDiscount),
            'fixed_charge' => money_sanitize($fixedCharge),
            'percentage_charge' => money_sanitize($percentageCharge),
            'primary_color' => $primaryColor,
            'text_color' => $textColor,
            'btn_color' => $btnColor,
            'btn_text_color' => $btnTextColor,
            'status' => $status,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        $configData = [];
        $skipFields = [
            'action', 'gateway-id', 'csrf_token', 'gateway_name', 'display_name',
            'min_amount', 'max_amount', 'fixed_charge', 'percentage_charge',
            'fixed_discount', 'percentage_discount', 'currency', 'status', 'gateway_logo',
            'primary_color', 'text_color', 'btn_color', 'btn_text_color'
        ];

        foreach ($postData as $key => $value) {
            if (in_array($key, $skipFields, true)) {
                continue;
            }
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $configData[$key] = $value;
        }

        foreach ($filesData as $key => $file) {
            if ($key === 'gateway_logo' || !($file instanceof \Illuminate\Http\UploadedFile)) {
                continue;
            }
            $maxSize = 5 * 1024 * 1024;
            if ($file->getSize() <= $maxSize) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $filename = strtolower(\Illuminate\Support\Str::random(10) . '_' . time() . '.' . $ext);
                    $path = storage_path('app/public/media');
                    $file->move($path, $filename);
                    $configData[$key] = rtrim($siteUrl, '/') . '/storage/media/' . $filename;
                }
            }
        }

        foreach ($configData as $optName => $optVal) {
            $val = (string) $optVal;
            $param = PpGatewayParameter::query()
                ->where('gateway_id', $gatewayId)
                ->where('brand_id', $brandId)
                ->where('option_name', $optName)
                ->first();

            if ($param !== null) {
                $param->update([
                    'value' => $val === '' ? '--' : $val,
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            } else {
                PpGatewayParameter::query()->create([
                    'brand_id' => $brandId,
                    'gateway_id' => $gatewayId,
                    'option_name' => $optName,
                    'value' => $val === '' ? '--' : $val,
                    'created_date' => now()->format('Y-m-d H:i:s'),
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Gateway Updated',
            'message' => 'The gateway has been updated successfully.',
        ];
    }
}
