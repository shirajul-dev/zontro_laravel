<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\DB;
use App\Models\PpApi;
use App\Models\PpTransaction;
use App\Models\PpCustomer;
use App\Models\PpDomain;
use App\Models\PpCurrency;
use Illuminate\Support\Str;

class ApiCheckoutService
{
    public function __construct(
        private readonly \App\Services\Payment\PaymentService $paymentService,
        private readonly \App\Services\Common\MoneyService $moneyService
    ) {
    }
    public function handleCheckout(array $data, string $apiType, ?string $checkoutType, PpApi $apiRow, string $siteUrl): array
    {
        $apiScopes = $apiRow->api_scopes ?? [];
        if (is_string($apiScopes)) {
            $decoded = json_decode($apiScopes, true);
            $apiScopes = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($apiScopes) || !in_array('create_payment', $apiScopes, true)) {
            return [
                'status' => 'error',
                'code' => 403,
                'error_code' => 'INSUFFICIENT_SCOPE',
                'message' => 'The API key does not have the required permission: Create Payment',
            ];
        }

        if ($checkoutType === 'redirect' || $checkoutType === 'popup') {
            return $this->processCheckoutCreation($data, $apiRow, $checkoutType, $siteUrl);
        }

        return [
            'status' => 'error',
            'code' => 400,
            'error_code' => 'INVALID_CHECKOUT_TYPE',
            'message' => 'The checkout type provided is invalid.',
        ];
    }

