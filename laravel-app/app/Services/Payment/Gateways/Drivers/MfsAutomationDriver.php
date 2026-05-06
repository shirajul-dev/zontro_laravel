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
    public function getDisplayName(): string
    {
        return $this->gateway->display ?? 'MFS Automation';
    }

    public function getLanguageStrings(): array
    {
        $slug = $this->gateway->slug;
        $parts = explode('-', $slug);
        $providerKey = $parts[0] ?? 'mfs';
        $type = $parts[1] ?? 'personal';

        $providers = [
            'bkash' => 'bKash',
            'nagad' => 'Nagad',
            'rocket' => 'Rocket',
            'upay' => 'upay',
            'tap' => 'tap',
            'cellfin' => 'Cellfin',
            'ipay' => 'iPay',
            'mcash' => 'mCash',
            'okwallet' => 'OK Wallet',
            'telecash' => 'TeleCash'
        ];

        $provider = $providers[$providerKey] ?? ucfirst($providerKey);
        
        $action = 'Send Money';
        $actionBn = 'Send Money';
        
        if ($type === 'agent') {
            $action = 'Cash Out';
            $actionBn = 'Cash Out';
        } elseif ($type === 'merchant') {
            $action = 'Payment';
            $actionBn = 'Payment';
        }

        return [
            'step_1' => [
                'en' => "Go to your {$provider} Mobile App.",
                'bn' => "আপনার {$provider} মোবাইল অ্যাপে যান।",
            ],
            'step_2' => [
                'en' => "Choose \"{$action}\"",
                'bn' => "“{$actionBn}” নির্বাচন করুন",
            ],
            'step_3' => [
                'en' => "Enter the Number: {mobile_number}",
                'bn' => "নম্বর লিখুন: {mobile_number}",
            ],
            'step_4' => [
                'en' => "Enter the Amount: {amount} {currency}",
                'bn' => "পরিমাণ লিখুন: {amount} {currency}",
            ],
            'step_5' => [
                'en' => "Now enter your {$provider} PIN to confirm.",
                'bn' => "এখন নিশ্চিত করতে আপনার {$provider} পিন লিখুন।",
            ],
            'step_6' => [
                'en' => "Put the Transaction ID in the box below and press Verify",
                'bn' => "ট্রানজ্যাকশন আইডি নিচের বক্সে লিখুন এবং যাচাই করুন চাপুন।",
            ],
            'step_qr' => [
                'en' => 'Or Scan the QR Code',
                'bn' => 'অথবা কিউআর কোড স্ক্যান করুন',
            ]
        ];
    }

    public function getInstructions(\App\Models\PpTransaction $transaction): array
    {
        $instructions = [
            ['icon' => '', 'text' => 'step_1', 'copy' => false],
            ['icon' => '', 'text' => 'step_2', 'copy' => false],
            [
                'icon' => '', 
                'text' => 'step_3', 
                'copy' => true, 
                'value' => $this->options['mobile_number'] ?? '',
                'vars' => ['{mobile_number}' => $this->options['mobile_number'] ?? '']
            ]
        ];

        // Add QR code if exists
        if (!empty($this->options['qr_code'])) {
            $instructions[] = [
                'icon' => '',
                'text' => 'step_qr',
                'action' => [
                    'type'  => 'image',
                    'label' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-qrcode"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -4" /><path d="M7 17l0 .01" /><path d="M14 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -4" /><path d="M7 7l0 .01" /><path d="M4 15a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -4" /><path d="M17 7l0 .01" /><path d="M14 14l3 0" /><path d="M20 14l0 .01" /><path d="M14 14l0 3" /><path d="M14 20l3 0" /><path d="M17 17l3 0" /><path d="M20 17l0 3" /></svg>',
                    'value' => $this->options['qr_code'],
                ]
            ];
        }

        $instructions[] = [
            'icon' => '', 
            'text' => 'step_4', 
            'copy' => true, 
            'value' => (string) $transaction->local_net_amount,
            'vars' => [
                '{amount}' => (string) $transaction->local_net_amount,
                '{currency}' => (string) $transaction->local_currency
            ]
        ];

        $instructions[] = ['icon' => '', 'text' => 'step_5', 'copy' => false];
        $instructions[] = ['icon' => '', 'text' => 'step_6', 'copy' => false];

        return $instructions;
    }

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
