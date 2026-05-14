<?php
    class AamarpayGateway
    {
        public function info()
        {
            return [
                'title'       => 'aamarPay Gateway',
                'logo'        => 'assets/logo.jpg',
                'currency'        => 'BDT',
                'tab'        => 'mfs',

                'gateway_type'        => 'api',
            ];
        }

        public function color()
        {
            return [
                'primary_color'        => '#f39700',
                'text_color'        => '#504e52',
                'btn_color'        => '#f39700',
                'btn_text_color'        => '#504e52',
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
                    'name'  => 'signature_key',
                    'label' => 'Signature Key',
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
