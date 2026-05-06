<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Legacy\LegacyRuntimeService;
use App\Services\Admin\CronService;
use Illuminate\Http\Request;

class CronController extends Controller
{
    public function __construct(
        private readonly LegacyRuntimeService $legacyRuntimeService,
        private readonly CronService $cronService
    ) {
    }

    /**
     * Handle cron jobs
     */
    public function handle(Request $request, ?string $token = null)
    {
        $result = $this->cronService->handle((string) $token);
        
        return response()->json($result, $result['code'] ?? 400);
    }
}
