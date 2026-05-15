<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

if (!function_exists('getCurrentDatetime')) {
    function getCurrentDatetime(string $format = 'Y-m-d H:i:s'): string
    {
        return now('UTC')->format($format);
    }
}

if (!function_exists('pp_parse_sql_segments')) {
    function pp_parse_sql_segments(string $clause): array
    {
        $result = [
            'where' => '',
            'group' => '',
            'order' => '',
            'limit' => '',
        ];

        $clause = trim($clause);
        if ($clause === '') {
            return $result;
        }

        $normalized = preg_replace('/\s+/', ' ', $clause);
        if ($normalized === null) {
            return $result;
        }

        $patterns = [
            'limit' => '/\bLIMIT\s+(.+?)(?=\s+ORDER\s+BY\b|\s+GROUP\s+BY\b|\s+WHERE\b|$)/i',
            'order' => '/\bORDER\s+BY\s+(.+?)(?=\s+GROUP\s+BY\b|\s+WHERE\b|\s+LIMIT\b|$)/i',
            'group' => '/\bGROUP\s+BY\s+(.+?)(?=\s+ORDER\s+BY\b|\s+WHERE\b|\s+LIMIT\b|$)/i',
            'where' => '/\bWHERE\s+(.+?)(?=\s+GROUP\s+BY\b|\s+ORDER\s+BY\b|\s+LIMIT\b|$)/i',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $normalized, $matches)) {
                $result[$key] = trim($matches[1]);
            }
        }

        return $result;
    }
}

if (!function_exists('getData')) {
    function getData(string $tableName, string $columnName, string $type = '* FROM', array $params = []): string
    {
        try {
            $select = trim(str_ireplace('FROM', '', $type));
            if ($select === '') {
                $select = '*';
            }

            $query = DB::table($tableName)->selectRaw($select);
            $segments = pp_parse_sql_segments($columnName);

            if ($segments['where'] !== '') {
                $query->whereRaw($segments['where'], $params);
            }

            if ($segments['group'] !== '') {
                $query->groupByRaw($segments['group']);
            }

            if ($segments['order'] !== '') {
                $query->orderByRaw($segments['order']);
            }

            if ($segments['limit'] !== '') {
                if (preg_match('/^(\d+)\s*,\s*(\d+)$/', $segments['limit'], $matches)) {
                    $query->offset((int) $matches[1])->limit((int) $matches[2]);
                } elseif (preg_match('/^(\d+)\s+OFFSET\s+(\d+)$/i', $segments['limit'], $matches)) {
                    $query->limit((int) $matches[1])->offset((int) $matches[2]);
                } elseif (is_numeric($segments['limit'])) {
                    $query->limit((int) $segments['limit']);
                }
            }

            $rows = $query->get()->map(static function ($item): array {
                $row = (array) $item;

                foreach ($row as $key => $value) {
                    if ($value === null) {
                        $row[$key] = '--';
                    }
                }

                return $row;
            })->all();

            return json_encode([
                'status' => !empty($rows),
                'response' => $rows,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: json_encode([
                'status' => false,
                'response' => [],
            ]);
        } catch (Throwable $exception) {
            return json_encode([
                'status' => false,
                'response' => [],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }
}

if (!function_exists('get_env')) {
    function get_env(string $optionName, string $brandId = 'both'): string
    {
        $row = DB::table('pp_env')
            ->where('brand_id', $brandId)
            ->where('option_name', $optionName)
            ->first();

        if (!$row) {
            DB::table('pp_env')->insert([
                'brand_id' => $brandId,
                'option_name' => $optionName,
                'value' => '--',
                'created_date' => getCurrentDatetime(),
                'updated_date' => getCurrentDatetime(),
            ]);

            return '';
        }

        return ((string) ($row->value ?? '')) === '--' ? '' : (string) $row->value;
    }
}

if (!function_exists('canAccessPage')) {
    function canAccessPage(array $permissions, string $page, string $adminType = 'staff'): bool
    {
        if ($adminType === 'admin') {
            return true;
        }

        return !empty($permissions['pages'][$page]);
    }
}

if (!function_exists('hasPermission')) {
    function hasPermission(array $permissions, string $module, string $action = 'view', string $adminType = 'staff'): bool
    {
        if ($adminType === 'admin') {
            return true;
        }

        return isset($permissions['resources'][$module][$action])
            && $permissions['resources'][$module][$action] === true;
    }
}

if (!function_exists('getNameChars')) {
    function getNameChars(string $fullName, int $length = 2): string
    {
        $fullName = trim($fullName);

        if ($fullName === '' || $length <= 0) {
            return '';
        }

        $parts = array_values(array_filter(explode(' ', $fullName)));

        if (count($parts) > 1) {
            $first = $parts[0];
            $last = end($parts);

            return strtoupper(substr($first, 0, 1) . substr((string) $last, 0, max(0, $length - 1)));
        }

        return strtoupper(substr($parts[0], 0, $length));
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo(string $datetime): string
    {
        $timezone = 'Asia/Dhaka';

        if (!empty($GLOBALS['global_response_brand']['response'][0]['timezone'])) {
            $brandTimezone = (string) $GLOBALS['global_response_brand']['response'][0]['timezone'];

            if ($brandTimezone !== '--') {
                $timezone = $brandTimezone;
            }
        }

        $tz = new DateTimeZone($timezone);
        $past = new DateTime($datetime, new DateTimeZone('UTC'));
        $past->setTimezone($tz);
        $now = new DateTime('now', $tz);
        $diff = $now->diff($past);

        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        }

        if ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        }

        if ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        }

        if ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        }

        if ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        }

        return 'Just now';
    }
}

if (!function_exists('convertUTCtoUserTZ')) {
    function convertUTCtoUserTZ(string $datetime, string $timezone, string $format = 'M d, Y h:i A'): string
    {
        $targetTimezone = $timezone !== '' && $timezone !== '--' ? $timezone : 'Asia/Dhaka';
        $dateTime = new DateTime($datetime, new DateTimeZone('UTC'));
        $dateTime->setTimezone(new DateTimeZone($targetTimezone));

        return $dateTime->format($format);
    }
}

if (!function_exists('getParam')) {
    function getParam(array $params, string $key): ?string {
        if (!isset($params[$key]) || !is_string($params[$key])) {
            return null;
        }

        $value = trim($params[$key]);
        if ($value === '') {
            return null;
        }

        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $value)) {
            return null;
        }

        return $value;
    }
}

