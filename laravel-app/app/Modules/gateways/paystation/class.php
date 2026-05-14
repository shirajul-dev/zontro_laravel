<?php
    class PaystationGateway
    {
        public function info()
        {
            return [
                'title'       => 'PayStation Gateway',
                'logo'        => 'assets/logo.jpg',
                'currency'        => 'BDT',
                'tab'        => 'mfs',

                'gateway_type'        => 'api',
            ];
        }

        public function color()
        {
            return [
                'primary_color'        => '#351e53',
                'text_color'        => '#FFFFFF',
                'btn_color'        => '#351e53',
                'btn_text_color'        => '#FFFFFF',
            ];
        }

        public function fields()
        {
            return [
                [
                    'name'  => 'merchant_id',
                    'label' => 'Merchant ID',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'merchant_password',
                    'label' => 'Merchant Password',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'checkout_items',
                    'label' => 'Checkout items',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'pay_with_charge',
                    'label' => 'Who pay fees?',
                    'type'  => 'select',
                    'options' => [
                        '0'  => 'Customer',
                        '1' => 'Merchant',
                    ],
                    'value' => '0',
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

        // Legacy methods removed in favor of native Laravel driver (PaystationDriver)
    }
