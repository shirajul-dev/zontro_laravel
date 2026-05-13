<?php

namespace App\Services\Admin;

use App\Models\PpAdmin;
use App\Models\PpBrand;
use App\Models\PpBrowserLog;
use App\Models\PpPermission;

class StaffAdminActionService
{
    public function staffList(array $input, string $currentAdminAId, string $brandTimezone, string $currentBrandId = '', string $userType = 'superadmin'): array
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

        $query = PpAdmin::query()
            ->select('pp_admin.*')
            ->where('pp_admin.role', 'staff')
            ->where('pp_admin.a_id', '!=', $currentAdminAId);

        // Multi-tenant filtering: Merchants only see staff for their brand
        if ($userType !== 'superadmin' && $currentBrandId !== '') {
            $query->join('pp_permission', 'pp_admin.a_id', '=', 'pp_permission.a_id')
                  ->where('pp_permission.brand_id', $currentBrandId);
        }

        if ($filterStart !== '') {
            $query->where('pp_admin.created_date', '>=', $filterStart . ' 00:00:00');
        }

        if ($filterEnd !== '') {
            $query->where('pp_admin.created_date', '<=', $filterEnd . ' 23:59:59');
        }

        if ($filterStatus !== '') {
            $query->where('pp_admin.status', $filterStatus);
        }

        if ($searchInput !== '') {
            $query->where(function ($q) use ($searchInput): void {
                $q->where('pp_admin.full_name', 'like', "%{$searchInput}%")
                    ->orWhere('pp_admin.email', 'like', "%{$searchInput}%")
                    ->orWhere('pp_admin.username', 'like', "%{$searchInput}%");
            });
        }

        $totalRecords = (clone $query)->count();
        $offset = ($page - 1) * $showLimit;

        $rows = (clone $query)
            ->orderByDesc('pp_admin.id')
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

