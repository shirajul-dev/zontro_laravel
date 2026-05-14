<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Legacy\LegacyRuntimeService;
use App\Services\Theme\ThemeService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use \App\Http\Controllers\Payment\Traits\HandlesPaymentActions;

    public function __construct(
        private readonly ThemeService $themeService,
        private readonly \App\Services\Payment\PaymentService $paymentService,
        private readonly \App\Services\Common\MoneyService $moneyService,
        private readonly \App\Services\Common\BrandingService $brandingService,
        private readonly \App\Services\Payment\PaymentVerificationService $verificationService
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

}