if (!function_exists('senderWhitelist')) {
    function senderWhitelist(?string $sender = null, ?string $providerKey = null, string $mode = 'provider', ?string $providerName = null) {
        $providers = [
            'bkash' => [
                'name'     => 'bKash',
                'currency' => 'BDT',
                'balance_verify' => 'true',
                'senders'  => ['bkash'],
            ],
            'nagad' => [
                'name'     => 'Nagad',
                'currency' => 'BDT',
                'balance_verify' => 'true',
                'senders'  => ['nagad'],
            ],
            'rocket' => [
                'name'     => 'Rocket',
                'currency' => 'BDT',
                'balance_verify' => 'true',
                'senders'  => ['16216'],
            ],
            'upay' => [
                'name'     => 'Upay',
                'currency' => 'BDT',
                'balance_verify' => 'true',
                'senders'  => ['upay'],
            ],
            'tap' => [
                'name'     => 'Tap',
                'currency' => 'USD',
                'balance_verify' => 'true',
                'senders'  => ['tap.'],
            ],
            'cellfin' => [
                'name'     => 'Cellfin',
                'currency' => 'BDT',
                'balance_verify' => 'false',
                'senders'  => ['ibbl .'],
            ],
            'okwallet' => [
                'name'     => 'Ok Wallet',
                'currency' => 'BDT',
                'balance_verify' => 'true',
                'senders'  => ['01847-348685'],
            ],
            'mcash' => [
                'name'     => 'mCash',
                'currency' => 'BDT',
                'balance_verify' => 'true',
                'senders'  => ['16259'],
            ],
            'pathaopay' => [
                'name'     => 'Pathao Pay',
                'currency' => 'BDT',
                'balance_verify' => 'true',
                'senders'  => ['pathaopay'],
            ],
            'telecash' => [
                'name'     => 'TeleCash',
                'currency' => 'BDT',
                'balance_verify' => 'true',
                'senders'  => ['telecash'],
            ],
            'ipay' => [
                'name'     => 'Ipay',
                'currency' => 'BDT',
                'balance_verify' => 'true',
                'senders'  => ['09638-900800'],
            ],
        ];

        if ($mode === 'senders') {
            $allSenders = [];
            foreach ($providers as $provider) {
                $allSenders = array_merge($allSenders, $provider['senders']);
            }
            $allSenders = array_values(array_unique($allSenders));
            return $allSenders;
        }

        if ($sender !== null) {
            $sender = strtolower(trim($sender));
            foreach ($providers as $key => $provider) {
                foreach ($provider['senders'] as $s) {
                    if (strtolower($s) === $sender) {
                        return [
                            'provider_key'   => $key,
                            'name'           => $provider['name'],
                            'currency'       => $provider['currency'],
                            'balance_verify'       => $provider['balance_verify'],
                            'sender'         => $sender,
                        ];
                    }
                }
            }
            return false;
        }

        if ($providerKey !== null) {
            return $providers[$providerKey] ?? false;
        }

        if ($providerName !== null) {
            $providerName = strtolower(trim($providerName));
            foreach ($providers as $key => $provider) {
                if (strtolower($provider['name']) === $providerName) {
                    return [
                        'provider_key' => $key,
                        'name'         => $provider['name'],
                        'currency'     => $provider['currency'],
                        'balance_verify'     => $provider['balance_verify'],
                        'senders'      => $provider['senders'],
                    ];
                }
            }
            return false;
        }

        return $providers;
    }
}

