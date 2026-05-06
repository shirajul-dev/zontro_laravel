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
 * BinancePersonalDriver
 *
 * Native implementation of the Binance Personal payment gateway.
 * This is a hybrid gateway that requires user interaction (manual transfer) 
 * followed by transaction verification via Binance API.
 */
class BinancePersonalDriver implements PaymentGatewayInterface
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
        return $this->gateway->display ?? 'Binance Personal';
    }

    public function initiate(PpTransaction $transaction): array
    {
        // For Binance Personal, we don't redirect to an external site immediately.
        // Instead, we stay on the same page to show instructions and the verification form.
        return [
            'status' => 'success',
        ];
    }

    public function verify(Request $request): bool
    {
        $orderId = $request->input('order_id');
        if (!$orderId) return false;

        $endpoint = "https://api.binance.com/sapi/v1/pay/transactions";
        $timestamp = (int) round(microtime(true) * 1000);
        
        $params = [
            'limit' => 100,
            'timestamp' => $timestamp
        ];

        $query = http_build_query($params);
        $signature = hash_hmac('sha256', $query, $this->options['secret_key'] ?? '');
        $url = $endpoint . "?" . $query . "&signature=" . $signature;

        try {
            $this->logDebug("Binance API Verify Request", ['url' => $url]);
            $response = Http::withHeaders([
                "X-MBX-APIKEY" => $this->options['api_key'] ?? ''
            ])->get($url);

            if ($response->successful()) {
                $dataRes = $response->json();
                $this->logDebug("Binance API Verify Response", ['response' => $dataRes]);

                if (isset($dataRes['data'])) {
                    foreach ($dataRes['data'] as $transaction) {
                        if ($transaction['orderId'] === $orderId && $transaction['currency'] === "USDT") {
                            $amount = (float) $transaction['amount'];
                            $expectedAmount = (float) $request->input('transaction_amount', 0);
                            
                            if ($amount >= $expectedAmount) {
                                return true;
                            }
                        }
                    }
                } else {
                    $this->logError("Binance API Error", ['message' => $dataRes['msg'] ?? 'Unknown error']);
                }
            } else {
                $this->logError("Binance API HTTP Error", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            $this->logError("BinancePersonalDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
