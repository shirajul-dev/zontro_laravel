<?php

namespace App\Http\Controllers\Admin\Traits;

use App\Models\PpAdmin;
use App\Models\PpBrand;
use App\Models\PpPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait HasLegacyEnvironment
{
    /**
     * Set up shared legacy globals for both page rendering and AJAX actions.
     */
    protected function setupLegacyGlobals(Request $request): array
    {
        $admin = $this->resolveAdmin($request);
        $permission = $this->resolvePermission($admin);
        $brand = $this->resolveBrand($permission);

        $userResponse = [];
        if ($admin !== null) {
            $adminArray = [];
            foreach ($admin->getAttributes() as $key => $value) {
                $adminArray[$key] = ($value === null || $value === '') ? '--' : (string) $value;
            }
            $userResponse = [$adminArray];
        }

        // Sync CSRF token to legacy session for pp-adapter.php / pp-functions.php
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['csrf_token'] = csrf_token();

        $brandResponse = [];
        if ($brand !== null) {
            $brandArray = [];
            $attributes = $brand->getAttributes();
            $essentialKeys = ['name', 'identify_name', 'brand_id', 'currency_code', 'currency_symbol', 'timezone', 'logo', 'favicon'];
            foreach ($essentialKeys as $key) {
                $val = $attributes[$key] ?? $brand->{$key} ?? '';
                $brandArray[$key] = ($val === null || (string)$val === '' || (string)$val === '--') ? '--' : (string)$val;
            }
            foreach ($attributes as $key => $value) {
                if (!isset($brandArray[$key])) {
                    $brandArray[$key] = ($value === null || (string)$value === '' || (string)$value === '--') ? '--' : (string)$value;
                }
            }
            $brandResponse = [$brandArray];
        }

        $permissionResponse = [];
        if ($permission !== null) {
            $permissionResponse = [[
                'brand_id' => (string) $permission->brand_id,
                'permission' => (string) $permission->permission,
                'status' => (string) $permission->status,
            ]];
        }

        // Ensure non-empty responses for index 0 access in legacy views
        $userResponse = $userResponse ?: [['id' => '--', 'full_name' => 'Guest', 'role' => 'staff', 'username' => '--', 'email' => '--']];
        $brandResponse = $brandResponse ?: [[
            'name' => 'No Brand',
            'identify_name' => 'No Brand',
            'brand_id' => '--',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'timezone' => 'Asia/Dhaka',
            'logo' => '--',
            'favicon' => '--'
        ]];
        $permissionResponse = $permissionResponse ?: [[
            'brand_id' => '--',
            'permission' => json_encode([]),
            'status' => 'inactive',
        ]];

        $currentVersion = [
            'version_name' => 'v3.0.0-beta',
            'version_code' => '3.0.0',
            'version_channel' => 'beta',
        ];

        // Populate legacy GLOBALS for update actions and other procedural code
        $GLOBALS['global_user_response'] = [
            'status' => $admin !== null,
            'response' => $userResponse,
        ];
        $GLOBALS['global_response_brand'] = [
            'status' => $brand !== null,
            'response' => $brandResponse,
        ];
        $GLOBALS['global_response_permission'] = [
            'status' => $permission !== null,
            'response' => $permissionResponse,
        ];
        $GLOBALS['db_prefix'] = env('DB_PREFIX', 'pp_');

        return [
            'global_user_response' => $GLOBALS['global_user_response'],
            'global_response_brand' => $GLOBALS['global_response_brand'],
            'global_response_permission' => $GLOBALS['global_response_permission'],
            'piprapay_current_version' => $currentVersion,
            'admin' => $admin,
            'brand' => $brand,
            'permission' => $permission,
            'db_prefix' => $GLOBALS['db_prefix'],
            'piprapay_favicon' => asset('assets/images/favicon-light.png'),
            'piprapay_logo_light' => asset('assets/images/logo-light.png'),
            'piprapay_logo_dark' => asset('assets/images/logo-dark.png'),
            'site_url' => rtrim(url('/'), '/') . '/',
            'path_admin' => trim((string) config('piprapay.paths.admin', 'admin'), '/'),
            'path_cron' => trim((string) config('piprapay.paths.cron', 'cron'), '/'),
        ];
    }

    protected function resolveAdmin(Request $request): ?PpAdmin
    {
        $admin = Auth::guard('pp_admin')->user();
        if ($admin instanceof PpAdmin) {
            return $admin;
        }

        $adminCookie = (string) $request->cookie('pp_admin', '');
        if ($adminCookie === '') {
            return null;
        }

        $browserLog = DB::table('pp_browser_log')
            ->where('cookie', $adminCookie)
            ->where('status', 'active')
            ->first();

        if ($browserLog === null) {
            return null;
        }

        return PpAdmin::query()
            ->where('a_id', (string) $browserLog->a_id)
            ->where('status', 'active')
            ->first();
    }

    protected function resolvePermission(?PpAdmin $admin): ?PpPermission
    {
        if ($admin === null) {
            return null;
        }

        $activeBrandId = (string) request()->cookie('pp_brand', '');

        $query = PpPermission::query()
            ->where('a_id', (string) $admin->a_id)
            ->where('status', 'active');

        if ($activeBrandId !== '') {
            $query->where('brand_id', $activeBrandId);
        }

        $permission = $query->orderBy('id')->first();

        if ($permission !== null) {
            return $permission;
        }

        return PpPermission::query()
            ->where('a_id', (string) $admin->a_id)
            ->where('status', 'active')
            ->orderBy('id')
            ->first();
    }

    protected function resolveBrand(?PpPermission $permission): ?PpBrand
    {
        if ($permission === null) {
            return null;
        }

        return PpBrand::query()
            ->where('brand_id', (string) $permission->brand_id)
            ->first();
    }
}
