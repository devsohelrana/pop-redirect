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
function pop_redirect_uninstall_cleanup_site_data()
{
    delete_option('pr_settings');
    delete_transient('pr_activation_redirect');
}

if (is_multisite()) {
    $pop_redirect_site_ids = get_sites(array(
        'fields' => 'ids',
        'number' => 0,
    ));

    foreach ($pop_redirect_site_ids as $pop_redirect_site_id) {
        switch_to_blog((int) $pop_redirect_site_id);
        pop_redirect_uninstall_cleanup_site_data();
        restore_current_blog();
    }
} else {
    pop_redirect_uninstall_cleanup_site_data();
}
