<?php

use App\Services\Migration\MigrationReadinessReportService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('piprapay:cron')->everyFiveMinutes();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('piprapay:migration-readiness-report {--format=table} {--write}', function (MigrationReadinessReportService $service) {
    $format = strtolower((string) $this->option('format'));
    if (!in_array($format, ['table', 'json'], true)) {
        $this->error('Invalid --format value. Supported: table, json');
        return self::FAILURE;
    }

    $report = $service->generate();

    if ($format === 'json') {
        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    } else {
        $rows = array_map(static function (array $row): array {
            return [
                $row['name'],
                $row['uri'],
                implode(',', $row['methods']),
                $row['mode'],
                $row['legacy_dependency'] ? 'yes' : 'no',
                $row['notes'],
            ];
        }, $report['routes']);

        $this->table(
            ['name', 'uri', 'methods', 'mode', 'legacy_dependency', 'notes'],
            $rows
        );

        $this->newLine();
        $this->line('Generated (UTC): ' . $report['generated_at_utc']);
        $this->line('In-scope routes: ' . $report['summary']['in_scope_routes']);
        $this->line('Fully native routes: ' . $report['summary']['fully_native_routes']);
        $this->line('Toggle-gated routes: ' . $report['summary']['toggle_gated_routes']);
        $this->line('Legacy-bound routes: ' . $report['summary']['legacy_bound_routes']);
        $this->line('Deletion ready: ' . ($report['deletion_ready'] ? 'YES' : 'NO'));
    }

    if ((bool) $this->option('write')) {
        $timestamp = now('UTC')->format('Ymd_His');
        $dir = storage_path('app/reports');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir . '/migration-readiness-report-' . $timestamp . '.md';
        $md = "# Migration Readiness Report\n\n";
        $md .= 'Generated (UTC): ' . $report['generated_at_utc'] . "\n\n";
        $md .= 'Deletion Ready: ' . ($report['deletion_ready'] ? 'YES' : 'NO') . "\n\n";

        $md .= "## Summary\n\n";
        foreach ($report['summary'] as $key => $value) {
            $md .= '- ' . $key . ': ' . $value . "\n";
        }

        $md .= "\n## Checks\n\n";
        foreach ($report['checks'] as $check) {
            $md .= '- [' . ($check['pass'] ? 'x' : ' ') . '] ' . $check['id'] . ' - ' . $check['detail'] . "\n";
        }

        $md .= "\n## Routes\n\n";
        $md .= "| name | uri | methods | mode | legacy_dependency | notes |\n";
        $md .= "| --- | --- | --- | --- | --- | --- |\n";
        foreach ($report['routes'] as $row) {
            $md .= '| ' . $row['name']
                . ' | ' . $row['uri']
                . ' | ' . implode(',', $row['methods'])
                . ' | ' . $row['mode']
                . ' | ' . ($row['legacy_dependency'] ? 'yes' : 'no')
                . ' | ' . str_replace('|', '\\|', $row['notes'])
                . " |\n";
        }

        file_put_contents($path, $md);
        $this->line('Report written: ' . $path);
    }

    return self::SUCCESS;
})->purpose('Generate route-by-route native/legacy migration readiness report.');
