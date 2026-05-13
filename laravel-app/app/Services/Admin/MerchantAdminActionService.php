<?php

namespace App\Services\Admin;

use App\Models\PpAdmin;
use App\Models\PpBrand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class MerchantAdminActionService
{
    /**
     * List merchants with pagination and filters.
     */
    public function list(array $params, string $userType = 'superadmin', string $currentAdminAId = ''): array
    {
        $search = $params['search_input'] ?? '';
        $limit = (int) ($params['show_limit'] ?? 10);
        $page = (int) ($params['page'] ?? 1);
        $statusFilter = $params['filter_status'] ?? '';
        $startFilter = $params['filter_start'] ?? '';
        $endFilter = $params['filter_end'] ?? '';

        $query = PpAdmin::where('user_type', 'merchant')
            ->leftJoin('pp_permission', 'pp_admin.a_id', '=', 'pp_permission.a_id')
            ->leftJoin('pp_brands', 'pp_permission.brand_id', '=', 'pp_brands.brand_id')
            ->select(
                'pp_admin.*',
                'pp_brands.name as brand_name',
                'pp_brands.brand_id'
            );

        // Multi-tenant filtering: Merchants only see themselves
        if ($userType !== 'superadmin' && $currentAdminAId !== '') {
            $query->where('pp_admin.a_id', $currentAdminAId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('pp_admin.full_name', 'like', "%$search%")
                  ->orWhere('pp_admin.username', 'like', "%$search%")
                  ->orWhere('pp_admin.email', 'like', "%$search%")
                  ->orWhere('pp_brands.name', 'like', "%$search%");
            });
        }

        if ($statusFilter !== '') {
            $query->where('pp_admin.status', $statusFilter);
        }

        if ($startFilter !== '') {
            $query->whereDate('pp_admin.created_date', '>=', $startFilter);
        }

        if ($endFilter !== '') {
            $query->whereDate('pp_admin.created_date', '<=', $endFilter);
        }

        $totalRows = $query->count();

        if ($totalRows === 0) {
            return [
                'status' => 'false',
                'title' => 'Nothing Here Yet',
                'message' => 'No data is available at the moment.',
            ];
        }

        $totalPages = ceil($totalRows / $limit);
        $offset = ($page - 1) * $limit;

        $merchants = $query->orderBy('pp_admin.id', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $response = $merchants->map(function ($merchant) {
            return [
                'id' => $merchant->a_id,
                'name' => $merchant->full_name,
                'username' => $merchant->username,
                'email' => $merchant->email,
                'brand' => $merchant->brand_name ?? 'N/A',
                'brand_id' => $merchant->brand_id,
                'status' => $merchant->status,
                'created_date' => $merchant->created_date,
            ];
        });

        $start = $offset + 1;
        $end = min($offset + $limit, $totalRows);

        return [
            'status' => 'true',
            'response' => $response,
            'datatableInfo' => "Showing <strong>$start to $end</strong> of <strong>$totalRows entries</strong>",
            'pagination' => $this->buildPagination($page, $totalPages),
        ];
    }

    /**
     * Create a new merchant.
     */
    public function create(array $data): array
    {
        $fullName = $data['full_name'] ?? '';
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $brandName = $data['brand_name'] ?? '';
        $identifyName = $data['identify_name'] ?? '';

        if (empty($fullName) || empty($username) || empty($email) || empty($password) || empty($brandName) || empty($identifyName)) {
            return [
                'status' => 'false',
                'title' => 'Validation Error',
                'message' => 'All mandatory fields are required.',
            ];
        }

        if (PpAdmin::where('username', $username)->exists()) {
            return [
                'status' => 'false',
                'title' => 'Duplicate Username',
                'message' => 'This username is already taken.',
            ];
        }

        if (PpAdmin::where('email', $email)->exists()) {
            return [
                'status' => 'false',
                'title' => 'Duplicate Email',
                'message' => 'This email address is already registered.',
            ];
        }

        if (PpBrand::where('identify_name', $identifyName)->exists()) {
            return [
                'status' => 'false',
                'title' => 'Duplicate Brand',
                'message' => 'The brand identify name is already in use.',
            ];
        }

        try {
            DB::beginTransaction();

            // 1. Create Brand
            $brandId = 'BRD' . strtoupper(Str::random(10));
            
            // Handle logo and favicon if provided
            $logoPath = $data['primary_logo_path'] ?? null;
            $faviconPath = $data['favicon_path'] ?? null;

            $brand = PpBrand::create([
                'brand_id' => $brandId,
                'name' => $brandName,
                'identify_name' => $identifyName,
                'logo' => $logoPath,
                'favicon' => $faviconPath,
                'status' => 'active',
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
                'theme' => 'twenty-six',
                'timezone' => 'Asia/Dhaka',
                'language' => 'en',
                'currency_code' => 'BDT',
            ]);

            // 2. Create Admin Account (Merchant)
            $aId = generateItemID();
            $merchant = PpAdmin::create([
                'a_id' => $aId,
                'full_name' => $fullName,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'user_type' => 'merchant',
                'status' => 'active',
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);

            // 3. Setup Permissions
            // Use the standard permission schema for admins
            $defaultPermissions = json_encode(permissionSchema());

            DB::table('pp_permission')->insert([
                'a_id' => $aId,
                'brand_id' => $brandId,
                'permission' => $defaultPermissions,
                'status' => 'active',
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);

            DB::commit();

            return [
                'status' => 'true',
                'title' => 'Merchant Created',
                'message' => 'The merchant and their brand have been successfully created.',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => 'false',
                'title' => 'System Error',
                'message' => 'Failed to create merchant: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle bulk actions for merchants.
     */
    public function bulkAction(string $actionId, array $selectedIds): array
    {
        if (empty($selectedIds)) {
            return [
                'status' => 'false',
                'title' => 'No Selection',
                'message' => 'Please select at least one merchant.',
            ];
        }

        $status = match($actionId) {
            'activated' => 'active',
            'suspended' => 'suspend',
            default => null
        };

        if ($status) {
            PpAdmin::whereIn('a_id', $selectedIds)->update(['status' => $status]);
            return [
                'status' => 'true',
                'title' => 'Success',
                'message' => "The selected merchants have been $actionId.",
            ];
        }

        return [
            'status' => 'false',
            'title' => 'Invalid Action',
            'message' => 'The requested bulk action is not supported.',
        ];
    }

    /**
     * Build pagination HTML (Legacy Tabler style)
     */
    private function buildPagination(int $currentPage, int $totalPages): string
    {
        if ($totalPages <= 1) return '';

        $html = '<ul class="pagination m-0 ms-auto">';
        
        // Previous
        $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
        $prevPage = $currentPage - 1;
        $html .= "<li class=\"page-item $prevDisabled\"><a class=\"page-link\" href=\"#\" data-page=\"$prevPage\"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><polyline points=\"15 6 9 12 15 18\" /></svg></a></li>";

        // Pages
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = $i === $currentPage ? 'active' : '';
            $html .= "<li class=\"page-item $active\"><a class=\"page-link\" href=\"#\" data-page=\"$i\">$i</a></li>";
        }

        // Next
        $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
        $nextPage = $currentPage + 1;
        $html .= "<li class=\"page-item $nextDisabled\"><a class=\"page-link\" href=\"#\" data-page=\"$nextPage\"><svg xmlns=\"http://www.w3.org/2000/svg\" class=\"icon\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" stroke-width=\"2\" stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path stroke=\"none\" d=\"M0 0h24v24H0z\" fill=\"none\"/><polyline points=\"9 6 15 12 9 18\" /></svg></a></li>";

        $html .= '</ul>';
        return $html;
    }

    /**
     * Upload brand file and return relative path.
     */
    public function uploadFile(UploadedFile $file, string $type): string
    {
        $filename = $type . '_' . Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = public_path('pp-media/storage');
        
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file->move($path, $filename);
        
        // Return full URL path as expected by the legacy system
        return url('pp-media/storage/' . $filename);
    }
}
