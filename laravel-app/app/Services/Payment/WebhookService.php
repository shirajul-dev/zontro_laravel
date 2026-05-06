<?php
declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\PpTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * WebhookService
 * 
 * Handles delivery of payment notifications to merchants.
 */
class WebhookService
{
    /**
     * Deliver a payment notification to the merchant's webhook URL.
     */
    public function deliver(PpTransaction $transaction): bool
    {
        if (empty($transaction->webhook_url)) {
            return false;
        }

        // Resolve the merchant's API Key to use as a secret for signing
        $apiKey = DB::table('pp_api')
            ->where('brand_id', $transaction->brand_id)
            ->where('status', 'active')
            ->value('api_key');

        if (!$apiKey) {
            Log::error("Webhook delivery failed: No active API key found for brand {$transaction->brand_id}");
            return false;
        }

        $payload = [
            'status' => $transaction->status,
            'ref' => $transaction->ref,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'trx_id' => $transaction->trx_id,
            'metadata' => json_decode($transaction->metadata ?? '{}', true),
            'timestamp' => now('UTC')->toIso8601String(),
        ];

        $jsonPayload = json_encode($payload);
        $signature = hash_hmac('sha256', $jsonPayload, $apiKey);

        Log::debug("Attempting Webhook delivery", [
            'ref' => $transaction->ref,
            'url' => $transaction->webhook_url,
            'payload' => $payload
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-PipraPay-Signature' => $signature,
                'User-Agent' => 'PipraPay-Webhook-Client/1.0',
            ])->timeout(15)->post($transaction->webhook_url, $payload);

            Log::debug("Webhook delivery response", [
                'ref' => $transaction->ref,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Log to pp_webhook_log
            DB::table('pp_webhook_log')->insert([
                'ref' => $transaction->ref,
                'brand_id' => $transaction->brand_id,
                'url' => $transaction->webhook_url,
                'payload' => $jsonPayload,
                'response_body' => $response->body(),
                'http_code' => (string) $response->status(),
                'status' => 'completed',
                'created_date' => now('UTC')->format('Y-m-d H:i:s'),
                'updated_date' => now('UTC')->format('Y-m-d H:i:s'),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Webhook delivery exception", [
                'ref' => $transaction->ref,
                'message' => $e->getMessage()
            ]);

            DB::table('pp_webhook_log')->insert([
                'ref' => $transaction->ref,
                'brand_id' => $transaction->brand_id,
                'url' => $transaction->webhook_url,
                'payload' => $jsonPayload,
                'response_body' => $e->getMessage(),
                'http_code' => '0',
                'status' => 'canceled',
                'created_date' => now('UTC')->format('Y-m-d H:i:s'),
                'updated_date' => now('UTC')->format('Y-m-d H:i:s'),
            ]);

            return false;
        }
    }
}
