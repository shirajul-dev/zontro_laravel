<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Legacy\LegacyRuntimeService;
use App\Services\Theme\ThemeService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly ThemeService $themeService,
        private readonly \App\Services\Payment\PaymentService $paymentService,
        private readonly \App\Services\Common\MoneyService $moneyService,
        private readonly \App\Services\Common\BrandingService $brandingService
    ) {
    }

    /**
     * Handle public checkout/payment page
     */
    public function show(Request $request, string $ref)
    {
        if ($request->isMethod('post') && $request->has('action-v2')) {
            return $this->handleAction($request);
        }

        return $this->themeService->renderCheckout($request, $ref);
    }

    /**
     * Handle payment link page
     */
    public function paymentLink(Request $request, string $ref)
    {
        if ($request->isMethod('post') && $request->has('action-v2')) {
            return $this->handleAction($request);
        }

        return $this->themeService->renderPaymentLink($request, $ref);
    }

    /**
     * Handle default payment link for a brand
     */
    public function paymentLinkDefault(Request $request, string $brand_id)
    {
        if ($request->isMethod('post') && $request->has('action-v2')) {
            return $this->handleAction($request);
        }

        return $this->themeService->renderPaymentLinkDefault($request, $brand_id);
    }

    private function handleAction(Request $request)
    {
        $action = $request->input('action-v2');

        if ($action === 'invoice') {
            return $this->handleInvoiceAction($request);
        }

        if ($action === 'payment-link' || $action === 'payment-link-default') {
            return $this->handlePaymentLinkAction($request);
        }

        // Delegate to legacy runtime for any other actions (like transaction-verify)
        return app(LegacyRuntimeService::class)->dispatch($request);
    }

    private function handleInvoiceAction(Request $request)
    {
        $itemId = (string) ($request->input('itemid') ?? $request->input('invoice_id') ?? '');
        
        $invoice = \App\Models\PpInvoice::where('ref', $itemId)->first();
        if (!$invoice) {
            return response()->json(['status' => 'false', 'message' => 'Invoice not found.'], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json(['status' => 'false', 'message' => 'This invoice has already been paid.'], 400);
        }

        // Calculate Total
        $total = '0';
        foreach ($invoice->items as $item) {
            $itemCost = $this->moneyService->mul($item->amount, $item->quantity);
            $itemTotalCost = $this->moneyService->sub($itemCost, $item->discount);
            $vatAmount = $this->moneyService->div($this->moneyService->mul($itemTotalCost, $item->vat), '100');
            $itemTotalCostWithVat = $this->moneyService->add($itemTotalCost, $vatAmount);
            $total = $this->moneyService->add($total, $itemTotalCostWithVat);
        }
        $total = $this->moneyService->add($total, $invoice->shipping ?? '0');

        $paymentId = $this->paymentService->generateRef(27);
        
        $transaction = $this->paymentService->createTransaction([
            'brand_id' => $invoice->brand_id,
            'ref' => $paymentId,
            'customer_info' => json_decode((string)$invoice->customer_info, true) ?: [],
            'amount' => $total,
            'currency' => $invoice->currency,
            'source' => 'invoice',
            'source_info' => ['type' => 'invoice', 'invoice_id' => $itemId],
            'status' => 'initiated'
        ]);

        $paymentPath = trim((string) config('piprapay.paths.payment', 'payment'), '/');
        
        return response()->json([
            'status' => 'true', 
            'redirect' => url('/') . '/' . $paymentPath . '/' . $paymentId
        ]);
    }

    private function handlePaymentLinkAction(Request $request)
    {
        $action = (string) $request->input('action-v2');
        $itemId = (string) ($request->input('itemid') ?? '');
        $fullName = (string) ($request->input('full-name') ?? '');
        $email = (string) ($request->input('email-address') ?? '');
        $mobile = (string) ($request->input('mobile-number') ?? '');
        $amountInput = (string) ($request->input('amount') ?? '0');

        if ($itemId === '' || $fullName === '' || $email === '' || $mobile === '') {
            return response()->json([
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.'
            ], 400);
        }

        if ($action === 'payment-link-default') {
            $brand = \App\Models\PpBrand::where('brand_id', $itemId)->first();
            if (!$brand) {
                return response()->json(['status' => 'false', 'message' => 'Brand not found.'], 404);
            }
            $brandId = $brand->brand_id;
            $currency = $this->brandingService->getSetting('payment-link-default-currency', $brandId, $brand->currency_code);
            $amount = $this->moneyService->sanitize($amountInput);
        } else {
            $link = \App\Models\PpPaymentLink::where('ref', $itemId)->first();
            if (!$link) {
                return response()->json(['status' => 'false', 'message' => 'Payment link not found.'], 404);
            }
            $brandId = $link->brand_id;
            $currency = $link->currency;
            $amount = $link->amount;
        }

        $paymentId = $this->paymentService->generateRef(27);
        $customerInfo = [
            'name' => $fullName,
            'email' => $email,
            'mobile' => $mobile
        ];

        $this->paymentService->createTransaction([
            'brand_id' => $brandId,
            'ref' => $paymentId,
            'customer_info' => $customerInfo,
            'amount' => $amount,
            'currency' => $currency,
            'source' => $action,
            'status' => 'initiated'
        ]);

        // Handle customer record
        $customer = \App\Models\PpCustomer::where('brand_id', $brandId)
            ->where('email', $email)
            ->first();
            
        if (!$customer) {
            $customerRef = $this->paymentService->generateRef(10); // Using genRef for consistency or create genCustomerRef
            \App\Models\PpCustomer::create([
                'ref' => $customerRef,
                'brand_id' => $brandId,
                'name' => $fullName,
                'email' => $email,
                'mobile' => $mobile,
                'created_date' => now('UTC')->format('Y-m-d H:i:s'),
                'updated_date' => now('UTC')->format('Y-m-d H:i:s'),
            ]);
        }

        $paymentPath = trim((string) config('piprapay.paths.payment', 'payment'), '/');
        
        return response()->json([
            'status' => 'true',
            'pp_id' => $paymentId,
            'pp_url' => url('/') . '/' . $paymentPath . '/' . $paymentId,
            'redirect' => url('/') . '/' . $paymentPath . '/' . $paymentId
        ]);
    }
}
