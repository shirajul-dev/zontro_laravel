<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways\Drivers;

use App\Models\PpGateway;
use App\Models\PpTransaction;
use App\Services\Payment\Gateways\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OxaPayDriver
 *
 * Native implementation of the OxaPay payment gateway.
 */
class OxaPayDriver implements PaymentGatewayInterface
{
    private array $options;

    public function __construct(private readonly PpGateway $gateway)
    {
        $this->options = $gateway->parameters->pluck('value', 'option_name')->toArray();
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/gateway_' . $this->gateway->slug . '.log'),
            ])->debug($message, $context);
        }
    }

    private function logError(string $message, array $context = []): void
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/gateway_' . $this->gateway->slug . '.log'),
        ])->error($message, $context);
    }

    public function getDisplayName(): string
    {
        return $this->gateway->display ?? 'OxaPay';
    }

    public function initiate(PpTransaction $transaction): array
    {
        $apiUrl = "https://api.oxapay.com/v1/payment/invoice";
        $customer = json_decode($transaction->customer_info, true) ?: [];

        $payload = [
            "amount" => $transaction->local_net_amount,
            "currency" => $transaction->local_currency,
            "lifetime" => 30,
            "fee_paid_by_payer" => ($this->options['fee_paid_by_payer'] ?? '0') == '1',
            "under_paid_coverage" => (float) ($this->options['under_paid_coverage'] ?? '0'),
            "to_currency" => "USDT",
            "auto_withdrawal" => false,
            "mixed_payment" => (($this->options['mixed_payment'] ?? 'disallow') === 'allow'),
            "callback_url" => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id]),
            "return_url" => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id, 'redirect' => 'true']),
            "email" => $customer['email'] ?? '',
            "order_id" => $transaction->ref . '_' . time(),
            "sandbox" => (($this->options['mode'] ?? 'sandbox') !== 'live')
        ];

        try {
            $this->logDebug("OxaPay API Initiate Request", ['url' => $apiUrl, 'payload' => $payload]);
            $response = Http::withHeaders([
                "merchant_api_key" => $this->options['api_key'] ?? '',
                "Content-Type" => "application/json"
            ])->post($apiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $this->logDebug("OxaPay API Initiate Response", ['response' => $data]);
                
                if (isset($data['data']['payment_url'])) {
                    // Store track_id in metadata for later verification if needed
                    $metadata = json_decode($transaction->metadata ?: '{}', true);
                    $metadata['oxapay_track_id'] = $data['data']['track_id'];
                    $transaction->metadata = json_encode($metadata);
                    $transaction->save();

                    return [
                        'status' => 'success',
                        'redirect_url' => $data['data']['payment_url']
                    ];
                }

                $this->logError("OxaPay API Initiation Failed", [
                    'message' => $data['message'] ?? 'OxaPay initiation failed.',
                    'response' => $data
                ]);
                return [
                    'status' => 'error',
                    'message' => $data['message'] ?? 'OxaPay initiation failed.'
                ];
            }

            $this->logError("OxaPay API Initiation HTTP Error", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to connect to OxaPay API.'
            ];
        } catch (\Exception $e) {
            $this->logError("OxaPayDriver: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An internal error occurred while initiating payment.'
            ];
        }
    }

    public function verify(Request $request): bool
    {
        $postData = $request->getContent();
        $data = $request->json()->all();

        // 1. IPN Signature Verification
        $apiSecretKey = $this->options['api_key'] ?? '';
        $hmacHeader = $request->header('HMAC');
        $calculatedHmac = hash_hmac('sha512', $postData, $apiSecretKey);

        if ($calculatedHmac !== $hmacHeader) {
            $this->logError("OxaPay IPN HMAC Signature Invalid", [
                'expected' => $calculatedHmac,
                'received' => $hmacHeader
            ]);
            // If it's a redirect-based verify, we might not have HMAC, but OxaPay usually uses IPN for status updates.
            // However, we should still try to verify if it's a browser return.
        }

        // 2. Fetch Status from OxaPay API
        $trackId = $data['track_id'] ?? $request->input('track_id');
        
        if (!$trackId) {
            // Try to find it in transaction metadata if it's a browser return
            $ref = $request->input('ref') ?? $request->input('order_id');
            if ($ref) {
                if (str_contains($ref, '_')) {
                    $ref = explode('_', $ref)[0];
                }
                $transaction = PpTransaction::where('ref', $ref)->first();
                if ($transaction) {
                    $metadata = json_decode($transaction->metadata ?: '{}', true);
                    $trackId = $metadata['oxapay_track_id'] ?? null;
                }
            }
        }

        if (!$trackId) return false;

        $apiUrl = "https://api.oxapay.com/v1/payment/" . $trackId;

        try {
            $this->logDebug("OxaPay API Status Request", ['url' => $apiUrl]);
            $response = Http::withHeaders([
                "merchant_api_key" => $this->options['api_key'] ?? '',
                "Content-Type" => "application/json"
            ])->get($apiUrl);

            if ($response->successful()) {
                $resData = $response->json();
                $this->logDebug("OxaPay API Status Response", ['response' => $resData]);
                
                if (($resData['data']['status'] ?? '') === 'paid') {
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->logError("OxaPayDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
