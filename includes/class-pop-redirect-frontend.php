<?php

/**
 * Frontend handler - injects the Pop Redirect script
 *
 * @package Auto_Open_Tab
 */

if (! defined('ABSPATH')) exit;

class Auto_Open_Tab_Frontend
{

    public function init()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_script'));
    }

    /**
     * Enqueue the frontend JS file
     */
    public function enqueue_script()
    {
        if (! $this->should_trigger()) return;

        wp_enqueue_script(
            'pop-redirect',
            AOT_PLUGIN_URL . 'assets/js/pop-redirect.js',
            array(),
            AOT_VERSION,
            true   // Load in footer
        );

        wp_add_inline_script(
            'pop-redirect',
            'window.aotConfig = ' . wp_json_encode(array(
                'url'         => Auto_Open_Tab::get_setting('url', ''),
                'delay'       => (int) Auto_Open_Tab::get_setting('delay', 1000),
                'onceSession' => (bool) Auto_Open_Tab::get_setting('once_session', true),
                'mode'        => Auto_Open_Tab::get_setting('mode', 'bar'),
                'barPos'      => Auto_Open_Tab::get_setting('bar_position', 'bottom'),
                'barColor'    => Auto_Open_Tab::get_setting('bar_color', '#1a73e8'),
                'barText'     => Auto_Open_Tab::get_setting('bar_text', 'Visit our special offer'),
                'barBtnText'  => Auto_Open_Tab::get_setting('bar_btn_text', 'Open Now'),
                'autoText'    => Auto_Open_Tab::get_setting('auto_text', 'Click anywhere to continue'),
            )) . ';',
            'before'
        );
    }

    /**
     * Determine whether the current page should trigger the tab
     */
    private function should_trigger()
    {
        // Plugin must be enabled
        if (! Auto_Open_Tab::get_setting('enabled', 1)) return false;

        // URL must be set
        $url = Auto_Open_Tab::get_setting('url', '');
        if (empty($url)) return false;

        // Check trigger locations
        $triggers = (array) Auto_Open_Tab::get_setting('trigger_on', array('pages', 'posts', 'homepage'));

        if (in_array('homepage', $triggers) && (is_front_page() || is_home())) return true;
        if (in_array('pages', $triggers)    && is_page())                        return true;
        if (in_array('posts', $triggers)    && is_single())                      return true;
        if (in_array('posts', $triggers)    && is_singular() && ! is_page())     return true;
        if (in_array('archives', $triggers) && (is_archive() || is_category() || is_tag())) return true;

        return false;
    }
}
