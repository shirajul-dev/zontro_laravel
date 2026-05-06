<?php
    class ShurjopayGateway
    {
        public function info()
        {
            return [
                'title'       => 'shurjoPay Gateway',
                'logo'        => 'assets/logo.jpg',
                'currency'        => 'BDT',
                'tab'        => 'mfs',

                'gateway_type'        => 'api',
            ];
        }

        public function color()
        {
            return [
                'primary_color'        => '#229454',
                'text_color'        => '#FFFFFF',
                'btn_color'        => '#229454',
                'btn_text_color'        => '#FFFFFF',
            ];
        }

        public function fields()
        {
            return [
                [
                    'name'  => 'prefix',
                    'label' => 'Transaction Prefix',
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
