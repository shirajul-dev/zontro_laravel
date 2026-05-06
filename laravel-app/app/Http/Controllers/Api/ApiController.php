<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Legacy\LegacyRuntimeService;
use App\Services\Api\ApiCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\PpTransaction;
use App\Models\PpBalanceVerification;
use App\Models\PpGateway;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function __construct(
        private readonly LegacyRuntimeService $legacyRuntimeService,
        private readonly ApiCheckoutService $apiCheckoutService,
        private readonly \App\Services\Common\MoneyService $moneyService,
        private readonly \App\Services\Common\BrandingService $brandingService,
        private readonly \App\Services\Payment\PaymentService $paymentService
    ) {
    }

    /**
     * Entry point for all API requests.
     * We'll delegate to legacy logic for now, but in a managed way.
     */
    public function handle(Request $request, string $api_type, ?string $api_subtype = null): JsonResponse
    {
        // Start native migration on a low-risk API surface while keeping fallback live.
        if (
            config('piprapay.migration.native_api_checkout_enabled', false)
            && $api_type === 'checkout'
            && $api_subtype === 'health'
        ) {
            return response()->json([
                'status' => true,
                'source' => 'laravel-native',
                'api_type' => 'checkout',
                'api_subtype' => 'health',
                'timestamp' => now('UTC')->toIso8601String(),
            ]);
        }

        if (config('piprapay.migration.native_api_verify_payment_enabled', false) && $api_type === 'verify-payment') {
            return $this->handleNativeVerifyPayment($request);
        }

        if ($api_type === 'balance') {
            return $this->handleNativeBalance($request);
        }

        if ($api_type === 'transaction-list') {
            return $this->handleNativeTransactionList($request);
        }

        if ($api_type === 'checkout') {
            return $this->handleNativeCheckout($request);
        }

        if ($api_type === 'refund-payment') {
            return $this->handleNativeRefundPayment($request);
        }

        return $this->dispatchLegacyAsJson($request);
    }

    /**
     * Future: Fully native implementation of Checkout
     */
    public function checkout(Request $request): JsonResponse
    {
        return $this->handleNativeCheckout($request);
    }

    /**
     * Future: Fully native implementation of Verify Payment
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        return $this->dispatchLegacyAsJson($request);
    }

    private function dispatchLegacyAsJson(Request $request): JsonResponse
    {
        // Legacy bridge remains default behavior.
        $response = $this->legacyRuntimeService->dispatch($request);

        $content = $response->getContent();
        $data = json_decode($content, true);

        return response()->json(['message' => $content], $response->getStatusCode());
    }

    private function handleNativeCheckout(Request $request): JsonResponse
    {
        if (config('piprapay.security.strict_api_methods_enabled', false) && !$request->isMethod('post')) {
            return response()->json([
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                    'message' => 'Checkout only supports POST requests.',
                ],
            ], 405);
        }

        /** @var \App\Models\PpApi $apiRow */
        $apiRow = $request->attributes->get('authenticated_api');
        $siteUrl = rtrim((string) config('app.url', '/'), '/') . '/';

        $rawInput = (string) $request->getContent();
        $data = json_decode($rawInput, true);

        if (!is_array($data)) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_JSON_PAYLOAD',
                    'message' => 'The JSON payload is invalid or malformed.',
                ],
            ], 400);
        }

        $apiType = 'checkout';
        $pathInfo = $request->getPathInfo();
        $segments = explode('/', trim($pathInfo, '/'));
        
        $checkoutType = null;
        if (count($segments) >= 3 && strtolower($segments[0]) === 'api' && strtolower($segments[1]) === 'checkout') {
            $checkoutType = strtolower($segments[2]);
        }

        $result = $this->apiCheckoutService->handleCheckout($data, $apiType, $checkoutType, $apiRow, $siteUrl);

        if (($result['status'] ?? '') === 'error') {
            return response()->json([
                'error' => [
                    'code' => $result['error_code'] ?? 'SERVER_ERROR',
                    'message' => $result['message'] ?? 'An unknown error occurred.',
                ],
            ], $result['code'] ?? 400);
        }

        return response()->json($result['data'] ?? []);
    }

    private function handleNativeVerifyPayment(Request $request): JsonResponse
    {
        if (config('piprapay.security.strict_api_methods_enabled', false) && !$request->isMethod('post')) {
            return response()->json([
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                    'message' => 'Verify Payment only supports POST requests.',
                ],
            ], 405);
        }

        /** @var \App\Models\PpApi $apiRow */
        $apiRow = $request->attributes->get('authenticated_api');

        $rawInput = (string) $request->getContent();
        $data = json_decode($rawInput, true);

        if (!is_array($data)) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_JSON_PAYLOAD',
                    'message' => 'The JSON payload is invalid or malformed.',
                ],
            ], 400);
        }

        $apiScopes = $apiRow->api_scopes ?? [];
        if (is_string($apiScopes)) {
            $decoded = json_decode($apiScopes, true);
            $apiScopes = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($apiScopes) || !in_array('verify_payment', $apiScopes, true)) {
            return response()->json([
                'error' => [
                    'code' => 'INSUFFICIENT_SCOPE',
                    'message' => 'The API key does not have the required permission: Verify Payment',
                ],
            ], 403);
        }

        $ppId = (string) ($data['pp_id'] ?? '');
        if ($ppId === '') {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_PP_ID',
                    'message' => 'A valid bp id is required.',
                ],
            ], 400);
        }

        /** @var \App\Models\PpTransaction|null $transaction */
        $transaction = \App\Models\PpTransaction::with(['brand', 'gateway'])
            ->where('ref', $ppId)
            ->first();

        if ($transaction === null) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_PP_ID',
                    'message' => 'A valid bp id is required.',
                ],
            ], 400);
        }

        $brand = $transaction->brand;
        $gateway = $transaction->gateway;

        $customer = json_decode((string) ($transaction->customer_info ?? '{}'), true);
        $customer = is_array($customer) ? $customer : [];

        $metadata = json_decode((string) ($transaction->metadata ?? '{}'), true);
        $metadata = is_array($metadata) ? $metadata : [];

        $amount = (float) ($transaction->amount ?? 0);
        $fee = (float) ($transaction->processing_fee ?? 0);
        $discount = (float) ($transaction->discount_amount ?? 0);
        $net = ($amount + $fee) - $discount;

        $timezone = (string) ($brand->timezone ?? 'Asia/Dhaka');
        if ($timezone === '' || $timezone === '--') {
            $timezone = 'Asia/Dhaka';
        }

        $date = Carbon::parse((string) $transaction->created_date, 'UTC')
            ->setTimezone($timezone)
            ->format('M d, Y h:i A');

        return response()->json([
            'pp_id' => (string) $transaction->ref,
            'full_name' => (string) ($customer['name'] ?? 'N/A'),
            'email_address' => (string) ($customer['email'] ?? 'N/A'),
            'mobile_number' => (string) ($customer['mobile'] ?? 'N/A'),
            'gateway' => (string) ($gateway->display ?? ''),
            'amount' => number_format($amount, 2, '.', ''),
            'fee' => number_format($fee, 2, '.', ''),
            'discount_amount' => number_format($discount, 2, '.', ''),
            'total' => number_format($net, 2, '.', ''),
            'local_net_amount' => number_format((float) ($transaction->local_net_amount ?? 0), 2, '.', ''),
            'currency' => (string) ($transaction->currency ?? ''),
            'local_currency' => (string) ($transaction->local_currency ?? ''),
            'metadata' => $metadata,
            'sender' => (string) ($transaction->sender ?? ''),
            'transaction_id' => (string) ($transaction->trx_id ?? ''),
            'status' => (string) ($transaction->status ?? ''),
            'date' => $date,
        ]);
    }

    private function handleNativeRefundPayment(Request $request): JsonResponse
    {
        if (config('piprapay.security.strict_api_methods_enabled', false) && !$request->isMethod('post')) {
            return response()->json([
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                    'message' => 'Refund Payment only supports POST requests.',
                ],
            ], 405);
        }

        /** @var \App\Models\PpApi $apiRow */
        $apiRow = $request->attributes->get('authenticated_api');

        $rawInput = (string) $request->getContent();
        $data = json_decode($rawInput, true);

        if (!is_array($data)) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_JSON_PAYLOAD',
                    'message' => 'The JSON payload is invalid or malformed.',
                ],
            ], 400);
        }

        $apiScopes = $apiRow->api_scopes ?? [];
        if (is_string($apiScopes)) {
            $decoded = json_decode($apiScopes, true);
            $apiScopes = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($apiScopes) || !in_array('refund_payment', $apiScopes, true)) {
            return response()->json([
                'error' => [
                    'code' => 'INSUFFICIENT_SCOPE',
                    'message' => 'The API key does not have the required permission: Refund Payment',
                ],
            ], 403);
        }

        $ppId = (string) ($data['pp_id'] ?? '');
        if ($ppId === '') {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_PP_ID',
                    'message' => 'A valid bp id is required.',
                ],
            ], 400);
        }

        /** @var \App\Models\PpTransaction|null $transaction */
        $transaction = \App\Models\PpTransaction::with(['brand', 'gateway'])
            ->where('ref', $ppId)
            ->first();

        if ($transaction === null) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_PP_ID',
                    'message' => 'A valid bp id is required.',
                ],
            ], 400);
        }

        // Update status natively via Eloquent
        $transaction->update([
            'status' => 'refunded',
            'updated_date' => Carbon::now('UTC')->toDateTimeString(),
        ]);

        $brand = $transaction->brand;
        $gateway = $transaction->gateway;

        $customer = json_decode((string) ($transaction->customer_info ?? '{}'), true);
        $customer = is_array($customer) ? $customer : [];

        $metadata = json_decode((string) ($transaction->metadata ?? '{}'), true);
        $metadata = is_array($metadata) ? $metadata : [];

        $amount = (float) ($transaction->amount ?? 0);
        $fee = (float) ($transaction->processing_fee ?? 0);
        $discount = (float) ($transaction->discount_amount ?? 0);
        $net = ($amount + $fee) - $discount;

        $timezone = (string) ($brand->timezone ?? 'Asia/Dhaka');
        if ($timezone === '' || $timezone === '--') {
            $timezone = 'Asia/Dhaka';
        }

        $date = Carbon::parse((string) $transaction->created_date, 'UTC')
            ->setTimezone($timezone)
            ->format('M d, Y h:i A');

        return response()->json([
            'pp_id' => (string) $transaction->ref,
            'full_name' => (string) ($customer['name'] ?? 'N/A'),
            'email_address' => (string) ($customer['email'] ?? 'N/A'),
            'mobile_number' => (string) ($customer['mobile'] ?? 'N/A'),
            'gateway' => (string) ($gateway->display ?? ''),
            'amount' => number_format($amount, 2, '.', ''),
            'fee' => number_format($fee, 2, '.', ''),
            'discount_amount' => number_format($discount, 2, '.', ''),
            'total' => number_format($net, 2, '.', ''),
            'local_net_amount' => number_format((float) ($transaction->local_net_amount ?? 0), 2, '.', ''),
            'currency' => (string) ($transaction->currency ?? ''),
            'local_currency' => (string) ($transaction->local_currency ?? ''),
            'metadata' => $metadata,
            'sender' => (string) ($transaction->sender ?? ''),
            'transaction_id' => (string) ($transaction->trx_id ?? ''),
            'status' => (string) ($transaction->status ?? ''),
            'date' => $date,
        ]);
    }

    private function handleNativeBalance(Request $request): JsonResponse
    {
        /** @var \App\Models\PpApi $apiRow */
        $apiRow = $request->attributes->get('authenticated_api');

        if (!$apiRow->hasScope('view_balance')) {
            return response()->json([
                'error' => [
                    'code' => 'INSUFFICIENT_SCOPE',
                    'message' => 'The API key does not have the required permission: View Balance',
                ],
            ], 403);
        }

        $brandId = (string) ($apiRow->brand_id ?? '');

        // For balance, we return the SIM wallet balances.
        // In Non-SaaS, we return all active SIM balances.
        $balances = PpBalanceVerification::query()
            ->where('status', 'active')
            ->get();

        $response = $balances->map(function (PpBalanceVerification $row): array {
            return [
                'sender_key' => (string) $row->sender_key,
                'type' => (string) $row->type,
                'balance' => number_format((float) ($row->current_balance ?? 0), 2, '.', ''),
                'simslot' => (string) $row->simslot,
                'updated_at' => (string) $row->updated_date,
            ];
        });

        return response()->json([
            'status' => true,
            'response' => $response,
        ]);
    }

    private function handleNativeTransactionList(Request $request): JsonResponse
    {
        /** @var \App\Models\PpApi $apiRow */
        $apiRow = $request->attributes->get('authenticated_api');

        if (!$apiRow->hasScope('view_transactions')) {
            return response()->json([
                'error' => [
                    'code' => 'INSUFFICIENT_SCOPE',
                    'message' => 'The API key does not have the required permission: View Transactions',
                ],
            ], 403);
        }

        $brandId = (string) ($apiRow->brand_id ?? '');

        // Handle simple pagination and filtering
        $status = $request->input('status');
        $limit = max(1, min(100, (int) $request->input('limit', 20)));

        $query = PpTransaction::query()
            ->where('brand_id', $brandId)
            ->where('status', '!=', 'initiated');

        if ($status && in_array($status, ['completed', 'pending', 'canceled', 'refunded'], true)) {
            $query->where('status', $status);
        }

        $transactions = $query->orderByDesc('id')
            ->paginate($limit);

        $brand = $this->brandingService->getBrand($brandId);
        $timezone = (string) ($brand->timezone ?? 'Asia/Dhaka');
        if ($timezone === '' || $timezone === '--') {
            $timezone = 'Asia/Dhaka';
        }

        $response = collect($transactions->items())->map(function (PpTransaction $row) use ($timezone): array {
            $customer = json_decode((string) ($row->customer_info ?? '{}'), true);
            $customer = is_array($customer) ? $customer : [];

            return [
                'pp_id' => (string) $row->ref,
                'trx_id' => (string) $row->trx_id,
                'amount' => number_format((float) ($row->amount ?? 0), 2, '.', ''),
                'fee' => number_format((float) ($row->processing_fee ?? 0), 2, '.', ''),
                'net' => number_format((float) (($row->amount + $row->processing_fee) - $row->discount_amount), 2, '.', ''),
                'currency' => (string) $row->currency,
                'status' => (string) $row->status,
                'customer_name' => (string) ($customer['name'] ?? 'N/A'),
                'date' => Carbon::parse((string) $row->created_date, 'UTC')
                    ->setTimezone($timezone)
                    ->format('M d, Y h:i A'),
            ];
        });

        return response()->json([
            'status' => true,
            'response' => $response,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'total_pages' => $transactions->lastPage(),
                'total_records' => $transactions->total(),
            ],
        ]);
    }
}
