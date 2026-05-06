<?php

namespace App\Services\Migration;

use Illuminate\Support\Facades\Route;

class MigrationReadinessReportService
{
    public function __construct(private readonly AdminLegacyFallbackTracker $adminLegacyFallbackTracker)
    {
    }

    /**
     * Build a route-by-route readiness report for native migration cutover.
     */
    public function generate(): array
    {
        $paths = [
            'payment' => trim((string) config('piprapay.paths.payment', 'payment'), '/'),
            'invoice' => trim((string) config('piprapay.paths.invoice', 'invoice'), '/'),
            'payment_link' => trim((string) config('piprapay.paths.payment_link', 'payment-link'), '/'),
            'admin' => trim((string) config('piprapay.paths.admin', 'admin'), '/'),
            'cron' => trim((string) config('piprapay.paths.cron', 'cron'), '/'),
        ];

        $toggles = [
            'native_api_checkout_enabled' => (bool) config('piprapay.migration.native_api_checkout_enabled', false),
            'native_api_verify_payment_enabled' => (bool) config('piprapay.migration.native_api_verify_payment_enabled', false),
            'native_invoice_webhook_enabled' => (bool) config('piprapay.migration.native_invoice_webhook_enabled', false),
            'native_admin_actions_enabled' => (bool) config('piprapay.migration.native_admin_actions_enabled', true),
        ];

        $routeRows = [];
        foreach (Route::getRoutes() as $route) {
            $uri = ltrim((string) $route->uri(), '/');

            if (!$this->isInScopeRoute($uri, $paths)) {
                continue;
            }

            $methods = array_values(array_filter(
                $route->methods(),
                static fn (string $method): bool => strtoupper($method) !== 'HEAD'
            ));

            $classification = $this->classifyRoute(
                (string) ($route->getName() ?? ''),
                $uri,
                $toggles,
                $paths
            );

            $routeRows[] = [
                'name' => (string) ($route->getName() ?? '(unnamed)'),
                'uri' => '/' . $uri,
                'methods' => $methods,
                'action' => (string) $route->getActionName(),
                'mode' => $classification['mode'],
                'legacy_dependency' => $classification['legacy_dependency'],
                'notes' => $classification['notes'],
            ];
        }

        usort($routeRows, static fn (array $a, array $b): int => strcmp($a['uri'], $b['uri']));

        $summary = [
            'in_scope_routes' => count($routeRows),
            'fully_native_routes' => count(array_filter($routeRows, static fn (array $row): bool => $row['mode'] === 'native')),
            'toggle_gated_routes' => count(array_filter($routeRows, static fn (array $row): bool => $row['mode'] === 'hybrid-toggle')),
            'legacy_bound_routes' => count(array_filter($routeRows, static fn (array $row): bool => $row['legacy_dependency'] === true)),
        ];

        $adminFallbackMetrics = $this->adminLegacyFallbackTracker->stats(14);
        $summary['admin_unknown_action_fallback_14d_count'] = (int) $adminFallbackMetrics['unknown_action_last_window'];

        $checks = $this->buildChecks($toggles, $routeRows, $paths, $adminFallbackMetrics);

        return [
            'generated_at_utc' => now('UTC')->toIso8601String(),
            'paths' => $paths,
            'toggles' => $toggles,
            'summary' => $summary,
            'admin_legacy_fallback_metrics' => $adminFallbackMetrics,
            'checks' => $checks,
            'routes' => $routeRows,
            'deletion_ready' => !in_array(false, array_column($checks, 'pass'), true),
        ];
    }

    private function isInScopeRoute(string $uri, array $paths): bool
    {
        if ($uri === '' || $uri === '404') {
            return true;
        }

        return str_starts_with($uri, 'api/')
            || str_starts_with($uri, 'ipn/')
            || str_starts_with($uri, $paths['invoice'] . '/')
            || str_starts_with($uri, $paths['payment'] . '/')
            || str_starts_with($uri, $paths['payment_link'] . '/')
            || str_starts_with($uri, $paths['admin'])
            || str_starts_with($uri, $paths['cron'] . '/');
    }

