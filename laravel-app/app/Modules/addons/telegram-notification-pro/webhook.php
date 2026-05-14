<?php
/**
 * Telegram Webhook + Hub/Node endpoint
 *
 * IMPORTANT: The server's .htaccess blocks direct .php access.
 * This file can only be reached if the server allows it via a specific
 * exception, OR if the site owner adds a rule. The webhook URL must be
 * set to a path that bypasses the block.
 *
 * Alternative: Use the index.php route by adding ?tgnp_webhook=1 to
 * the site URL and catching it early in the adapter. That approach
 * requires modifying pp-adapter.php which we cannot do.
 *
 * For now this file handles requests when direct PHP access IS allowed
 * (some server configs allow specific subdirectories).
 */

// Bootstrap Laravel if called directly (not via route)
if (!function_exists('app')) {
    $root = __DIR__;
    for ($i = 0; $i < 10; $i++) {
        if (file_exists($root . '/bootstrap/app.php')) break;
        $root = dirname($root);
    }

    if (file_exists($root . '/bootstrap/app.php')) {
        require $root . '/vendor/autoload.php';
        $app = require_once $root . '/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    }
}

// Define PipraPay_INIT to allow including zp-functions.php
if (!defined('PipraPay_INIT')) {
    define('PipraPay_INIT', true);
}

// Ensure zp-functions.php is loaded
if (function_exists('app')) {
    $functionsPath = app_path('Support/zp-functions.php');
} else {
    $functionsPath = dirname(dirname(dirname(dirname(__DIR__)))) . '/app/Support/zp-functions.php';
}

if (file_exists($functionsPath)) {
    require_once $functionsPath;
}

if (!class_exists('TelegramNotificationProAddon')) {
    require __DIR__ . '/class.php';
}

global $db_prefix;
$addonRow = json_decode(getData($db_prefix . 'addon', 'WHERE slug = "telegram-notification-pro"'), true);
$addon_id = $addonRow['response'][0]['addon_id'] ?? '';

$options = [];
if (!empty($addon_id)) {
    $optRows = json_decode(getData($db_prefix . 'addon_parameter', 'WHERE addon_id = "' . $addon_id . '"'), true);
    foreach ($optRows['response'] ?? [] as $row) {
        $v = $row['value'];
        if ($v !== '--' && in_array($v[0] ?? '', ['{', '['])) {
            $dec = json_decode($v, true);
            if ($dec !== null) $v = $dec;
        }
        $options[$row['option_name']] = $v;
    }
}

$addon = new TelegramNotificationProAddon($options);
$bot_token = $options['bot_token'] ?? '';

// ── Hub/Node inter-site request ──────────────────────────────────────
if (isset($_GET['tgnp_hub']) || isset($_POST['tgnp_hub_request'])) {
    header('Content-Type: application/json');
    $my_secret    = $options['api_secret'] ?? '';
    $payload_json = $_POST['payload']   ?? '';
    $signature    = $_POST['signature'] ?? '';
    $payload      = json_decode($payload_json, true);

    if (empty($my_secret) || !$payload) {
        echo json_encode(['status' => false, 'message' => 'Server not configured.']);
        exit();
    }

    ksort($payload);
    if (!hash_equals(hash_hmac('sha256', json_encode($payload), $my_secret), $signature)) {
        echo json_encode(['status' => false, 'message' => 'Invalid signature.']);
        exit();
    }

    $action = $payload['action'] ?? '';

    if ($action === 'register_node') {
        $nodes    = is_array($options['connected_nodes_json'] ?? [])
            ? ($options['connected_nodes_json'] ?? [])
            : (json_decode($options['connected_nodes_json'] ?? '[]', true) ?: []);
        $new_node = [
            'site_name'       => $payload['site_name'],
            'site_url'        => $payload['site_url'],
            'site_identifier' => $payload['site_identifier'],
            'connected_at'    => date('Y-m-d H:i:s'),
        ];
        $found = false;
        foreach ($nodes as &$n) {
            if ($n['site_identifier'] === $new_node['site_identifier']) { $n = $new_node; $found = true; break; }
        }
        if (!$found) $nodes[] = $new_node;

        $existing = json_decode(getData($db_prefix . 'addon_parameter',
            'WHERE addon_id = "' . $addon_id . '" AND option_name = "connected_nodes_json"'), true);
        $value = json_encode($nodes);
        if (!empty($existing['response'][0]['id'])) {
            updateData($db_prefix . 'addon_parameter', ['value', 'updated_date'],
                [$value, getCurrentDatetime('Y-m-d H:i:s')], 'id = "' . $existing['response'][0]['id'] . '"');
        } else {
            insertData($db_prefix . 'addon_parameter',
                ['addon_id', 'option_name', 'value', 'created_date', 'updated_date'],
                [$addon_id, 'connected_nodes_json', $value, getCurrentDatetime('Y-m-d H:i:s'), getCurrentDatetime('Y-m-d H:i:s')]);
        }
        echo json_encode(['status' => true, 'message' => 'Node registered.']);
        exit();
    }

    echo json_encode(['status' => false, 'message' => 'Unknown action.']);
    exit();
}

// ── Telegram Webhook Update ──────────────────────────────────────────
$raw    = file_get_contents('php://input');
$update = json_decode($raw, true);
if (!$update || empty($bot_token)) exit();

// Callback query (inline button)
if (isset($update['callback_query'])) {
    $cq      = $update['callback_query'];
    $chat_id = $cq['message']['chat']['id'];
    $data    = $cq['data'] ?? '';

    if (str_starts_with($data, 'cp|')) {
        [, $site_id, $pp_id] = explode('|', $data, 3);
        // Approve transaction
        updateData($db_prefix . 'transaction', ['status', 'updated_date'],
            ['completed', getCurrentDatetime('Y-m-d H:i:s')], 'ref = "' . escape_string($pp_id) . '"');
        $addon->callTelegram($bot_token, 'sendMessage', [
            'chat_id' => $chat_id, 'text' => "Transaction `{$pp_id}` approved.", 'parse_mode' => 'Markdown',
        ]);
        $addon->callTelegram($bot_token, 'answerCallbackQuery', ['callback_query_id' => $cq['id'], 'text' => 'Approved!']);
    }
    exit();
}

if (!isset($update['message']['text'])) exit();

$chat_id = $update['message']['chat']['id'];
$text    = trim($update['message']['text']);
$command = strtolower(explode('@', explode(' ', $text)[0])[0]);

$reply = tgnpHandleCommand($command, $chat_id, $options, $db_prefix);
$addon->callTelegram($bot_token, 'sendMessage', ['chat_id' => $chat_id, 'text' => $reply, 'parse_mode' => 'Markdown']);
exit();

function tgnpHandleCommand(string $cmd, string $chat_id, array $options, string $db_prefix): string
{
    switch ($cmd) {
        case '/start':
            return "*Welcome to Telegram Bot Notification Pro!*\n\nYour *Chat ID* is: `{$chat_id}`\n\nAdd this ID in the addon settings to receive notifications.\nType /help for all commands.";

        case '/help':
            return "*Available Commands*\n\n"
                . "/start — Your Chat ID\n"
                . "/last_transaction — Most recent transaction\n"
                . "/sales_today — Today's sales\n"
                . "/sales_yesterday — Yesterday's sales\n"
                . "/sales_this_month — This month's sales\n"
                . "/pending_transactions — Pending count\n"
                . "/failed_transactions — Failed count\n"
                . "/completed_transactions — Completed count";

        case '/sales_today':
            $r = json_decode(getData($db_prefix . 'transaction',
                'WHERE DATE(created_date) = CURDATE() AND status = "completed"',
                'SUM(amount) as total, COUNT(*) as cnt FROM'), true);
            $total = $r['response'][0]['total'] ?? '0';
            $cnt   = $r['response'][0]['cnt']   ?? '0';
            return "*Today's Sales*\n\nTotal: `{$total}`\nTransactions: `{$cnt}`";

        case '/sales_yesterday':
            $r = json_decode(getData($db_prefix . 'transaction',
                'WHERE DATE(created_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND status = "completed"',
                'SUM(amount) as total, COUNT(*) as cnt FROM'), true);
            $total = $r['response'][0]['total'] ?? '0';
            $cnt   = $r['response'][0]['cnt']   ?? '0';
            return "*Yesterday's Sales*\n\nTotal: `{$total}`\nTransactions: `{$cnt}`";

        case '/sales_this_month':
            $r = json_decode(getData($db_prefix . 'transaction',
                'WHERE MONTH(created_date) = MONTH(CURDATE()) AND YEAR(created_date) = YEAR(CURDATE()) AND status = "completed"',
                'SUM(amount) as total, COUNT(*) as cnt FROM'), true);
            $total = $r['response'][0]['total'] ?? '0';
            $cnt   = $r['response'][0]['cnt']   ?? '0';
            return "*This Month's Sales*\n\nTotal: `{$total}`\nTransactions: `{$cnt}`";

        case '/last_transaction':
            $r = json_decode(getData($db_prefix . 'transaction', 'ORDER BY id DESC LIMIT 1'), true);
            if (empty($r['response'][0])) return 'No transactions found.';
            $t    = $r['response'][0];
            $cust = json_decode($t['customer_info'] ?? '{}', true);
            return "*Last Transaction*\n\nID: `{$t['ref']}`\nAmount: `{$t['amount']} {$t['currency']}`\nName: " . ($cust['name'] ?? 'N/A') . "\nStatus: " . ucfirst($t['status']) . "\nDate: {$t['created_date']}";

        case '/pending_transactions':
            $r = json_decode(getData($db_prefix . 'transaction', 'WHERE status = "pending"', 'COUNT(*) as cnt FROM'), true);
            return "*Pending:* `" . ($r['response'][0]['cnt'] ?? '0') . "`";

        case '/failed_transactions':
            $r = json_decode(getData($db_prefix . 'transaction', 'WHERE status = "failed"', 'COUNT(*) as cnt FROM'), true);
            return "*Failed:* `" . ($r['response'][0]['cnt'] ?? '0') . "`";

        case '/completed_transactions':
            $r = json_decode(getData($db_prefix . 'transaction', 'WHERE status = "completed"', 'COUNT(*) as cnt FROM'), true);
            return "*Completed:* `" . ($r['response'][0]['cnt'] ?? '0') . "`";

        default:
            return 'Unknown command. Type /help for all available commands.';
    }
}
