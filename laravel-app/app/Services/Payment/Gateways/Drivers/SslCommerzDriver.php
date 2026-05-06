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
 * SslCommerzDriver
 * 
 * Native implementation of the SSLCommerz payment gateway.
 */
class SslCommerzDriver implements PaymentGatewayInterface
{
    private array $options;

    public function __construct(private readonly PpGateway $gateway)
    {
        // Load gateway options from the pp_gateways_parameter table
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
        return $this->gateway->display ?? 'SSLCommerz';
    }

    public function initiate(PpTransaction $transaction): array
    {
        $mode = $this->options['mode'] ?? 'sandbox';
        $baseUrl = ($mode === 'live') ? 'https://securepay.sslcommerz.com' : 'https://sandbox.sslcommerz.com';
        $apiUrl = "{$baseUrl}/gwprocess/v4/api.php";

        $customer = json_decode($transaction->customer_info, true) ?: [];

        $payload = [
            "store_id" => $this->options['store_id'] ?? '',
            "store_passwd" => $this->options['store_password'] ?? '',
            "total_amount" => $transaction->local_net_amount,
            "currency" => $transaction->local_currency,
            "tran_id" => $transaction->ref . '_' . time(),
            "success_url" => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id]),
            "fail_url" => route('payment.checkout', ['ref' => $transaction->ref]),
            "cancel_url" => route('payment.checkout', ['ref' => $transaction->ref]),
            "ipn_url" => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id]),
            "emi_option" => "0",
            "cus_name" => $customer['name'] ?? 'N/A',
            "cus_email" => $customer['email'] ?? 'N/A',
            "cus_phone" => $customer['mobile'] ?? 'N/A',
            "cus_add1" => "N/A",
            "cus_city" => "Dhaka",
            "cus_country" => "Bangladesh",
            "shipping_method" => "NO",
            "num_of_item" => "1",
            "product_name" => $this->options['product_category'] ?? 'Payment',
            "product_category" => $this->options['product_category'] ?? 'Payment',
            "product_profile" => "general",
            "value_a" => $transaction->ref
        ];

        try {
            $this->logDebug("SSLCommerz API Initiate Request", ['url' => $apiUrl, 'payload' => $payload]);
            $response = Http::asForm()->post($apiUrl, $payload);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->logDebug("SSLCommerz API Initiate Response", ['response' => $data]);
                if (($data['status'] ?? '') === 'SUCCESS') {
                    return [
                        'status' => 'success',
                        'redirect_url' => $data['GatewayPageURL']
                    ];
                }
                $this->logError("SSLCommerz API Initiation Failed", [
                    'message' => $data['failedreason'] ?? 'SSLCommerz initiation failed.',
                    'response' => $data
                ]);
                return [
                    'status' => 'error',
                    'message' => $data['failedreason'] ?? 'SSLCommerz initiation failed.'
                ];
            }
            
            $this->logError("SSLCommerz API Initiation HTTP Error", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Failed to connect to SSLCommerz API.'
            ];
        } catch (\Exception $e) {
            $this->logError("SslCommerzDriver: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An internal error occurred while initiating payment.'
            ];
        }
    }

    public function verify(Request $request): bool
    {
        $tranId = $request->input('tran_id');
        if (!$tranId) return false;

        $mode = $this->options['mode'] ?? 'sandbox';
        $baseUrl = ($mode === 'live') ? 'https://securepay.sslcommerz.com' : 'https://sandbox.sslcommerz.com';
        $verifyUrl = "{$baseUrl}/validator/api/merchantTransIDvalidationAPI.php";

        try {
            $this->logDebug("SSLCommerz API Verify Request", ['url' => $verifyUrl, 'tran_id' => $tranId]);
            $response = Http::get($verifyUrl, [
                'tran_id' => $tranId,
                'store_id' => $this->options['store_id'] ?? '',
                'store_passwd' => $this->options['store_password'] ?? '',
                'format' => 'json'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->logDebug("SSLCommerz API Verify Response", ['response' => $data]);
                if (
                    ($data['APIConnect'] ?? '') === 'DONE' && 
                    ($data['no_of_trans_found'] ?? 0) > 0 && 
                    in_array($data['element'][0]['status'] ?? '', ['VALID', 'VALIDATED'])
                ) {
                    return true;
                }
            }
            } else {
                $this->logError("SSLCommerz API Verify HTTP Error", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            $this->logError("SslCommerzDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
