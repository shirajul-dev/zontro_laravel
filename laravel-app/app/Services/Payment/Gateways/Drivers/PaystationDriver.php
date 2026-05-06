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
 * PaystationDriver
 *
 * Native implementation of the Paystation payment gateway.
 */
class PaystationDriver implements PaymentGatewayInterface
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
        return $this->gateway->display ?? 'PayStation';
    }

    public function initiate(PpTransaction $transaction): array
    {
        $mode = $this->options['mode'] ?? 'sandbox';
        $baseUrl = ($mode === 'live') ? 'https://api.paystation.com.bd' : 'https://sandbox.paystation.com.bd';
        $apiUrl = "{$baseUrl}/initiate-payment";

        $customer = json_decode($transaction->customer_info, true) ?: [];

        $payload = [
            'invoice_number' => $transaction->ref . '_' . time(),
            'currency' => 'BDT',
            'payment_amount' => $transaction->local_net_amount,
            'reference' => $transaction->ref,
            'cust_name' => $customer['name'] ?? 'N/A',
            'cust_phone' => $customer['mobile'] ?? 'N/A',
            'cust_email' => $customer['email'] ?? 'N/A',
            'cust_address' => "Bangladesh",
            'pay_with_charge' => ($this->options['pay_with_charge'] ?? '0'),
            'callback_url' => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id, 'redirect' => 'true']),
            'checkout_items' => ($this->options['checkout_items'] ?? ''),
            'merchantId' => ($this->options['merchant_id'] ?? ''),
            'password' => ($this->options['merchant_password'] ?? '')
        ];

        try {
            $this->logDebug("Paystation API Initiate Request", ['url' => $apiUrl, 'payload' => $payload]);
            $response = Http::asForm()->post($apiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $this->logDebug("Paystation API Initiate Response", ['response' => $data]);
                
                if (isset($data['payment_url'])) {
                    return [
                        'status' => 'success',
                        'redirect_url' => $data['payment_url']
                    ];
                }

                $this->logError("Paystation API Initiation Failed", [
                    'message' => $data['message'] ?? 'Paystation initiation failed.',
                    'response' => $data
                ]);
                return [
                    'status' => 'error',
                    'message' => $data['message'] ?? 'Paystation initiation failed.'
                ];
            }

            $this->logError("Paystation API Initiation HTTP Error", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to connect to Paystation API.'
            ];
        } catch (\Exception $e) {
            $this->logError("PaystationDriver: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An internal error occurred while initiating payment.'
            ];
        }
    }

    public function verify(Request $request): bool
    {
        $status = $request->input('status');
        if ($status === 'Canceled') {
            return false;
        }

        $invoiceNumber = $request->input('invoice_number');
        if (!$invoiceNumber) return false;

        $mode = $this->options['mode'] ?? 'sandbox';
        $baseUrl = ($mode === 'live') ? 'https://api.paystation.com.bd' : 'https://sandbox.paystation.com.bd';
        $verifyUrl = "{$baseUrl}/transaction-status";

        try {
            $this->logDebug("Paystation API Status Request", ['url' => $verifyUrl, 'invoice_number' => $invoiceNumber]);
            $response = Http::withHeaders([
                'merchantId' => $this->options['merchant_id'] ?? ''
            ])->asForm()->post($verifyUrl, [
                'invoice_number' => $invoiceNumber
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->logDebug("Paystation API Status Response", ['response' => $data]);
                
                if (
                    ($data['status_code'] ?? '') == "200" && 
                    ($data['status'] ?? '') == "success" &&
                    (strtolower($data['data']['trx_status'] ?? '') == "successful" || strtolower($data['data']['trx_status'] ?? '') == "success")
                ) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->logError("PaystationDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
