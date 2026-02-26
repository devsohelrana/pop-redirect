<?php
/**
 * Main plugin class - wires everything together
 *
 * @package Auto_Open_Tab
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Auto_Open_Tab {

    /**
     * Initialize and hook admin + frontend components
     */
    public function run() {

        // Admin settings
        if ( is_admin() ) {
            $admin = new Auto_Open_Tab_Admin();
            $admin->init();
        }

        // Frontend output
        $frontend = new Auto_Open_Tab_Frontend();
        $frontend->init();
    }

    /**
     * Helper: get a single setting value
     *
     * @param string $key     Option key
     * @param mixed  $default Default value
     * @return mixed
     */
    public static function get_setting( $key, $default = null ) {
        $settings = get_option( 'aot_settings', array() );
        return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
    }
}
