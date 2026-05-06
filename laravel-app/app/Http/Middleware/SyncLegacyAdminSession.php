<?php

namespace App\Http\Middleware;

use App\Services\Legacy\LegacyAuthSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SyncLegacyAdminSession
{
    public function __construct(private readonly LegacyAuthSessionService $legacyAuthSessionService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('pp_admin');

        $this->legacyAuthSessionService->sync($request, $guard);

        return $next($request);
    }
}
