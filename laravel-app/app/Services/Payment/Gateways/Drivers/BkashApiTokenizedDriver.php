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
 * BkashApiTokenizedDriver
 * 
 * Native implementation of the bKash Tokenized Checkout API.
 */
class BkashApiTokenizedDriver implements PaymentGatewayInterface
{
    private array $options;

    public function __construct(private readonly PpGateway $gateway)
    {
        $this->options = $gateway->parameters->pluck('value', 'option_name')->toArray();
    }

    public function getDisplayName(): string
    {
        return $this->gateway->display ?? 'bKash Tokenized';
    }

    private function getBaseUrl(): string
    {
        $mode = $this->options['mode'] ?? 'sandbox';
        return ($mode === 'live') 
            ? 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized' 
            : 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized';
    }

    private function getToken(): ?string
    {
        $apiUrl = "{$this->getBaseUrl()}/checkout/token/grant";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'username' => $this->options['username'] ?? '',
                'password' => $this->options['password'] ?? '',
            ])->post($apiUrl, [
                'app_key' => $this->options['app_key'] ?? '',
                'app_secret' => $this->options['app_secret_key'] ?? '',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['id_token'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error("BkashApiTokenizedDriver Token Error: " . $e->getMessage());
        }

        return null;
    }

    public function initiate(PpTransaction $transaction): array
    {
        $token = $this->getToken();
        if (!$token) {
            return [
                'status' => 'error',
                'message' => 'Failed to obtain bKash authentication token.'
            ];
        }

        // Store token in session for verify() step
        session(['bkash_token_' . $transaction->ref => $token]);

        $apiUrl = "{$this->getBaseUrl()}/checkout/create";

        $payload = [
            'mode' => '0011',
            'amount' => $transaction->local_net_amount,
            'currency' => $transaction->local_currency,
            'intent' => 'sale',
            'payerReference' => 'PipraPay',
            'merchantInvoiceNumber' => 'PP-' . $transaction->ref . '-' . rand(1000, 9999),
            'callbackURL' => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id, 'ref' => $transaction->ref])
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => $token,
                'X-APP-Key' => $this->options['app_key'] ?? '',
            ])->post($apiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['bkashURL'])) {
                    return [
                        'status' => 'success',
                        'redirect_url' => $data['bkashURL']
                    ];
                }
                return [
                    'status' => 'error',
                    'message' => $data['statusMessage'] ?? 'bKash initiation failed.'
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Failed to connect to bKash API.'
            ];
        } catch (\Exception $e) {
            Log::error("BkashApiTokenizedDriver: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An internal error occurred while initiating bKash payment.'
            ];
        }
    }

    public function verify(Request $request): bool
    {
        $status = $request->query('status');
        $paymentID = $request->query('paymentID');
        $ref = $request->query('ref');

        if ($status !== 'success' || !$paymentID || !$ref) {
            return false;
        }

        $token = session('bkash_token_' . $ref);
        if (!$token) {
            // Fallback: try to get a fresh token if session expired (though bKash might not allow it for execute)
            $token = $this->getToken();
        }

        if (!$token) return false;

        $apiUrl = "{$this->getBaseUrl()}/checkout/execute";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => $token,
                'X-APP-Key' => $this->options['app_key'] ?? '',
            ])->post($apiUrl, [
                'paymentID' => $paymentID
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['statusMessage'] ?? '') === 'Successful') {
                    // Success!
                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::error("BkashApiTokenizedDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
