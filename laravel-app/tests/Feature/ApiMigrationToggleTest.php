<?php

namespace Tests\Feature;

use App\Services\Legacy\LegacyRuntimeService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class ApiMigrationToggleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
        $this->seedMinimalData();
    }

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

        $response = $this->getJson('/api/checkout/create', ['MHS-PIPRAPAY-API-KEY' => 'key_123']);

        $response->assertOk();
        $response->assertJson(['legacy' => true]);
    }

    private function createSchema(): void
    {
        DB::statement('CREATE TABLE IF NOT EXISTS pp_api (id INTEGER PRIMARY KEY AUTOINCREMENT, api_key TEXT, status TEXT, expired_date TEXT, api_scopes TEXT, brand_id TEXT)');
    }

    private function seedMinimalData(): void
    {
        DB::table('pp_api')->insert([
            'api_key' => 'key_123',
            'status' => 'active',
            'expired_date' => '--',
            'api_scopes' => json_encode(['verify_payment']),
            'brand_id' => 'brand_1',
        ]);
    }
}

