<?php
if (!defined('ABSPATH')) { exit; }
$days = array(
    "0" => "Everyday of week",
    "1" => "Sunday",
    "2" => "Monday",
    "3" => "Tuesday",
    "4" => "Wednesday",
    "5" => "Thursday",
    "6" => "Friday",
    "7" => "Saturday",
    "8" => "Sunday to Thursday",
    "9" => "Monday to Friday",
    "10" => "Weekend",
)
?>

<section class="section">
    <h1 class="section-title">
        <strong><?php esc_attr_e('Step', CHT_OPT);?> 3:</strong> <?php esc_attr_e('Triggers and targeting', CHT_OPT);?>
    </h1>

    <div class="form-horizontal">
        <div class="form-horizontal__item flex-center single-channel-setting active">
            <label class="form-horizontal__item-label" for="chaty_icons_view"><?php esc_attr_e('Icons view', CHT_OPT);?>:</label>
            <div>
                <?php
                $modes = array(
                    "vertical" => "Vertical mode",
                    "horizontal" => "Horizontal mode"
                );
                $mode = get_option('chaty_icons_view'.$this->widget_index);
                $mode = empty($mode)?"vertical":$mode;
                ?>
                <select name="chaty_icons_view" id="chaty_icons_view" class="chaty-select">
                    <?php foreach ($modes as $key => $value): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($mode, $key); ?>><?php echo esc_attr($value); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-horizontal__item flex-center single-channel-setting active">
            <label class="form-horizontal__item-label"><?php esc_attr_e('Default state', CHT_OPT);?>:</label>
            <div>
                <?php
                $states = array(
                    "click" => "Click to open",
                    "hover" => "Hover to open",
                    "open" => "Opened by default"
                );
                $state = get_option('chaty_default_state'.$this->widget_index);
                $state = empty($state)?"click":$state;
                ?>
                <select name="chaty_default_state" id="chaty_default_state" class="chaty-select">
                    <?php foreach ($states as $key => $value): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($state, $key); ?>><?php echo esc_attr($value); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-horizontal__item flex-center hide-show-button <?php echo esc_attr($state=="open"?"active":"") ?>" >
            <label class="form-horizontal__item-label"><?php esc_attr_e('Show close button', CHT_OPT);?>:</label>
            <div>
                <label class="switch">
                    <span class="switch__label"><?php esc_attr_e('Off', CHT_OPT);?></span>
                    <?php $close_button = get_option('cht_close_button'.$this->widget_index); ?>
                    <?php $close_button = empty($close_button)?"yes":$close_button; ?>
                    <input type="hidden" name="cht_close_button" value="no" >
                    <input data-gramm_editor="false" type="checkbox" id="cht_close_button" name="cht_close_button" value="yes" <?php checked($close_button, "yes") ?> >
                    <span class="switch__styled"></span>
                    <span class="switch__label"><?php esc_attr_e('On', CHT_OPT);?></span>
                </label>
            </div>
        </div>
        <div class="form-horizontal__item" id="trigger-setting">
            <label class="form-horizontal__item-label">
                <span class="header-tooltip">
                    <span class="header-tooltip-text text-center">Your Chaty widget will first appear to the user according to the selected trigger. After the widget appeared for the first time, it'll always be visible on-load - once the user is aware of the widget, the user expects it to always appear</span>
                    <span class="dashicons dashicons-editor-help"></span>
                </span>
                <?php esc_attr_e('Trigger', CHT_OPT);?>:
            </label>
            <div class="trigger-block">
                <?php $checked = get_option('chaty_trigger_on_time'.$this->widget_index) ?>
                <?php $time = get_option('chaty_trigger_time'.$this->widget_index); ?>
                <?php $time = empty($time)?"0":$time; ?>
                <?php $checked = empty($checked)?"yes":$checked; ?>
                <input type="hidden" name="chaty_trigger_on_time" value="no" >
                <div class="trigger-option-block">
                    <label class="chaty-switch" for="trigger_on_time">
                        <input type="checkbox" name="chaty_trigger_on_time" id="trigger_on_time" value="yes" <?php checked($checked, "yes") ?> >
                        <div class="chaty-slider round"></div>
                        Time Delay
                    </label>
                    <div class="trigger-block-input">
                        Display after <input type="number" id="chaty_trigger_time" name="chaty_trigger_time" value="<?php echo esc_attr($time) ?>"> seconds on the page
                    </div>
                </div>
                <?php $checked = get_option('chaty_trigger_on_exit'.$this->widget_index) ?>
                <?php $time = get_option('chaty_trigger_on_exit'.$this->widget_index); ?>
                <?php $time = empty($time)?"0":$time; ?>
                <?php $checked = empty($checked)?"no":$checked; ?>
                <div class="trigger-option-block">
                    <input type="hidden" name="chaty_trigger_on_exit" value="no" >
                    <label class="chaty-switch" for="chaty_trigger_on_exit">
                        <input type="checkbox" name="chaty_trigger_on_exit" id="chaty_trigger_on_exit" value="yes" <?php checked($checked, "yes") ?> >
                        <div class="chaty-slider round"></div>
                        Exit intent
                    </label>
                    <div class="trigger-block-input">
                        Display when visitor is about to leave the page
                    </div>
                </div>
                <?php $checked = get_option('chaty_trigger_on_scroll'.$this->widget_index) ?>
                <?php $time = get_option('chaty_trigger_on_page_scroll'.$this->widget_index); ?>
                <?php $time = empty($time)?"0":$time; ?>
                <?php $checked = empty($checked)?"no":$checked; ?>
                <div class="trigger-option-block">
                    <input type="hidden" name="chaty_trigger_on_scroll" value="no" >
                    <label class="chaty-switch" for="chaty_trigger_on_scroll">
                        <input type="checkbox" name="chaty_trigger_on_scroll" id="chaty_trigger_on_scroll" value="yes" <?php checked($checked, "yes") ?> >
                        <div class="chaty-slider round"></div>
                        Page Scroll
                    </label>
                    <div class="trigger-block-input">
                        Display after <input type="number" id="chaty_trigger_on_page_scroll" name="chaty_trigger_on_page_scroll" value="<?php echo esc_attr($time) ?>"> % on page
                    </div>
                </div>
            </div>
        </div>
        <div class="form-horizontal__item" id="scroll-to-item">
            <label class="form-horizontal__item-label">
                <span class="header-tooltip">
                    <span class="header-tooltip-text text-center"><?php esc_html_e('Schedule the specific time and date when your Chaty widget appears.', CHT_OPT);?></span>
                    <span class="dashicons dashicons-editor-help"></span>
                </span>
                <?php esc_attr_e('Date scheduling', CHT_OPT);?>:
            </label>
            <?php
            $date_rules = get_option('cht_date_rules'.$this->widget_index);
            $timezone = isset($date_rules['timezone'])?$date_rules['timezone']:"";
            $start_date = isset($date_rules['start_date'])?$date_rules['start_date']:"";
            $start_time = isset($date_rules['start_time'])?$date_rules['start_time']:"";
            $end_date = isset($date_rules['end_date'])?$date_rules['end_date']:"";
            $end_time = isset($date_rules['end_time'])?$date_rules['end_time']:"";
            $status = isset($date_rules['status'])?$date_rules['status']:"no";
            ?>
            <div class="chaty-option-box">
                <div id="date-schedule" class="<?php echo ($status=="yes")?"active":"" ?>">
                    <div class="date-schedule-box">
                        <div class="date-schedule">
                            <div class="select-box">
                                <label><?php esc_html_e('Timezone', CHT_OPT);?></label>
                                <select class="select2-box" name="cht_date_rules[timezone]" id="cht_date_rules_time_zone">
                                    <?php echo chaty_timezone_choice($timezone, true );?>
                                </select>
                            </div>
                            <div class="date-time-box">
                                <div class="date-select-option">
                                    <label for="date_start_date">
                                        <span class="header-tooltip">
                                        <span class="header-tooltip-text text-center"><?php esc_html_e('Schedule a date from which the Chaty widget will be displayed (the starting date is included)', CHT_OPT);?></span>
                                        <span class="dashicons dashicons-editor-help"></span>
                                    </span>
                                    <?php esc_html_e('Start Date', CHT_OPT);?></label>
                                    <input autocomplete="off" type="text" name="cht_date_rules[start_date]" id="date_start_date" value="<?php echo esc_attr($start_date) ?>" >
                                </div>
                                <div class="time-select-option">
                                    <label for="date_start_time"><?php esc_html_e('Start Time', CHT_OPT);?></label>
                                    <input autocomplete="off" type="text" name="cht_date_rules[start_time]" id="date_start_time" value="<?php echo esc_attr($start_time) ?>">
                                </div>
                                <div class="clearfix clear"></div>
                            </div>
                            <div class="date-time-box">
                            <div class="date-select-option">
                                <label for="date_end_date">
                                    <label for="date_start_date">
                                        <span class="header-tooltip">
                                        <span class="header-tooltip-text text-center"><?php esc_html_e('Schedule a date from which the Chaty widget will stop being displayed (the end date is included)', CHT_OPT);?></span>
                                        <span class="dashicons dashicons-editor-help"></span>
                                    </span>
                                    <?php esc_html_e('End Date', CHT_OPT);?></label>
                                <input type="text" name="cht_date_rules[end_date]" id="date_end_date" value="<?php echo esc_attr($end_date) ?>">
                            </div>
                            <div class="time-select-option">
                                <label for="date_end_time"><?php esc_html_e('End Time', CHT_OPT);?></label>
                                <input type="text" name="cht_date_rules[end_time]" id="date_end_time" value="<?php echo esc_attr($end_time) ?>">
                            </div>
                            <div class="clearfix clear"></div>
                        </div>
                        </div>
                        <a href="javascript:;" class="create-rule remove-rules" id="remove-date-rule"><?php esc_html_e('Remove Rules', CHT_OPT);?></a>
                    </div>
                    <div class="date-schedule-button">
                        <a href="javascript:;" class="create-rule" id="create-date-rule"><?php esc_html_e('Add Rule', CHT_OPT);?></a>
                    </div>
                </div>
            </div>
            <input type="hidden" name="cht_date_rules[status]" id="cht_date_rules" value="<?php echo esc_attr($status) ?>" />
        </div>
        <div class="form-horizontal__item">
            <label class="form-horizontal__item-label">
                <span class="header-tooltip">
                    <span class="header-tooltip-text text-center">Display the widget on specific days and hours based on your opening days and hours</span>
                    <span class="dashicons dashicons-editor-help"></span>
                </span>
                <?php esc_attr_e('Days and hours', CHT_OPT);?>:
            </label>
            <div class="chaty-option-box">
                <div class="chaty-data-and-time-rules">
                    <?php
                    $time_options = get_option("cht_date_and_time_settings".$this->widget_index);
                    if(!empty($time_options) && is_array($time_options)) {
                        $count = 0;
                        foreach($time_options as $k=>$option) {
                            $count++;
                            $selected_day = isset($option['days'])?$option['days']:0;
                            $start_time = isset($option['start_time'])?$option['start_time']:0;
                            $end_time = isset($option['end_time'])?$option['end_time']:0;
                            echo $gmt = isset($option['gmt'])?$option['gmt']:0;
                            if(is_numeric($gmt)) {
                                $gmt = floatval($gmt);
                            }
                            ?>
                            <div class="chaty-date-time-option" data-index="<?php echo esc_attr($count) ?>">
                                <div class="date-time-content">
                                    <div class="day-select">
                                        <select class="cht-required" name="cht_date_and_time_settings[<?php echo esc_attr($count) ?>][days]" id="url_shown_on_<?php echo esc_attr($count) ?>_option">
                                            <?php foreach ($days as $key=>$value) { ?>
                                                <option <?php selected($key, $selected_day) ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_attr($value) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="day-label">
                                        From
                                    </div>
                                    <div class="day-time">
                                        <input type="text" class="cht-required time-picker ui-timepicker-input" autocomplete="off" value="<?php echo esc_attr($start_time) ?>" name="cht_date_and_time_settings[<?php echo esc_attr($count) ?>][start_time]" id="start_time_<?php echo esc_attr($count) ?>" />
                                    </div>
                                    <div class="day-label">
                                        To
                                    </div>
                                    <div class="day-time">
                                        <input type="text" class="cht-required time-picker ui-timepicker-input" autocomplete="off"  value="<?php echo esc_attr($end_time) ?>" name="cht_date_and_time_settings[<?php echo esc_attr($count) ?>][end_time]" id="end_time_<?php echo esc_attr($count) ?>" />
                                    </div>
                                    <div class="day-label">
                                        <span class="gmt-data">GMT</span>
                                    </div>
                                    <div class="day-time gtm-select">
                                        <div class="gmt-data">
                                            <select class="select2-box" name="cht_date_and_time_settings[<?php echo esc_attr($count) ?>][gmt]" id="url_shown_on_<?php echo esc_attr($count) ?>_option">
                                                <?php echo chaty_timezone_choice($gmt, false );?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="day-buttons">
                                        <a class="remove-page-option" href="javascript:;">
                                            <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(2.26764 0.0615997) rotate(45)" fill="white"></rect>
                                                <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(13.3198 1.649) rotate(135)" fill="white"></rect>
                                            </svg>
                                        </a>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
                <a href="javascript:;" class="create-rule" id="create-data-and-time-rule">Add Rule</a>
            </div>
        </div>

        <div class="form-horizontal__item" id="custom-rules">
            <label class="form-horizontal__item-label">
                <span class="header-tooltip">
                    <span class="header-tooltip-text text-center">Show or don't show the widget on specific pages. You can use rules like contains, exact match, starts with, and ends with</span>
                    <span class="dashicons dashicons-editor-help"></span>
                </span>
                Show on pages:
            </label>
            <div class="chaty-option-box">
                <div class="chaty-page-options" id="chaty-page-options">
                    <?php $page_option = get_option("cht_page_settings".$this->widget_index);
                    if(!empty($page_option) && is_array($page_option)) {
                        $count = 0;
                        foreach($page_option as $k=>$option) {
                            $count++;
                            ?>
                            <div class="chaty-page-option <?php echo $k==count($page_option)?"last":""; ?>">
                                <div class="url-content">
                                    <div class="url-select">
                                        <select class="cht-required" name="cht_page_settings[<?php echo $count  ?>][shown_on]" id="url_shown_on_<?php echo $count  ?>_option">
                                            <option value="show_on" <?php selected($option['shown_on'] ,"show_on") ?> >Show on</option>
                                            <option value="not_show_on" <?php selected($option['shown_on'], "not_show_on") ?>>Don't show on</option>
                                        </select>
                                    </div>
                                    <div class="url-option">
                                        <select class="url-options cht-required" name="cht_page_settings[<?php echo $count  ?>][option]" id="url_rules_<?php echo $count  ?>_option">
                                            <option disabled value="">Select Rule</option>
                                            <?php foreach($url_options as $key=>$value) {
                                                $selected = selected($option['option'], $key, false);
                                                echo '<option '.$selected.' value="'.$key.'">'.$value.'</option>';
                                            } ?>
                                        </select>
                                    </div>
                                    <div class="url-box">
                                        <span class='chaty-url'><?php esc_attr_e(site_url("/")); ?></span>
                                    </div>
                                    <div class="url-values">
                                        <input type="text" class="cht-required" value="<?php esc_attr_e($option['value']) ?>" name="cht_page_settings[<?php echo $count  ?>][value]" id="url_rules_<?php esc_attr_e($count)  ?>_value" />
                                    </div>
                                    <div class="url-buttons">
                                        <a class="remove-chaty" href="javascript:;">
                                            <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(2.26764 0.0615997) rotate(45)" fill="white"></rect>
                                                <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(13.3198 1.649) rotate(135)" fill="white"></rect>
                                            </svg>
                                        </a>
                                        <a class="add-chaty-option" href="javascript:;">
                                            <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(2.26764 0.0615997) rotate(45)" fill="white"></rect>
                                                <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(13.3198 1.649) rotate(135)" fill="white"></rect>
                                            </svg>
                                        </a>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <?php
                        }
                    }
                    ?>

                </div>
                <a href="javascript:;" class="create-rule" id="create-rule">Add Rule</a>
            </div>
        </div>

        <div class="form-horizontal__item" id="custom-rules">
            <label class="form-horizontal__item-label">
                <span class="header-tooltip">
                    <span class="header-tooltip-text text-center">Show the widget only to visitors who come from specific traffic sources including direct traffic, social networks, search engines, Google Ads, or any other traffic source.</span>
                    <span class="dashicons dashicons-editor-help"></span>
                </span>
                Traffic source:
            </label>
            <?php
            $checked = get_option('chaty_traffic_source'.$this->widget_index);
            $checked = empty($checked)?"no":$checked;
            ?>
            <div class="chaty-option-box traffic-options-box <?php echo ($checked=="yes")?"active":"" ?>">
                <div class="traffic-default">
                    <a href="javascript:;" class="create-rule" id="update-chaty-traffic-source-rule">Add Rule</a>
                    <input type="hidden" name="chaty_traffic_source" id="chaty_traffic_source" value="<?php echo $checked ?>">
                </div>
                <div class="traffic-active">
                    <div class="trigger-block no-margin">
                        <?php
                        $checked = get_option('chaty_traffic_source_direct_visit'.$this->widget_index);
                        $checked = empty($checked)?"no":$checked;
                        ?>
                        <input type="hidden" name="chaty_traffic_source_direct_visit" value="no">
                        <div class="trigger-option-block">
                            <label class="chaty-switch" for="chaty_traffic_source_direct_visit">
                                <input type="checkbox" name="chaty_traffic_source_direct_visit" id="chaty_traffic_source_direct_visit" value="yes" <?php checked($checked, "yes") ?> >
                                <div class="chaty-slider round"></div>
                                <span class="header-tooltip">
                                    <span class="header-tooltip-text text-center">Show the Chaty to visitors who arrived to your website from direct traffic</span>
                                    <span class="dashicons dashicons-editor-help"></span>
                                </span>
                                Direct visit
                            </label>
                        </div>
                        <?php
                        $checked = get_option('chaty_traffic_source_social_network'.$this->widget_index);
                        $checked = empty($checked)?"no":$checked;
                        ?>
                        <div class="trigger-option-block">
                            <input type="hidden" name="chaty_traffic_source_social_network" value="no">
                            <label class="chaty-switch" for="chaty_traffic_source_social_network">
                                <input type="checkbox" name="chaty_traffic_source_social_network" id="chaty_traffic_source_social_network" value="yes" <?php checked($checked, "yes") ?>>
                                <div class="chaty-slider round"></div>
                                <span class="header-tooltip">
                                    <span class="header-tooltip-text text-center">Show the Chaty to visitors who arrived to your website from social networks including: Facebook, Twitter, Pinterest, Instagram, Google+, LinkedIn, Delicious, Tumblr, Dribbble, StumbleUpon, Flickr, Plaxo, Digg and more</span>
                                    <span class="dashicons dashicons-editor-help"></span>
                                </span>
                                Social networks
                            </label>
                        </div>
                        <?php
                        $checked = get_option('chaty_traffic_source_search_engine'.$this->widget_index);
                        $checked = empty($checked)?"no":$checked;
                        ?>
                        <div class="trigger-option-block">
                            <input type="hidden" name="chaty_traffic_source_search_engine" value="no">
                            <label class="chaty-switch" for="chaty_traffic_source_search_engine">
                                <input type="checkbox" name="chaty_traffic_source_search_engine" id="chaty_traffic_source_search_engine" value="yes" <?php checked($checked, "yes") ?>>
                                <div class="chaty-slider round"></div>
                                <span class="header-tooltip">
                                    <span class="header-tooltip-text text-center">Show the Chaty to visitors who arrived from search engines including: Google, Bing, Yahoo!, Yandex, AOL, Ask, WOW,  WebCrawler, Baidu and more</span>
                                    <span class="dashicons dashicons-editor-help"></span>
                                </span>
                                Search engines
                            </label>
                        </div>
                        <?php
                        $checked = get_option('chaty_traffic_source_google_ads'.$this->widget_index);
                        $checked = empty($checked)?"no":$checked;
                        ?>
                        <div class="trigger-option-block">
                            <input type="hidden" name="chaty_traffic_source_google_ads" value="no">
                            <label class="chaty-switch" for="chaty_traffic_source_google_ads">
                                <input type="checkbox" name="chaty_traffic_source_google_ads" id="chaty_traffic_source_google_ads" value="yes" <?php checked($checked, "yes") ?>>
                                <div class="chaty-slider round"></div>
                                <span class="header-tooltip">
                                    <span class="header-tooltip-text text-center">Show the Chaty to visitors who arrived from search engines including: Google, Bing, Yahoo!, Yandex, AOL, Ask, WOW,  WebCrawler, Baidu and more</span>
                                    <span class="dashicons dashicons-editor-help"></span>
                                </span>
                                Google Ads
                            </label>
                        </div>
                        <div class="clear clearfix"></div>
                        <?php
                        $custom_rules = get_option("chaty_custom_traffic_rules".$this->widget_index)
                        ?>
                        <div class="traffic-custom-rules">
                            <div class="custom-rule-title">Specific URL</div>
                            <div class="traffic-custom-rules-box">
                                <?php if(!empty($custom_rules) && is_array($custom_rules) && count($custom_rules) > 0) {
                                    foreach ($custom_rules as $key=>$rule) { ?>
                                        <div class="custom-traffic-rule">
                                            <div class="traffic-option">
                                                <select name="chaty_custom_traffic_rules[<?php echo esc_attr($key) ?>][url_option]">
                                                    <option value="contain" <?php selected($rule['url_option'], "contain") ?>>Contains</option>
                                                    <option value="not_contain" <?php selected($rule['url_option'], "not_contain") ?>>Not contains</option>
                                                </select>
                                            </div>
                                            <div class="traffic-url">
                                                <input type="text" name="chaty_custom_traffic_rules[<?php echo esc_attr($key) ?>][url_value]" value="<?php echo esc_attr($rule['url_value']) ?>" placeholder="https://www.example.com" />
                                            </div>
                                            <div class="traffic-action">
                                                <a class="remove-traffic-option" href="javascript:;">
                                                    <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(2.26764 0.0615997) rotate(45)" fill="white"></rect>
                                                        <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(13.3198 1.649) rotate(135)" fill="white"></rect>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    <?php }
                                } else { ?>
                                    <div class="custom-traffic-rule">
                                        <div class="traffic-option">
                                            <select name="chaty_custom_traffic_rules[0][url_option]">
                                                <option value="contain">Contains</option>
                                                <option value="not_contain">Not contains</option>
                                            </select>
                                        </div>
                                        <div class="traffic-url">
                                            <input type="text" name="chaty_custom_traffic_rules[0][url_value]" />
                                        </div>
                                        <div class="traffic-action">
                                            <a class="remove-traffic-option" href="javascript:;">
                                                <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(2.26764 0.0615997) rotate(45)" fill="white"></rect>
                                                    <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(13.3198 1.649) rotate(135)" fill="white"></rect>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="clear clearfix"></div>
                        <div class="traffic-rule-actions">
                            <a href="javascript:;" class="create-rule" id="add-traffic-rule">Add Rule</a>
                            <a href="javascript:;" class="create-rule remove-rules" id="remove-traffic-rules">Remove Rules</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php $is_pro = $this->is_pro() ?>

        <?php $countries = array(array("short_name" => "AF", "country_name" => "Afghanistan"), array("short_name" => "AL", "country_name" => "Albania"), array("short_name" => "DZ", "country_name" => "Algeria"), array("short_name" => "AD", "country_name" => "Andorra"), array("short_name" => "AO", "country_name" => "Angola"), array("short_name" => "AI", "country_name" => "Anguilla"), array("short_name" => "AG", "country_name" => "Antigua and Barbuda"), array("short_name" => "AR", "country_name" => "Argentina"), array("short_name" => "AM", "country_name" => "Armenia"), array("short_name" => "AW", "country_name" => "Aruba"), array("short_name" => "AU", "country_name" => "Australia"), array("short_name" => "AT", "country_name" => "Austria"), array("short_name" => "AZ", "country_name" => "Azerbaijan"), array("short_name" => "BS", "country_name" => "Bahamas"), array("short_name" => "BH", "country_name" => "Bahrain"), array("short_name" => "BD", "country_name" => "Bangladesh"), array("short_name" => "BB", "country_name" => "Barbados"), array("short_name" => "BY", "country_name" => "Belarus"), array("short_name" => "BE", "country_name" => "Belgium"), array("short_name" => "BZ", "country_name" => "Belize"), array("short_name" => "BJ", "country_name" => "Benin"), array("short_name" => "BM", "country_name" => "Bermuda"), array("short_name" => "BT", "country_name" => "Bhutan"), array("short_name" => "BO", "country_name" => "Bolivia"), array("short_name" => "BA", "country_name" => "Bosnia and Herzegowina"), array("short_name" => "BW", "country_name" => "Botswana"), array("short_name" => "BV", "country_name" => "Bouvet Island"), array("short_name" => "BR", "country_name" => "Brazil"), array("short_name" => "IO", "country_name" => "British Indian Ocean Territory"), array("short_name" => "BN", "country_name" => "Brunei Darussalam"), array("short_name" => "BG", "country_name" => "Bulgaria"), array("short_name" => "BF", "country_name" => "Burkina Faso"), array("short_name" => "BI", "country_name" => "Burundi"), array("short_name" => "KH", "country_name" => "Cambodia"), array("short_name" => "CM", "country_name" => "Cameroon (Republic of Cameroon)"), array("short_name" => "CA", "country_name" => "Canada"), array("short_name" => "CV", "country_name" => "Cape Verde"), array("short_name" => "KY", "country_name" => "Cayman Islands"), array("short_name" => "CF", "country_name" => "Central African Republic"), array("short_name" => "TD", "country_name" => "Chad"), array("short_name" => "CL", "country_name" => "Chile"), array("short_name" => "CN", "country_name" => "China"), array("short_name" => "CX", "country_name" => "Christmas Island"), array("short_name" => "CC", "country_name" => "Cocos (Keeling) Islands"), array("short_name" => "CO", "country_name" => "Colombia"), array("short_name" => "KM", "country_name" => "Comoros"), array("short_name" => "CG", "country_name" => "Congo"), array("short_name" => "CK", "country_name" => "Cook Islands"), array("short_name" => "CR", "country_name" => "Costa Rica"), array("short_name" => "CI", "country_name" => "Cote D\Ivoire"), array("short_name" => "HR", "country_name" => "Croatia"), array("short_name" => "CU", "country_name" => "Cuba"), array("short_name" => "CY", "country_name" => "Cyprus"), array("short_name" => "CZ", "country_name" => "Czech Republic"), array("short_name" => "DK", "country_name" => "Denmark"), array("short_name" => "DJ", "country_name" => "Djibouti"), array("short_name" => "DM", "country_name" => "Dominica"), array("short_name" => "DO", "country_name" => "Dominican Republic"), array("short_name" => "EC", "country_name" => "Ecuador"), array("short_name" => "EG", "country_name" => "Egypt"), array("short_name" => "SV", "country_name" => "El Salvador"), array("short_name" => "GQ", "country_name" => "Equatorial Guinea"), array("short_name" => "ER", "country_name" => "Eritrea"), array("short_name" => "EE", "country_name" => "Estonia"), array("short_name" => "ET", "country_name" => "Ethiopia"), array("short_name" => "FK", "country_name" => "Falkland Islands (Malvinas)"), array("short_name" => "FO", "country_name" => "Faroe Islands"), array("short_name" => "FJ", "country_name" => "Fiji"), array("short_name" => "FI", "country_name" => "Finland"), array("short_name" => "FR", "country_name" => "France"), array("short_name" => "Me", "country_name" => "Montenegro"), array("short_name" => "GF", "country_name" => "French Guiana"), array("short_name" => "PF", "country_name" => "French Polynesia"), array("short_name" => "TF", "country_name" => "French Southern Territories"), array("short_name" => "GA", "country_name" => "Gabon"), array("short_name" => "GM", "country_name" => "Gambia"), array("short_name" => "GE", "country_name" => "Georgia"), array("short_name" => "DE", "country_name" => "Germany"), array("short_name" => "GH", "country_name" => "Ghana"), array("short_name" => "GI", "country_name" => "Gibraltar"), array("short_name" => "GR", "country_name" => "Greece"), array("short_name" => "GL", "country_name" => "Greenland"), array("short_name" => "GD", "country_name" => "Grenada"), array("short_name" => "GP", "country_name" => "Guadeloupe"), array("short_name" => "GT", "country_name" => "Guatemala"), array("short_name" => "GN", "country_name" => "Guinea"), array("short_name" => "GW", "country_name" => "Guinea bissau"), array("short_name" => "GY", "country_name" => "Guyana"), array("short_name" => "HT", "country_name" => "Haiti"), array("short_name" => "HM", "country_name" => "Heard Island And Mcdonald Islands"), array("short_name" => "HN", "country_name" => "Honduras"), array("short_name" => "HK", "country_name" => "Hong Kong"), array("short_name" => "HU", "country_name" => "Hungary"), array("short_name" => "IS", "country_name" => "Iceland"), array("short_name" => "IN", "country_name" => "India"), array("short_name" => "ID", "country_name" => "Indonesia"), array("short_name" => "IR", "country_name" => "Iran, Islamic Republic Of"), array("short_name" => "IQ", "country_name" => "Iraq"), array("short_name" => "IE", "country_name" => "Ireland"), array("short_name" => "IL", "country_name" => "Israel"), array("short_name" => "IT", "country_name" => "Italy"), array("short_name" => "JM", "country_name" => "Jamaica"), array("short_name" => "JP", "country_name" => "Japan"), array("short_name" => "JO", "country_name" => "Jordan"), array("short_name" => "KZ", "country_name" => "Kazakhstan"), array("short_name" => "KE", "country_name" => "Kenya"), array("short_name" => "KI", "country_name" => "Kiribati"), array("short_name" => "KP", "country_name" => "Korea, Democratic People's Republic Of"), array("short_name" => "KR", "country_name" => "South Korea"), array("short_name" => "KW", "country_name" => "Kuwait"), array("short_name" => "KG", "country_name" => "Kyrgyzstan"), array("short_name" => "LA", "country_name" => "Lao People\s Democratic Republic"), array("short_name" => "LV", "country_name" => "Latvia"), array("short_name" => "LB", "country_name" => "Lebanon"), array("short_name" => "LS", "country_name" => "Lesotho"), array("short_name" => "LR", "country_name" => "Liberia"), array("short_name" => "LY", "country_name" => "Libyan Arab Jamahiriya"), array("short_name" => "LI", "country_name" => "Liechtenstein"), array("short_name" => "LT", "country_name" => "Lithuania"), array("short_name" => "LU", "country_name" => "Luxembourg"), array("short_name" => "MO", "country_name" => "Macao"), array("short_name" => "MK", "country_name" => "Macedonia"), array("short_name" => "MG", "country_name" => "Madagascar"), array("short_name" => "MW", "country_name" => "Malawi"), array("short_name" => "MY", "country_name" => "Malaysia"), array("short_name" => "MV", "country_name" => "Maldives"), array("short_name" => "ML", "country_name" => "Mali"), array("short_name" => "MT", "country_name" => "Malta"), array("short_name" => "MQ", "country_name" => "Martinique"), array("short_name" => "MR", "country_name" => "Mauritania"), array("short_name" => "MU", "country_name" => "Mauritius"), array("short_name" => "YT", "country_name" => "Mayotte"), array("short_name" => "MD", "country_name" => "Moldova"), array("short_name" => "MC", "country_name" => "Monaco"), array("short_name" => "MN", "country_name" => "Mongolia"), array("short_name" => "MS", "country_name" => "Montserrat"), array("short_name" => "MA", "country_name" => "Morocco"), array("short_name" => "MZ", "country_name" => "Mozambique"), array("short_name" => "MM", "country_name" => "Myanmar"), array("short_name" => "NA", "country_name" => "Namibia"), array("short_name" => "NR", "country_name" => "Nauru"), array("short_name" => "NP", "country_name" => "Nepal"), array("short_name" => "NL", "country_name" => "Netherlands"), array("short_name" => "AN", "country_name" => "Netherlands Antilles"), array("short_name" => "NC", "country_name" => "New Caledonia"), array("short_name" => "NZ", "country_name" => "New Zealand"), array("short_name" => "NI", "country_name" => "Nicaragua"), array("short_name" => "NE", "country_name" => "Niger"), array("short_name" => "NG", "country_name" => "Nigeria"), array("short_name" => "NU", "country_name" => "Niue"), array("short_name" => "NF", "country_name" => "Norfolk Island"), array("short_name" => "NO", "country_name" => "Norway"), array("short_name" => "OM", "country_name" => "Oman"), array("short_name" => "PK", "country_name" => "Pakistan"), array("short_name" => "PA", "country_name" => "Panama"), array("short_name" => "PG", "country_name" => "Papua New Guinea"), array("short_name" => "PY", "country_name" => "Paraguay"), array("short_name" => "PE", "country_name" => "Peru"), array("short_name" => "PH", "country_name" => "Philippines"), array("short_name" => "PN", "country_name" => "Pitcairn"), array("short_name" => "PL", "country_name" => "Poland"), array("short_name" => "PT", "country_name" => "Portugal"), array("short_name" => "QA", "country_name" => "Qatar"), array("short_name" => "RE", "country_name" => "Reunion"), array("short_name" => "RO", "country_name" => "Romania"), array("short_name" => "RU", "country_name" => "Russia"), array("short_name" => "RW", "country_name" => "Rwanda"), array("short_name" => "KN", "country_name" => "Saint Kitts and Nevis"), array("short_name" => "LC", "country_name" => "Saint Lucia"), array("short_name" => "VC", "country_name" => "St. Vincent"), array("short_name" => "WS", "country_name" => "Samoa"), array("short_name" => "SM", "country_name" => "San Marino"), array("short_name" => "ST", "country_name" => "Sao Tome and Principe"), array("short_name" => "SA", "country_name" => "Saudi Arabia"), array("short_name" => "SN", "country_name" => "Senegal"), array("short_name" => "SC", "country_name" => "Seychelles"), array("short_name" => "SL", "country_name" => "Sierra Leone"), array("short_name" => "SG", "country_name" => "Singapore"), array("short_name" => "SK", "country_name" => "Slovakia"), array("short_name" => "SI", "country_name" => "Slovenia"), array("short_name" => "SB", "country_name" => "Solomon Islands"), array("short_name" => "SO", "country_name" => "Somalia"), array("short_name" => "ZA", "country_name" => "South Africa"), array("short_name" => "GS", "country_name" => "South Georgia & South Sandwich Islands"), array("short_name" => "ES", "country_name" => "Spain"), array("short_name" => "LK", "country_name" => "Sri Lanka"), array("short_name" => "SH", "country_name" => "Saint Helena"), array("short_name" => "PM", "country_name" => "Saint Pierre And Miquelon"), array("short_name" => "SD", "country_name" => "Sudan"), array("short_name" => "SR", "country_name" => "Suriname"), array("short_name" => "SJ", "country_name" => "Svalbard And Jan Mayen"), array("short_name" => "SZ", "country_name" => "Swaziland"), array("short_name" => "SE", "country_name" => "Sweden"), array("short_name" => "CH", "country_name" => "Switzerland"), array("short_name" => "SY", "country_name" => "Syria"), array("short_name" => "TW", "country_name" => "Taiwan"), array("short_name" => "TJ", "country_name" => "Tajikistan"), array("short_name" => "TZ", "country_name" => "Tanzania, United Republic Of"), array("short_name" => "TH", "country_name" => "Thailand"), array("short_name" => "TG", "country_name" => "Togo"), array("short_name" => "TK", "country_name" => "Tokelau"), array("short_name" => "TO", "country_name" => "Tonga"), array("short_name" => "TT", "country_name" => "Trinidad and Tobago"), array("short_name" => "TN", "country_name" => "Tunisia"), array("short_name" => "TR", "country_name" => "Turkey"), array("short_name" => "TM", "country_name" => "Turkmenistan"), array("short_name" => "TC", "country_name" => "Turks and Caicos Islands"), array("short_name" => "TV", "country_name" => "Tuvalu"), array("short_name" => "UG", "country_name" => "Uganda"), array("short_name" => "UA", "country_name" => "Ukraine"), array("short_name" => "AE", "country_name" => "United Arab Emirates"), array("short_name" => "GB", "country_name" => "United Kingdom"), array("short_name" => "US", "country_name" => "United States"), array("short_name" => "UM", "country_name" => "United States Minor Outlying Islands"), array("short_name" => "UY", "country_name" => "Uruguay"), array("short_name" => "UZ", "country_name" => "Uzbekistan"), array("short_name" => "VU", "country_name" => "Vanuatu"), array("short_name" => "VA", "country_name" => "Holy See (Vatican City State)"), array("short_name" => "VE", "country_name" => "Venezuela"), array("short_name" => "VN", "country_name" => "Vietnam"), array("short_name" => "VG", "country_name" => "Virgin Islands (British)"), array("short_name" => "WF", "country_name" => "Wallis and Futuna Islands"), array("short_name" => "EH", "country_name" => "Western Sahara"), array("short_name" => "YE", "country_name" => "Yemen"), array("short_name" => "ZM", "country_name" => "Zambia"), array("short_name" => "ZW", "country_name" => "Zimbabwe"), array("short_name" => "AX", "country_name" => "Aland Islands"), array("short_name" => "CD", "country_name" => "Congo, The Democratic Republic Of The"), array("short_name" => "CW", "country_name" => "Curaçao"), array("short_name" => "GG", "country_name" => "Guernsey"), array("short_name" => "IM", "country_name" => "Isle Of Man"), array("short_name" => "JE", "country_name" => "Jersey"), array("short_name" => "KV", "country_name" => "Kosovo"), array("short_name" => "PS", "country_name" => "Palestinian Territory"), array("short_name" => "BL", "country_name" => "Saint Barthélemy"), array("short_name" => "MF", "country_name" => "Saint Martin"), array("short_name" => "RS", "country_name" => "Serbia"), array("short_name" => "SX", "country_name" => "Sint Maarten"), array("short_name" => "TL", "country_name" => "Timor Leste"), array("short_name" => "MX", "country_name" => "Mexico")) ?>

        <?php
        $selected_countries = get_option("chaty_countries_list".$this->widget_index);
        $selected_countries = ($selected_countries === false || empty($selected_countries) || !is_array($selected_countries))?array():$selected_countries;
        $count = count($selected_countries);
        $message =  "All countries";
        if($count == 1) {
            $message = "1 country selected";
        } else if($count > 1){
            $message = $count." countries selected";
        }
        ?>

        <div class="form-horizontal__item">
            <label class="form-horizontal__item-label">
                <span class="header-tooltip">
                    <span class="header-tooltip-text text-center">Target your widget to specific countries. You can create different widgets for different countries</span>
                    <span class="dashicons dashicons-editor-help"></span>
                </span>
                Country targeting:
            </label>
            <div class="country-option-box <?php echo esc_attr($is_pro?"is-pro":"not-pro") ?>">
                <button type="button" class="chaty-input-button"><?php echo esc_attr($message) ?></button>
                <div class="country-list-box">
                    <select name="chaty_countries_list[]" multiple placeholder="Select Country" class="country-list chaty-select <?php echo esc_attr($is_pro?"is-pro":"not-pro") ?>">
                        <?php foreach($countries as $country) {
                            $selected = in_array($country["short_name"], $selected_countries)?"selected":"";
                            ?>
                            <option <?php echo esc_attr($selected) ?> value="<?php echo esc_attr($country["short_name"]) ?>"><?php echo esc_attr($country["country_name"]) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php if(!$is_pro) { ?>
                    <div class="chaty-pro-feature">
                        <a target="_blank" href="<?php echo esc_url($this->getUpgradeMenuItemUrl());?>">
                            <?php esc_attr_e('Activate your license key', CHT_OPT);?>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="form-horizontal__item">
            <label class="form-horizontal__item-label">Custom CSS:</label>
            <?php $custom_css = get_option("chaty_custom_css".$this->widget_index); ?>
            <div class="country-option-box <?php echo esc_attr($is_pro?"is-pro":"not-pro") ?>">
                <div class="css-option-box">
                    <textarea name="chaty_custom_css" id="chaty_custom_css" class="custom-css"><?php echo esc_attr($custom_css) ?></textarea>
                </div>
                <?php if(!$is_pro) { ?>
                <div class="chaty-pro-feature">
                    <a target="_blank" href="<?php echo esc_url($this->getUpgradeMenuItemUrl());?>">
                        <?php esc_attr_e('Activate your license key', CHT_OPT);?>
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

<div class="chaty-date-and-time-options-html" style="display: none">
    <div class="chaty-date-time-option" data-index="__count__">
        <div class="date-time-content">
            <div class="day-select">
                <select class="cht-required" name="cht_date_and_time_settings[__count__][days]" id="url_shown_on___count___option">
                    <?php foreach ($days as $key=>$value) { ?>
                        <option value="<?php echo esc_attr($key) ?>"><?php echo esc_attr($value) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="day-label">
                From
            </div>
            <div class="day-time">
                <input type="text" class="cht-required time-picker" value="" autocomplete="off" name="cht_date_and_time_settings[__count__][start_time]" id="start_time___count__" />
            </div>
            <div class="day-label">
                To
            </div>
            <div class="day-time">
                <input type="text" class="cht-required time-picker" value="" autocomplete="off" name="cht_date_and_time_settings[__count__][end_time]" id="end_time___count__" />
            </div>
            <div class="day-label time-data">
                <span class="gmt-data">GMT</span>
            </div>
            <div class="day-time time-data gtm-select">
                <div class="gmt-data">
                    <select class="select2-pending" name="cht_date_and_time_settings[__count__][gmt]" id="gmt___count___option">
                        <?php echo chaty_timezone_choice("", false );?>
                    </select>
                </div>
            </div>
            <div class="day-buttons">
                <a class="remove-page-option" href="javascript:;">
                    <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(2.26764 0.0615997) rotate(45)" fill="white"></rect>
                        <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(13.3198 1.649) rotate(135)" fill="white"></rect>
                    </svg>
                </a>
            </div>
            <div class="clear"></div>
        </div>
        <?php if(!$this->is_pro()) { ?>
            <div class="chaty-pro-feature">
                <a target="_blank" href="<?php echo esc_url($this->getUpgradeMenuItemUrl()) ?>">
                    <?php esc_attr_e('Activate your license key', CHT_OPT); ?>
                </a>
            </div>
        <?php } ?>
    </div>
</div>

<div class="chaty-page-options-html" style="display: none">
    <div class="chaty-page-option">
        <div class="url-content">
            <div class="url-select">
                <select class="cht-required" name="cht_page_settings[__count__][shown_on]" id="url_shown_on___count___option">
                    <option value="show_on">Show on</option>
                    <option value="not_show_on">Don't show on</option>
                </select>
            </div>
            <div class="url-option">
                <select class="url-options cht-required" name="cht_page_settings[__count__][option]" id="url_rules___count___option">
                    <option selected="selected" disabled value="">Select Rule</option>
                    <?php foreach($url_options as $key=>$value) { ?>
                        <option value="<?php esc_attr_e($key) ?>"><?php esc_attr_e($value) ?></option>';
                        <?php
                    } ?>
                </select>
            </div>
            <div class="url-box">
                <span class='chaty-url'><?php esc_attr_e(site_url("/")); ?></span>
            </div>
            <div class="url-values">
                <input type="text" class="cht-required" value="" name="cht_page_settings[__count__][value]" id="url_rules___count___value" />
            </div>
            <div class="url-buttons">
                <a class="remove-chaty" href="javascript:;">
                    <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(2.26764 0.0615997) rotate(45)" fill="white"></rect>
                        <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(13.3198 1.649) rotate(135)" fill="white"></rect>
                    </svg>
                </a>
                <a class="add-chaty-option" href="javascript:;">
                    <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(2.26764 0.0615997) rotate(45)" fill="white"></rect>
                        <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(13.3198 1.649) rotate(135)" fill="white"></rect>
                    </svg>
                </a>
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
        <?php if(!$this->is_pro()) { ?>
            <div class="chaty-pro-feature">
                <a target="_blank" href="<?php echo esc_url($this->getUpgradeMenuItemUrl()) ?>">
                    <?php esc_attr_e('Activate your license key', CHT_OPT); ?>
                </a>
            </div>
        <?php } ?>
    </div>
</div>
<div class="custom-traffic-rules-html" style="display: none">
    <div class="custom-traffic-rule" data-id="__count__">
        <div class="traffic-option">
            <select name="chaty_custom_traffic_rules[__count__][url_option]">
                <option value="contain">Contains</option>
                <option value="not_contain">Not contains</option>
            </select>
        </div>
        <div class="traffic-url">
            <input type="text" name="chaty_custom_traffic_rules[__count__][url_value]" placeholder="https://www.example.com" />
        </div>
        <div class="traffic-action">
            <a class="remove-traffic-option" href="javascript:;">
                <svg width="14" height="13" viewBox="0 0 14 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(2.26764 0.0615997) rotate(45)" fill="white"></rect>
                    <rect width="15.6301" height="2.24494" rx="1.12247" transform="translate(13.3198 1.649) rotate(135)" fill="white"></rect>
                </svg>
            </a>
        </div>
    </div>
</div>