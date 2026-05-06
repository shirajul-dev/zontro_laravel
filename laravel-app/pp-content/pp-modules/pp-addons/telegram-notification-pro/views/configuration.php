<?php
/**
 * Telegram Bot Notification Pro — Configuration View (V3)
 * Variables available from configuration(): all locals set before include
 */
if (!defined('PipraPay_INIT')) { http_response_code(403); exit; }
?>

<div class="tgnp-wrap">

<?php if (!empty($last_error)): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <strong>Error:</strong> <?php echo htmlspecialchars($last_error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($test_result)): ?>
<div class="alert alert-<?php echo str_starts_with($test_result, '✅') ? 'success' : 'warning'; ?> alert-dismissible fade show mb-3">
    <?php echo htmlspecialchars($test_result); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div id="tgnp-ajax-response" class="mb-3"></div>

<!-- ═══════════════════════════════════════════════════════
     1. OPERATION MODE & IDENTITY
═══════════════════════════════════════════════════════ -->
<div class="card mb-3">
    <div class="card-header"><h4 class="card-title mb-0">1. Operation Mode &amp; Identity</h4></div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-bold">Select Mode</label>
            <select class="form-select" id="tgnp-mode-select" name="operation_mode_display">
                <option value="standalone" <?php echo $operation_mode === 'standalone' ? 'selected' : ''; ?>>👤 Standalone (Standard)</option>
                <option value="hub"        <?php echo $operation_mode === 'hub'        ? 'selected' : ''; ?>>👑 Controller (Hub)</option>
                <option value="node"       <?php echo $operation_mode === 'node'       ? 'selected' : ''; ?>>🔗 Connected Node (Client)</option>
            </select>
            <div class="form-text" id="tgnp-mode-desc"></div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Site Name</label>
                <input type="text" class="form-control" id="tgnp-site-name" value="<?php echo htmlspecialchars($site_name); ?>" placeholder="My Store">
                <div class="form-text">Shown at the bottom of every notification.</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Site Identifier (Slug)</label>
                <input type="text" class="form-control" id="tgnp-site-identifier" value="<?php echo htmlspecialchars($site_identifier); ?>" pattern="[a-zA-Z0-9_]+" placeholder="store_1">
                <div class="form-text">Unique ID — letters, numbers, underscore only.</div>
            </div>
        </div>

        <div class="mb-3" id="tgnp-secret-container" style="display:none">
            <label class="form-label">API Secret Key</label>
            <div class="input-group">
                <input type="text" class="form-control font-monospace" id="tgnp-api-secret" value="<?php echo htmlspecialchars($api_secret); ?>">
                <button class="btn btn-outline-secondary" type="button" onclick="tgnpGenerateSecret()">Generate New</button>
            </div>
            <div class="form-text tgnp-hub-only">⚠️ Copy this key to your Node sites.</div>
            <div class="form-text tgnp-node-only">🔑 Paste the Hub's secret key here.</div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-sm" onclick="tgnpSaveIdentity(this)">Save Identity Settings</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     2. BOT SETTINGS
