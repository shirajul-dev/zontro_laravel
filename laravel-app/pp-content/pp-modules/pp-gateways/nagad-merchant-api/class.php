<?php
    use Xenon\NagadApi\Base;
    use Xenon\NagadApi\Exception\NagadPaymentException;
    use Xenon\NagadApi\Helper;

    class NagadMerchantApiGateway
    {
        public function info()
        {
            return [
                'title'       => 'Nagad Merchant Api',
                'logo'        => 'assets/logo.jpg',
                'currency'        => 'BDT',
                'tab'        => 'mfs',

                'gateway_type'        => 'api',
            ];
        }

        public function color()
        {
            return [
                'primary_color'        => '#ed1c24',
                'text_color'        => '#FFFFFF',
                'btn_color'        => '#ed1c24',
                'btn_text_color'        => '#FFFFFF',
            ];
        }

        public function fields()
        {
            return [
                [
                    'name'  => 'app_account',
                    'label' => 'App Account',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'merchant_id',
                    'label' => 'Merchant ID',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'private_key',
                    'label' => 'Private Key',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'public_key',
                    'label' => 'Public Key',
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
