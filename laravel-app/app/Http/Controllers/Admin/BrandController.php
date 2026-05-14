<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasLegacyEnvironment;
use App\Services\Admin\BrandAdminActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
{
    use HasLegacyEnvironment;

    public function __construct(
        private readonly BrandAdminActionService $brandService
    ) {}

    /**
     * Display the brand list page.
     */
    public function index(Request $request)
    {
        if (!$this->isAuthenticated()) {
            return redirect()->route('native.auth.login');
        }

        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->hasPageAccess($legacy, 'brands')) {
            abort(403, 'Access denied. You need permission to perform this action.');
        }

        $data = $this->viewData($request, $legacy);

        // If it's an AJAX content request (from load_content in JS)
        if ($request->has('content') || $request->ajax()) {
            return view('admin.pages.brands.index', $data);
        }

        // Full page load returns the shell layout
        return view('admin.layouts.app', $data);
    }

    /**
     * Fetch brand list data for AJAX datatable.
     */
    public function list(Request $request): JsonResponse
    {
        $legacy = $this->setupLegacyGlobals($request);

        if (!$this->hasPageAccess($legacy, 'brands')) {
            return response()->json([
                'status' => 'false',
                'title' => 'Access Denied',
                'message' => 'You need permission to perform this action.',
                'csrf_token' => csrf_token()
            ], 403);
        }

        $currentBrandId = (string) ($legacy['global_response_brand']['response'][0]['brand_id'] ?? '');
        $brandTimezone = (string) ($legacy['global_response_brand']['response'][0]['timezone'] ?? 'Asia/Dhaka');

        $result = $this->brandService->list($request->all(), $currentBrandId, $brandTimezone);
        $result['csrf_token'] = csrf_token();

        return response()->json($result);
    }

    /**
     * Basic view data provider.
     */
    private function viewData(Request $request, array $legacy): array
    {
        $userRole = strtolower($legacy['global_user_response']['response'][0]['role'] ?? 'staff');
        $permissions = json_decode($legacy['global_response_permission']['response'][0]['permission'] ?? '[]', true);

        // Super-user check
        $isSuper = ($userRole === 'root' || $userRole === 'admin');

        return array_merge($legacy, [
            'site_url' => rtrim(config('app.url', '/'), '/') . '/',
            'path_admin' => config('piprapay.paths.admin', 'admin'),
            'csrfToken' => csrf_token(),
            'canEdit' => $isSuper || (bool) ($permissions['brands']['edit'] ?? false),
            'canDelete' => $isSuper || (bool) ($permissions['brands']['delete'] ?? false),
        ]);
    }

    /**
     * Check if user is authenticated.
     */
    private function isAuthenticated(): bool
    {
        return Auth::guard('pp_admin')->check();
    }

    /**
     * Helper to check page access based on legacy permission structure.
     */
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
