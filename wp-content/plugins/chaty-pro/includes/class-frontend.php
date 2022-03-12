<?php

namespace CHT\frontend;

use CHT\admin\CHT_PRO_Admin_Base;
use CHT\admin\CHT_PRO_Social_Icons;
use CHT\admin\CHT_Social_Icons;

if (!defined('ABSPATH')) { exit; }

$admin_base = CHT_PRO_ADMIN_INC . '/class-admin-base.php';
require_once($admin_base);

$social_icons = CHT_PRO_ADMIN_INC . '/class-social-icons.php';
require_once($social_icons);

class CHT_PRO_Frontend extends CHT_PRO_Admin_Base {

    public $widget_number = "";

    public $inline_css = "";

    public $widget_settings = array();

    public $chaty_settings = array();
    /**
     * constructor.
     */
    public function __construct() {
        $this->socials = CHT_PRO_Social_Icons::get_instance()->get_icons_list();            // collecting default social media icons
        if (wp_doing_ajax()) {
            /* initialize function it is AJAX request */
            add_action('wp_ajax_choose_social', array($this, 'choose_social_handler'));     // return setting for a social media in html
            add_action('wp_ajax_get_chaty_settings', array($this, 'get_chaty_settings'));     // return setting for a social media in html
            add_action('wp_ajax_remove_chaty_widget', array($this, 'remove_chaty_widget'));     // remove social media widget
            add_action('wp_ajax_change_chaty_widget_status', array($this, 'change_chaty_widget_status'));     // remove social media widget
        }

        /* save contact form submit data */
        add_action('wp_ajax_chaty_front_form_save_data', array($this, 'chaty_front_form_save_data'));
        add_action('wp_ajax_nopriv_chaty_front_form_save_data', array($this, 'chaty_front_form_save_data'));

        /* update channel click status */
        add_action('wp_ajax_update_chaty_channel_status', array($this, 'update_chaty_channel_status'));
        add_action('wp_ajax_nopriv_update_chaty_channel_status', array($this, 'update_chaty_channel_status'));

        /* update channel view status */
        add_action('wp_ajax_update_chaty_channel_view', array($this, 'update_chaty_channel_view'));
        add_action('wp_ajax_nopriv_update_chaty_channel_view', array($this, 'update_chaty_channel_view'));

        if(!isset($_GET['ct_builder'])) {
            add_action('wp_enqueue_scripts', array($this, 'cht_front_end_css_and_js'), 0);
        }
    }

    function chaty_front_form_save_data() {
        $response = array(
            'status' => 0,
            'error' => 0,
            'errors' => array(),
            'message' => ''
        );
        $postData = filter_input_array(INPUT_POST);
        if(isset($postData['nonce']) && isset($postData['widget']) && wp_verify_nonce($postData['nonce'], "chaty-front-form".$postData['widget'])) {
            $name = isset($postData['name'])?$postData['name']:"";
            $email = isset($postData['email'])?$postData['email']:"";
            $message = isset($postData['message'])?$postData['message']:"";
            $phone = isset($postData['phone'])?$postData['phone']:"";
            $ref_url = isset($postData['ref_url'])?$postData['ref_url']:"";
            $widget = $postData['widget'];
            $channel = $postData['channel'];

            $value = get_option('cht_social'.$widget.'_' . $channel);   //  get saved settings for button

            $errors = array();
            if(!empty($value)) {
                $field_setting = isset($value['name'])?$value['name']:array();
                if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes" && isset($field_setting['is_required']) && $field_setting['is_required'] == "yes" && empty($name)) {
                    $error = array(
                        'field' => 'chaty-field-name',
                        'message' => esc_attr("this field is required", 'chaty')
                    );
                    $errors[] = $error;
                }
                $field_setting = isset($value['phone'])?$value['phone']:array();
                if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes" && isset($field_setting['is_required']) && $field_setting['is_required'] == "yes" && empty($phone)) {
                    $error = array(
                        'field' => 'chaty-field-phone',
                        'message' => esc_attr("this field is required", 'chaty')
                    );
                    $errors[] = $error;
                }
                $field_setting = isset($value['email'])?$value['email']:array();
                if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes" && isset($field_setting['is_required']) && $field_setting['is_required'] == "yes") {
                    if(empty($email)) {
                        $error = array(
                            'field' => 'chaty-field-name',
                            'message' => esc_attr("this field is required", 'chaty')
                        );
                        $errors[] = $error;
                    } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $error = array(
                            'field' => 'chaty-field-email',
                            'message' => esc_attr("email address is not valid", 'chaty')
                        );
                        $errors[] = $error;
                    }
                }
                $field_setting = isset($value['message'])?$value['message']:array();
                if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes" && isset($field_setting['is_required']) && $field_setting['is_required'] == "yes" && empty($message)) {
                    $error = array(
                        'field' => 'chaty-field-message',
                        'message' => esc_attr("this field is required", 'chaty')
                    );
                    $errors[] = $error;
                }
                if(empty($errors)) {
                    $widget = trim($widget, "_");
                    $response['message'] = $value['thanks_message'];
                    $response['redirect_action'] = $value['redirect_action'];
                    $response['redirect_link'] = esc_url($value['redirect_link']);
                    $response['link_in_new_tab'] = $value['link_in_new_tab'];
                    $response['close_form_after'] = $value['close_form_after'];
                    $response['close_form_after_seconds'] = $value['close_form_after_seconds'];
                    $send_leads_in_email = $value['send_leads_in_email'];
                    $save_leads_locally = $value['save_leads_locally'];

                    date_default_timezone_set("UTC");
                    $current_date = date("Y-m-d H:i:s");

                    $new_date = get_date_from_gmt($current_date, "Y-m-d H:i:s");
                    if($save_leads_locally == "yes") {
                        global $wpdb;
                        $chaty_table = $wpdb->prefix . 'chaty_contact_form_leads';
                        $insert = array();
                        $field_setting = isset($value['name'])?$value['name']:array();
                        if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                            $insert['name'] = esc_sql(sanitize_text_field($name));
                        }
                        $field_setting = isset($value['email'])?$value['email']:array();
                        if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                            $insert['email'] = esc_sql(sanitize_text_field($email));
                        }
                        $field_setting = isset($value['phone'])?$value['phone']:array();
                        if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                            $insert['phone_number'] = esc_sql(sanitize_text_field($phone));
                        }
                        $field_setting = isset($value['message'])?$value['message']:array();
                        if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                            $insert['message'] = esc_sql(sanitize_text_field($message));
                        }
                        $insert['ref_page'] = $ref_url;
                        $insert['ip_address'] = $this->get_user_ipaddress();
                        $insert['widget_id'] = esc_sql(sanitize_text_field($widget));
                        $insert['created_on'] = $new_date;
                        $wpdb->insert($chaty_table, $insert);
                    }

                    if($send_leads_in_email == "yes") {
                        $mail_content = "";
                        $mail_content .= "<table cellspacing='0' cellpadding='0' border='0' >";
                        $field_setting = isset($value['name'])?$value['name']:array();
                        if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                            $mail_content .= "<tr>";
                            $mail_content .= "<th>Name: </th>";
                            $mail_content .= "<td>".esc_attr($name)."</td>";
                            $mail_content .= "</tr>";
                        }
                        $field_setting = isset($value['email'])?$value['email']:array();
                        if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                            $mail_content .= "<tr>";
                            $mail_content .= "<th>Email: </th>";
                            $mail_content .= "<td>".esc_attr($email)."</td>";
                            $mail_content .= "</tr>";
                        }
                        $field_setting = isset($value['phone'])?$value['phone']:array();
                        if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                            $mail_content .= "<tr>";
                            $mail_content .= "<th>Phone number: </th>";
                            $mail_content .= "<td>".esc_attr($phone)."</td>";
                            $mail_content .= "</tr>";
                        }
                        $field_setting = isset($value['message'])?$value['message']:array();
                        if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                            $mail_content .= "<tr>";
                            $mail_content .= "<th>Message: </th>";
                            $mail_content .= "<td>".nl2br($message)."</td>";
                            $mail_content .= "</tr>";
                        }
                        $mail_content .= "</table>";

                        $blog_email = (isset($value['email_address']) && !empty($value['email_address'])) ? $value['email_address'] : get_bloginfo('admin_email');
                        $blog_name = (isset($value['sender_name']) && !empty($value['sender_name'])) ? $value['sender_name'] : get_bloginfo('name');
                        $subject = (isset($value['email_subject']) && !empty($value['email_subject'])) ? $value['email_subject'] : "New contact form lead";

                        $form_title = (isset($value['contact_form_title']) && !empty($value['contact_form_title'])) ? " (".trim($value['contact_form_title']).")" : "";

                        $date_format = get_option("date_format");
                        $time_format = get_option("time_format");

                        if(empty($date_format)) {
                            $date_format = "Y-m-d";
                        }
                        if(empty($time_format)) {
                            $time_format = "H:i:s";
                        }