if (!function_exists('permissionSchema')) {
    function permissionSchema(){
        $permissionSchema = [
            'resources' => [
                'customers' => [
                    'create' => true,
                    'edit'   => true,
                    'delete' => true
                ],
                'transaction' => [
                    'edit'      => true,
                    'delete'    => true,
                    'approve'   => true,
                    'cancel'   => true,
                    'refund'    => true,
                    'send_ipn'  => true
                ],
                'invoice' => [
                    'create'    => true,
                    'edit'      => true,
                    'delete'    => true
                ],
                'payment_link' => [
                    'create' => true,
                    'edit'   => true,
                    'delete' => true
                ],
                'gateways' => [
                    'create' => true,
                    'edit'   => true,
                    'delete' => true
                ],
                'addons' => [
                    'create' => true,
                    'edit'   => true,
                    'delete' => true
                ],
                'brand_settings' => [
                    'view' => true,
                    'edit'   => true
                ],
                'api_settings' => [
                    'view' => true,
                    'create' => true,
                    'edit'   => true,
                    'delete' => true
                ],
                'theme_settings' => [
                    'view' => true,
                    'edit'   => true
                ],
                'faq_settings' => [
                    'view' => true,
                    'create' => true,
                    'edit'   => true,
                    'delete' => true
                ],
                'currency_settings' => [
                    'view' => true,
                    'sync_rate' => true,
                    'import'   => true,
                    'edit'   => true
                ],
                'sms_data' => [
                    'create' => true,
                    'edit'   => true,
                    'delete' => true
                ],
                'device' => [
                    'connect' => true,
                    'delete'  => true,
                    'balance_verification_for'  => true
                ],
                'brands' => [
                    'create' => true,
                    'edit'   => true,
                    'delete' => true
                ],
                'staff' => [
                    'create' => true,
                    'edit'   => true,
                    'delete' => true,
                    'assign_brand_to' => true,
                    'edit_permission' => true,
                    'view_permission_list' => true,
                    'delete_permission_of' => true
                ],
                'domains' => [
                    'whitelist' => true,
                    'edit'   => true,
                    'delete' => true
                ],
                'system_settings' => [
                    'manage_general' => true,
                    'manage_cron' => true,
                    'manage_update'   => true,
                    'manage_import'   => true
                ],
            ],
            'pages' => [
                'dashboard' => true,
                'reports' => true,
                'customers' => true,
                'transaction' => true,
                'invoice' => true,
                'payment_link' => true,
                'gateways' => true,
                'addons' => true,
                'brand_settings' => true,
                'sms_data' => true,
                'device' => true,
                'brands' => true,
                'staff_management' => true,
                'domains' => true,
                'system_settings' => true,
            ]
        ];

        return $permissionSchema ?? [];
    }
}

if (!function_exists('countPermissions')) {
    function countPermissions($tabKey, $tabData) {
        $count = 0;

        if ($tabKey === 'resources') {
            foreach ($tabData as $module => $actions) {
                $count += count($actions);
            }
        }

        if ($tabKey === 'pages') {
            $count = count($tabData);
        }

        return $count;
    }
}

if (!function_exists('pp_theme_asset')) {
    function pp_theme_asset(string $path, ?string $themeSlug = null): string
    {
        $theme = $themeSlug;
        if (!$theme && isset($GLOBALS['global_response_brand']['response'][0]['theme'])) {
            $theme = $GLOBALS['global_response_brand']['response'][0]['theme'];
        }

        if (!$theme) {
            return url('assets/' . ltrim($path, '/'));
        }

        return route('module.asset', [
            'type' => 'theme',
            'module' => $theme,
            'path' => ltrim($path, '/')
        ]);
    }
}
if (!function_exists('m_view')) {
    /**
     * Get the theme-based view for the merchant dashboard.
     * Falls back to 'default' theme if the view doesn't exist in the current theme.
     */
    function m_view(string $view, array $data = []): \Illuminate\Contracts\View\View
    {
        $theme = config('piprapay.merchant_theme', 'default');

        $viewPath = "merchant.{$theme}.pages.{$view}";

        if (!view()->exists($viewPath)) {
            $viewPath = "merchant.default.pages.{$view}";
        }

        return view($viewPath, $data);
    }
}

if (!function_exists('m_asset')) {
    /**
     * Get the theme-based asset URL.
     */
    function m_asset(string $path, ?string $theme = null): string
    {
        $theme = $theme ?: config('piprapay.merchant_theme', 'default');
        return route('module.asset.v2', [
            'type' => 'themes',
            'module' => $theme,
            'path' => ltrim($path, '/')
        ]);
    }
}

if (!function_exists('m_layout')) {
    /**
     * Get the theme-based layout path.
     */
    function m_layout(string $layout): string
    {
        $theme = config('piprapay.merchant_theme', 'default');
        $layoutPath = "merchant.{$theme}.layouts.{$layout}";

        if (!view()->exists($layoutPath)) {
            $layoutPath = "merchant.default.layouts.{$layout}";
        }

        return $layoutPath;
    }
}

