<?php

/**
 * Main plugin class - wires everything together
 *
 * @package POP_Redirect
 */

if (! defined('ABSPATH')) exit;

class POP_Redirect
{

    /**
     * Initialize and hook admin + frontend components
     */
    public function run()
    {

        // Admin settings
        if (is_admin()) {
            $admin = new POP_Redirect_Admin();
            $admin->init();
        }

        // Frontend output
        $frontend = new POP_Redirect_Frontend();
        $frontend->init();
    }

    /**
     * Helper: get a single setting value
     *
     * @param string $key     Option key
     * @param mixed  $default Default value
     * @return mixed
     */
    public static function get_setting($key, $default = null)
    {
        $settings = get_option('pr_settings', array());
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
}
