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
 * ShurjopayDriver
 * 
 * Native implementation of the shurjoPay payment gateway.
 */
class ShurjopayDriver implements PaymentGatewayInterface
{
    private array $options;

    public function __construct(private readonly PpGateway $gateway)
    {
        // Load gateway options from the pp_gateways_parameter table
        $this->options = $gateway->parameters->pluck('value', 'option_name')->toArray();
    }

    public function getDisplayName(): string
    {
        return $this->gateway->display ?? 'shurjoPay';
    }

    private function getBaseUrl(): string
    {
        $mode = $this->options['mode'] ?? 'sandbox';
        return ($mode === 'live') ? 'https://engine.shurjopayment.com' : 'https://sandbox.shurjopayment.com';
    }

    private function getToken(): ?string
    {
        try {
            $response = Http::post($this->getBaseUrl() . "/api/get_token", [
                "username" => $this->options['username'] ?? '',
                "password" => $this->options['password'] ?? ''
            ]);

            if ($response->successful()) {
                return $response->json('token');
            }
        } catch (\Exception $e) {
            Log::error("ShurjopayDriver Token Error: " . $e->getMessage());
        }

        return null;
    }

    public function initiate(PpTransaction $transaction): array
    {
        $token = $this->getToken();
        if (!$token) {
            return [
                'status' => 'error',
                'message' => 'Failed to obtain shurjoPay authentication token.'
            ];
        }

        $customer = json_decode($transaction->customer_info, true) ?: [];

        $payload = [
            'prefix' => $this->options['prefix'] ?? 'bp',
            'token' => $token,
            'return_url' => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id, 'ref' => $transaction->ref]),
            'cancel_url' => route('payment.checkout', ['ref' => $transaction->ref]),
            'store_id' => '1', // Often 1 or provided by token response, legacy used rand logic
            'amount' => $transaction->local_net_amount,
            'order_id' => $transaction->ref . '_' . time(),
            'currency' => 'BDT',
            'customer_name' => $customer['name'] ?? 'N/A',
            'customer_address' => 'Dhaka',
            'customer_phone' => $customer['mobile'] ?? 'N/A',
            'customer_city' => 'Dhaka',
            'client_ip' => request()->ip(),
            'discount_amount' => '0',
            'disc_percent' => '0',
            'customer_email' => $customer['email'] ?? 'N/A',
            'customer_state' => 'Dhaka',
            'customer_postcode' => '1206',
            'customer_country' => 'BD',
            'shipping_address' => 'Dhaka',
            'shipping_city' => 'Dhaka',
            'shipping_country' => 'BD',
            'received_person_name' => $customer['name'] ?? 'N/A',
            'shipping_phone_number' => $customer['mobile'] ?? 'N/A'
        ];

        try {
            $response = Http::withToken($token)->asForm()->post($this->getBaseUrl() . "/api/secret-pay", $payload);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['checkout_url'])) {
                    return [
                        'status' => 'success',
                        'redirect_url' => $data['checkout_url']
                    ];
                }
                return [
                    'status' => 'error',
                    'message' => 'shurjoPay initiation failed: ' . $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error("ShurjopayDriver Initiation Error: " . $e->getMessage());
        }

        return [
            'status' => 'error',
            'message' => 'An internal error occurred while initiating shurjoPay payment.'
        ];
    }

    public function verify(Request $request): bool
    {
        $orderId = $request->input('order_id');
        if (!$orderId) return false;

        $token = $this->getToken();
        if (!$token) return false;

        try {
            $response = Http::withToken($token)->post($this->getBaseUrl() . "/api/verification", [
                'order_id' => $orderId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data[0]['bank_status']) && $data[0]['bank_status'] === 'Success') {
                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::error("ShurjopayDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
