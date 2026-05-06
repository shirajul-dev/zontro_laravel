<?php

namespace Tests\Feature;

use App\Services\Legacy\LegacyRuntimeService;
use Mockery;
use Tests\TestCase;

class ApiMigrationToggleTest extends TestCase
{
    public function test_checkout_health_uses_native_path_when_enabled(): void
    {
        config(['piprapay.migration.native_api_checkout_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->getJson('/api/checkout/health');

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'source' => 'laravel-native',
            'api_type' => 'checkout',
            'api_subtype' => 'health',
        ]);
    }

    public function test_checkout_non_health_falls_back_to_legacy_even_when_enabled(): void
    {
        config(['piprapay.migration.native_api_checkout_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldReceive('dispatch')
            ->once()
            ->andReturn(response('{"legacy":true}', 200, ['Content-Type' => 'application/json']));

        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->getJson('/api/checkout/create');

        $response->assertOk();
        $response->assertJson(['legacy' => true]);
    }
}
