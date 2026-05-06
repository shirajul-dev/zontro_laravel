<?php

namespace App\Services\Migration;

class AdminLegacyFallbackTracker
{
    public function record(string $reason, string $action, ?string $pageName, string $method, string $path): void
    {
        $dir = storage_path('app/reports');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $payload = [
            'occurred_at_utc' => now('UTC')->toIso8601String(),
            'reason' => $reason,
            'action' => $action,
            'page_name' => (string) ($pageName ?? ''),
            'method' => strtoupper($method),
            'path' => $path,
        ];

        $line = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($line === false) {
            return;
        }

        file_put_contents($this->filePath(), $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function stats(int $windowDays = 14): array
    {
        $path = $this->filePath();
        if (!is_file($path)) {
            return [
                'window_days' => $windowDays,
                'total_events' => 0,
                'unknown_action_total' => 0,
                'unknown_action_last_window' => 0,
            ];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return [
                'window_days' => $windowDays,
                'total_events' => 0,
                'unknown_action_total' => 0,
                'unknown_action_last_window' => 0,
            ];
        }

        $cutoff = now('UTC')->subDays($windowDays);

        $totalEvents = 0;
        $unknownTotal = 0;
        $unknownLastWindow = 0;

        foreach ($lines as $line) {
            $row = json_decode((string) $line, true);
            if (!is_array($row)) {
                continue;
            }

            $totalEvents++;

            $reason = (string) ($row['reason'] ?? '');
            if ($reason !== 'unknown_action') {
                continue;
            }

            $unknownTotal++;

            $occurredAt = (string) ($row['occurred_at_utc'] ?? '');
            $timestamp = strtotime($occurredAt);
            if ($timestamp === false) {
                continue;
            }

            if ($timestamp >= $cutoff->getTimestamp()) {
                $unknownLastWindow++;
            }
        }

        return [
            'window_days' => $windowDays,
            'total_events' => $totalEvents,
            'unknown_action_total' => $unknownTotal,
            'unknown_action_last_window' => $unknownLastWindow,
        ];
    }

    public function filePath(): string
    {
        return storage_path('app/reports/admin-legacy-fallback.ndjson');
    }
}
