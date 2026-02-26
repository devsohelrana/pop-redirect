<?php

/**
 * Plugin Name:       Pop Redirect
 * Plugin URI:        https://sohelrana.me/pop-redirect
 * Description:       Opens a specified URL in a new browser tab after visitor interaction, using bar mode or first-click mode.
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
define('PR_VERSION',     '1.0.0');
define('PR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PR_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load plugin files
 */
require_once PR_PLUGIN_DIR . 'includes/class-pop-redirect.php';
require_once PR_PLUGIN_DIR . 'includes/class-pop-redirect-admin.php';
require_once PR_PLUGIN_DIR . 'includes/class-pop-redirect-frontend.php';

/**
 * Run the plugin
 */
function pop_redirect_run()
{
    $plugin = new POP_Redirect();
    $plugin->run();
}
pop_redirect_run();

/**
 * Activation hook - set default options
 */
register_activation_hook(__FILE__, 'pop_redirect_activate');
function pop_redirect_activate()
{
    if (false === get_option('pr_settings')) {
        add_option('pr_settings', array(
            'enabled'       => 1,
            'url'           => '',
            'delay'         => 1000,
            'once_session'  => 1,
            'trigger_on'    => array('pages', 'posts', 'homepage'),
        ));
    }

    set_transient('pr_activation_redirect', 1, 30);
}

/**
 * Redirect to settings page once after activation
 */
add_action('admin_init', 'pop_redirect_maybe_redirect_to_settings');
function pop_redirect_maybe_redirect_to_settings()
{
    if (! get_transient('pr_activation_redirect')) {
        return;
    }

    delete_transient('pr_activation_redirect');

    if (is_network_admin()) {
        return;
    }

    wp_safe_redirect(admin_url('admin.php?page=pop-redirect'));
    exit;
}

/**
 * Deactivation hook - cleanup (optional)
 */
register_deactivation_hook(__FILE__, 'pop_redirect_deactivate');
function pop_redirect_deactivate()
{
    // Nothing to do on deactivation
}
