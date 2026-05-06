<?php

namespace Tests\Feature;

use App\Services\Legacy\LegacyRuntimeService;
use App\Services\Theme\ThemeService;
use Mockery;
use Tests\TestCase;

class PaymentLinkSubmitRedirectFlowTest extends TestCase
{
    public function test_payment_link_submit_redirect_and_gateway_checkout_contract(): void
    {
        $fixturePath = base_path('tests/Fixtures/payment_link_submit_payload.json');
        $payload = json_decode((string) file_get_contents($fixturePath), true);

        $this->assertIsArray($payload);

        $redirectUrl = '/payment/txn_contract_123?gateway=bank';

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldReceive('dispatch')
            ->once()
            ->andReturn(response(
                json_encode([
                    'status' => 'true',
                    'redirect' => $redirectUrl,
                    'source' => 'legacy-bridge',
                ], JSON_UNESCAPED_SLASHES),
                200,
                ['Content-Type' => 'application/json']
            ));

        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $submitResponse = $this->post('/', $payload);

        $submitResponse->assertOk();
        $submitResponse->assertJsonPath('status', 'true');
        $submitResponse->assertJsonPath('redirect', $redirectUrl);

        $themeMock = Mockery::mock(ThemeService::class);
        $themeMock->shouldReceive('renderCheckout')
            ->once()
            ->withArgs(function ($request, $ref) {
                return $ref === 'txn_contract_123' && (string) $request->query('gateway', '') === 'bank';
            })
            ->andReturn(response('checkout-contract-ok', 200));

        $this->app->instance(ThemeService::class, $themeMock);

        $checkoutResponse = $this->get($redirectUrl);
        $checkoutResponse->assertOk();
        $checkoutResponse->assertSee('checkout-contract-ok');
    }
}
