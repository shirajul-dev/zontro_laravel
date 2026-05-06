<?php
    class BinancePersonalGateway
    {
        public function info()
        {
            return [
                'title'       => 'Binance Personal',
                'logo'        => 'assets/logo.jpg',
                'currency'        => 'USD',
                'tab'        => 'global',

                'gateway_type'        => 'api',
            ];
        }

        public function color()
        {
            return [
                'primary_color'        => '#f0b90b',
                'text_color'        => '#000000',
                'btn_color'        => '#f0b90b',
                'btn_text_color'        => '#000000',
            ];
        }

        public function fields()
        {
            return [
                [
                    'name'  => 'binance_uid',
                    'label' => 'Binance UID',
                    'type'  => 'text',
                ],
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
                    'name'  => 'qr_code',
                    'label' => 'Qr Code',
                    'type'  => 'image',
                ]
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
            return [
                '1' => [
                    'en' => 'Go to your Binance Mobile App or Website',
                    'bn' => 'আপনার Binance মোবাইল অ্যাপ বা ওয়েবসাইটে যান',
                    'hi' => 'अपने Binance मोबाइल ऐप या वेबसाइट पर जाएं',
                    'ur' => 'اپنی Binance موبائل ایپ یا ویب سائٹ پر جائیں',
                    'ar' => 'انتقل إلى تطبيق Binance على هاتفك أو إلى الموقع الإلكتروني',
                ],

                '2' => [
                    'en' => 'Choose "Send to Binance user"',
                    'bn' => '"Send to Binance user" অপশনটি নির্বাচন করুন',
                    'hi' => '"Send to Binance user" विकल्प चुनें',
                    'ur' => '"Send to Binance user" کا انتخاب کریں',
                    'ar' => 'اختر خيار "الإرسال إلى مستخدم Binance"',
                ],

                '3' => [
                    'en' => 'Enter the Binance UID "{binance_uid}"',
                    'bn' => 'Binance UID লিখুন "{binance_uid}"',
                    'hi' => 'Binance UID दर्ज करें "{binance_uid}"',
                    'ur' => 'Binance UID درج کریں "{binance_uid}"',
                    'ar' => 'أدخل Binance UID "{binance_uid}"',
                ],

                '4' => [
                    'en' => 'Or scan the QR Code',
                    'bn' => 'অথবা কিউআর কোড স্ক্যান করুন',
                    'hi' => 'या QR कोड स्कैन करें',
                    'ur' => 'یا QR کوڈ اسکین کریں',
                    'ar' => 'أو قم بمسح رمز QR',
                ],

                '5' => [
                    'en' => 'Enter amount: {amount} {currency}',
                    'bn' => 'পরিমাণ লিখুন: {amount} {currency}',
                    'hi' => 'राशि दर्ज करें: {amount} {currency}',
                    'ur' => 'رقم درج کریں: {amount} {currency}',
                    'ar' => 'أدخل المبلغ: {amount} {currency}',
                ],

                '6' => [
                    'en' => 'Check all details carefully and confirm the transfer',
                    'bn' => 'সব তথ্য ভালোভাবে যাচাই করে ট্রান্সফার নিশ্চিত করুন',
                    'hi' => 'सभी विवरण ध्यान से जांचें और ट्रांसफर की पुष्टि करें',
                    'ur' => 'تمام تفصیلات غور سے چیک کریں اور ٹرانسفر کی تصدیق کریں',
                    'ar' => 'تحقق من جميع التفاصيل بعناية ثم أكد التحويل',
                ],

                '7' => [
                    'en' => 'Enter the transaction ID in the box below and click "Submit"',
                    'bn' => 'নিচের বক্সে ট্রানজ্যাকশন আইডি লিখুন এবং "Submit" চাপুন',
                    'hi' => 'नीचे दिए गए बॉक्स में ट्रांज़ैक्शन ID दर्ज करें और "Submit" पर क्लिक करें',
                    'ur' => 'نیچے دیے گئے باکس میں ٹرانزیکشن ID درج کریں اور "Submit" پر کلک کریں',
                    'ar' => 'أدخل معرف المعاملة في الحقل أدناه ثم اضغط على "Submit"',
                ],

                'order_id' => [
                    'en' => 'Order ID',
                    'bn' => 'অর্ডার আইডি',
                    'hi' => 'ऑर्डर आईडी',
                    'ur' => 'آرڈر آئی ڈی',
                    'ar' => 'معرّف الطلب',
                ],

                'enter_order_id' => [
                    'en' => 'Enter Order ID',
                    'bn' => 'অর্ডার আইডি লিখুন',
                    'hi' => 'ऑर्डर आईडी दर्ज करें',
                    'ur' => 'آرڈر آئی ڈی درج کریں',
                    'ar' => 'أدخل معرّف الطلب',
                ],

                'verify' => [
                    'en' => 'Verify',
                    'bn' => 'যাচাই করুন',
                    'hi' => 'सत्यापित करें',
                    'ur' => 'تصدیق کریں',
                    'ar' => 'تحقق',
                ],
            ];
        }

        public function instructions($data)
        {
            return [
                [
                    'icon' => '',
                    'text' => '1',
                    'copy' => false,
                ],
                [
                    'icon' => '',
                    'text' => '2',
                    'copy' => false,
                ],
                [
                    'icon' => '',
                    'text' => '3',
                    'copy' => true,
                    'value' => $data['options']['binance_uid'] ?? '',
                    'vars' => [
                        '{binance_uid}' => $data['options']['binance_uid'] ?? ''
                    ]
                ],
                [
                    'icon' => '',
                    'text' => '4',
                    'action' => [
                        'type'  => 'image',
                        'label' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-qrcode"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -4" /><path d="M7 17l0 .01" /><path d="M14 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -4" /><path d="M7 7l0 .01" /><path d="M4 15a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -4" /><path d="M17 7l0 .01" /><path d="M14 14l3 0" /><path d="M20 14l0 .01" /><path d="M14 14l0 3" /><path d="M14 20l3 0" /><path d="M17 17l3 0" /><path d="M20 17l0 3" /></svg>',
                        'value' => $data['options']['qr_code'] ?? '',
                    ]
                ],
                [
                    'icon' => '',
                    'text' => '5',
                    'copy' => true,
                    'value' => $data['transaction']['local_net_amount'],
                    'vars' => [
                        '{amount}' => $data['transaction']['local_net_amount'],
                        '{currency}' => $data['transaction']['local_currency']
                    ]
                ],
                [
                    'icon' => '',
                    'text' => '6',
                    'copy' => false
                ],
                [
                    'icon' => '',
                    'text' => '7',
                    'copy' => false
                ],
            ];
        }

        // Legacy methods removed in favor of native Laravel driver (BinancePersonalDriver)
    }
