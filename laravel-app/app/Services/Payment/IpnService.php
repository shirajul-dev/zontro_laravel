<?php

namespace App\Services\Payment;

use App\Models\PpGateway;
use App\Models\PpBrand;
use App\Models\PpGatewaysParameter;

class IpnService
{
    public function __construct(
        private readonly \App\Services\Payment\Gateways\GatewayRegistry $gatewayRegistry
    ) {
    }
    public function handleIpn(string $gatewayId, string $siteUrl): array
    {
        $gatewayRow = PpGateway::query()
            ->where('gateway_id', $gatewayId)
            ->first();

        if ($gatewayRow === null) {
            return [
                'status' => 'error',
                'code' => 400,
                'error_code' => 'INVALID_GATEWAY',
                'message' => 'The Gateway provided is incorrect or invalid.',
            ];
        }

        // Try native driver first
        $nativeDriver = $this->gatewayRegistry->resolve($gatewayRow);
        if ($nativeDriver) {
            $status = $nativeDriver->verify(request());
            
            if ($status) {
                // Standardized resolution of transaction reference
                $ref = request()->input('ref') ?? 
                       request()->input('opt_a') ?? 
                       request()->input('mer_txnid') ?? 
                       request()->input('order_id');

                // Strip time suffix if present (from our drivers' tran_id logic)
                if ($ref && str_contains($ref, '_')) {
                    $ref = explode('_', $ref)[0];
                }

                if ($ref) {
                    $transaction = \App\Models\PpTransaction::where('ref', $ref)->first();
                    if ($transaction) {
                        $paymentService = app(\App\Services\Payment\PaymentService::class);
                        
                        // Extract gateway transaction ID
                        $gatewayTrxId = request()->input('bank_trxid') ?? 
                                        request()->input('pg_txnid') ?? 
                                        request()->input('bank_tran_id') ?? 
                                        request()->input('transaction_id') ?? 
                                        request()->input('paymentID') ??
                                        request()->input('mer_txnid');

                        $paymentService->updateStatus($transaction, 'completed');
                        
                        $transaction->gateway_id = $gatewayId;
                        if ($gatewayTrxId) {
                            $transaction->trx_id = (string) $gatewayTrxId;
                        }
                        $transaction->save();

                        // Fire native events if needed
                        event(new \App\Events\PaymentCompleted($transaction));
                    }
                }

                return [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Payment Verified'
                ];
            }

            return [
                'status' => 'error',
                'code' => 400,
                'message' => 'Payment Verification Failed'
            ];
        }

        $brandRow = $gatewayRow->brand;

        if ($brandRow === null) {
            return [
                'status' => 'error',
                'code' => 400,
                'error_code' => 'INVALID_GATEWAY',
                'message' => 'The Gateway provided is incorrect or invalid.',
            ];
        }

        $gatewayParams = $gatewayRow->parameters;

        $options = [];
        foreach ($gatewayParams as $field) {
            $value = $field->value;
            if (!empty($field->multiple) && !empty($value)) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $value = $decoded;
                }
            }
            $options[$field->option_name] = $value;
        }

        $slug = (string) $gatewayRow->slug;
        $classFile = base_path('pp-content/pp-modules/pp-gateways/' . $slug . '/class.php');

        if (!file_exists($classFile)) {
            return [
                'status' => 'error',
                'code' => 400,
                'error_code' => 'INVALID_GATEWAY',
                'message' => 'The Gateway provided is incorrect or invalid.',
            ];
        }

        require_once $classFile;

        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))) . 'Gateway';
        
        if (!class_exists($className)) {
            return [
                'status' => 'error',
                'code' => 400,
                'error_code' => 'INVALID_GATEWAY',
                'message' => 'The Gateway provided is incorrect or invalid.',
            ];
        }

        $gatewayObject = new $className();
        $baseLanguage = (string) ($brandRow->language ?? 'en');

        $responseStruct = [
            'gateway' => [
                'gateway_id' => $gatewayRow->gateway_id,
                'slug' => $gatewayRow->slug,
                'name' => $gatewayRow->name,
                'display' => $gatewayRow->display,
                'logo' => $gatewayRow->logo,
                'currency' => $gatewayRow->currency,
                'min_allow' => money_round((float) $gatewayRow->min_allow),
                'max_allow' => money_round((float) $gatewayRow->max_allow),
                'fixed_discount' => money_round((float) $gatewayRow->fixed_discount),
                'percentage_discount' => money_round((float) $gatewayRow->percentage_discount),
                'fixed_charge' => money_round((float) $gatewayRow->fixed_charge),
                'percentage_charge' => money_round((float) $gatewayRow->percentage_charge),
                'primary_color' => $gatewayRow->primary_color,
                'text_color' => $gatewayRow->text_color,
                'btn_color' => $gatewayRow->btn_color,
                'btn_text_color' => $gatewayRow->btn_text_color,
                'options' => $options,
            ],
            'brand' => [
                'id' => $brandRow->brand_id,
                'name' => $brandRow->name,
                'identifyName' => $brandRow->identify_name,
                'logo' => $brandRow->logo !== '--' ? $brandRow->logo : null,
                'favicon' => $brandRow->favicon !== '--' ? $brandRow->favicon : null,
                'support' => [
                    'email' => $brandRow->support_email_address,
                    'phone' => $brandRow->support_phone_number,
                    'website' => $brandRow->support_website,
                    'whatsapp' => $brandRow->whatsapp_number,
                    'telegram' => 'https://t.me/' . $brandRow->telegram,
                    'messenger' => 'https://m.me/' . $brandRow->facebook_messenger,
                    'fb_page' => 'https://facebook.com/' . $brandRow->facebook_page,
                ],
                'address' => [
                    'street' => $brandRow->street_address,
                    'city' => $brandRow->city_town,
                    'postal' => $brandRow->postal_code,
                    'country' => $brandRow->country,
                ],
                'locale' => [
                    'timezone' => $brandRow->timezone,
                    'language' => $baseLanguage,
                    'currency' => $brandRow->currency_code,
                ],
            ],
            // Since we aren't loading translations dynamically via old globals easily,
            // we provide an empty lang array to prevent undefined array keys if needed.
            'lang' => []
        ];

        if (is_callable([$gatewayObject, 'ipn'])) {
            $gatewayObject->ipn($responseStruct);
            // ipn() function inside the gateway plugin typically outputs or dies on its own hook.
            // If it returns nicely we return success.
            return [
                'status' => 'success',
                'code' => 200,
            ];
        }

        return [
            'status' => 'error',
            'code' => 400,
            'error_code' => 'INVALID_GATEWAY',
            'message' => 'The Gateway provided is incorrect or invalid.',
        ];
    }
}
