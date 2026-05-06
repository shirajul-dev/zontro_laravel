<?php

namespace Tests\Feature;

use App\Services\Legacy\LegacyRuntimeService;
use App\Services\Theme\ThemeService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class InvoiceWebhookMigrationToggleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
    }

    public function test_invoice_webhook_falls_back_to_legacy_when_toggle_disabled(): void
    {
        config(['piprapay.migration.native_invoice_webhook_enabled' => false]);

        $themeMock = Mockery::mock(ThemeService::class);
        $themeMock->shouldIgnoreMissing();
        $this->app->instance(ThemeService::class, $themeMock);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldReceive('dispatch')
            ->once()
            ->andReturn(response('{"legacy":true}', 200, ['Content-Type' => 'application/json']));
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->postJson('/invoice/webhook', ['pp_id' => 'pp_legacy']);
        $response->assertOk();
        $response->assertSee('{"legacy":true}', false);
    }

    public function test_native_invoice_webhook_updates_paid_status_when_enabled(): void
    {
        config(['piprapay.migration.native_invoice_webhook_enabled' => true]);

        DB::table('pp_transaction')->insert([
            'ref' => 'pp_123',
            'metadata' => json_encode(['invoice_id' => 'inv_1']),
            'status' => 'completed',
            'gateway_id' => 'gw_1',
        ]);

        DB::table('pp_invoice')->insert([
            'id' => 1,
            'ref' => 'inv_1',
            'status' => 'unpaid',
            'gateway_id' => '--',
            'updated_date' => '2026-04-20 00:00:00',
        ]);

        $themeMock = Mockery::mock(ThemeService::class);
        $themeMock->shouldIgnoreMissing();
        $this->app->instance(ThemeService::class, $themeMock);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->postJson('/invoice/webhook', ['pp_id' => 'pp_123']);
        $response->assertOk();
        $response->assertSee('OK', false);

        $invoice = DB::table('pp_invoice')->where('ref', 'inv_1')->first();
        $this->assertNotNull($invoice);
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('gw_1', $invoice->gateway_id);
    }

    public function test_native_invoice_webhook_returns_invalid_json_message(): void
    {
        config(['piprapay.migration.native_invoice_webhook_enabled' => true]);

        $themeMock = Mockery::mock(ThemeService::class);
        $themeMock->shouldIgnoreMissing();
        $this->app->instance(ThemeService::class, $themeMock);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->call('POST', '/invoice/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{invalid-json');

        $response->assertStatus(400);
        $response->assertSee('Invalid JSON');
    }

    private function createSchema(): void
    {
        DB::statement('CREATE TABLE IF NOT EXISTS pp_transaction (id INTEGER PRIMARY KEY AUTOINCREMENT, ref TEXT, metadata TEXT, status TEXT, gateway_id TEXT)');
        DB::statement('CREATE TABLE IF NOT EXISTS pp_invoice (id INTEGER PRIMARY KEY AUTOINCREMENT, ref TEXT, status TEXT, gateway_id TEXT, updated_date TEXT)');
    }
}