        $response = $rows->map(static function (PpAdmin $row) use ($timezone): array {
            return [
                'id' => (string) $row->a_id,
                'name' => (string) $row->full_name,
                'username' => (string) $row->username,
                'email' => (string) $row->email,
                'status' => (string) $row->status,
                'role' => (string) $row->role,
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

    public function staffBulkAction(string $actionId, array $selectedIds, string $currentAdminAId, bool $canDelete, bool $canEdit): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No Staff selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (string) $id, $selectedIds), static fn ($id) => $id !== ''));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No Staff selected.',
            ];
        }

        foreach ($ids as $itemId) {
            $staff = PpAdmin::query()
                ->where('role', 'staff')
                ->where('a_id', $itemId)
                ->first();

            if ($staff === null || $itemId === $currentAdminAId) {
                continue;
            }

            if ($actionId === 'deleted' && $canDelete) {
                PpPermission::query()->where('a_id', (string) $staff->a_id)->delete();
                PpBrowserLog::query()->where('a_id', (string) $staff->a_id)->delete();
                PpAdmin::query()->where('a_id', (string) $staff->a_id)->delete();
                continue;
            }

            if ($actionId === 'activated' && $canEdit) {
                $staff->update([
                    'status' => 'active',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
                continue;
            }

            if ($actionId === 'suspended' && $canEdit) {
                $staff->update([
                    'status' => 'suspend',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Staff ' . $actionId,
            'message' => 'The selected staff members have been ' . $actionId . ' successfully.',
        ];
    }

    public function staffDelete(string $itemId, string $currentAdminAId): array
    {
        $staff = PpAdmin::query()
            ->where('role', 'staff')
            ->where('a_id', $itemId)
            ->first();

        if ($staff === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No Staff selected.',
            ];
        }

        if ($itemId === $currentAdminAId) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'You cannot delete your own account.',
            ];
        }

        PpPermission::query()->where('a_id', (string) $staff->a_id)->delete();
        PpBrowserLog::query()->where('a_id', (string) $staff->a_id)->delete();
        PpAdmin::query()->where('id', (int) $staff->id)->delete();

        return [
            'status' => 'true',
            'title' => 'Staff Deleted',
            'message' => 'The staff member have been deleted successfully.',
        ];
    }

    public function permissionList(array $input, string $targetAId, string $currentAdminAId, int $currentAdminId, string $brandTimezone): array
    {
        $filterStatus = trim((string) ($input['filter_status'] ?? ''));
        $filterStart = trim((string) ($input['filter_start'] ?? ''));
        $filterEnd = trim((string) ($input['filter_end'] ?? ''));

        $page = max(1, (int) ($input['page'] ?? 1));
        $rawShowLimit = (string) ($input['show_limit'] ?? '');
        $showLimit = $rawShowLimit === '' ? 999999 : (int) $rawShowLimit;
        if ($showLimit <= 0) {
            $showLimit = 8;
        }

        $targetStaff = PpAdmin::query()
            ->where('a_id', $targetAId)
            ->where('id', '!=', $currentAdminId)
            ->where('role', 'staff')
            ->first();

        if ($targetStaff === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid Staff ID',
            ];
        }

        if ($currentAdminAId === $targetAId) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => "You can't edit your info",
            ];
        }

        $query = PpPermission::query()->where('a_id', (string) $targetStaff->a_id);

        if ($filterStart !== '') {
            $query->where('created_date', '>=', $filterStart . ' 00:00:00');
        }

        if ($filterEnd !== '') {
            $query->where('created_date', '<=', $filterEnd . ' 23:59:59');
        }

        if ($filterStatus !== '') {
            $query->where('status', $filterStatus);
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

        $brandMap = PpBrand::query()
            ->whereIn('brand_id', $rows->pluck('brand_id')->filter()->unique()->values())
            ->get(['brand_id', 'identify_name', 'name'])
            ->keyBy('brand_id');

        $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;

        $response = [];
        foreach ($rows as $row) {
            $brand = $brandMap->get((string) $row->brand_id);
            if ($brand === null) {
                continue;
            }

            $response[] = [
                'id' => (int) $row->id,
                'identify_name' => (string) $brand->identify_name,
                'brandname' => (string) $brand->name,
                'status' => (string) $row->status,
                'created_date' => convertUTCtoUserTZ((string) $row->created_date, $timezone, 'M d, Y h:i A'),
                'updated_date' => convertUTCtoUserTZ((string) $row->updated_date, $timezone, 'M d, Y h:i A'),
            ];
        }

        if ($response === []) {
            return [
                'status' => 'false',
                'title' => 'Nothing Here Yet',
                'message' => 'No data is available at the moment.',
            ];
        }

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

    public function permissionBulkAction(string $actionId, array $selectedIds, int $currentAdminId, string $currentAdminAId, bool $canDeletePermissionOf, bool $canEditPermission): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No Staff selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (string) $id, $selectedIds), static fn ($id) => $id !== ''));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No Staff selected.',
            ];
        }

        foreach ($ids as $itemId) {
            $permission = PpPermission::query()->where('id', $itemId)->first();
            if ($permission === null) {
                continue;
            }

            if ((string) $permission->a_id === $currentAdminAId) {
                continue;
            }

            $adminOwner = PpAdmin::query()
                ->where('role', 'admin')
                ->where('a_id', (string) $permission->a_id)
                ->first();
            if ($adminOwner !== null) {
                continue;
            }

            if ($actionId === 'deleted' && $canDeletePermissionOf) {
                $permission->delete();
                continue;
            }

            if ($actionId === 'activated' && $canEditPermission) {
                $permission->update([
                    'status' => 'active',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
                continue;
            }

            if ($actionId === 'suspended' && $canEditPermission) {
                $permission->update([
                    'status' => 'suspend',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Staff Permissions ' . $actionId,
            'message' => 'The selected staff permissions have been ' . $actionId . ' successfully.',
        ];
    }

    public function permissionDelete(string $itemId, int $currentAdminId): array
    {
        $permission = PpPermission::query()->where('id', $itemId)->first();
        if ($permission === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid Permission ID',
            ];
        }

        $staff = PpAdmin::query()
            ->where('role', 'staff')
            ->where('a_id', (string) $permission->a_id)
            ->first();

        if ($staff === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No Staff selected.',
            ];
        }

        if ((int) $staff->id === $currentAdminId) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'You cannot delete your own permission.',
            ];
        }

        $permission->delete();

        return [
            'status' => 'true',
            'title' => 'Staff Permission Deleted',
            'message' => 'The staff member permission have been deleted successfully.',
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

        return $html;
    }

    public function staffCreate(array $input): array
    {
        $fullname = trim((string) ($input['full-name'] ?? ''));
        $username = trim((string) ($input['username'] ?? ''));
        $emailAddress = trim((string) ($input['email-address'] ?? ''));
        $passwordstr = (string) ($input['password'] ?? '');
        $brands = $input['brands'] ?? [];

        if ($fullname === '' || $username === '' || $emailAddress === '' || $passwordstr === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'false',
                'title' => 'Invalid Email',
                'message' => 'Please enter a valid email address.',
            ];
        }

        if (empty($brands) || !is_array($brands)) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'You need to allow minimum 1 brand to create a staff',
            ];
        }

        if (PpAdmin::query()->where('username', $username)->exists()) {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Username already exits.',
            ];
        }

        if (PpAdmin::query()->where('email', $emailAddress)->exists()) {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Email Address already exits.',
            ];
        }

        $newTempPassword = generateStrongPassword(8);
        $password = \Illuminate\Support\Facades\Hash::make($passwordstr);
        $tempPassword = \Illuminate\Support\Facades\Hash::make($newTempPassword);

        $aId = generateItemID();

        PpAdmin::query()->create([
            'a_id' => $aId,
            'full_name' => $fullname,
            'username' => $username,
            'email' => $emailAddress,
            'password' => $password,
            'temp_password' => $tempPassword,
            'role' => 'staff',
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        $schema = permissionSchema();
        $inputPermissions = json_decode($input['permissions_json'] ?? '{}', true);
        if (!is_array($inputPermissions)) {
            $inputPermissions = [];
        }

        $newPermissions = [
            'resources' => [],
            'pages' => []
        ];

        if (isset($schema['resources']) && is_array($schema['resources'])) {
            foreach ($schema['resources'] as $module => $actions) {
                foreach ($actions as $action => $_) {
                    $newPermissions['resources'][$module][$action] = !empty($inputPermissions['resources'][$module][$action]);
                }
            }
        }

        if (isset($schema['pages']) && is_array($schema['pages'])) {
            foreach ($schema['pages'] as $page => $_) {
                $newPermissions['pages'][$page] = !empty($inputPermissions['pages'][$page]);
            }
        }

        $permissionJson = json_encode($newPermissions);

        foreach ($brands as $brandId) {
            if (PpBrand::query()->where('brand_id', $brandId)->exists()) {
                PpPermission::query()->create([
                    'brand_id' => $brandId,
                    'a_id' => $aId,
                    'permission' => $permissionJson,
                    'created_date' => now()->format('Y-m-d H:i:s'),
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Staff Created',
            'message' => 'The staff account has been created successfully.',
        ];
    }

    public function staffUpdate(array $input, string $currentAdminAId): array
    {
        $fullname = trim((string) ($input['full-name'] ?? ''));
        $username = trim((string) ($input['username'] ?? ''));
        $emailAddress = trim((string) ($input['email-address'] ?? ''));
        $aId = trim((string) ($input['a_id'] ?? ''));

        if ($fullname === '' || $username === '' || $emailAddress === '' || $aId === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        if ($currentAdminAId === $aId) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'You can\'t edit your info',
            ];
        }

        $staff = PpAdmin::query()->where('role', 'staff')->where('a_id', $aId)->first();
        if ($staff === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Staff not found.',
            ];
        }

        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'false',
                'title' => 'Invalid Email',
                'message' => 'Please enter a valid email address.',
            ];
        }

        if ($username !== (string) $staff->username) {
            if (PpAdmin::query()->where('username', $username)->exists()) {
                return [
                    'status' => 'false',
                    'title' => 'Incomplete Information',
                    'message' => 'Username already exits.',
                ];
            }
        }

        if ($emailAddress !== (string) $staff->email) {
            if (PpAdmin::query()->where('email', $emailAddress)->exists()) {
                return [
                    'status' => 'false',
                    'title' => 'Incomplete Information',
                    'message' => 'Email Address already exits.',
                ];
            }
        }

        $passwordstr = (string) ($input['password'] ?? '');
        $updateData = [
            'full_name' => $fullname,
            'username' => $username,
            'email' => $emailAddress,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ];

        if ($passwordstr !== '') {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($passwordstr);
            $updateData['temp_password'] = \Illuminate\Support\Facades\Hash::make(generateStrongPassword(8));
        }

        $staff->update($updateData);

        return [
            'status' => 'true',
            'title' => 'Profile Updated',
            'message' => 'The staff profile information has been updated successfully.',
        ];
    }

    public function staffBrandAdd(array $input, string $currentAdminAId): array
    {
        $aId = trim((string) ($input['a_id'] ?? ''));
        $brands = $input['brands'] ?? [];

        if (empty($brands) || !is_array($brands) || $aId === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        if ($currentAdminAId === $aId) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'You can\'t edit your info',
            ];
        }

        $staff = PpAdmin::query()->where('role', 'staff')->where('a_id', $aId)->first();
        if ($staff === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Staff not found.',
            ];
        }

        $schema = permissionSchema();
        $inputPermissions = json_decode($input['permissions_json'] ?? '{}', true);
        if (!is_array($inputPermissions)) {
            $inputPermissions = [];
        }

        $newPermissions = [
            'resources' => [],
            'pages' => []
        ];

        if (isset($schema['resources']) && is_array($schema['resources'])) {
            foreach ($schema['resources'] as $module => $actions) {
                foreach ($actions as $action => $_) {
                    $newPermissions['resources'][$module][$action] = !empty($inputPermissions['resources'][$module][$action]);
                }
            }
        }

        if (isset($schema['pages']) && is_array($schema['pages'])) {
            foreach ($schema['pages'] as $page => $_) {
                $newPermissions['pages'][$page] = !empty($inputPermissions['pages'][$page]);
            }
        }

        $permissionJson = json_encode($newPermissions);

        foreach ($brands as $brandId) {
            if (PpBrand::query()->where('brand_id', $brandId)->exists() && !PpPermission::query()->where('brand_id', $brandId)->where('a_id', $aId)->exists()) {
                PpPermission::query()->create([
                    'brand_id' => $brandId,
                    'a_id' => $aId,
                    'permission' => $permissionJson,
                    'created_date' => now()->format('Y-m-d H:i:s'),
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Staff Permissions Attached',
            'message' => 'The brand attached successfully to the staff.',
        ];
    }

    public function staffUpdatePermission(array $input, string $currentAdminAId): array
    {
        $id = trim((string) ($input['id'] ?? ''));

        if ($id === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $permissionModel = PpPermission::query()->where('id', $id)->first();

        if ($permissionModel === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Permission not found.',
            ];
        }

        if ((string) $permissionModel->a_id === $currentAdminAId) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'You can\'t edit your info',
            ];
        }

        $staff = PpAdmin::query()->where('role', 'staff')->where('a_id', (string) $permissionModel->a_id)->first();
        if ($staff === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Staff not found.',
            ];
        }

        $schema = permissionSchema();
        $inputPermissions = json_decode($input['permissions_json'] ?? '{}', true);
        if (!is_array($inputPermissions)) {
            $inputPermissions = [];
        }

        $newPermissions = [
            'resources' => [],
            'pages' => []
        ];

        if (isset($schema['resources']) && is_array($schema['resources'])) {
            foreach ($schema['resources'] as $module => $actions) {
                foreach ($actions as $action => $_) {
                    $newPermissions['resources'][$module][$action] = !empty($inputPermissions['resources'][$module][$action]);
                }
            }
        }

        if (isset($schema['pages']) && is_array($schema['pages'])) {
            foreach ($schema['pages'] as $page => $_) {
                $newPermissions['pages'][$page] = !empty($inputPermissions['pages'][$page]);
            }
        }

        $permissionJson = json_encode($newPermissions);
        $permissionModel->update([
            'permission' => $permissionJson,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Staff Permission Updated',
            'message' => 'The staff permission has been updated successfully.',
        ];
    }
}
