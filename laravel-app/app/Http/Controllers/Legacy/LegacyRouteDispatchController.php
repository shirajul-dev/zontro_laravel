<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Services\Legacy\LegacyRuntimeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LegacyRouteDispatchController extends Controller
{
    public function __construct(private readonly LegacyRuntimeService $legacyRuntimeService)
    {
    }

    private function dispatchLegacy(Request $request, ?string $overridePage = null): Response
    {
        return $this->legacyRuntimeService->dispatch($request, $overridePage);
    }

    /**
     * Show a 404 page using the legacy template
     */
    public function show404(Request $request): Response
    {
        return $this->dispatchLegacy($request, '404');
    }

    /**
     * Handle POST requests to the root URL (legacy AJAX submissions)
     */
    public function handleRootPost(Request $request): Response
    {
        return $this->dispatchLegacy($request);
    }

    /**
     * Catch-all fallback for any routes not explicitly defined in Laravel
     */
    public function fallback(Request $request): Response
    {
        return $this->dispatchLegacy($request);
    }
}
