<?php
declare(strict_types=1);

namespace App\Services\Payment\Gateways\Drivers;

use App\Models\PpGateway;
use App\Models\PpTransaction;
use App\Services\Payment\Gateways\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Xenon\NagadApi\Base;
use Xenon\NagadApi\Helper;

/**
 * NagadDriver
 * 
 * Native implementation of the Nagad Merchant API gateway.
 */
class NagadDriver implements PaymentGatewayInterface
{
    private array $options;

    public function __construct(private readonly PpGateway $gateway)
    {
        // Load gateway options from the pp_gateways_parameter table
        $this->options = $gateway->parameters->pluck('value', 'option_name')->toArray();
    }

    public function getDisplayName(): string
    {
        return $this->gateway->display ?? 'Nagad';
    }

    private function getConfig(): array
    {
        return [
            'NAGAD_APP_ENV' => ($this->options['mode'] ?? 'sandbox') === 'sandbox' ? 'development' : 'production',
            'NAGAD_APP_LOG' => '1',
            'NAGAD_APP_ACCOUNT' => $this->options['app_account'] ?? '',
            'NAGAD_APP_MERCHANTID' => $this->options['merchant_id'] ?? '',
            'NAGAD_APP_MERCHANT_PRIVATE_KEY' => $this->options['private_key'] ?? '',
            'NAGAD_APP_MERCHANT_PG_PUBLIC_KEY' => $this->options['public_key'] ?? '',
            'NAGAD_APP_TIMEZONE' => 'Asia/Dhaka',
        ];
    }

    public function initiate(PpTransaction $transaction): array
    {
        try {
            $config = $this->getConfig();
            
            // Note: Nagad SDK might expect a string for amount
            $nagad = new Base($config, [
                'amount' => (string) round((float)$transaction->local_net_amount),
                'invoice' => $transaction->ref . '_' . time(),
                'merchantCallback' => route('payment.ipn', ['gateway_id' => $this->gateway->gateway_id, 'ref' => $transaction->ref]),
            ]);
    
            // The payNow method usually returns a redirect URL or handles the redirect
            // If it returns a string (URL), we return it.
            $redirectUrl = $nagad->payNow($nagad);

            if (is_string($redirectUrl) && filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                return [
                    'status' => 'success',
                    'redirect_url' => $redirectUrl
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Nagad initiation failed to return a valid URL.'
            ];

        } catch (\Exception $e) {
            Log::error("NagadDriver Initiation Error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An error occurred during Nagad payment initiation: ' . $e->getMessage()
            ];
        }
    }

    public function verify(Request $request): bool
    {
        $paymentRefId = $request->input('payment_ref_id');
        if (!$paymentRefId) {
            // Check successResponse helper from SDK
            $responseArray = Helper::successResponse(url()->full());
            $paymentRefId = $responseArray['payment_ref_id'] ?? null;
        }

        if (!$paymentRefId) return false;

        try {
            $config = $this->getConfig();
            $helper = new Helper($config);
            $response = $helper->verifyPayment($paymentRefId);
            $data = json_decode($response, true);

            if (isset($data['status']) && $data['status'] === 'Success') {
                return true;
            }
        } catch (\Exception $e) {
            Log::error("NagadDriver Verify Error: " . $e->getMessage());
        }

        return false;
    }
}
