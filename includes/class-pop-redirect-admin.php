<?php

/**
 * Admin settings page for Pop Redirect
 *
 * @package POP_REDIRECT
 */

if (! defined('ABSPATH')) exit;

class POP_REDIRECT_Admin
{

    public function init()
    {
        add_action('admin_menu',  array($this, 'add_settings_page'));
        add_action('admin_init',  array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_filter('plugin_action_links_' . plugin_basename(PR_PLUGIN_DIR . 'pop-redirect.php'), array($this, 'add_settings_link'));
    }

    /**
     * Add Settings link on Plugins page
     */
    public function add_settings_link($links)
    {
        $settings_link = '<a href="admin.php?page=pop-redirect">' . esc_html__('Settings', 'pop-redirect') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Register the settings page under Settings menu
     */
    public function add_settings_page()
    {
        add_menu_page(
            esc_html__('Pop Redirect Settings', 'pop-redirect'),
            esc_html__('Pop Redirect', 'pop-redirect'),
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
            'pr_settings_group',
            'pr_settings',
            array('sanitize_callback' => array($this, 'sanitize_settings'))
        );

        // Section: General
        add_settings_section('pr_general_section', esc_html__('General Settings', 'pop-redirect'), array($this, 'general_section_description'), 'pop-redirect');

        add_settings_field('pr_enabled',    esc_html__('Enable Plugin', 'pop-redirect'),        array($this, 'field_enabled'),    'pop-redirect', 'pr_general_section');
        add_settings_field('pr_url',        esc_html__('URL to Open', 'pop-redirect'),           array($this, 'field_url'),        'pop-redirect', 'pr_general_section');
        add_settings_field('pr_delay',      esc_html__('Delay (ms)', 'pop-redirect'),            array($this, 'field_delay'),      'pop-redirect', 'pr_general_section');
        add_settings_field('pr_once',       esc_html__('Once Per Session', 'pop-redirect'),      array($this, 'field_once'),       'pop-redirect', 'pr_general_section');
        add_settings_field('pr_trigger_on', esc_html__('Trigger On', 'pop-redirect'),            array($this, 'field_trigger_on'), 'pop-redirect', 'pr_general_section');

        // Section: Display Mode
        add_settings_section('pr_display_section', esc_html__('Display Mode', 'pop-redirect'), array($this, 'display_section_description'), 'pop-redirect');

        add_settings_field('pr_mode',         esc_html__('Mode', 'pop-redirect'),               array($this, 'field_mode'),         'pop-redirect', 'pr_display_section');
        add_settings_field('pr_bar_position', esc_html__('Bar Position', 'pop-redirect'),        array($this, 'field_bar_position'), 'pop-redirect', 'pr_display_section', array('class' => 'pr-mode-row pr-mode-bar'));
        add_settings_field('pr_bar_color',    esc_html__('Bar Color', 'pop-redirect'),           array($this, 'field_bar_color'),    'pop-redirect', 'pr_display_section', array('class' => 'pr-mode-row pr-mode-bar'));
        add_settings_field('pr_bar_text',     esc_html__('Bar Message Text', 'pop-redirect'),    array($this, 'field_bar_text'),     'pop-redirect', 'pr_display_section', array('class' => 'pr-mode-row pr-mode-bar'));
        add_settings_field('pr_bar_btn_text', esc_html__('Bar Button Text', 'pop-redirect'),     array($this, 'field_bar_btn_text'), 'pop-redirect', 'pr_display_section', array('class' => 'pr-mode-row pr-mode-bar'));
        add_settings_field('pr_auto_text',    esc_html__('Auto Mode Hint Text', 'pop-redirect'), array($this, 'field_auto_text'),    'pop-redirect', 'pr_display_section', array('class' => 'pr-mode-row pr-mode-auto'));
    }

    public function general_section_description()
    {
        echo '<p>' . esc_html__('Configure when and where the new tab opens.', 'pop-redirect') . '</p>';
    }

    public function display_section_description()
    {
        echo '<p>' . esc_html__('Choose how opening is triggered. Bar mode shows a sticky notification bar. Auto mode opens on the visitor\'s first click anywhere on the page, without adding a hidden overlay.', 'pop-redirect') . '</p>';
    }

    public function field_enabled()
    {
        $val = POP_REDIRECT::get_setting('enabled', 1);
        echo '<input type="checkbox" name="pr_settings[enabled]" value="1" ' . checked(1, $val, false) . ' />';
        echo '<label> ' . esc_html__('Enable the plugin', 'pop-redirect') . '</label>';
    }

    public function field_url()
    {
        $val = POP_REDIRECT::get_setting('url', '');
        echo '<input type="url" name="pr_settings[url]" value="' . esc_attr($val) . '" class="regular-text" placeholder="https://example.com" />';
        echo '<p class="description">' . esc_html__('Full URL to open in a new tab (must start with https://).', 'pop-redirect') . '</p>';
    }

    public function field_delay()
    {
        $val = POP_REDIRECT::get_setting('delay', 1000);
        echo '<input type="number" name="pr_settings[delay]" value="' . esc_attr($val) . '" min="0" step="100" style="width:100px;" /> ms';
        echo '<p class="description">' . esc_html__('How long to wait before showing the bar / enabling auto-open. 1000 = 1 second.', 'pop-redirect') . '</p>';
    }

    public function field_once()
    {
        $val = POP_REDIRECT::get_setting('once_session', 1);
        echo '<input type="checkbox" name="pr_settings[once_session]" value="1" ' . checked(1, $val, false) . ' />';
        echo '<label> ' . esc_html__('Show/trigger only once per browser session (recommended).', 'pop-redirect') . '</label>';
    }

    public function field_trigger_on()
    {
        $val = POP_REDIRECT::get_setting('trigger_on', array('pages', 'posts', 'homepage'));
        $options = array(
            'homepage' => esc_html__('Homepage', 'pop-redirect'),
            'pages'    => esc_html__('Pages', 'pop-redirect'),
            'posts'    => esc_html__('Posts', 'pop-redirect'),
            'archives' => esc_html__('Archives / Category pages', 'pop-redirect'),
        );
        foreach ($options as $key => $label) {
            echo '<label style="display:block;margin-bottom:5px;">';
            echo '<input type="checkbox" name="pr_settings[trigger_on][]" value="' . esc_attr($key) . '" ' . checked(in_array($key, (array) $val, true), true, false) . ' /> ' . esc_html($label);
            echo '</label>';
        }
    }

    public function field_mode()
    {
        $val = POP_REDIRECT::get_setting('mode', 'bar');
        echo '<label style="display:block;margin-bottom:8px;"><input type="radio" name="pr_settings[mode]" value="bar" ' . checked('bar', $val, false) . ' /> ';
        echo '<strong>' . esc_html__('Bar mode', 'pop-redirect') . '</strong> — ' . esc_html__('Shows a sticky notification bar. Visitor clicks the button to open the tab. Most reliable.', 'pop-redirect') . '</label>';
        echo '<label style="display:block;"><input type="radio" name="pr_settings[mode]" value="auto" ' . checked('auto', $val, false) . ' /> ';
        echo '<strong>' . esc_html__('Auto mode', 'pop-redirect') . '</strong> — ' . esc_html__('Opens the tab on the visitor\'s first click anywhere on the page. Optional hint text can be shown.', 'pop-redirect') . '</label>';
    }

    public function field_bar_position()
    {
        $val = POP_REDIRECT::get_setting('bar_position', 'bottom');
        echo '<select name="pr_settings[bar_position]">';
        echo '<option value="bottom" ' . selected('bottom', $val, false) . '>' . esc_html__('Bottom', 'pop-redirect') . '</option>';
        echo '<option value="top" '    . selected('top',    $val, false) . '>' . esc_html__('Top', 'pop-redirect')    . '</option>';
        echo '</select>';
        echo '<p class="description">' . esc_html__('Only applies to Bar mode.', 'pop-redirect') . '</p>';
    }

    public function field_bar_color()
    {
        $val = POP_REDIRECT::get_setting('bar_color', '#1a73e8');
        echo '<input type="color" name="pr_settings[bar_color]" value="' . esc_attr($val) . '" />';
        echo '<p class="description">' . esc_html__('Background color of the notification bar.', 'pop-redirect') . '</p>';
    }

    public function field_bar_text()
    {
        $val = POP_REDIRECT::get_setting('bar_text', 'Visit our special offer');
        echo '<input type="text" name="pr_settings[bar_text]" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('Message shown in the bar.', 'pop-redirect') . '</p>';
    }

    public function field_bar_btn_text()
    {
        $val = POP_REDIRECT::get_setting('bar_btn_text', 'Open Now');
        echo '<input type="text" name="pr_settings[bar_btn_text]" value="' . esc_attr($val) . '" style="width:200px;" />';
        echo '<p class="description">' . esc_html__('Text on the button inside the bar.', 'pop-redirect') . '</p>';
    }

    public function field_auto_text()
    {
        $val = POP_REDIRECT::get_setting('auto_text', 'Priyo Email');
        echo '<input type="text" name="pr_settings[auto_text]" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . esc_html__('Small hint shown at the bottom of the screen in Auto mode. Leave empty to hide.', 'pop-redirect') . '</p>';
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

        if (! empty($clean['url']) && ! wp_http_validate_url($clean['url'])) {
            add_settings_error(
                'pr_settings',
                'pr_invalid_url',
                esc_html__('Please enter a valid URL (including http:// or https://).', 'pop-redirect'),
                'error'
            );
            $clean['url'] = '';
        }

        if (! empty($clean['enabled']) && empty($clean['url'])) {
            add_settings_error(
                'pr_settings',
                'pr_missing_url',
                esc_html__('URL is required when the plugin is enabled.', 'pop-redirect'),
                'error'
            );
        }

        return $clean;
    }

    /**
     * Enqueue admin CSS
     */
    public function enqueue_admin_assets($hook)
    {
        if ('toplevel_page_pop-redirect' !== $hook) return;
        wp_enqueue_style('pr-admin', PR_PLUGIN_URL . 'assets/css/admin.css', array(), PR_VERSION);
        wp_enqueue_script('pr-admin', PR_PLUGIN_URL . 'assets/js/admin.js', array(), PR_VERSION, true);
    }

    /**
     * Render the settings page HTML
     */
    public function render_settings_page()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'pop-redirect'));
        }
?>
        <div class="wrap pr-wrap">
            <h1>
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>

            <?php settings_errors(); ?>

            <div class="pr-layout">
                <div class="pr-main">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('pr_settings_group');
                        do_settings_sections('pop-redirect');
                        submit_button(esc_html__('Save Settings', 'pop-redirect'));
                        ?>
                    </form>
                </div>

                <div class="pr-sidebar">
                    <div class="pr-box">
                        <h3>📋 How It Works</h3>
                        <ol>
                            <li>Enter the URL you want to open.</li>
                            <li>Set a delay (1–3 seconds works best).</li>
                            <li>Enable "Once Per Session" to avoid bothering repeat visitors.</li>
                            <li>Choose which pages trigger the tab.</li>
                            <li>Save and test on your site.</li>
                        </ol>
                    </div>
                    <div class="pr-box">
                        <h3>⚠️ Browser Note</h3>
                        <p>Modern browsers may block pop-ups if the visitor hasn't interacted with the page. A delay of <strong>1000–3000ms</strong> after scroll or click interaction improves success rate.</p>
                    </div>
                    <div class="pr-box">
                        <h3>🔌 Plugin Version</h3>
                        <p>Version: <strong><?php echo esc_html(PR_VERSION); ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
}
