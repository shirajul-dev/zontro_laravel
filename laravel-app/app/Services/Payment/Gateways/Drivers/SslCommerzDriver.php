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
            $response = Http::asForm()->post($apiUrl, $payload);
            
            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'SUCCESS') {
                    return [
                        'status' => 'success',
                        'redirect_url' => $data['GatewayPageURL']
                    ];
                }
                return [
                    'status' => 'error',
                    'message' => $data['failedreason'] ?? 'SSLCommerz initiation failed.'
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'Failed to connect to SSLCommerz API.'
            ];
        } catch (\Exception $e) {
            Log::error("SslCommerzDriver: " . $e->getMessage());
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
            $response = Http::get($verifyUrl, [
                'tran_id' => $tranId,
                'store_id' => $this->options['store_id'] ?? '',
                'store_passwd' => $this->options['store_password'] ?? '',
                'format' => 'json'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (
                    ($data['APIConnect'] ?? '') === 'DONE' && 
                    ($data['no_of_trans_found'] ?? 0) > 0 && 
                    in_array($data['element'][0]['status'] ?? '', ['VALID', 'VALIDATED'])
                ) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::error("SslCommerzDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
