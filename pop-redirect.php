<?php

/**
 * Plugin Name:       Pop Redirect
 * Plugin URI:        https://sohelrana.me/pop-redirect
 * Description:       Automatically opens a specified URL in a new browser tab when visitors view any page or post on your WordPress site.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            Sohel Rana
 * Author URI:        https://sohelrana.me
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pop-redirect
 */

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AOT_VERSION',     '1.0.0');
define('AOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AOT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load plugin files
 */
require_once AOT_PLUGIN_DIR . 'includes/class-pop-redirect.php';
require_once AOT_PLUGIN_DIR . 'includes/class-pop-redirect-admin.php';
require_once AOT_PLUGIN_DIR . 'includes/class-pop-redirect-frontend.php';

/**
 * Run the plugin
 */
function aot_run()
{
    $plugin = new Auto_Open_Tab();
    $plugin->run();
}
aot_run();

/**
 * Activation hook - set default options
 */
register_activation_hook(__FILE__, 'aot_activate');
function aot_activate()
{
    if (false === get_option('aot_settings')) {
        add_option('aot_settings', array(
            'enabled'       => 1,
            'url'           => '',
            'delay'         => 1000,
            'once_session'  => 1,
            'trigger_on'    => array('pages', 'posts', 'homepage'),
        ));
    }

    set_transient('aot_activation_redirect', 1, 30);
}

/**
 * Redirect to settings page once after activation
 */
add_action('admin_init', 'aot_maybe_redirect_to_settings');
function aot_maybe_redirect_to_settings()
{
    if (! get_transient('aot_activation_redirect')) {
        return;
    }

    delete_transient('aot_activation_redirect');

    if (is_network_admin() || isset($_GET['activate-multi'])) {
        return;
    }

    wp_safe_redirect(admin_url('admin.php?page=pop-redirect'));
    exit;
}

/**
 * Deactivation hook - cleanup (optional)
 */
register_deactivation_hook(__FILE__, 'aot_deactivate');
function aot_deactivate()
{
    // Nothing to do on deactivation
}
