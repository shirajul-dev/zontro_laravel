<?php
    class OxapayGateway
    {
        public function info()
        {
            return [
                'title'       => 'OxaPay Gateway',
                'logo'        => 'assets/logo.jpg',
                'currency'        => 'USD',
                'tab'        => 'global',

                'gateway_type'        => 'api',
            ];
        }

        public function color()
        {
            return [
                'primary_color'        => '#1a34c2',
                'text_color'        => '#FFFFFF',
                'btn_color'        => '#1a34c2',
                'btn_text_color'        => '#FFFFFF',
            ];
        }

        public function fields()
        {
            return [
                [
                    'name'  => 'api_key',
                    'label' => 'Merchant Api Key',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'fee_paid_by_payer',
                    'label' => 'Fee Paid By',
                    'type'  => 'select',
                    'options' => [
                        '0'  => 'Merchant',
                        '1' => 'Payer',
                    ],
                    'value' => '1',
                    'required' => true,
                    'multiple' => false,
                ],
                [
                    'name'  => 'under_paid_coverage',
                    'label' => 'Under Paid Coverage',
                    'type'  => 'text',
                    'value' => '0',
                ],
                [
                    'name'  => 'mixed_payment',
                    'label' => 'Mixed Payment',
                    'type'  => 'select',
                    'options' => [
                        'allow'  => 'Allow',
                        'disallow' => 'Disallow',
                    ],
                    'value' => 'disallow',
                    'required' => true,
                    'multiple' => false,
                ],
                [
                    'name'  => 'mode',
                    'label' => 'Mode',
                    'type'  => 'select',
                    'options' => [
                        'live'  => 'Live',
                        'sandbox' => 'Sandbox',
                    ],
                    'value' => 'live',
                    'required' => true,
                    'multiple' => false,
                ],
            ];
        }

        // Legacy methods removed in favor of native Laravel driver (OxaPayDriver)
    }