    private function processCheckoutCreation(array $data, PpApi $apiRow, string $checkoutType, string $siteUrl): array
    {
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $email = trim((string) ($data['email_address'] ?? ''));
        $mobile = trim((string) ($data['mobile_number'] ?? ''));
        $amount = trim((string) ($data['amount'] ?? '0'));
        $currency = strtoupper(trim((string) ($data['currency'] ?? 'BDT')));
        $metadataRaw = $data['metadata'] ?? '{}';
        
        $returnUrl = '--';
        if ($checkoutType === 'redirect') {
            $returnUrl = trim((string) ($data['return_url'] ?? '--'));
        }
        $webhookUrl = trim((string) ($data['webhook_url'] ?? '--'));

        if ($webhookUrl === '') {
            $webhookUrl = '--';
        }
        if ($returnUrl === '') {
            $returnUrl = '--';
        }

        if ($checkoutType === 'redirect' && $returnUrl !== '--') {
            $returnDomain = $this->getDomainFromUrl($returnUrl);
            if (!$returnDomain) {
                return $this->errorResponse('INVALID_URL', 'Return URL is invalid.');
            }

            $domainCheck = PpDomain::query()->where('domain', $returnDomain)->first();
            if ($domainCheck) {
                if ($domainCheck->status !== 'active') {
                    return $this->errorResponse('INVALID_URL', 'The Return URL ("'.$returnDomain.'") is whitelisted but not active. Please activate this domain in the "Domains" section to proceed.');
                }
            } else {
                return $this->errorResponse('INVALID_URL', 'The provided Return URL ("'.$returnDomain.'") is not whitelisted. Please add this domain in the "Domains" section to continue.');
            }
        }

        if ($webhookUrl !== '--') {
            $webhookDomain = $this->getDomainFromUrl($webhookUrl);
            if (!$webhookDomain) {
                return $this->errorResponse('INVALID_URL', 'Webhook URL is invalid.');
            }

            $domainCheck = PpDomain::query()->where('domain', $webhookDomain)->first();
            if ($domainCheck) {
                if ($domainCheck->status !== 'active') {
                    return $this->errorResponse('INVALID_URL', 'The Webhook URL ("'.$webhookDomain.'") is whitelisted but not active. Please activate this domain in the "Domains" section to proceed.');
                }
            } else {
                return $this->errorResponse('INVALID_URL', 'The provided Webhook URL ("'.$webhookDomain.'") is not whitelisted. Please add this domain in the "Domains" section to continue.');
            }
        }

        $metadata = [];
        if (is_string($metadataRaw)) {
            $metadata = json_decode($metadataRaw, true);
            if ($metadata === null && json_last_error() !== JSON_ERROR_NONE) {
                return $this->errorResponse('INVALID_JSON', 'The metadata JSON is invalid.');
            }
        } elseif (is_array($metadataRaw)) {
            $metadata = $metadataRaw;
        } else {
            return $this->errorResponse('INVALID_METADATA', 'Metadata must be an array or valid JSON string.');
        }

        if ($fullName === '') {
            return $this->errorResponse('MISSING_FIELD', 'Full name is required.');
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->errorResponse('INVALID_EMAIL', 'A valid email address is required.');
        }

        if ($mobile === '') {
            return $this->errorResponse('MISSING_FIELD', 'Mobile number is required.');
        }

        if (!is_numeric($amount) || (float)$amount <= 0) {
            return $this->errorResponse('INVALID_AMOUNT', 'Amount is invalid.');
        }

        if ($currency === '') {
            return $this->errorResponse('MISSING_FIELD', 'Currency is required.');
        }

        $currencyRow = PpCurrency::query()
            ->where('brand_id', $apiRow->brand_id)
            ->where('code', $currency)
            ->first();

        if ($currencyRow === null) {
            return $this->errorResponse('INVALID_CURRENCY', 'Currency not supported.');
        }

        $customerSuspendCheck = PpCustomer::query()
            ->where('brand_id', $apiRow->brand_id)
            ->where('email', $email)
            ->where('status', 'suspend')
            ->first();

        if ($customerSuspendCheck !== null) {
            $reason = $customerSuspendCheck->suspend_reason;
            $msg = ($reason === null || $reason === '--') 
                ? 'Customer is already suspended by the admin.' 
                : 'Customer is already suspended by the admin. Reason: ' . $reason;
            return $this->errorResponse('INVALID_CUSTOMER', $msg);
        }

        $paymentId = $this->paymentService->generateRef(27);
        $customerInfo = [
            'name' => $fullName,
            'email' => $email,
            'mobile' => $mobile
        ];

        $this->paymentService->createTransaction([
            'brand_id' => $apiRow->brand_id,
            'ref' => $paymentId,
            'customer_info' => $customerInfo,
            'amount' => $amount,
            'currency' => $currency,
            'metadata' => $metadata,
            'return_url' => $returnUrl,
            'webhook_url' => $webhookUrl,
            'source' => 'api',
            'status' => 'initiated'
        ]);

        $existingCustomer = PpCustomer::query()
            ->where('brand_id', $apiRow->brand_id)
            ->where('email', $email)
            ->first();

        if ($existingCustomer === null) {
            PpCustomer::query()->create([
                'ref' => $this->generateItemId(8, 8),
                'brand_id' => $apiRow->brand_id,
                'name' => $fullName,
                'email' => $email,
                'mobile' => $mobile,
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        $paymentPath = trim((string) config('piprapay.paths.payment', 'payment'), '/');

        return [
            'status' => 'success',
            'code' => 200,
            'data' => [
                'pp_id' => $paymentId,
                'pp_url' => rtrim($siteUrl, '/') . '/' . $paymentPath . '/' . $paymentId,
            ]
        ];
    }

    private function getDomainFromUrl(string $url): ?string
    {
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['host'])) {
            $domain = $parsedUrl['host'];
            $domain = str_replace('www.', '', $domain);
            return $domain;
        }
        return null;
    }

    private function generateItemId(int $min = 8, int $max = 8): string
    {
        $length = random_int($min, $max);
        return Str::random($length);
    }

    private function errorResponse(string $code, string $message): array
    {
        return [
            'status' => 'error',
            'code' => 400,
            'error_code' => $code,
            'message' => $message,
        ];
    }
}