═══════════════════════════════════════════════════════ -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">2. Bot Settings</h4>
        <?php if (!empty($bot_token)): ?>
            <span class="badge bg-success">Connected</span>
        <?php endif; ?>
    </div>
    <div class="card-body">

        <?php if (empty($bot_token)): ?>
        <!-- Connect form -->
        <div id="tgnp-connect-form">
            <div class="mb-3">
                <label class="form-label">Telegram Bot Token</label>
                <input type="text" class="form-control font-monospace" id="tgnp-bot-token-input" placeholder="123456789:AAXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX">
                <div class="form-text">Get one from <a href="https://t.me/BotFather" target="_blank">@BotFather</a> on Telegram.</div>
            </div>
            <button type="button" class="btn btn-primary" onclick="tgnpConnectBot(this)">Connect Bot</button>
        </div>
        <?php else: ?>
        <!-- Connected status -->
        <div class="alert alert-light border mb-3">
            <div class="row g-2">
                <div class="col-sm-3 text-muted">Bot:</div>
                <div class="col-sm-9"><strong>@<?php echo htmlspecialchars($bot_username); ?></strong></div>
                <div class="col-sm-3 text-muted">Webhook:</div>
                <div class="col-sm-9"><small class="font-monospace text-break"><?php echo htmlspecialchars($webhook_status); ?></small></div>
            </div>
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Disconnect bot and remove webhook?')) tgnpDisconnectBot(this)">Disconnect / Reset Token</button>

        <!-- Test message -->
        <hr class="my-3">
        <div class="row g-2 align-items-end">
            <div class="col">
                <label class="form-label">Send Test Message</label>
                <input type="text" class="form-control" id="tgnp-test-chat-id" placeholder="Chat ID">
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-secondary" onclick="tgnpSendTest(this)">Send Test</button>
            </div>
        </div>

        <!-- Connected nodes (hub mode) -->
        <?php if ($operation_mode === 'hub' && !empty($connected_nodes)): ?>
        <hr class="my-3">
        <h6>🔗 Connected Nodes</h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead><tr><th>Site</th><th>Identifier</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($connected_nodes as $node): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($node['site_name']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($node['site_url']); ?></small></td>
                    <td><code><?php echo htmlspecialchars($node['site_identifier']); ?></code></td>
                    <td><button class="btn btn-sm btn-outline-danger" onclick="tgnpRemoveNode('<?php echo htmlspecialchars($node['site_identifier']); ?>', this)">Remove</button></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     3. CONNECT TO HUB (node mode only)
═══════════════════════════════════════════════════════ -->
<div class="card mb-3 tgnp-node-section" style="display:none">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">3. Connect to Hub</h4>
        <?php if ($hub_status === 'Connected'): ?>
            <span class="badge bg-success">✅ Connected</span>
        <?php else: ?>
            <span class="badge bg-light text-dark border">Not Connected</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($hub_status === 'Connected'): ?>
        <div class="alert alert-success mb-3">
            <strong>Connected to:</strong> <code><?php echo htmlspecialchars($hub_url); ?></code>
        </div>
        <?php endif; ?>
        <div class="mb-3">
            <label class="form-label">Hub Site URL</label>
            <input type="url" class="form-control" id="tgnp-hub-url" value="<?php echo htmlspecialchars($hub_url); ?>" placeholder="https://main-site.com">
        </div>
        <div class="mb-3">
            <label class="form-label">Hub API Secret</label>
            <input type="text" class="form-control font-monospace" id="tgnp-hub-secret" value="<?php echo htmlspecialchars($api_secret); ?>">
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn <?php echo $hub_status === 'Connected' ? 'btn-outline-success' : 'btn-success'; ?>" onclick="tgnpRegisterNode(this)">
                <?php echo $hub_status === 'Connected' ? '🔄 Update Connection' : '🔗 Connect to Hub'; ?>
            </button>
            <?php if ($hub_status === 'Connected'): ?>
            <button type="button" class="btn btn-danger" onclick="if(confirm('Disconnect from hub?')) tgnpDisconnectHub(this)">Disconnect</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     4. NOTIFICATION RULES
═══════════════════════════════════════════════════════ -->
<div class="card mb-3">
    <div class="card-header"><h4 class="card-title mb-0">4. Notification Rules</h4></div>
    <div class="card-body">

        <div class="form-check form-switch mb-3">
            <input class="form-check-input tgnp-checkbox" type="checkbox" id="cb-notifications-enabled"
                <?php echo ($options['notifications_enabled'] ?? 'true') === 'true' ? 'checked' : ''; ?>>
            <input type="hidden" id="h-notifications-enabled" value="<?php echo ($options['notifications_enabled'] ?? 'true') === 'true' ? 'true' : 'false'; ?>">
            <label class="form-check-label fw-bold" for="cb-notifications-enabled">Enable All Notifications</label>
        </div>

        <div class="d-flex flex-wrap gap-4 mb-3">
            <?php foreach (['pending' => 'Pending', 'completed' => 'Completed', 'failed' => 'Failed', 'refunded' => 'Refunded', 'cancelled' => 'Cancelled'] as $key => $label): ?>
            <div class="form-check form-switch">
                <input class="form-check-input tgnp-checkbox" type="checkbox" id="cb-notify-<?php echo $key; ?>"
                    <?php echo ($options['notify_' . $key] ?? 'true') === 'true' ? 'checked' : ''; ?>>
                <input type="hidden" id="h-notify-<?php echo $key; ?>" value="<?php echo ($options['notify_' . $key] ?? 'true') === 'true' ? 'true' : 'false'; ?>">
                <label class="form-check-label" for="cb-notify-<?php echo $key; ?>"><?php echo $label; ?></label>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="form-check form-switch mb-4">
            <input class="form-check-input tgnp-checkbox" type="checkbox" id="cb-enable-confirm"
                <?php echo ($options['enable_confirm_button'] ?? 'false') === 'true' ? 'checked' : ''; ?>>
            <input type="hidden" id="h-enable-confirm" value="<?php echo ($options['enable_confirm_button'] ?? 'false') === 'true' ? 'true' : 'false'; ?>">
            <label class="form-check-label fw-bold" for="cb-enable-confirm">Enable "Confirm Transaction" Button on Pending</label>
        </div>

        <hr>
        <h6>Recipient Chat IDs</h6>
        <div id="tgnp-chat-ids-container">
            <?php foreach ($chat_ids as $i => $chat): ?>
            <div class="row g-2 align-items-center mb-2 tgnp-chat-row">
                <div class="col"><input type="text" class="form-control" placeholder="Chat ID" value="<?php echo htmlspecialchars($chat['id'] ?? ''); ?>"></div>
                <div class="col"><input type="text" class="form-control" placeholder="Name (optional)" value="<?php echo htmlspecialchars($chat['name'] ?? ''); ?>"></div>
                <div class="col-auto">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" <?php echo ($chat['enabled'] ?? 'false') === 'true' ? 'checked' : ''; ?>>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="$(this).closest('.tgnp-chat-row').remove()" title="Remove">✕</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="tgnpAddChatRow()">
            <i class="bi-plus"></i> Add Chat ID
        </button>

        <hr>
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary" onclick="tgnpSaveNotificationSettings(this)">Save Notification Settings</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     5. MESSAGE TEMPLATES
═══════════════════════════════════════════════════════ -->
<div class="card mb-3">
    <div class="card-header"><h4 class="card-title mb-0">5. Message Templates</h4></div>
    <div class="card-body">
        <p class="text-muted small">Placeholders: <code>{amount} {currency} {customer_name} {payment_method} {sender_number} {date} {payment_id} {gateway_trx_id} {status}</code></p>
        <?php
        // Show a warning if templates contain raw unicode escapes from the previous buggy version
        $broken = false;
        foreach ($templates as $tpl) { if (strpos($tpl, '\u') !== false) { $broken = true; break; } }
        if ($broken): ?>
        <div class="alert alert-warning py-2">
            ⚠️ Your templates contain raw unicode escape sequences (e.g. <code>\u2705</code>) from a previous version.
            Click <strong>Reset to Defaults</strong> below to fix them.
        </div>
        <?php endif; ?>
        <?php foreach (['completed' => 'Completed ✅', 'pending' => 'Pending ⏳', 'failed' => 'Failed ❌', 'refunded' => 'Refunded 🔄', 'cancelled' => 'Cancelled 🚫'] as $key => $label): ?>
        <div class="mb-3">
            <label class="form-label"><?php echo $label; ?></label>
            <textarea class="form-control font-monospace" id="tpl-<?php echo $key; ?>" rows="4"><?php echo htmlspecialchars($templates[$key]); ?></textarea>
        </div>
        <?php endforeach; ?>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" onclick="tgnpSaveTemplates(this)">Save Templates</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="if(confirm('Reset all templates to defaults?')) tgnpResetTemplates(this)">Reset to Defaults</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     6. BOT COMMANDS REFERENCE
═══════════════════════════════════════════════════════ -->
<div class="card mb-3">
    <div class="card-header"><h4 class="card-title mb-0">Available Bot Commands</h4></div>
    <div class="card-body">
        <p class="text-muted small">Send these commands to your bot in Telegram to get instant responses.</p>
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><code>/start</code> — Get your Chat ID</li>
            <li class="list-group-item"><code>/sales_today</code> — Today's total sales</li>
            <li class="list-group-item"><code>/sales_yesterday</code> — Yesterday's sales</li>
            <li class="list-group-item"><code>/sales_this_month</code> — This month's sales</li>
            <li class="list-group-item"><code>/last_transaction</code> — Most recent transaction</li>
            <li class="list-group-item"><code>/pending_transactions</code> — Count of pending</li>
            <li class="list-group-item"><code>/failed_transactions</code> — Count of failed</li>
            <li class="list-group-item"><code>/completed_transactions</code> — Count of completed</li>
            <li class="list-group-item"><code>/help</code> — Show all commands</li>
        </ul>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     7. UPDATES & DEVELOPER
═══════════════════════════════════════════════════════ -->
<div class="row">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><h4 class="card-title mb-0">Plugin Updates</h4></div>
            <div class="card-body">
                <p class="text-muted small">Check for new versions. Updates install directly.</p>
                <button type="button" class="btn btn-secondary" onclick="tgnpCheckUpdates(this)">Check for Updates</button>
                <div id="tgnp-update-response" class="mt-3"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><h4 class="card-title mb-0">Developer</h4></div>
            <div class="card-body">
                <p class="mb-1"><strong>Refat Rahman</strong></p>
                <a href="https://github.com/refatbd/" target="_blank" class="me-3"><i class="bi-github"></i> GitHub</a>
                <a href="https://www.facebook.com/rjrefat" target="_blank"><i class="bi-facebook"></i> Facebook</a>
            </div>
        </div>
    </div>
</div>

<!-- CLOUDFLARE NOTICE -->
<?php if (!empty($options['webhook_secret'])): ?>
<div class="card mb-3 border-warning">
    <div class="card-header bg-warning bg-opacity-10">
        <h6 class="mb-0">⚠️ Cloudflare: Whitelist Webhook URL to enable bot commands</h6>
    </div>
    <div class="card-body">
        <p class="small mb-2">Cloudflare is blocking Telegram from reaching your webhook. Add a <strong>Bypass</strong> rule in Cloudflare for this URL:</p>

        <p class="small fw-bold mb-1">Step 1 — Copy your webhook URL:</p>
        <?php
            global $site_url;
            $wh_display = rtrim($site_url, '/') . '/?page=tgnp-webhook&s=' . $options['webhook_secret'];
        ?>
        <div class="input-group mb-3">
            <input type="text" class="form-control font-monospace" id="tgnp-wh-url-copy" value="<?php echo htmlspecialchars($wh_display); ?>" readonly>
            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('tgnp-wh-url-copy').value).then(()=>{ this.textContent='Copied!'; setTimeout(()=>{ this.textContent='Copy'; },2000); })">Copy</button>
        </div>

        <p class="small fw-bold mb-1">Step 2 — Add a WAF Bypass rule in Cloudflare:</p>
        <ol class="small mb-2">
            <li>Go to <strong>Cloudflare → your site → Security → WAF</strong></li>
            <li>Click <strong>Custom Rules → Create Rule</strong></li>
            <li>Set: <em>Field</em> = <strong>URI Full</strong>, <em>Operator</em> = <strong>contains</strong>, <em>Value</em> = <code>tgnp-webhook</code></li>
            <li>Action = <strong>Skip → All remaining custom rules</strong> (or "Allow")</li>
            <li>Save and deploy</li>
        </ol>

        <p class="small mb-0 text-muted">After adding the rule, click <strong>Re-register Webhook</strong> in Bot Settings above to reconnect.</p>
    </div>
