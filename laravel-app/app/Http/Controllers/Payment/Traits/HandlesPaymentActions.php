<?php

namespace App\Http\Controllers\Payment\Traits;

use App\Services\Legacy\LegacyRuntimeService;
use Illuminate\Http\Request;

trait HandlesPaymentActions
{
    protected function handleAction(Request $request)
    {
        $action = $request->input('action-v2');

        if ($action === 'invoice') {
            return $this->handleInvoiceAction($request);
        }

        if ($action === 'payment-link' || $action === 'payment-link-default') {
            return $this->handlePaymentLinkAction($request);
        }

        if ($action === 'transaction-verify') {
            return $this->handleTransactionVerify($request);
        }

        // Delegate to legacy runtime for any other actions
        return app(LegacyRuntimeService::class)->dispatch($request);
    }

    protected function handleTransactionVerify(Request $request)
    {
        $response = $this->verificationService->verify($request);
        return response()->json($response);
    }

    protected function handleInvoiceAction(Request $request)
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

    protected function handlePaymentLinkAction(Request $request)
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
            $customerRef = $this->paymentService->generateRef(10); 
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
