<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
 exit;
}

// Delete plugin options
delete_option('woocommerce_piprapay_settings');