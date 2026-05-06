<?php

namespace Tests\Feature;

use App\Services\Legacy\LegacyRuntimeService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class ApiVerifyPaymentMigrationToggleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->seedMinimalData();
    }

    public function test_native_verify_payment_success_when_toggle_enabled(): void
    {
        config(['piprapay.migration.native_api_verify_payment_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->postJson(
            '/api/verify-payment',
            ['pp_id' => 'pp_123'],
            ['MHS-PIPRAPAY-API-KEY' => 'key_123']
        );

        $response->assertOk();
        $response->assertJson([
            'pp_id' => 'pp_123',
            'full_name' => 'Test User',
            'email_address' => 'test@example.com',
            'mobile_number' => '01700000000',
            'gateway' => 'Demo Gateway',
            'amount' => '100.00',
            'fee' => '5.00',
            'discount_amount' => '10.00',
            'total' => '95.00',
            'currency' => 'BDT',
            'local_currency' => 'BDT',
            'status' => 'completed',
        ]);
    }

    public function test_native_verify_payment_returns_scope_error(): void
    {
        config(['piprapay.migration.native_api_verify_payment_enabled' => true]);

        DB::table('pp_api')->where('api_key', 'key_123')->update([
            'api_scopes' => json_encode(['create_payment']),
        ]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->postJson(
            '/api/verify-payment',
            ['pp_id' => 'pp_123'],
            ['MHS-PIPRAPAY-API-KEY' => 'key_123']
        );

        $response->assertStatus(400);
        $response->assertJsonPath('error.code', 'INSUFFICIENT_SCOPE');
    }

    public function test_verify_payment_falls_back_to_legacy_when_toggle_disabled(): void
    {
        config(['piprapay.migration.native_api_verify_payment_enabled' => false]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldReceive('dispatch')
            ->once()
            ->andReturn(response('{"legacy":true}', 200, ['Content-Type' => 'application/json']));

        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->postJson('/api/verify-payment', ['pp_id' => 'pp_123']);
        $response->assertOk();
        $response->assertJson(['legacy' => true]);
    }

    public function test_verify_payment_rejects_get_when_strict_api_methods_enabled(): void
    {
        config([
            'piprapay.migration.native_api_verify_payment_enabled' => true,
            'piprapay.security.strict_api_methods_enabled' => true,
        ]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->getJson('/api/verify-payment', [
            'MHS-PIPRAPAY-API-KEY' => 'key_123',
        ]);

        $response->assertStatus(405);
        $response->assertJsonPath('error.code', 'METHOD_NOT_ALLOWED');
    }

    public function test_verify_payment_allows_get_when_strict_api_methods_disabled(): void
    {
        config([
            'piprapay.migration.native_api_verify_payment_enabled' => true,
            'piprapay.security.strict_api_methods_enabled' => false,
        ]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->getJson('/api/verify-payment', [
            'MHS-PIPRAPAY-API-KEY' => 'key_123',
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error.code', 'INVALID_PP_ID');
    }

    private function createSchema(): void
    {
        DB::statement('CREATE TABLE IF NOT EXISTS pp_api (id INTEGER PRIMARY KEY AUTOINCREMENT, api_key TEXT, status TEXT, expired_date TEXT, api_scopes TEXT, brand_id TEXT)');
        DB::statement('CREATE TABLE IF NOT EXISTS pp_transaction (id INTEGER PRIMARY KEY AUTOINCREMENT, ref TEXT, brand_id TEXT, gateway_id TEXT, customer_info TEXT, amount TEXT, processing_fee TEXT, discount_amount TEXT, local_net_amount TEXT, currency TEXT, local_currency TEXT, metadata TEXT, sender TEXT, trx_id TEXT, status TEXT, created_date TEXT)');
        DB::statement('CREATE TABLE IF NOT EXISTS pp_gateways (id INTEGER PRIMARY KEY AUTOINCREMENT, brand_id TEXT, gateway_id TEXT, display TEXT)');
        DB::statement('CREATE TABLE IF NOT EXISTS pp_brands (id INTEGER PRIMARY KEY AUTOINCREMENT, brand_id TEXT, timezone TEXT)');
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

        DB::table('pp_brands')->insert([
            'brand_id' => 'brand_1',
            'timezone' => 'Asia/Dhaka',
        ]);

        DB::table('pp_gateways')->insert([
            'brand_id' => 'brand_1',
            'gateway_id' => 'gw_1',
            'display' => 'Demo Gateway',
        ]);

        DB::table('pp_transaction')->insert([
            'ref' => 'pp_123',
            'brand_id' => 'brand_1',
            'gateway_id' => 'gw_1',
            'customer_info' => json_encode([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'mobile' => '01700000000',
            ]),
            'amount' => '100',
            'processing_fee' => '5',
            'discount_amount' => '10',
            'local_net_amount' => '95',
            'currency' => 'BDT',
            'local_currency' => 'BDT',
            'metadata' => json_encode(['source' => 'test']),
            'sender' => 'sender-1',
            'trx_id' => 'trx-1',
            'status' => 'completed',
            'created_date' => '2026-04-20 00:00:00',
        ]);
    }
}
