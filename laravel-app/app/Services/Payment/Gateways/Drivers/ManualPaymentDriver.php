<?php
declare(strict_types=1);

namespace App\Services\Payment\Gateways\Drivers;

use Illuminate\Http\Request;

/**
 * ManualPaymentDriver
 * 
 * Handles gateways that require manual review (Bank, Wise, etc.) 
 * and usually involve a payment slip upload.
 */
class ManualPaymentDriver extends AbstractBaseDriver
{
    /**
     * For manual payments, verification is always "false" (pending) 
     * as it requires admin intervention.
     */
    public function verify(Request $request): bool
    {
        return false;
    }
}
