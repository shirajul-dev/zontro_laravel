<?php
declare(strict_types=1);

namespace App\Services\Payment\Gateways\Drivers;

use App\Models\PpGateway;
use App\Models\PpTransaction;
use App\Services\Payment\Gateways\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * AbstractBaseDriver
 * 
 * Provides common functionality for all payment gateway drivers.
 */
abstract class AbstractBaseDriver implements PaymentGatewayInterface
{
    protected array $options;

    public function __construct(protected readonly PpGateway $gateway)
    {
        // Load gateway options from the pp_gateways_parameter table
        $this->options = $gateway->parameters->pluck('value', 'option_name')->toArray();
    }

    protected function logDebug(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/gateway_' . $this->gateway->slug . '.log'),
            ])->debug($message, $context);
        }
    }

    protected function logError(string $message, array $context = []): void
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/gateway_' . $this->gateway->slug . '.log'),
        ])->error($message, $context);
    }

    public function getDisplayName(): string
    {
        return $this->gateway->display ?? ucfirst(str_replace(['-', '_'], ' ', $this->gateway->slug));
    }

    /**
     * Default initiate logic for manual/automation gateways.
     * Redirects back to the payment page with a gateway parameter.
     */
    public function initiate(PpTransaction $transaction): array
    {
        // For manual and automated gateways, we stay on the same page to show instructions.
        // Thus, we don't return a redirect_url unless it's an external gateway.
        return [
            'status' => 'success',
        ];
    }
}
