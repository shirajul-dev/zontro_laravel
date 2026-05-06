<?php

namespace Tests\Feature;

use App\Services\Legacy\LegacyRuntimeService;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\TestCase;

class AdminActionsMigrationToggleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanupFallbackTrackerFile();
    }

    protected function tearDown(): void
    {
        $this->cleanupFallbackTrackerFile();

        parent::tearDown();
    }

    public function test_admin_actions_fall_back_to_legacy_when_toggle_disabled(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => false]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldReceive('dispatch')
            ->once()
            ->andReturn(response('{"legacy":true}', 200, ['Content-Type' => 'application/json']));

        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->post('/admin/dashboard', [
            'action' => 'set-default-brand',
            'brand_id' => 'brand_1',
        ]);

        $response->assertOk();
        $response->assertSee('{"legacy":true}', false);
    }

    public function test_admin_actions_use_native_handlers_when_toggle_enabled(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->post('/admin/dashboard', [
            'action' => 'set-default-brand',
            'brand_id' => 'brand_1',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'false');
        $response->assertJsonPath('title', 'Request Failed');
    }

    public function test_unknown_action_fallback_is_recorded_for_observability(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldReceive('dispatch')
            ->once()
            ->andReturn(response('{"legacy":true}', 200, ['Content-Type' => 'application/json']));

        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->post('/admin/dashboard', [
            'action' => 'unknown-admin-action',
        ]);

        $response->assertOk();

        $filePath = $this->fallbackTrackerFilePath();
        $this->assertTrue(File::exists($filePath));

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertIsArray($lines);
        $this->assertCount(1, $lines);

        $row = json_decode((string) $lines[0], true);
        $this->assertIsArray($row);
        $this->assertSame('unknown_action', (string) ($row['reason'] ?? ''));
        $this->assertSame('unknown-admin-action', (string) ($row['action'] ?? ''));
    }

    public function test_dashboard_transaction_statistics_action_is_handled_natively(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->post('/admin/dashboard', [
            'action' => 'dashboard-transaction-statistics',
            'date' => 'this_year',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'false');
        $response->assertJsonPath('title', 'Request Failed');
        $response->assertJsonPath('message', 'Invalid request');
    }

    public function test_dashboard_gateway_statistics_action_is_handled_natively(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->post('/admin/dashboard', [
            'action' => 'dashboard-gateway-statistics',
            'date' => 'this_year',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'false');
        $response->assertJsonPath('title', 'Request Failed');
        $response->assertJsonPath('message', 'Invalid request');
    }

    public function test_device_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $responseList = $this->post('/admin/dashboard', [
            'action' => 'device-list',
        ]);

        $responseList->assertOk();
        $responseList->assertJsonPath('status', 'false');
        $responseList->assertJsonPath('title', 'Request Failed');

        $responseConnect = $this->post('/admin/dashboard', [
            'action' => 'device-connect-info',
        ]);

        $responseConnect->assertOk();
        $responseConnect->assertJsonPath('status', 'false');
        $responseConnect->assertJsonPath('title', 'Request Failed');

        $responseDelete = $this->post('/admin/dashboard', [
            'action' => 'device-delete',
            'ItemID' => 'DUMMY',
        ]);

        $responseDelete->assertOk();
        $responseDelete->assertJsonPath('status', 'false');
        $responseDelete->assertJsonPath('title', 'Request Failed');

        $responseBulk = $this->post('/admin/dashboard', [
            'action' => 'device-bulk-action',
            'actionID' => 'deleted',
            'selected_ids' => '[]',
        ]);

        $responseBulk->assertOk();
        $responseBulk->assertJsonPath('status', 'false');
        $responseBulk->assertJsonPath('title', 'Request Failed');
    }

    public function test_sms_data_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $responseList = $this->post('/admin/dashboard', [
            'action' => 'sms-data-list',
        ]);

        $responseList->assertOk();
        $responseList->assertJsonPath('status', 'false');
        $responseList->assertJsonPath('title', 'Request Failed');

        $responseCreate = $this->post('/admin/dashboard', [
            'action' => 'sms-data-create',
            'entry_type' => 'manual',
            'sender_key' => 'telecash',
            'status' => 'approved',
        ]);

        $responseCreate->assertOk();
        $responseCreate->assertJsonPath('status', 'false');
        $responseCreate->assertJsonPath('title', 'Request Failed');

        $responseInfo = $this->post('/admin/dashboard', [
            'action' => 'sms-data-info-byID',
            'ItemID' => 1,
        ]);

        $responseInfo->assertOk();
        $responseInfo->assertJsonPath('status', 'false');
        $responseInfo->assertJsonPath('title', 'Request Failed');

        $responseEdit = $this->post('/admin/dashboard', [
            'action' => 'sms-data-edit',
            'itemid' => 1,
        ]);

        $responseEdit->assertOk();
        $responseEdit->assertJsonPath('status', 'false');
        $responseEdit->assertJsonPath('title', 'Request Failed');

        $responseDelete = $this->post('/admin/dashboard', [
            'action' => 'sms-data-delete',
            'ItemID' => 1,
        ]);

        $responseDelete->assertOk();
        $responseDelete->assertJsonPath('status', 'false');
        $responseDelete->assertJsonPath('title', 'Request Failed');

        $responseBulk = $this->post('/admin/dashboard', [
            'action' => 'sms-data-bulk-action',
            'actionID' => 'deleted',
            'selected_ids' => '[]',
        ]);

        $responseBulk->assertOk();
        $responseBulk->assertJsonPath('status', 'false');
        $responseBulk->assertJsonPath('title', 'Request Failed');
    }

    public function test_balance_verification_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'balance-verification-list', 'd_id' => 'DUMMY'],
            ['action' => 'balance-verification-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
            ['action' => 'balance-verification-delete', 'ItemID' => 1],
            ['action' => 'balance-verification-create', 'd_id' => 'DUMMY'],
            ['action' => 'balance-verification-iupdate', 'ItemID' => 1, 'balance' => '0'],
            ['action' => 'balance-verification-info-byID', 'ItemID' => 1],
            ['action' => 'balance-verification-update', 'itemID' => 1],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_faq_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'faq-list'],
            ['action' => 'faq-create', 'faq_title' => 'T', 'faq_description' => 'D', 'faq_status' => 'active'],
            ['action' => 'faq-info-byID', 'ItemID' => 1],
            ['action' => 'faq-edit', 'faq_id' => 1, 'faq_title' => 'T', 'faq_description' => 'D', 'faq_status' => 'active'],
            ['action' => 'faq-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
            ['action' => 'faq-delete', 'ItemID' => 1],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_domain_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'all-domain-list'],
            ['action' => 'domains-info-byID', 'ItemID' => 1],
            ['action' => 'create-domains', 'domain_name' => 'example.com', 'domain_status' => 'active'],
            ['action' => 'domains-edit', 'domain_id' => 1, 'domain_name' => 'example.com', 'domain_status' => 'active'],
            ['action' => 'domains-delete', 'ItemID' => 1],
            ['action' => 'domain-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_customer_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'customer-list'],
            ['action' => 'customers-create', 'name' => 'A', 'email' => 'a@example.com', 'mobile' => '0123', 'status' => 'active'],
            ['action' => 'customers-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
            ['action' => 'customers-delete', 'ItemID' => 'CUST1'],
            ['action' => 'customers-info-byID', 'ItemID' => 'CUST1'],
            ['action' => 'customers-edit', 'customer_id' => 'CUST1', 'name' => 'A', 'email' => 'a@example.com', 'mobile' => '0123', 'status' => 'active'],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_transaction_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'transaction-list'],
            ['action' => 'transaction-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
            ['action' => 'transaction-delete', 'ItemID' => 'TRX1'],
            ['action' => 'transaction-ipn', 'ItemID' => 'TRX1'],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_brand_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'all-brand-list'],
            ['action' => 'brand-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
            ['action' => 'brand-delete', 'ItemID' => 'BRAND1'],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_api_settings_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'api-list'],
            ['action' => 'api-create', 'api_name' => 'Key A', 'apiExpiryDate' => '', 'api_status' => 'active', 'scopes' => []],
            ['action' => 'api-info-byID', 'ItemID' => '1'],
            ['action' => 'api-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
            ['action' => 'api-delete', 'ItemID' => '1'],
            ['action' => 'api-edit', 'api_id' => '1', 'api_name' => 'Key A', 'apiExpiryDate' => '', 'api_status' => 'active', 'scopes' => []],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_currency_settings_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'currency-list'],
            ['action' => 'currency-edit', 'currency_id' => '1', 'currency_symbol' => '$', 'currency_rate' => '1'],
            ['action' => 'currency-info-byID', 'ItemID' => '1'],
            ['action' => 'currency-bulkImport'],
            ['action' => 'currency-rateSync', 'ItemID' => '1'],
            ['action' => 'currency-bulk-rateSync'],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_gateway_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'gateway-create', 'gateway' => 'bkash'],
            ['action' => 'gateways-list'],
            ['action' => 'gateways-delete', 'ItemID' => 'GW1'],
            ['action' => 'gateways-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_reports_action_is_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $response = $this->post('/admin/dashboard', [
            'action' => 'reports',
            'date' => 'this_year',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'false');
        $response->assertJsonPath('title', 'Request Failed');
    }

    public function test_invoice_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'invoice-list'],
            ['action' => 'invoice-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
            ['action' => 'invoice-delete', 'ItemID' => 'INV1'],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_payment_link_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'paymentLink-list'],
            ['action' => 'paymentLink-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
            ['action' => 'paymentLink-delete', 'ItemID' => 'PL1'],
            ['action' => 'paymentLink-defaultLinkCurrency', 'DefaultCurrency' => 'USD'],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_staff_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'staff-management-list'],
            ['action' => 'staff-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
            ['action' => 'staff-delete', 'ItemID' => 'STAFF1'],
            ['action' => 'staff-permissions', 'a_id' => 'STAFF1'],
            ['action' => 'staff-permission-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
            ['action' => 'staff-permission-delete', 'ItemID' => '1'],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_optional_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'activities-list'],
            ['action' => 'addons-list'],
            ['action' => 'addons-create', 'addon' => 'demo-addon'],
            ['action' => 'addons-delete', 'ItemID' => 'ADDON1'],
            ['action' => 'addons-bulk-action', 'actionID' => 'deleted', 'selected_ids' => '[]'],
            ['action' => 'themes-new-active', 'slug' => 'twenty-six'],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    public function test_system_settings_actions_are_handled_natively_without_legacy_dispatch(): void
    {
        config(['piprapay.migration.native_admin_actions_enabled' => true]);

        $legacyMock = Mockery::mock(LegacyRuntimeService::class);
        $legacyMock->shouldNotReceive('dispatch');
        $this->app->instance(LegacyRuntimeService::class, $legacyMock);

        $actions = [
            ['action' => 'geneal-application-settings'],
            ['action' => 'cron-job-command-generate'],
            ['action' => 'system-settings-update-setting'],
            ['action' => 'system-settings-update-check'],
            ['action' => 'system-settings-update-download'],
            ['action' => 'system-settings-update-install'],
        ];

        foreach ($actions as $payload) {
            $response = $this->post('/admin/dashboard', $payload);
            $response->assertOk();
            $response->assertJsonPath('status', 'false');
            $response->assertJsonPath('title', 'Request Failed');
        }
    }

    private function fallbackTrackerFilePath(): string
    {
        return storage_path('app/reports/admin-legacy-fallback.ndjson');
    }

    private function cleanupFallbackTrackerFile(): void
    {
        $filePath = $this->fallbackTrackerFilePath();
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
}
