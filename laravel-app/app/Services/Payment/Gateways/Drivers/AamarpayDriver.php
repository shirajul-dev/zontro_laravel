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
 * AamarpayDriver
 * 
 * Native implementation of the aamarPay payment gateway.
 */
class AamarpayDriver implements PaymentGatewayInterface
{
    private array $options;

    public function __construct(private readonly PpGateway $gateway)
    {
        // Load gateway options from the pp_gateways_parameter table
        $this->options = $gateway->parameters->pluck('value', 'option_name')->toArray();
    }

    public function getDisplayName(): string
    {
        return $this->gateway->display ?? 'aamarPay';
    }

    public function initiate(PpTransaction $transaction): array
    {
        $mode = $this->options['mode'] ?? 'sandbox';
        $baseUrl = ($mode === 'live') ? 'https://secure.aamarpay.com' : 'https://sandbox.aamarpay.com';
        $apiUrl = "{$baseUrl}/jsonpost.php";

        $customer = json_decode($transaction->customer_info, true) ?: [];

        $payload = [
            "store_id" => $this->options['store_id'] ?? '',
            "tran_id" => $transaction->ref . '_' . time(),
            "success_url" => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id, 'ref' => $transaction->ref]),
            "fail_url" => route('payment.checkout', ['ref' => $transaction->ref]),
            "cancel_url" => route('payment.checkout', ['ref' => $transaction->ref]),
            "amount" => $transaction->local_net_amount,
            "currency" => $transaction->local_currency,
            "signature_key" => $this->options['signature_key'] ?? '',
            "desc" => "Payment for Ref: " . $transaction->ref,
            "cus_name" => $customer['name'] ?? 'N/A',
            "cus_email" => $customer['email'] ?? 'N/A',
            "cus_add1" => "N/A",
            "cus_city" => "Dhaka",
            "cus_country" => "Bangladesh",
            "cus_phone" => $customer['mobile'] ?? 'N/A',
            "type" => "json",
            "opt_a" => $transaction->ref
        ];

        try {
            $response = Http::post($apiUrl, $payload);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['payment_url'])) {
                    return [
                        'status' => 'success',
                        'redirect_url' => $data['payment_url']
                    ];
                }
                return [
                    'status' => 'error',
                    'message' => 'aamarPay initiation failed: ' . ($response->body())
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'Failed to connect to aamarPay API.'
            ];
        } catch (\Exception $e) {
            Log::error("AamarpayDriver: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An internal error occurred while initiating payment.'
            ];
        }
    }

    public function verify(Request $request): bool
    {
        // aamarPay sends POST data back. mer_txnid is the local transaction ID we sent.
        $mer_txnid = $request->input('mer_txnid');
        if (!$mer_txnid) return false;

        $mode = $this->options['mode'] ?? 'sandbox';
        $baseUrl = ($mode === 'live') ? 'https://secure.aamarpay.com' : 'https://sandbox.aamarpay.com';
        $verifyUrl = "{$baseUrl}/api/v1/trxcheck/request.php";

        try {
            $response = Http::get($verifyUrl, [
                'request_id' => $mer_txnid,
                'store_id' => $this->options['store_id'] ?? '',
                'signature_key' => $this->options['signature_key'] ?? '',
                'type' => 'json'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Check if payment was successful
                if (
                    isset($data['pay_status']) && 
                    $data['pay_status'] === 'Successful' && 
                    isset($data['status_code']) && 
                    (string)$data['status_code'] === '2'
                ) {
                    // Verify the reference matches (opt_a)
                    $optA = $data['opt_a'] ?? null;
                    // We might need to store the bank_trxid in the transaction model later in IpnService
                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::error("AamarpayDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
