<?php
declare(strict_types=1);

namespace App\Services\Payment\Gateways\Drivers;

use App\Models\PpTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * MfsAutomationDriver
 * 
 * Handles gateways that require manual Transaction ID submission and 
 * verify against the pp_sms_data table (e.g., bKash Personal, Nagad Personal).
 */
class MfsAutomationDriver extends AbstractBaseDriver
{
    /**
     * Verify the transaction by checking the submitted Trx ID against SMS data.
     */
    public function verify(Request $request): bool
    {
        $trxId = $request->input('trxid');
        $transactionId = $request->input('transaction-id') ?? $request->input('bpid');
        
        if (!$trxId || !$transactionId) {
            return false;
        }

        $transaction = PpTransaction::where('ref', $transactionId)->first();
        if (!$transaction) return false;

        // Load gateway info to get sender_key and sender_type
        $senderKey = $this->options['sender_key'] ?? '';
        $senderType = $this->options['sender_type'] ?? '';

        // Fallback to slug-based detection for MFS automation
        if ($senderKey === '') {
            $slugParts = explode('-', $this->gateway->slug);
            $senderKey = $slugParts[0] ?? '';
            if ($senderType === '' && isset($slugParts[1])) {
                $senderType = ucfirst($slugParts[1]); // personal -> Personal
            }
        }

        // Attempt SMS verification
        $sms = DB::table('pp_sms_data')
            ->where('sender_key', $senderKey)
            ->where('type', $senderType)
            ->where('trx_id', $trxId)
            ->where('status', 'approved')
            ->first();

        if ($sms) {
            $brand = $transaction->brand;
            $allowPending = ($this->options['pending_payment'] ?? 'disable') === 'enable';
            $tolerance = $allowPending ? ($brand->payment_tolerance ?? '0') : '0';

            $paymentService = app(\App\Services\Payment\PaymentService::class);
            if ($paymentService->verifyTolerance((string)$transaction->local_net_amount, (string)$sms->amount, (string)$tolerance)) {
                // Match found! 
                // Mark SMS as used
                DB::table('pp_sms_data')
                    ->where('id', $sms->id)
                    ->update(['status' => 'used', 'updated_date' => now('UTC')->format('Y-m-d H:i:s')]);
                
                return true;
            }
        }

        return false;
    }
}
