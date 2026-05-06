<?php
    class TwentySixTheme
    {
        public function info()
        {
            return [
                'title'       => 'Twenty Six',
                'logo'        => 'assets/logo.jpg'
            ];
        }

        public function fields()
        {
            return [
                [
                    'name'  => 'enable_bg_image',
                    'label' => 'Background Image',
                    'type'  => 'select',
                    'options' => [
                        'enabled'  => 'Enable',
                        'disabled' => 'Disable',
                    ],
                    'value' => 'disabled',
                    'required' => false,
                    'multiple' => false,
                ],
                [
                    'name'  => 'background_image',
                    'label' => 'Background Image',
                    'required' => false,
                    'type'  => 'image',
                ],
                [
                    'name'  => 'watermark_text',
                    'label' => 'Footer Branding Text',
                    'type'  => 'text',
                    'value' => 'Powered by BillPax',
                    'required' => false,
                    'placeholder' => 'e.g. Powered by BillPax'
                ],
                [
                    'name'  => 'seo_title',
                    'label' => 'SEO Title',
                    'type'  => 'text',
                    'required' => false,
                    'placeholder' => 'Enter SEO title (max 60 characters)',
                ],
                [
                    'name'  => 'seo_description',
                    'label' => 'SEO Description',
                    'type'  => 'textarea',
                    'required' => false,
                    'placeholder' => 'Enter SEO description (max 160 characters)',
                ],
                [
                    'name'  => 'seo_keywords',
                    'label' => 'SEO Keywords',
                    'type'  => 'text',
                    'required' => false,
                    'placeholder' => 'e.g. billing, invoicing, payments',
                ],
                [
                    'name'  => 'analytics_code',
                    'label' => 'Analytics & Tracking Code',
                    'type'  => 'textarea',
                    'required' => false,
                    'placeholder' => 'Paste Google Analytics, GTM, or other tracking code',
                ],
                [
                    'name'  => 'primary_color',
                    'label' => 'Primary Color',
                    'type'  => 'color',
                    'value' => '#5f38f9',
                    'required' => false,
                    'placeholder' => '',
                ],
                [
                    'name'  => 'text_color',
                    'label' => 'Text Color',
                    'type'  => 'color',
                    'value' => '#FFFFFF',
                    'required' => false,
                    'placeholder' => '',
                ],
            ];
        }

        public function supported_languages()
        {
            return [
                'en' => 'English',
                'bn' => 'বাংলা',
                'hi' => 'हिन्दी',
                'ur' => 'اردو',
                'ar' => 'العربية',
            ];
        }

        public function lang_text()
        {
            $langs = [];
            $langDir = __DIR__ . '/langs';
            
            if (is_dir($langDir)) {
                $files = glob($langDir . '/*.json');
                foreach ($files as $file) {
                    $langCode = pathinfo($file, PATHINFO_FILENAME);
                    $content = json_decode(file_get_contents($file), true);
                    if ($content) {
                        foreach ($content as $key => $value) {
                            $langs[$key][$langCode] = $value;
                        }
                    }
                }
            }

            return $langs;
        }

        public function renderCheckout($data = [])
        {
            if($data['transaction']['status'] == "initiated"){
                if(isset($_GET['gateway'])){
                    include(__DIR__.'/gateway.php');
                }else{
                    include(__DIR__.'/checkout.php');
                }
            }else{
                include(__DIR__.'/checkout-status.php');
            }
        }

        public function renderInvoice($data = [])
        {
            include(__DIR__.'/invoice.php');
        }

        public function renderPaymentLink($data = [])
        {
            include(__DIR__.'/payment-link.php');
        }

        public function renderPaymentLinkDefault($data = [])
        {
            include(__DIR__.'/payment-link-default.php');
        }

        public function head()
        {
            // Inject theme specific head content here
            // Example: echo '<link rel="stylesheet" href="' . pp_theme_asset('css/style.css', 'twenty-six') . '">';
        }

        public function footer()
        {
            // Inject theme specific footer content here
        }
    }
