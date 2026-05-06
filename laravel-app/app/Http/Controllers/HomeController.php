<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Legacy\LegacyRuntimeService;

class HomeController extends Controller
{
    public function __construct(private readonly LegacyRuntimeService $legacyRuntimeService)
    {
    }

    /**
     * Handle landing page
     */
    public function index(Request $request)
    {
        return $this->legacyRuntimeService->dispatch($request, '');
    }
}
