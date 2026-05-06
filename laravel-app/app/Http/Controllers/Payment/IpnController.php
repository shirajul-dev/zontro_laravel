<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Legacy\LegacyRuntimeService;
use App\Services\Payment\IpnService;

class IpnController extends Controller
{
    public function __construct(
        private readonly LegacyRuntimeService $legacyRuntimeService,
        private readonly IpnService $ipnService
    ) {
    }

    /**
     * Handle Instant Payment Notifications (IPN)
     */
    public function handle(Request $request, string $gateway_id)
    {
        $siteUrl = rtrim((string) config('app.url', '/'), '/') . '/';
        $result = $this->ipnService->handleIpn($gateway_id, $siteUrl);

        if (($result['status'] ?? '') === 'error') {
            return response()->json([
                'error' => [
                    'code' => $result['error_code'] ?? 'SERVER_ERROR',
                    'message' => $result['message'] ?? 'An unknown error occurred.',
                ],
            ], $result['code'] ?? 400);
        }

        return response()->json(['status' => 'success']);
    }
}
