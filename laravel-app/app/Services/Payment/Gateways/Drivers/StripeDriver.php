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
 * StripeDriver
 * 
 * Native implementation of the Stripe Checkout API.
 */
class StripeDriver implements PaymentGatewayInterface
{
    private array $options;

    public function __construct(private readonly PpGateway $gateway)
    {
        $this->options = $gateway->parameters->pluck('value', 'option_name')->toArray();
    }

    public function getDisplayName(): string
    {
        return $this->gateway->display ?? 'Stripe';
    }

    public function initiate(PpTransaction $transaction): array
    {
        $apiUrl = "https://api.stripe.com/v1/checkout/sessions";
        
        $successUrl = route('payment.ipn', [
            'gateway_id' => $this->gateway->gateway_id,
            'ref' => $transaction->ref,
            'session_id' => '{CHECKOUT_SESSION_ID}'
        ]);

        $cancelUrl = route('payment.checkout', ['ref' => $transaction->ref]);

        $payload = [
            "payment_method_types" => ["card"],
            "line_items" => [[
                "price_data" => [
                    "currency" => $transaction->local_currency,
                    "product_data" => ["name" => "Payment for Transaction #" . $transaction->ref],
                    "unit_amount" => (int) round($transaction->local_net_amount * 100),
                ],
                "quantity" => 1,
            ]],
            "mode" => "payment",
            "success_url" => $successUrl,
            "cancel_url" => $cancelUrl,
            "metadata" => ["invoice_id" => $transaction->ref],
        ];

        try {
            $response = Http::withBasicAuth($this->options['secret_key'] ?? '', '')
                ->asForm()
                ->post($apiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['url'])) {
                    return [
                        'status' => 'success',
                        'redirect_url' => $data['url']
                    ];
                }
            }

            return [
                'status' => 'error',
                'message' => $response->json()['error']['message'] ?? 'Stripe initiation failed.'
            ];
        } catch (\Exception $e) {
            Log::error("StripeDriver Error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An internal error occurred while initiating Stripe payment.'
            ];
        }
    }

    public function verify(Request $request): bool
    {
        $sessionId = $request->query('session_id');
        $ref = $request->query('ref');

        if (!$sessionId || !$ref) {
            return false;
        }

        $apiUrl = "https://api.stripe.com/v1/checkout/sessions/{$sessionId}";

        try {
            $response = Http::withBasicAuth($this->options['secret_key'] ?? '', '')
                ->get($apiUrl);

            if ($response->successful()) {
                $session = $response->json();
                if (
                    ($session['payment_status'] ?? '') === 'paid' && 
                    ($session['metadata']['invoice_id'] ?? '') === $ref
                ) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::error("StripeDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
