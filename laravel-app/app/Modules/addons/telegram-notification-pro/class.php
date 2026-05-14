<?php
/**
 * Telegram Bot Notification Pro — PipraPay V3 Addon
 * Slug : telegram-notification-pro
 * Class: TelegramNotificationProAddon
 *
 * Webhook approach
 * ----------------
 * Telegram webhooks cannot hit .php files directly (root .htaccess blocks them).
 * A real file at site-root/tgnp-webhook/index.php receives Telegram POST requests
 * directly (no mod_rewrite involved, so the request body is never lost).
 * URL format: https://site.com/tgnp-webhook/?s={secret}
 *
 * Notification approach
 * ---------------------
 * The adapter's transactions.updated hook is gated by brand_id cookie and
 * doesn't fire reliably. We poll the transaction table in the shutdown function
 * for any rows updated since last check, sending Telegram messages for those.
 */

if (!defined('PipraPay_INIT')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

// Cache php://input immediately — it can only be read once as a stream.
// After any internal rewrite or framework processing it may appear empty.
if (!isset($GLOBALS['_TGNP_RAW_INPUT'])) {
    $GLOBALS['_TGNP_RAW_INPUT'] = file_get_contents('php://input');
}

class TelegramNotificationProAddon
{
    const VERSION     = '3.0.0';
    const SLUG        = 'telegram-notification-pro';
    const GITHUB_REPO = 'refatbd/PipraPay-bot-notification-pro';
    const UPDATE_URL  = 'https://wordpress.refat.ovh/api/update.php';

    private array  $options;
    private string $addon_id = '';

    public function __construct(array $options = [])
    {
        $this->options = $options;

        // Intercept Telegram webhook via the index.php router.
        // URL: https://site.com/?page=tgnp-webhook&s={secret}
        // index.php loads pp-adapter (which loads our constructor) BEFORE the router switch,
        // so our exit() here fires before the default 403 case.
        // Raw body is cached at top of this file into $GLOBALS['_TGNP_RAW_INPUT'].
        $page_raw = trim($_GET['page'] ?? '', '/');
        if ($page_raw === 'tgnp-webhook') {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $this->writeLog([
                date('Y-m-d H:i:s') . ' CONSTRUCTOR INTERCEPT',
                'method=' . $method,
                's=' . substr($_GET['s'] ?? '', 0, 8),
                'raw_input_len=' . strlen($GLOBALS['_TGNP_RAW_INPUT'] ?? ''),
            ]);
            // GET request = browser test, return diagnostic info
            if ($method === 'GET') {
                http_response_code(200);
                header('Content-Type: text/plain');
                echo "TGNP Webhook endpoint is reachable.
";
                echo "PHP is running.
";
                echo "Time: " . date('Y-m-d H:i:s') . "
";
                echo "Secret received: " . ($_GET['s'] ?? '(none)') . "
";
                echo "Stored secret: " . ($this->options['webhook_secret'] ?? '(not set)') . "
";
                echo "Bot token set: " . (!empty($this->options['bot_token']) ? 'YES' : 'NO') . "
";
                $root = $this->siteRoot();
                echo "Site root: " . $root . "
";
                foreach ([
                    $root . '/storage/media/',
                    $root . '/pp-media/',
                    sys_get_temp_dir() . '/',
                    $root . '/tgnp-webhook/',
                ] as $d) {
                    echo "Dir writable [" . $d . "]: " . (@is_writable($d) ? 'YES' : 'NO') . "
";
                }
                exit();
            }
            $this->handleWebhookRequest($_GET['s'] ?? '');
            exit();
        }

        add_action('transactions.updated', [$this, 'onTransactionsUpdated']);
        add_action('invoices.updated',     [$this, 'onInvoicesUpdated']);
        add_action('invoices.created',     [$this, 'onInvoicesUpdated']);

        register_shutdown_function([$this, 'shutdownHandler']);
    }

    // ================================================================
    // WEBHOOK HANDLER — receives Telegram updates via tgnp-webhook route
    // ================================================================
    private function handleWebhookRequest(string $secret): void
    {
        http_response_code(200);
        header('Content-Type: application/json');

        $stored_secret = $this->options['webhook_secret'] ?? '';

        if (empty($stored_secret) || $secret !== $stored_secret) {
            $this->writeLog([
                date('Y-m-d H:i:s') . ' WEBHOOK SECRET MISMATCH',
                'received=' . $secret,
                'stored=' . $stored_secret,
            ]);
            echo json_encode(['ok' => false, 'reason' => 'bad_secret']);
            return;
        }

        $token = $this->options['bot_token'] ?? '';
        if (empty($token)) {
            $this->writeLog([date('Y-m-d H:i:s') . ' WEBHOOK: no bot token']);
            echo json_encode(['ok' => true]);
            return;
        }

        $raw    = $GLOBALS['_TGNP_RAW_INPUT'] ?? file_get_contents('php://input');
        $update = json_decode($raw, true);

        $this->writeLog([
            date('Y-m-d H:i:s') . ' WEBHOOK UPDATE',
            'raw_len=' . strlen($raw),
            'has_message=' . (isset($update['message']) ? 'yes' : 'no'),
            'has_callback=' . (isset($update['callback_query']) ? 'yes' : 'no'),
            'text=' . ($update['message']['text'] ?? '(none)'),
            'chat_id=' . ($update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'] ?? '(none)'),
        ]);

        if (!$update) { echo json_encode(['ok' => true]); return; }

        echo json_encode(['ok' => true]);

        // Callback query (inline confirm button)
        if (isset($update['callback_query'])) {
            $cq      = $update['callback_query'];
            $chat_id = (string)$cq['message']['chat']['id'];
            $data    = $cq['data'] ?? '';
            if (str_starts_with($data, 'cp|')) {
                [, , $pp_id] = explode('|', $data, 3);
                global $db_prefix;
                updateData($db_prefix . 'transaction',
                    ['status', 'updated_date'],
                    ['completed', getCurrentDatetime('Y-m-d H:i:s')],
                    'ref = "' . escape_string($pp_id) . '"'
                );
                $this->callTelegram($token, 'sendMessage', [
                    'chat_id' => $chat_id, 'text' => "Transaction `{$pp_id}` approved.", 'parse_mode' => 'Markdown',
                ]);
            }
            $this->callTelegram($token, 'answerCallbackQuery', ['callback_query_id' => $cq['id'], 'text' => 'Done!']);
            return;
        }

        // Regular text message / command
        if (!isset($update['message']['text'])) return;
        $chat_id = (string)$update['message']['chat']['id'];
        $text    = trim($update['message']['text']);
        $command = strtolower(explode('@', explode(' ', $text)[0])[0]);

        $this->writeLog([date('Y-m-d H:i:s') . ' WEBHOOK CMD: ' . $command . ' from ' . $chat_id]);

        $reply = $this->handleBotCommand($command, $chat_id);

        $sendResult = $this->callTelegram($token, 'sendMessage', [
            'chat_id' => $chat_id, 'text' => $reply, 'parse_mode' => 'Markdown',
        ]);

        $this->writeLog([
            date('Y-m-d H:i:s') . ' WEBHOOK REPLY SENT',
            'ok=' . ($sendResult['ok'] ? 'yes' : 'no: ' . ($sendResult['description'] ?? '?')),
        ]);
    }

    private function handleBotCommand(string $cmd, string $chat_id): string
    {
        global $db_prefix;
        switch ($cmd) {
            case '/start':
                return "*Welcome to Telegram Bot Notification Pro!*\n\n"
                    . "Your *Chat ID* is: `{$chat_id}`\n\n"
                    . "Add this ID in the addon settings under *Chat IDs* to receive payment notifications.\n\n"
                    . "Type /help to see all commands.";

            case '/help':
                return "*Available Commands*\n\n"
                    . "/start — Get your Chat ID\n"
                    . "/last_transaction — Most recent transaction\n"
                    . "/sales_today — Today's total sales\n"
                    . "/sales_yesterday — Yesterday's sales\n"
                    . "/sales_this_month — This month's sales\n"
                    . "/pending_transactions — Pending count\n"
                    . "/failed_transactions — Failed count\n"
                    . "/completed_transactions — Completed count";

            case '/sales_today':
                $r = json_decode(getData($db_prefix . 'transaction',
                    'WHERE DATE(created_date) = CURDATE() AND status = "completed"',
                    'COALESCE(SUM(amount),0) as total, COUNT(*) as cnt FROM'), true);
                return "*Today's Sales*\n\nTotal: `" . ($r['response'][0]['total'] ?? '0') . "`\nTransactions: `" . ($r['response'][0]['cnt'] ?? '0') . "`";

            case '/sales_yesterday':
                $r = json_decode(getData($db_prefix . 'transaction',
                    'WHERE DATE(created_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND status = "completed"',
                    'COALESCE(SUM(amount),0) as total, COUNT(*) as cnt FROM'), true);
                return "*Yesterday's Sales*\n\nTotal: `" . ($r['response'][0]['total'] ?? '0') . "`\nTransactions: `" . ($r['response'][0]['cnt'] ?? '0') . "`";

            case '/sales_this_month':
                $r = json_decode(getData($db_prefix . 'transaction',
                    'WHERE MONTH(created_date) = MONTH(CURDATE()) AND YEAR(created_date) = YEAR(CURDATE()) AND status = "completed"',
                    'COALESCE(SUM(amount),0) as total, COUNT(*) as cnt FROM'), true);
                return "*This Month's Sales*\n\nTotal: `" . ($r['response'][0]['total'] ?? '0') . "`\nTransactions: `" . ($r['response'][0]['cnt'] ?? '0') . "`";

            case '/last_transaction':
                $r = json_decode(getData($db_prefix . 'transaction', 'ORDER BY id DESC LIMIT 1'), true);
                if (empty($r['response'][0])) return 'No transactions found.';
                $t    = $r['response'][0];
                $cust = json_decode($t['customer_info'] ?? '{}', true);
                return "*Last Transaction*\n\nID: `{$t['ref']}`\nAmount: `{$t['amount']} {$t['currency']}`\nName: " . ($cust['name'] ?? 'N/A') . "\nStatus: " . ucfirst($t['status']) . "\nDate: {$t['created_date']}";

            case '/pending_transactions':
                $r = json_decode(getData($db_prefix . 'transaction', 'WHERE status = "pending"', 'COUNT(*) as cnt FROM'), true);
                return "*Pending Transactions:* `" . ($r['response'][0]['cnt'] ?? '0') . "`";

            case '/failed_transactions':
                $r = json_decode(getData($db_prefix . 'transaction', 'WHERE status = "failed"', 'COUNT(*) as cnt FROM'), true);
                return "*Failed Transactions:* `" . ($r['response'][0]['cnt'] ?? '0') . "`";

            case '/completed_transactions':
                $r = json_decode(getData($db_prefix . 'transaction', 'WHERE status = "completed"', 'COUNT(*) as cnt FROM'), true);
                return "*Completed Transactions:* `" . ($r['response'][0]['cnt'] ?? '0') . "`";

            default:
                return "Unknown command. Type /help to see all commands.";
        }
    }

    // ================================================================
    // SHUTDOWN HANDLER
    // ================================================================
    public function shutdownHandler(): void
    {
        // Flush response to browser — rest runs in background
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        global $db_prefix;
        if (empty($this->addon_id)) {
            $row = json_decode(getData($db_prefix . 'addon', 'WHERE slug = "' . self::SLUG . '"'), true);
            $this->addon_id = $row['response'][0]['addon_id'] ?? '';
        }

        // Process tgnp_action (settings saves)
        if (($_POST['action'] ?? '') === 'addon-configuration-update'
            && !empty($_POST['tgnp_action'])
            && !empty($this->addon_id)) {
            $this->loadOptionsFromDb($this->addon_id);
            $this->processTgnpAction($this->addon_id, $_POST['tgnp_action']);
        }

        // Poll Telegram for bot commands (getUpdates, non-blocking)
        $this->pollBotUpdates();

        // Poll transaction table for new payment notifications
        $this->pollTransactions();
    }

    private function pollBotUpdates(): void
    {
        $token = $this->options['bot_token'] ?? '';
        if (empty($token) || $token === '--') return;

        $offset = (int)($this->options['bot_update_offset'] ?? 0);

        $res = $this->callTelegram($token, 'getUpdates', [
            'offset'          => $offset,
            'limit'           => 10,
            'timeout'         => 0,
            'allowed_updates' => json_encode(['message', 'callback_query']),
        ]);

        $updates = $res['result'] ?? [];
        if (empty($updates)) return;

        foreach ($updates as $update) {
            $this->processUpdate($update);
            $offset = max($offset, $update['update_id'] + 1);
        }

        if (!empty($this->addon_id)) {
            $this->dbSave($this->addon_id, ['bot_update_offset' => (string)$offset]);
        }
    }

    private function processUpdate(array $update): void
    {
        $token = $this->options['bot_token'] ?? '';

        // Callback query (inline confirm button)
        if (isset($update['callback_query'])) {
            $cq      = $update['callback_query'];
            $chat_id = (string)$cq['message']['chat']['id'];
            $data    = $cq['data'] ?? '';
            if (str_starts_with($data, 'cp|')) {
                [, , $pp_id] = explode('|', $data, 3);
                global $db_prefix;
                updateData($db_prefix . 'transaction',
                    ['status', 'updated_date'],
                    ['completed', getCurrentDatetime('Y-m-d H:i:s')],
                    'ref = "' . escape_string($pp_id) . '"'
                );
                $this->callTelegram($token, 'sendMessage', [
                    'chat_id'    => $chat_id,
                    'text'       => "Transaction `{$pp_id}` approved.",
                    'parse_mode' => 'Markdown',
                ]);
            }
            $this->callTelegram($token, 'answerCallbackQuery', [
                'callback_query_id' => $cq['id'],
                'text'              => 'Done!',
            ]);
            return;
        }

        // Text command
        if (!isset($update['message']['text'])) return;
        $chat_id = (string)$update['message']['chat']['id'];
        $text    = trim($update['message']['text']);
        $command = strtolower(explode('@', explode(' ', $text)[0])[0]);

        $reply = $this->handleBotCommand($command, $chat_id);
        $this->callTelegram($token, 'sendMessage', [
            'chat_id'    => $chat_id,
            'text'       => $reply,
            'parse_mode' => 'Markdown',
        ]);
    }

    // ================================================================
    // TRANSACTION POLLING
    // ================================================================
    private function pollTransactions(): void
    {
        global $db_prefix;

        $token = $this->options['bot_token'] ?? '';
        if (empty($token) || $token === '--') return;

        $notif_enabled = $this->options['notifications_enabled'] ?? 'true';
        if ($notif_enabled === 'false' || $notif_enabled === '--') return;

        if (empty($this->addon_id)) return;

        $last_polled = $this->options['last_polled_at'] ?? '';
        if (empty($last_polled) || $last_polled === '--') {
            $this->dbSave($this->addon_id, ['last_polled_at' => getCurrentDatetime('Y-m-d H:i:s')]);
            return;
        }

        $escaped = escape_string($last_polled);
        $rows = json_decode(getData(
            $db_prefix . 'transaction',
            'WHERE updated_date > "' . $escaped . '" AND status NOT IN ("initiated") ORDER BY updated_date ASC LIMIT 50'
        ), true);

        $now = getCurrentDatetime('Y-m-d H:i:s');
        $this->dbSave($this->addon_id, ['last_polled_at' => $now]);

        $transactions = $rows['response'] ?? [];
        if (empty($transactions)) return;

        foreach ($transactions as $row) {
            $customer_info = json_decode($row['customer_info'] ?? '{}', true) ?: [];
            $gw_row = json_decode(getData(
                $db_prefix . 'gateways',
                'WHERE brand_id = "' . escape_string($row['brand_id']) . '" AND gateway_id = "' . escape_string($row['gateway_id'] ?? '') . '"'
            ), true);
            $gateway = $gw_row['response'][0]['name'] ?? ($row['gateway_id'] ?? 'N/A');

            $t = [
                'pp_id'          => $row['ref']            ?? '',
                'full_name'      => $customer_info['name']  ?? 'N/A',
                'email_address'  => $customer_info['email'] ?? 'N/A',
                'mobile_number'  => $customer_info['mobile'] ?? 'N/A',
                'gateway'        => $gateway,
                'amount'         => $row['amount']          ?? 'N/A',
                'fee'            => $row['processing_fee']  ?? '0',
                'total'          => $row['amount']          ?? 'N/A',
                'currency'       => $row['currency']        ?? '',
                'local_currency' => $row['local_currency']  ?? '',
                'sender'         => $row['sender']          ?? '--',
                'transaction_id' => $row['trx_id']          ?? '--',
                'status'         => $row['status']          ?? '',
                'date'           => $row['updated_date']    ?? '',
            ];

            $this->notifyTransaction($t);
        }
    }

    private function notifyTransaction(array $t): void
    {
        $status = strtolower($t['status'] ?? '');

        // PipraPay stores "canceled" (one l), our keys use "cancelled" (two l)
        $notify_key = ($status === 'canceled') ? 'cancelled' : $status;
        $notify_val = $this->options['notify_' . $notify_key] ?? 'true';

        if ($notify_val === 'false' || $notify_val === '--') return;

        $t['_display_status'] = ($status === 'canceled') ? 'cancelled' : $status;
        $message = $this->buildTransactionMessage($t, $notify_key);
        $markup  = null;

        if ($status === 'pending'
            && ($this->options['enable_confirm_button'] ?? 'false') === 'true') {
            $site_id = $this->options['site_identifier'] ?? 'local';
            $cbd     = 'cp|' . $site_id . '|' . $t['pp_id'];
            if (strlen($cbd) <= 64) {
                $markup = ['inline_keyboard' => [[
                    ['text' => 'Confirm Transaction', 'callback_data' => $cbd],
                ]]];
            }
        }

        $this->broadcast($message, $markup);
    }

    // ================================================================
    // LEGACY HOOK
    // ================================================================
    public function onTransactionsUpdated(array $transactions): void
    {
        foreach ($transactions as $t) {
            $this->notifyTransaction($t);
        }
    }

    public function onInvoicesUpdated(array $invoices): void {}

    // ================================================================
    // TGNP ACTION PROCESSOR
    // ================================================================
    private function processTgnpAction(string $addon_id, string $tgnp_action): void
    {
        global $db_prefix, $site_url;

        switch ($tgnp_action) {

            case 'save_bot_token':
                $token = $this->options['bot_token'] ?? '';
                if (empty($token) || $token === '--') {
                    $this->dbSave($addon_id, [
                        'bot_username'    => '',
                        'webhook_status'  => 'Error: empty token',
                        'tgnp_last_error' => 'Bot token was empty',
                        'tgnp_action'     => '',
                    ]);
                    break;
                }
                $me = $this->callTelegram($token, 'getMe');
                if (!($me['ok'] ?? false)) {
                    $this->dbSave($addon_id, [
                        'bot_token'       => '',
                        'bot_username'    => '',
                        'webhook_status'  => 'Error: invalid token',
                        'tgnp_last_error' => 'Invalid token: ' . ($me['description'] ?? 'Unknown'),
                        'tgnp_action'     => '',
                    ]);
                    break;
                }
                $secret  = bin2hex(random_bytes(16));
                $wh_url  = rtrim($site_url, '/') . '/?page=tgnp-webhook&s=' . $secret;
                $res     = $this->callTelegram($token, 'setWebhook', [
                    'url'             => $wh_url,
                    'max_connections' => 40,
                    'allowed_updates' => json_encode(['message', 'callback_query']),
                ]);
                $wh_status = ($res['ok'] ?? false)
                    ? 'Active: ' . $wh_url
                    : 'Webhook error: ' . ($res['description'] ?? 'Unknown');
                if ($res['ok'] ?? false) {
                    $this->registerBotCommands($token);
                }
                $this->dbSave($addon_id, [
                    'bot_username'    => $me['result']['username'] ?? '',
                    'webhook_status'  => $wh_status,
                    'webhook_secret'  => $secret,
                    'tgnp_last_error' => '',
                    'tgnp_action'     => '',
                ]);
                break;

            case 'delete_bot_token':
                $cur_token = $this->options['bot_token'] ?? '';
                if (!empty($cur_token) && $cur_token !== '--') {
                    $this->callTelegram($cur_token, 'deleteWebhook', ['drop_pending_updates' => true]);
                }
                $this->dbSave($addon_id, [
                    'bot_token'         => '',
                    'bot_username'      => '',
                    'webhook_status'    => 'Disconnected',
                    'webhook_secret'    => '',
                    'bot_update_offset' => '0',
                    'tgnp_action'       => '',
                ]);
                break;

            case 'send_test_message':
                $token   = $this->options['bot_token'] ?? '';
                $chat_id = $this->options['tgnp_test_chat_id'] ?? '';
                $username = $this->options['bot_username'] ?? 'unknown';
                if (empty($token) || $token === '--' || empty($chat_id)) {
                    $this->dbSave($addon_id, [
                        'tgnp_test_result' => 'Error: missing token or chat ID',
                        'tgnp_action'      => '',
                    ]);
                    break;
                }
                $res = $this->callTelegram($token, 'sendMessage', [
                    'chat_id'    => $chat_id,
                    'text'       => "Test from PipraPay\n\nBot: @{$username}\nTime: " . date('d M Y, h:i A'),
                    'parse_mode' => 'Markdown',
                ]);
                $this->dbSave($addon_id, [
                    'tgnp_test_result'  => ($res['ok'] ?? false)
                        ? "✅ Sent to {$chat_id}"
                        : "❌ Failed: " . ($res['description'] ?? 'Unknown'),
                    'tgnp_test_chat_id' => '',
                    'tgnp_action'       => '',
                ]);
                break;

            case 'save_templates':
                $def  = $this->defaultTemplates();
                $save = ['tgnp_action' => ''];
                foreach (['completed', 'pending', 'failed', 'refunded', 'cancelled'] as $s) {
                    $key = 'template_' . $s;
                    $val = $this->options[$key] ?? '';
                    if ($val === '' || $val === '--') $save[$key] = $def[$s];
                }
                $this->dbSave($addon_id, $save);
                break;

            case 'register_node':
                $hub_url_raw     = $this->options['hub_url']         ?? '';
                $api_secret      = $this->options['api_secret']      ?? '';
                $site_identifier = $this->options['site_identifier'] ?? '';
                $site_name_val   = $this->options['site_name']       ?? '';
                $target          = rtrim($hub_url_raw, '/') . '/?page=tgnp-webhook&s=' . $this->options['webhook_secret'];
                $response = $this->sendSignedRequest($target, 'register_node', [
                    'site_url'        => rtrim($site_url, '/'),
                    'site_name'       => $site_name_val,
                    'site_identifier' => $site_identifier,
                ], $api_secret);
                $this->dbSave($addon_id, [
                    'hub_connected_status' => ($response['status'] ?? false) ? 'Connected' : 'Error: ' . ($response['message'] ?? 'Unknown'),
                    'tgnp_last_error'      => ($response['status'] ?? false) ? '' : ($response['message'] ?? 'Unknown'),
                    'tgnp_action'          => '',
                ]);
                break;

            case 'disconnect_hub':
                $this->dbSave($addon_id, [
                    'hub_url'              => '',
                    'hub_connected_status' => 'Disconnected',
                    'tgnp_action'          => '',
                ]);
                break;

            case 'reset_poll_time':
                $this->dbSave($addon_id, [
                    'last_polled_at' => getCurrentDatetime('Y-m-d H:i:s'),
                    'tgnp_action'    => '',
                ]);
                break;

            case 'reset_templates':
                $def = $this->defaultTemplates();
                $this->dbSave($addon_id, array_merge([
                    'template_completed' => $def['completed'],
                    'template_pending'   => $def['pending'],
                    'template_failed'    => $def['failed'],
                    'template_refunded'  => $def['refunded'],
                    'template_cancelled' => $def['cancelled'],
                ], ['tgnp_action' => '']));
                break;

            case 'remove_node':
                $node_id = $_POST['remove_node_id'] ?? '';
                $raw = $this->options['connected_nodes_json'] ?? [];
                $nodes = is_array($raw) ? $raw : (json_decode($raw ?: '[]', true) ?: []);
                $nodes = array_values(array_filter($nodes, fn($n) => $n['site_identifier'] !== $node_id));
                $this->dbSave($addon_id, ['connected_nodes_json' => json_encode($nodes), 'tgnp_action' => '']);
                break;

            case 'check_updates':
                $github = $this->checkGithubUpdates();
                // Return inline — can't echo from shutdown, store result
                $this->dbSave($addon_id, ['tgnp_action' => '']);
                break;

            case 'install_update':
                $url = $_POST['update_url'] ?? '';
                if (!empty($url)) $this->installUpdateZip($url);
                $this->dbSave($addon_id, ['tgnp_action' => '']);
                break;

            case 'diagnose_paths':
                $sr = $this->siteRoot();
                $wh_dir  = $sr . '/tgnp-webhook';
                $wh_file = $wh_dir . '/index.php';
                $tmp     = sys_get_temp_dir();
                $diag = implode("\n", [
                    '__FILE__       = ' . __FILE__,
                    'siteRoot()     = ' . $sr,
                    'wh_dir        = ' . $wh_dir,
                    'wh_dir exists = ' . (is_dir($wh_dir)       ? 'YES' : 'NO'),
                    'wh_file exists= ' . (file_exists($wh_file) ? 'YES' : 'NO'),
                    'sr writable   = ' . (is_writable($sr)      ? 'YES' : 'NO'),
                    'tmp           = ' . $tmp,
                    'tmp writable  = ' . (is_writable($tmp)     ? 'YES' : 'NO'),
                    'pp-media      = ' . $sr . '/pp-media -> ' . (is_dir($sr.'/pp-media') ? 'exists' : 'missing'),
                    'pp-media writ = ' . (is_writable($sr.'/pp-media') ? 'YES' : 'NO'),
                ]);
                // Store in DB so view can read it, and also return in message
                $this->dbSave($addon_id, ['tgnp_diag' => $diag, 'tgnp_action' => '']);
                // Try to write log directly now that we know the path
                $this->writeLog(['DIAGNOSE', $diag]);
                break;

            case 'clear_debug_log':
                $root = $this->siteRoot();
                foreach ([
                    $root . '/storage/media/tgnp-debug.log',
                    $root . '/pp-media/tgnp-debug.log',
                    sys_get_temp_dir() . '/tgnp-debug.log',
                ] as $lf) {
                    if (@file_exists($lf)) @unlink($lf);
                }
                $this->dbSave($addon_id, ['tgnp_action' => '']);
                break;

            case 'reregister_webhook':
                $token = $this->options['bot_token'] ?? '';
                if (empty($token) || $token === '--') break;
                $this->callTelegram($token, 'deleteWebhook', ['drop_pending_updates' => false]);
                sleep(1);
                $secret  = bin2hex(random_bytes(16));
                $wh_url  = rtrim($site_url, '/') . '/?page=tgnp-webhook&s=' . $secret;
                $res     = $this->callTelegram($token, 'setWebhook', [
                    'url'             => $wh_url,
                    'max_connections' => 40,
                    'allowed_updates' => json_encode(['message', 'callback_query']),
                ]);
                $status = ($res['ok'] ?? false) ? 'Active: ' . $wh_url : 'Error: ' . ($res['description'] ?? 'unknown');
                $this->dbSave($addon_id, [
                    'webhook_status'  => $status,
                    'webhook_secret'  => $secret,
                    'tgnp_action'     => '',
                    'tgnp_last_error' => ($res['ok'] ?? false) ? '' : ($res['description'] ?? ''),
                ]);
                break;

            case 'drain_pending_updates':
                // Pull any queued updates via getUpdates (one-time, then delete them)
                $token = $this->options['bot_token'] ?? '';
                if (empty($token) || $token === '--') break;
                // Temporarily delete webhook to allow getUpdates
                $this->callTelegram($token, 'deleteWebhook', ['drop_pending_updates' => false]);
                sleep(1);
                $res = $this->callTelegram($token, 'getUpdates', [
                    'offset'  => 0,
                    'limit'   => 100,
                    'timeout' => 0,
                    'allowed_updates' => json_encode(['message', 'callback_query']),
                ]);
                $updates = $res['result'] ?? [];
                $this->writeLog([
                    date('Y-m-d H:i:s') . ' DRAIN: found ' . count($updates) . ' pending updates',
                    json_encode(array_map(fn($u) => [
                        'id'   => $u['update_id'],
                        'text' => $u['message']['text'] ?? $u['callback_query']['data'] ?? '(none)',
                        'from' => $u['message']['chat']['id'] ?? $u['callback_query']['message']['chat']['id'] ?? '?',
                    ], $updates)),
                ]);
                // Process each update
                foreach ($updates as $update) {
                    if (isset($update['message']['text'])) {
                        $chat_id = (string)$update['message']['chat']['id'];
                        $text    = trim($update['message']['text']);
                        $command = strtolower(explode('@', explode(' ', $text)[0])[0]);
                        $reply   = $this->handleBotCommand($command, $chat_id);
                        $this->callTelegram($token, 'sendMessage', [
                            'chat_id'    => $chat_id,
                            'text'       => $reply,
                            'parse_mode' => 'Markdown',
                        ]);
                    }
                }
                // Advance offset past all updates so they're acknowledged
                if (!empty($updates)) {
                    $max_id = max(array_column($updates, 'update_id'));
                    $this->callTelegram($token, 'getUpdates', ['offset' => $max_id + 1, 'timeout' => 0]);
                }
                // Re-register webhook
                global $site_url;
                $secret  = $this->options['webhook_secret'] ?? bin2hex(random_bytes(16));
                $wh_url  = rtrim($site_url, '/') . '/?page=tgnp-webhook&s=' . $secret;
                $this->callTelegram($token, 'setWebhook', [
                    'url'             => $wh_url,
                    'max_connections' => 40,
                    'allowed_updates' => json_encode(['message', 'callback_query']),
                ]);
                $this->dbSave($addon_id, ['tgnp_action' => '']);
                break;

            default:
                $this->dbSave($addon_id, ['tgnp_action' => '']);
        }
    }

    // ================================================================
    // BROADCAST
    // ================================================================
    private function broadcast(string $message, ?array $reply_markup = null): void
    {
        $token     = $this->options['bot_token'] ?? '';
        $raw       = $this->options['chat_ids_json'] ?? [];
        $chat_ids  = is_array($raw) ? $raw : (json_decode(is_string($raw) ? $raw : '[]', true) ?: []);

        $site_name = $this->options['site_name'] ?? '';
        if (empty($site_name) || $site_name === '--') {
            global $global_response_brand;
            $site_name = $global_response_brand['response'][0]['identify_name'] ?? 'PipraPay';
        }

        if (empty($token) || $token === '--' || empty($chat_ids)) return;

        $message .= "\n\n----------\n*From:* " . $site_name;

        foreach ($chat_ids as $chat) {
            $cid     = $chat['id'] ?? '';
            $enabled = $chat['enabled'] ?? 'false';
            if ($enabled !== 'true') continue;
            $params = ['chat_id' => $cid, 'text' => $message, 'parse_mode' => 'Markdown'];
            if ($reply_markup) $params['reply_markup'] = json_encode($reply_markup);
            $this->callTelegram($token, 'sendMessage', $params);
        }
    }

    // ================================================================
    // MESSAGE BUILDER
    // ================================================================
    private function buildTransactionMessage(array $t, string $status): string
    {
        $def      = $this->defaultTemplates();
        $tpl_key  = 'template_' . $status;
        $template = !empty($this->options[$tpl_key])
            ? $this->options[$tpl_key]
            : ($def[$status] ?? $def['completed']);

        $display_status = $t['_display_status'] ?? $status;
        $map = [
            'amount'         => $t['amount']         ?? 'N/A',
            'currency'       => $t['currency']        ?? 'N/A',
            'customer_name'  => $t['full_name']       ?? 'N/A',
            'payment_method' => $t['gateway']         ?? 'N/A',
            'sender_number'  => $t['sender']          ?? 'N/A',
            'date'           => $t['date']            ?? 'N/A',
            'payment_id'     => $t['pp_id']           ?? 'N/A',
            'gateway_trx_id' => $t['transaction_id']  ?? 'N/A',
            'status'         => ucfirst($display_status),
            'email'          => $t['email_address']   ?? 'N/A',
            'mobile'         => $t['mobile_number']   ?? 'N/A',
            'fee'            => $t['fee']             ?? '0',
            'total'          => $t['total']           ?? 'N/A',
            'local_currency' => $t['local_currency']  ?? '',
        ];

        foreach ($map as $k => $v) {
            $template = str_replace('{' . $k . '}', $v, $template);
        }
        return $template;
    }

    // ================================================================
    // DEFAULT TEMPLATES
    // ================================================================
    public function defaultTemplates(): array
    {
        return [
            'completed' => "✅ *New Transaction: Completed*\n\n💰 *Amount:* `{amount} {currency}`\n👤 *From:* {customer_name}\n💳 *Method:* {payment_method}\n📱 *Sender:* `{sender_number}`\n🗓️ *Date:* {date}\n📄 *Payment ID:* `{payment_id}`\n🔗 *TRX ID:* `{gateway_trx_id}`",
            'pending'   => "⏳ *New Transaction: Pending*\n\n💰 *Amount:* `{amount} {currency}`\n👤 *From:* {customer_name}\n💳 *Method:* {payment_method}\n📱 *Sender:* `{sender_number}`\n🗓️ *Date:* {date}\n📄 *Payment ID:* `{payment_id}`\n🔗 *TRX ID:* `{gateway_trx_id}`",
            'failed'    => "❌ *New Transaction: Failed*\n\n💰 *Amount:* `{amount} {currency}`\n👤 *From:* {customer_name}\n💳 *Method:* {payment_method}\n📱 *Sender:* `{sender_number}`\n🗓️ *Date:* {date}\n📄 *Payment ID:* `{payment_id}`\n🔗 *TRX ID:* `{gateway_trx_id}`",
            'refunded'  => "🔄 *New Transaction: Refunded*\n\n💰 *Amount:* `{amount} {currency}`\n👤 *From:* {customer_name}\n💳 *Method:* {payment_method}\n📱 *Sender:* `{sender_number}`\n🗓️ *Date:* {date}\n📄 *Payment ID:* `{payment_id}`\n🔗 *TRX ID:* `{gateway_trx_id}`",
            'cancelled' => "🚫 *New Transaction: Cancelled*\n\n💰 *Amount:* `{amount} {currency}`\n👤 *From:* {customer_name}\n💳 *Method:* {payment_method}\n📱 *Sender:* `{sender_number}`\n🗓️ *Date:* {date}\n📄 *Payment ID:* `{payment_id}`\n🔗 *TRX ID:* `{gateway_trx_id}`",
        ];
    }

    // ================================================================
    // ADDON INFO & CONFIGURATION UI
    // ================================================================
    public function info(): array
    {
        return [
            'title'       => 'Telegram Bot Notification Pro',
            'description' => 'Real-time Telegram notifications for every payment event.',
            'version'     => self::VERSION,
            'author'      => 'Refat Rahman',
            'author_url'  => 'https://github.com/refatbd',
        ];
    }

    public function configuration(): string
    {
        ob_start();

        global $db_prefix, $site_url, $path_admin, $csrf_token;

        $addonRow    = json_decode(getData($db_prefix . 'addon', 'WHERE slug = "' . self::SLUG . '"'), true);
        $addon_db_id = $addonRow['response'][0]['addon_id'] ?? '';

        $options = [];
        if (!empty($addon_db_id)) {
            $rows = json_decode(getData($db_prefix . 'addon_parameter', 'WHERE addon_id = "' . $addon_db_id . '"'), true);
            foreach ($rows['response'] ?? [] as $row) {
                $v = ($row['value'] === '--') ? '' : $row['value'];
                if (!empty($v) && in_array($v[0] ?? '', ['{', '['])) {
                    $dec = json_decode($v, true);
                    if ($dec !== null) $v = $dec;
                }
                $options[$row['option_name']] = $v;
            }
        }

        $bot_token       = $options['bot_token']           ?? '';
        $bot_username    = $options['bot_username']         ?? '';
        $webhook_status  = $options['webhook_status']       ?? 'Not configured';
        $operation_mode  = $options['operation_mode']       ?? 'standalone';
        $site_name       = $options['site_name']            ?? '';
        $site_identifier = $options['site_identifier']      ?? 'store_' . rand(100, 999);
        $api_secret      = $options['api_secret']           ?? bin2hex(random_bytes(16));
        $hub_url         = $options['hub_url']              ?? '';
        $hub_status      = $options['hub_connected_status'] ?? 'Disconnected';
        $last_error      = $options['tgnp_last_error']      ?? '';
        $diag_output     = $options['tgnp_diag']           ?? '';
        $test_result     = $options['tgnp_test_result']     ?? '';

        // Clear test_result and last_error after reading so they don't persist across refreshes
        if (!empty($test_result) && !empty($addon_db_id)) {
            $rows2 = json_decode(getData($db_prefix . 'addon_parameter',
                'WHERE addon_id = "' . $addon_db_id . '" AND option_name = "tgnp_test_result"'), true);
            if (!empty($rows2['response'][0]['id'])) {
                updateData($db_prefix . 'addon_parameter', ['value', 'updated_date'],
                    ['--', getCurrentDatetime('Y-m-d H:i:s')], 'id = "' . $rows2['response'][0]['id'] . '"');
            }
        }
        if (!empty($last_error) && !empty($addon_db_id)) {
            $rows3 = json_decode(getData($db_prefix . 'addon_parameter',
                'WHERE addon_id = "' . $addon_db_id . '" AND option_name = "tgnp_last_error"'), true);
            if (!empty($rows3['response'][0]['id'])) {
                updateData($db_prefix . 'addon_parameter', ['value', 'updated_date'],
                    ['--', getCurrentDatetime('Y-m-d H:i:s')], 'id = "' . $rows3['response'][0]['id'] . '"');
            }
        }

        $raw_chat_ids = $options['chat_ids_json'] ?? '';
        $chat_ids     = is_array($raw_chat_ids)
            ? $raw_chat_ids
            : (json_decode(is_string($raw_chat_ids) ? $raw_chat_ids : '[]', true) ?: []);

        $raw_nodes       = $options['connected_nodes_json'] ?? '';
        $connected_nodes = is_array($raw_nodes)
            ? $raw_nodes
            : (json_decode(is_string($raw_nodes) ? $raw_nodes : '[]', true) ?: []);

        $def       = $this->defaultTemplates();
        $templates = [
            'completed' => $options['template_completed'] ?? $def['completed'],
            'pending'   => $options['template_pending']   ?? $def['pending'],
            'failed'    => $options['template_failed']    ?? $def['failed'],
            'refunded'  => $options['template_refunded']  ?? $def['refunded'],
            'cancelled' => $options['template_cancelled'] ?? $def['cancelled'],
        ];

        $dashboard_url = rtrim($site_url, '/') . '/' . ltrim($path_admin, '/') . '/dashboard';

        if (empty($csrf_token) && !empty($_SESSION['csrf_token'])) {
            $csrf_token = $_SESSION['csrf_token'];
        }

        include __DIR__ . '/views/configuration.php';
        return ob_get_clean();
    }

    // ================================================================
    // DB HELPERS
    // ================================================================
    private function loadOptionsFromDb(string $addon_id): void
    {
        global $db_prefix;
        $rows = json_decode(getData($db_prefix . 'addon_parameter', 'WHERE addon_id = "' . $addon_id . '"'), true);
        foreach ($rows['response'] ?? [] as $row) {
            $v = ($row['value'] === '--') ? '' : $row['value'];
            if (!empty($v) && in_array($v[0] ?? '', ['{', '['])) {
                $dec = json_decode($v, true);
                if ($dec !== null) $v = $dec;
            }
            $this->options[$row['option_name']] = $v;
        }
    }

    private function dbSave(string $addon_id, array $data): void
    {
        global $db_prefix;
        foreach ($data as $key => $value) {
            if (is_array($value)) $value = json_encode($value);
            $store  = ($value === '' || $value === null) ? '--' : (string) $value;
            $ek     = escape_string($key);
            $exists = json_decode(getData(
                $db_prefix . 'addon_parameter',
                'WHERE addon_id = "' . $addon_id . '" AND option_name = "' . $ek . '"'
            ), true);
            if (!empty($exists['response'][0]['id'])) {
                updateData($db_prefix . 'addon_parameter', ['value', 'updated_date'],
                    [$store, getCurrentDatetime('Y-m-d H:i:s')], 'id = "' . $exists['response'][0]['id'] . '"');
            } else {
                insertData($db_prefix . 'addon_parameter',
                    ['addon_id', 'option_name', 'value', 'created_date', 'updated_date'],
                    [$addon_id, $key, $store, getCurrentDatetime('Y-m-d H:i:s'), getCurrentDatetime('Y-m-d H:i:s')]);
            }
        }
    }

    // ================================================================
    // WEBHOOK FILE WRITER
    // ================================================================
    private function writeWebhookFile(string $wh_file, string $addon_id, string $secret): void
    {
        // This file lives at site-root/tgnp-webhook/index.php
        // It's a completely standalone handler - no PipraPay bootstrap needed for the happy path
        // It reads config directly, processes the Telegram update, and exits.
        $php = <<<'WEBHOOKPHP'
<?php
/**
 * Telegram Bot Notification Pro — Webhook Handler
 * Auto-generated. Do not edit manually.
 * Located at: {site-root}/tgnp-webhook/index.php
 * Telegram calls: POST https://your-site.com/tgnp-webhook/?s={secret}
 */

http_response_code(200);
header('Content-Type: application/json');

// Validate secret from query param ?s=SECRET
$received_secret = $_GET['s'] ?? '';

$site_root = dirname(__DIR__);

// Load DB config
$config_file = $site_root . '/pp-config.php';
if (!file_exists($config_file)) { echo json_encode(['ok'=>false,'r'=>'no config']); exit; }
require $config_file;  // sets $db_host, $db_user, $db_pass, $db_name, $db_prefix

// Connect DB
try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user, $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (Exception $e) {
    echo json_encode(['ok'=>false,'r'=>'db_err']); exit;
}

// Load addon settings
$addon_slug = 'telegram-notification-pro';
$stmt = $pdo->prepare("SELECT addon_id FROM {$db_prefix}addon WHERE slug = ? LIMIT 1");
$stmt->execute([$addon_slug]);
$addon_row = $stmt->fetch();
if (!$addon_row) { echo json_encode(['ok'=>false,'r'=>'no addon']); exit; }

$addon_id = $addon_row['addon_id'];
$stmt2 = $pdo->prepare("SELECT option_name, value FROM {$db_prefix}addon_parameter WHERE addon_id = ?");
$stmt2->execute([$addon_id]);
$opts = [];
foreach ($stmt2->fetchAll() as $row) {
    $v = ($row['value'] === '--') ? '' : $row['value'];
    if ($v !== '' && in_array($v[0] ?? '', ['{','['])) { $d = json_decode($v, true); if ($d !== null) $v = $d; }
    $opts[$row['option_name']] = $v;
}

$stored_secret = $opts['webhook_secret'] ?? '';
$bot_token     = $opts['bot_token']      ?? '';

// Validate
if ($received_secret !== $stored_secret || empty($bot_token)) {
    tgnp_log($site_root, "WEBHOOK: secret mismatch or no token. received={$received_secret} stored={$stored_secret}");
    echo json_encode(['ok'=>false,'r'=>'auth']); exit;
}

// Read body
$raw    = file_get_contents('php://input');
$update = json_decode($raw ?: '', true);

tgnp_log($site_root, date('Y-m-d H:i:s') . " WEBHOOK UPDATE raw_len=" . strlen($raw) .
    " has_msg=" . (isset($update['message']) ? 'yes' : 'no') .
    " text=" . ($update['message']['text'] ?? '(none)') .
    " chat=" . ($update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'] ?? '?'));

echo json_encode(['ok' => true]);

if (!$update) exit;

// --- Callback query (confirm button) ---
if (isset($update['callback_query'])) {
    $cq      = $update['callback_query'];
    $chat_id = (string)$cq['message']['chat']['id'];
    $data    = $cq['data'] ?? '';
    if (str_starts_with($data, 'cp|')) {
        [, , $pp_id] = explode('|', $data, 3);
        $stmt3 = $pdo->prepare("UPDATE {$db_prefix}transaction SET status='completed', updated_date=NOW() WHERE ref=?");
        $stmt3->execute([$pp_id]);
        tgnp_send($bot_token, 'sendMessage', ['chat_id'=>$chat_id,'text'=>"Transaction `{$pp_id}` approved.",'parse_mode'=>'Markdown']);
    }
    tgnp_send($bot_token, 'answerCallbackQuery', ['callback_query_id'=>$cq['id'],'text'=>'Done!']);
    exit;
}

// --- Text command ---
if (!isset($update['message']['text'])) exit;
$chat_id = (string)$update['message']['chat']['id'];
$text    = trim($update['message']['text']);
$command = strtolower(explode('@', explode(' ', $text)[0])[0]);

tgnp_log($site_root, date('Y-m-d H:i:s') . " WEBHOOK CMD: {$command} from {$chat_id}");

$reply = tgnp_handle_command($command, $chat_id, $pdo, $db_prefix);
$res   = tgnp_send($bot_token, 'sendMessage', ['chat_id'=>$chat_id,'text'=>$reply,'parse_mode'=>'Markdown']);

tgnp_log($site_root, date('Y-m-d H:i:s') . " REPLY: ok=" . ($res['ok'] ? 'yes' : 'no: '.($res['description']??'?')));

// --- Helpers ---

function tgnp_send(string $token, string $method, array $params): array {
    $ch = curl_init("https://api.telegram.org/bot{$token}/{$method}");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>http_build_query($params), CURLOPT_TIMEOUT=>10, CURLOPT_SSL_VERIFYPEER=>false]);
    $res = curl_exec($ch); curl_close($ch);
    return json_decode($res ?: '{}', true) ?: [];
}

function tgnp_log(string $site_root, string $msg): void {
    foreach ([
        $site_root.'/storage/media/',
        $site_root.'/pp-media/',
        sys_get_temp_dir().'/',
        __DIR__.'/',  // tgnp-webhook/ dir itself as last resort
    ] as $d) {
        if (@is_dir($d) && @is_writable($d)) {
            @file_put_contents($d.'tgnp-debug.log', $msg."
---
", FILE_APPEND|LOCK_EX);
            return;
        }
    }
}

function tgnp_db_val(PDO $pdo, string $sql, array $params = []): string {
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    $row = $stmt->fetch(); return $row ? (string)array_values($row)[0] : '0';
}

function tgnp_handle_command(string $cmd, string $chat_id, PDO $pdo, string $pfx): string {
    switch ($cmd) {
        case '/start':
            return "*Welcome!*

Your *Chat ID* is: `{$chat_id}`

Add this in addon settings → Chat IDs to receive notifications.

Type /help for all commands.";
        case '/help':
            return "*Available Commands*

/start — Get your Chat ID
/last_transaction — Most recent transaction
/sales_today — Today's sales
/sales_yesterday — Yesterday's sales
/sales_this_month — This month's sales
/pending_transactions — Pending count
/failed_transactions — Failed count
/completed_transactions — Completed count";
        case '/sales_today':
            $total = tgnp_db_val($pdo, "SELECT COALESCE(SUM(amount),0) FROM {$pfx}transaction WHERE DATE(created_date)=CURDATE() AND status='completed'");
            $cnt   = tgnp_db_val($pdo, "SELECT COUNT(*) FROM {$pfx}transaction WHERE DATE(created_date)=CURDATE() AND status='completed'");
            return "*Today's Sales*

Total: `{$total}`
Transactions: `{$cnt}`";
        case '/sales_yesterday':
            $total = tgnp_db_val($pdo, "SELECT COALESCE(SUM(amount),0) FROM {$pfx}transaction WHERE DATE(created_date)=DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND status='completed'");
            $cnt   = tgnp_db_val($pdo, "SELECT COUNT(*) FROM {$pfx}transaction WHERE DATE(created_date)=DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND status='completed'");
            return "*Yesterday's Sales*

Total: `{$total}`
Transactions: `{$cnt}`";
        case '/sales_this_month':
            $total = tgnp_db_val($pdo, "SELECT COALESCE(SUM(amount),0) FROM {$pfx}transaction WHERE MONTH(created_date)=MONTH(CURDATE()) AND YEAR(created_date)=YEAR(CURDATE()) AND status='completed'");
            $cnt   = tgnp_db_val($pdo, "SELECT COUNT(*) FROM {$pfx}transaction WHERE MONTH(created_date)=MONTH(CURDATE()) AND YEAR(created_date)=YEAR(CURDATE()) AND status='completed'");
            return "*This Month's Sales*

Total: `{$total}`
Transactions: `{$cnt}`";
        case '/last_transaction':
            $stmt = $pdo->query("SELECT * FROM {$pfx}transaction ORDER BY id DESC LIMIT 1");
            $t = $stmt->fetch();
            if (!$t) return 'No transactions found.';
            $c = json_decode($t['customer_info'] ?? '{}', true);
            return "*Last Transaction*

ID: `{$t['ref']}`
Amount: `{$t['amount']} {$t['currency']}`
Name: ".($c['name']??'N/A')."
Status: ".ucfirst($t['status'])."
Date: {$t['created_date']}";
        case '/pending_transactions':
            $cnt = tgnp_db_val($pdo, "SELECT COUNT(*) FROM {$pfx}transaction WHERE status='pending'");
            return "*Pending Transactions:* `{$cnt}`";
        case '/failed_transactions':
            $cnt = tgnp_db_val($pdo, "SELECT COUNT(*) FROM {$pfx}transaction WHERE status='failed'");
            return "*Failed Transactions:* `{$cnt}`";
        case '/completed_transactions':
            $cnt = tgnp_db_val($pdo, "SELECT COUNT(*) FROM {$pfx}transaction WHERE status='completed'");
            return "*Completed Transactions:* `{$cnt}`";
        default:
            return "Unknown command. Type /help for all commands.";
    }
}
WEBHOOKPHP;

        @file_put_contents($wh_file, $php);

        // Also write a .htaccess inside tgnp-webhook/ to ensure index.php is served
        $htaccess = "DirectoryIndex index.php
Options -Indexes
";
        @file_put_contents(dirname($wh_file) . '/.htaccess', $htaccess);
    }

    // ================================================================
    // LOGGING
    // ================================================================
    private function siteRoot(): string
    {
        // class.php: <root>/pp-content/pp-modules/pp-addons/<slug>/class.php
        // dirname x1 = <slug> dir
        // dirname x2 = pp-addons
        // dirname x3 = pp-modules
        // dirname x4 = pp-content
        // dirname x5 = <root>
        static $root = null;
        if ($root === null) {
            $root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
        }
        return $root;
    }

    private function writeLog(array $lines): void
    {
        $root = $this->siteRoot();
        $msg  = implode("\n", $lines) . "\n---\n";
        foreach ([
            $root . '/storage/media/',
            $root . '/pp-media/',
            sys_get_temp_dir() . '/',
            dirname(dirname(__FILE__)) . '/',  // addon dir fallback
        ] as $dir) {
            if (@is_dir($dir) && @is_writable($dir)) {
                @file_put_contents($dir . 'tgnp-debug.log', $msg, FILE_APPEND | LOCK_EX);
                return;
            }
        }
        @error_log('TGNP: ' . implode(' | ', $lines));
    }

    // ================================================================
    // TELEGRAM API
    // ================================================================
    public function callTelegram(string $token, string $method, array $params = []): array
    {
        $url = 'https://api.telegram.org/bot' . $token . '/' . $method;
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res ?: '{}', true) ?: [];
    }

    public function registerBotCommands(string $token): void
    {
        $commands = [
            ['command' => '/start',                 'description' => 'Get your Chat ID'],
            ['command' => '/last_transaction',       'description' => 'Most recent transaction'],
            ['command' => '/sales_today',            'description' => "Today's sales"],
            ['command' => '/sales_yesterday',        'description' => "Yesterday's sales"],
            ['command' => '/sales_this_month',       'description' => "This month's sales"],
            ['command' => '/pending_transactions',   'description' => 'Pending count'],
            ['command' => '/failed_transactions',    'description' => 'Failed count'],
            ['command' => '/completed_transactions', 'description' => 'Completed count'],
            ['command' => '/help',                   'description' => 'Show all commands'],
        ];
        $this->callTelegram($token, 'setMyCommands', ['commands' => json_encode($commands)]);
    }

    // ================================================================
    // HUB / NODE
    // ================================================================
    public function signPayload(array $payload, string $secret): string
    {
        ksort($payload);
        return hash_hmac('sha256', json_encode($payload), $secret);
    }

    public function sendSignedRequest(string $url, string $action, array $data, string $secret): array
    {
        $payload   = array_merge(['action' => $action], $data);
        $signature = $this->signPayload($payload, $secret);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'payload'   => json_encode($payload),
                'signature' => $signature,
                'tgnp_hub'  => 'true',
            ]),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode($res ?: '{}', true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : ['status' => false, 'message' => 'Invalid JSON from hub.'];
    }

    // ================================================================
    // UPDATES
    // ================================================================
    public function checkGithubUpdates(): ?array
    {
        $ch = curl_init('https://api.github.com/repos/' . self::GITHUB_REPO . '/releases/latest');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_USERAGENT => 'PipraPay-Addon-Updater']);
        $res  = curl_exec($ch); curl_close($ch);
        $data = json_decode($res ?: '{}', true);
        if (!isset($data['tag_name'])) return null;
        $version = ltrim($data['tag_name'], 'v');
        $dl_url  = '';
        foreach ($data['assets'] ?? [] as $asset) {
            if (str_ends_with($asset['name'], '.zip')) { $dl_url = $asset['browser_download_url']; break; }
        }
        if (empty($dl_url)) $dl_url = $data['zipball_url'] ?? '';
        $cl = $data['body'] ?? '';
        $cl = preg_replace('/^### (.*)$/m', '<h5>$1</h5>', $cl);
        $cl = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $cl);
        $cl = preg_replace('/^[-*]\s+(.*)$/m', '<li>$1</li>', $cl);
        $cl = nl2br($cl);
        return ['new_version' => $version, 'download_url' => $dl_url, 'changelog' => $cl];
    }

    public function installUpdateZip(string $url): bool|string
    {
        if (!class_exists('ZipArchive')) return 'ZipArchive not available.';
        $dir     = __DIR__;
        $bk_dir  = dirname($dir, 3) . '/backups/tgnp/';
        @mkdir($bk_dir, 0755, true);
        $tmp_zip = $bk_dir . 'tgnp-update.zip';
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_USERAGENT => 'PipraPay-Addon-Updater']);
        $data = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($code !== 200 || $data === false) return 'Download failed (HTTP ' . $code . ').';
        if (file_put_contents($tmp_zip, $data) === false) return 'Cannot write temp zip.';
        $this->deleteDirectory($dir); @mkdir($dir, 0755, true);
        $zip = new ZipArchive();
        if ($zip->open($tmp_zip) !== true) { @unlink($tmp_zip); return 'Cannot open zip.'; }
        $root = $zip->getNameIndex(0);
        if (substr_count($root, '/') === 1 && str_ends_with($root, '/')) {
            $tmp_ex = $bk_dir . 'tgnp-extract/';
            $this->deleteDirectory($tmp_ex); $zip->extractTo($tmp_ex); $zip->close();
            foreach (scandir($tmp_ex . $root) as $f) {
                if ($f === '.' || $f === '..') continue;
                @rename($tmp_ex . $root . $f, $dir . '/' . $f);
            }
            $this->deleteDirectory($tmp_ex);
        } else { $zip->extractTo($dir); $zip->close(); }
        @unlink($tmp_zip);
        return true;
    }

    public function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        return rmdir($dir);
    }
}
