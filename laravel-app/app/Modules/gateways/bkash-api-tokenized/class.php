<?php
    class BkashApiTokenizedGateway
    {
        public function info()
        {
            return [
                'title'       => 'Bkash Api (Tokenized)',
                'logo'        => 'assets/logo.jpg',
                'currency'        => 'BDT',
                'tab'        => 'mfs',

                'gateway_type'        => 'api',
            ];
        }

        public function color()
        {
            return [
                'primary_color'        => '#D12053',
                'text_color'        => '#FFFFFF',
                'btn_color'        => '#D12053',
                'btn_text_color'        => '#FFFFFF',
            ];
        }

        public function fields()
        {
            return [
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
                    'name'  => 'app_key',
                    'label' => 'App Key',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'app_secret_key',
                    'label' => 'App Secret Key',
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