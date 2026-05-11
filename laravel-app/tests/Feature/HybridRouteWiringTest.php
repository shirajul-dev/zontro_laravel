<?php

namespace Tests\Feature;

use App\Services\Legacy\LegacyRuntimeService;
use App\Services\Theme\ThemeService;
use Mockery;
use Tests\TestCase;

class HybridRouteWiringTest extends TestCase
{
    public function test_payment_route_uses_theme_service_render_checkout(): void
    {
        $mock = Mockery::mock(ThemeService::class);
        $mock->shouldReceive('renderCheckout')
            ->once()
            ->withArgs(function ($request, $ref) {
                return $ref === 'abc123';
            })
            ->andReturn(response('checkout-ok', 200));

        $this->app->instance(ThemeService::class, $mock);

        $response = $this->get('/payment/abc123');
        $response->assertOk();
        $response->assertSee('checkout-ok');
    }

    public function test_payment_route_dispatches_legacy_for_action_v2_posts(): void
    {
        $themeMock = Mockery::mock(ThemeService::class);
        $themeMock->shouldNotReceive('renderCheckout');

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldReceive('dispatch')
            ->once()
            ->andReturn(response('{"status":"true"}', 200, ['Content-Type' => 'application/json']));

        $this->app->instance(ThemeService::class, $themeMock);
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->post('/payment/abc123', [
            'action-v2' => 'unknown-action',
        ]);

        $response->assertOk();
        $response->assertSee('{"status":"true"}', false);
    }

    public function test_payment_link_default_route_uses_theme_service(): void
    {
        $mock = Mockery::mock(ThemeService::class);
        $mock->shouldReceive('renderPaymentLinkDefault')
            ->once()
            ->withArgs(function ($request, $brandId) {
                return $brandId === '6657227357';
            })
            ->andReturn(response('payment-link-default-ok', 200));

        $this->app->instance(ThemeService::class, $mock);

        $response = $this->get('/payment-link/default/6657227357');
        $response->assertOk();
        $response->assertSee('payment-link-default-ok');
    }

    public function test_payment_link_route_uses_theme_service(): void
    {
        $mock = Mockery::mock(ThemeService::class);
        $mock->shouldReceive('renderPaymentLink')
            ->once()
            ->withArgs(function ($request, $ref) {
                return $ref === 'pl_123';
            })
            ->andReturn(response('payment-link-ok', 200));

        $this->app->instance(ThemeService::class, $mock);

        $response = $this->get('/payment-link/pl_123');
        $response->assertOk();
        $response->assertSee('payment-link-ok');
    }

    public function test_payment_link_route_dispatches_legacy_for_action_v2_posts(): void
    {
        $themeMock = Mockery::mock(ThemeService::class);
        $themeMock->shouldNotReceive('renderPaymentLink');

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldReceive('dispatch')
            ->once()
            ->andReturn(response('{"status":"true"}', 200, ['Content-Type' => 'application/json']));

        $this->app->instance(ThemeService::class, $themeMock);
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->post('/payment-link/pl_123', [
            'action-v2' => 'unknown-action',
        ]);

        $response->assertOk();
        $response->assertSee('{"status":"true"}', false);
    }

    public function test_invoice_show_route_uses_theme_service(): void
    {
        $themeMock = Mockery::mock(ThemeService::class);
        $themeMock->shouldReceive('renderInvoice')
            ->once()
            ->withArgs(function ($request, $ref) {
                return $ref === 'inv_123';
            })
            ->andReturn(response('invoice-ok', 200));

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldIgnoreMissing();

        $this->app->instance(ThemeService::class, $themeMock);
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->get('/invoice/inv_123');
        $response->assertOk();
        $response->assertSee('invoice-ok');
    }

    public function test_invoice_webhook_route_uses_legacy_runtime(): void
    {
        $themeMock = Mockery::mock(ThemeService::class);
        $themeMock->shouldIgnoreMissing();

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldReceive('dispatch')
            ->once()
            ->andReturn(response('{"ok":true}', 200, ['Content-Type' => 'application/json']));

        $this->app->instance(ThemeService::class, $themeMock);
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->post('/invoice/webhook', ['foo' => 'bar']);
        $response->assertOk();
        $response->assertSee('{"ok":true}', false);
    }
}