//                        $subject = $subject.$form_title." ".date_i18n($date_format." ".$time_format);

                        $date = get_date_from_gmt($current_date, $date_format);
                        $time = get_date_from_gmt($current_date, $time_format);

                        $subject = str_replace(array("{name}","{phone}","{email}","{date}","{hour}"), array($name, $phone, $email, $date, $time), $subject);

                        $headers = "MIME-Version: 1.0\r\n";
                        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                        $headers .= 'From: ' . $blog_name . ' <' . $blog_email . '>' . "\r\n";
                        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
                        if (isset($email) && !empty($email) && isset($name) && !empty($name)) {
                            $headers .= "Reply-To: " . sanitize_text_field($name) ." <" . sanitize_email($email) . ">\r\n";
                        }
                        wp_mail($blog_email, $subject, $mail_content, $headers);
                    }
                    $response['status'] = 1;
                } else {
                    $response['errors'] = $errors;
                    $response['error'] = 1;
                }
            } else {
                $response['message'] = "Invalid request, Please try again";
            }
        } else {
            $response['message'] = "Invalid request, Please try again";
        }
        echo json_encode($response);
        exit;
    }

    function cht_front_end_css_and_js() {
        if ($this->canInsertWidget()):
            $settings = $this->widget_settings;
            if(!empty($settings)) {
                $widgets = array();
                for($i=0;$i<count($settings);$i++) {
                    $widgets[] = array(
                        'on_page_status' => 0,
                        'is_displayed' => 0
                    );
                }
                $chaty_updated_on = get_option("chaty_updated_on");
                if(empty($chaty_updated_on)) {
                    $chaty_updated_on = time();
                }
                $data = array();
                $data['chaty_widgets'] = $settings;
                $data['object_settings'] = $settings[0];
                $data['widget_status'] = $widgets;
                $data['ajax_url'] = admin_url("admin-ajax.php");
                $status = get_option("cht_data_analytics_status");
                $status = ($status === false)?"on":$status;
                $data['data_analytics_settings'] = $status;

                wp_enqueue_style('chaty-front-css', CHT_PLUGIN_URL . "css/chaty-front.min.css", array(), $chaty_updated_on);
                wp_add_inline_style('chaty-front-css', $this->inline_css);
                wp_enqueue_script("chaty-pro-front-end", CHT_PLUGIN_URL . "js/cht-front-script.min.js", array('jquery'), $chaty_updated_on, false);
                wp_localize_script('chaty-pro-front-end', 'chaty_settings', $data);

                $this->chaty_settings['chaty_settings'] = $data;
            }
        endif;
    }

    public function update_chaty_channel_view() {
        $postData = filter_input_array(INPUT_POST);
        $response = array();
        if(!empty($postData)) {
            $is_widget = isset($postData['is_widget']) ? $postData['is_widget'] : "";
            $widget_id = trim(isset($postData['index']) ? $postData['index'] : "");
            $type = isset($postData['type']) ? $postData['type'] : "";
            $date = strtotime(date("Y-m-d 00:00:00"));
            global $wpdb;
            $chaty_table = $wpdb->prefix . 'chaty_widget_analysis';
            $widget_id = trim($widget_id, "_");
            $channels = isset($postData['channels'])?$postData['channels']:"";
            $channels = trim($channels,",");
            if(!empty($channels) && count($channels) <=20 && $type == "view" && $is_widget == 1) {
                $channels = explode(",",$channels);
                if(is_array($channels) && count($channels) > 0) {
                    foreach($channels as $channel) {
                        $query = "SELECT id, widget_id, channel_slug, no_of_views, no_of_clicks, is_widget, analysis_date
                            FROM {$chaty_table}
                            WHERE widget_id = '%d' AND is_widget = '0' AND analysis_date ='%d' AND channel_slug = '%s'";
                        $query = $wpdb->prepare($query, array($widget_id, $date, $channel));

                        if(!empty($query)) {
                            $result = $wpdb->get_row($query, ARRAY_A);
                            if(!empty($result)) {
                                $id = $result['id'];
                                if ($type == "view") {
                                    $query = "UPDATE {$chaty_table} SET no_of_views = no_of_views + 1 WHERE id = '%d'";
                                    $query = $wpdb->prepare($query, array($id));

                                    $wpdb->query($query);
                                }
                            } else {
                                $data = array();
                                $data['is_widget'] = 0;
                                $data['no_of_views'] = 1;
                                $data['no_of_clicks'] = 0;
                                $data['widget_id'] = $widget_id;
                                $data['channel_slug'] = $channel;
                                $data['analysis_date'] = $date;

                                $wpdb->insert($chaty_table, $data);
                            }
                        }
                    }
                }
            }
        }
        echo "1";
    }

    function get_user_ipaddress() {
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = sanitize_text_field( wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //ip pass from proxy
            $ip = sanitize_text_field( wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        }else{
            $ip = sanitize_text_field( wp_unslash($_SERVER['REMOTE_ADDR']));
        }
        return $ip;
    }

    public function update_chaty_channel_status() {

        $postData = filter_input_array(INPUT_POST);
        $response = array();
        if(!empty($postData)) {
            $nonce = isset($postData['nonce'])?$postData['nonce']:"";
            $is_widget = isset($postData['is_widget'])?$postData['is_widget']:"";
            $widget_id = trim(isset($postData['index'])?$postData['index']:"");
            $channel = isset($postData['channel'])?$postData['channel']:"";
            $type = isset($postData['type'])?$postData['type']:"";

            global $wpdb;
            $chaty_table = $wpdb->prefix . 'chaty_widget_analysis';
            $widget_id = trim($widget_id, "_");
            $date = strtotime(date("Y-m-d 00:00:00"));
            $query = "";
            if($is_widget == 1) {
                $query = "SELECT id, widget_id, channel_slug, no_of_views, no_of_clicks, is_widget, analysis_date
                    FROM {$chaty_table}
                    WHERE widget_id = '%d' AND is_widget = '1' AND analysis_date ='%d'";
                $query = $wpdb->prepare($query, array($widget_id, $date));
            } else if($is_widget == 0) {
                $query = "SELECT id, widget_id, channel_slug, no_of_views, no_of_clicks, is_widget, analysis_date
                    FROM {$chaty_table}
                    WHERE widget_id = '%d' AND is_widget = '0' AND analysis_date ='%d' AND channel_slug = '%s'";
                $query = $wpdb->prepare($query, array($widget_id, $date, $channel));
            }
            if(!empty($query)) {
                $result = $wpdb->get_row($query, ARRAY_A);
                if(!empty($result)) {
                    $id = $result['id'];
                    if($type == "view") {
                        $query = "UPDATE {$chaty_table} SET no_of_views = no_of_views + 1 WHERE id = '%d'";
                        $query = $wpdb->prepare($query, array($id));
                    } else if($type == "click") {
                        $query = "UPDATE {$chaty_table} SET no_of_clicks = no_of_clicks + 1 WHERE id = '%d'";
                        $query = $wpdb->prepare($query, array($id));
                    }
                    $wpdb->query($query);
                } else {
                    $data = array();
                    $data['is_widget'] = $is_widget;
                    $data['no_of_views'] = 0;
                    $data['no_of_clicks'] = 0;
                    $data['widget_id'] = $widget_id;
                    $data['channel_slug'] = $channel;
                    $data['analysis_date'] = $date;
                    if($type == "view") {
                        $data['no_of_views'] = 1;
                    } else if($type == "click") {
                        $data['no_of_clicks'] = 1;
                    }
                    if($is_widget == 0 && !empty($channel) && $type == "click") {
                        $data['no_of_views'] = 1;
                    }
                    $wpdb->insert($chaty_table, $data);
                }

                if($is_widget == 1 && !empty($channel) && $type == "click") {
                    $channel = trim($channel,",");
                    if(!empty($channel)) {
                        $channel_array = explode(",", $channel);
                        if(count($channel_array) == 1) {
                            $query = "SELECT id, widget_id, channel_slug, no_of_views, no_of_clicks, is_widget, analysis_date
                                    FROM {$chaty_table}
                                    WHERE widget_id = '%d' AND is_widget = '0' AND analysis_date ='%d' AND channel_slug = '%s'";
                            $query = $wpdb->prepare($query, array($widget_id, $date, $channel));
                            if(!empty($query)) {
                                $result = $wpdb->get_row($query, ARRAY_A);
                                if(!empty($result)) {
                                    $id = $result['id'];
                                    $query = "UPDATE {$chaty_table} SET no_of_clicks = no_of_clicks + 1 WHERE id = '%d'";
                                    $query = $wpdb->prepare($query, array($id));
                                    $wpdb->query($query);
                                } else {
                                    $data = array();
                                    $data['is_widget'] = 0;
                                    $data['no_of_views'] = 1;
                                    $data['no_of_clicks'] = 1;
                                    $data['widget_id'] = $widget_id;
                                    $data['channel_slug'] = $channel;
                                    $data['analysis_date'] = $date;

                                    $wpdb->insert($chaty_table, $data);
                                }
                            }
                        }
                    }
                }

                if($is_widget == 1 && $type == "view") {
                    $channels = isset($postData['channels'])?$postData['channels']:"";
                    $channels = trim($channels,",");
                    if(!empty($channels)) {
                        $channels = explode(",",$channels);
                        if(is_array($channels) && count($channels) > 0 && count($channels) <=20) {
                            foreach($channels as $channel) {
                                $query = "SELECT id, widget_id, channel_slug, no_of_views, no_of_clicks, is_widget, analysis_date
                                        FROM {$chaty_table}
                                        WHERE widget_id = '%d' AND is_widget = '0' AND analysis_date ='%d' AND channel_slug = '%s'";
                                $query = $wpdb->prepare($query, array($widget_id, $date, $channel));

                                $result = $wpdb->get_row($query, ARRAY_A);
                                if (!empty($result)) {
                                    $id = $result['id'];
                                    if ($type == "view") {
                                        $query = "UPDATE {$chaty_table} SET no_of_views = no_of_views + 1 WHERE id = '%d'";
                                        $query = $wpdb->prepare($query, array($id));

                                        $wpdb->query($query);
                                    }
                                } else {
                                    $data = array();
                                    $data['is_widget'] = 0;
                                    $data['no_of_views'] = 1;
                                    $data['no_of_clicks'] = 0;
                                    $data['widget_id'] = $widget_id;
                                    $data['channel_slug'] = $channel;
                                    $data['analysis_date'] = $date;

                                    $wpdb->insert($chaty_table, $data);
                                }

                            }
                        }
                    }
                }
            }
        }
        echo 1; exit;
    }

    public function get_chaty_settings() {
        if (current_user_can('manage_options')) {
            $slug = filter_input(INPUT_POST, 'social', FILTER_SANITIZE_STRING);
            $channel = filter_input(INPUT_POST, 'channel', FILTER_SANITIZE_STRING);
            $status = 0;
            $data = array();
            if (!empty($slug)) {
                foreach ($this->socials as $social) {
                    if ($social['slug'] == $slug) {
                        break;
                    }
                }
                if (!empty($social)) {
                    $status = 1;
                    $data = $social;
//                echo "<pre>"; print_r($social); echo "</pre>";
                    $data['help'] = "";
                    $data['help_text'] = "";
                    $data['help_link'] = "";
                    if ((isset($social['help']) && !empty($social['help'])) || isset($social['help_link'])) {
                        $data['help_title'] = isset($social['help_title']) ? $social['help_title'] : "Doesn't work?";
                        $data['help_text'] = isset($social['help']) ? $social['help'] : "";
                        if (isset($data['help_link']) && !empty($data['help_link'])) {
                            $data['help_link'] = $data['help_link'];
                        } else {
                            $data['help_title'] = $data['help_title'];
                        }
                    }
                }
            }
            $response = array();
            $response['data'] = $data;
            $response['status'] = $status;
            $response['channel'] = $channel;
            echo json_encode($response);
            die;
        }
    }

    /* function choose_social_handler start */
    public function choose_social_handler()
    {
        if (current_user_can('manage_options')) {
            check_ajax_referer('cht_nonce_ajax', 'nonce_code');
            $slug = filter_input(INPUT_POST, 'social', FILTER_SANITIZE_STRING);

            if (!is_null($slug) && !empty($slug)) {
                foreach ($this->socials as $social) {
                    if ($social['slug'] == $slug) {
                        break;
                    }
                }
                if (!$social) {
                    return;                                     // return if social media setting not found
                }

                $widget_index = filter_input(INPUT_POST, 'widget_index', FILTER_SANITIZE_STRING);

                $value = get_option('cht_social' . $widget_index . '_' . $slug);   // get setting for media if already saved

                if (empty($value)) {                                        // Initialize default values if not found
                    $value = [
                        'value' => '',
                        'is_mobile' => 'checked',
                        'is_desktop' => 'checked',
                        'image_id' => '',
                        'title' => $social['title'],
                        'bg_color' => "",
                    ];
                }
                if (!isset($value['bg_color']) || empty($value['bg_color'])) {
                    $value['bg_color'] = $social['color'];                  // Initialize background color value if not exists. 2.1.0 change
                }
                if (!isset($value['image_id'])) {
                    $value['image_id'] = '';                                // Initialize custom image id if not exists. 2.1.0 change
                }
                if (!isset($value['title'])) {
                    $value['title'] = $social['title'];                     // Initialize title if not exists. 2.1.0 change
                }
                if (!isset($value['fa_icon'])) {
                    $value['fa_icon'] = "";                     // Initialize title if not exists. 2.1.0 change
                }
                if(!isset($value['value'])) {
                    $value['value'] = "";
                }
                $imageId = $value['image_id'];
                $imageUrl = "";
                $status = 0;
                if (!empty($imageId)) {
                    $imageUrl = wp_get_attachment_image_src($imageId, "full")[0];                       // get custom image URL if exists
                    $status = 1;
                }
                if ($imageUrl == "") {
                    $imageUrl = plugin_dir_url("") . "chaty-pro/admin/assets/images/chaty-default.png";   // Initialize with default image if custom image is not exists
                    $status = 0;
                    $imageId = "";
                }
                $color = "";
                if (!empty($value['bg_color'])) {
                    $color = "background-color: " . $value['bg_color'];                                   // set background color of icon it it is exists
                }
                if ($social['slug'] == "Whatsapp") {
                    $val = $value['value'];
                    $val = str_replace("+", "", $val);
                    $val = str_replace("-", "", $val);
                    $value['value'] = $val;
                } else if ($social['slug'] == "Facebook_Messenger") {
                    $val = $value['value'];
                    $val = str_replace("facebook.com", "m.me", $val);                                    // Replace facebook.com with m.me version 2.0.1 change
                    $val = str_replace("www.", "", $val);                                                // Replace www. with blank version 2.0.1 change
                    $value['value'] = $val;

                    $val = trim($val, "/");
                    $val_array = explode("/", $val);
                    $total = count($val_array) - 1;
                    $last_value = $val_array[$total];
                    $last_value = explode("-", $last_value);
                    $total_text = count($last_value) - 1;
                    $total_text = $last_value[$total_text];

                    if (is_numeric($total_text)) {
                        $val_array[$total] = $total_text;
                        $value['value'] = implode("/", $val_array);
                    }
                }
                if(!isset($value['value'])) {
                    $value['value'] = "";
                }
                $value['value'] = esc_attr__(wp_unslash($value['value']));
                $value['title'] = esc_attr__(wp_unslash($value['title']));

                $svg_icon = $social['svg'];

                $help_title = "";
                $help_text = "";
                $help_link = "";

                if ((isset($social['help']) && !empty($social['help'])) || isset($social['help_link'])) {
                    $help_title = isset($social['help_title']) ? $social['help_title'] : "Doesn't work?";
                    $help_text = isset($social['help']) ? $social['help'] : "";
                    if (isset($social['help_link']) && !empty($social['help_link'])) {
                        $help_link = $social['help_link'];
                    }
                }

                $channel_type = "";
                $placeholder = $social['example'];
                if ($social['slug'] == "Link" || $social['slug'] == "Custom_Link" || $social['slug'] == "Custom_Link_3" || $channel_type == "Custom_Link_4" || $channel_type == "Custom_Link_5") {
                    if (isset($value['channel_type'])) {
                        $channel_type = esc_attr__(wp_unslash($value['channel_type']));
                    }

                    if (!empty($channel_type)) {
                        foreach ($this->socials as $icon) {
                            if ($icon['slug'] == $channel_type) {
                                $svg_icon = $icon['svg'];

                                $placeholder = $icon['example'];

                                if ((isset($icon['help']) && !empty($icon['help'])) || isset($icon['help_link'])) {
                                    $help_title = isset($icon['help_title']) ? $icon['help_title'] : "Doesn't work?";
                                    $help_text = isset($icon['help']) ? $icon['help'] : "";
                                    if (isset($icon['help_link']) && !empty($icon['help_link'])) {
                                        $help_link = $icon['help_link'];
                                    }
                                }
                            }
                        }
                    }
                }
                if (empty($channel_type)) {
                    $channel_type = $social['slug'];
                }
                if($channel_type == "Telegram") {
                    $value['value'] = trim($value['value'], "@");
                }
                ob_start();
                ?>
                <!-- Social media setting box: start -->
                <li data-id="<?php echo esc_attr($social['slug']) ?>" class="chaty-channel" data-channel="<?php echo esc_attr($channel_type) ?>" id="chaty-social-<?php echo esc_attr($social['slug']) ?>">
                    <div class="channels-selected__item <?php esc_attr_e(($status)?"img-active":"") ?> <?php esc_attr_e(($this->is_pro()) ? 'pro' : 'free'); ?> 1 available">
                        <div class="chaty-default-settings">
                            <div class="move-icon">
                                <img src="<?php echo esc_url(plugin_dir_url("")."/chaty-pro/admin/assets/images/move-icon.png") ?>">
                            </div>
                            <div class="icon icon-md active" data-title="<?php esc_attr_e($social['title']); ?>">
                            <span style="" class="custom-chaty-image custom-image-<?php echo esc_attr($social['slug']) ?>" id="image_data_<?php echo esc_attr($social['slug']) ?>">
                                <img src="<?php echo esc_url($imageUrl) ?>" />
                                <span onclick="remove_chaty_image('<?php echo esc_attr($social['slug']) ?>')" class="remove-icon-img"></span>
                            </span>
                                                <span class="default-chaty-icon <?php echo (isset($value['fa_icon'])&&!empty($value['fa_icon']))?"has-fa-icon":"" ?> custom-icon-<?php echo esc_attr($social['slug']) ?> default_image_<?php echo esc_attr($social['slug']) ?>" >
                                <svg width="39" height="39" viewBox="0 0 39 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <?php echo $svg_icon; ?>
                                </svg>
                                <span class="facustom-icon" style="background-color: <?php echo esc_attr($value['bg_color']) ?>"><i class="<?php echo esc_attr($value['fa_icon']) ?>"></i></span>
                            </span>
                            </div>

                            <?php if($social['slug'] != 'Contact_Us') { ?>

                                <?php if(($social['slug'] == "Whatsapp" || $channel_type == "Whatsapp") && !empty($value['value'])) {
                                    $value['value'] = trim($value['value'], "+");
                                    $value['value'] = "+".$value['value'];
                                } ?>

                                <!-- Social Media input  -->
                                <div class="channels__input-box">
                                    <input data-label="<?php echo esc_attr($social['title']) ?>" placeholder="<?php esc_attr_e($placeholder); ?>" type="text" class="channels__input custom-channel-<?php echo esc_attr__($channel_type) ?> <?php echo isset($social['attr'])?$social['attr']:"" ?>" name="cht_social_<?php echo esc_attr($social['slug']); ?>[value]" value="<?php esc_attr_e(wp_unslash($value['value'])); ?>" data-gramm_editor="false" id="channel_input_<?php echo esc_attr($social['slug']); ?>" />
                                </div>
                            <?php } ?>
                            <div class="channels__device-box">
                                <?php
                                $slug =  esc_attr__($this->del_space($social['slug']));
                                $slug = str_replace(' ', '_', $slug);
                                $is_desktop = isset($value['is_desktop']) && $value['is_desktop'] == "checked" ? "checked" : '';
                                $is_mobile = isset($value['is_mobile']) && $value['is_mobile'] == "checked" ? "checked" : '';
                                ?>
                                <!-- setting for desktop -->
                                <label class="channels__view" for="<?php echo esc_attr($slug); ?>Desktop">
                                    <input type="checkbox" id="<?php echo esc_attr($slug); ?>Desktop" class="channels__view-check js-chanel-icon js-chanel-desktop" data-type="<?php echo str_replace(' ', '_', strtolower(esc_attr__($this->del_space($social['slug'])))); ?>" name="cht_social_<?php echo esc_attr($social['slug']); ?>[is_desktop]" value="checked" data-gramm_editor="false" <?php esc_attr_e($is_desktop) ?> />
                                    <span class="channels__view-txt">Desktop</label>
                                </label>

                                <!-- setting for mobile -->
                                <label class="channels__view" for="<?php echo esc_attr($slug); ?>Mobile">
                                    <input type="checkbox" id="<?php echo esc_attr($slug); ?>Mobile" class="channels__view-check js-chanel-icon js-chanel-mobile" data-type="<?php echo str_replace(' ', '_', strtolower(esc_attr__($this->del_space($social['slug'])))); ?>" name="cht_social_<?php echo esc_attr($social['slug']); ?>[is_mobile]" value="checked" data-gramm_editor="false" <?php esc_attr_e($is_mobile) ?> >
                                    <span class="channels__view-txt">Mobile</span>
                                </label>
                            </div>

                            <?php if($social['slug'] == 'Contact_Us') { ?>
                                <div class="channels__input transparent"></div>
                            <?php } ?>

                            <?php
                            $close_class = "active";
                            if($social['slug'] == 'Contact_Us') {
                                $setting_status = get_option("chaty_contact_us_setting");
                                if($setting_status === false) {
                                    $close_class = "";
                                }
                            }
                            ?>

                            <!-- button for advance setting -->
                            <div class="chaty-settings <?php echo esc_attr($close_class) ?>" data-nonce="<?php echo wp_create_nonce($social['slug']."-settings") ?>" id="<?php echo esc_attr($social['slug']); ?>-close-btn" onclick="toggle_chaty_setting('<?php echo esc_attr($social['slug']); ?>')">
                                <a href="javascript:;"><span class="dashicons dashicons-admin-generic"></span> Settings</a>
                            </div>
                            <?php if($social['slug'] != 'Contact_Us') { ?>
                                <!-- example for social media -->
                                <div class="input-example">
                                    <?php esc_attr_e('For example', CHT_OPT); ?>:
                                    <span class="inline-box channel-example">
                                        <?php if($social['slug'] == "Poptin") { ?>
                                            <br/>
                                        <?php } ?>
                                            <?php esc_attr_e($placeholder); ?>
                                    </span>
                                </div>

                                <!-- checking for extra help message for social media -->
                                <div class="help-section">
                                    <?php if((isset($social['help']) && !empty($social['help'])) || isset($social['help_link'])) { ?>
                                        <div class="viber-help">
                                            <?php if(isset($help_link) && !empty($help_link)) { ?>
                                                <a class="help-link" href="<?php echo esc_url($help_link) ?>" target="_blank"><?php esc_attr_e($help_title); ?></a>
                                            <?php } else if(isset($help_text) && !empty($help_text)) { ?>
                                                <span class="help-text"><?php echo $help_text; ?></span>
                                                <span class="help-title"><?php esc_attr_e($help_title); ?></span>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>

                        <?php if($social['slug'] == "Whatsapp" || $social['slug'] == "Link" || $social['slug'] == "Custom_Link" || $social['slug'] == "Custom_Link_3" || $social['slug'] == "Custom_Link_4" || $social['slug'] == "Custom_Link_5") { ?>
                            <div class="Whatsapp-settings advanced-settings extra-chaty-settings">
                                <?php $embedded_window = isset($value['embedded_window'])?$value['embedded_window']:"no"; ?>
                                <div class="chaty-setting-col">
                                    <input type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[embedded_window]" value="no" >
                                    <label class="chaty-switch chaty-embedded-window" for="whatsapp_embedded_window_<?php echo esc_attr($social['slug']); ?>">
                                        <input type="checkbox" class="embedded_window-checkbox" name="cht_social_<?php echo esc_attr($social['slug']); ?>[embedded_window]" id="whatsapp_embedded_window_<?php echo esc_attr($social['slug']); ?>" value="yes" <?php checked($embedded_window, "yes") ?> >
                                        <div class="chaty-slider round"></div>
                                        WhatsApp Chat Popup &#128172;
                                        <div class="html-tooltip">
                                            <span class="dashicons dashicons-editor-help"></span>
                                            <span class="tooltip-text">
                                                Show an embedded WhatsApp window to your visitors with a welcome message. Your users can start typing their own message and start a conversation with you right away once they are forwarded to the WhatsApp app.
                                                <img src="<?php echo esc_url(CHT_PLUGIN_URL) ?>/admin/assets/images/whatsapp-popup.gif" />
                                            </span>
                                        </div>
                                    </label>
                                </div>
                                <!-- advance setting for Whatsapp -->
                                <div class="whatsapp-welcome-message <?php echo ($embedded_window=="yes")?"active":"" ?>">
                                    <div class="chaty-setting-col">
                                        <label for="cht_social_embedded_message_<?php echo esc_attr($social['slug']); ?>">Welcome message</label>
                                        <div class="full-width">
                                            <div class="full-width">
                                                <?php $unique_id = uniqid(); ?>
                                                <?php $embedded_message = isset($value['embedded_message'])?$value['embedded_message']:esc_html__("How can I help you? :)", "chaty"); ?>
                                                <textarea class="chaty-setting-textarea chaty-whatsapp-setting-textarea" data-id="<?php echo esc_attr($unique_id) ?>" id="cht_social_embedded_message_<?php echo esc_attr($unique_id) ?>" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[embedded_message]" ><?php echo $embedded_message ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="chaty-setting-col">
                                        <?php $is_default_open = isset($value['is_default_open'])?$value['is_default_open']:""; ?>
                                        <input type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[is_default_open]" value="no" >
                                        <label class="chaty-switch" for="whatsapp_default_open_embedded_window_<?php echo esc_attr($social['slug']); ?>">
                                            <input type="checkbox" name="cht_social_<?php echo esc_attr($social['slug']); ?>[is_default_open]" id="whatsapp_default_open_embedded_window_<?php echo esc_attr($social['slug']); ?>" value="yes" <?php checked($is_default_open, "yes") ?> >
                                            <div class="chaty-slider round"></div>
                                            Open the window on load
                                            <span class="icon label-tooltip" data-title="Open the WhatsApp chat popup on page load, after the user sends a message or closes the window, the window will stay closed to avoid disruption"><span class="dashicons dashicons-editor-help"></span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <!-- advance setting fields: start -->
                        <?php $class_name = !$this->is_pro()?"not-is-pro":""; ?>
                        <div class="chaty-advance-settings <?php esc_attr_e($class_name); ?>" style="<?php echo (empty($close_class) && $social['slug'] == 'Contact_Us')?"display:block":""; ?>">
                            <!-- Settings for custom icon and color -->
                            <div class="chaty-setting-col">
                                <label>Icon Appearance</label>
                                <div>
                                    <!-- input for custom color -->
                                    <input type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[bg_color]" class="chaty-color-field" value="<?php esc_attr_e($value['bg_color']) ?>" />

                                    <!-- button to upload custom image -->
                                    <?php if($this->is_pro()) { ?>
                                        <a onclick="upload_chaty_image('<?php echo esc_attr($social['slug']); ?>')" href="javascript:;" class="upload-chaty-icon"><span class="dashicons dashicons-upload"></span> Custom Image</a>

                                        <!-- hidden input value for image -->
                                        <input id="cht_social_image_<?php echo esc_attr($social['slug']); ?>" type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[image_id]" value="<?php esc_attr_e($imageId) ?>" />
                                    <?php } else { ?>
                                        <div class="pro-features">
                                            <div class="pro-item">
                                                <a target="_blank" href="<?php echo esc_url($this->getUpgradeMenuItemUrl());?>" class="upload-chaty-icon"><span class="dashicons dashicons-upload"></span> Custom Image</a>
                                            </div>
                                            <div class="pro-button">
                                                <a target="_blank" href="<?php echo esc_url($this->getUpgradeMenuItemUrl());?>"><?php esc_attr_e('Activate your key', CHT_OPT);?></a>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="clear clearfix"></div>

                            <?php if($social['slug'] == "Link" || $social['slug'] == "Custom_Link" || $social['slug'] == "Custom_Link_3" || $social['slug'] == "Custom_Link_4" || $social['slug'] == "Custom_Link_5") {
                                $channel_type = "";
                                if(isset($value['channel_type'])) {
                                    $channel_type = esc_attr__(wp_unslash($value['channel_type']));
                                } else {
                                    $channel_type = $social['slug'];
                                }
                                $socials = $this->socials;
                                ?>
                                <div class="chaty-setting-col">
                                    <label>Channel type</label>
                                    <div>
                                        <!-- input for custom title -->
                                        <select class="channel-select-input" name="cht_social_<?php echo esc_attr($social['slug']); ?>[channel_type]" value="<?php esc_attr_e($value['channel_type']) ?>">
                                            <option value="<?php echo esc_attr($social['slug']) ?>">Custom channel</option>
                                            <?php foreach ($socials as $social_icon) {
                                                $selected = ($social_icon['slug'] == $channel_type)?"selected":"";
                                                if ($social_icon['slug'] != 'Custom_Link' && $social_icon['slug'] != 'Link' && $social_icon['slug'] != 'Custom_Link_3' && $social_icon['slug'] != 'Custom_Link_4' && $social_icon['slug'] != 'Custom_Link_5' && $social_icon['slug'] != 'Contact_Us') { ?>
                                                    <option <?php echo esc_attr($selected) ?> value="<?php echo esc_attr($social_icon['slug']) ?>"><?php echo esc_attr($social_icon['title']) ?></option>
                                                <?php }
                                            }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="clear clearfix"></div>
                            <?php } ?>
                            <!-- Settings for custom title -->
                            <div class="chaty-setting-col">
                                <label>On Hover Text</label>
                                <div>
                                    <!-- input for custom title -->
                                    <input type="text" class="chaty-title" name="cht_social_<?php echo esc_attr($social['slug']); ?>[title]" value="<?php esc_attr_e($value['title']) ?>">
                                </div>
                            </div>
                            <div class="Whatsapp-settings advanced-settings">
                                <div class="clear clearfix"></div>
                                <div class="chaty-setting-col">
                                    <label>Pre Set Message <span class="icon label-tooltip inline-tooltip" data-title="Add your own pre-set message that's automatically added to the user's message. You can also use merge tags and add the URL or the title of the current visitor's page. E.g. you can add the current URL of a product to the message so you know which product the visitor is talking about when the visitor messages you"><span class="dashicons dashicons-editor-help"></span></span></label>
                                    <div>
                                        <div class="pre-message-whatsapp">
                                            <?php $pre_set_message = isset($value['pre_set_message'])?$value['pre_set_message']:""; ?>
                                            <input id="cht_social_message_<?php echo esc_attr($social['slug']); ?>" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[pre_set_message]" class="pre-set-message-whatsapp" value="<?php esc_attr_e($pre_set_message) ?>" >
                                            <button data-button="cht_social_message_<?php echo esc_attr($social['slug']); ?>" type="button"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0m0 22C6.486 22 2 17.514 2 12S6.486 2 12 2s10 4.486 10 10-4.486 10-10 10"></path><path d="M8 7a2 2 0 1 0-.001 3.999A2 2 0 0 0 8 7M16 7a2 2 0 1 0-.001 3.999A2 2 0 0 0 16 7M15.232 15c-.693 1.195-1.87 2-3.349 2-1.477 0-2.655-.805-3.347-2H15m3-2H6a6 6 0 1 0 12 0"></path></svg></button>
                                        </div>
                                        <span class="supported-tags"><span class="icon label-tooltip support-tooltip" data-title="{title} tag grabs the page title of the webpage">{title}</span> and  <span class="icon label-tooltip support-tooltip" data-title="{url} tag grabs the URL of the page">{url}</span> tags are supported</span>
                                    </div>
                                </div>
                            </div>
                            <?php $use_whatsapp_web = isset($value['use_whatsapp_web'])?$value['use_whatsapp_web']:"yes"; ?>
                            <div class="Whatsapp-settings advanced-settings">
                                <div class="clear clearfix"></div>
                                <div class="chaty-setting-col">
                                    <label>Whatsapp Web</label>
                                    <input type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[use_whatsapp_web]" value="no" />
                                    <div>
                                        <div class="checkbox">
                                            <label for="cht_social_<?php echo esc_attr($social['slug']); ?>_use_whatsapp_web" class="chaty-checkbox">
                                                <input class="sr-only" type="checkbox" id="cht_social_<?php echo esc_attr($social['slug']); ?>_use_whatsapp_web" name="cht_social_<?php echo esc_attr($social['slug']); ?>[use_whatsapp_web]" value="yes" <?php echo checked($use_whatsapp_web, "yes") ?> />
                                                <span></span>
                                                Use Whatsapp Web directly on desktop
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="Contact_Us-settings advanced-settings">
                                <div class="clear clearfix"></div>
                                <div class="chaty-setting-col">
                                    <label>Contact Form Title</label>
                                    <div>
                                        <?php $contact_form_title = isset($value['contact_form_title'])?$value['contact_form_title']:esc_html__("Contact Us", "chaty"); ?>
                                        <input id="cht_social_message_<?php echo esc_attr($social['slug']); ?>" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[contact_form_title]" value="<?php esc_attr_e($contact_form_title) ?>" >
                                    </div>
                                </div>
                                <?php
                                $fields = array(
                                    'name' => array(
                                        'title' => "Name",
                                        'placeholder' => "Enter your name",
                                        'is_required' => 1,
                                        'type' => 'input',
                                        'is_enabled' => 1
                                    ),
                                    'email' => array(
                                        'title' => "Email",
                                        'placeholder' => "Enter your email address",
                                        'is_required' => 1,
                                        'type' => 'email',
                                        'is_enabled' => 1
                                    ),
                                    'phone' => array(
                                        'title' => "Phone",
                                        'placeholder' => "Enter your phone number",
                                        'is_required' => 1,
                                        'type' => 'input',
                                        'is_enabled' => 1
                                    ),
                                    'message' => array(
                                        'title' => "Message",
                                        'placeholder' => "Enter your message",
                                        'is_required' => 1,
                                        'type' => 'textarea',
                                        'is_enabled' => 1
                                    )
                                );
                                echo '<div class="form-field-setting-col">';
                                foreach ($fields as $label => $field) {
                                    $saved_value = isset($value[$label])?$value[$label]:array();
                                    $field_value = array(
                                        'is_active' => (isset($saved_value['is_active']))?$saved_value['is_active']:'yes',
                                        'is_required' => (isset($saved_value['is_required']))?$saved_value['is_required']:'yes',
                                        'placeholder' => (isset($saved_value['placeholder']))?$saved_value['placeholder']:$field['placeholder'],
                                    );
                                    ?>
                                    <div class="field-setting-col">
                                        <input type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[<?php echo $label ?>][is_active]" value="no">
                                        <input type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[<?php echo $label ?>][is_required]" value="no">

                                        <div class="left-section">
                                            <label class="chaty-switch chaty-switch-toggle" for="field_for_<?php echo esc_attr($social['slug']); ?>_<?php echo $label ?>">
                                                <input type="checkbox" class="chaty-field-setting" name="cht_social_<?php echo esc_attr($social['slug']); ?>[<?php echo $label ?>][is_active]" id="field_for_<?php echo esc_attr($social['slug']); ?>_<?php echo $label ?>" value="yes" <?php checked($field_value['is_active'], "yes") ?>>
                                                <div class="chaty-slider round"></div>

                                                <?php echo $field['title'] ?>
                                            </label>
                                        </div>
                                        <div class="right-section">
                                            <div class="field-settings <?php echo ($field_value['is_active']=="yes")?"active":"" ?>">
                                                <div class="inline-block">
                                                    <label class="inline-block" for="field_required_for_<?php echo esc_attr($social['slug']); ?>_<?php echo $label ?>">Required?</label>
                                                    <div class="inline-block">
                                                        <label class="chaty-switch" for="field_required_for_<?php echo esc_attr($social['slug']); ?>_<?php echo $label ?>">
                                                            <input type="checkbox" name="cht_social_<?php echo esc_attr($social['slug']); ?>[<?php echo $label ?>][is_required]" id="field_required_for_<?php echo esc_attr($social['slug']); ?>_<?php echo $label ?>" value="yes" <?php checked($field_value['is_required'], "yes") ?>>
                                                            <div class="chaty-slider round"></div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="clear clearfix"></div>
                                        <div class="field-settings <?php echo ($field_value['is_active']=="yes")?"active":"" ?>">
                                            <div class="chaty-setting-col">
                                                <label for="placeholder_for_<?php echo esc_attr($social['slug']); ?>_<?php echo $label ?>">Placeholder text</label>
                                                <div>
                                                    <input id="placeholder_for_<?php echo esc_attr($social['slug']); ?>_<?php echo $label ?>" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[<?php echo $label ?>][placeholder]" value="<?php esc_attr_e($field_value['placeholder']) ?>" >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if($label != 'message') { ?>
                                        <div class="chaty-separator"></div>
                                    <?php } ?>
                                <?php }
                                echo '</div>'; ?>
                                <div class="form-field-setting-col">
                                    <div class="form-field-title">Submit Button</div>
                                    <div class="color-box">
                                        <div class="clr-setting">
                                            <?php $field_value = isset($value['button_text_color'])?$value['button_text_color']:"#ffffff" ?>
                                            <div class="chaty-setting-col">
                                                <label for="button_text_color_for_<?php echo esc_attr($social['slug']); ?>">Text color</label>
                                                <div>
                                                    <input id="button_text_color_for_<?php echo esc_attr($social['slug']); ?>" class="chaty-color-field button-color" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[button_text_color]" value="<?php esc_attr_e($field_value); ?>" >
                                                </div>
                                            </div>
                                        </div>
                                        <?php $field_value = isset($value['button_bg_color'])?$value['button_bg_color']:"#A886CD" ?>
                                        <div class="clr-setting">
                                            <div class="chaty-setting-col">
                                                <label for="button_bg_color_for_<?php echo esc_attr($social['slug']); ?>">Background color</label>
                                                <div>
                                                    <input id="button_bg_color_for_<?php echo esc_attr($social['slug']); ?>" class="chaty-color-field button-color" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[button_bg_color]" value="<?php esc_attr_e($field_value); ?>" >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $field_value = isset($value['button_text'])?$value['button_text']:"Submit" ?>
                                    <div class="chaty-setting-col">
                                        <label for="button_text_for_<?php echo esc_attr($social['slug']); ?>">Button text</label>
                                        <div>
                                            <input id="button_text_for_<?php echo esc_attr($social['slug']); ?>" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[button_text]" value="<?php esc_attr_e($field_value); ?>" >
                                        </div>
                                    </div>
                                    <?php $field_value = isset($value['thanks_message'])?$value['thanks_message']:"Your message was sent successfully" ?>
                                    <div class="chaty-setting-col">
                                        <label for="thanks_message_for_<?php echo esc_attr($social['slug']); ?>">Thank you message</label>
                                        <div>
                                            <input id="thanks_message_for_<?php echo esc_attr($social['slug']); ?>" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[thanks_message]" value="<?php esc_attr_e($field_value); ?>" >
                                        </div>
                                    </div>
                                    <div class="chaty-separator"></div>
                                    <?php $field_value = isset($value['redirect_action'])?$value['redirect_action']:"no" ?>
                                    <div class="chaty-setting-col">
                                        <input type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[redirect_action]" value="no" >
                                        <label class="chaty-switch" for="redirect_action_<?php echo esc_attr($social['slug']); ?>">
                                            <input type="checkbox" class="chaty-redirect-setting" name="cht_social_<?php echo esc_attr($social['slug']); ?>[redirect_action]" id="redirect_action_<?php echo esc_attr($social['slug']); ?>" value="yes" <?php checked($field_value, "yes") ?> >
                                            <div class="chaty-slider round"></div>
                                            Redirect visitors after submission
                                        </label>
                                    </div>
                                    <div class="redirect_action-settings <?php echo ($field_value == "yes")?"active":"" ?>">
                                        <?php $field_value = isset($value['redirect_link'])?$value['redirect_link']:"" ?>
                                        <div class="chaty-setting-col">
                                            <label for="redirect_link_for_<?php echo esc_attr($social['slug']); ?>">Redirect link</label>
                                            <div>
                                                <input id="redirect_link_for_<?php echo esc_attr($social['slug']); ?>" placeholder="<?php echo site_url("/") ?>" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[redirect_link]" value="<?php esc_attr_e($field_value); ?>" >
                                            </div>
                                        </div>
                                        <?php $field_value = isset($value['link_in_new_tab'])?$value['link_in_new_tab']:"no" ?>
                                        <div class="chaty-setting-col">
                                            <input type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[link_in_new_tab]" value="no" >
                                            <label class="chaty-switch" for="link_in_new_tab_<?php echo esc_attr($social['slug']); ?>">
                                                <input type="checkbox" class="chaty-field-setting" name="cht_social_<?php echo esc_attr($social['slug']); ?>[link_in_new_tab]" id="link_in_new_tab_<?php echo esc_attr($social['slug']); ?>" value="yes" <?php checked($field_value, "yes") ?> >
                                                <div class="chaty-slider round"></div>
                                                Open in a new tab
                                            </label>
                                        </div>
                                    </div>
                                    <div class="chaty-separator"></div>
                                    <?php $field_value = isset($value['close_form_after'])?$value['close_form_after']:"no" ?>
                                    <div class="chaty-setting-col">
                                        <input type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[close_form_after]" value="no" >
                                        <label class="chaty-switch" for="close_form_after_<?php echo esc_attr($social['slug']); ?>">
                                            <input type="checkbox" class="chaty-close_form_after-setting" name="cht_social_<?php echo esc_attr($social['slug']); ?>[close_form_after]" id="close_form_after_<?php echo esc_attr($social['slug']); ?>" value="yes" <?php checked($field_value, "yes") ?> >
                                            <div class="chaty-slider round"></div>
                                            Close form automatically after submission
                                            <span class="icon label-tooltip inline-message" data-title="Close the form automatically after a few seconds based on your choice"><span class="dashicons dashicons-editor-help"></span></span>
                                        </label>
                                    </div>
                                    <div class="close_form_after-settings <?php echo ($field_value == "yes")?"active":"" ?>">
                                        <?php $field_value = isset($value['close_form_after_seconds'])?$value['close_form_after_seconds']:"3" ?>
                                        <div class="chaty-setting-col">
                                            <label for="close_form_after_seconds_<?php echo esc_attr($social['slug']); ?>">Close after (Seconds)</label>
                                            <div>
                                                <input id="close_form_after_seconds_<?php echo esc_attr($social['slug']); ?>" type="number" name="cht_social_<?php echo esc_attr($social['slug']); ?>[close_form_after_seconds]" value="<?php esc_attr_e($field_value); ?>" >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-field-setting-col no-margin">
                                    <input type="hidden" value="no" name="cht_social_<?php echo esc_attr($social['slug']); ?>[send_leads_in_email]" >
                                    <input type="hidden" value="no" name="cht_social_<?php echo esc_attr($social['slug']); ?>[save_leads_locally]" >
                                    <?php $field_value = isset($val['save_leads_locally'])?$val['save_leads_locally']:"yes" ?>
                                    <div class="chaty-setting-col">
                                        <label for="save_leads_locally_<?php echo esc_attr($social['slug']); ?>" class="full-width chaty-switch">
                                            <input type="checkbox" id="save_leads_locally_<?php echo esc_attr($social['slug']); ?>" value="yes" name="cht_social_<?php echo esc_attr($social['slug']); ?>[save_leads_locally]" <?php checked($field_value, "yes") ?> >
                                            <div class="chaty-slider round"></div>
                                            Save leads to the local database
                                            <div class="html-tooltip top no-position">
                                                <span class="dashicons dashicons-editor-help"></span>
                                                <span class="tooltip-text top">Your leads will be saved in your local database, you'll be able to find them <a target="_blank" href="<?php echo admin_url("admin.php?page=chaty-contact-form-feed") ?>">here</a></span>
                                            </div>
                                        </label>
                                    </div>
                                    <?php $field_value = isset($value['send_leads_in_email'])?$value['send_leads_in_email']:"no" ?>
                                    <div class="chaty-setting-col">
                                        <label for="save_leads_to_email_<?php echo esc_attr($social['slug']); ?>" class="email-setting full-width chaty-switch">
                                            <input class="email-setting-field" type="checkbox" id="save_leads_to_email_<?php echo esc_attr($social['slug']); ?>" value="yes" name="cht_social_<?php echo esc_attr($social['slug']); ?>[send_leads_in_email]" <?php checked($field_value, "yes") ?> >
                                            <div class="chaty-slider round"></div>
                                            Send leads to your email
                                            <span class="icon label-tooltip" data-title="Get your leads by email, whenever you get a new email you'll get an email notification"><span class="dashicons dashicons-editor-help"></span></span>
                                        </label>
                                    </div>
                                    <div class="email-settings <?php echo ($field_value == "yes")?"active":"" ?>">
                                        <div class="chaty-setting-col">
                                            <label for="email_for_<?php echo esc_attr($social['slug']); ?>">Email address</label>
                                            <div>
                                                <?php $field_value = isset($value['email_address'])?$value['email_address']:"" ?>
                                                <input id="email_for_<?php echo esc_attr($social['slug']); ?>" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[email_address]" value="<?php esc_attr_e($field_value); ?>" >
                                            </div>
                                        </div>
                                        <div class="chaty-setting-col">
                                            <label for="sender_name_for_<?php echo esc_attr($social['slug']); ?>">Sender's name</label>
                                            <div>
                                                <?php $field_value = isset($value['sender_name'])?$value['sender_name']:"" ?>
                                                <input id="sender_name_for_<?php echo esc_attr($social['slug']); ?>" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[sender_name]" value="<?php esc_attr_e($field_value); ?>" >
                                            </div>
                                        </div>
                                        <div class="chaty-setting-col">
                                            <label for="email_subject_for_<?php echo esc_attr($social['slug']); ?>">Email subject</label>
                                            <div>
                                                <?php $field_value = isset($value['email_subject'])?$value['email_subject']:"New lead from Chaty - {name} - {date} {hour}" ?>
                                                <input id="email_subject_for_<?php echo esc_attr($social['slug']); ?>" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[email_subject]" value="<?php esc_attr_e($field_value); ?>" >
                                                <div class="mail-merge-tags"><span>{name}</span><span>{phone}</span><span>{email}</span><span>{date}</span><span>{hour}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if($this->is_pro()) { ?>
                                <div class="clear clearfix"></div>
                                <div class="Email-settings advanced-settings">
                                    <!-- advance setting for Email -->
                                    <div class="clear clearfix"></div>
                                    <div class="chaty-setting-col">
                                        <label>Mail Subject <span class="icon label-tooltip inline-tooltip" data-title="Add your own pre-set message that's automatically added to the user's message. You can also use merge tags and add the URL or the title of the current visitor's page. E.g. you can add the current URL of a product to the message so you know which product the visitor is talking about when the visitor messages you"><span class="dashicons dashicons-editor-help"></span></span></label>
                                        <div>
                                            <?php $mail_subject = isset($value['mail_subject'])?$value['mail_subject']:""; ?>
                                            <input id="cht_social_message_<?php echo esc_attr($social['slug']); ?>" type="text" name="cht_social_<?php echo esc_attr($social['slug']); ?>[mail_subject]" value="<?php esc_attr_e($mail_subject) ?>" >
                                            <span class="supported-tags"><span class="icon label-tooltip support-tooltip" data-title="{title} tag grabs the page title of the webpage">{title}</span> and  <span class="icon label-tooltip support-tooltip" data-title="{url} tag grabs the URL of the page">{url}</span> tags are supported</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="WeChat-settings advanced-settings">
                                    <!-- advance setting for WeChat -->
                                    <?php
                                    $qr_code = isset($value['qr_code'])?$value['qr_code']:"";                               // Initialize QR code value if not exists. 2.1.0 change
                                    $imageUrl = "";
                                    $status = 0;
                                    if($qr_code != "") {
                                        $imageUrl = wp_get_attachment_image_src($qr_code, "full")[0];                       // get custom Image URL if exists
                                    }
                                    if($imageUrl == "") {
                                        $imageUrl = plugin_dir_url("")."chaty-pro/admin/assets/images/chaty-default.png";   // Initialize with default image URL if URL is not exists
                                    } else {
                                        $status = 1;
                                    }
                                    ?>
                                    <div class="clear clearfix"></div>
                                    <div class="chaty-setting-col">
                                        <label>Upload QR Code</label>
                                        <div>
                                            <!-- Button to upload QR Code image -->
                                            <a class="cht-upload-image <?php esc_attr_e(($status)?"active":"") ?>" id="upload_qr_code" href="javascript:;" onclick="upload_qr_code('<?php echo esc_attr($social['slug']); ?>')">
                                                <img id="cht_social_image_src_<?php echo esc_attr($social['slug']); ?>" src="<?php echo esc_url($imageUrl) ?>" alt="<?php esc_attr_e($value['title']) ?>">
                                                <span class="dashicons dashicons-upload"></span>
                                            </a>

                                            <!-- Button to remove QR Code image -->
                                            <a href="javascript:;" class="remove-qr-code remove-qr-code-<?php echo esc_attr($social['slug']); ?> <?php esc_attr_e(($status)?"active":"") ?>" onclick="remove_qr_code('<?php echo esc_attr($social['slug']); ?>')"><span class="dashicons dashicons-no-alt"></span></a>

                                            <!-- input hidden field for QR Code -->
                                            <input id="upload_qr_code_val-<?php echo esc_attr($social['slug']); ?>" type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[qr_code]" value="<?php esc_attr_e($qr_code) ?>" >
                                        </div>
                                    </div>
                                </div>
                                <div class="Link-settings Custom_Link-settings Custom_Link_3-settings Custom_Link_4-settings Custom_Link_5-settings advanced-settings">
                                    <?php $is_checked = (!isset($value['new_window']) || $value['new_window'] == 1)?1:0; ?>
                                    <!-- Advance setting for Custom Link -->
                                    <div class="clear clearfix"></div>
                                    <div class="chaty-setting-col">
                                        <label >Open In a New Tab</label>
                                        <div>
                                            <input type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[new_window]" value="0" >
                                            <label class="channels__view" for="cht_social_window_<?php echo esc_attr($social['slug']); ?>">
                                                <input id="cht_social_window_<?php echo esc_attr($social['slug']); ?>" type="checkbox" class="channels__view-check" name="cht_social_<?php echo esc_attr($social['slug']); ?>[new_window]" value="1" <?php checked($is_checked, 1) ?> >
                                                <span class="channels__view-txt">&nbsp;</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="Linkedin-settings advanced-settings">
                                    <?php $is_checked = isset($value['link_type'])?$value['link_type']:"personal"; ?>
                                    <!-- Advance setting for Custom Link -->
                                    <div class="clear clearfix"></div>
                                    <div class="chaty-setting-col">
                                        <label >LinkedIn</label>
                                        <div>
                                            <label>
                                                <input type="radio" <?php checked($is_checked, "personal") ?> name="cht_social_<?php echo esc_attr($social['slug']); ?>[link_type]" value="personal">
                                                Personal
                                            </label>
                                            <label>
                                                <input type="radio" <?php checked($is_checked, "company") ?> name="cht_social_<?php echo esc_attr($social['slug']); ?>[link_type]" value="company">
                                                Company
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="clear clearfix"></div>
                                <div class="Email-settings advanced-settings">
                                    <div class="clear clearfix"></div>
                                    <div class="chaty-setting-col">
                                        <label>Mail Subject <span class="icon label-tooltip inline-tooltip" data-title="Add your own pre-set message that's automatically added to the user's message. You can also use merge tags and add the URL or the title of the current visitor's page. E.g. you can add the current URL of a product to the message so you know which product the visitor is talking about when the visitor messages you"><span class="dashicons dashicons-editor-help"></span></span></label>
                                        <div>
                                            <div class="pro-features">
                                                <div class="pro-item">
                                                    <input disabled id="cht_social_message_<?php echo esc_attr($social['slug']); ?>" type="text" name="" value="" >
                                                    <span class="supported-tags"><span class="icon label-tooltip support-tooltip" data-title="{title} tag grabs the page title of the webpage">{title}</span> and  <span class="icon label-tooltip support-tooltip" data-title="{url} tag grabs the URL of the page">{url}</span> tags are supported</span>
                                                </div>
                                                <div class="pro-button">
                                                    <a target="_blank" href="<?php echo esc_url($this->getUpgradeMenuItemUrl());?>"><?php esc_attr_e('Activate your license key', CHT_OPT);?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="WeChat-settings advanced-settings">
                                    <div class="clear clearfix"></div>
                                    <div class="chaty-setting-col">
                                        <label>Upload QR Code</label>
                                        <div>
                                            <a target="_blank" class="cht-upload-image-pro" id="upload_qr_code" href="<?php echo esc_url($this->getUpgradeMenuItemUrl());?>" >
                                                <span class="dashicons dashicons-upload"></span>
                                            </a>
                                            <a target="_blank" href="<?php echo esc_url($this->getUpgradeMenuItemUrl());?>"><?php esc_attr_e('Activate your license key', CHT_OPT);?></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="Link-settings Custom_Link-settings Custom_Link_3-settings Custom_Link_4-settings Custom_Link_5-settings advanced-settings">
                                    <?php $is_checked = 1; ?>
                                    <div class="clear clearfix"></div>
                                    <div class="chaty-setting-col">
                                        <label >Open In a New Tab</label>
                                        <div>
                                            <input type="hidden" name="cht_social_<?php echo esc_attr($social['slug']); ?>[new_window]" value="0" >
                                            <label class="channels__view" for="cht_social_window_<?php echo esc_attr($social['slug']); ?>">
                                                <input id="cht_social_window_<?php echo esc_attr($social['slug']); ?>" type="checkbox" class="channels__view-check" name="cht_social_<?php echo esc_attr($social['slug']); ?>[new_window]" value="1" checked >
                                                <span class="channels__view-txt">&nbsp;</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="Linkedin-settings advanced-settings">
                                    <?php $is_checked = "personal"; ?>
                                    <!-- Advance setting for Custom Link -->
                                    <div class="clear clearfix"></div>
                                    <div class="chaty-setting-col">
                                        <label >LinkedIn</label>
                                        <div>
                                            <label>
                                                <input type="radio" <?php checked($is_checked, "personal") ?> name="cht_social_<?php echo esc_attr($social['slug']); ?>[link_type]" value="personal">
                                                Personal
                                            </label>
                                            <label>
                                                <input type="radio" <?php checked($is_checked, "company") ?> name="cht_social_<?php echo esc_attr($social['slug']); ?>[link_type]" value="company">
                                                Company
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <!-- advance setting fields: end -->


                        <!-- remove social media setting button: start -->
                        <button type="button" class="btn-cancel" data-social="<?php echo esc_attr($social['slug']); ?>">
                            <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(2.26764 0.0615997) rotate(45)" fill="white"/>
                                <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(13.3198 1.649) rotate(135)" fill="white"/>
                            </svg>
                        </button>
                        <!-- remove social media setting button: end -->
                    </div>
                </li>
                <!-- Social media setting box: end -->
                <?php
                $html = ob_get_clean();
                echo json_encode($html);
            }
            wp_die();
        }
    }
    /* function choose_social_handler end */

    public function remove_chaty_widget() {
        if (current_user_can('manage_options')) {
            $widget_index = filter_input(INPUT_POST, 'widget_index', FILTER_SANITIZE_STRING);
            $widget_nonce = filter_input(INPUT_POST, 'widget_nonce', FILTER_SANITIZE_STRING);
            if (isset($widget_index) && !empty($widget_index) && !empty($widget_nonce) && wp_verify_nonce($widget_nonce, "chaty_remove_" . $widget_index)) {

                $index = $widget_index;
                $index = trim($index, "_");

                $deleted_list = get_option("chaty_deleted_settings");
                if (empty($deleted_list) || !is_array($deleted_list)) {
                    $deleted_list = array();
                }

                if (!in_array($index, $deleted_list)) {
                    $deleted_list[] = $index;
                    update_option("chaty_deleted_settings", $deleted_list);
                }

                if($index == 0) {
                    update_option("cht_is_default_deleted", 1);
                }

                echo esc_url(admin_url("admin.php?page=chaty-app"));
                exit;
            }
        }
    }

    public function change_chaty_widget_status() {
        if (current_user_can('manage_options')) {
            $widget_index = filter_input(INPUT_POST, 'widget_index', FILTER_SANITIZE_STRING);
            $widget_nonce = filter_input(INPUT_POST, 'widget_nonce', FILTER_SANITIZE_STRING);
            if (isset($widget_index) && !empty($widget_index) && !empty($widget_nonce) && wp_verify_nonce($widget_nonce, "chaty_remove_" . $widget_index)) {
                $widget_index = trim($widget_index,"_");
                if(empty($widget_index) || $widget_index == 0) {
                    $widget_index = "";
                } else {
                    $widget_index = "_".$widget_index;
                }
                $status = get_option("cht_active".$widget_index);
                if($status) {
                    update_option("cht_active".$widget_index, 0);
                } else {
                    update_option("cht_active".$widget_index, 1);
                }
            }
        }
        echo "1"; exit;
    }

    /* get social media list for front end widget */
    public function get_social_icon_list( $index = "") {
        if(empty($index)) {
            $index = $this->widget_number;
        }
        $social = get_option('cht_numb_slug'.$index); // get saved social media list
        $social = explode(",", $social);

        $arr = array();
        foreach ($social as $number=>$key_soc):
            foreach ($this->socials as $key => $social) :       // compare with Default Social media list
                if ($social['slug'] != $key_soc) {
                    continue;                                   // return if slug is not equal
                }
                $value = get_option('cht_social'.$index.'_' . $social['slug']);   //  get saved settings for button
                if ($value) {
                    $slug = strtolower($social['slug']);
                    if (!empty($value['value']) || $slug == "contact_us") {
                        $url = "";
                        $mobile_url = "";
                        $desktop_target = "";
                        $mobile_target = "";
                        $qr_code_image = "";

                        $channel_type = $slug;

                        if(!isset($value['value'])) {
                            $value['value'] = "";
                        }

                        $svg_icon = $social['svg'];
                        if($slug == "link" || $slug == "custom_link" || $slug == "custom_link_3" || $slug == "custom_link_4" || $slug == "custom_link_5") {
                            if(isset($value['channel_type']) && !empty($value['channel_type'])) {
                                $channel_type = $value['channel_type'];

                                foreach($this->socials as $icon) {
                                    if($icon['slug'] == $channel_type) {
                                        $svg_icon = $icon['svg'];
                                    }
                                }
                            }
                        }

                        $channel_type = strtolower($channel_type);
                        $channel_id = "cht-channel-".$number.$index;
                        $channel_id = trim($channel_id, "_");
                        $pre_set_message = "";

                        if($channel_type == "viber") {
                            /* Viber change to exclude + from number for desktop */
                            $val = $value['value'];
                            if(is_numeric($val)) {
                                $fc = substr($val, 0, 1);
                                if($fc == "+") {
                                    $length = -1*(strlen($val)-1);
                                    $val = substr($val, $length);
                                }
                                if(!wp_is_mobile()) {
                                    /* Viber change to include + from number for mobile */
                                    $val = "+".$val;
                                }
                            }
                        } else if($channel_type == "whatsapp") {
                            /* Whatspp change to exclude + from phone number */
                            $val = $value['value'];
                            $val = str_replace("+","", $val);
                        } else if($channel_type == "facebook_messenger") {
                            /* Facebook change to change URL from facebook.com to m.me version 2.1.0 change */
                            $val = $value['value'];
                            $val = str_replace("facebook.com","m.me", $val);
                            /* Facebook change to remove www. from URL. version 2.1.0 change */
                            $val = str_replace("www.","", $val);

                            $val = trim($val, "/");
                            $val_array = explode("/", $val);
                            $total = count($val_array)-1;
                            $last_value = $val_array[$total];
                            $last_value = explode("-", $last_value);
                            $total_text = count($last_value)-1;
                            $total_text = $last_value[$total_text];

                            if(is_numeric($total_text)) {
                                $val_array[$total] = $total_text;
                                $val = implode("/", $val_array);
                            }
                        } else {
                            $val = $value['value'];
                        }
                        if(!isset($value['title'])) {
                            $value['title'] = $social['title'];         // Initialize title with default title if not exists. version 2.1.0 change
                        }
                        $image_url = "";

                        /* get custom image URL if uploaded. version 2.1.0 change */
                        if(isset($value['image_id']) && !empty($value['image_id'])) {
                            $image_id = $value['image_id'];
                            if(!empty($image_id)) {
                                $image_data = wp_get_attachment_image_src($image_id, "full");
                                if(!empty($image_data) && is_array($image_data)) {
                                    $image_url = $image_data[0];
                                }
                            }
                        }

                        $on_click_fn = "";
                        $has_custom_popup = 0;
                        $popup_html = "";
                        $is_default_open = 0;
                        /* get custom icon background color if exists. version 2.1.0 change */
                        if(!isset($value['bg_color']) || empty($value['bg_color'])) {
                            $value['bg_color'] = '';
                        }
                        if($channel_type == "whatsapp") {
                            /* setting for Whatsapp URL */
                            $val = str_replace("+","",$val);
                            $val = str_replace(" ","",$val);
                            $val = str_replace("-","",$val);
                            if(isset($value['use_whatsapp_web']) && $value['use_whatsapp_web'] == "no") {
                                $url = "https://wa.me/".$val;
                            } else {
                                $url = "https://web.whatsapp.com/send?phone=" . $val;
                            }
                            $mobile_url = "https://wa.me/".$val;
                            // https://wa.me/$number?text=$test
                            if(isset($value['pre_set_message']) && !empty($value['pre_set_message'])) {
                                if(isset($value['use_whatsapp_web']) && $value['use_whatsapp_web'] == "no") {
                                    $url .= "?text=".rawurlencode($value['pre_set_message']);
                                } else {
                                    $url .= "&text=".rawurlencode($value['pre_set_message']);
                                }
                                $mobile_url .= "?text=".rawurlencode($value['pre_set_message']);
                            }
                            if(wp_is_mobile()) {
                                $mobile_target = "";
                            } else {
                                $desktop_target = "_blank";
                            }
                            if(isset($value['embedded_window']) && $value['embedded_window'] == "yes") {
                                $embedded_message = isset($value['embedded_message'])?$value['embedded_message']:"";
                                $pre_set_message = isset($value['pre_set_message'])?$value['pre_set_message']:"";
                                $is_default_open = (isset($value['is_default_open'])&&$value['is_default_open']=="yes")?1:0;
                                $has_custom_popup = 1;
                                $mobile_url = "javascript:;";
                                $url = "javascript:;";
                                $close_button = "<div role='button' class='close-chaty-popup is-whatsapp-btn'><div class='chaty-close-button'></div></div>";
                                $popup_html = "<div class='chaty-whatsapp-popup'>";
                                $popup_html .= "<span class='default-value' style='display:none'>".esc_attr($pre_set_message)."</span>";
                                $popup_html .= "<span class='default-msg-value' style='display:none'>".esc_attr($embedded_message)."</span>";
                                $popup_html .= "<span class='default-msg-phone' style='display:none'>".esc_attr($val)."</span>";
                                $popup_html .= "<div class='chaty-whatsapp-body'>".$close_button."<div class='chaty-whatsapp-message'></div></div>";
                                $popup_html .= "<div class='chaty-whatsapp-footer'>";
                                $popup_html .= "<form class='whatsapp-chaty-form' autocomplete='off' target='_blank' action='https://web.whatsapp.com/send' method='get'>";
                                $popup_html .= "<div class='chaty-whatsapp-field'><input autocomplete='off' class='chaty-whatsapp-msg' name='text' value='' /></div>";
                                $popup_html .= "<input type='hidden' name='phone' class='chaty-whatsapp-phone' value='' />";
                                $popup_html .= "<input type='hidden' class='is-default-open' value='".esc_attr($is_default_open)."' />";
                                $popup_html .= "<input type='hidden' class='channel-id' value='".esc_attr($channel_id)."' />";
                                if(isset($value['use_whatsapp_web']) && $value['use_whatsapp_web'] == "no") {
                                    $popup_html .= "<input type='hidden' class='use-whatsapp-web' value='1' />";
                                }
                                $popup_html .= "<button type='submit' class='chaty-whatsapp-submit-btn'><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24'><path fill='#ffffff' d='M1.101 21.757L23.8 12.028 1.101 2.3l.011 7.912 13.623 1.816-13.623 1.817-.011 7.912z'></path></svg></button><div style='clear:both'></div>";
                                $popup_html .= "</form>";
                                $popup_html .= "</div>";
                                $popup_html .= "</div>";
                            }
                        } else if($channel_type == "phone") {
                            /* setting for Phone */
                            $url = "tel:".$val;
                        } else if($channel_type == "sms") {
                            /* setting for SMS */
                            $url = "sms:".$val;
                        } else if($channel_type == "telegram") {
                            /* setting for Telegram */
                            $val = ltrim($val, "@");
                            $url = "https://telegram.me/".$val;
                            $desktop_target = "_blank";
                            $mobile_target = "_blank";
                        } else if($channel_type == "line" || $channel_type == "google_maps" || $channel_type == "poptin" || $channel_type == "waze" ) {
                            /* setting for Line, Google Map, Link, Poptin, Waze, Custom Link */
                            $url = esc_url($val);
                            $desktop_target = "_blank";
                            $mobile_target = "_blank";
                        } else if($channel_type == "link" || $channel_type == "custom_link" || $channel_type == "custom_link_3" || $channel_type == "custom_link_4" || $channel_type == "custom_link_5") {
                            $url = $val;
                            $is_exist = strpos($val, "javascript");
                            $is_viber = strpos($val, "viber");
                            if($is_viber !== false) {
                                $url = $val;
                            } else if($is_exist === false) {
                                $url = esc_url($val);
                                if($channel_type == "custom_link" || $channel_type == "link" || $channel_type == "custom_link_3" || $channel_type == "custom_link_4" || $channel_type == "custom_link_5") {
                                    $desktop_target = (isset($value['new_window']) && $value['new_window'] == 0)?"":"_blank";
                                    $mobile_target = (isset($value['new_window']) && $value['new_window'] == 0)?"":"_blank";
                                }
                            } else {
                                $url = "javascript:;";
                                $on_click_fn = str_replace('"',"'",$val);
                            }
                        }else if($channel_type == "wechat") {
                            /* setting for WeChat */
                            $url = "javascript:;";
                            if(!empty($value['title'])) {
                                $value['title'] .= ": ".$val;
                            } else {
                                $value['title'] = $val;
                            }
                            $qr_code = isset($value['qr_code'])?$value['qr_code']:"";
                            if(!empty($qr_code)) {
                                $image_data = wp_get_attachment_image_src($qr_code, "full");
                                if(!empty($image_data) && is_array($image_data)) {
                                    $qr_code_image = $image_data[0];
                                }
                            }
                        } else if($channel_type == "viber") {
                            /* setting for Viber */
                            $url = $val;
                        } else if($channel_type == "snapchat") {
                            /* setting for SnapChat */
                            $url = "https://www.snapchat.com/add/".$val;
                            $desktop_target = "_blank";
                            $mobile_target = "_blank";
                        } else if($channel_type == "waze") {
                            /* setting for Waze */
                            $url = "javascript:;";
                            $value['title'] .= ": ".$val;
                        } else if($channel_type == "vkontakte") {
                            /* setting for vkontakte */
                            $url = "https://vk.me/".$val;
                            $desktop_target = "_blank";
                            $mobile_target = "_blank";
                        } else if($channel_type == "skype") {
                            /* setting for Skype */
                            $url = "skype:".$val."?chat";
                        } else if($channel_type == "email") {
                            /* setting for Email */
                            $url = "mailto:".$val;
                            $mail_subject = (isset($value['mail_subject']) && !empty($value['mail_subject']))?$value['mail_subject']:"";
                            if($mail_subject != "") {
                                $url .= "?subject=".rawurlencode($mail_subject);
                            }
                        } else if($channel_type == "facebook_messenger") {
                            /* setting for facebook URL */
                            $url = esc_url($val);
                            $url = str_replace("http:", "https:", $url);
                            if(wp_is_mobile()) {
                                $mobile_target = "";
                            } else {
                                $desktop_target = "_blank";
                            }
                        } else if($channel_type == "twitter") {
                            /* setting for Twitter */
                            $url = "https://twitter.com/".$val;
                            $desktop_target = "_blank";
                            $mobile_target = "_blank";
                        } else if($channel_type == "instagram") {
                            /* setting for Instagram */
                            $url = "https://www.instagram.com/".$val;
                            $desktop_target = "_blank";
                            $mobile_target = "_blank";
                        } else if($channel_type == "linkedin") {
                            /* setting for Linkedin */
                            $link_type = !isset($value['link_type']) || $value['link_type'] == "company"?"company":"personal";
                            if($link_type == "personal") {
                                $url = "https://www.linkedin.com/in/".$val;
                            } else {
                                $url = "https://www.linkedin.com/company/".$val;
                            }
                            $desktop_target = "_blank";
                            $mobile_target = "_blank";
                        } else if($channel_type == "slack") {
                            /* setting for Twitter */
                            $url = esc_url($val);
                            $desktop_target = "_blank";
                            $mobile_target = "_blank";
                        } else if($channel_type == "contact_us") {
                            $url = "javascript:;";
                            $desktop_target = "";
                            $mobile_target = "";
                            $input_fields = "";
                            if(isset($value['name']) || isset($value['email']) || isset($value['message'])) {
                                $field_setting = $value['name'];
                                if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                                    $is_required = (isset($field_setting['is_required']) && $field_setting['is_required'] == "yes")?"is-required":"";
                                    $placeholder = isset($field_setting['placeholder'])?$field_setting['placeholder']:"Enter your name";
                                    $input_fields .= "<div class='chaty-input-area'>";
                                    $input_fields .= "<input autocomplete='off' class='chaty-input-field chaty-field-name {$is_required}' name='name' type='text' id='chaty-name' placeholder='{$placeholder}' />";
                                    $input_fields .= "</div>";
                                }
                                $field_setting = $value['email'];
                                if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                                    $is_required = (isset($field_setting['is_required']) && $field_setting['is_required'] == "yes")?"is-required":"";
                                    $placeholder = isset($field_setting['placeholder'])?$field_setting['placeholder']:"Enter your email address";
                                    $input_fields .= "<div class='chaty-input-area'>";
                                    $input_fields .= "<input autocomplete='off' class='chaty-input-field chaty-field-email {$is_required}' name='email' type='email' id='chaty-name' placeholder='{$placeholder}' />";
                                    $input_fields .= "</div>";
                                }
                                $field_setting = $value['phone'];
                                if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                                    $is_required = (isset($field_setting['is_required']) && $field_setting['is_required'] == "yes")?"is-required":"";
                                    $placeholder = isset($field_setting['placeholder'])?$field_setting['placeholder']:"Enter your phone number";
                                    $input_fields .= "<div class='chaty-input-area'>";
                                    $input_fields .= "<input autocomplete='off' class='chaty-input-field chaty-field-phone {$is_required}' name='name' type='text' id='chaty-phone' placeholder='{$placeholder}' />";
                                    $input_fields .= "</div>";
                                }
                                $field_setting = $value['message'];
                                if(isset($field_setting['is_active']) && $field_setting['is_active'] == "yes") {
                                    $is_required = (isset($field_setting['is_required']) && $field_setting['is_required'] == "yes")?"is-required":"";
                                    $placeholder = isset($field_setting['placeholder'])?$field_setting['placeholder']:"Enter your message";
                                    $input_fields .= "<div class='chaty-input-area'>";
                                    $input_fields .= "<textarea autocomplete='off' class='chaty-input-field chaty-field-message {$is_required}' name='name' id='chaty-name' placeholder='{$placeholder}' ></textarea>";
                                    $input_fields .= "</div>";
                                }
                            }
                            if(!empty($input_fields)) {
                                $has_custom_popup = 1;
                                $button_text = isset($value['button_text']) && !empty($value['button_text'])?$value['button_text']:"Submit";
                                $button_bg_color = isset($value['button_bg_color']) && !empty($value['button_bg_color'])?$value['button_bg_color']:"#A886CD";
                                $button_text_color = isset($value['button_text_color']) && !empty($value['button_text_color'])?$value['button_text_color']:"#ffffff";
                                $contact_form_title = isset($value['contact_form_title'])?$value['contact_form_title']:"";
                                $popup_html = "<div class='chaty-contact-form'>";
                                $popup_html .= "<form action='#' method='post' class='chaty-contact-form-data' autocomplete='off'>";
                                $popup_html .= "<div class='chaty-contact-header'>".esc_attr($contact_form_title)." <div role='button' class='close-chaty-popup'><div class='chaty-close-button'></div></div><div style='clear:both'></div></div>";
                                $popup_html .= "<div class='chaty-contact-body'>";
                                $popup_html .= $input_fields;
                                $popup_html .= "<input type='hidden' class='chaty-field-widget' name='widget_id' value='{$index}' />";
                                $popup_html .= "<input type='hidden' class='chaty-field-channel' name='channel' value='{$social['slug']}' />";
                                $nonce = wp_create_nonce("chaty-front-form".$index);
                                $popup_html .= "<input type='hidden' class='chaty-field-nonce' name='nonce' value='{$nonce}' />";
                                $popup_html .= "</div>";
                                $popup_html .= "<div class='chaty-contact-footer'>";
                                $popup_html .= "<button style='color: {$button_text_color}; background: {$button_bg_color}' type='submit' class='chaty-contact-submit-btn' data-text='{$button_text}'>{$button_text}</div>";
                                $popup_html .= "</div>";
                                $popup_html .= "</form>";
                                $popup_html .= "</div>";
                            }
                        } else if($channel_type == "tiktok") {
                            $val = $value['value'];
                            $firstCharacter = substr($val, 0, 1);
                            if($firstCharacter != "@") {
                                $val = "@".$val;
                            }
                            $url = esc_url("https://www.tiktok.com/".$val);
                            $desktop_target = $mobile_target = "_blank";
                        }

                        /* Instagram checking for custom color */
                        if($channel_type == "instagram" && $value['bg_color'] == "#ffffff") {
                            $value['bg_color'] = "";
                        }

                        $svg = trim(preg_replace('/\s\s+/', '', $svg_icon));

                        $is_mobile = isset($value['is_mobile']) ? 1 : 0;
                        $is_desktop = isset($value['is_desktop']) ? 1 : 0;

                        if(empty($mobile_url)) {
                            $mobile_url = $url;
                        }

                        $svg_class = ($channel_type == "contact_us")?"color-element":"";

                        $svg = '<svg aria-hidden="true" class="ico_d '.$svg_class.'" width="39" height="39" viewBox="0 0 39 39" fill="none" xmlns="http://www.w3.org/2000/svg" style="transform: rotate(0deg);">'.$svg.'</svg>';

                        $rgb_color = $this->getRGBColor($value['bg_color']);
                        $data = array(
                            'val' => esc_attr__(wp_unslash($val)),
                            'default_icon' => $svg,
                            'bg_color' => $value['bg_color'],
                            'rbg_color' => $rgb_color,
                            'title' => esc_attr__(wp_unslash($value['title'])),
                            'img_url' => esc_url($image_url),
                            'social_channel' => $slug,
                            'channel_type' => $channel_type,
                            'href_url' => $url,
                            'desktop_target' => $desktop_target,
                            'mobile_target' => $mobile_target,
                            'qr_code_image' => esc_url($qr_code_image),
                            'channel' => $social['slug'],
                            'channel_nonce' => wp_create_nonce($social['slug']."_".$index),
                            'is_mobile' => $is_mobile,
                            'is_desktop' => $is_desktop,
                            'mobile_url' => $mobile_url,
                            'on_click' => $on_click_fn,
                            "has_font" => 0,
                            'has_custom_popup' => $has_custom_popup,
                            'popup_html' => $popup_html,
                            'is_default_open' => $is_default_open,
                            'channel_id' => $channel_id,
                            'pre_set_message' => $pre_set_message
                        );
                        $arr[] = $data;
                    }
                }
            endforeach;
        endforeach;
        return $arr;
    }

    /* add widget to fron end */
    public function insert_widget()
    {

    }

    public function getRGBColor($color) {
        if(!empty($color)) {
            if (strpos($color, '#') !== false) {
                $color = $this->hex2rgba($color);
            }
            if (strpos($color, 'rgba(') !== false || strpos($color, 'rgb(') !== false) {
                $color = explode(",", $color);
                $color = str_replace(array("rgba(", "rgb(", ")"), array("","",""), $color);
                $string = "";
                $string .= ((isset($color[0]))?trim($color[0]):"0").",";
                $string .= ((isset($color[1]))?trim($color[1]):"0").",";
                $string .= ((isset($color[2]))?trim($color[2]):"0");
                return $string;
            }
        }
        return "0,0,0";
    }

    public function hex2rgba($color, $opacity = false) {

        $default = 'rgb(0,0,0)';

        //Return default if no color provided
        if(empty($color))
            return $default;

        //Sanitize $color if "#" is provided
        if ($color[0] == '#' ) {
            $color = substr( $color, 1 );
        }

        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
            $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
            $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
            return $default;
        }

        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if($opacity){
            if(abs($opacity) > 1)
                $opacity = 1.0;
            $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
            $output = 'rgb('.implode(",",$rgb).')';
        }

        //Return rgb(a) color string
        return $output;
    }

    public function check_for_url( $index = "") {
        if(empty($index)) {
            $index = $this->widget_number;
        }
        $page_options = get_option("cht_page_settings".$index);
        $or_flag = 1;       // for page Rule contain
        /* checking for page visibility settings */
        if (!empty($page_options) && is_array($page_options)) {
            $server = $_SERVER;

            $link = (isset($server['HTTPS']) && $server['HTTPS'] === 'on' ? "https" : "http") . "://" .$server['HTTP_HOST'] . $server['REQUEST_URI'];
            $site_url = site_url("/");
            $request_url = substr($link, strlen($site_url));
            $url = trim($request_url, "/");
            $url = strtolower($url);
            $or_flag = 0;
            $total_option = count($page_options);
            $options = 0;
            /* checking for each page options */
            foreach ($page_options as $option) {
                $key = $option['option'];
                    $value = trim(strtolower($option['value']));
                if ($key != '' && $value != '') {
                    if($option['shown_on'] == "show_on") {
                        $value = trim($value, "/");
                        switch ($key) {
                            case 'page_contains':
                                $index = strpos($url, $value);
                                if($index !== false) {
                                    $or_flag = 1;
                                }
                                break;
                            case 'page_has_url':
                                if ($url === $value) {
                                    $or_flag = 1;
                                }
                                break;
                            case 'page_start_with':
                                $length = strlen($value);
                                $result = substr($url, 0, $length);
                                if ($result == $value) {
                                    $or_flag = 1;
                                }
                                break;
                            case 'page_end_with':
                                $length = strlen($value);
                                $result = substr($url, (-1) * $length);
                                if ($result == $value) {
                                    $or_flag = 1;
                                }
                                break;
                        }
                    } else {
                        $options++;
                    }
                }
            }
            if($total_option == $options) {
                $or_flag = 1;
            }
            foreach ($page_options as $option) {
                $key = $option['option'];
                $value = trim(strtolower($option['value']));
                if ($key != '' && $option['shown_on'] == "not_show_on" && $value != '') {
                    $value = trim($value, "/");
                    switch ($key) {
                        case 'page_contains':
                            $index = strpos($url, $value);
                            if($index !== false) {
                                $or_flag = 0;
                            }
                            break;
                        case 'page_has_url':
                            if ($url === $value) {
                                $or_flag = 0;
                            }
                            break;
                        case 'page_start_with':
                            $length = strlen($value);
                            $result = substr($url, 0, $length);
                            if ($result == $value) {
                                $or_flag = 0;
                            }
                            break;
                        case 'page_end_with':
                            $length = strlen($value);
                            $result = substr($url, (-1) * $length);
                            if ($result == $value) {
                                $or_flag = 0;
                            }
                            break;
                    }
                }
            }
        }
        return $or_flag;
    }

    public function get_widget_settings( $index = "") {
        $is_traffic_source = $this->getVisitorTrafficSource($index);
        if(get_option('cht_active'.$index) && $is_traffic_source) {
            $page_status = $this->check_for_url($index);
            if ($page_status) {
                $social = $this->get_social_icon_list($index);
                $cht_active = get_option("cht_active".$index);

                $len = count($social);

                if($len >= 1 && !empty($social)) {

                    $def_color = get_option('cht_color' . $index);
                    $custom_color = get_option('cht_custom_color' . $index);     // checking for custom color
                    if (!empty($custom_color)) {
                        $color = $custom_color;
                    } else {
                        $color = $def_color;
                    }
                    $bg_color = strtoupper($color);


                    // get total active channels
                    $cta = nl2br(get_option('cht_cta' . $index));
                    $cta = str_replace(array("\r", "\n"), "", $cta);
                    $cta = str_replace("&amp;#39;","'",$cta);
                    $cta = str_replace("&#39;","'",$cta);
                    $cta = esc_attr__(wp_unslash($cta));

                    $isPro = get_option('cht_token');                                 // is PRO version
                    $isPro = (empty($isPro) || $isPro == null) ? 0 : 1;

                    $positionSide = get_option('positionSide' . $index);                             // get widget position
                    $cht_bottom_spacing = get_option('cht_bottom_spacing' . $index);                 // get widget position from bottom
                    $cht_side_spacing = get_option('cht_side_spacing' . $index);                     // get widget position from left/Right
                    $cht_widget_size = get_option('cht_widget_size' . $index);                       // get widget size
                    $positionSide = empty($positionSide) ? 'right' : $positionSide;         // Initialize widget position if not exists
                    $cht_side_spacing = ($cht_side_spacing) ? $cht_side_spacing : '25';     // Initialize widget from left/Right if not exists
                    $cht_widget_size = ($cht_widget_size) ? $cht_widget_size : '54';        // Initialize widget size if not exists
                    $position = get_option('cht_position' . $index);
                    $position = ($position) ? $position : 'right';                          // Initialize widget position if not exists
                    $total = $cht_side_spacing + $cht_widget_size + $cht_side_spacing;
                    $cht_bottom_spacing = ($cht_bottom_spacing) ? $cht_bottom_spacing : '25';   // Initialize widget bottom position if not exists
                    $cht_side_spacing = ($cht_side_spacing) ? $cht_side_spacing : '25';     // Initialize widget left/Right position if not exists
                    $image_id = "";
                    $imageUrl = plugin_dir_url("") . "chaty-pro/admin/assets/images/chaty-default.png";       // Initialize default image
                    $analytics = get_option("cht_google_analytics" . $index);                       // check for google analytics enable or not
                    $analytics = empty($analytics) ? 0 : $analytics;                            // Initialize google analytics flag to 0 if not data not exists
                    $text = get_option("cht_close_button_text" . $index);       // close button settings
                    $close_text = ($text === false) ? "Hide" : $text;

                    $imageUrl = "";
                    if ($image_id != "") {
                        $image_data = wp_get_attachment_image_src($image_id, "full");
                        if (!empty($image_data) && is_array($image_data)) {
                            $imageUrl = $image_data[0];                                     // change close button image if exists
                        }
                    }
                    $font_family = get_option('cht_widget_font' . $index);
                    /* add inline css for custom position */

                    if($position != "custom") {
                        $positionSide = $position;
                        $cht_bottom_spacing = 25;
                        $cht_side_spacing = 25;
                    } else {
                        $position = $positionSide;
                    }

                    $animation_class = get_option("chaty_attention_effect" . $index);
                    $animation_class = empty($animation_class) ? "" : $animation_class;

                    $time_trigger = get_option("chaty_trigger_on_time" . $index);
                    $time_trigger = empty($time_trigger) ? "no" : $time_trigger;

                    $trigger_time = get_option("chaty_trigger_time" . $index);
                    $trigger_time = (empty($trigger_time) || !is_numeric($trigger_time) || $trigger_time < 0) ? "0" : $trigger_time;

                    $exit_intent = get_option("chaty_trigger_on_exit" . $index);
                    $exit_intent = empty($exit_intent) ? "no" : $exit_intent;

                    $on_page_scroll = get_option("chaty_trigger_on_scroll" . $index);
                    $on_page_scroll = empty($on_page_scroll) ? "no" : $on_page_scroll;

                    $page_scroll = get_option("chaty_trigger_on_page_scroll" . $index);
                    $page_scroll = (empty($page_scroll) || !is_numeric($page_scroll) || $page_scroll < 0) ? "0" : $page_scroll;

                    $state = get_option("chaty_default_state" . $index);
                    $state = empty($state) ? "click" : $state;

                    $mode = get_option("chaty_icons_view" . $index);
                    $mode = empty($mode) ? "vertical" : $mode;

                    $has_close_button = get_option("cht_close_button" . $index);
                    $has_close_button = empty($has_close_button) ? "yes" : $has_close_button;

                    $countries = get_option("chaty_countries_list" . $index);
                    $countries = ($countries === false || empty($countries) || !is_array($countries)) ? array() : $countries;
                    if(count($countries) == 240) {
                        $countries = array();
                    }

                    $display_days = get_option("cht_date_and_time_settings" . $index);
                    $display_rules = array();

                    $gmt = "";
                    if (!empty($display_days)) {
                        $count = 0;
                        foreach ($display_days as $key => $value) {
                            if ($count == 0) {
                                $gmt = $value['gmt'];

                                /* Set GMT according to timestamp*/
                                if(!is_numeric($gmt)) {
                                    date_default_timezone_set("UTC");
                                    $date = date("Y-m-d h:i:s");
                                    $current_utc_time = strtotime($date);

                                    $time_zone = str_replace('UTC','',$gmt);
                                    date_default_timezone_set($time_zone);
                                    $gmt_time = strtotime($date);

                                    $difference = $current_utc_time - $gmt_time;

                                    $gmt = ($difference / (60*60));
                                }
                                $count++;
                            }
                            if($value['end_time'] == "00:00") {
                                $value['end_time'] = "23:59:59";
                            }
                            $start_time = $value['start_time'];
                            $end_time = $value['end_time'];
                            $start_time = date("H:i", strtotime(date("Y-m-d " . $start_time)));
                            $end_time = date("H:i", strtotime(date("Y-m-d " . $end_time)));
                            if($end_time >= $start_time) {
                                $record = array();
                                $record['days'] = $value['days'] - 1;
                                $record['start_time'] = $value['start_time'];
                                $record['start_hours'] = intval(date("G", strtotime(date("Y-m-d " . $value['start_time']))));
                                $record['start_min'] = intval(date("i", strtotime(date("Y-m-d " . $value['start_time']))));
                                $record['end_time'] = $value['end_time'];
                                $record['end_hours'] = intval(date("G", strtotime(date("Y-m-d " . $value['end_time']))));
                                $record['end_min'] = intval(date("i", strtotime(date("Y-m-d " . $value['end_time']))));
                                $display_rules[] = $record;
                            } else {
                                $record = array();
                                $record['days'] = $value['days'] - 1;
                                $record['start_time'] = $value['start_time'];
                                $record['start_hours'] = intval(date("G", strtotime(date("Y-m-d " . $value['start_time']))));
                                $record['start_min'] = intval(date("i", strtotime(date("Y-m-d " . $value['start_time']))));
                                $record['end_time'] = "23:59";
                                $record['end_hours'] = 23;
                                $record['end_min'] = 59;
                                $display_rules[] = $record;
                                $record = array();
                                if($value['days'] >= 1 && $value['days'] <= 6) {
                                    $value['days'] = $value['days'] + 1;
                                } else if($value['days'] == 7) {
                                    $value['days'] = 1;
                                }
                                $record['days'] = $value['days'] - 1;
                                $record['start_time'] = "00:00";
                                $record['start_hours'] = intval(date("G", strtotime(date("Y-m-d 00:00"))));
                                $record['start_min'] = intval(date("i", strtotime(date("Y-m-d 00:00"))));
                                $record['end_time'] = $value['end_time'];
                                $record['end_hours'] = intval(date("G", strtotime(date("Y-m-d " . $value['end_time']))));
                                $record['end_min'] = intval(date("i", strtotime(date("Y-m-d " . $value['end_time']))));
                                $display_rules[] = $record;
                            }
                        }
                    }
                    $display_conditions = 0;
                    if (!empty($display_rules)) {
                        $display_conditions = 1;
                    }

                    /* checking for date and time */
                    $cht_date_rules = get_option("cht_date_rules".$index);
                    $date_status = 0;
                    $start_date = "";
                    $end_date = "";
                    $time_diff = 0;
                    if(isset($cht_date_rules['status']) && $cht_date_rules['status'] == "yes") {
                        $start_date = isset($cht_date_rules['start_date'])?$cht_date_rules['start_date']:"";
                        $end_date = isset($cht_date_rules['end_date'])?$cht_date_rules['end_date']:"";
                        $start_time = isset($cht_date_rules['start_time'])?$cht_date_rules['start_time']:"";
                        $end_time = isset($cht_date_rules['end_time'])?$cht_date_rules['end_time']:"";
                        if(!empty($start_date)) {
                            $start_date = $this->getYMDDate($start_date);
                            if(!empty($start_time)) {
                                $start_date = $start_date." ".$start_time.":00";
                            } else {
                                $start_date = $start_date." 00:00:00";
                            }
                        }
                        if(!empty($end_date)) {
                            $end_date = $this->getYMDDate($end_date);
                            if(!empty($end_time)) {
                                $end_date = $end_date." ".$end_time.":00";
                            } else {
                                $end_date = $end_date." 23:59:59";
                            }
                        }
                        if(!empty($start_date) || !empty($end_date)) {
                            $date_status = 1;
                            if(isset($cht_date_rules['timezone']) && !empty($cht_date_rules['timezone'])) {
                                date_default_timezone_set("UTC");
                                $date = date("Y-m-d h:i:s");
                                $current_utc_time = strtotime($date);

                                $time_zone = str_replace('UTC','',$cht_date_rules['timezone']);
                                date_default_timezone_set($time_zone);
                                $gmt_time = strtotime($date);

                                $difference = $current_utc_time - $gmt_time;

                                $time_diff = ($difference / (60*60));
                            }
//                            if(!empty($start_date)) {
//                                $start_date = strtotime($start_date);
//                            }
//                            if(!empty($end_date)) {
//                                $end_date = strtotime($end_date);
//                            }
                        }
                    }


                    $custom_css = get_option('chaty_custom_css' . $index);
                    $custom_css = trim(preg_replace('/\s\s+/', '', $custom_css));

                    $pending_messages = get_option("cht_pending_messages".$index);
                    $pending_messages = ($pending_messages === false)?"off":$pending_messages;

                    $click_setting = get_option("cht_cta_action".$index);
                    $click_setting = ($click_setting === false)?"click":$click_setting;

                    $cht_number_of_messages = get_option("cht_number_of_messages".$index);
                    $cht_number_of_messages = ($cht_number_of_messages === false)?0:$cht_number_of_messages;

                    $number_color = get_option("cht_number_color".$index);
                    $number_color = ($number_color === false)?"#ffffff":$number_color;

                    $number_bg_color = get_option("cht_number_bg_color".$index);
                    $number_bg_color = ($number_bg_color === false)?"#dd0000":$number_bg_color;

                    $cht_cta_text_color = get_option("cht_cta_text_color".$index);
                    $cht_cta_text_color = ($cht_cta_text_color === false)?"#333333":$cht_cta_text_color;

                    $cht_cta_bg_color = get_option("cht_cta_bg_color".$index);
                    $cht_cta_bg_color = ($cht_cta_bg_color === false)?"#ffffff":$cht_cta_bg_color;

                    if(empty($cht_number_of_messages)) {
                        $pending_messages = "off";
                    }

                    if(empty($bg_color)) {
                        $bg_color = '#A886CD';
                    }
                    $bg_color = strtolower($bg_color);
                    if(strpos($bg_color, "#") === false && strpos($bg_color, "rgb") === false) {
                        $bg_color = "#".$bg_color;
                    }

                    /* widget setting array */
                    $settings = array();

                    /* date settings */
                    $settings['has_date_setting'] = $date_status;
                    $settings['date_utc_diff'] = $time_diff;
                    $settings['chaty_start_time'] = $start_date;
                    $settings['chaty_end_time'] = $end_date;

                    $settings['isPRO'] = $isPro;
                    $settings['cht_cta_text_color'] = $cht_cta_text_color;
                    $settings['cht_cta_bg_color'] = $cht_cta_bg_color;
                    $settings['click_setting'] = $click_setting;
                    $settings['pending_messages'] = $pending_messages;
                    $settings['number_of_messages'] = $cht_number_of_messages;
                    $settings['number_bg_color'] = $number_bg_color;
                    $settings['number_color'] = $number_color;
                    $settings['position'] = $position;
                    $settings['pos_side'] = $positionSide;
                    $settings['bot'] = $cht_bottom_spacing;
                    $settings['side'] = $cht_side_spacing;
                    $settings['device'] = $this->device();
                    $settings['color'] = $bg_color;
                    $settings['rgb_color'] = $this->getRGBColor($bg_color);
                    $settings['widget_size'] = $cht_widget_size;
                    $settings['widget_type'] = get_option('widget_icon' . $index);
                    $settings['custom_css'] = $custom_css;
                    $settings['widget_img'] = $this->getCustomWidgetImg($index);
                    $settings['cta'] = html_entity_decode($cta);
                    $settings['active'] = ($cht_active && $len >= 1) ? 'true' : 'false';
                    $settings['close_text'] = $close_text;
                    $settings['analytics'] = $analytics;

                    $settings['save_user_clicks'] = 0;
                    $settings['close_img'] = "";
                    $settings['is_mobile'] = (wp_is_mobile()) ? 1 : 0;
                    $settings['ajax_url'] = admin_url('admin-ajax.php');
                    $settings['animation_class'] = $animation_class;
                    $settings['time_trigger'] = $time_trigger;
                    $settings['trigger_time'] = $trigger_time;
                    $settings['exit_intent'] = $exit_intent;
                    $settings['on_page_scroll'] = $on_page_scroll;
                    $settings['page_scroll'] = $page_scroll;
                    $settings['gmt'] = $gmt;
                    $settings['display_conditions'] = $display_conditions;
                    $settings['display_rules'] = $display_rules;
                    $settings['display_state'] = $state;
                    $settings['has_close_button'] = $has_close_button;
                    $settings['countries'] = $countries;
                    $settings['widget_index'] = $index;
                    $settings['widget_nonce'] = wp_create_nonce("chaty_widget_nonce".$index);
                    $settings['mode'] = $mode;
                    $settings['social'] = $social;

                    $inline_css = "";
                    if ($position == "left") {
                        $inline_css .= ".chaty-widget-css{$index} #wechat-qr-code{left: " . esc_attr($total) . "px; right:auto;}";
                    } else if ($position == "right") {
                        $inline_css .= ".chaty-widget-css{$index} #wechat-qr-code{right: " . esc_attr($total) . "px; left:auto;}";
                    } else if ($position == "custom") {
                    } else if ($position == "custom") {
                        if ($positionSide == "left") {
                            $inline_css .= ".chaty-widget-css{$index} #wechat-qr-code{left: " . esc_attr($total) . "px; right:auto;}";
                        } else {
                            $inline_css .= ".chaty-widget-css{$index} #wechat-qr-code{right: " . esc_attr($total) . "px; left:auto;}";
                        }
                    }

                    $inline_css .= ".chaty-widget-css{$index} .chaty-widget, .chaty-widget-css{$index} .chaty-widget .get, .chaty-widget-css{$index} .chaty-widget .get a { width: " . esc_attr($cht_widget_size + 8) . "px; }";
                    $inline_css .= ".chaty-widget-css{$index} .facustom-icon { width: " . esc_attr($cht_widget_size) . "px; line-height: " . esc_attr($cht_widget_size) . "px; height: " . esc_attr($cht_widget_size) . "px; font-size: " . esc_attr(intval($cht_widget_size / 2)) . "px; }";
                    $inline_css .= ".chaty-widget-css{$index} img { width: " . esc_attr($cht_widget_size) . "px; line-height: " . esc_attr($cht_widget_size) . "px; height: " . esc_attr($cht_widget_size) . "px; object-fit: cover; }";

                    $inline_css .= ".chaty-widget-css{$index} .i-trigger .chaty-widget-i-title {color:".esc_attr($cht_cta_text_color)." !important; background:".esc_attr($cht_cta_bg_color)." !important;}";
//                    $inline_css .= ".chaty-widget-css{$index} .i-trigger .chaty-widget-i-title p {color:".esc_attr($cht_cta_text_color)." !important; background:".esc_attr($cht_cta_bg_color)." !important;}";
                    $inline_css .= ".chaty-widget-css{$index} .i-trigger .chaty-widget-i-title p {color:".esc_attr($cht_cta_text_color)." !important; }";
                    $inline_css .= ".chaty-widget-css{$index} .i-trigger .chaty-widget-i:not(.no-tooltip):before { border-color: transparent transparent transparent ".esc_attr($cht_cta_bg_color)." !important;}";
                    $inline_css .= ".chaty-widget-css{$index}.chaty-widget.chaty-widget-is-right .i-trigger .chaty-widget-i:not(.no-tooltip):before { border-color: transparent ".esc_attr($cht_cta_bg_color)." transparent transparent !important;}";

                    if($font_family == "System Stack") {
                        $font_family = "-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif";
                    }
                    if (!empty($font_family)) {
                        $inline_css .= ".chaty-widget-css{$index}.chaty-widget {font-family:".esc_attr($font_family) . "; }";
                    }
                    foreach ($settings['social'] as $social) {
                        if (!empty($social['bg_color']) && $social['bg_color'] != "#ffffff") {
                            $inline_css .= ".chaty-widget-css{$index} .facustom-icon.chaty-btn-" . esc_attr($social['social_channel']) . " {background-color: " . esc_attr($social['bg_color']) . "}";
                            $inline_css .= ".chaty-widget-css{$index} .chaty-" . esc_attr($social['social_channel']) . " .color-element {fill: " . esc_attr($social['bg_color']) . "}";
                            $inline_css .= ".chaty-widget-css{$index} .chaty-" . esc_attr($social['social_channel']) . " a {background: " . esc_attr($social['bg_color']) . "}";
                        }
                    }

                    $this->inline_css .= $inline_css;

                    if(in_array($font_family, array("Arial", "Tahoma", "Verdana", "Helvetica", "Times New Roman", "Trebuchet MS", "Georgia", "System Stack", "-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif"))) {
                        $font_family = "";
                    }
                    $settings['font_family'] = $font_family;
                    $this->widget_settings[] = $settings;
                }
            }
        }
    }

    public function getYMDDate($date) {
        $date = explode("/", $date);
        $month = isset($date[0])?$date[0]:"00";
        $month_date = isset($date[1])?$date[1]:"00";
        $year = isset($date[2])?$date[2]:"0000";
        return $year."-".$month."-".$month_date;
    }

    public function getVisitorTrafficSource($index = "") {

        $traffic_source = get_option("chaty_traffic_source".$index);
        if($traffic_source === false || $traffic_source != "yes") {
            return true;
        }

        $origin_landing_page	= '';
        $HTTP_REFERER 			= ( isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
        if ( isset( $_COOKIE['CHATY_HTTP_REFERER']) && $_COOKIE['CHATY_HTTP_REFERER'] != '' ) {
            $HTTP_REFERER = $_COOKIE['CHATY_HTTP_REFERER'];
        }
        if ( $HTTP_REFERER != '' ) {
            @setcookie('CHATY_HTTP_REFERER', $HTTP_REFERER, time() + (86400 * 30), "/"); // 86400 = 1 day
        }

        $chaty_traffic_source = get_option("chaty_traffic_source".$index);
        if($chaty_traffic_source == "yes") {
            $direct_visit = get_option("chaty_traffic_source_direct_visit".$index);
            $social_network = get_option("chaty_traffic_source_social_network".$index);
            $search_engines = get_option("chaty_traffic_source_search_engine".$index);
            $google_ads = get_option("chaty_traffic_source_google_ads".$index);
            $other_source_url = get_option("chaty_custom_traffic_rules".$index);
            $other_source_url = !is_array($other_source_url)?array():$other_source_url;
            $url_setting = array();
            foreach ($other_source_url as $setting) {
                if (!empty($setting['url_value'])) {
                    $url_setting[] = $setting;
                }
            }

            if($direct_visit != "yes" && $social_network != "yes" && $search_engines != "yes" && $google_ads != "yes" && empty($url_setting)) {
                return "no-rule";
            }

            if ( isset($_COOKIE['chaty_traffic_source-'. $index]) &&  $_COOKIE['chaty_traffic_source-'. $index] != '' ) {
                return $_COOKIE['chaty_traffic_source-'. $index];
            }

            $coupon_traffic_source = $this->trafficSource();

            $response = false;
            $visitor_referel = ( (isset($HTTP_REFERER) && $HTTP_REFERER !='' ) ? parse_url($HTTP_REFERER)['host'] : '' );

            if ( ( ( empty($visitor_referel) || $_SERVER['HTTP_HOST'] == $visitor_referel || (isset($_SERVER['HTTP_ORIGIN']) && (parse_url($_SERVER['HTTP_ORIGIN'])['host'] == $visitor_referel )) ) ) &&  $direct_visit == "yes" ){
                $response = "direct_link";
            }

            if (!$response && $search_engines == "yes" ) {
                foreach($coupon_traffic_source['search_engine'] as $source){
                    if ( (strpos($visitor_referel, $source) !== false) ) {
                        if ( $source == "google." && strpos($visitor_referel,"plus.google" ) !== false  ){
                            break;
                        }else{
                            $response = "search_engine";
                            break;
                        }
                    }
                }
            }

            // if social_media
            if (!$response && $social_network == "yes"){
                foreach($coupon_traffic_source['social_media'] as $source){
                    if ( strpos($visitor_referel, $source) !== false ) {
                        $response = "social_media";
                        break;
                    }
                }
            }

            // if google_ads
            if ( $google_ads == "yes" && !$response &&  isset($origin_landing_page) && !empty($origin_landing_page) ){
                if ((strpos($origin_landing_page, 'gclid=') !== false)){
                    $response = "google_ads";
                }
            }

            if (!empty($url_setting) && !$response) {
                $flag =  $this->checkSpecifixUrlInRolesTrafficSource($index);
                if ( $flag ){
                    $response = "specific_url";
                }else{
                    $response = false;
                }
            }
        } else {
            $response = "no-rule";
        }
        return $response;
    }

    function checkSpecifixUrlInRolesTrafficSource($index) {
        $flag = true;
        $flag_array = array();
        $contain_flag_array = array();
        $not_contain_flag_array = array();

        $HTTP_REFERER 			= ( isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
        if ( isset( $_COOKIE['CHATY_HTTP_REFERER']) && $_COOKIE['CHATY_HTTP_REFERER'] != '' ) {
            $HTTP_REFERER = $_COOKIE['CHATY_HTTP_REFERER'];
        }

        $referer = (isset($HTTP_REFERER) ? parse_url($HTTP_REFERER) : 'empty' );

        if ($referer == 'empty' || !isset($referer['host'])){
            return true;
        }
        $referer_host =  $this->removeWWW($referer['host']);
        $query = (isset($referer['query']) && !empty($referer['query']) ? '?'.$referer['query']:'');
        $referer_path =  $referer['path'].$query;
        $referer_path =  strtolower(str_replace("/", "%2f", $referer_path));
        $contain_array = array();
        $not_contain_array = array();
        $url_settings = get_option("chaty_custom_traffic_rules".$index);
        foreach ($url_settings as $setting) {
            if (!empty($setting['url_value'])) {
                if($setting['url_option'] == "contain") {
                    $contain_array[] = array($setting['url_option'], $setting['url_value']);
                } else {
                    $not_contain_array[] = array($setting['url_option'], $setting['url_value']);
                }
            }
        }

        if(empty($contain_array) && empty($not_contain_array)) {
            return true;
        }

//        echo "<pre>"; print_r($contain_array); die;

        if ( !empty($contain_array) ) {
            foreach($contain_array as $key=>$value){
                $role_link = parse_url($value[1]);
                $role_host = $this->removeWWW( $role_link['host'] );
                $role_path = '';
                if(isset($role_link['path'])){
                    $role_path =  $role_link['path'];
                }else{
                    $role_path = '';
                }
                if(isset($role_link['query'])){
                    $role_path .=  '?'.$role_link['query'];
                }

                $role_path = (preg_match("/\p{Hebrew}/u", $role_path) ? $role_path : str_replace("/","%2f",$role_path));
                $role_path = strtolower(str_replace("&amp;","&",$role_path));
                $role_path = trim($role_path);
                if ($role_path == ''){
                    $role_path = '/';
                }
                if ($referer_path == ''){
                    $referer_path = '/';
                }
                if ($role_host != $referer_host){

                    $flag = false;
                }else if(empty($role_path) && empty($referer_path)){

                    $flag = true;
                }else if(strtolower(urlencode($role_path)) == strtolower($referer_path) && strtolower($referer_path)=='%2f'){

                    $flag = true;
                }else{
                    switch($value[0]){
                        case 'contain':
                            if (empty($role_path) && !empty($referer_path)){
                                $flag = true;
                            }else if ($role_path == "/" || $role_path=="%2f") {
                                $flag = true;
                            }else if (strpos($referer_path,( preg_match("/\p{Hebrew}/u", $role_path) ? strtolower(urlencode($role_path)) : strtolower($role_path) )) !== false){
                                $flag = true;
                            }else if (strpos($referer_path.'/',( preg_match("/\p{Hebrew}/u", $role_path) ? strtolower(urlencode($role_path)) : strtolower($role_path) )) !== false){
                                $flag = true;
                            }else if (strpos($referer_path.'%2f',( preg_match("/\p{Hebrew}/u", $role_path) ? strtolower(urlencode($role_path)) : strtolower($role_path) )) !== false){
                                $flag = true;
                            }else{
                                $flag = false;
                            }
                            break;
                    }
                    $and = $flag;
                }
                $flag_array[] = $flag;
                $contain_flag_array[] = $flag;
            }
        }

        if ( !empty( $not_contain_array ) ) {
            foreach($not_contain_array as $key=>$value){
                $role_link = parse_url($value[1]);

                $role_host = $this->removeWWW( $role_link['host'] );

                $role_path = '';
                if(isset($role_link['path'])){
                    $role_path =  $role_link['path'];
                }else{
                    $role_path = '';
                }
                if(isset($role_link['query'])){
                    $role_path .=  '?'.$role_link['query'];
                }
                $role_path = (preg_match("/\p{Hebrew}/u", $role_path) ? $role_path : str_replace("/","%2f",$role_path));
                $role_path = str_replace("&amp;","&",$role_path);
                $role_path = trim($role_path);
                if ($role_path == ''){
                    $role_path = '/';
                }
                if ($referer_path == ''){
                    $referer_path = '/';
                }

                if ($role_host == $referer_host && (empty($role_path) || $role_path=="%2f") && (empty($referer_path) || $referer_path=="%2f")){
                    $flag = false;
                } else{
                    switch($value[0]){
                        case 'not_contain':
                            if (isset($referer_path) && strpos(strtolower($referer_path),((preg_match("/\p{Hebrew}/u", $role_path)) ? strtolower(urlencode($role_path)) : strtolower($role_path)))!== false){
                                $flag = false;
                            }else if ($role_path == "/" || $role_path=="%2f"){
                                $flag = false;
                            }else{
                                $flag = true;
                            }
                            break;
                    }
                }
                $flag_array[] = $flag;
                $not_contain_flag_array[] = $flag;
            }
        }

        if (!empty($not_contain_array) && empty($contain_array)){
            return (in_array(false, $not_contain_flag_array) ? false : true );
        }else if (!empty($not_contain_array) && !empty($contain_array)){
            if (in_array(false, $not_contain_flag_array)){
                return false;
            }else{
                return (in_array(true, $contain_flag_array) ? true : false );
            }
        }else if (empty($not_contain_array) && !empty($contain_array)){
            return (in_array(true, $contain_flag_array) ? true : false );
        }else{
            return $flag;
        }
    }

    function removeWWW( $url ) {
        return str_replace('www.','',$url );
    }

    public function trafficSource() {
        $traffic_source = [
            "search_engine" => array(
                'accoona',
                'ansearch',
                'biglobe',
                'daum',
                'egerin	',
                'leit.is',
                'maktoob',
                'miner.hu',
                'najdi.si',
                'najdi.org',
                'naver',
                'rambler',
                'rediff',
                'sapo',
                'search.ch',
                'sesam',
                'seznam',
                'walla',
                'zipLoca',
                'slurp',
                'search.msn.com',
                'nutch',
                'simpy',
                'bot.',
                'aspSeek',
                'crawler.',
                'msnbot',
                'libwww-perl',
                'fast',
                'baidu.',
                'bing.',
                'google.',
                'duckduckgo',
                'ecosia',
                'exalead',
                'giablast',
                'munax',
                'qwant',
                'sogou',
                'soso',
                'yahoo.',
                'yandex.',
                'youdao',
                'aol.',
                'hotbot.',
                'webcrawler.',
                'eniro',
                'naver',
                'lycos',
                'ask',
                'altavista',
                'netscape',
                'about',
                'mamma',
                'alltheweb',
                'voila',
                'live',
                'alice',
                'mama',
                'wp.pl',
                'onecenter',
                'szukacz',
                'yam',
                'kvasir',
                'ozu',
                'terra',
                'pchome',
                'mynet',
                'ekolay',
                'rembler',
            ),
            "social_media" => array(
                "facebook.",
                "instagram.",
                "linkedin.",
                "myspace.",
                "twitter.",
                "t.co",
                "plus.google",
                "disqus.",
                "snapchat.",
                "tumbler.",
                "pinterest.",
                "twoo",
                "mymfb",
                "youtube.",
                "vine",
                "whatsapp",
                "vk.com",
                "secret",
                "medium",
                "bebo",
                "friendster",
                "hi5",
                "habbo",
                "ning",
                "classmates",
                "tagged",
                "myyearbook",
                "meetup",
                "mylife",
                "reunion",
                "flixster",
                "myheritage",
                "multiply",
                "orkut",
                "badoo",
                "gaiaonline",
                "blackplanet",
                "skyrock",
                "perfspot",
                "zorpia",
                "netlog",
                "tuenti",
                "nasza-klasa.pl",
                "irc-gallery",
                "studivz",
                "xing",
                "renren",
                "kaixin001",
                "hyves.nl",
                "MillatFacebook",
                "ibibo",
                "sonico",
                "wer-kennt-wen",
                "cyworld",
                "iwiw",
                "dribbble.",
                "stumbleupon.",
                "flickr.",
                "plaxo.",
                "digg.",
                "del.icio.us"
            ),
        ];
        return $traffic_source;
    }

    /* returns for widget is active or not */
    private function canInsertWidget() {

        $flag = false;
        $status = get_option('cht_active') && $this->checkChannels() && $this->check_for_url();
        $is_deleted = get_option("cht_is_default_deleted");
        if($status && $is_deleted === false) {
            $this->get_widget_settings();
            $flag = true;
        }

        $deleted_list = get_option("chaty_deleted_settings");
        if (empty($deleted_list) || !is_array($deleted_list)) {
            $deleted_list = array();
        }

        $chaty_widgets = get_option("chaty_total_settings");
        if (!empty($chaty_widgets) && $chaty_widgets != null && is_numeric($chaty_widgets) && $chaty_widgets > 0) {
            for ($i = 1; $i <= $chaty_widgets; $i++) {
                if (!in_array($i, $deleted_list)) {
                    $this->widget_number = "_".$i;
                    $status = get_option('cht_active_'.$i) && $this->checkChannels() && $this->check_for_url();
                    if($status) {
                        $this->get_widget_settings("_".$i);
                        $flag = true;
                    }
                }
            }
        }
        return $flag;
    }

    /* checking for social channels */
    private function checkChannels() {
        $social = explode(",", get_option('cht_numb_slug'.$this->widget_number));
        $res = false;
        foreach ($social as $name) {
            $value = get_option('cht_social'.$this->widget_number.'_' . strtolower($name));
            $res = $res || !empty($value['value']) || ($name == "Contact_Us");
        }
        return $res;
    }
}

return new CHT_PRO_Frontend();
