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

        // Attempt to extract the transaction reference
        $ref = $request->input('ref') ?? 
               $request->input('opt_a') ?? 
               $request->input('mer_txnid') ?? 
               $request->input('order_id') ??
               $request->input('value_a');

        if ($ref && str_contains($ref, '_')) {
            $ref = explode('_', $ref)[0];
        }

        if (($result['status'] ?? '') === 'error') {
            if ($ref && ($request->query('redirect') === 'true' || $request->has('redirect'))) {
                // Determine if the user canceled or if it failed
                $gwStatus = strtolower($request->input('status') ?? '');
                $newStatus = 'failed';
                if (in_array($gwStatus, ['cancelled', 'canceled', 'aborted', 'cancel'])) {
                    $newStatus = 'canceled';
                }

                $transaction = \App\Models\PpTransaction::where('ref', $ref)->first();
                if ($transaction && $transaction->status === 'initiated') {
                    app(\App\Services\Payment\PaymentService::class)->updateStatus($transaction, $newStatus);
                }

                return redirect()->route('payment.checkout', ['ref' => $ref]);
            }
            return response()->json([
                'error' => [
                    'code' => $result['error_code'] ?? 'SERVER_ERROR',
                    'message' => $result['message'] ?? 'An unknown error occurred.',
                ],
            ], $result['code'] ?? 400);
        }

        if ($ref && ($request->query('redirect') === 'true' || $request->has('redirect'))) {
            return redirect()->route('payment.checkout', ['ref' => $ref]);
        }

        return response()->json(['status' => 'success']);
    }
}
