<?php

namespace CHT\admin;

use CHT\includes\CHT_PRO_Widget;

if (!defined('ABSPATH')) { exit; }
/**
 * Class CHT_PRO_Admin_Base
 * @since 1.0
 */

require_once 'class-social-icons.php';

class CHT_PRO_Admin_Base
{
    public $page;
    public $socials;
    public $colors;
    public $fa_icons;
    public $widget_index = '';
    protected $token;
    protected static $response = null;
    protected static $checked_token = false;
    protected $upgrade_slug;

    public function __construct()
    {
        $plugin = CHT_PRO_Widget::get_instance(); // get class instance
        $this->plugin_slug = $plugin->get_plugin_slug(); // plugin slug
        $this->friendly_name = $plugin->get_name(); // plugin name
        $this->socials = CHT_PRO_Social_Icons::get_instance()->get_icons_list(); // social icon list
        $this->colors = CHT_PRO_Social_Icons::get_instance()->get_colors(); // widget color list
        $this->token = $this->get_token(); // Plugin token
        $this->upgrade_slug = $this->plugin_slug . '-upgrade'; // Plugin upgrade slug

        /* Initialize function for admin */
        if (is_admin()) { // admin actions

            // add chaty menu
            add_action('admin_menu', array($this, 'cht_admin_setting_page'));

            // Adds all of the options for the administrative settings
            add_action('admin_init', array($this, 'cht_register_inputs'));

            // add css
            add_action('admin_head', array($this, 'cht_inline_css_admin'));

            // add popup model on Plugin List page
            add_action('admin_footer', array($this, 'add_deactivate_modal'));

            // sending message to plugin owner why plugin is deactivated
            add_action('wp_ajax_chaty_plugin_deactivate', array($this, 'chaty_plugin_deactivate'));

            /* ADD Upgrade link to plugin */
            add_filter('plugin_action_links_' . WCP_PRO_CHATY_BASE, [$this, 'plugin_action_links']);

            add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'), 99);

            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'), 99);

            $widget = filter_input(INPUT_GET, 'widget', FILTER_SANITIZE_STRING);

            if (isset($widget) && !empty($widget) && is_numeric($widget) && $widget > 0) {
                $this->widget_index = "_" . esc_attr($widget);
            }

            $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);

            $total_widget = $this->get_total_widgets();

            if((isset($page) && $page == "chaty-widget-settings") || (isset($page) && $page == "chaty-app" && empty($total_widget))) {
                $this->widget_index = "_new_widget";

                $option = array(
                    'mobile' => '1',
                    'desktop' => '1',
                );

                update_option('cht_devices'.$this->widget_index, $option);
                update_option('cht_active'.$this->widget_index, '1');
                update_option('cht_position'.$this->widget_index, 'right');
                update_option('cht_cta'.$this->widget_index, 'Contact us');
                update_option('cht_cta_action'.$this->widget_index, 'Button action');
                update_option('cht_cta_text_color'.$this->widget_index, 'Button action');
                update_option('cht_cta_bg_color'.$this->widget_index, 'Button action');
                update_option('cht_numb_slug'.$this->widget_index, ',Phone,Whatsapp');
                update_option('cht_social_whatsapp'.$this->widget_index, '');
                update_option('cht_social_phone'.$this->widget_index, '');
                update_option('cht_widget_size'.$this->widget_index, '54');
                update_option('widget_icon'.$this->widget_index, 'chat-base');
                update_option('cht_widget_img'.$this->widget_index, '');
                update_option('cht_widget_img'.$this->widget_index, '');
                update_option('chaty_attention_effect'.$this->widget_index, '');
                update_option('chaty_default_state'.$this->widget_index, 'click');
                update_option('cht_close_button'.$this->widget_index, 'yes');
                update_option('chaty_trigger_on_time'.$this->widget_index, 'yes');
                update_option('chaty_trigger_time'.$this->widget_index, '0');
                update_option('cht_created_on'.$this->widget_index, date("Y-m-d"));
            }

            // activate and deactivate license key
            add_action('wp_ajax_activate_deactivate_chaty_license_key', array($this, 'activate_deactivate_chaty_license_key'));
            add_action('wp_ajax_cht_save_analytics_status', array($this, 'save_analytics_status'));
            add_action("wp_ajax_update_channel_setting", array($this, 'update_channel_setting'));
        }
    }

    public function update_channel_setting() {
        if(!empty($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], "Contact_Us-settings")) {
            update_option("chaty_contact_us_setting", "hide");
        }
        echo esc_attr("1");
        die;
    }

    public function save_analytics_status() {
        if(current_user_can("manage_options")) {
            $postData = filter_input_array(INPUT_POST);
            if(isset($postData['nonce']) && wp_verify_nonce($postData['nonce'], "cht_analytics_status")) {
                $status = isset($postData['status'])?$postData['status']:"off";
                if($status != "on" && $status != "off") {
                    $status = "off";
                }
                update_option("cht_data_analytics_status", $status);
                if(function_exists('cht_clear_all_caches')) {
                    cht_clear_all_caches();
                }
            }
        }
    }

    public function plugin_action_links($links) {
        $links['need_help'] = '<a target="_blank" href="https://premio.io/help/chaty/?utm_source=pluginspage" >' . __('Need help?', CHT_OPT) . '</a>';
        return $links;
    }

    /* sending message to plugin owner why plugin is deactivated */

    /* chaty_plugin_deactivate start */
    public function chaty_plugin_deactivate()
    {
        $postData = $_POST;
        $errorCounter = 0;
        $response = array();
        $response['status'] = 0;
        $response['message'] = "";
        $response['valid'] = 1;
        $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
        $nonce = filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);
        if (empty($reason)) {             // checking for required validation
            $errorCounter++;
            $response['message'] = "Please provide reason";
        } else if (empty($nonce)) {       // checking for required validation
            $response['message'] = esc_attr__("Your request is not valid", CHT_OPT);
            $errorCounter++;
            $response['valid'] = 0;
        } else if (!current_user_can("manage_options")) {       // checking for required validation
            $response['message'] = esc_attr__("Your request is not valid", CHT_OPT);
            $errorCounter++;
            $response['valid'] = 0;
        } else {
            if (!wp_verify_nonce($nonce, 'chaty_deactivate_nonce')) {
                $response['message'] = esc_attr__("Your request is not valid", CHT_OPT);
                $errorCounter++;
                $response['valid'] = 0;
            }
        }
        if ($errorCounter == 0) {
            global $current_user;
            $email = "none@none.none";

            if (isset($postData['email_id']) && !empty($postData['email_id']) && filter_var($postData['email_id'], FILTER_VALIDATE_EMAIL)) {
                $email = $postData['email_id'];
            }
            $domain = site_url();
            $user_name = $current_user->first_name . " " . $current_user->last_name;

            $response['status'] = 1;

            /* sending message to Crisp */
            $post_message = array();

            $message_data = array();
            $message_data['key'] = "Plugin";
            $message_data['value'] = "Chaty Pro";
            $post_message[] = $message_data;

            $message_data = array();
            $message_data['key'] = "Plugin Version";
            $message_data['value'] = CHT_CURRENT_VERSION;
            $post_message[] = $message_data;

            $message_data = array();
            $message_data['key'] = "Domain";
            $message_data['value'] = $domain;
            $post_message[] = $message_data;

            $message_data = array();
            $message_data['key'] = "Email";
            $message_data['value'] = $email;
            $post_message[] = $message_data;

            $message_data = array();
            $message_data['key'] = "WordPress Version";
            $message_data['value'] = esc_attr(get_bloginfo('version'));
            $post_message[] = $message_data;

            $message_data = array();
            $message_data['key'] = "PHP Version";
            $message_data['value'] = PHP_VERSION;
            $post_message[] = $message_data;

            $message_data = array();
            $message_data['key'] = "Message";
            $message_data['value'] = $reason;
            $post_message[] = $message_data;

            $api_params = array(
                'domain' => $domain,
                'email' => $email,
                'url' => site_url(),
                'name' => $user_name,
                'message' => $post_message,
                'plugin' => "Chaty Pro",
                'type' => "Uninstall",
            );

            /* Sending message to Crisp API */
            $crisp_response = wp_safe_remote_post("https://go.premio.io/crisp/crisp-send-message.php", array('body' => $api_params, 'timeout' => 15, 'sslverify' => true));

            if (is_wp_error($crisp_response)) {
                wp_safe_remote_post("https://go.premio.io/crisp/crisp-send-message.php", array('body' => $api_params, 'timeout' => 15, 'sslverify' => false));
            }
        }
        echo json_encode($response);
        wp_die();

    }
    /* chaty_plugin_deactivate end */

    /* function to sanitize input values */
    public static function chaty_sanitize_options($value)
    {
        $value = stripslashes($value);
        $value = filter_var($value, FILTER_SANITIZE_STRING);
        $value = htmlspecialchars($value);
        return $value;
    }

    // add popup model html on Plugin List page
    public function add_deactivate_modal()
    {
        if (current_user_can('manage_options')) {
            global $pagenow;

            if ('plugins.php' !== $pagenow) {     // checking for plugin page
                return;                             // return if it is not plugin page
            }

            include CHT_PRO_DIR . '/views/admin/chaty-deactivate-form.php';
        }
    }

    public function del_space($text)
    {
        return str_replace('_', ' ', $text);
    }

    /*Inline admin css for chaty menu on sidebar */
    public function cht_inline_css_admin()
    {
        ob_start();
        ?>
        <style>
            #toplevel_page_chaty-app img:hover, #toplevel_page_chaty-app img {
                opacity: 0 !important;
            }

            #toplevel_page_chaty-app:hover .dashicons-before {
                background-color: #00b9eb;
            }

            #toplevel_page_chaty-app .dashicons-before {
                background-color: #A0A3A8;
                -webkit-mask: url('<?php echo esc_url(plugins_url('../images/chaty.svg', __FILE__)) ?>') no-repeat center;
                mask: url('<?php echo esc_url(plugins_url('../images/chaty.svg', __FILE__)) ?>') no-repeat center;
            }

            .current#toplevel_page_chaty-app .dashicons-before {
                background-color: #fff;
            }
        </style>
        <?php
        echo ob_get_clean();

    }

    /* admin css files */
    public function enqueue_styles($page) {
        if($page != "toplevel_page_chaty-app" && $page != "chaty_page_chaty-widget-settings" && $page != "chaty_page_chaty-app-upgrade" && $page != "chaty_page_chaty-upgrade" && $page != 'chaty_page_widget-analytics' && $page != 'chaty_page_chaty-contact-form-feed') {
            return;
        }
        $query_args = array(
            'family' => 'Rubik:400,700|Oswald:400,600',
            'subset' => 'latin,latin-ext'
        );
        wp_enqueue_style('google_fonts', add_query_arg($query_args, "//fonts.googleapis.com/css"), array(), null);
        if($page != 'chaty_page_widget-analytics') {
            wp_enqueue_style($this->plugin_slug . 'spectrum', plugins_url('../admin/assets/css/spectrum.min.css', __FILE__));
            wp_enqueue_style($this->plugin_slug . 'datepicker', plugins_url('../admin/assets/css/timepicker.min.css', __FILE__));
            wp_enqueue_style($this->plugin_slug . 'select2', plugins_url('../admin/assets/css/select2.min.css', __FILE__));
            wp_enqueue_style($this->plugin_slug . 'intlTelInput', plugins_url('../admin/assets/css/intlTelInput.min.css', __FILE__));
            wp_enqueue_style( 'jquery-ui-css', plugins_url('../admin/assets/css/datepicker.min.css', __FILE__), array(), CHT_CURRENT_VERSION );
        }

        if($page == 'chaty_page_widget-analytics') {
            wp_enqueue_style( 'jquery-ui-css', plugins_url('../admin/assets/css/datepicker.min.css', __FILE__), array(), CHT_CURRENT_VERSION );
        }

        /* WP change this */
        wp_enqueue_style($this->plugin_slug, plugins_url('../admin/assets/css/cht-style.min.css', __FILE__), array(), CHT_CURRENT_VERSION);
    }

    /* admin js files */
    public function enqueue_scripts($page) {
        if($page != "toplevel_page_chaty-app" && $page != "chaty_page_chaty-widget-settings") {
            if($page == 'chaty_page_widget-analytics' || $page == "chaty_page_chaty-contact-form-feed") {
                wp_enqueue_script('jquery-ui-datepicker');
            }
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script($this->plugin_slug . 'fileinput', plugins_url('../admin/assets/js/fileinput.min.js', __FILE__), array('jquery'), CHT_CURRENT_VERSION);
        wp_enqueue_script($this->plugin_slug . 'pop', plugins_url('../admin/assets/js/popper.min.js', __FILE__), array('jquery'), CHT_CURRENT_VERSION);
        wp_enqueue_script($this->plugin_slug . 'spectrum', plugins_url('../admin/assets/js/spectrum.min.js', __FILE__), array('jquery'), CHT_CURRENT_VERSION);
        wp_enqueue_script($this->plugin_slug . 'timepicker', plugins_url('../admin/assets/js/timepicker.min.js', __FILE__), array('jquery'), CHT_CURRENT_VERSION);
        wp_enqueue_script($this->plugin_slug . 'stick', plugins_url('../admin/assets/js/jquery.sticky.min.js', __FILE__), array('jquery'), CHT_CURRENT_VERSION);
        wp_enqueue_script($this->plugin_slug . 'select2', plugins_url('../admin/assets/js/select2.min.js', __FILE__), array('jquery'), CHT_CURRENT_VERSION);
        wp_enqueue_script($this->plugin_slug . 'intlTelInput', plugins_url('../admin/assets/js/intlTelInput.min.js', __FILE__), array('jquery'), CHT_CURRENT_VERSION);

        wp_enqueue_script('jquery-ui-datepicker');

        /* WP change this */
        wp_enqueue_editor();
        wp_enqueue_script($this->plugin_slug. 'chaty-js', plugins_url('../admin/assets/js/cht-scripts.js', __FILE__), array('jquery', 'wp-color-picker', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable'), CHT_CURRENT_VERSION);
        $whatsapp_settings = array();
        foreach($this->socials as $social) {
            $whatsapp_settings[$social['slug']] = "";
        }
        wp_localize_script($this->plugin_slug . 'chaty-js', 'cht_settings',
            array(
                'plugin_url' => CHT_PLUGIN_URL,
                'channel_settings' => $whatsapp_settings
            )
        );
        wp_localize_script($this->plugin_slug . 'pop', 'cht_nonce_ajax',
            array(
                'cht_nonce' => wp_create_nonce('cht_nonce_ajax')
            )
        );
    }

    /* admin chaty menu initialize function */
    public function cht_admin_setting_page()
    {
        // checking for user permission

        /* creating admin menu for chaty */
        $this->page = add_menu_page(
           esc_attr__('Chaty', CHT_OPT),
           esc_attr__('Chaty', CHT_OPT),
            'manage_options',
            $this->plugin_slug,
            array($this, 'display_cht_admin_page'),
            plugins_url('chaty-pro/images/chaty.svg')
        );

        $menu_text = "Dashboard";
        /* creating admin sub menu for chaty */
        add_submenu_page(
            $this->plugin_slug,
            $menu_text,
            $menu_text,
            'manage_options',
            $this->plugin_slug,
            array($this, 'display_cht_admin_page')
        );

        $page_function = ($this->is_pro())?"display_cht_admin_page":"chaty_widget_page";
        $page_slug = ($this->is_pro())?"chaty-widget-settings":"chaty-upgrade";
        add_submenu_page(
            $this->plugin_slug,
           esc_attr__('Settings Admin', CHT_OPT),
           esc_attr__('+ Create New Widget', CHT_OPT),
            'manage_options',
            $page_slug,
            array($this, $page_function)
        );

        /* creating admin sub menu for chaty */
        $upgrade_page = add_submenu_page(
            $this->plugin_slug,
            esc_attr__('Widget Analytics', CHT_OPT),
            esc_attr__('Widget Analytics', CHT_OPT),
            'manage_options',
            'widget-analytics',
            array($this, 'display_cht_admin_widget_analytics')
        );
        add_action('admin_print_styles-' . $upgrade_page, array($this, 'enqueue_styles'));

        $getData = filter_input_array(INPUT_GET);
        if(isset($getData['hide_chaty_recommended_plugin']) && isset($getData['nonce'])) {
            if(current_user_can('manage_options')) {
                $nonce = $getData['nonce'];
                if(wp_verify_nonce($nonce, "chaty_recommended_plugin")) {
                    update_option('hide_chaty_recommended_plugin',true);
                }
            }
        }

        /* creating admin sub menu for chaty */
        $upgrade_page = add_submenu_page(
            $this->plugin_slug,
            esc_attr__('Contact form leads', CHT_OPT),
            esc_attr__('Contact form leads', CHT_OPT),
            'manage_options',
            "chaty-contact-form-feed",
            array($this, 'chaty_contact_form_feed')
        );
        add_action('admin_print_styles-' . $upgrade_page, array($this, 'enqueue_styles')); /* creating admin sub menu for chaty */

        $recommended_plugin = get_option("hide_chaty_recommended_plugin");
        if($recommended_plugin === false) {
            add_submenu_page(
                $this->plugin_slug,
                esc_html__('Recommended Plugins', CHT_OPT),
                esc_html__('Recommended Plugins', CHT_OPT),
                'manage_options',
                'recommended-chaty-plugins',
                array($this, 'recommended_plugins')
            );
        }

        $upgrade_page = add_submenu_page(
            $this->plugin_slug,
           esc_attr__('License Key', CHT_OPT),
           esc_attr__('License Key', CHT_OPT),
            'manage_options',
            $this->upgrade_slug,
            array($this, 'display_cht_admin_upgrade_page')
        );
        add_action('admin_print_styles-' . $upgrade_page, array($this, 'enqueue_styles'));

    }

    public function chaty_contact_form_feed() {
        include_once CHT_PRO_DIR . '/views/admin/contact-form-feed.php';
    }

    public function recommended_plugins() {
        include_once CHT_PRO_DIR . '/views/admin/recommended-plugins.php';
    }

    public function display_cht_admin_widget_analytics() {
        if($this->is_pro()) {
            $socials = array();
            foreach ($this->socials as $social) {
                $socials[strtolower($social['slug'])] = $social;
            }
            include_once CHT_PRO_DIR . '/views/admin/widget_analytics.php';
        } else {
            include_once CHT_PRO_DIR . '/views/admin/pro_analytics.php';
        }
    }

    /* returns upgrade menu item url */
    public function getUpgradeMenuItemUrl()
    {
        return admin_url("admin.php?page=chaty-app-upgrade");
    }

    public function chaty_widget_page() {
        include_once CHT_PRO_DIR . '/views/admin/chaty_widget.php';
    }

    /* chaty admin page for settings */
    public function display_cht_admin_page()
    {

        $total_widgets = $this->get_total_widgets();
        $has_no_widgets = 0;
        if(isset($_GET['widget']) || (isset($_GET['page']) && $_GET['page'] == "chaty-widget-settings") || empty($total_widgets)) {
            if(empty($total_widgets)) {
                $has_no_widgets = 1;
            }
            $fonts = self::get_font_list();
            include_once CHT_PRO_DIR . '/views/admin/admin.php';
        } else {
            include_once CHT_PRO_DIR . '/views/admin/dashboard.php';
        }

        if(isset($_REQUEST['show_message']) && $_REQUEST['show_message'] == 1) { ?>
            <div class="toast-message">
                <div class="toast-close-btn"><a href="javascript:;"></a></div>
                <div class="toast-message-title">Settings Updated</div>
                <div class="toast-message-body">Your settings has been saved</div>
            </div>
        <?php }
    }

    /* chaty admin page for license key */
    public function display_cht_admin_upgrade_page()
    {
        include_once CHT_PRO_DIR . '/views/admin/upgrade.php';
    }

    /* returns EDD token */
    protected function get_token()
    {
        return get_option('cht_token');
    }

    // this site domain
    public function get_site()
    {
        $permalink = get_home_url();
        return $permalink;
    }

    /* returns is license key is activated or not */
    public function is_pro($token = '')
    {
        if ($token === '') {
            $token = $this->get_token();
        }

        // return false if license key is not exists
        if (empty($token))
            return false;

        if (self::$response == null || self::$checked_token == false) { // checking for license key data

            $license_data = get_transient("cht_token_data");
            if($license_data !== false && !empty($license_data)) {
                self::$response = $license_data;
            } else {
                $api_params = array(
                    'edd_action' => 'check_license',
                    'license' => $token,
                    'item_id' => CHT_CHATY_PLUGIN_ID,
                    'url' => site_url()
                );

                /* checking for valid license key on premio.io */
                $response = wp_safe_remote_post(CHT_CHATY_PLUGIN_URL, array('body' => $api_params, 'timeout' => 15, 'sslverify' => true));

                if (is_wp_error($response)) {
                    $response = wp_safe_remote_post(CHT_CHATY_PLUGIN_URL, array('body' => $api_params, 'timeout' => 15, 'sslverify' => true));
                }

                if (is_wp_error($response)) {
                    self::$response = null;                                 // save null if error in response
                } else {
                    $response = json_decode(wp_remote_retrieve_body($response), true);      // save response
                    set_transient("cht_token_data", $response, DAY_IN_SECONDS);
                    self::$response = $response;
                }
            }
        }

        $body = null;

        if (self::$response['success'] == 1) {
            $body = self::$response;                                    // save response data
        }

        if (!empty($token)) {
            self::$checked_token = true;
        }

        if (isset($body['license']) && ($body['license'] == "valid" || $body['license'] == "expired")) {
            return true;            // return true if license key is expired  or valid
        } else {
            return false;           // return fals if license key is not valid
        }
    }

    /* compare current date with license expiry date */
    public function data_has_expired()
    {
        return strtotime(date('Y-m-d')) > strtotime(date('Y-m-d', strtotime($this->active_license())));
    }

    /* returns is license key is expired or not */
    public function is_expired()
    {
        if (self::$response != null) {
            $body = self::$response;
            if (isset($body['license']) && $body['license'] == "expired") {
                return $body['expires'];
            }
        }
        return false;
    }

    /* checking for license is expired or not */
    public function active_license()
    {
        if (!$this->is_pro())
            return;
        $body = self::$response;
        if (isset($body['expires']) && $body['expires']) {
            return $body['expires'];
        }
    }

    /* checking for license key */
    public function data_check()
    {
        if ($this->is_pro()) {
            return false;
        };
    }

    /* get current color for widget */
    public function get_current_color()
    {
        $def_color = get_option('cht_color' . $this->widget_index);
        $custom_color = get_option('cht_custom_color' . $this->widget_index);     // checking for custom color
        if (!empty($custom_color)) {
            $color = $custom_color;
        } else {
            $color = $def_color;
        }
        $color = strtoupper($color);
        return $color;
    }

    /* checking for widget position */
    public function get_position_style()
    {
        $position = get_option('cht_position' . $this->widget_index);         // checking for custom position
        $pos_style = 'left: 25px; bottom: 25px; right: auto';
        if ($position === 'custom') {
            $pos_side = get_option('positionSide' . $this->widget_index);
            $bot = (get_option('cht_bottom_spacing' . $this->widget_index)) ? get_option('cht_bottom_spacing' . $this->widget_index) : '25';
            $side = (get_option('cht_side_spacing' . $this->widget_index)) ? get_option('cht_side_spacing' . $this->widget_index) : '25';
            $pos_style = 'left: ' . $side . 'px; bottom: ' . $bot . 'px; right: auto';
            if ($pos_side === 'right') {
                $pos_style = 'left: auto; bottom: ' . $bot . 'px; right: ' . $side . 'px';
            }
        } elseif ($position === 'right') {              // checking for right position
            $pos_style = 'left: auto; bottom: 25px; right: 25px';
        }
        return $pos_style; // return position style
    }

    public function cht_register_inputs()
    {
        if (current_user_can('manage_options')) {

            global $wpdb;
            $table_name = $wpdb->prefix . 'chaty_contact_form_leads';
            $postData = filter_input_array(INPUT_POST);
            if(isset($postData['remove_chaty_leads'])) {
                if(wp_verify_nonce($postData['remove_chaty_leads'], "remove_chaty_leads")) {
                    if(isset($postData['chaty_leads']) && !empty($postData['chaty_leads'])) {
                        if(isset($postData['action']) && $postData['action'] == "delete_message") {
                            if(is_array($postData['chaty_leads'])) {
                                $chaty_leads = $postData['chaty_leads'];
                                $chaty_leads = implode(",", $chaty_leads);
                            } else {
                                $chaty_leads = $postData['chaty_leads'];
                            }
                            if($chaty_leads) {
                                $delete = $wpdb->query("DELETE FROM {$table_name} WHERE id IN(".$chaty_leads.")");

                                $paged = isset($postData['paged'])&&!empty($postData['paged'])&&is_numeric($postData['paged'])&&$postData['paged']>0?$postData['paged']:1;
                                $search = isset($postData['search'])&&!empty($postData['search'])?$postData['search']:"";
                                $url = admin_url("admin.php?page=chaty-contact-form-feed");
                                if(intval($paged) > 1) {
                                    $url .= "&paged=".$paged;
                                }
                                if(!empty($search)) {
                                    $url .= "&search=".$search;
                                }
                                wp_redirect($url);
                                exit;
                            }
                        }
                    }
                }
            }
            $postData = filter_input_array(INPUT_GET);
            if(isset($postData['remove_chaty_leads'])) {
                if(wp_verify_nonce($postData['remove_chaty_leads'], "remove_chaty_leads")) {
                    if(isset($postData['chaty_leads']) && !empty($postData['chaty_leads'])) {
                        if(isset($postData['action']) && $postData['action'] == "delete_message") {
                            if(is_array($postData['chaty_leads'])) {
                                $chaty_leads = $postData['chaty_leads'];
                                $chaty_leads = implode(",", $chaty_leads);
                            } else {
                                $chaty_leads = $postData['chaty_leads'];
                            }
                            if(!empty($chaty_leads)) {
                                if($chaty_leads == "remove-all") {
                                    $delete = $wpdb->query("TRUNCATE TABLE {$table_name}");
                                } else {
                                    $delete = $wpdb->query("DELETE FROM {$table_name} WHERE id IN(" . $chaty_leads . ")");
                                }
                                $paged = isset($postData['paged'])&&!empty($postData['paged'])&&is_numeric($postData['paged'])&&$postData['paged']>0?$postData['paged']:1;
                                $search = isset($postData['search'])&&!empty($postData['search'])?$postData['search']:"";
                                $url = admin_url("admin.php?page=chaty-contact-form-feed");
                                if(intval($paged) > 1) {
                                    $url .= "&paged=".$paged;
                                }
                                if(!empty($search)) {
                                    $url .= "&search=".$search;
                                }
                                wp_redirect($url);
                                exit;
                            }
                        }
                    }
                }
            }
            $postData = filter_input_array(INPUT_GET);

            if(isset($postData['download_chaty_file']) && $postData['download_chaty_file'] == "chaty_contact_leads" && isset($postData['nonce'])) {
                if(wp_verify_nonce($postData['nonce'], "download_chaty_contact_leads")) {
                    $file = "chaty_contact_leads.csv";
                    $fp = fopen($file, "w")or die("Error Couldn't open {$file} for writing!");

                    global $wpdb;
                    $contact_lists_table = $wpdb->prefix.'chaty_contact_form_leads';
                    $results = $wpdb->get_results( "SELECT * FROM ".$contact_lists_table." ORDER BY ID DESC");
                    $all_data = '';
                    foreach ($results as $res) {
                        if($res->widget_id == 0) {
                            $widget_name = "Default";
                        } else {
                            $widget_name = get_option("cht_widget_title_".$res->widget_id);
                            if(empty($widget_name)) {
                                $widget_name = "Widget #".($res->widget_id+1);
                            }
                        }
                        $fields = array(
                            $res->id,
                            $widget_name,
                            $res->name,
                            $res->email,
                            nl2br($res->message),
                            $res->created_on,
                            $res->ref_page,
                        );

                        fputcsv($fp, $fields);
                    }
                    fclose($fp);

                    $file_content = file_get_contents($file);
                    header("Content-Disposition: attachment; filename=".basename($file));
                    header("Content-Length: " . filesize($file));
                    header("Content-Type: application/octet-stream;");
                    readfile($file);
                    exit;
                }
            }

            if (isset($_GET['task']) && !empty($_GET['task']) && isset($_GET['nonce']) && !empty($_GET['nonce'])) {
                if (wp_verify_nonce($_GET['nonce'], "chaty_remove_analytics")) {
                    global $wpdb;
                    $chaty_table = $wpdb->prefix . 'chaty_widget_analysis';
                    if ($wpdb->get_var("show tables like '{$chaty_table}'") == $chaty_table) {
                        $query = "TRUNCATE " . $chaty_table;
                        $wpdb->query($query);

                        if (isset($_COOKIE['chaty_status_string'])) {
                            setcookie("chaty_status_string", "1", time() - 3600, "/");
                        }

                        wp_redirect(admin_url("admin.php?page=widget-analytics"));
                    }
                }
            }

            /* deactivating free version */
            $DS = DIRECTORY_SEPARATOR;
            $dirName = ABSPATH . "wp-content{$DS}plugins{$DS}chaty{$DS}";
            if (is_dir($dirName)) {
                if (is_plugin_active("chaty/cht-icons.php")) {
                    deactivate_plugins("chaty/cht-icons.php");
                }
            }

            /**
             * Adding settings fields
             */
            // Section One
            $nonce = filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);

            /*check for nonce*/
            if (isset($nonce) && !empty($nonce) && wp_verify_nonce($nonce, "chaty_plugin_nonce")) {

                $widget_no = "";
                $widget_index = "";
                $widget = filter_input(INPUT_POST, 'widget', FILTER_SANITIZE_STRING);
                $post_data = filter_input_array(INPUT_POST);

                if (!empty($post_data)) {
                    if (isset($widget) && !empty($widget) && $widget == "new-widget") {
                        $chaty_options = get_option("chaty_total_settings");
                        if (!empty($chaty_options) || $chaty_options != null || is_numeric($chaty_options) && $chaty_options > 0) {
                            $chaty_options = $chaty_options + 1;
                        } else {
                            $chaty_options = 1;
                        }
                        $widget_index = $chaty_options;
                        update_option("chaty_total_settings", $chaty_options);
                        $widget_no = "_" . $chaty_options;
                    } else if (isset($widget) && is_numeric($widget) && $widget > 0) {
                        $widget_index = $widget;
                        $widget_no = "_" . $widget_index;
                    }
                }
                foreach ($this->socials as $social) {
                    add_settings_field(
                        'cht_social' . $widget_no . '_' . $social['slug'],
                        ucfirst($social['slug']),
                        '',
                        $this->plugin_slug
                    );
                }

                // Section Two
                add_settings_field('cht_devices' . $widget_no, 'Devices', '', $this->plugin_slug);
                add_settings_field('cht_color' . $widget_no, 'Color', '', $this->plugin_slug);
                add_settings_field('cht_custom_color' . $widget_no, 'Color', '', $this->plugin_slug);
                add_settings_field('cht_position' . $widget_no, 'Position', '', $this->plugin_slug);
                add_settings_field('cht_widget_font' . $widget_no, 'Position', '', $this->plugin_slug);
                add_settings_field('positionSide' . $widget_no, 'PositionSide', '', $this->plugin_slug);
                add_settings_field('cht_bottom_spacing' . $widget_no, 'Bottom spacing', '', $this->plugin_slug);
                add_settings_field('cht_side_spacing' . $widget_no, 'Side spacing', '', $this->plugin_slug);
                add_settings_field('cht_cta' . $widget_no, 'CTA', '', $this->plugin_slug);
                add_settings_field('cht_cta_action' . $widget_no, 'CTA', '', $this->plugin_slug);
                add_settings_field('cht_cta_text_color' . $widget_no, 'CTA', '', $this->plugin_slug);
                add_settings_field('cht_cta_bg_color' . $widget_no, 'CTA', '', $this->plugin_slug);
                add_settings_field('cht_cta_switcher' . $widget_no, 'CTA switcher', '', $this->plugin_slug);
                add_settings_field('cht_date_rules' . $widget_no, 'Date rules', '', $this->plugin_slug);

                /* Setting field for page options */
                add_settings_field('cht_page_settings' . $widget_no, 'Show on', '', $this->plugin_slug);
                add_settings_field('cht_google_analytics' . $widget_no, 'Google Analytics', '', $this->plugin_slug);

                add_settings_field('cht_close_settings' . $widget_no, 'Close Settings', '', $this->plugin_slug);

                // section three
                add_settings_field('cht_active' . $widget_no, 'Active', '', $this->plugin_slug);
                add_settings_field('cht_pending_messages' . $widget_no, 'Pending messages', '', $this->plugin_slug);
                add_settings_field('cht_number_of_messages' . $widget_no, 'Number of messages', '', $this->plugin_slug);
                add_settings_field('cht_number_color' . $widget_no, 'Number color', '', $this->plugin_slug);
                add_settings_field('cht_number_bg_color' . $widget_no, 'Number bg color', '', $this->plugin_slug);

                // token
                add_settings_field('cht_token' . $widget_no, 'Token', '', $this->plugin_slug);

                // slug
                add_settings_field('cht_numb_slug' . $widget_no, 'Numb', '', $this->plugin_slug);

                add_settings_field('chaty_attention_effect' . $widget_no, 'Attention effect', '', $this->plugin_slug);
                add_settings_field('chaty_default_state' . $widget_no, 'Chaty default state', '', $this->plugin_slug);
                add_settings_field('cht_close_button' . $widget_no, 'Chaty close button', '', $this->plugin_slug);
                add_settings_field('chaty_trigger_on_time' . $widget_no, 'Time delay', '', $this->plugin_slug);
                add_settings_field('chaty_trigger_time' . $widget_no, 'Trigger time', '', $this->plugin_slug);
                add_settings_field('chaty_trigger_on_exit' . $widget_no, 'Trigger on exit', '', $this->plugin_slug);
                add_settings_field('chaty_trigger_on_scroll' . $widget_no, 'Trigger on page scroll', '', $this->plugin_slug);
                add_settings_field('chaty_trigger_on_page_scroll' . $widget_no, 'Trigger on page scroll', '', $this->plugin_slug);
                add_settings_field('cht_date_and_time_settings' . $widget_no, 'Date and time', '', $this->plugin_slug);
                add_settings_field('chaty_countries_list' . $widget_no, 'Countries', '', $this->plugin_slug);
                add_settings_field('chaty_icons_view' . $widget_no, 'Icons view', '', $this->plugin_slug);


                /* Traffic Rules - Pro Only*/
                add_settings_field('chaty_traffic_source' . $widget_no, 'Traffic source', '', $this->plugin_slug);
                add_settings_field('chaty_traffic_source_direct_visit' . $widget_no, 'Traffic source direct visit', '', $this->plugin_slug);
                add_settings_field('chaty_traffic_source_social_network' . $widget_no, 'Traffic source social network', '', $this->plugin_slug);
                add_settings_field('chaty_traffic_source_search_engine' . $widget_no, 'Traffic source search engine', '', $this->plugin_slug);
                add_settings_field('chaty_traffic_source_google_ads' . $widget_no, 'Traffic source google ads', '', $this->plugin_slug);
                add_settings_field('chaty_custom_traffic_rules' . $widget_no, 'Traffic source custom rules', '', $this->plugin_slug);

                add_settings_field('cht_created_on' . $widget_no, 'Chaty Created On', '', $this->plugin_slug);

                /**
                 * Registering settings fields
                 */


                // register field section one

                $time = time();
                update_option("chaty_updated_on", $time);

                $this->widget_index = $widget_no;

                $post_data = filter_input_array(INPUT_POST);

                foreach ($this->socials as $social) {
                    //register_setting($this->plugin_slug, 'cht_social'.$widget_no.'_' . $social['slug']);
                    if (isset($post_data['cht_social_' . $social['slug']])) {
                        update_option('cht_social' . $widget_no . '_' . $social['slug'], $post_data['cht_social_' . $social['slug']]);
                    }
                }

                if (isset($post_data['cht_created_on'])) {
                    update_option('cht_created_on' . $widget_no, $this->chaty_sanitize_options($post_data['cht_created_on']));
                }

                if (isset($post_data['cht_widget_title'])) {
                    update_option('cht_widget_title' . $widget_no, $this->chaty_sanitize_options($post_data['cht_widget_title']));
                }

                if (isset($post_data['cht_devices'])) {
                    update_option('cht_devices' . $widget_no, $this->chaty_sanitize_options($post_data['cht_devices']));
                }

                if (isset($post_data['cht_color'])) {
                    update_option('cht_color' . $widget_no, $this->chaty_sanitize_options($post_data['cht_color']));
                }

                if (isset($post_data['chaty_icons_view'])) {
                    update_option('chaty_icons_view' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_icons_view']));
                }

                if (isset($post_data['cht_custom_color'])) {
                    update_option('cht_custom_color' . $widget_no, $this->chaty_sanitize_options($post_data['cht_custom_color']));
                }

                if (isset($post_data['cht_numb_slug'])) {
                    update_option('cht_numb_slug' . $widget_no, $this->chaty_sanitize_options($post_data['cht_numb_slug']));
                }

                if (isset($post_data['cht_position'])) {
                    update_option('cht_position' . $widget_no, $this->chaty_sanitize_options($post_data['cht_position']));
                }

                if (isset($post_data['positionSide'])) {
                    update_option('positionSide' . $widget_no, $this->chaty_sanitize_options($post_data['positionSide']));
                }

                if (isset($post_data['cht_bottom_spacing'])) {
                    update_option('cht_bottom_spacing' . $widget_no, $this->chaty_sanitize_options($post_data['cht_bottom_spacing']));
                }

                if (isset($post_data['cht_side_spacing'])) {
                    update_option('cht_side_spacing' . $widget_no, $this->chaty_sanitize_options($post_data['cht_side_spacing']));
                }

                if (isset($post_data['cht_widget_font'])) {
                    update_option('cht_widget_font' . $widget_no, $this->chaty_sanitize_options($post_data['cht_widget_font']));
                }

                if (isset($post_data['cht_cta'])) {
                    update_option('cht_cta' . $widget_no, $this->chaty_sanitize_options($post_data['cht_cta']));
                }

                if (isset($post_data['cht_pending_messages'])) {
                    update_option('cht_pending_messages' . $widget_no, $this->chaty_sanitize_options($post_data['cht_pending_messages']));
                }

                if (isset($post_data['cht_number_of_messages'])) {
                    update_option('cht_number_of_messages' . $widget_no, $this->chaty_sanitize_options($post_data['cht_number_of_messages']));
                }

                if (isset($post_data['cht_number_color'])) {
                    update_option('cht_number_color' . $widget_no, $this->chaty_sanitize_options($post_data['cht_number_color']));
                }

                if (isset($post_data['cht_number_bg_color'])) {
                    update_option('cht_number_bg_color' . $widget_no, $this->chaty_sanitize_options($post_data['cht_number_bg_color']));
                }

                if (isset($post_data['cht_cta_action'])) {
                    update_option('cht_cta_action' . $widget_no, $this->chaty_sanitize_options($post_data['cht_cta_action']));
                }

                if (isset($post_data['cht_cta_text_color'])) {
                    update_option('cht_cta_text_color' . $widget_no, $this->chaty_sanitize_options($post_data['cht_cta_text_color']));
                }

                if (isset($post_data['cht_cta_bg_color'])) {
                    update_option('cht_cta_bg_color' . $widget_no, $this->chaty_sanitize_options($post_data['cht_cta_bg_color']));
                }

                if (isset($post_data['chaty_custom_css'])) {
                    update_option('chaty_custom_css' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_custom_css']));
                }

//            echo "<pre>"; print_r($post_data); die;
                if (isset($post_data['chaty_countries_list'])) {
                    update_option('chaty_countries_list' . $widget_no, $post_data['chaty_countries_list']);
                } else {
                    update_option('chaty_countries_list' . $widget_no, array());
                }

                if (isset($post_data['cht_page_settings'])) {
                    update_option('cht_page_settings' . $widget_no, $post_data['cht_page_settings']);
                } else {
                    update_option('cht_page_settings' . $widget_no, array());
                }

                if (isset($post_data['cht_google_analytics'])) {
                    update_option('cht_google_analytics' . $widget_no, $this->chaty_sanitize_options($post_data['cht_google_analytics']));
                } else {
                    update_option('cht_google_analytics' . $widget_no, "off");
                }

                if (isset($post_data['cht_close_settings'])) {
                    update_option('cht_close_settings' . $widget_no, $post_data['cht_close_settings']);
                }

                if (isset($post_data['cht_cta_switcher'])) {
                    update_option('cht_cta_switcher' . $widget_no, $this->chaty_sanitize_options($post_data['cht_cta_switcher']));
                }

                if (isset($post_data['cht_date_rules'])) {
                    update_option('cht_date_rules' . $widget_no, $post_data['cht_date_rules']);
                }

                if (isset($post_data['cht_widget_size'])) {
                    update_option('cht_widget_size' . $widget_no, $this->chaty_sanitize_options($post_data['cht_widget_size']));
                }

                if (isset($_FILES['cht_widget_img'])) {
                    $current_data = get_option('cht_widget_img' . $widget_no);
                    $file_data = $this->uploadCustomWidget("", $current_data, 'cht_widget_img', $widget_no);

                    update_option('cht_widget_img' . $widget_no, $file_data);
                }

                if (isset($post_data['widget_icon'])) {
                    update_option('widget_icon' . $widget_no, $this->chaty_sanitize_options($post_data['widget_icon']));
                }

                if (isset($post_data['cht_active'])) {
                    update_option('cht_active' . $widget_no, $this->chaty_sanitize_options($post_data['cht_active']));
                }

                if (isset($post_data['chaty_attention_effect'])) {
                    update_option('chaty_attention_effect' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_attention_effect']));
                }

                if (isset($post_data['chaty_default_state'])) {
                    update_option('chaty_default_state' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_default_state']));
                }

                if (isset($post_data['cht_close_button'])) {
                    update_option('cht_close_button' . $widget_no, $this->chaty_sanitize_options($post_data['cht_close_button']));
                }

                if (isset($post_data['chaty_trigger_on_time'])) {
                    update_option('chaty_trigger_on_time' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_trigger_on_time']));
                }

                if (isset($post_data['chaty_trigger_time'])) {
                    update_option('chaty_trigger_time' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_trigger_time']));
                }

                if (isset($post_data['chaty_trigger_on_exit'])) {
                    update_option('chaty_trigger_on_exit' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_trigger_on_exit']));
                }

                if (isset($post_data['chaty_trigger_on_scroll'])) {
                    update_option('chaty_trigger_on_scroll' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_trigger_on_scroll']));
                }

                if (isset($post_data['chaty_trigger_on_page_scroll'])) {
                    update_option('chaty_trigger_on_page_scroll' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_trigger_on_page_scroll']));
                }

                if (isset($post_data['cht_close_button_text'])) {
                    update_option('cht_close_button_text' . $widget_no, $this->chaty_sanitize_options($post_data['cht_close_button_text']));
                }

                if (isset($post_data['cht_date_and_time_settings'])) {
                    update_option('cht_date_and_time_settings' . $widget_no, $post_data['cht_date_and_time_settings']);
                } else {
                    update_option('cht_date_and_time_settings' . $widget_no, array());
                }

                /* Traffic Source */
                if (isset($post_data['chaty_traffic_source'])) {
                    update_option('chaty_traffic_source' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_traffic_source']));
                }
                if (isset($post_data['chaty_traffic_source_direct_visit'])) {
                    update_option('chaty_traffic_source_direct_visit' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_traffic_source_direct_visit']));
                }
                if (isset($post_data['chaty_traffic_source_social_network'])) {
                    update_option('chaty_traffic_source_social_network' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_traffic_source_social_network']));
                }
                if (isset($post_data['chaty_traffic_source_search_engine'])) {
                    update_option('chaty_traffic_source_search_engine' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_traffic_source_search_engine']));
                }
                if (isset($post_data['chaty_traffic_source_google_ads'])) {
                    update_option('chaty_traffic_source_google_ads' . $widget_no, $this->chaty_sanitize_options($post_data['chaty_traffic_source_google_ads']));
                }

                if (isset($post_data['chaty_custom_traffic_rules'])) {
                    update_option('chaty_custom_traffic_rules' . $widget_no, $post_data['chaty_custom_traffic_rules']);
                } else {
                    update_option('chaty_custom_traffic_rules' . $widget_no, array());
                }

                cht_clear_all_caches();
//                wp_safe_redirect(admin_url("admin.php?page=chaty-app&show_message=1&widget=".trim($widget_no, "_")));

                wp_safe_redirect(admin_url("admin.php?page=chaty-app&show_message=1"));
                exit;
                exit;
            }
        }
    }

    /* checking for devices desktop/mobile */
    public function device()
    {
        return 'desktop_active mobile_active';
    }

    /* return custom widget URL if uploaded */
    public function getCustomWidgetImg($index = 0)
    {
        if(empty($index)) {
            $index = $this->widget_index;
        }
        $value = get_option('cht_widget_img' . $index);
        $url = isset($value['url']) ? $value['url'] : '';
        if(!empty($url)) {
            $url = str_replace("http:","", $url);
        }
        return $url;
    }

    /* uploads custom widget image */
    public function uploadCustomWidget($value, $old_value, $option, $widget_no = "")
    {
//        $index = $this->widget_index;
        $option = !empty($option) ? $option : 'cht_widget_img';
//        $option = $option.$widget_no;
        $allowed_ext = ['jpeg', 'png', 'jpg', 'svg'];
        if (!function_exists('wp_handle_upload'))
            require_once(ABSPATH . 'wp-admin/includes/file.php');

        if(isset($_FILES[$option]) && !empty($_FILES[$option])) {
            $file = &$_FILES[$option];
            $type = wp_check_filetype($file['name']);

            if (!in_array($type['ext'], $allowed_ext)) {
                return $old_value;                                  // return old file data if extension is not valid
            }
            $overrides = array('test_form' => false);
            $movefile = wp_handle_upload($file, $overrides);        // upload file

            if ($movefile && empty($movefile['error'])) {

                if (isset($old_value['file'])) {
                    wp_delete_file($old_value['file']);             // remove old file from server if exists
                }

                return array(
                    'file' => $movefile['file'],
                    'url' => $movefile['url']
                );
            }
        }
        return array();
    }

    /* returns CTA text */
    public function getCallToAction()
    {
        if (get_option('cht_cta' . $this->widget_index)) {
            $res = nl2br(get_option('cht_cta' . $this->widget_index));
            $res = str_replace(array("\n", "\r"), "", $res);
            return $res;
        }
        return '';
    }

    public static function get_font_list(){
        return array(
            // System fonts.
            "System Stack" => 'Default',
            'Arial' => 'Default',
            'Tahoma' => 'Default',
            'Verdana' => 'Default',
            'Helvetica' => 'Default',
            'Times New Roman' => 'Default',
            'Trebuchet MS' => 'Default',
            'Georgia' => 'Default',

            // Google Fonts (last update: 23/10/2018).
            'ABeeZee' => 'Google Fonts',
            'Abel' => 'Google Fonts',
            'Abhaya Libre' => 'Google Fonts',
            'Abril Fatface' => 'Google Fonts',
            'Aclonica' => 'Google Fonts',
            'Acme' => 'Google Fonts',
            'Actor' => 'Google Fonts',
            'Adamina' => 'Google Fonts',
            'Advent Pro' => 'Google Fonts',
            'Aguafina Script' => 'Google Fonts',
            'Akronim' => 'Google Fonts',
            'Aladin' => 'Google Fonts',
            'Aldrich' => 'Google Fonts',
            'Alef' => 'Google Fonts',
            'Alef Hebrew' => 'Google Fonts', // Hack for Google Early Access.
            'Alegreya' => 'Google Fonts',
            'Alegreya SC' => 'Google Fonts',
            'Alegreya Sans' => 'Google Fonts',
            'Alegreya Sans SC' => 'Google Fonts',
            'Alex Brush' => 'Google Fonts',
            'Alfa Slab One' => 'Google Fonts',
            'Alice' => 'Google Fonts',
            'Alike' => 'Google Fonts',
            'Alike Angular' => 'Google Fonts',
            'Allan' => 'Google Fonts',
            'Allerta' => 'Google Fonts',
            'Allerta Stencil' => 'Google Fonts',
            'Allura' => 'Google Fonts',
            'Almendra' => 'Google Fonts',
            'Almendra Display' => 'Google Fonts',
            'Almendra SC' => 'Google Fonts',
            'Amarante' => 'Google Fonts',
            'Amaranth' => 'Google Fonts',
            'Amatic SC' => 'Google Fonts',
            'Amethysta' => 'Google Fonts',
            'Amiko' => 'Google Fonts',
            'Amiri' => 'Google Fonts',
            'Amita' => 'Google Fonts',
            'Anaheim' => 'Google Fonts',
            'Andada' => 'Google Fonts',
            'Andika' => 'Google Fonts',
            'Angkor' => 'Google Fonts',
            'Annie Use Your Telescope' => 'Google Fonts',
            'Anonymous Pro' => 'Google Fonts',
            'Antic' => 'Google Fonts',
            'Antic Didone' => 'Google Fonts',
            'Antic Slab' => 'Google Fonts',
            'Anton' => 'Google Fonts',
            'Arapey' => 'Google Fonts',
            'Arbutus' => 'Google Fonts',
            'Arbutus Slab' => 'Google Fonts',
            'Architects Daughter' => 'Google Fonts',
            'Archivo' => 'Google Fonts',
            'Archivo Black' => 'Google Fonts',
            'Archivo Narrow' => 'Google Fonts',
            'Aref Ruqaa' => 'Google Fonts',
            'Arima Madurai' => 'Google Fonts',
            'Arimo' => 'Google Fonts',
            'Arizonia' => 'Google Fonts',
            'Armata' => 'Google Fonts',
            'Arsenal' => 'Google Fonts',
            'Artifika' => 'Google Fonts',
            'Arvo' => 'Google Fonts',
            'Arya' => 'Google Fonts',
            'Asap' => 'Google Fonts',
            'Asap Condensed' => 'Google Fonts',
            'Asar' => 'Google Fonts',
            'Asset' => 'Google Fonts',
            'Assistant' => 'Google Fonts',
            'Astloch' => 'Google Fonts',
            'Asul' => 'Google Fonts',
            'Athiti' => 'Google Fonts',
            'Atma' => 'Google Fonts',
            'Atomic Age' => 'Google Fonts',
            'Aubrey' => 'Google Fonts',
            'Audiowide' => 'Google Fonts',
            'Autour One' => 'Google Fonts',
            'Average' => 'Google Fonts',
            'Average Sans' => 'Google Fonts',
            'Averia Gruesa Libre' => 'Google Fonts',
            'Averia Libre' => 'Google Fonts',
            'Averia Sans Libre' => 'Google Fonts',
            'Averia Serif Libre' => 'Google Fonts',
            'Bad Script' => 'Google Fonts',
            'Bahiana' => 'Google Fonts',
            'Bai Jamjuree' => 'Google Fonts',
            'Baloo' => 'Google Fonts',
            'Baloo Bhai' => 'Google Fonts',
            'Baloo Bhaijaan' => 'Google Fonts',
            'Baloo Bhaina' => 'Google Fonts',
            'Baloo Chettan' => 'Google Fonts',
            'Baloo Da' => 'Google Fonts',
            'Baloo Paaji' => 'Google Fonts',
            'Baloo Tamma' => 'Google Fonts',
            'Baloo Tammudu' => 'Google Fonts',
            'Baloo Thambi' => 'Google Fonts',
            'Balthazar' => 'Google Fonts',
            'Bangers' => 'Google Fonts',
            'Barlow' => 'Google Fonts',
            'Barlow Condensed' => 'Google Fonts',
            'Barlow Semi Condensed' => 'Google Fonts',
            'Barrio' => 'Google Fonts',
            'Basic' => 'Google Fonts',
            'Battambang' => 'Google Fonts',
            'Baumans' => 'Google Fonts',
            'Bayon' => 'Google Fonts',
            'Belgrano' => 'Google Fonts',
            'Bellefair' => 'Google Fonts',
            'Belleza' => 'Google Fonts',
            'BenchNine' => 'Google Fonts',
            'Bentham' => 'Google Fonts',
            'Berkshire Swash' => 'Google Fonts',
            'Bevan' => 'Google Fonts',
            'Bigelow Rules' => 'Google Fonts',
            'Bigshot One' => 'Google Fonts',
            'Bilbo' => 'Google Fonts',
            'Bilbo Swash Caps' => 'Google Fonts',
            'BioRhyme' => 'Google Fonts',
            'BioRhyme Expanded' => 'Google Fonts',
            'Biryani' => 'Google Fonts',
            'Bitter' => 'Google Fonts',
            'Black And White Picture' => 'Google Fonts',
            'Black Han Sans' => 'Google Fonts',
            'Black Ops One' => 'Google Fonts',
            'Bokor' => 'Google Fonts',
            'Bonbon' => 'Google Fonts',
            'Boogaloo' => 'Google Fonts',
            'Bowlby One' => 'Google Fonts',
            'Bowlby One SC' => 'Google Fonts',
            'Brawler' => 'Google Fonts',
            'Bree Serif' => 'Google Fonts',
            'Bubblegum Sans' => 'Google Fonts',
            'Bubbler One' => 'Google Fonts',
            'Buda' => 'Google Fonts',
            'Buenard' => 'Google Fonts',
            'Bungee' => 'Google Fonts',
            'Bungee Hairline' => 'Google Fonts',
            'Bungee Inline' => 'Google Fonts',
            'Bungee Outline' => 'Google Fonts',
            'Bungee Shade' => 'Google Fonts',
            'Butcherman' => 'Google Fonts',
            'Butterfly Kids' => 'Google Fonts',
            'Cabin' => 'Google Fonts',
            'Cabin Condensed' => 'Google Fonts',
            'Cabin Sketch' => 'Google Fonts',
            'Caesar Dressing' => 'Google Fonts',
            'Cagliostro' => 'Google Fonts',
            'Cairo' => 'Google Fonts',
            'Calligraffitti' => 'Google Fonts',
            'Cambay' => 'Google Fonts',
            'Cambo' => 'Google Fonts',
            'Candal' => 'Google Fonts',
            'Cantarell' => 'Google Fonts',
            'Cantata One' => 'Google Fonts',
            'Cantora One' => 'Google Fonts',
            'Capriola' => 'Google Fonts',
            'Cardo' => 'Google Fonts',
            'Carme' => 'Google Fonts',
            'Carrois Gothic' => 'Google Fonts',
            'Carrois Gothic SC' => 'Google Fonts',
            'Carter One' => 'Google Fonts',
            'Catamaran' => 'Google Fonts',
            'Caudex' => 'Google Fonts',
            'Caveat' => 'Google Fonts',
            'Caveat Brush' => 'Google Fonts',
            'Cedarville Cursive' => 'Google Fonts',
            'Ceviche One' => 'Google Fonts',
            'Chakra Petch' => 'Google Fonts',
            'Changa' => 'Google Fonts',
            'Changa One' => 'Google Fonts',
            'Chango' => 'Google Fonts',
            'Charmonman' => 'Google Fonts',
            'Chathura' => 'Google Fonts',
            'Chau Philomene One' => 'Google Fonts',
            'Chela One' => 'Google Fonts',
            'Chelsea Market' => 'Google Fonts',
            'Chenla' => 'Google Fonts',
            'Cherry Cream Soda' => 'Google Fonts',
            'Cherry Swash' => 'Google Fonts',
            'Chewy' => 'Google Fonts',
            'Chicle' => 'Google Fonts',
            'Chivo' => 'Google Fonts',
            'Chonburi' => 'Google Fonts',
            'Cinzel' => 'Google Fonts',
            'Cinzel Decorative' => 'Google Fonts',
            'Clicker Script' => 'Google Fonts',
            'Coda' => 'Google Fonts',
            'Coda Caption' => 'Google Fonts',
            'Codystar' => 'Google Fonts',
            'Coiny' => 'Google Fonts',
            'Combo' => 'Google Fonts',
            'Comfortaa' => 'Google Fonts',
            'Coming Soon' => 'Google Fonts',
            'Concert One' => 'Google Fonts',
            'Condiment' => 'Google Fonts',
            'Content' => 'Google Fonts',
            'Contrail One' => 'Google Fonts',
            'Convergence' => 'Google Fonts',
            'Cookie' => 'Google Fonts',
            'Copse' => 'Google Fonts',
            'Corben' => 'Google Fonts',
            'Cormorant' => 'Google Fonts',
            'Cormorant Garamond' => 'Google Fonts',
            'Cormorant Infant' => 'Google Fonts',
            'Cormorant SC' => 'Google Fonts',
            'Cormorant Unicase' => 'Google Fonts',
            'Cormorant Upright' => 'Google Fonts',
            'Courgette' => 'Google Fonts',
            'Cousine' => 'Google Fonts',
            'Coustard' => 'Google Fonts',
            'Covered By Your Grace' => 'Google Fonts',
            'Crafty Girls' => 'Google Fonts',
            'Creepster' => 'Google Fonts',
            'Crete Round' => 'Google Fonts',
            'Crimson Text' => 'Google Fonts',
            'Croissant One' => 'Google Fonts',
            'Crushed' => 'Google Fonts',
            'Cuprum' => 'Google Fonts',
            'Cute Font' => 'Google Fonts',
            'Cutive' => 'Google Fonts',
            'Cutive Mono' => 'Google Fonts',
            'Damion' => 'Google Fonts',
            'Dancing Script' => 'Google Fonts',
            'Dangrek' => 'Google Fonts',
            'David Libre' => 'Google Fonts',
            'Dawning of a New Day' => 'Google Fonts',
            'Days One' => 'Google Fonts',
            'Dekko' => 'Google Fonts',
            'Delius' => 'Google Fonts',
            'Delius Swash Caps' => 'Google Fonts',
            'Delius Unicase' => 'Google Fonts',
            'Della Respira' => 'Google Fonts',
            'Denk One' => 'Google Fonts',
            'Devonshire' => 'Google Fonts',
            'Dhurjati' => 'Google Fonts',
            'Didact Gothic' => 'Google Fonts',
            'Diplomata' => 'Google Fonts',
            'Diplomata SC' => 'Google Fonts',
            'Do Hyeon' => 'Google Fonts',
            'Dokdo' => 'Google Fonts',
            'Domine' => 'Google Fonts',
            'Donegal One' => 'Google Fonts',
            'Doppio One' => 'Google Fonts',
            'Dorsa' => 'Google Fonts',
            'Dosis' => 'Google Fonts',
            'Dr Sugiyama' => 'Google Fonts',
            'Droid Arabic Kufi' => 'Google Fonts', // Hack for Google Early Access.
            'Droid Arabic Naskh' => 'Google Fonts', // Hack for Google Early Access.
            'Duru Sans' => 'Google Fonts',
            'Dynalight' => 'Google Fonts',
            'EB Garamond' => 'Google Fonts',
            'Eagle Lake' => 'Google Fonts',
            'East Sea Dokdo' => 'Google Fonts',
            'Eater' => 'Google Fonts',
            'Economica' => 'Google Fonts',
            'Eczar' => 'Google Fonts',
            'El Messiri' => 'Google Fonts',
            'Electrolize' => 'Google Fonts',
            'Elsie' => 'Google Fonts',
            'Elsie Swash Caps' => 'Google Fonts',
            'Emblema One' => 'Google Fonts',
            'Emilys Candy' => 'Google Fonts',
            'Encode Sans' => 'Google Fonts',
            'Encode Sans Condensed' => 'Google Fonts',
            'Encode Sans Expanded' => 'Google Fonts',
            'Encode Sans Semi Condensed' => 'Google Fonts',
            'Encode Sans Semi Expanded' => 'Google Fonts',
            'Engagement' => 'Google Fonts',
            'Englebert' => 'Google Fonts',
            'Enriqueta' => 'Google Fonts',
            'Erica One' => 'Google Fonts',
            'Esteban' => 'Google Fonts',
            'Euphoria Script' => 'Google Fonts',
            'Ewert' => 'Google Fonts',
            'Exo' => 'Google Fonts',
            'Exo 2' => 'Google Fonts',
            'Expletus Sans' => 'Google Fonts',
            'Fahkwang' => 'Google Fonts',
            'Fanwood Text' => 'Google Fonts',
            'Farsan' => 'Google Fonts',
            'Fascinate' => 'Google Fonts',
            'Fascinate Inline' => 'Google Fonts',
            'Faster One' => 'Google Fonts',
            'Fasthand' => 'Google Fonts',
            'Fauna One' => 'Google Fonts',
            'Faustina' => 'Google Fonts',
            'Federant' => 'Google Fonts',
            'Federo' => 'Google Fonts',
            'Felipa' => 'Google Fonts',
            'Fenix' => 'Google Fonts',
            'Finger Paint' => 'Google Fonts',
            'Fira Mono' => 'Google Fonts',
            'Fira Sans' => 'Google Fonts',
            'Fira Sans Condensed' => 'Google Fonts',
            'Fira Sans Extra Condensed' => 'Google Fonts',
            'Fjalla One' => 'Google Fonts',
            'Fjord One' => 'Google Fonts',
            'Flamenco' => 'Google Fonts',
            'Flavors' => 'Google Fonts',
            'Fondamento' => 'Google Fonts',
            'Fontdiner Swanky' => 'Google Fonts',
            'Forum' => 'Google Fonts',
            'Francois One' => 'Google Fonts',
            'Frank Ruhl Libre' => 'Google Fonts',
            'Freckle Face' => 'Google Fonts',
            'Fredericka the Great' => 'Google Fonts',
            'Fredoka One' => 'Google Fonts',
            'Freehand' => 'Google Fonts',
            'Fresca' => 'Google Fonts',
            'Frijole' => 'Google Fonts',
            'Fruktur' => 'Google Fonts',
            'Fugaz One' => 'Google Fonts',
            'GFS Didot' => 'Google Fonts',
            'GFS Neohellenic' => 'Google Fonts',
            'Gabriela' => 'Google Fonts',
            'Gaegu' => 'Google Fonts',
            'Gafata' => 'Google Fonts',
            'Galada' => 'Google Fonts',
            'Galdeano' => 'Google Fonts',
            'Galindo' => 'Google Fonts',
            'Gamja Flower' => 'Google Fonts',
            'Gentium Basic' => 'Google Fonts',
            'Gentium Book Basic' => 'Google Fonts',
            'Geo' => 'Google Fonts',
            'Geostar' => 'Google Fonts',
            'Geostar Fill' => 'Google Fonts',
            'Germania One' => 'Google Fonts',
            'Gidugu' => 'Google Fonts',
            'Gilda Display' => 'Google Fonts',
            'Give You Glory' => 'Google Fonts',
            'Glass Antiqua' => 'Google Fonts',
            'Glegoo' => 'Google Fonts',
            'Gloria Hallelujah' => 'Google Fonts',
            'Goblin One' => 'Google Fonts',
            'Gochi Hand' => 'Google Fonts',
            'Gorditas' => 'Google Fonts',
            'Gothic A1' => 'Google Fonts',
            'Goudy Bookletter 1911' => 'Google Fonts',
            'Graduate' => 'Google Fonts',
            'Grand Hotel' => 'Google Fonts',
            'Gravitas One' => 'Google Fonts',
            'Great Vibes' => 'Google Fonts',
            'Griffy' => 'Google Fonts',
            'Gruppo' => 'Google Fonts',
            'Gudea' => 'Google Fonts',
            'Gugi' => 'Google Fonts',
            'Gurajada' => 'Google Fonts',
            'Habibi' => 'Google Fonts',
            'Halant' => 'Google Fonts',
            'Hammersmith One' => 'Google Fonts',
            'Hanalei' => 'Google Fonts',
            'Hanalei Fill' => 'Google Fonts',
            'Handlee' => 'Google Fonts',
            'Hanuman' => 'Google Fonts',
            'Happy Monkey' => 'Google Fonts',
            'Harmattan' => 'Google Fonts',
            'Headland One' => 'Google Fonts',
            'Heebo' => 'Google Fonts',
            'Henny Penny' => 'Google Fonts',
            'Herr Von Muellerhoff' => 'Google Fonts',
            'Hi Melody' => 'Google Fonts',
            'Hind' => 'Google Fonts',
            'Hind Guntur' => 'Google Fonts',
            'Hind Madurai' => 'Google Fonts',
            'Hind Siliguri' => 'Google Fonts',
            'Hind Vadodara' => 'Google Fonts',
            'Holtwood One SC' => 'Google Fonts',
            'Homemade Apple' => 'Google Fonts',
            'Homenaje' => 'Google Fonts',
            'IBM Plex Mono' => 'Google Fonts',
            'IBM Plex Sans' => 'Google Fonts',
            'IBM Plex Sans Condensed' => 'Google Fonts',
            'IBM Plex Serif' => 'Google Fonts',
            'IM Fell DW Pica' => 'Google Fonts',
            'IM Fell DW Pica SC' => 'Google Fonts',
            'IM Fell Double Pica' => 'Google Fonts',
            'IM Fell Double Pica SC' => 'Google Fonts',
            'IM Fell English' => 'Google Fonts',
            'IM Fell English SC' => 'Google Fonts',
            'IM Fell French Canon' => 'Google Fonts',
            'IM Fell French Canon SC' => 'Google Fonts',
            'IM Fell Great Primer' => 'Google Fonts',
            'IM Fell Great Primer SC' => 'Google Fonts',
            'Iceberg' => 'Google Fonts',
            'Iceland' => 'Google Fonts',
            'Imprima' => 'Google Fonts',
            'Inconsolata' => 'Google Fonts',
            'Inder' => 'Google Fonts',
            'Indie Flower' => 'Google Fonts',
            'Inika' => 'Google Fonts',
            'Inknut Antiqua' => 'Google Fonts',
            'Irish Grover' => 'Google Fonts',
            'Istok Web' => 'Google Fonts',
            'Italiana' => 'Google Fonts',
            'Italianno' => 'Google Fonts',
            'Itim' => 'Google Fonts',
            'Jacques Francois' => 'Google Fonts',
            'Jacques Francois Shadow' => 'Google Fonts',
            'Jaldi' => 'Google Fonts',
            'Jim Nightshade' => 'Google Fonts',
            'Jockey One' => 'Google Fonts',
            'Jolly Lodger' => 'Google Fonts',
            'Jomhuria' => 'Google Fonts',
            'Josefin Sans' => 'Google Fonts',
            'Josefin Slab' => 'Google Fonts',
            'Joti One' => 'Google Fonts',
            'Jua' => 'Google Fonts',
            'Judson' => 'Google Fonts',
            'Julee' => 'Google Fonts',
            'Julius Sans One' => 'Google Fonts',
            'Junge' => 'Google Fonts',
            'Jura' => 'Google Fonts',
            'Just Another Hand' => 'Google Fonts',
            'Just Me Again Down Here' => 'Google Fonts',
            'K2D' => 'Google Fonts',
            'Kadwa' => 'Google Fonts',
            'Kalam' => 'Google Fonts',
            'Kameron' => 'Google Fonts',
            'Kanit' => 'Google Fonts',
            'Kantumruy' => 'Google Fonts',
            'Karla' => 'Google Fonts',
            'Karma' => 'Google Fonts',
            'Katibeh' => 'Google Fonts',
            'Kaushan Script' => 'Google Fonts',
            'Kavivanar' => 'Google Fonts',
            'Kavoon' => 'Google Fonts',
            'Kdam Thmor' => 'Google Fonts',
            'Keania One' => 'Google Fonts',
            'Kelly Slab' => 'Google Fonts',
            'Kenia' => 'Google Fonts',
            'Khand' => 'Google Fonts',
            'Khmer' => 'Google Fonts',
            'Khula' => 'Google Fonts',
            'Kirang Haerang' => 'Google Fonts',
            'Kite One' => 'Google Fonts',
            'Knewave' => 'Google Fonts',
            'KoHo' => 'Google Fonts',
            'Kodchasan' => 'Google Fonts',
            'Kosugi' => 'Google Fonts',
            'Kosugi Maru' => 'Google Fonts',
            'Kotta One' => 'Google Fonts',
            'Koulen' => 'Google Fonts',
            'Kranky' => 'Google Fonts',
            'Kreon' => 'Google Fonts',
            'Kristi' => 'Google Fonts',
            'Krona One' => 'Google Fonts',
            'Krub' => 'Google Fonts',
            'Kumar One' => 'Google Fonts',
            'Kumar One Outline' => 'Google Fonts',
            'Kurale' => 'Google Fonts',
            'La Belle Aurore' => 'Google Fonts',
            'Laila' => 'Google Fonts',
            'Lakki Reddy' => 'Google Fonts',
            'Lalezar' => 'Google Fonts',
            'Lancelot' => 'Google Fonts',
            'Lateef' => 'Google Fonts',
            'Lato' => 'Google Fonts',
            'League Script' => 'Google Fonts',
            'Leckerli One' => 'Google Fonts',
            'Ledger' => 'Google Fonts',
            'Lekton' => 'Google Fonts',
            'Lemon' => 'Google Fonts',
            'Lemonada' => 'Google Fonts',
            'Libre Barcode 128' => 'Google Fonts',
            'Libre Barcode 128 Text' => 'Google Fonts',
            'Libre Barcode 39' => 'Google Fonts',
            'Libre Barcode 39 Extended' => 'Google Fonts',
            'Libre Barcode 39 Extended Text' => 'Google Fonts',
            'Libre Barcode 39 Text' => 'Google Fonts',
            'Libre Baskerville' => 'Google Fonts',
            'Libre Franklin' => 'Google Fonts',
            'Life Savers' => 'Google Fonts',
            'Lilita One' => 'Google Fonts',
            'Lily Script One' => 'Google Fonts',
            'Limelight' => 'Google Fonts',
            'Linden Hill' => 'Google Fonts',
            'Lobster' => 'Google Fonts',
            'Lobster Two' => 'Google Fonts',
            'Londrina Outline' => 'Google Fonts',
            'Londrina Shadow' => 'Google Fonts',
            'Londrina Sketch' => 'Google Fonts',
            'Londrina Solid' => 'Google Fonts',
            'Lora' => 'Google Fonts',
            'Love Ya Like A Sister' => 'Google Fonts',
            'Loved by the King' => 'Google Fonts',
            'Lovers Quarrel' => 'Google Fonts',
            'Luckiest Guy' => 'Google Fonts',
            'Lusitana' => 'Google Fonts',
            'Lustria' => 'Google Fonts',
            'M PLUS 1p' => 'Google Fonts',
            'M PLUS Rounded 1c' => 'Google Fonts',
            'Macondo' => 'Google Fonts',
            'Macondo Swash Caps' => 'Google Fonts',
            'Mada' => 'Google Fonts',
            'Magra' => 'Google Fonts',
            'Maiden Orange' => 'Google Fonts',
            'Maitree' => 'Google Fonts',
            'Mako' => 'Google Fonts',
            'Mali' => 'Google Fonts',
            'Mallanna' => 'Google Fonts',
            'Mandali' => 'Google Fonts',
            'Manuale' => 'Google Fonts',
            'Marcellus' => 'Google Fonts',
            'Marcellus SC' => 'Google Fonts',
            'Marck Script' => 'Google Fonts',
            'Margarine' => 'Google Fonts',
            'Markazi Text' => 'Google Fonts',
            'Marko One' => 'Google Fonts',
            'Marmelad' => 'Google Fonts',
            'Martel' => 'Google Fonts',
            'Martel Sans' => 'Google Fonts',
            'Marvel' => 'Google Fonts',
            'Mate' => 'Google Fonts',
            'Mate SC' => 'Google Fonts',
            'Maven Pro' => 'Google Fonts',
            'McLaren' => 'Google Fonts',
            'Meddon' => 'Google Fonts',
            'MedievalSharp' => 'Google Fonts',
            'Medula One' => 'Google Fonts',
            'Meera Inimai' => 'Google Fonts',
            'Megrim' => 'Google Fonts',
            'Meie Script' => 'Google Fonts',
            'Merienda' => 'Google Fonts',
            'Merienda One' => 'Google Fonts',
            'Merriweather' => 'Google Fonts',
            'Merriweather Sans' => 'Google Fonts',
            'Metal' => 'Google Fonts',
            'Metal Mania' => 'Google Fonts',
            'Metamorphous' => 'Google Fonts',
            'Metrophobic' => 'Google Fonts',
            'Michroma' => 'Google Fonts',
            'Milonga' => 'Google Fonts',
            'Miltonian' => 'Google Fonts',
            'Miltonian Tattoo' => 'Google Fonts',
            'Mina' => 'Google Fonts',
            'Miniver' => 'Google Fonts',
            'Miriam Libre' => 'Google Fonts',
            'Mirza' => 'Google Fonts',
            'Miss Fajardose' => 'Google Fonts',
            'Mitr' => 'Google Fonts',
            'Modak' => 'Google Fonts',
            'Modern Antiqua' => 'Google Fonts',
            'Mogra' => 'Google Fonts',
            'Molengo' => 'Google Fonts',
            'Molle' => 'Google Fonts',
            'Monda' => 'Google Fonts',
            'Monofett' => 'Google Fonts',
            'Monoton' => 'Google Fonts',
            'Monsieur La Doulaise' => 'Google Fonts',
            'Montaga' => 'Google Fonts',
            'Montez' => 'Google Fonts',
            'Montserrat' => 'Google Fonts',
            'Montserrat Alternates' => 'Google Fonts',
            'Montserrat Subrayada' => 'Google Fonts',
            'Moul' => 'Google Fonts',
            'Moulpali' => 'Google Fonts',
            'Mountains of Christmas' => 'Google Fonts',
            'Mouse Memoirs' => 'Google Fonts',
            'Mr Bedfort' => 'Google Fonts',
            'Mr Dafoe' => 'Google Fonts',
            'Mr De Haviland' => 'Google Fonts',
            'Mrs Saint Delafield' => 'Google Fonts',
            'Mrs Sheppards' => 'Google Fonts',
            'Mukta' => 'Google Fonts',
            'Mukta Mahee' => 'Google Fonts',
            'Mukta Malar' => 'Google Fonts',
            'Mukta Vaani' => 'Google Fonts',
            'Muli' => 'Google Fonts',
            'Mystery Quest' => 'Google Fonts',
            'NTR' => 'Google Fonts',
            'Nanum Brush Script' => 'Google Fonts',
            'Nanum Gothic' => 'Google Fonts',
            'Nanum Gothic Coding' => 'Google Fonts',
            'Nanum Myeongjo' => 'Google Fonts',
            'Nanum Pen Script' => 'Google Fonts',
            'Neucha' => 'Google Fonts',
            'Neuton' => 'Google Fonts',
            'New Rocker' => 'Google Fonts',
            'News Cycle' => 'Google Fonts',
            'Niconne' => 'Google Fonts',
            'Niramit' => 'Google Fonts',
            'Nixie One' => 'Google Fonts',
            'Nobile' => 'Google Fonts',
            'Nokora' => 'Google Fonts',
            'Norican' => 'Google Fonts',
            'Nosifer' => 'Google Fonts',
            'Notable' => 'Google Fonts',
            'Nothing You Could Do' => 'Google Fonts',
            'Noticia Text' => 'Google Fonts',
            'Noto Kufi Arabic' => 'Google Fonts', // Hack for Google Early Access.
            'Noto Naskh Arabic' => 'Google Fonts', // Hack for Google Early Access.
            'Noto Sans' => 'Google Fonts',
            'Noto Sans Hebrew' => 'Google Fonts', // Hack for Google Early Access.
            'Noto Sans JP' => 'Google Fonts',
            'Noto Sans KR' => 'Google Fonts',
            'Noto Serif' => 'Google Fonts',
            'Noto Serif JP' => 'Google Fonts',
            'Noto Serif KR' => 'Google Fonts',
            'Nova Cut' => 'Google Fonts',
            'Nova Flat' => 'Google Fonts',
            'Nova Mono' => 'Google Fonts',
            'Nova Oval' => 'Google Fonts',
            'Nova Round' => 'Google Fonts',
            'Nova Script' => 'Google Fonts',
            'Nova Slim' => 'Google Fonts',
            'Nova Square' => 'Google Fonts',
            'Numans' => 'Google Fonts',
            'Nunito' => 'Google Fonts',
            'Nunito Sans' => 'Google Fonts',
            'Odor Mean Chey' => 'Google Fonts',
            'Offside' => 'Google Fonts',
            'Old Standard TT' => 'Google Fonts',
            'Oldenburg' => 'Google Fonts',
            'Oleo Script' => 'Google Fonts',
            'Oleo Script Swash Caps' => 'Google Fonts',
            'Open Sans' => 'Google Fonts',
            'Open Sans Condensed' => 'Google Fonts',
            'Open Sans Hebrew' => 'Google Fonts', // Hack for Google Early Access.
            'Open Sans Hebrew Condensed' => 'Google Fonts', // Hack for Google Early Access.
            'Oranienbaum' => 'Google Fonts',
            'Orbitron' => 'Google Fonts',
            'Oregano' => 'Google Fonts',
            'Orienta' => 'Google Fonts',
            'Original Surfer' => 'Google Fonts',
            'Oswald' => 'Google Fonts',
            'Over the Rainbow' => 'Google Fonts',
            'Overlock' => 'Google Fonts',
            'Overlock SC' => 'Google Fonts',
            'Overpass' => 'Google Fonts',
            'Overpass Mono' => 'Google Fonts',
            'Ovo' => 'Google Fonts',
            'Oxygen' => 'Google Fonts',
            'Oxygen Mono' => 'Google Fonts',
            'PT Mono' => 'Google Fonts',
            'PT Sans' => 'Google Fonts',
            'PT Sans Caption' => 'Google Fonts',
            'PT Sans Narrow' => 'Google Fonts',
            'PT Serif' => 'Google Fonts',
            'PT Serif Caption' => 'Google Fonts',
            'Pacifico' => 'Google Fonts',
            'Padauk' => 'Google Fonts',
            'Palanquin' => 'Google Fonts',
            'Palanquin Dark' => 'Google Fonts',
            'Pangolin' => 'Google Fonts',
            'Paprika' => 'Google Fonts',
            'Parisienne' => 'Google Fonts',
            'Passero One' => 'Google Fonts',
            'Passion One' => 'Google Fonts',
            'Pathway Gothic One' => 'Google Fonts',
            'Patrick Hand' => 'Google Fonts',
            'Patrick Hand SC' => 'Google Fonts',
            'Pattaya' => 'Google Fonts',
            'Patua One' => 'Google Fonts',
            'Pavanam' => 'Google Fonts',
            'Paytone One' => 'Google Fonts',
            'Peddana' => 'Google Fonts',
            'Peralta' => 'Google Fonts',
            'Permanent Marker' => 'Google Fonts',
            'Petit Formal Script' => 'Google Fonts',
            'Petrona' => 'Google Fonts',
            'Philosopher' => 'Google Fonts',
            'Piedra' => 'Google Fonts',
            'Pinyon Script' => 'Google Fonts',
            'Pirata One' => 'Google Fonts',
            'Plaster' => 'Google Fonts',
            'Play' => 'Google Fonts',
            'Playball' => 'Google Fonts',
            'Playfair Display' => 'Google Fonts',
            'Playfair Display SC' => 'Google Fonts',
            'Podkova' => 'Google Fonts',
            'Poiret One' => 'Google Fonts',
            'Poller One' => 'Google Fonts',
            'Poly' => 'Google Fonts',
            'Pompiere' => 'Google Fonts',
            'Pontano Sans' => 'Google Fonts',
            'Poor Story' => 'Google Fonts',
            'Poppins' => 'Google Fonts',
            'Port Lligat Sans' => 'Google Fonts',
            'Port Lligat Slab' => 'Google Fonts',
            'Pragati Narrow' => 'Google Fonts',
            'Prata' => 'Google Fonts',
            'Preahvihear' => 'Google Fonts',
            'Press Start 2P' => 'Google Fonts',
            'Pridi' => 'Google Fonts',
            'Princess Sofia' => 'Google Fonts',
            'Prociono' => 'Google Fonts',
            'Prompt' => 'Google Fonts',
            'Prosto One' => 'Google Fonts',
            'Proza Libre' => 'Google Fonts',
            'Puritan' => 'Google Fonts',
            'Purple Purse' => 'Google Fonts',
            'Quando' => 'Google Fonts',
            'Quantico' => 'Google Fonts',
            'Quattrocento' => 'Google Fonts',
            'Quattrocento Sans' => 'Google Fonts',
            'Questrial' => 'Google Fonts',
            'Quicksand' => 'Google Fonts',
            'Quintessential' => 'Google Fonts',
            'Qwigley' => 'Google Fonts',
            'Racing Sans One' => 'Google Fonts',
            'Radley' => 'Google Fonts',
            'Rajdhani' => 'Google Fonts',
            'Rakkas' => 'Google Fonts',
            'Raleway' => 'Google Fonts',
            'Raleway Dots' => 'Google Fonts',
            'Ramabhadra' => 'Google Fonts',
            'Ramaraja' => 'Google Fonts',
            'Rambla' => 'Google Fonts',
            'Rammetto One' => 'Google Fonts',
            'Ranchers' => 'Google Fonts',
            'Rancho' => 'Google Fonts',
            'Ranga' => 'Google Fonts',
            'Rasa' => 'Google Fonts',
            'Rationale' => 'Google Fonts',
            'Ravi Prakash' => 'Google Fonts',
            'Redressed' => 'Google Fonts',
            'Reem Kufi' => 'Google Fonts',
            'Reenie Beanie' => 'Google Fonts',
            'Revalia' => 'Google Fonts',
            'Rhodium Libre' => 'Google Fonts',
            'Ribeye' => 'Google Fonts',
            'Ribeye Marrow' => 'Google Fonts',
            'Righteous' => 'Google Fonts',
            'Risque' => 'Google Fonts',
            'Roboto' => 'Google Fonts',
            'Roboto Condensed' => 'Google Fonts',
            'Roboto Mono' => 'Google Fonts',
            'Roboto Slab' => 'Google Fonts',
            'Rochester' => 'Google Fonts',
            'Rock Salt' => 'Google Fonts',
            'Rokkitt' => 'Google Fonts',
            'Romanesco' => 'Google Fonts',
            'Ropa Sans' => 'Google Fonts',
            'Rosario' => 'Google Fonts',
            'Rosarivo' => 'Google Fonts',
            'Rouge Script' => 'Google Fonts',
            'Rozha One' => 'Google Fonts',
            'Rubik' => 'Google Fonts',
            'Rubik Mono One' => 'Google Fonts',
            'Ruda' => 'Google Fonts',
            'Rufina' => 'Google Fonts',
            'Ruge Boogie' => 'Google Fonts',
            'Ruluko' => 'Google Fonts',
            'Rum Raisin' => 'Google Fonts',
            'Ruslan Display' => 'Google Fonts',
            'Russo One' => 'Google Fonts',
            'Ruthie' => 'Google Fonts',
            'Rye' => 'Google Fonts',
            'Sacramento' => 'Google Fonts',
            'Sahitya' => 'Google Fonts',
            'Sail' => 'Google Fonts',
            'Saira' => 'Google Fonts',
            'Saira Condensed' => 'Google Fonts',
            'Saira Extra Condensed' => 'Google Fonts',
            'Saira Semi Condensed' => 'Google Fonts',
            'Salsa' => 'Google Fonts',
            'Sanchez' => 'Google Fonts',
            'Sancreek' => 'Google Fonts',
            'Sansita' => 'Google Fonts',
            'Sarala' => 'Google Fonts',
            'Sarina' => 'Google Fonts',
            'Sarpanch' => 'Google Fonts',
            'Satisfy' => 'Google Fonts',
            'Sawarabi Gothic' => 'Google Fonts',
            'Sawarabi Mincho' => 'Google Fonts',
            'Scada' => 'Google Fonts',
            'Scheherazade' => 'Google Fonts',
            'Schoolbell' => 'Google Fonts',
            'Scope One' => 'Google Fonts',
            'Seaweed Script' => 'Google Fonts',
            'Secular One' => 'Google Fonts',
            'Sedgwick Ave' => 'Google Fonts',
            'Sedgwick Ave Display' => 'Google Fonts',
            'Sevillana' => 'Google Fonts',
            'Seymour One' => 'Google Fonts',
            'Shadows Into Light' => 'Google Fonts',
            'Shadows Into Light Two' => 'Google Fonts',
            'Shanti' => 'Google Fonts',
            'Share' => 'Google Fonts',
            'Share Tech' => 'Google Fonts',
            'Share Tech Mono' => 'Google Fonts',
            'Shojumaru' => 'Google Fonts',
            'Short Stack' => 'Google Fonts',
            'Shrikhand' => 'Google Fonts',
            'Siemreap' => 'Google Fonts',
            'Sigmar One' => 'Google Fonts',
            'Signika' => 'Google Fonts',
            'Signika Negative' => 'Google Fonts',
            'Simonetta' => 'Google Fonts',
            'Sintony' => 'Google Fonts',
            'Sirin Stencil' => 'Google Fonts',
            'Six Caps' => 'Google Fonts',
            'Skranji' => 'Google Fonts',
            'Slabo 13px' => 'Google Fonts',
            'Slabo 27px' => 'Google Fonts',
            'Slackey' => 'Google Fonts',
            'Smokum' => 'Google Fonts',
            'Smythe' => 'Google Fonts',
            'Sniglet' => 'Google Fonts',
            'Snippet' => 'Google Fonts',
            'Snowburst One' => 'Google Fonts',
            'Sofadi One' => 'Google Fonts',
            'Sofia' => 'Google Fonts',
            'Song Myung' => 'Google Fonts',
            'Sonsie One' => 'Google Fonts',
            'Sorts Mill Goudy' => 'Google Fonts',
            'Source Code Pro' => 'Google Fonts',
            'Source Sans Pro' => 'Google Fonts',
            'Source Serif Pro' => 'Google Fonts',
            'Space Mono' => 'Google Fonts',
            'Special Elite' => 'Google Fonts',
            'Spectral' => 'Google Fonts',
            'Spectral SC' => 'Google Fonts',
            'Spicy Rice' => 'Google Fonts',
            'Spinnaker' => 'Google Fonts',
            'Spirax' => 'Google Fonts',
            'Squada One' => 'Google Fonts',
            'Sree Krushnadevaraya' => 'Google Fonts',
            'Sriracha' => 'Google Fonts',
            'Srisakdi' => 'Google Fonts',
            'Stalemate' => 'Google Fonts',
            'Stalinist One' => 'Google Fonts',
            'Stardos Stencil' => 'Google Fonts',
            'Stint Ultra Condensed' => 'Google Fonts',
            'Stint Ultra Expanded' => 'Google Fonts',
            'Stoke' => 'Google Fonts',
            'Strait' => 'Google Fonts',
            'Stylish' => 'Google Fonts',
            'Sue Ellen Francisco' => 'Google Fonts',
            'Suez One' => 'Google Fonts',
            'Sumana' => 'Google Fonts',
            'Sunflower' => 'Google Fonts',
            'Sunshiney' => 'Google Fonts',
            'Supermercado One' => 'Google Fonts',
            'Sura' => 'Google Fonts',
            'Suranna' => 'Google Fonts',
            'Suravaram' => 'Google Fonts',
            'Suwannaphum' => 'Google Fonts',
            'Swanky and Moo Moo' => 'Google Fonts',
            'Syncopate' => 'Google Fonts',
            'Tajawal' => 'Google Fonts',
            'Tangerine' => 'Google Fonts',
            'Taprom' => 'Google Fonts',
            'Tauri' => 'Google Fonts',
            'Taviraj' => 'Google Fonts',
            'Teko' => 'Google Fonts',
            'Telex' => 'Google Fonts',
            'Tenali Ramakrishna' => 'Google Fonts',
            'Tenor Sans' => 'Google Fonts',
            'Text Me One' => 'Google Fonts',
            'The Girl Next Door' => 'Google Fonts',
            'Tienne' => 'Google Fonts',
            'Tillana' => 'Google Fonts',
            'Timmana' => 'Google Fonts',
            'Tinos' => 'Google Fonts',
            'Titan One' => 'Google Fonts',
            'Titillium Web' => 'Google Fonts',
            'Trade Winds' => 'Google Fonts',
            'Trirong' => 'Google Fonts',
            'Trocchi' => 'Google Fonts',
            'Trochut' => 'Google Fonts',
            'Trykker' => 'Google Fonts',
            'Tulpen One' => 'Google Fonts',
            'Ubuntu' => 'Google Fonts',
            'Ubuntu Condensed' => 'Google Fonts',
            'Ubuntu Mono' => 'Google Fonts',
            'Ultra' => 'Google Fonts',
            'Uncial Antiqua' => 'Google Fonts',
            'Underdog' => 'Google Fonts',
            'Unica One' => 'Google Fonts',
            'UnifrakturCook' => 'Google Fonts',
            'UnifrakturMaguntia' => 'Google Fonts',
            'Unkempt' => 'Google Fonts',
            'Unlock' => 'Google Fonts',
            'Unna' => 'Google Fonts',
            'VT323' => 'Google Fonts',
            'Vampiro One' => 'Google Fonts',
            'Varela' => 'Google Fonts',
            'Varela Round' => 'Google Fonts',
            'Vast Shadow' => 'Google Fonts',
            'Vesper Libre' => 'Google Fonts',
            'Vibur' => 'Google Fonts',
            'Vidaloka' => 'Google Fonts',
            'Viga' => 'Google Fonts',
            'Voces' => 'Google Fonts',
            'Volkhov' => 'Google Fonts',
            'Vollkorn' => 'Google Fonts',
            'Vollkorn SC' => 'Google Fonts',
            'Voltaire' => 'Google Fonts',
            'Waiting for the Sunrise' => 'Google Fonts',
            'Wallpoet' => 'Google Fonts',
            'Walter Turncoat' => 'Google Fonts',
            'Warnes' => 'Google Fonts',
            'Wellfleet' => 'Google Fonts',
            'Wendy One' => 'Google Fonts',
            'Wire One' => 'Google Fonts',
            'Work Sans' => 'Google Fonts',
            'Yanone Kaffeesatz' => 'Google Fonts',
            'Yantramanav' => 'Google Fonts',
            'Yatra One' => 'Google Fonts',
            'Yellowtail' => 'Google Fonts',
            'Yeon Sung' => 'Google Fonts',
            'Yeseva One' => 'Google Fonts',
            'Yesteryear' => 'Google Fonts',
            'Yrsa' => 'Google Fonts',
            'Zeyada' => 'Google Fonts',
            'Zilla Slab' => 'Google Fonts',
            'Zilla Slab Highlight' => 'Google Fonts',
        );
    }

    public function activate_deactivate_chaty_license_key()
    {
        if (current_user_can('manage_options')) {
            $license_key = filter_input(INPUT_POST, 'license_key', FILTER_SANITIZE_STRING);
            $action_type = filter_input(INPUT_POST, 'chaty_license_action', FILTER_SANITIZE_STRING);
            $activate_token = filter_input(INPUT_POST, 'activate_token', FILTER_SANITIZE_STRING);
            $deactivate_token = filter_input(INPUT_POST, 'deactivate_token', FILTER_SANITIZE_STRING);
            if (empty($license_key)) {               // required validation for license key
                esc_attr_e("invalid");
            } else if (!isset($action_type) || empty($action_type)) {        // required validation for action: activate/deactivate key
                esc_attr_e("invalid");
            } else if ($action_type == "save" && (!isset($activate_token) || empty($activate_token))) {          // required validation for activation nonce
                esc_attr_e("invalid");
            } else if ($action_type == "remove" && (!isset($deactivate_token) || empty($deactivate_token))) {    // required validation for deactivation nonce
                esc_attr_e("invalid");
            } else if ($action_type == "save" && (!wp_verify_nonce($activate_token, "chaty_activate_nonce"))) {              // validating activation nonce
                esc_attr_e("invalid");
            } else if ($action_type == "remove" && (!wp_verify_nonce($deactivate_token, "chaty_deactivate_nonce"))) {        // validating deactivation nonce
                esc_attr_e("invalid");
            } else if ($action_type != "save" && $action_type != "remove") {
                esc_attr_e("invalid");
            } else {
                $licenseKey = trim($license_key);
                $licenseKey = self::chaty_sanitize_options($licenseKey);        // sanitize input values
                if ($action_type == "save") {
                    $license_data = $this->activateLicenseKey($licenseKey);     // function to activate license key
                    if (!empty($license_data)) {
                        /* checking response */
                        delete_transient("cht_token_data");
                        if ($license_data['license'] == 'valid') {
                            esc_attr_e("valid");
                            update_option("cht_token", $licenseKey);            // save license key if it is valid
                        } else if ($license_data['license'] == 'invalid' && $license_data['error'] == 'expired') {
                            esc_attr_e("expired");
                            update_option("cht_token", "");                     // set license key = blank if it is not valid or expired
                        } else if ($license_data['license'] == 'invalid' && $license_data['error'] == 'no_activations_left') {
                            esc_attr_e("no_activations");
                            update_option("cht_token", "");                     // set license key = blank if it is not valid or expired
                        } else {
                            update_option("cht_token", "");                     // set license key = blank if response is not valid
                            esc_attr_e("error");
                        }
                    } else {
                        update_option("cht_token", "");                         // set license key = blank if response is blank or null
                        esc_attr_e("error");;
                    }
                } else {
                    $license_data = $this->deActivateLicenseKey($licenseKey);   // function to activate license key
                    if (!empty($license_data)) {
                        delete_transient("cht_token_data");
                        if ($license_data['license'] == 'deactivated') {
                            esc_attr_e("unactivated");
                            update_option("cht_token", "");                     // set license key = blank
                        }
                    }
                    update_option("cht_token", "");                             // set license key = blank if response is blank or null
                }
            }
            return;
        }
    }

    /* EDD Licence key activation function */
    public function activateLicenseKey($licenseKey)
    {
        if (empty($licenseKey)) {
            $licenseKey = get_option("cht_token");
        }

        if(empty($licenseKey)) {
            return array();
        }

        $licenseData = get_transient("cht_token_data");
        if($licenseData !== false && !empty($licenseData)) {
            return $licenseData;
        }

        $api_params = array(
            'edd_action' => 'activate_license',
            'license' => $licenseKey,
            'item_id' => CHT_CHATY_PLUGIN_ID,
            'url' => site_url()
        );

        /* Request to premio.io for key activation */
        $response = wp_safe_remote_post(CHT_CHATY_PLUGIN_URL, array('body' => $api_params, 'timeout' => 15, 'sslverify' => false));

        if (is_wp_error($response)) {
            $response = wp_safe_remote_post(CHT_CHATY_PLUGIN_URL, array('body' => $api_params, 'timeout' => 15, 'sslverify' => true));
        }

        if (is_wp_error($response)) {
            return array();                                                     // return empty array if error in response
        } else {
            $response = json_decode(wp_remote_retrieve_body($response), true);  // return response
            set_transient("cht_token_data", $response, DAY_IN_SECONDS);
            return $response;
        }
    }

    /* EDD Licence key deactivation function */
    public function deActivateLicenseKey($licenseKey)
    {
        if ($licenseKey == "") {
            return array();
        }

        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license' => $licenseKey,
            'item_id' => CHT_CHATY_PLUGIN_ID,
            'url' => site_url()
        );

        /* Request to premio.io for key deactivation */
        $response = wp_safe_remote_post(CHT_CHATY_PLUGIN_URL, array('body' => $api_params, 'timeout' => 15, 'sslverify' => false));

        if (is_wp_error($response)) {
            $response = wp_safe_remote_post(CHT_CHATY_PLUGIN_URL, array('body' => $api_params, 'timeout' => 15, 'sslverify' => true));
        }

        if (is_wp_error($response)) {
            return array();                                                     // return empty array if error in response
        } else {
            $response = json_decode(wp_remote_retrieve_body($response), true);  // return response
            return $response;
        }
    }

    /* EDD get Licence key information */
    public function getLicenseKeyInformation($licenseKey)
    {
        if ($licenseKey == "") {
            return array();
        }

        $api_params = array(
            'edd_action' => 'check_license',
            'license' => $licenseKey,
            'item_id' => CHT_CHATY_PLUGIN_ID,
            'url' => site_url()
        );

        /* Request to premio.io for checking Licence key */
        $response = wp_safe_remote_post(CHT_CHATY_PLUGIN_URL, array('body' => $api_params, 'timeout' => 15, 'sslverify' => false));

        if (is_wp_error($response)) {
            $response = wp_safe_remote_post(CHT_CHATY_PLUGIN_URL, array('body' => $api_params, 'timeout' => 15, 'sslverify' => true));
        }

        if (is_wp_error($response)) {
            return array();                                                     // return empty array if error in response
        } else {
            $response = json_decode(wp_remote_retrieve_body($response), true);  // return response
            set_transient("cht_token_data", $response, DAY_IN_SECONDS);
            return $response;
        }
    }

    public function get_total_widgets() {
        $total_widget = 0;
        $is_deleted = get_option("cht_is_default_deleted");
        if($is_deleted === false) {
            $total_widget = $total_widget+1;
        }

        $chaty_widgets = get_option("chaty_total_settings");

        $deleted_list = get_option("chaty_deleted_settings");
        if(empty($deleted_list) || !is_array($deleted_list)) {
            $deleted_list = array();
        }

        if (!empty($chaty_widgets) && $chaty_widgets != null && is_numeric($chaty_widgets) && $chaty_widgets > 0) {
            for ($i = 1; $i <= $chaty_widgets; $i++) {
                if(!in_array($i, $deleted_list)) {
                    $total_widget = $total_widget+1;
                }
            }
        }
        return $total_widget;
    }
}

new CHT_PRO_Admin_Base();
