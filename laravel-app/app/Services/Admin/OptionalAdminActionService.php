<?php

namespace App\Services\Admin;

use App\Models\PpAddon;
use App\Models\PpAddonParameter;
use App\Models\PpBrand;
use App\Models\PpBrowserLog;

class OptionalAdminActionService
{
    public function activitiesList(array $input, string $adminAId, string $currentCookie, string $brandTimezone): array
    {
        $searchInput = trim((string) ($input['search_input'] ?? ''));
        $filterStatus = trim((string) ($input['filter_status'] ?? ''));
        $filterStart = trim((string) ($input['filter_start'] ?? ''));
        $filterEnd = trim((string) ($input['filter_end'] ?? ''));

        $page = max(1, (int) ($input['page'] ?? 1));
        $rawShowLimit = (string) ($input['show_limit'] ?? '');
        $showLimit = $rawShowLimit === '' ? 999999 : (int) $rawShowLimit;
        if ($showLimit <= 0) {
            $showLimit = 8;
        }

        $query = PpBrowserLog::query()->where('a_id', $adminAId);

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
                $q->where('browser', 'like', "%{$searchInput}%")
                    ->orWhere('device', 'like', "%{$searchInput}%")
                    ->orWhere('ip', 'like', "%{$searchInput}%");
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

        $response = $rows->map(static function (PpBrowserLog $row) use ($timezone, $currentCookie): array {
            return [
                'id' => (int) $row->id,
                'browser' => (string) $row->browser,
                'device' => (string) $row->device,
                'ip' => (string) $row->ip,
                'status' => (string) $row->status,
                'isequal' => ((string) $row->cookie === $currentCookie) ? 'matched' : '',
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

    public function createAddon(string $addon): array
    {
        if ($addon === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $moduleFile = app_path('Modules/addons/' . $addon . '/class.php');
        if (!file_exists($moduleFile)) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        require_once $moduleFile;

        $slug = basename(app_path('Modules/addons/' . $addon));
        $class = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))) . 'Addon';
        if (!class_exists($class)) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $addonObj = new $class();
        $addonInfo = $addonObj->info();

        $addonId = function_exists('generateItemID') ? (string) generateItemID() : bin2hex(random_bytes(10));

        PpAddon::query()->create([
            'addon_id' => $addonId,
            'slug' => $slug,
            'name' => (string) ($addonInfo['title'] ?? ''),
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Addon Created',
            'message' => 'The addon has been created successfully.',
        ];
    }

    public function addonsList(array $input): array
    {
        $searchInput = trim((string) ($input['search_input'] ?? ''));
        $filterStatus = trim((string) ($input['filter_status'] ?? ''));
        $filterStart = trim((string) ($input['filter_start'] ?? ''));
        $filterEnd = trim((string) ($input['filter_end'] ?? ''));

        $page = max(1, (int) ($input['page'] ?? 1));
        $rawShowLimit = (string) ($input['show_limit'] ?? '');
        $showLimit = $rawShowLimit === '' ? 999999 : (int) $rawShowLimit;
        if ($showLimit <= 0) {
            $showLimit = 8;
        }

        $query = PpAddon::query()->where('status', '!=', '--');

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

        $response = $rows->map(static fn (PpAddon $row): array => [
            'id' => (string) $row->addon_id,
            'name' => (string) $row->name,
            'status' => (string) $row->status,
        ])->values()->all();

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

    public function deleteAddon(string $itemId): array
    {
        $addon = PpAddon::query()->where('addon_id', $itemId)->first();
        if ($addon !== null) {
            PpAddon::query()->where('addon_id', $itemId)->delete();
            PpAddonParameter::query()->where('addon_id', $itemId)->delete();
        }

        return [
            'status' => 'true',
            'title' => 'Addon Deleted',
            'message' => 'The selected addon have been deleted successfully.',
        ];
    }

    public function addonsBulkAction(string $actionId, array $selectedIds, bool $canDelete, bool $canEdit): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No addons selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (string) $id, $selectedIds), static fn ($id) => $id !== ''));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No addons selected.',
            ];
        }

        foreach ($ids as $itemId) {
            $addon = PpAddon::query()->where('addon_id', $itemId)->first();
            if ($addon === null) {
                continue;
            }

            if ($actionId === 'deleted' && $canDelete) {
                PpAddon::query()->where('addon_id', $itemId)->delete();
                PpAddonParameter::query()->where('addon_id', $itemId)->delete();
                continue;
            }

            if ($actionId === 'activated' && $canEdit) {
                $addon->update([
                    'status' => 'active',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
                continue;
            }

            if ($actionId === 'inactivated' && $canEdit) {
                $addon->update([
                    'status' => 'inactive',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Addons ' . $actionId,
            'message' => 'The selected addons have been ' . $actionId . ' successfully.',
        ];
    }

    public function activateTheme(string $slug, string $brandId): array
    {
        $brand = PpBrand::query()->where('brand_id', $brandId)->first();
        if ($brand !== null) {
            $brand->update([
                'theme' => $slug,
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        return [
            'status' => 'true',
            'title' => 'Theme Activated',
            'message' => 'The theme has been activated successfully.',
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
}
