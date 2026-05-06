<?php
declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\PpTransaction;
use App\Services\Common\MoneyService;

/**
 * PaymentService
 * 
 * Core business logic for transaction processing and verification.
 */
class PaymentService
{
    public function __construct(
        private readonly MoneyService $moneyService
    ) {
    }

    /**
     * Verify if the paid amount is within the allowed tolerance for a transaction.
     */
    public function verifyTolerance(string $expected, string $actual, string $tolerance): bool
    {
        $expected  = $this->moneyService->round($expected);
        $actual    = $this->moneyService->round($actual);
        $tolerance = $this->moneyService->round($tolerance);

        if ($this->moneyService->compare($expected, '0') <= 0 || $this->moneyService->compare($actual, '0') <= 0) {
            return false;
        }

        $maxAllowed = $this->moneyService->add($expected, $tolerance);

        return (
            $this->moneyService->compare($actual, $expected) >= 0 &&
            $this->moneyService->compare($actual, $maxAllowed) <= 0
        );
    }

    /**
     * Update transaction status and trigger related events.
     */
    public function updateStatus(PpTransaction $transaction, string $status): bool
    {
        $transaction->status = $status;
        $transaction->updated_date = now('UTC')->format('Y-m-d H:i:s');
        
        return $transaction->save();
    }

    /**
     * Create a new transaction record natively.
     */
    public function createTransaction(array $data): PpTransaction
    {
        $transaction = new PpTransaction();
        $transaction->brand_id = $data['brand_id'];
        $transaction->ref = $data['ref'] ?? $this->generateRef(27);
        $transaction->amount = $this->moneyService->sanitize($data['amount']);
        $transaction->currency = $data['currency'];
        $transaction->status = $data['status'] ?? 'initiated';
        
        $transaction->customer_info = is_array($data['customer_info']) 
            ? json_encode($data['customer_info'], JSON_UNESCAPED_UNICODE) 
            : $data['customer_info'];
            
        $transaction->metadata = isset($data['metadata']) 
            ? (is_array($data['metadata']) ? json_encode($data['metadata']) : $data['metadata'])
            : '{}';

        $transaction->source = $data['source'] ?? 'api';
        $transaction->source_info = isset($data['source_info']) 
            ? (is_array($data['source_info']) ? json_encode($data['source_info']) : $data['source_info'])
            : null;

        $transaction->return_url = $data['return_url'] ?? null;
        $transaction->webhook_url = $data['webhook_url'] ?? null;
        
        $transaction->processing_fee = '0.00';
        $transaction->discount_amount = '0.00';
        $transaction->local_net_amount = '0.00';
        $transaction->sender = '--';
        $transaction->trx_id = '--';
        $transaction->gateway_id = 0;
        $transaction->sender_type = '--';
        $transaction->sender_key = '--';
        $transaction->trx_slip = '--';
        $transaction->local_currency = $data['currency'];

        $now = now('UTC')->format('Y-m-d H:i:s');
        $transaction->created_date = $now;
        $transaction->updated_date = $now;

        $transaction->save();

        return $transaction;
    }

    /**
     * Generate a numeric unique reference ID (matching legacy generateItemID).
     */
    public function generateRef(int $length = 27): string
    {
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= (string) mt_rand(0, 9);
        }
        return $id;
    }
}
