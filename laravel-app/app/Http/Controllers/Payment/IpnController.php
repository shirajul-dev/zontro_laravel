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
        \Illuminate\Support\Facades\Log::debug('Incoming IPN Request', [
            'gateway_id' => $gateway_id,
            'params' => $request->all()
        ]);
        $result = $this->ipnService->handleIpn($gateway_id, $siteUrl);

        // Standardized resolution of transaction reference for redirection
        $ref = $request->input('ref') ?? 
               $request->input('opt_a') ?? 
               $request->input('mer_txnid') ?? 
               $request->input('order_id') ??
               $request->input('value_a') ??
               $request->input('paymentID');

        if ($ref && str_contains($ref, '_')) {
            $ref = explode('_', $ref)[0];
        }

        // If redirect requested (standard for redirect-based gateways)
        if ($ref && ($request->query('redirect') === 'true' || $request->has('redirect'))) {
            return redirect()->route('payment.checkout', ['ref' => $ref]);
        }

        // If background IPN (standard for headless notifications)
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
