<?php
declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Models\PpGateway;
use Illuminate\Support\Facades\Log;

/**
 * GatewayRegistry
 * 
 * Factory for resolving and instantiating payment gateway drivers.
 */
class GatewayRegistry
{
    /**
     * @var array<string, string> Map of gateway slugs to driver classes.
     */
    private array $drivers = [];

    public function __construct()
    {
        $this->register('sslcommerz', \App\Services\Payment\Gateways\Drivers\SslCommerzDriver::class);
        $this->register('bkash-api-tokenized', \App\Services\Payment\Gateways\Drivers\BkashApiTokenizedDriver::class);
        $this->register('stripe', \App\Services\Payment\Gateways\Drivers\StripeDriver::class);
        $this->register('aamarpay', \App\Services\Payment\Gateways\Drivers\AamarpayDriver::class);
        $this->register('nagad-merchant-api', \App\Services\Payment\Gateways\Drivers\NagadDriver::class);
        $this->register('shurjopay', \App\Services\Payment\Gateways\Drivers\ShurjopayDriver::class);
    }

    /**
     * Register a new gateway driver.
     */
    public function register(string $slug, string $driverClass): void
    {
        $this->drivers[$slug] = $driverClass;
    }

    /**
     * Resolve a driver instance for a given gateway model.
     */
    public function resolve(PpGateway $gateway): ?PaymentGatewayInterface
    {
        $slug = $gateway->slug;
        
        if (!isset($this->drivers[$slug])) {
            Log::warning("GatewayRegistry: No native driver registered for slug [{$slug}]");
            return null;
        }

        $class = $this->drivers[$slug];
        
        if (!class_exists($class)) {
            Log::error("GatewayRegistry: Driver class [{$class}] not found for slug [{$slug}]");
            return null;
        }

        return new $class($gateway);
    }

    /**
     * Resolve a driver by gateway_id string.
     */
    public function resolveById(string $gatewayId): ?PaymentGatewayInterface
    {
        $gateway = PpGateway::where('gateway_id', $gatewayId)->first();
        
        if (!$gateway) {
            Log::warning("GatewayRegistry: Gateway ID [{$gatewayId}] not found in database.");
            return null;
        }

        return $this->resolve($gateway);
    }
}
