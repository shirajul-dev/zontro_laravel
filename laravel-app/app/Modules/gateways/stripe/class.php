<?php
    class StripeGateway
    {
        public function info()
        {
            return [
                'title'       => 'Stripe Gateway',
                'logo'        => 'assets/logo.jpg',
                'currency'        => 'USD',
                'tab'        => 'global',

                'gateway_type'        => 'api',
            ];
        }

        public function color()
        {
            return [
                'primary_color'        => '#635bff',
                'text_color'        => '#FFFFFF',
                'btn_color'        => '#635bff',
                'btn_text_color'        => '#FFFFFF',
            ];
        }

        public function fields()
        {
            return [
                [
                    'name'  => 'secret_key',
                    'label' => 'Stripe Secret Key',
                    'type'  => 'text',
                ],
                [
                    'name'  => 'webhook_secret',
                    'label' => 'Stripe Webhook Secret',
                    'type'  => 'text',
                ],
            ];
        }


    }
