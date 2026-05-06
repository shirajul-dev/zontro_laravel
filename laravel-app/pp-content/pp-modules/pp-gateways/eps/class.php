<?php
    class EpsGateway
    {
        public function info()
        {
            return [
                'title'       => 'EPS Gateway',
                'logo'        => 'assets/logo.jpg',
                'currency'        => 'BDT',
                'tab'        => 'mfs',

                'gateway_type'        => 'api',
            ];
        }

        public function color()
        {
            return [
                'primary_color'        => '#ee2d42',
                'text_color'        => '#FFFFFF',
                'btn_color'        => '#ee2d42',
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
                    'name'  => 'store_id',
                    'label' => 'Store id',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'username',
                    'label' => 'Username',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'password',
                    'label' => 'Password',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'hashkey',
                    'label' => 'Hash key',
                    'type'  => 'text',
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

        // Legacy methods removed in favor of native Laravel driver (EpsDriver)
    }
    }