    private function classifyRoute(string $name, string $uri, array $toggles, array $paths): array
    {
        if (str_starts_with($uri, 'api/')) {
            $enabled = $toggles['native_api_checkout_enabled'] || $toggles['native_api_verify_payment_enabled'];

            if ($enabled) {
                return [
                    'mode' => 'hybrid-toggle',
                    'legacy_dependency' => true,
                    'notes' => 'Some API slices are native, unresolved slices still dispatch legacy runtime.',
                ];
            }

            return [
                'mode' => 'legacy',
                'legacy_dependency' => true,
                'notes' => 'API route currently handled by legacy dispatch path.',
            ];
        }

        if (str_starts_with($uri, 'ipn/')) {
            return [
                'mode' => 'legacy',
                'legacy_dependency' => true,
                'notes' => 'IPN controller delegates directly to legacy runtime.',
            ];
        }

        if ($uri === $paths['invoice'] . '/webhook') {
            if ($toggles['native_invoice_webhook_enabled']) {
                return [
                    'mode' => 'hybrid-toggle',
                    'legacy_dependency' => true,
                    'notes' => 'Native webhook enabled, but fallback legacy branch still present.',
                ];
            }

            return [
                'mode' => 'legacy',
                'legacy_dependency' => true,
                'notes' => 'Webhook uses legacy runtime while toggle is disabled.',
            ];
        }

        if ($uri === '' || $uri === '404') {
            return [
                'mode' => 'legacy',
                'legacy_dependency' => true,
                'notes' => 'Root/fallback route still routes through legacy runtime bridge.',
            ];
        }

        if (str_starts_with($uri, $paths['cron'] . '/')) {
            return [
                'mode' => 'legacy',
                'legacy_dependency' => true,
                'notes' => 'Cron route currently delegates to legacy runtime.',
            ];
        }

        if (str_starts_with($uri, $paths['admin']) && str_contains($name, '.post')) {
            if ($toggles['native_admin_actions_enabled']) {
                return [
                    'mode' => 'hybrid-toggle',
                    'legacy_dependency' => true,
                    'notes' => 'Known actions are native, unknown actions still fall back to legacy.',
                ];
            }

            return [
                'mode' => 'legacy',
                'legacy_dependency' => true,
                'notes' => 'Admin POST flow fully delegates to legacy runtime when toggle is disabled.',
            ];
        }

        if (str_starts_with($uri, $paths['payment'] . '/') || str_starts_with($uri, $paths['payment_link'] . '/') || str_starts_with($uri, $paths['invoice'] . '/')) {
            return [
                'mode' => 'native-controller-legacy-theme',
                'legacy_dependency' => true,
                'notes' => 'Route uses Laravel controller but still depends on legacy theme/helpers.',
            ];
        }

        return [
            'mode' => 'hybrid-toggle',
            'legacy_dependency' => true,
            'notes' => 'Hybrid route with unresolved legacy dependencies.',
        ];
    }

    private function buildChecks(array $toggles, array $routeRows, array $paths, array $adminFallbackMetrics): array
    {
        $hasLegacyApi = $this->hasLegacyDependencyForPrefix($routeRows, '/api/');
        $hasLegacyIpn = $this->hasLegacyDependencyForPrefix($routeRows, '/ipn/');
        $hasLegacyCron = $this->hasLegacyDependencyForPrefix($routeRows, '/' . $paths['cron'] . '/');
        $hasLegacyPayment = $this->hasLegacyDependencyForPrefix($routeRows, '/' . $paths['payment'] . '/');
        $hasLegacyPaymentLink = $this->hasLegacyDependencyForPrefix($routeRows, '/' . $paths['payment_link'] . '/');
        $unknownActionFallback14d = (int) ($adminFallbackMetrics['unknown_action_last_window'] ?? 0);

        return [
            [
                'id' => 'route_dependency.api_fully_native',
                'pass' => !$hasLegacyApi,
                'detail' => $hasLegacyApi ? 'At least one /api route still reports legacy dependency.' : 'All /api routes are native.',
            ],
            [
                'id' => 'route_dependency.ipn_fully_native',
                'pass' => !$hasLegacyIpn,
                'detail' => $hasLegacyIpn ? 'At least one /ipn route still reports legacy dependency.' : 'All /ipn routes are native.',
            ],
            [
                'id' => 'route_dependency.invoice_webhook_native_toggle_on',
                'pass' => $toggles['native_invoice_webhook_enabled'],
                'detail' => $toggles['native_invoice_webhook_enabled'] ? 'Native invoice webhook toggle is enabled.' : 'Enable native invoice webhook toggle for burn-in.',
            ],
            [
                'id' => 'route_dependency.cron_fully_native',
                'pass' => !$hasLegacyCron,
                'detail' => $hasLegacyCron ? 'At least one /cron route still reports legacy dependency.' : 'All /cron routes are native.',
            ],
            [
                'id' => 'public_flow.payment_checkout_native_stack',
                'pass' => !$hasLegacyPayment && !$hasLegacyPaymentLink,
                'detail' => (!$hasLegacyPayment && !$hasLegacyPaymentLink)
                    ? 'Payment and payment-link routes are native.'
                    : 'Payment/payment-link still rely on legacy theme/helper stack.',
            ],
            [
                'id' => 'admin_flow.native_actions_toggle_on',
                'pass' => $toggles['native_admin_actions_enabled'],
                'detail' => $toggles['native_admin_actions_enabled']
                    ? 'Native admin actions toggle is enabled (fallback still possible).'
                    : 'Native admin actions toggle is disabled.',
            ],
            [
                'id' => 'admin_flow.unknown_action_fallback_zero_14d',
                'pass' => $unknownActionFallback14d === 0,
                'detail' => $unknownActionFallback14d === 0
                    ? 'Unknown admin-action fallback count is zero for the last 14 days.'
                    : 'Unknown admin-action fallback count in last 14 days: ' . $unknownActionFallback14d,
            ],
        ];
    }

    private function hasLegacyDependencyForPrefix(array $routeRows, string $prefix): bool
    {
        foreach ($routeRows as $row) {
            if (str_starts_with((string) $row['uri'], $prefix) && $row['legacy_dependency'] === true) {
                return true;
            }
        }

        return false;
    }
}
