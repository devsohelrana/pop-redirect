<?php

/**
 * Admin settings page for Pop Redirect
 *
 * @package Auto_Open_Tab
 */

if (! defined('ABSPATH')) exit;

class Auto_Open_Tab_Admin
{

    public function init()
    {
        add_action('admin_menu',  array($this, 'add_settings_page'));
        add_action('admin_init',  array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_filter('plugin_action_links_' . plugin_basename(AOT_PLUGIN_DIR . 'pop-redirect.php'), array($this, 'add_settings_link'));
    }

    /**
     * Add Settings link on Plugins page
     */
    public function add_settings_link($links)
    {
        $settings_link = '<a href="admin.php?page=pop-redirect">' . __('Settings', 'pop-redirect') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Register the settings page under Settings menu
     */
    public function add_settings_page()
    {
        add_menu_page(
            __('Pop Redirect Settings', 'pop-redirect'),
            __('Pop Redirect', 'pop-redirect'),
            'manage_options',
            'pop-redirect',
            array($this, 'render_settings_page'),
            'dashicons-external',
            58
        );
    }

    /**
     * Register settings using the Settings API
     */
    public function register_settings()
    {
        register_setting(
            'aot_settings_group',
            'aot_settings',
            array('sanitize_callback' => array($this, 'sanitize_settings'))
        );

        // Section: General
        add_settings_section('aot_general_section', __('General Settings', 'pop-redirect'), array($this, 'general_section_description'), 'pop-redirect');

        add_settings_field('aot_enabled',    __('Enable Plugin', 'pop-redirect'),        array($this, 'field_enabled'),    'pop-redirect', 'aot_general_section');
        add_settings_field('aot_url',        __('URL to Open', 'pop-redirect'),           array($this, 'field_url'),        'pop-redirect', 'aot_general_section');
        add_settings_field('aot_delay',      __('Delay (ms)', 'pop-redirect'),            array($this, 'field_delay'),      'pop-redirect', 'aot_general_section');
        add_settings_field('aot_once',       __('Once Per Session', 'pop-redirect'),      array($this, 'field_once'),       'pop-redirect', 'aot_general_section');
        add_settings_field('aot_trigger_on', __('Trigger On', 'pop-redirect'),            array($this, 'field_trigger_on'), 'pop-redirect', 'aot_general_section');

        // Section: Display Mode
        add_settings_section('aot_display_section', __('Display Mode', 'pop-redirect'), array($this, 'display_section_description'), 'pop-redirect');

        add_settings_field('aot_mode',         __('Mode', 'pop-redirect'),               array($this, 'field_mode'),         'pop-redirect', 'aot_display_section');
        add_settings_field('aot_bar_position', __('Bar Position', 'pop-redirect'),        array($this, 'field_bar_position'), 'pop-redirect', 'aot_display_section', array('class' => 'aot-mode-row aot-mode-bar'));
        add_settings_field('aot_bar_color',    __('Bar Color', 'pop-redirect'),           array($this, 'field_bar_color'),    'pop-redirect', 'aot_display_section', array('class' => 'aot-mode-row aot-mode-bar'));
        add_settings_field('aot_bar_text',     __('Bar Message Text', 'pop-redirect'),    array($this, 'field_bar_text'),     'pop-redirect', 'aot_display_section', array('class' => 'aot-mode-row aot-mode-bar'));
        add_settings_field('aot_bar_btn_text', __('Bar Button Text', 'pop-redirect'),     array($this, 'field_bar_btn_text'), 'pop-redirect', 'aot_display_section', array('class' => 'aot-mode-row aot-mode-bar'));
        add_settings_field('aot_auto_text',    __('Auto Mode Hint Text', 'pop-redirect'), array($this, 'field_auto_text'),    'pop-redirect', 'aot_display_section', array('class' => 'aot-mode-row aot-mode-auto'));
    }

    public function general_section_description()
    {
        echo '<p>' . __('Configure when and where the new tab opens.', 'pop-redirect') . '</p>';
    }

    public function display_section_description()
    {
        echo '<p>' . __('Choose how opening is triggered. <strong>Bar mode</strong> shows a sticky notification bar. <strong>Auto mode</strong> opens on the visitor\'s first click anywhere on the page.', 'pop-redirect') . '</p>';
    }

    public function field_enabled()
    {
        $val = Auto_Open_Tab::get_setting('enabled', 1);
        echo '<input type="checkbox" name="aot_settings[enabled]" value="1" ' . checked(1, $val, false) . ' />';
        echo '<label> ' . __('Enable the plugin', 'pop-redirect') . '</label>';
    }

    public function field_url()
    {
        $val = Auto_Open_Tab::get_setting('url', '');
        echo '<input type="url" name="aot_settings[url]" value="' . esc_attr($val) . '" class="regular-text" placeholder="https://example.com" />';
        echo '<p class="description">' . __('Full URL to open in a new tab (must start with https://).', 'pop-redirect') . '</p>';
    }

    public function field_delay()
    {
        $val = Auto_Open_Tab::get_setting('delay', 1000);
        echo '<input type="number" name="aot_settings[delay]" value="' . esc_attr($val) . '" min="0" step="100" style="width:100px;" /> ms';
        echo '<p class="description">' . __('How long to wait before showing the bar / enabling auto-open. 1000 = 1 second.', 'pop-redirect') . '</p>';
    }

    public function field_once()
    {
        $val = Auto_Open_Tab::get_setting('once_session', 1);
        echo '<input type="checkbox" name="aot_settings[once_session]" value="1" ' . checked(1, $val, false) . ' />';
        echo '<label> ' . __('Show/trigger only once per browser session (recommended).', 'pop-redirect') . '</label>';
    }

    public function field_trigger_on()
    {
        $val = Auto_Open_Tab::get_setting('trigger_on', array('pages', 'posts', 'homepage'));
        $options = array(
            'homepage' => __('Homepage', 'pop-redirect'),
            'pages'    => __('Pages', 'pop-redirect'),
            'posts'    => __('Posts', 'pop-redirect'),
            'archives' => __('Archives / Category pages', 'pop-redirect'),
        );
        foreach ($options as $key => $label) {
            $checked = in_array($key, (array) $val) ? 'checked' : '';
            echo '<label style="display:block;margin-bottom:5px;">';
            echo '<input type="checkbox" name="aot_settings[trigger_on][]" value="' . esc_attr($key) . '" ' . $checked . ' /> ' . esc_html($label);
            echo '</label>';
        }
    }

    public function field_mode()
    {
        $val = Auto_Open_Tab::get_setting('mode', 'bar');
        echo '<label style="display:block;margin-bottom:8px;"><input type="radio" name="aot_settings[mode]" value="bar" ' . checked('bar', $val, false) . ' /> ';
        echo '<strong>' . __('Bar mode', 'pop-redirect') . '</strong> — ' . __('Shows a sticky notification bar. Visitor clicks the button to open the tab. <em>Most reliable.</em>', 'pop-redirect') . '</label>';
        echo '<label style="display:block;"><input type="radio" name="aot_settings[mode]" value="auto" ' . checked('auto', $val, false) . ' /> ';
        echo '<strong>' . __('Auto mode', 'pop-redirect') . '</strong> — ' . __('Opens the tab on the visitor\'s very first click anywhere on the page (invisible overlay). No UI shown.', 'pop-redirect') . '</label>';
    }

    public function field_bar_position()
    {
        $val = Auto_Open_Tab::get_setting('bar_position', 'bottom');
        echo '<select name="aot_settings[bar_position]">';
        echo '<option value="bottom" ' . selected('bottom', $val, false) . '>' . __('Bottom', 'pop-redirect') . '</option>';
        echo '<option value="top" '    . selected('top',    $val, false) . '>' . __('Top', 'pop-redirect')    . '</option>';
        echo '</select>';
        echo '<p class="description">' . __('Only applies to Bar mode.', 'pop-redirect') . '</p>';
    }

    public function field_bar_color()
    {
        $val = Auto_Open_Tab::get_setting('bar_color', '#1a73e8');
        echo '<input type="color" name="aot_settings[bar_color]" value="' . esc_attr($val) . '" />';
        echo '<p class="description">' . __('Background color of the notification bar.', 'pop-redirect') . '</p>';
    }

    public function field_bar_text()
    {
        $val = Auto_Open_Tab::get_setting('bar_text', 'Visit our special offer');
        echo '<input type="text" name="aot_settings[bar_text]" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . __('Message shown in the bar.', 'pop-redirect') . '</p>';
    }

    public function field_bar_btn_text()
    {
        $val = Auto_Open_Tab::get_setting('bar_btn_text', 'Open Now');
        echo '<input type="text" name="aot_settings[bar_btn_text]" value="' . esc_attr($val) . '" style="width:200px;" />';
        echo '<p class="description">' . __('Text on the button inside the bar.', 'pop-redirect') . '</p>';
    }

    public function field_auto_text()
    {
        $val = Auto_Open_Tab::get_setting('auto_text', 'Priyo Email');
        echo '<input type="text" name="aot_settings[auto_text]" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . __('Small hint shown at the bottom of the screen in Auto mode. Leave empty to hide.', 'pop-redirect') . '</p>';
    }

    /**
     * Sanitize all settings before saving
     */
    public function sanitize_settings($input)
    {
        $clean = array();
        $clean['enabled']      = ! empty($input['enabled']) ? 1 : 0;
        $clean['url']          = isset($input['url']) ? esc_url_raw(trim($input['url'])) : '';
        $clean['delay']        = isset($input['delay']) ? absint($input['delay']) : 1000;
        $clean['once_session'] = ! empty($input['once_session']) ? 1 : 0;
        $allowed_modes         = array('bar', 'auto');
        $clean['mode']         = (isset($input['mode']) && in_array($input['mode'], $allowed_modes, true)) ? $input['mode'] : 'bar';
        $clean['bar_position'] = (isset($input['bar_position']) && $input['bar_position'] === 'top') ? 'top' : 'bottom';
        $clean['bar_color']    = isset($input['bar_color']) ? sanitize_hex_color($input['bar_color']) : '#1a73e8';
        $clean['bar_text']     = isset($input['bar_text'])     ? sanitize_text_field($input['bar_text'])     : 'Visit our special offer';
        $clean['bar_btn_text'] = isset($input['bar_btn_text']) ? sanitize_text_field($input['bar_btn_text']) : 'Open Now';
        $clean['auto_text']    = isset($input['auto_text'])    ? sanitize_text_field($input['auto_text'])    : 'Click anywhere to continue';

        $allowed_triggers = array('homepage', 'pages', 'posts', 'archives');
        if (! empty($input['trigger_on']) && is_array($input['trigger_on'])) {
            $clean['trigger_on'] = array_intersect($input['trigger_on'], $allowed_triggers);
        } else {
            $clean['trigger_on'] = array();
        }

        return $clean;
    }

    /**
     * Enqueue admin CSS
     */
    public function enqueue_admin_assets($hook)
    {
        if ('toplevel_page_pop-redirect' !== $hook) return;
        wp_enqueue_style('aot-admin', AOT_PLUGIN_URL . 'assets/css/admin.css', array(), AOT_VERSION);
        wp_enqueue_script('aot-admin', AOT_PLUGIN_URL . 'assets/js/admin.js', array(), AOT_VERSION, true);
    }

    /**
     * Render the settings page HTML
     */
    public function render_settings_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'pop-redirect'));
        }
?>
        <div class="wrap aot-wrap">
            <h1>
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>

            <?php settings_errors('aot_settings_group'); ?>

            <div class="aot-layout">
                <div class="aot-main">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('aot_settings_group');
                        do_settings_sections('pop-redirect');
                        submit_button(__('Save Settings', 'pop-redirect'));
                        ?>
                    </form>
                </div>

                <div class="aot-sidebar">
                    <div class="aot-box">
                        <h3>📋 How It Works</h3>
                        <ol>
                            <li>Enter the URL you want to open.</li>
                            <li>Set a delay (1–3 seconds works best).</li>
                            <li>Enable "Once Per Session" to avoid bothering repeat visitors.</li>
                            <li>Choose which pages trigger the tab.</li>
                            <li>Save and test on your site.</li>
                        </ol>
                    </div>
                    <div class="aot-box">
                        <h3>⚠️ Browser Note</h3>
                        <p>Modern browsers may block pop-ups if the visitor hasn't interacted with the page. A delay of <strong>1000–3000ms</strong> after scroll or click interaction improves success rate.</p>
                    </div>
                    <div class="aot-box">
                        <h3>🔌 Plugin Version</h3>
                        <p>Version: <strong><?php echo AOT_VERSION; ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
}
