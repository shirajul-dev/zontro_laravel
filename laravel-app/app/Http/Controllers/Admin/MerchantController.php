<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasLegacyEnvironment;
use App\Services\Admin\MerchantAdminActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MerchantController extends Controller
{
    use HasLegacyEnvironment;

    public function __construct(
        private readonly MerchantAdminActionService $merchantService
    ) {}

    /**
     * Display the merchant list page.
     */
    public function index(Request $request)
    {
        if (!$this->isAuthenticated()) {
            return redirect()->route('native.auth.login');
        }

        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->hasPageAccess($legacy, 'merchants')) {
            abort(403, 'Access denied. You need permission to perform this action.');
        }

        $data = $this->viewData($request, $legacy);

        if ($request->has('content') || $request->ajax()) {
            return view('admin.pages.merchants.index', $data);
        }

        return view('admin.layouts.app', $data);
    }

    /**
     * Show create merchant page.
     */
    public function create(Request $request)
    {
        if (!$this->isAuthenticated()) {
            return redirect()->route('native.auth.login');
        }

        $legacy = $this->setupLegacyGlobals($request);

        // Creation of NEW merchants is strictly for SuperAdmins
        if (!$this->isSuperAdmin($legacy) || !$this->hasPageAccess($legacy, 'merchants')) {
            abort(403, 'Access denied. Only system administrators can create new merchants.');
        }

        $data = $this->viewData($request, $legacy);
        $data['plans'] = \App\Models\PpPlan::where('is_active', true)->get();

        if ($request->has('content') || $request->ajax()) {
            return view('admin.pages.merchants.create', $data);
        }

        return view('admin.layouts.app', $data);
    }

    /**
     * Fetch merchant list data for AJAX datatable.
     */
    public function list(Request $request): JsonResponse
    {
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->hasPageAccess($legacy, 'merchants')) {
            return response()->json([
                'status' => 'false',
                'title' => 'Access Denied',
                'message' => 'You need permission to perform this action.',
                'csrf_token' => csrf_token()
            ], 403);
        }

        $userType = (string) ($legacy['global_user_response']['response'][0]['user_type'] ?? 'staff');
        $currentAdminAId = (string) ($legacy['global_user_response']['response'][0]['a_id'] ?? '');

        $result = $this->merchantService->list($request->all(), $userType, $currentAdminAId);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Basic view data provider.
     */
    private function viewData(Request $request, array $legacy): array
    {
        $userRole = strtolower($legacy['global_user_response']['response'][0]['role'] ?? 'staff');
        $userType = (string) ($legacy['global_user_response']['response'][0]['user_type'] ?? 'staff');
        $permissions = json_decode($legacy['global_response_permission']['response'][0]['permission'] ?? '[]', true);

        $isSuper = ($userRole === 'root' || $userRole === 'admin');
        $isSystemAdmin = ($userType === 'superadmin');

        return array_merge($legacy, [
            'site_url' => rtrim(config('app.url', '/'), '/') . '/',
            'path_admin' => config('piprapay.paths.admin', 'admin'),
            'csrfToken' => csrf_token(),
            'isSuperAdmin' => $isSystemAdmin,
            'canEdit' => $isSystemAdmin && ($isSuper || (bool) ($permissions['merchants']['edit'] ?? false)),
            'canDelete' => $isSystemAdmin && ($isSuper || (bool) ($permissions['merchants']['delete'] ?? false)),
            'canCreate' => $isSystemAdmin && ($isSuper || (bool) ($permissions['merchants']['create'] ?? false)),
        ]);
    }

    private function isAuthenticated(): bool
    {
        return Auth::guard('pp_admin')->check();
    }

    private function isSuperAdmin(array $legacy): bool
    {
        return ($legacy['global_user_response']['response'][0]['user_type'] ?? '') === 'superadmin';
    }

    private function hasPageAccess(array $legacy, string $page): bool
    {
        $permissions = json_decode($legacy['global_response_permission']['response'][0]['permission'] ?? '[]', true);
        $userRole = strtolower($legacy['global_user_response']['response'][0]['role'] ?? 'staff');
        if ($userRole == 'root' || $userRole == 'admin') {
            return true;
        }

        return (bool) ($permissions[$page]['access'] ?? false);
    }
}
