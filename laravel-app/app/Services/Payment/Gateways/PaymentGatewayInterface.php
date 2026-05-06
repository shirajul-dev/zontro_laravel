<?php
declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Models\PpTransaction;
use Illuminate\Http\Request;

/**
 * PaymentGatewayInterface
 * 
 * Standard interface for all payment gateway drivers.
 */
interface PaymentGatewayInterface
{
    /**
     * Initiate a payment and return the redirect URL or form data.
     */
    public function initiate(PpTransaction $transaction): array;

    /**
     * Verify the payment from the gateway's IPN/Callback request.
     */
    public function verify(Request $request): bool;

    /**
     * Get the gateway's display name.
     */
    public function getDisplayName(): string;
}
