<?php
    class PathaopayMerchantApiGateway
    {
        public function info()
        {
            return [
                'title'       => 'PathaoPay Merchant Api',
                'logo'        => 'assets/logo.jpg',
                'currency'        => 'BDT',
                'tab'        => 'mfs',

                'gateway_type'        => 'api',
            ];
        }

        public function color()
        {
            return [
                'primary_color'        => '#3b82de',
                'text_color'        => '#FFFFFF',
                'btn_color'        => '#3b82de',
                'btn_text_color'        => '#FFFFFF',
            ];
        }

        public function fields()
        {
            return [
                [
                    'name'  => 'api_key',
                    'label' => 'Api Key',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'secret_key',
                    'label' => 'Secret Key',
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

        // Legacy methods removed in favor of native Laravel driver (PathaoPayDriver)
    }