<?php
/*
 * Plugin Name: PipraPay Gateway
 * Plugin URI: https://piprapay.com
 * Description: A seamless and secure payment gateway integration for WooCommerce using PipraPay.
 * Author: PipraPay
 * Version: 1.0.4
 * Requires at least: 5.2
 * Requires PHP: 7.4
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: piprapay
 * Contributors: piprapay
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (isset($_GET['wc-api']) && $_GET['wc-api'] === 'piprapay') {
    if (isset($_GET['debug_all'])) {
        die('PARAMS: ' . json_encode($_GET));
    }
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'piprapay_plugin_action_links');
function piprapay_plugin_action_links($links)
{
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=piprapay') . '">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Declare compatibility with HPOS and Blocks
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

// Initialize the gateway class
add_action('plugins_loaded', 'piprapay_init_gateway_class');

// Ensure WC_API is registered early and globally
add_action('woocommerce_api_piprapay', function() {
    if (function_exists('piprapay_init_gateway_class')) {
        piprapay_init_gateway_class(); // Ensure class is defined
    }
    if (class_exists('WC_PIPRAPAY_Gateway')) {
        WC_PIPRAPAY_Gateway::get_instance()->handle_webhook();
        exit;
    } else {
        die('-1 (PipraPay Class Missing)');
    }
});

function piprapay_init_gateway_class()
{
    if (class_exists('WC_PIPRAPAY_Gateway')) {
        return;
    }
    if (!class_exists('WC_Payment_Gateway')) {
        return; // Exit if WooCommerce isn’t active
    }

    class WC_PIPRAPAY_Gateway extends WC_Payment_Gateway
    {
        private static $instance = null;

        public $show_icon;
        public $apikey;
        public $baseUrl;
        public $order_type;
        public $piprapay_version;
        public $debug;

        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            $this->id = 'piprapay';
            $this->has_fields = false;
            $this->method_title = __('PipraPay', 'piprapay-gateway');
            $this->method_description = __('Secure and fast payments via PipraPay.', 'piprapay-gateway');
            $this->supports = ['products'];

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title') ?: __('PipraPay', 'piprapay-gateway');
            $this->description = $this->get_option('description') ?: __('Pay securely via PipraPay.', 'piprapay-gateway');
            $this->enabled = $this->get_option('enabled');
            $this->show_icon   = $this->get_option('show_icon') === 'yes';

            $this->icon = '';
            if ($this->show_icon) {
                $this->icon = $this->get_option('logo_url');
                if (empty($this->icon)) $this->icon = plugins_url('assets/icon.png', __FILE__);
            }

            $this->apikey = sanitize_text_field($this->get_option('apikey'));
            $this->baseUrl = sanitize_text_field($this->get_option('baseUrl'));
            $this->order_type = sanitize_text_field($this->get_option('order_type'));
            $this->piprapay_version = sanitize_text_field($this->get_option('piprapay_version'));
            $this->debug = true;

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('woocommerce_api_' . strtolower($this->id), [$this, 'handle_webhook']);
            add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'display_piprapay_order_meta'], 10, 1);
        }

        public function log($message)
        {
            error_log('PipraPay Log: ' . $message);
            if ($this->debug) {
                $logger = wc_get_logger();
                $context = ['source' => 'piprapay-gateway'];
                $logger->debug($message, $context);
            }
        }

        public function init_form_fields()
        {
            $this->form_fields = [
                'enabled' => [
                    'title' => __('Enable/Disable', 'piprapay-gateway'),
                    'type' => 'checkbox',
                    'label' => __('Enable PipraPay Gateway', 'piprapay-gateway'),
                    'default' => 'no',
                ],
                'title' => [
                    'title' => __('Title', 'piprapay-gateway'),
                    'type' => 'text',
                    'description' => __('The title displayed at checkout.', 'piprapay-gateway'),
                    'default' => __('PipraPay', 'piprapay-gateway'),
                    'desc_tip' => true,
                ],
                'description' => [
                    'title' => __('Description', 'piprapay-gateway'),
                    'type' => 'textarea',
                    'description' => __('The description displayed at checkout.', 'piprapay-gateway'),
                    'default' => __('Pay securely via PipraPay.', 'piprapay-gateway'),
                    'desc_tip' => true,
                ],
                'logo_url' => [
                    'title' => __('Logo URL', 'piprapay-gateway'),
                    'type' => 'text',
                    'description' => __('Enter the URL of the logo to display at checkout.', 'piprapay-gateway'),
                    'default' => '',
                    'desc_tip' => true,
                ],
                'show_icon' => [
                    'title'   => __('Show Icon', 'piprapay-gateway'),
                    'type'    => 'checkbox',
                    'label'   => __('Display icon on checkout page', 'piprapay-gateway'),
                    'default' => 'yes',
                ],
                'order_type' => [
                    'title'       => __('Order Type', 'piprapay-gateway'),
                    'type'        => 'select',
                    'description' => __('Choose how the order should be handled after payment.', 'piprapay-gateway'),
                    'desc_tip'    => true,
                    'default'     => 'physical',
                    'options'     => [
                        'physical'            => __('Physical Product (Set to processing)', 'piprapay-gateway'),
                        'digital_processing'  => __('Digital Product (Set to processing)', 'piprapay-gateway'),
                        'digital_complete'    => __('Digital Product (Auto complete)', 'piprapay-gateway'),
                    ],
                ],
                'piprapay_version' => [
                    'title'       => __('PipraPay Panel Version', 'piprapay-gateway'),
                    'type'        => 'select',
                    'description' => __('Select the version of the PipraPay panel you are using.', 'piprapay-gateway'),
                    'desc_tip'    => true,
                    'default'     => 'new',
                    'options'     => [
                        'new' => __('Version 3.0 and above', 'piprapay-gateway'),
                        'old' => __('Version below 3.0', 'piprapay-gateway'),
                    ],
                ],
                'apikey' => [
                    'title' => __('API Key', 'piprapay-gateway'),
                    'type' => 'password',
                    'description' => __('Your PipraPay API key.', 'piprapay-gateway'),
                    'default' => '',
                    'desc_tip' => true,
                ],
                'baseUrl' => [
                    'title' => __('Base URL', 'piprapay-gateway'),
                    'type' => 'text',
                    'description' => __('Your PipraPay Base URL.', 'piprapay-gateway'),
                    'default' => '',
                    'desc_tip' => true,
                ],
                'debug' => [
                    'title' => __('Debug Mode', 'piprapay-gateway'),
                    'type' => 'checkbox',
                    'label' => __('Enable logging', 'piprapay-gateway'),
                    'default' => 'no',
                    'description' => __('Log API requests and responses to WooCommerce logs.', 'piprapay-gateway'),
                ],
            ];
        }

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            if (!$order) {
                wc_add_notice(__('Order not found.', 'piprapay-gateway'), 'error');
                return ['result' => 'fail'];
            }

            if ($this->piprapay_version == 'old') {
                $data = [
                    'full_name'    => sanitize_text_field(trim(($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: 'Jhon')),
                    'email_mobile' => sanitize_email($order->get_billing_email() ?: 'jhon@gmail.com'),
                    'amount'       => (string) $order->get_total(),
                    'metadata'     => ['invoiceid' => (string) $order->get_id()],
                    'redirect_url' => $this->get_return_url($order),
                    'cancel_url'   => wc_get_checkout_url(),
                    'webhook_url'  => WC()->api_request_url(strtolower($this->id)),
                    'return_type'  => 'POST',
                    'currency'     => $order->get_currency(),
                ];

                $args = [
                    'body'    => json_encode($data),
                    'headers' => [
                        'Content-Type'           => 'application/json',
                        'Accept'                 => 'application/json',
                        'mh-piprapay-api-key'    => $this->apikey,
                    ],
                    'timeout' => 45,
                ];

                $this->log('Create Charge Request (Old API): ' . print_r($data, true));

                $response = wp_remote_post($this->baseUrl . '/create-charge', $args);

                if (is_wp_error($response)) {
                    $this->log('Create Charge Error (Old API): ' . $response->get_error_message());
                    wc_add_notice(__('Payment error: ', 'piprapay-gateway') . $response->get_error_message(), 'error');
                    return ['result' => 'fail'];
                }

                $body = wp_remote_retrieve_body($response);
                $this->log('Create Charge Response (Old API): ' . $body);

                $result = json_decode($body, true);

                if (isset($result['pp_url'])) {
                    return [
                        'result'   => 'success',
                        'redirect' => esc_url($result['pp_url']),
                    ];
                }

                $message = !empty($result['message']) ? esc_html($result['message']) : __('Unknown error.', 'piprapay-gateway');
                wc_add_notice(sprintf(__('Payment error: Unable to create payment link. %s', 'piprapay-gateway'), $message), 'error');
                return ['result' => 'fail'];
            } else {
                // Use the query parameter format for maximum compatibility across different server environments
                $new_url = home_url('/?wc-api=' . strtolower($this->id));

                $data = [
                    'full_name'    => sanitize_text_field(trim(($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: 'Jhon')),
                    'email_address' => sanitize_email($order->get_billing_email() ?: 'jhon@gmail.com'),
                    'mobile_number' => sanitize_text_field($order->get_billing_phone() ?: '01700000000'),
                    'amount'       => $order->get_total(),
                    'metadata'     => ['invoiceid' => $order->get_id()],
                    'return_url' => $new_url,
                    'webhook_url'  => $new_url,
                    'return_type'  => 'POST',
                    'currency'     => $order->get_currency(),
                ];

                $args = [
                    'body'    => json_encode($data),
                    'headers' => [
                        'Content-Type'           => 'application/json',
                        'Accept'                 => 'application/json',
                        'MHS-PIPRAPAY-API-KEY'    => $this->apikey,
                    ],
                    'timeout' => 45,
                    'sslverify' => false, // Added for local development
                ];

                $this->log('Checkout Request (New API): ' . print_r($data, true));
                $this->log('Endpoint: ' . $this->baseUrl . '/checkout/redirect');

                $response = wp_remote_post(rtrim($this->baseUrl, '/') . '/checkout/redirect', $args);

                if (is_wp_error($response)) {
                    $this->log('Checkout Error (New API): ' . $response->get_error_message());
                    wc_add_notice(__('Payment server connection error: ', 'piprapay-gateway') . $response->get_error_message(), 'error');
                    return ['result' => 'failure'];
                }

                $body = wp_remote_retrieve_body($response);
                $this->log('Checkout Response (New API): ' . $body);

                $result = json_decode($body, true);

                // 2. Check for a valid redirect URL from your API
                if (isset($result['pp_url'])) {
                    if (!empty($result['pp_id'])) {
                        $order->update_meta_data('_piprapay_payment_id', sanitize_text_field($result['pp_id']));
                        $order->save();
                    }

                    return [
                        'result'   => 'success',
                        'redirect' => esc_url_raw($result['pp_url']),
                    ];
                }

                // 3. Handle API-specific errors or unknown responses
                $message = !empty($result['error']['message'])
                    ? esc_html($result['error']['message'])
                    : __('Unknown error from payment provider.', 'piprapay-gateway');

                if (empty($result['pp_url']) && empty($result['error']['message'])) {
                    $message .= ' Raw response: ' . $body;
                }

                wc_add_notice(sprintf(__('Payment error: %s', 'piprapay-gateway'), $message), 'error');

                return ['result' => 'failure'];
            }
        }

        public function handle_webhook()
        {

            $this->log('Webhook/Redirect TRIGGERED at ' . date('Y-m-d H:i:s'));
            $this->log('Full URL: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
            $raw     = file_get_contents("php://input");
            $payload = json_decode($raw, true);
            $headers = getallheaders();

            $this->log('Incoming Request: ' . $_SERVER['REQUEST_METHOD']);
            $this->log('GET Params: ' . print_r($_GET, true));
            $this->log('POST Params: ' . print_r($_POST, true));
            $this->log('Payload: ' . $raw);

            // Determine if this is a browser redirect or a background webhook
            $is_redirect = ($_SERVER['REQUEST_METHOD'] === 'GET' || isset($_GET['pp_status']) || isset($_GET['amp;pp_status']));

            // Extract PipraPay Transaction Reference
            $pp_id = '';
            if (!empty($payload['pp_id'])) {
                $pp_id = sanitize_text_field($payload['pp_id']);
            } elseif (!empty($_GET['transaction_ref'])) {
                $pp_id = sanitize_text_field($_GET['transaction_ref']);
            } elseif (!empty($_GET['amp;transaction_ref'])) {
                $pp_id = sanitize_text_field($_GET['amp;transaction_ref']);
                $this->log('Warning: transaction_ref was received as amp;transaction_ref');
            }

            if (empty($pp_id)) {
                $this->log('Error: Missing PipraPay Transaction Reference.');
                if ($is_redirect) {
                    wc_add_notice(__('Invalid payment response.', 'piprapay-gateway'), 'error');
                    wp_safe_redirect(wc_get_checkout_url());
                } else {
                    status_header(400);
                    wp_send_json_error(['message' => 'Missing transaction reference.']);
                }
                exit;
            }

            // Security: Verify API Key for background webhooks
            if (!$is_redirect) {
                $received_api_key = '';
                $key_names = ['mh-piprapay-api-key', 'Mh-Piprapay-Api-Key', 'MHS-PIPRAPAY-API-KEY', 'Mhs-Piprapay-Api-Key'];
                foreach ($key_names as $key) {
                    if (isset($headers[$key])) {
                        $received_api_key = $headers[$key];
                        break;
                    }
                }
                if (empty($received_api_key) && isset($_SERVER['HTTP_MHS_PIPRAPAY_API_KEY'])) {
                    $received_api_key = $_SERVER['HTTP_MHS_PIPRAPAY_API_KEY'];
                }

                if (!empty($this->apikey) && !hash_equals($this->apikey, $received_api_key)) {
                    $this->log('Security Error: API Key mismatch or missing.');
                    status_header(401);
                    wp_send_json_error(['message' => 'Unauthorized request.']);
                    exit;
                }
            }

            // Always verify the transaction status with the PipraPay server
            $verification = $this->verify_payment($pp_id);
            $this->log('Verification Result: ' . print_r($verification, true));

            if (is_wp_error($verification) || empty($verification['status']) || $verification['status'] === 'error') {
                $error_msg = is_wp_error($verification) ? $verification->get_error_message() : ($verification['message'] ?? 'Empty status in response');
                $this->log('Error: Could not verify payment status with server. ' . $error_msg);
                
                // Store error in order meta if we can find the order by pp_id
                if (!empty($pp_id)) {
                    $orders = wc_get_orders(['limit' => 1, 'meta_key' => '_piprapay_payment_id', 'meta_value' => $pp_id]);
                    if (!empty($orders)) {
                        $orders[0]->update_meta_data('_piprapay_last_verification_error', $error_msg);
                        $orders[0]->save();
                    }
                }

                if ($is_redirect) {
                    wc_add_notice(__('Could not verify payment. Please check your order status later.', 'piprapay-gateway'), 'notice');
                    wp_redirect(wc_get_checkout_url());
                } else {
                    status_header(500);
                    wp_send_json_error(['message' => 'Verification failed.']);
                }
                exit;
            }

            // Identify the Order
            $order_id = 0;
            $search_keys = ['invoiceid', 'invoice_id', 'order_id', 'orderid'];

            foreach ($search_keys as $key) {
                if (!empty($verification['metadata'][$key])) {
                    $order_id = absint($verification['metadata'][$key]);
                    break;
                }
                if (!empty($verification[$key])) {
                    $order_id = absint($verification[$key]);
                    break;
                }
                if (!empty($payload['metadata'][$key])) {
                    $order_id = absint($payload['metadata'][$key]);
                    break;
                }
                if (!empty($payload[$key])) {
                    $order_id = absint($payload[$key]);
                    break;
                }
            }

            // Fallback: Search by Meta Key if metadata didn't yield an ID
            if (!$order_id && !empty($pp_id)) {
                $this->log('Order ID not found in metadata. Searching by meta key _piprapay_payment_id: ' . $pp_id);
                $orders = wc_get_orders([
                    'limit'      => 1,
                    'meta_key'   => '_piprapay_payment_id',
                    'meta_value' => $pp_id,
                ]);
                if (!empty($orders)) {
                    $order = $orders[0];
                    $order_id = $order->get_id();
                    $this->log('Order found by meta key: #' . $order_id);
                }
            }

            $order = wc_get_order($order_id);
            if (!$order) {
                $this->log('Error: Order not found for ID: ' . $order_id);
                if ($is_redirect) {
                    wc_add_notice(__('Order not found. Please contact support.', 'piprapay-gateway'), 'error');
                    wp_redirect(wc_get_checkout_url());
                } else {
                    status_header(404);
                    wp_send_json_error(['message' => 'Order not found.']);
                }
                exit;
            }

            // Process based on Status
            $status = strtolower($verification['status'] ?? $_GET['pp_status'] ?? $_GET['amp;pp_status'] ?? '');
            
            if (empty($status) && !empty($payload['status'])) {
                $status = strtolower($payload['status']);
            }
            switch ($status) {
                case 'completed':
                    if (!$order->has_status('completed') && !$order->has_status('processing')) {
                        $this->log('Payment Completed. Updating order #' . $order_id);
                        $order->update_meta_data('_piprapay_payment_id', $pp_id);

                        $trx_id = $verification['transaction_id'] ?? '';
                        $sender = $verification['sender'] ?? $verification['sender_number'] ?? '';
                        $gateway = $verification['gateway'] ?? $verification['payment_method'] ?? $this->method_title;

                        if (!empty($trx_id)) $order->update_meta_data('_piprapay_transaction_id', $trx_id);
                        if (!empty($sender)) $order->update_meta_data('_piprapay_sender_number', $sender);
                        $order->update_meta_data('_piprapay_actual_payment_method', $gateway);
                        $order->save();

                        // Set order status based on user settings
                        $order_type = $this->order_type;
                        if ($order_type === 'digital_complete') {
                            $order->update_status('completed', __('Payment confirmed via PipraPay.', 'piprapay-gateway'));
                        } else {
                            $order->update_status('processing', __('Payment confirmed via PipraPay.', 'piprapay-gateway'));
                        }
                        $order->payment_complete();
                    }
                    if ($is_redirect) {
                        wp_redirect($order->get_checkout_order_received_url());
                        exit;
                    }
                    break;

                case 'canceled':
                case 'failed':
                    $this->log('Payment ' . $status . '. Updating order #' . $order_id);
                    if (!$order->has_status('failed')) {
                        $order->update_status('failed', sprintf(__('Payment %s via PipraPay.', 'piprapay-gateway'), $status));
                    }
                    if ($is_redirect) {
                        wc_add_notice(__('Your payment was canceled or failed. Please try again.', 'piprapay-gateway'), 'error');
                        wp_redirect(wc_get_checkout_url());
                        exit;
                    }
                    break;

                case 'pending':
                case 'initiated':
                    $this->log('Payment Pending. Order #' . $order_id);
                    $order->add_order_note(__('Payment is currently pending verification.', 'piprapay-gateway'));
                    if ($is_redirect) {
                        wp_redirect($order->get_checkout_order_received_url());
                        exit;
                    }
                    break;

                default:
                    $this->log('Unknown status received: ' . $status);
                    break;
            }

            if (!$is_redirect) {
                status_header(200);
                wp_send_json_success(['message' => 'Processed successfully.']);
                exit;
            }
        }

        private function verify_payment($pp_id)
        {
            if ($this->piprapay_version == 'old') {
                $data = json_encode(['pp_id' => $pp_id]);
                $args = [
                    'body'    => $data,
                    'headers' => [
                        'Content-Type'        => 'application/json',
                        'Accept'              => 'application/json',
                        'mh-piprapay-api-key' => $this->apikey,
                    ],
                    'timeout'   => 45,
                    'sslverify' => false,
                ];
                $url      = rtrim($this->baseUrl, '/') . '/verify-payments';
                $response = wp_remote_post($url, $args);
                if (is_wp_error($response)) return ['status' => 'error', 'message' => $response->get_error_message()];
                return json_decode(wp_remote_retrieve_body($response), true);
            } else {
                $data = json_encode(['pp_id' => $pp_id]);
                $args = [
                    'body'    => $data,
                    'headers' => [
                        'Content-Type'        => 'application/json',
                        'Accept'              => 'application/json',
                        'MHS-PIPRAPAY-API-KEY' => $this->apikey,
                    ],
                    'timeout'   => 45,
                    'sslverify' => false,
                ];
                $url      = rtrim($this->baseUrl, '/') . '/verify-payment';
                $response = wp_remote_post($url, $args);
                if (is_wp_error($response)) return ['status' => 'error', 'message' => $response->get_error_message()];
                return json_decode(wp_remote_retrieve_body($response), true);
            }
        }

        public function display_piprapay_order_meta($order)
        {
            $payment_id            = $order->get_meta('_piprapay_payment_id', true);
            $transaction_id        = $order->get_meta('_piprapay_transaction_id', true);
            $sender_number         = $order->get_meta('_piprapay_sender_number', true);
            $actual_payment_method = $order->get_meta('_piprapay_actual_payment_method', true);

            echo '<h3>' . __('PipraPay Details', 'piprapay-gateway') . '</h3>';
            if ($payment_id) {
                echo '<p><i class="fa fa-arrow-right"></i> <strong>' . __('Payment ID:', 'piprapay-gateway') . '</strong> ' . esc_html($payment_id) . '</p>';
            }
            if ($transaction_id) {
                echo '<p><i class="fa fa-arrow-right"></i> <strong>' . __('Transaction ID:', 'piprapay-gateway') . '</strong> ' . esc_html($transaction_id) . '</p>';
            }
            echo '<p><i class="fa fa-arrow-right"></i> <strong>' . __('Payment Method:', 'piprapay-gateway') . '</strong> ' . esc_html($actual_payment_method) . '</p>';
            if ($sender_number) {
                echo '<p><i class="fa fa-arrow-right"></i> <strong>' . __('Sender Number:', 'piprapay-gateway') . '</strong> ' . esc_html($sender_number) . '</p>';
            } else {
                echo '<p><i class="fa fa-arrow-right"></i> <strong>' . __('Sender Number:', 'piprapay-gateway') . '</strong> ' . __('N/A (Not available from PipraPay API response)', 'piprapay-gateway') . '</p>';
            }
        }

        private function show_redirect_page($status, $redirect_url, $message)
        {
            $color = ($status === 'success') ? '#10b981' : '#ef4444';
            $icon  = ($status === 'success') ? '✓' : '✕';
            $title = ($status === 'success') ? __('Payment Successful', 'piprapay-gateway') : __('Payment Failed/Canceled', 'piprapay-gateway');

            if (!headers_sent()) {
                header('Content-Type: text/html; charset=utf-8');
            }

            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title><?php echo esc_html($title); ?></title>
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
                <style>
                    body {
                        font-family: 'Inter', sans-serif;
                        background: #f3f4f6;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        height: 100vh;
                        margin: 0;
                    }
                    .card {
                        background: white;
                        padding: 2.5rem;
                        border-radius: 1.5rem;
                        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
                        text-align: center;
                        max-width: 400px;
                        width: 90%;
                    }
                    .icon {
                        width: 64px;
                        height: 64px;
                        background: <?php echo esc_attr($color); ?>;
                        color: white;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 2rem;
                        margin: 0 auto 1.5rem;
                        animation: scaleIn 0.5s ease-out;
                    }
                    h1 {
                        color: #1f2937;
                        font-size: 1.5rem;
                        font-weight: 600;
                        margin-bottom: 0.5rem;
                    }
                    p {
                        color: #6b7280;
                        margin-bottom: 2rem;
                    }
                    .countdown {
                        font-size: 0.875rem;
                        color: #9ca3af;
                    }
                    .timer {
                        font-weight: 600;
                        color: <?php echo esc_attr($color); ?>;
                    }
                    .btn {
                        display: inline-block;
                        background: <?php echo esc_attr($color); ?>;
                        color: white;
                        padding: 0.75rem 1.5rem;
                        border-radius: 0.5rem;
                        text-decoration: none;
                        font-weight: 600;
                        transition: opacity 0.2s;
                    }
                    .btn:hover {
                        opacity: 0.9;
                    }
                    @keyframes scaleIn {
                        from { transform: scale(0); opacity: 0; }
                        to { transform: scale(1); opacity: 1; }
                    }
                </style>
            </head>
            <body>
                <div class="card">
                    <div class="icon"><?php echo esc_html($icon); ?></div>
                    <h1><?php echo esc_html($title); ?></h1>
                    <p><?php echo esc_html($message); ?></p>
                    <div style="margin-bottom: 1.5rem;">
                        <a href="<?php echo esc_url($redirect_url); ?>" class="btn"><?php _e('Return to Merchant', 'piprapay-gateway'); ?></a>
                    </div>
                    <div class="countdown">
                        <?php _e('Redirecting in', 'piprapay-gateway'); ?> <span id="timer" class="timer">3</span> <?php _e('seconds...', 'piprapay-gateway'); ?>
                    </div>
                </div>
                <script>
                    // Log response to browser console for debugging
                    console.log("PipraPay Redirect Info:", {
                        status: "<?php echo esc_js($status); ?>",
                        redirect_url: "<?php echo esc_js($redirect_url); ?>",
                        message: "<?php echo esc_js($message); ?>"
                    });

                    let timeLeft = 3;
                    const timerElement = document.getElementById('timer');
                    const interval = setInterval(() => {
                        timeLeft--;
                        timerElement.textContent = timeLeft;
                        if (timeLeft <= 0) {
                            clearInterval(interval);
                            window.location.href = "<?php echo esc_url($redirect_url); ?>";
                        }
                    }, 1000);
                </script>
            </body>
            </html>
            <?php
            exit;
        }
    }

    // Define server-side integration for blocks
    class WC_PIPRAPAY_Blocks extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType
    {
        protected $name = 'piprapay';

        public function initialize()
        {
            $this->settings = get_option('woocommerce_piprapay_settings', []);
        }

        public function is_active()
        {
            return !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'];
        }

        public function get_payment_method_script_handles()
        {
            wp_register_script(
                'piprapay-blocks',
                plugins_url('assets/js/piprapay-blocks.js', __FILE__),
                ['wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-i18n'],
                filemtime(plugin_dir_path(__FILE__) . 'assets/js/piprapay-blocks.js'),
                true
            );
            return ['piprapay-blocks'];
        }

        public function get_payment_method_data()
        {
            $gateway = WC_PIPRAPAY_Gateway::get_instance();
            return [
                'title' => $gateway->title,
                'description' => $gateway->description,
                'icon' => $gateway->icon,
                'supports' => ['products'],
            ];
        }
    }

    // Register payment method for WooCommerce Blocks
    add_action('woocommerce_blocks_payment_method_type_registration', function ($registry) {
        $registry->register(new WC_PIPRAPAY_Blocks());
    });

    // Add filter to include payment gateway
    add_filter('woocommerce_payment_gateways', function ($gateways) {
        $gateways[] = 'WC_PIPRAPAY_Gateway';
        return $gateways;
    });

    // Force instantiation to register hooks early (important for WC_API)
    WC_PIPRAPAY_Gateway::get_instance();
}