</div>
<?php endif; ?>

</div><!-- /.tgnp-wrap -->

<script>
(function($) {
    'use strict';

    var DASH_URL = <?php echo json_encode($dashboard_url); ?>;
    var ADDON_ID = <?php echo json_encode($addon_db_id); ?>;

    function getCsrf() { return $('input[name="csrf_token"]').first().val() || $('input[name="csrf_token_default"]').first().val(); }
    function updateCsrf(t) { if (t) { $('input[name="csrf_token"], input[name="csrf_token_default"]').val(t); } }

    function showAlert(msg, ok) {
        var cls = ok ? 'alert-success' : 'alert-danger';
        $('#tgnp-ajax-response').html('<div class="alert ' + cls + ' alert-dismissible fade show">' + msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        window.scrollTo(0, 0);
    }

    // Sync checkboxes to hidden fields
    $(document).on('change', '.tgnp-checkbox', function() {
        var hid = $(this).attr('id').replace('cb-', 'h-');
        $('#' + hid).val($(this).is(':checked') ? 'true' : 'false');
    });

    // Post to adapter (addon-configuration-update)
    function addonPost(extraData, btn, callback) {
        var orig = btn ? $(btn).html() : '';
        if (btn) { $(btn).html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true); }

        var data = $.extend({
            'action'     : 'addon-configuration-update',
            'addon-id'   : ADDON_ID,
            'csrf_token' : getCsrf()
        }, extraData);

        $.ajax({
            url: DASH_URL, type: 'POST', dataType: 'json', data: data,
            success: function(r) {
                updateCsrf(r.csrf_token);
                if (callback) callback(r);
                else showAlert(r.message || (r.status === 'true' ? 'Saved.' : 'Error.'), r.status === 'true');
            },
            error: function() { showAlert('Request failed. Please try again.', false); },
            complete: function() { if (btn) { $(btn).html(orig).prop('disabled', false); } }
        });
    }

    // Post tgnp_action (processed in shutdown handler)
    function tgnpPost(tgnp_action, extraData, btn, callback) {
        var data = $.extend({ 'tgnp_action': tgnp_action }, extraData);
        addonPost(data, btn, callback);
    }

    // ── Identity ───────────────────────────────────────────
    window.tgnpSaveIdentity = function(btn) {
        addonPost({
            'operation_mode' : $('#tgnp-mode-select').val(),
            'site_name'      : $('#tgnp-site-name').val(),
            'site_identifier': $('#tgnp-site-identifier').val(),
            'api_secret'     : $('#tgnp-api-secret').val()
        }, btn, function(r) {
            showAlert(r.message || (r.status === 'true' ? 'Identity settings saved.' : 'Error saving.'), r.status === 'true');
        });
    };

    window.tgnpGenerateSecret = function() {
        var arr = new Uint8Array(16);
        crypto.getRandomValues(arr);
        var hex = Array.from(arr, function(b) { return b.toString(16).padStart(2,'0'); }).join('');
        $('#tgnp-api-secret, #tgnp-hub-secret').val(hex);
    };

    // ── Bot connect / disconnect ───────────────────────────
    window.tgnpConnectBot = function(btn) {
        var token = $('#tgnp-bot-token-input').val().trim();
        if (!token) { showAlert('Please enter a bot token.', false); return; }
        tgnpPost('save_bot_token', { 'bot_token': token }, btn, function(r) {
            if (r.status === 'true') {
                showAlert('Bot connected! Refreshing…', true);
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                showAlert(r.message || 'Connection failed.', false);
            }
        });
    };

    window.tgnpDisconnectBot = function(btn) {
        tgnpPost('delete_bot_token', {}, btn, function(r) {
            if (r.status === 'true') location.reload();
            else showAlert(r.message || 'Error disconnecting.', false);
        });
    };

    // ── Test message ───────────────────────────────────────
    window.tgnpSendTest = function(btn) {
        var cid = $('#tgnp-test-chat-id').val().trim();
        if (!cid) { showAlert('Enter a Chat ID first.', false); return; }
        tgnpPost('send_test_message', { 'tgnp_test_chat_id': cid }, btn, function(r) {
            showAlert(r.status === 'true' ? '✅ Test message sent to ' + cid : '❌ ' + (r.message || 'Failed'), r.status === 'true');
        });
    };

    // ── Hub / Node ─────────────────────────────────────────
    window.tgnpRegisterNode = function(btn) {
        addonPost({
            'tgnp_action'    : 'register_node',
            'hub_url'        : $('#tgnp-hub-url').val().trim(),
            'api_secret'     : $('#tgnp-hub-secret').val().trim(),
            'site_name'      : $('#tgnp-site-name').val(),
            'site_identifier': $('#tgnp-site-identifier').val()
        }, btn, function(r) {
            if (r.status === 'true') {
                showAlert('Connected to hub! Refreshing…', true);
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                showAlert(r.message || 'Connection failed.', false);
            }
        });
    };

    window.tgnpDisconnectHub = function(btn) {
        tgnpPost('disconnect_hub', {}, btn, function(r) {
            if (r.status === 'true') location.reload();
            else showAlert(r.message || 'Error.', false);
        });
    };

    window.tgnpRemoveNode = function(site_id, btn) {
        tgnpPost('remove_node', { 'remove_node_id': site_id }, btn, function(r) {
            if (r.status === 'true') location.reload();
        });
    };

    // ── Notification Settings ──────────────────────────────
    window.tgnpSaveNotificationSettings = function(btn) {
        // Collect chat IDs
        var chatIds = [];
        $('.tgnp-chat-row').each(function() {
            var inputs = $(this).find('input[type="text"]');
            var id   = $(inputs[0]).val().trim();
            var name = $(inputs[1]).val().trim();
            var ena  = $(this).find('input[type="checkbox"]').is(':checked') ? 'true' : 'false';
            if (id) chatIds.push({ id: id, name: name || id, enabled: ena });
        });

        addonPost({
            'notifications_enabled' : $('#h-notifications-enabled').val(),
            'notify_pending'        : $('#h-notify-pending').val(),
            'notify_completed'      : $('#h-notify-completed').val(),
            'notify_failed'         : $('#h-notify-failed').val(),
            'notify_refunded'       : $('#h-notify-refunded').val(),
            'notify_cancelled'      : $('#h-notify-cancelled').val(),
            'enable_confirm_button' : $('#h-enable-confirm').val(),
            'chat_ids_json'         : JSON.stringify(chatIds)
        }, btn, function(r) {
            showAlert(r.status === 'true' ? '✅ Notification settings saved.' : '❌ ' + (r.message || 'Error.'), r.status === 'true');
        });
    };

    window.tgnpAddChatRow = function() {
        $('#tgnp-chat-ids-container').append(
            '<div class="row g-2 align-items-center mb-2 tgnp-chat-row">' +
            '<div class="col"><input type="text" class="form-control" placeholder="Chat ID"></div>' +
            '<div class="col"><input type="text" class="form-control" placeholder="Name (optional)"></div>' +
            '<div class="col-auto"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" checked></div></div>' +
            '<div class="col-auto"><button type="button" class="btn btn-outline-danger btn-sm" onclick="$(this).closest(\'.tgnp-chat-row\').remove()" title="Remove">✕</button></div>' +
            '</div>'
        );
    };

    // ── Templates ──────────────────────────────────────────
    window.tgnpSaveTemplates = function(btn) {
        addonPost({
            'template_completed': $('#tpl-completed').val(),
            'template_pending'  : $('#tpl-pending').val(),
            'template_failed'   : $('#tpl-failed').val(),
            'template_refunded' : $('#tpl-refunded').val(),
            'template_cancelled': $('#tpl-cancelled').val()
        }, btn, function(r) {
            showAlert(r.status === 'true' ? '✅ Templates saved.' : '❌ ' + (r.message || 'Error.'), r.status === 'true');
        });
    };

    window.tgnpResetTemplates = function(btn) {
        tgnpPost('reset_templates', {}, btn, function(r) {
            if (r.status === 'true') { showAlert('Templates reset. Refreshing…', true); setTimeout(function() { location.reload(); }, 1000); }
        });
    };

    // ── Updates ────────────────────────────────────────────
    window.tgnpCheckUpdates = function() {
        window.open('https://github.com/refatbd/PipraPay-bot-notification-pro/releases', '_blank');
    };

    // ── Mode UI toggle ─────────────────────────────────────
    function applyModeUI() {
        var mode = $('#tgnp-mode-select').val();
        $('.tgnp-hub-only, .tgnp-node-only, .tgnp-node-section').hide();
        $('#tgnp-secret-container').hide();

        if (mode === 'standalone') {
            $('#tgnp-mode-desc').text('This site manages the bot directly.');
        } else if (mode === 'hub') {
            $('#tgnp-secret-container, .tgnp-hub-only').show();
            $('#tgnp-mode-desc').text('Hub: receives webhooks and forwards to connected nodes.');
        } else if (mode === 'node') {
            $('#tgnp-secret-container, .tgnp-node-only, .tgnp-node-section').show();
            $('#tgnp-mode-desc').text('Node: sends through the hub. Webhook lives on the hub.');
        }
    }

    $('#tgnp-mode-select').on('change', applyModeUI);
    applyModeUI();

}(jQuery));
</script>
