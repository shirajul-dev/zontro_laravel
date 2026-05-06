<?php
declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\PpGateway;
use App\Models\PpTransaction;
use App\Services\Payment\Gateways\GatewayRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * PaymentVerificationService
 * 
 * Handles manual and automated transaction verification requests
 * submitted by users (e.g., Transaction ID entry, slip upload).
 */
class PaymentVerificationService
{
    public function __construct(
        private readonly GatewayRegistry $gatewayRegistry,
        private readonly PaymentService $paymentService
    ) {
        $GLOBALS['site_url'] = rtrim(config('app.url'), '/') . '/';
        $GLOBALS['db_prefix'] = env('DB_PREFIX', 'pp_');
        if (!defined('PipraPay_INIT')) {
            define('PipraPay_INIT', true);
        }

        if (!function_exists('pp_set_transaction_status')) {
            require_once base_path('pp-content/pp-include/pp-functions.php');
        }
    }

    public function verify(Request $request): array
    {
        $ref = (string) ($request->input('transaction-id') ?? $request->input('bpid') ?? '');
        $gatewayId = (string) ($request->input('gateway-id') ?? '');
        
        if ($ref === '') {
            return ['status' => 'false', 'title' => 'Invalid Request', 'message' => 'Transaction reference is missing.'];
        }

        $transaction = PpTransaction::where('ref', $ref)->first();
        if (!$transaction) {
            return ['status' => 'false', 'title' => 'Invalid Transaction', 'message' => 'Transaction not found.'];
        }

        if ($transaction->status === 'completed') {
            return ['status' => 'true', 'is_completed' => 'true', 'title' => 'Already Paid', 'message' => 'This transaction has already been completed.'];
        }

        $gateway = PpGateway::where('gateway_id', $gatewayId)->first();
        if (!$gateway) {
            return ['status' => 'false', 'title' => 'Gateway Error', 'message' => 'Gateway configuration not found.'];
        }

        // Handle Slip Upload
        $sourceInfo = $request->except(['action-v2', 'transaction-id', 'bpid', 'gateway-id', '_token']);
        $trxId = '';
        
        // Find the first value that looks like a Trx ID
        foreach ($sourceInfo as $value) {
            if (is_string($value) && $value !== '') {
                $trxId = $value;
                break;
            }
        }

        if ($request->hasFile('slip')) {
            $file = $request->file('slip');
            $path = $file->store('slips', 'public');
            $sourceInfo['payment_slip'] = url('storage/' . $path);
            if ($trxId === '') {
                $trxId = basename($path);
            }
        }

        if ($trxId === '') {
            return ['status' => 'false', 'title' => 'Incomplete Information', 'message' => 'Please fill in all required fields.'];
        }

        // Check for duplicate Trx ID across other transactions
        $existing = PpTransaction::where('trx_id', $trxId)->where('ref', '!=', $ref)->first();
        if ($existing) {
            return ['status' => 'false', 'title' => 'Duplicate Transaction ID', 'message' => 'This Transaction ID has already been used by another transaction.'];
        }

        // Update transaction with initial info
        $transaction->gateway_id = $gatewayId;
        $transaction->trx_id = $trxId;
        
        // Check if pending is allowed
        $allowPendingParam = $gateway->parameters()->where('option_name', 'pending_payment')->first()?->value;
        $allowPending = in_array(strtolower((string)$allowPendingParam), ['enable', 'enabled', 'active']);
        
        if ($gateway->tab === 'bank' || isset($sourceInfo['payment_slip'])) {
            $allowPending = true;
        }

        $targetStatus = $allowPending ? 'pending' : 'initiated';
        
        // Use native driver if available for automated verification
        $driver = $this->gatewayRegistry->resolve($gateway);
        $verified = false;
        
        if ($driver) {
            $verified = $driver->verify($request);
        }

        if ($verified) {
            pp_set_transaction_status($ref, 'completed', $gatewayId, $trxId, $sourceInfo);
            return [
                'status' => 'true', 
                'is_completed' => 'true', 
                'title' => 'Verified Successfully', 
                'message' => 'Your payment has been verified and processed.'
            ];
        }

        // Update to pending/initiated via legacy function
        pp_set_transaction_status($ref, $targetStatus, $gatewayId, $trxId, $sourceInfo);

        if ($allowPending) {
            return [
                'status' => 'true', 
                'is_completed' => 'false', 
                'title' => 'Under Verification', 
                'message' => 'We have received your request. It is under verification, please wait a moment.'
            ];
        }

        return [
            'status' => 'false', 
            'title' => 'Verification Failed', 
            'message' => 'Payment record not found. Please ensure you have sent the correct amount and try again after a few moments.'
        ];
    }
}
