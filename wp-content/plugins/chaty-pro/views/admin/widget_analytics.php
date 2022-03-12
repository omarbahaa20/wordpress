<?php
if (!defined('ABSPATH')) { exit; }
?>
<h1></h1>
<div class="widget-analytics">
    <h2>Widget Analytics</h2>
    <div class="date-section">
        <?php
        $search_list = array(
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'last_30_days' => 'Last 30 Days',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'all_time' => 'All Time',
            'custom' => 'Custom Date'
        );

        $search_for = "all_time";
        if(isset($_GET['search_for']) && !empty($_GET['search_for']) && isset($search_list[$_GET['search_for']])) {
            $search_for = $_GET['search_for'];
        }
        if($search_for == "today") {
            $start_date = date("m/d/Y");
            $end_date = date("m/d/Y");
        } else if($search_for == "yesterday") {
            $start_date = date("m/d/Y", strtotime("-1 days"));
            $end_date = date("m/d/Y", strtotime("-1 days"));
        } else if($search_for == "last_7_days") {
            $start_date = date("m/d/Y", strtotime("-7 days"));
            $end_date = date("m/d/Y");
        } else if($search_for == "last_30_days") {
            $start_date = date("m/d/Y", strtotime("-30 days"));
            $end_date = date("m/d/Y");
        } else if($search_for == "this_week") {
            $start_date = date("m/d/Y", strtotime('monday this week'));
            $end_date = date("m/d/Y");
        } else if($search_for == "this_month") {
            $start_date = date("m/01/Y");
            $end_date = date("m/d/Y");
        } else if($search_for == "custom") {
            if(isset($_GET['start_date']) && !empty($_GET['start_date'])) {
                $start_date = $_GET['start_date'];
            }
            if(isset($_GET['end_date']) && !empty($_GET['end_date'])) {
                $end_date = $_GET['end_date'];
            }
        } else if($search_for == "all_time") {
            $start_date = "";
            $end_date = "";
        }
        ?>
        <form action="<?php echo admin_url("admin.php") ?>" method="get" id="analytics_form">
            <input type="hidden" name="page" value="widget-analytics"/>
            <div class="custom-search-box">
                <select name="search_for" id="search_for">
                    <?php foreach($search_list as $key=>$value) { ?>
                        <option <?php selected($key, $search_for) ?> value="<?php echo $key ?>"><?php echo $value ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="date-options" id="date_options" style="display: <?php echo ($search_for == "custom")?"block":"none" ?>">
                <div class="date-option">
                    <label for="start_date">Start Date</label>
                    <input type="text" name="start_date" id="start_date" readonly value="<?php echo $start_date ?>" />
                </div>
                <div class="date-option">
                    <label for="end_date">End Date</label>
                    <input type="text" name="end_date" id="end_date" readonly value="<?php echo $end_date ?>" />
                </div>
                <div class="date-option">
                    <button type="submit" class="button search-btn">Search</button>
                </div>
            </div>
        </form>
    </div>
    <div class="analytics-records">
        <?php if(!empty($start_date) && !empty($end_date)) {
            $res_string = ($start_date == $end_date) ? "for <b>" . $end_date . "</b>" : "from <b>" . $start_date . "</b> to <b>" . $end_date . "</b>";
        } elseif(empty($start_date) && empty($end_date)) {
            $res_string = "for <b>All time</b>";
        } ?>
        <div class="result-data">
            Showing results <?php echo $res_string ?>
        </div>
        <?php
        if(!empty($start_date) && !empty($end_date)) {
            $start_date = strtotime($start_date);
            $end_date = strtotime($end_date);
        } else {
            $start_date = "";
            $end_date = "";
        }
        $records = get_analytics_records($start_date, $end_date);
        $i = 0;
        $i_count = 0;
        if(!empty($records)) {
            foreach($records as $record) {
                $channel_record = array();
                ?>
                <?php
                $i_count++;
                $widget_id = $record['id'];
                if(!empty($widget_id)) {
                    $widget_id = "_".$widget_id;
                }
                $widget_name = get_option("cht_widget_title".$widget_id);
                if(empty($widget_name)) {
                    if(!empty($widget_id)) {
                        $widget_name = "Widget #".(trim($widget_id, "_") + 1);
                    } else {
                        $widget_name = "Widget #1";
                    }
                }
                $state = get_option("chaty_default_state".$widget_id);
                $button = get_option("cht_close_button".$widget_id);
                $has_icon = true;
                if($state == "open" && $button == "no") {
                    $has_icon = false;
                }
                $has_image = false;
                $image_url = "";
                if($has_icon) {
                    $social = get_option('cht_numb_slug'.$widget_id);
                    $social = trim($social,",");
                    if(!empty($social)) {
                        $social = explode(",", $social);
                        if(count($social) == 1) {
                            $channel = $social[0];
                            $def_channel = $socials[strtolower($channel)];
                            $title = $def_channel['title'];
                            $color = $def_channel['color'];
                            $icon = $def_channel['svg'];
                            $slug = $def_channel['slug'];
                            $settings = get_option('cht_social' . $widget_id . '_' . $slug);
                            $color = isset($settings['bg_color'])?$settings['bg_color']:$color;
                            $title = isset($settings['title'])?$settings['title']:$title;
                            $image_id = isset($settings['image_id'])?$settings['image_id']:"";

                            if(!empty($image_id)) {
                                $image_url = wp_get_attachment_url($image_id);
                                if(!empty($image_url)) {
                                    $has_image = true;
                                }
                            }
                            if(!empty($color)) {
                                if(strtolower($channel) != 'instagram' || $color != '#ffffff') {
                                    echo "<style>.svg-main-icon-{$i_count} svg circle {fill: $color !important;}</style>";
                                }
                            }
                        } else {
                            $icon = get_option("widget_icon".$widget_id);
                            if($icon == "chat-image") {
                                $image_data = get_option("cht_widget_img".$widget_id);
                                if(isset($image_data['url']) && !empty($image_data['url'])) {
                                    $image_url = $image_data['url'];
                                    $has_image = true;
                                }
                            }
                            if($icon == "chat-base") {
                                $icon = '<svg version="1.1" id="ch" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="-496 507.7 54 54" style="enable-background:new -496 507.7 54 54;" xml:space="preserve"> <style type="text/css">.st1 { fill: #FFFFFF; } .st0 { fill: #808080; } </style> <g><circle cx="-469" cy="534.7" r="27" fill="#a886cd"/></g><path class="st1" d="M-459.9,523.7h-20.3c-1.9,0-3.4,1.5-3.4,3.4v15.3c0,1.9,1.5,3.4,3.4,3.4h11.4l5.9,4.9c0.2,0.2,0.3,0.2,0.5,0.2 h0.3c0.3-0.2,0.5-0.5,0.5-0.8v-4.2h1.7c1.9,0,3.4-1.5,3.4-3.4v-15.3C-456.5,525.2-458,523.7-459.9,523.7z"/><path class="st0" d="M-477.7,530.5h11.9c0.5,0,0.8,0.4,0.8,0.8l0,0c0,0.5-0.4,0.8-0.8,0.8h-11.9c-0.5,0-0.8-0.4-0.8-0.8l0,0C-478.6,530.8-478.2,530.5-477.7,530.5z"/><path class="st0" d="M-477.7,533.5h7.9c0.5,0,0.8,0.4,0.8,0.8l0,0c0,0.5-0.4,0.8-0.8,0.8h-7.9c-0.5,0-0.8-0.4-0.8-0.8l0,0C-478.6,533.9-478.2,533.5-477.7,533.5z"/></svg>';
                            } else if($icon == "chat-smile") {
                                $icon = '<svg version="1.1" id="smile" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="-496.8 507.1 54 54" style="enable-background:new -496.8 507.1 54 54;" xml:space="preserve"><style type="text/css">.st1 { fill: #FFFFFF; }.st2 { fill: none; stroke: #808080; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }</style><g><circle cx="-469.8" cy="534.1" r="27" fill="#a886cd"/></g><path class="st1" d="M-459.5,523.5H-482c-2.1,0-3.7,1.7-3.7,3.7v13.1c0,2.1,1.7,3.7,3.7,3.7h19.3l5.4,5.4c0.2,0.2,0.4,0.2,0.7,0.2c0.2,0,0.2,0,0.4,0c0.4-0.2,0.6-0.6,0.6-0.9v-21.5C-455.8,525.2-457.5,523.5-459.5,523.5z"/><path class="st2" d="M-476.5,537.3c2.5,1.1,8.5,2.1,13-2.7"/><path class="st2" d="M-460.8,534.5c-0.1-1.2-0.8-3.4-3.3-2.8"/></svg>';
                            } else if($icon == "chat-bubble") {
                                $icon = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="-496.9 507.1 54 54" style="enable-background:new -496.9 507.1 54 54;" xml:space="preserve"><style type="text/css">.st1 { fill: #FFFFFF; }</style><g><circle cx="-469.9" cy="534.1" r="27" fill="#a886cd"/></g><path class="st1" d="M-472.6,522.1h5.3c3,0,6,1.2,8.1,3.4c2.1,2.1,3.4,5.1,3.4,8.1c0,6-4.6,11-10.6,11.5v4.4c0,0.4-0.2,0.7-0.5,0.9 c-0.2,0-0.2,0-0.4,0c-0.2,0-0.5-0.2-0.7-0.4l-4.6-5c-3,0-6-1.2-8.1-3.4s-3.4-5.1-3.4-8.1C-484.1,527.2-478.9,522.1-472.6,522.1z M-462.9,535.3c1.1,0,1.8-0.7,1.8-1.8c0-1.1-0.7-1.8-1.8-1.8c-1.1,0-1.8,0.7-1.8,1.8C-464.6,534.6-463.9,535.3-462.9,535.3z M-469.9,535.3c1.1,0,1.8-0.7,1.8-1.8c0-1.1-0.7-1.8-1.8-1.8c-1.1,0-1.8,0.7-1.8,1.8C-471.7,534.6-471,535.3-469.9,535.3z M-477,535.3c1.1,0,1.8-0.7,1.8-1.8c0-1.1-0.7-1.8-1.8-1.8c-1.1,0-1.8,0.7-1.8,1.8C-478.8,534.6-478.1,535.3-477,535.3z"/>';
                            } else if($icon == "chat-db") {
                                $icon = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="-496 507.1 54 54" style="enable-background:new -496 507.1 54 54;" xml:space="preserve"><style type="text/css">.st1 {fill: #FFFFFF;}</style><g><circle cx="-469" cy="534.1" r="27" fill="#a886cd"/></g><path class="st1" d="M-464.6,527.7h-15.6c-1.9,0-3.5,1.6-3.5,3.5v10.4c0,1.9,1.6,3.5,3.5,3.5h12.6l5,5c0.2,0.2,0.3,0.2,0.7,0.2 c0.2,0,0.2,0,0.3,0c0.3-0.2,0.5-0.5,0.5-0.9v-18.2C-461.1,529.3-462.7,527.7-464.6,527.7z"/><path class="st1" d="M-459.4,522.5H-475c-1.9,0-3.5,1.6-3.5,3.5h13.9c2.9,0,5.2,2.3,5.2,5.2v11.6l1.9,1.9c0.2,0.2,0.3,0.2,0.7,0.2 c0.2,0,0.2,0,0.3,0c0.3-0.2,0.5-0.5,0.5-0.9v-18C-455.9,524.1-457.5,522.5-459.4,522.5z"/></svg>';
                            } else {
                                $icon = '<svg version="1.1" id="ch" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="-496 507.7 54 54" style="enable-background:new -496 507.7 54 54;" xml:space="preserve"> <style type="text/css">.st1 { fill: #FFFFFF; } .st0 { fill: #808080; } </style> <g><circle cx="-469" cy="534.7" r="27" fill="#a886cd"/></g><path class="st1" d="M-459.9,523.7h-20.3c-1.9,0-3.4,1.5-3.4,3.4v15.3c0,1.9,1.5,3.4,3.4,3.4h11.4l5.9,4.9c0.2,0.2,0.3,0.2,0.5,0.2 h0.3c0.3-0.2,0.5-0.5,0.5-0.8v-4.2h1.7c1.9,0,3.4-1.5,3.4-3.4v-15.3C-456.5,525.2-458,523.7-459.9,523.7z"/><path class="st0" d="M-477.7,530.5h11.9c0.5,0,0.8,0.4,0.8,0.8l0,0c0,0.5-0.4,0.8-0.8,0.8h-11.9c-0.5,0-0.8-0.4-0.8-0.8l0,0C-478.6,530.8-478.2,530.5-477.7,530.5z"/><path class="st0" d="M-477.7,533.5h7.9c0.5,0,0.8,0.4,0.8,0.8l0,0c0,0.5-0.4,0.8-0.8,0.8h-7.9c-0.5,0-0.8-0.4-0.8-0.8l0,0C-478.6,533.9-478.2,533.5-477.7,533.5z"/></svg>';
                            }
                            $color = get_option("cht_color".$widget_id);
                            $custom_color = get_option("cht_custom_color".$widget_id);
                            if(!empty($custom_color)) {
                                $color = $custom_color;
                            }
                            if(!empty($color)) {
                                echo "<style>.svg-main-icon-{$i_count} svg circle {fill: $color !important;}</style>";
                            }
                        }
                    }
                }
                $no_of_views = isset($record['setting']['no_of_views'])?$record['setting']['no_of_views']:0;
                $no_of_clicks = isset($record['setting']['no_of_clicks'])?$record['setting']['no_of_clicks']:0;
                ?>
                <div class="analytics-record">
                    <div class="analytics-head">
                        <?php if($has_icon) { ?>
                            <span class="img-icon svg-main-icon-<?php echo $i_count; ?>">
                                <?php if($has_image) { ?>
                                    <img src="<?php echo $image_url ?>" />
                                <?php } else { ?>
                                    <svg class="svg-main-icon-<?php echo $i_count ?>" width="39" height="39" viewBox="0 0 39 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <?php echo isset($icon)?$icon:"" ?>
                                    </svg>
                                <?php } ?>
                            </span>
                        <?php } ?>
                        <?php echo $widget_name ?>
                    </div>
                    <div class="analytics-table">
                        <table>
                            <tr>
                                <th width="34%">Channel</th>
                                <th width="22%">Visitors</th>
                                <th width="24%">Unique Clicks <span class="icon label-tooltip" data-title="If a visitor clicked twice on the same channel(E.g. WhatsApp), it'll count as 1 click. Please keep in mind that the unique clicks aren't the number of visitors that contacted you - in some cases, a visitor will click on your chat channels but won't actually send you a message."><span class="dashicons dashicons-editor-help"></span></span></th>
                                <th width="20%">Click-rate</th>
                            </tr>
                            <tr>
                                <td>
                                    <?php if($has_icon) { ?>
                                        <span class="img-icon svg-main-icon-<?php echo $i_count; ?>">
                                            <?php if($has_image) { ?>
                                                <img src="<?php echo $image_url ?>" />
                                            <?php } else { ?>
                                                <svg class="svg-main-icon-<?php echo $i_count ?>" width="39" height="39" viewBox="0 0 39 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <?php echo isset($icon)?$icon:"" ?>
                                                </svg>
                                            <?php } ?>
                                        </span>
                                    <?php } ?>
                                    <?php echo $widget_name ?>
                                </td>
                                <td><?php echo $no_of_views ?></td>
                                <td><?php echo $no_of_clicks ?></td>
                                <td><?php echo (!empty($no_of_views))?number_format(($no_of_clicks*100/$no_of_views),2,".",",")."%":"-"; ?></td>
                            </tr>
                            <?php foreach($record['channels'] as $channel) {
                                $def_channel = $socials[$channel['channel_slug']];
                                $title = $def_channel['title'];
                                $color = $def_channel['color'];
                                $icon = $def_channel['svg'];
                                $has_image = false;
                                $slug = $def_channel['slug'];
                                $settings = get_option('cht_social' . $widget_id . '_' . $slug);
                                $color = isset($settings['bg_color'])?$settings['bg_color']:$color;
                                $title = isset($settings['title'])?$settings['title']:$title;
                                $image_id = isset($settings['image_id'])?$settings['image_id']:"";
                                $image_url = "";
                                if(!empty($image_id)) {
                                    $image_url = wp_get_attachment_url($image_id);
                                    if(!empty($image_url)) {
                                        $has_image = true;
                                    }
                                }
                                ?>
                                <tr>
                                    <td>
                                        <span class="img-icon">
                                            <?php if($has_image) { ?>
                                                <img src="<?php echo $image_url ?>" />
                                            <?php } else { ?>
                                                <?php if($channel['channel_slug'] != "instagram" || $color != "#ffffff") { ?>
                                                    <style>.svg-icon-<?php echo $i ?> .color-element {fill: <?php echo $color ?>}</style>
                                                <?php } ?>
                                                <svg class="svg-icon-<?php echo $i ?>" width="39" height="39" viewBox="0 0 39 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <?php echo $icon ?>
                                                </svg>
                                            <?php } ?>
                                        </span>
                                        <?php echo $title ?>
                                    </td>
                                    <td><?php echo $channel['no_of_views'] ?></td>
                                    <td><?php echo $channel['no_of_clicks'] ?></td>
                                    <td><?php echo (!empty($channel['no_of_views']))?number_format(($channel['no_of_clicks']*100/$channel['no_of_views']),2,".",",")."%":"-"; ?></td>
                                </tr>
                                <?php
                                $i++;
                            } ?>
                        </table>
                    </div>
                </div>
            <?php } ?>
        <?php } else {
            echo "<div class='no-records'>Your analytics data will appear once your Chaty widgets are displayed to your website's visitors</div>";
        }
        ?>
        <div class="trigger-option-block">
            <div class="reset-analytics-btn">
                <?php
                $checked = get_option("cht_data_analytics_status");
                $checked = ($checked === false)?"on":$checked;
                ?>
                <input type="hidden" name="collect_analytics_data" value="no">
                <label class="chaty-switch" for="collect_analytics_data">
                    <input type="checkbox" name="collect_analytics_data" id="collect_analytics_data" value="on" <?php checked("on", $checked) ?>>
                    <div class="chaty-slider round"></div>
                    Collect Analytics Data
                </label>
                <div class="clearfix"></div>
                <?php if(!empty($records)) { ?>
                    <a href="javascript:;">Reset Data</a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="chaty-popup-form" id="clear-data" style="display: none;">
    <div class="chaty-popup-overlay"></div>
    <div class="chaty-popup-content">
        <div class="popup-description text-center">Are you sure you want to reset all data?<br/>All your analytics data will be deleted</div>
        <form action="<?php echo admin_url("admin.php?page=") ?>" method="get">
            <div class="select-field-btn chaty-popup-btns text-center">
                <input type="hidden" name="page" value="widget-analytics">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce("chaty_remove_analytics") ?>">
                <input type="hidden" name="task" value="remove-data">
                <button type="submit" class="popup-form-reset-btn">Reset</button>
                <a href="javascript:;" class="popup-form-cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<div class="chaty-popup-form" id="analytics-popup" style="display: none;">
    <div class="chaty-popup-overlay"></div>
    <div class="chaty-popup-content">
        <div class="popup-description text-center">Chaty Analytics was turned off</div>
        <div class="select-field-btn chaty-popup-btns text-center">
            <a href="javascript:;" class="popup-form-cancel-btn">Close</a>
        </div>
    </div>
</div>

<?php
function get_analytics_records($start_date, $end_date) {
    global $wpdb;
    $chaty_table = $wpdb->prefix . 'chaty_widget_analysis';
    if ($wpdb->get_var("show tables like '{$chaty_table}'") == $chaty_table) {

        if(!empty($start_date) && !empty($end_date)) {
            $query = "SELECT id, widget_id, channel_slug, SUM(no_of_views) AS no_of_views, SUM(no_of_clicks) as no_of_clicks, is_widget
                FROM {$chaty_table}
                WHERE analysis_date >= '%s' AND analysis_date <= '%s'
                GROUP BY widget_id, is_widget, channel_slug
                ORDER BY is_widget DESC, widget_id ASC, channel_slug ASC";
            $query = $wpdb->prepare($query, array($start_date, $end_date));
        } else {
            $query = "SELECT id, widget_id, channel_slug, SUM(no_of_views) AS no_of_views, SUM(no_of_clicks) as no_of_clicks, is_widget
                FROM {$chaty_table}
                GROUP BY widget_id, is_widget, channel_slug
                ORDER BY is_widget DESC, widget_id ASC, channel_slug ASC";
        }

        $records = $wpdb->get_results($query, ARRAY_A);
        $widgets = array();
        if(!empty($records)) {
            foreach($records as $record) {
                if($record['is_widget'] == 1) {
                    $widget = array();
                    $widget_id = $record['widget_id'];
                    if($widget_id == 0) {
                        $widget_id = "";
                    }
                    $widget['id'] = $widget_id;
                    $widget['setting'] = $record;
                    $widget['channels'] = array();
                    $widgets[$record['widget_id']] = $widget;
                } else {
                    if(!isset($widgets[$record['widget_id']])) {
                        $widget = array();
                        $widget_id = $record['widget_id'];
                        if($widget_id == 0) {
                            $widget_id = "";
                        }
                        $widget['id'] = $widget_id;
                        $widget['channels'] = array();
                        $widgets[$record['widget_id']] = $widget;
                    }
                    $widgets[$record['widget_id']]['channels'][] = $record;
                }
            }
        }
    }
    if(!empty($widgets)) {
        $deleted_list = get_option("chaty_deleted_settings");
        if(empty($deleted_list) || !is_array($deleted_list)) {
            $deleted_list = array();
        }
        foreach($widgets as $key=>$widget) {
            if(in_array($key, $deleted_list)) {
                unset($widgets[$key]);
            }
        }
    }
    return $widgets;
}
?>
<script>
    jQuery(document).ready(function(){
        jQuery(document).on("change", "input[name='collect_analytics_data']", function(){
            var dataStatus = "off";
            if(jQuery("#collect_analytics_data").is(":checked")) {
                dataStatus = "on"
            } else {
                jQuery("#analytics-popup").show();
            }
            jQuery.ajax({
                url: "<?php echo admin_url("admin-ajax.php"); ?>",
                data: "status="+dataStatus+"&action=cht_save_analytics_status&nonce=<?php echo wp_create_nonce("cht_analytics_status") ?>",
                type: 'post'
            })
        });
        if(jQuery("#start_date").length) {
            jQuery("#start_date").datepicker({
                dateFormat: 'mm/dd/yy',
                altFormat: 'mm/dd/yy',
                maxDate: 0,
                onSelect: function(d,i){
                    var minDate = jQuery("#start_date").datepicker('getDate');
                    minDate.setDate(minDate.getDate()); //add two days
                    jQuery("#end_date").datepicker("option", "minDate", minDate);
                    if(jQuery("#end_date").val() <= jQuery("#start_date").val()) {
                        jQuery("#end_date").val(jQuery("#start_date").val());
                    }

                    if(jQuery("#end_date").val() == "") {
                        jQuery("#end_date").val(jQuery("#start_date").val());
                    }
                }
            });
        }
        if(jQuery("#end_date").length) {
            jQuery("#end_date").datepicker({
                dateFormat: 'mm/dd/yy',
                altFormat: 'mm/dd/yy',
                maxDate: 0,
                minDate: 0,
                onSelect: function(d,i){
                    if(jQuery("#start_date").val() == "") {
                        jQuery("#start_date").val(jQuery("#end_date").val());
                    }
                }
            });
        }
        if(jQuery("#start_date").length) {
            if(jQuery("#start_date").val() != "") {
                var minDate = jQuery("#start_date").datepicker('getDate');
                minDate.setDate(minDate.getDate()); //add two days
                jQuery("#end_date").datepicker("option", "minDate", minDate);
                if(jQuery("#end_date").val() <= jQuery("#start_date").val()) {
                    jQuery("#end_date").val(jQuery("#start_date").val());
                }
            }
        }
        jQuery(".custom-search-title").on("click", function(e){
            e.stopPropagation();
            jQuery(".custom-search-options").toggle();
        });

        jQuery("#search_for").on("change", function(e){
            var selValue = jQuery(this).val();
            if(selValue != "custom") {
                jQuery("#analytics_form").submit();
            } else {
                jQuery("#date_options").show();
            }
        });

        jQuery(document).on("click", ".reset-analytics-btn a", function(e){
            e.preventDefault();
            jQuery("#clear-data").show();
        });
        jQuery(document).on("click", ".popup-form-cancel-btn", function(e){
            e.preventDefault();
            jQuery(".chaty-popup-form").hide();
        });
        jQuery(document).on("click", "body, html", function(){
            jQuery(".custom-search-options").hide();
        });

    });
</script>