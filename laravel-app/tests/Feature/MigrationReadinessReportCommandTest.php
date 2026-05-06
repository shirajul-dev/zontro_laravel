<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MigrationReadinessReportCommandTest extends TestCase
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

    public function test_migration_readiness_report_outputs_expected_json_shape(): void
    {
        config([
            'piprapay.migration.native_api_checkout_enabled' => false,
            'piprapay.migration.native_api_verify_payment_enabled' => false,
            'piprapay.migration.native_invoice_webhook_enabled' => false,
            'piprapay.migration.native_admin_actions_enabled' => true,
        ]);

        $exitCode = Artisan::call('piprapay:migration-readiness-report', [
            '--format' => 'json',
        ]);

        $this->assertSame(0, $exitCode);

        $payload = json_decode(Artisan::output(), true);

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('summary', $payload);
        $this->assertArrayHasKey('checks', $payload);
        $this->assertArrayHasKey('routes', $payload);
        $this->assertArrayHasKey('admin_legacy_fallback_metrics', $payload);
        $this->assertArrayHasKey('admin_unknown_action_fallback_14d_count', $payload['summary']);
        $this->assertSame(0, (int) $payload['summary']['admin_unknown_action_fallback_14d_count']);
        $this->assertFalse((bool) ($payload['deletion_ready'] ?? true));

        $routesByName = [];
        foreach ($payload['routes'] as $route) {
            $routesByName[(string) $route['name']] = $route;
        }

        $this->assertSame('legacy', (string) ($routesByName['api.handle']['mode'] ?? ''));
        $this->assertSame('legacy', (string) ($routesByName['payment.ipn']['mode'] ?? ''));
        $this->assertSame('legacy', (string) ($routesByName['invoice.webhook']['mode'] ?? ''));
    }

    public function test_migration_readiness_report_marks_toggle_gated_routes_when_enabled(): void
    {
        config([
            'piprapay.migration.native_api_checkout_enabled' => true,
            'piprapay.migration.native_api_verify_payment_enabled' => true,
            'piprapay.migration.native_invoice_webhook_enabled' => true,
            'piprapay.migration.native_admin_actions_enabled' => true,
        ]);

        $exitCode = Artisan::call('piprapay:migration-readiness-report', [
            '--format' => 'json',
        ]);

        $this->assertSame(0, $exitCode);

        $payload = json_decode(Artisan::output(), true);
        $this->assertIsArray($payload);

        $routesByName = [];
        foreach ($payload['routes'] as $route) {
            $routesByName[(string) $route['name']] = $route;
        }

        $this->assertSame('hybrid-toggle', (string) ($routesByName['api.handle']['mode'] ?? ''));
        $this->assertSame('hybrid-toggle', (string) ($routesByName['invoice.webhook']['mode'] ?? ''));
        $this->assertTrue((bool) ($payload['summary']['toggle_gated_routes'] > 0));
    }

    public function test_readiness_report_exposes_unknown_action_fallback_14_day_metric(): void
    {
        $this->writeFallbackTrackerRows([
            [
                'occurred_at_utc' => now('UTC')->subDays(2)->toIso8601String(),
                'reason' => 'unknown_action',
                'action' => 'legacy-only-action',
                'page_name' => 'dashboard',
                'method' => 'POST',
                'path' => 'admin/dashboard',
            ],
            [
                'occurred_at_utc' => now('UTC')->subDays(20)->toIso8601String(),
                'reason' => 'unknown_action',
                'action' => 'old-action',
                'page_name' => 'dashboard',
                'method' => 'POST',
                'path' => 'admin/dashboard',
            ],
        ]);

        $exitCode = Artisan::call('piprapay:migration-readiness-report', [
            '--format' => 'json',
        ]);

        $this->assertSame(0, $exitCode);

        $payload = json_decode(Artisan::output(), true);
        $this->assertIsArray($payload);
        $this->assertSame(1, (int) ($payload['summary']['admin_unknown_action_fallback_14d_count'] ?? -1));

        $checksById = [];
        foreach (($payload['checks'] ?? []) as $check) {
            $checksById[(string) ($check['id'] ?? '')] = $check;
        }

        $this->assertFalse((bool) ($checksById['admin_flow.unknown_action_fallback_zero_14d']['pass'] ?? true));
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

    private function writeFallbackTrackerRows(array $rows): void
    {
        $dir = dirname($this->fallbackTrackerFilePath());
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $lines = [];
        foreach ($rows as $row) {
            $line = json_encode($row, JSON_UNESCAPED_SLASHES);
            if ($line !== false) {
                $lines[] = $line;
            }
        }

        file_put_contents($this->fallbackTrackerFilePath(), implode(PHP_EOL, $lines) . PHP_EOL);
    }
}
