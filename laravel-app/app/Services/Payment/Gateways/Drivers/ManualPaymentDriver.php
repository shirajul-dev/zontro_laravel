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
    public function getDisplayName(): string
    {
        return $this->gateway->display ?? 'Bank Transfer';
    }

    public function getLanguageStrings(): array
    {
        return [
            'bank_step_bank_name' => [
                'en' => 'Bank Name: {bank_name}',
                'bn' => 'ব্যাংকের নাম: {bank_name}',
            ],
            'bank_step_account_name' => [
                'en' => 'Account Name: {account_holder_name}',
                'bn' => 'অ্যাকাউন্টের নাম: {account_holder_name}',
            ],
            'bank_step_account_number' => [
                'en' => 'Account Number: {account_number}',
                'bn' => 'অ্যাকাউন্ট নম্বর: {account_number}',
            ],
            'bank_step_branch_name' => [
                'en' => 'Branch Name: {branch_name}',
                'bn' => 'ব্রাঞ্চের নাম: {branch_name}',
            ],
            'bank_step_routing_number' => [
                'en' => 'Routing Number: {routing_number}',
                'bn' => 'রাউটিং নম্বর: {routing_number}',
            ],
            'bank_step_slip' => [
                'en' => 'Upload the Payment Slip in the box below and press Submit',
                'bn' => 'নিচের বক্সে পেমেন্ট স্লিপ আপলোড করুন এবং জমা দিন চাপুন।',
            ],
        ];
    }

    public function getInstructions(\App\Models\PpTransaction $transaction): array
    {
        $options = $this->gateway->parameters->pluck('value', 'option_name')->toArray();

        $instructions = [];

        if (!empty($options['bank_name'])) {
            $instructions[] = [
                'icon' => '',
                'text' => 'bank_step_bank_name',
                'copy' => true,
                'value' => $options['bank_name'],
                'vars' => ['{bank_name}' => $options['bank_name']]
            ];
        }

        if (!empty($options['account_holder_name'])) {
            $instructions[] = [
                'icon' => '',
                'text' => 'bank_step_account_name',
                'copy' => true,
                'value' => $options['account_holder_name'],
                'vars' => ['{account_holder_name}' => $options['account_holder_name']]
            ];
        }

        if (!empty($options['account_number'])) {
            $instructions[] = [
                'icon' => '',
                'text' => 'bank_step_account_number',
                'copy' => true,
                'value' => $options['account_number'],
                'vars' => ['{account_number}' => $options['account_number']]
            ];
        }

        if (!empty($options['branch_name'])) {
            $instructions[] = [
                'icon' => '',
                'text' => 'bank_step_branch_name',
                'copy' => true,
                'value' => $options['branch_name'],
                'vars' => ['{branch_name}' => $options['branch_name']]
            ];
        }

        if (!empty($options['routing_number'])) {
            $instructions[] = [
                'icon' => '',
                'text' => 'bank_step_routing_number',
                'copy' => true,
                'value' => $options['routing_number'],
                'vars' => ['{routing_number}' => $options['routing_number']]
            ];
        }

        $instructions[] = [
            'icon' => '',
            'text' => 'bank_step_slip',
            'copy' => false
        ];

        return $instructions;
    }

    /**
     * For manual payments, verification is always "false" (pending)
     * as it requires admin intervention.
     */
    public function verify(Request $request): bool
    {
        return false;
    }
}
