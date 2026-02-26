<?php

/**
 * Uninstall logic for Pop Redirect.
 *
 * @package Auto_Open_Tab
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Remove plugin data from the current site.
 */
function aot_uninstall_cleanup_site_data()
{
    delete_option('aot_settings');
    delete_transient('aot_activation_redirect');
}

if (is_multisite()) {
    $site_ids = get_sites(array(
        'fields' => 'ids',
        'number' => 0,
    ));

    foreach ($site_ids as $site_id) {
        switch_to_blog((int) $site_id);
        aot_uninstall_cleanup_site_data();
        restore_current_blog();
    }
} else {
    aot_uninstall_cleanup_site_data();
}
