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
 * EpsDriver
 *
 * Native implementation of the EPS payment gateway.
 */
class EpsDriver extends AbstractBaseDriver
{
    public function getDisplayName(): string
    {
        return $this->gateway->display ?? 'EPS';
    }

    private function getBaseUrl(): string
    {
        $mode = $this->options['mode'] ?? 'sandbox';
        return ($mode === 'live') ? 'https://pgapi.eps.com.bd' : 'https://sandboxpgapi.eps.com.bd';
    }

    private function getToken(): ?string
    {
        $username = $this->options['username'] ?? '';
        $hashkey = $this->options['hashkey'] ?? '';
        $x_hash = base64_encode(hash_hmac('sha512', $username, $hashkey, true));

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-hash' => $x_hash
            ])->post($this->getBaseUrl() . '/v1/Auth/GetToken', [
                'userName' => $username,
                'password' => $this->options['password'] ?? ''
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['token'] ?? null;
            }
        } catch (\Exception $e) {
            $this->logError("EpsDriver: Failed to get token: " . $e->getMessage());
        }

        return null;
    }

    public function initiate(PpTransaction $transaction): array
    {
        $token = $this->getToken();
        if (!$token) {
            return ['status' => 'error', 'message' => 'EPS Token Error'];
        }

        $merchantTransactionId = (string) (time() . rand(1000, 9999));
        $x_hash = base64_encode(hash_hmac('sha512', $merchantTransactionId, $this->options['hashkey'] ?? '', true));

        $customer = json_decode($transaction->customer_info, true) ?: [];

        $payload = [
            'merchantId' => $this->options['merchant_id'] ?? '',
            'storeId' => $this->options['store_id'] ?? '',
            'CustomerOrderId' => $merchantTransactionId,
            'merchantTransactionId' => $merchantTransactionId,
            'transactionTypeId' => 1,
            'totalAmount' => $transaction->local_net_amount,
            'successUrl' => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id, 'redirect' => 'true']),
            'failUrl' => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id, 'redirect' => 'true', 'status' => 'failed']),
            'cancelUrl' => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id, 'redirect' => 'true', 'status' => 'canceled']),
            'customerName' => trim($customer['name'] ?? 'N/A'),
            'customerEmail' => $customer['email'] ?? 'N/A',
            'customerAddress' => 'N/A',
            'customerCity' => 'N/A',
            'customerState' => 'N/A',
            'customerPostcode' => '0000',
            'customerCountry' => 'BD',
            'customerPhone' => $customer['mobile'] ?? 'N/A',
            'productName' => 'Digital Products',
            'ValueA' => (string) $transaction->ref
        ];

        try {
            $this->logDebug("EPS API Initialize Request", ['payload' => $payload]);
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'x-hash' => $x_hash
            ])->post($this->getBaseUrl() . '/v1/EPSEngine/InitializeEPS', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $this->logDebug("EPS API Initialize Response", ['response' => $data]);
                if (isset($data['RedirectURL'])) {
                    return [
                        'status' => 'success',
                        'redirect_url' => $data['RedirectURL']
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logError("EpsDriver Initiate Error: " . $e->getMessage());
        }

        return ['status' => 'error', 'message' => 'EPS Initialize Error'];
    }

    public function verify(Request $request): bool
    {
        $merchantTransactionId = $request->input('MerchantTransactionId');
        $epsTransactionId = $request->input('EPSTransactionId');
        
        if (!$merchantTransactionId && !$epsTransactionId) return false;

        $token = $this->getToken();
        if (!$token) return false;

        $x_hash = base64_encode(hash_hmac('sha512', (string) $merchantTransactionId, $this->options['hashkey'] ?? '', true));

        try {
            $url = $this->getBaseUrl() . '/v1/EPSEngine/CheckMerchantTransactionStatus' . 
                   '?merchantTransactionId=' . urlencode((string)$merchantTransactionId) . 
                   '&EPSTransactionId=' . urlencode((string)$epsTransactionId);

            $this->logDebug("EPS API Status Request", ['url' => $url]);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'x-hash' => $x_hash
            ])->get($url);

            if ($response->successful()) {
                $resData = $response->json();
                $this->logDebug("EPS API Status Response", ['response' => $resData]);
                
                if (isset($resData['Status']) && $resData['Status'] === 'Success') {
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->logError("EpsDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
