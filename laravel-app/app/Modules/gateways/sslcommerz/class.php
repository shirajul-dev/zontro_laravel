<?php
    class SslcommerzGateway
    {
        public function info()
        {
            return [
                'title'       => 'SSLCommerz Gateway',
                'logo'        => 'assets/logo.jpg',
                'currency'        => 'BDT',
                'tab'        => 'mfs',

                'gateway_type'        => 'api',
            ];
        }

        public function color()
        {
            return [
                'primary_color'        => '#295cab',
                'text_color'        => '#FFFFFF',
                'btn_color'        => '#295cab',
                'btn_text_color'        => '#FFFFFF',
            ];
        }

        public function fields()
        {
            return [
                [
                    'name'  => 'store_id',
                    'label' => 'Store ID',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'store_password',
                    'label' => 'Store Password',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'product_category',
                    'label' => 'Product Category',
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


    }