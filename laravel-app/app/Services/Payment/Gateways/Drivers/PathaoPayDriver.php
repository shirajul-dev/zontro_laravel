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
 * PathaoPayDriver
 *
 * Native implementation of the PathaoPay Merchant API gateway.
 */
class PathaoPayDriver extends AbstractBaseDriver
{
    public function getDisplayName(): string
    {
        return $this->gateway->display ?? 'PathaoPay';
    }

    private function getBaseUrl(): string
    {
        $mode = $this->options['mode'] ?? 'sandbox';
        return ($mode === 'live') ? 'https://api.pathaopay.com' : 'https://api-stage.pathaopay.com';
    }

    public function initiate(PpTransaction $transaction): array
    {
        $apiUrl = $this->getBaseUrl() . '/api/v1/settlements/request-payment';
        $amountInPaisa = (int) round($transaction->local_net_amount * 100);

        $payload = [
            "amount" => $amountInPaisa,
            "merchant_reference_id" => $transaction->ref . '_' . time(),
            "force_otp" => false,
            "merchant_callback_url" => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id, 'redirect' => 'true'])
        ];

        try {
            $this->logDebug("PathaoPay API Initiate Request", ['url' => $apiUrl, 'payload' => $payload]);
            $response = Http::withHeaders([
                "Content-Type" => "application/json",
                "Application-Key" => $this->options['api_key'] ?? '',
                "Application-Secret" => $this->options['secret_key'] ?? ''
            ])->post($apiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $this->logDebug("PathaoPay API Initiate Response", ['response' => $data]);
                
                if (isset($data['data']['redirect_url'])) {
                    $token = $response->header('Payment-Reference-Token');
                    
                    // Store token and invoice_id in metadata
                    $metadata = json_decode($transaction->metadata ?: '{}', true);
                    $metadata['pathaopay_token'] = $token;
                    $metadata['pathaopay_invoice_id'] = $data['data']['invoice_id'];
                    $transaction->metadata = json_encode($metadata);
                    $transaction->save();

                    return [
                        'status' => 'success',
                        'redirect_url' => $data['data']['redirect_url']
                    ];
                }
            }

            $this->logError("PathaoPay API Initiation Failed", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            $this->logError("PathaoPayDriver Initiate Error: " . $e->getMessage());
        }

        return ['status' => 'error', 'message' => 'PathaoPay Initiation Error'];
    }

    public function verify(Request $request): bool
    {
        $ref = $request->input('ref') ?? $request->input('order_id');
        if (!$ref) return false;

        if (str_contains($ref, '_')) {
            $ref = explode('_', $ref)[0];
        }

        $transaction = PpTransaction::where('ref', $ref)->first();
        if (!$transaction) return false;

        $metadata = json_decode($transaction->metadata ?: '{}', true);
        $invoiceId = $metadata['pathaopay_invoice_id'] ?? null;
        $token = $metadata['pathaopay_token'] ?? null;

        if (!$invoiceId || !$token) return false;

        $apiUrl = $this->getBaseUrl() . '/api/v1/settlements/request-payment/capture';

        try {
            $this->logDebug("PathaoPay API Capture Request", ['url' => $apiUrl, 'invoice_id' => $invoiceId]);
            $response = Http::withHeaders([
                "Content-Type" => "application/json",
                "Application-Key" => $this->options['api_key'] ?? '',
                "Application-Secret" => $this->options['secret_key'] ?? '',
                "Payment-Reference-Token" => $token
            ])->post($apiUrl, [
                "invoice_id" => $invoiceId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->logDebug("PathaoPay API Capture Response", ['response' => $data]);
                
                if (($data['data']['status'] ?? '') === 'success') {
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->logError("PathaoPayDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
