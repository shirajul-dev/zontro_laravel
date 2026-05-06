<?php

declare(strict_types=1);

if ($argc < 2) {
    fwrite(STDERR, "Usage: php scripts/ci/enforce_readiness_gate.php <report-json-path>\n");
    exit(2);
}

$reportPath = $argv[1];
if (!is_file($reportPath)) {
    fwrite(STDERR, "Readiness report not found: {$reportPath}\n");
    exit(2);
}

$raw = (string) file_get_contents($reportPath);
$report = json_decode($raw, true);

if (!is_array($report)) {
    fwrite(STDERR, "Invalid readiness JSON payload.\n");
    exit(2);
}

$count = (int) ($report['summary']['admin_unknown_action_fallback_14d_count'] ?? -1);
$checks = $report['checks'] ?? [];
$checkPass = null;

if (is_array($checks)) {
    foreach ($checks as $check) {
        if (!is_array($check)) {
            continue;
        }

        if (($check['id'] ?? '') === 'admin_flow.unknown_action_fallback_zero_14d') {
            $checkPass = (bool) ($check['pass'] ?? false);
            break;
        }
    }
}

if ($count < 0) {
    fwrite(STDERR, "Missing summary.admin_unknown_action_fallback_14d_count in readiness report.\n");
    exit(2);
}

if ($checkPass === null) {
    fwrite(STDERR, "Missing check admin_flow.unknown_action_fallback_zero_14d in readiness report.\n");
    exit(2);
}

fwrite(STDOUT, "admin_unknown_action_fallback_14d_count={$count}\n");
fwrite(STDOUT, "admin_flow.unknown_action_fallback_zero_14d=" . ($checkPass ? 'true' : 'false') . "\n");

if ($count !== 0 || $checkPass !== true) {
    fwrite(STDERR, "Migration readiness gate failed: unknown admin-action legacy fallback is not zero for the last 14 days.\n");
    exit(1);
}

fwrite(STDOUT, "Migration readiness gate passed.\n");
exit(0);
