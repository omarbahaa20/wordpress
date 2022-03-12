<?php
$total_widgets = $this->get_total_widgets();
$deleted_list = get_option("chaty_deleted_settings");
if(empty($deleted_list) || !is_array($deleted_list)) {
    $deleted_list = array();
}
$chaty_widget = [];
$chaty_widgets = get_option("chaty_total_settings");
if (!empty($chaty_widgets) && $chaty_widgets != null && is_numeric($chaty_widgets) && $chaty_widgets > 0) {
    for ($i = 1; $i <= $chaty_widgets; $i++) {
        if(!in_array($i, $deleted_list)) {
            $chaty_widget[] = $i;
        }
    }
}
$chaty_widgets = array();
$widget = "";
$is_deleted = get_option("cht_is_default_deleted");
if($is_deleted === false) {
    $cht_widget_title = get_option("cht_widget_title");
    $cht_widget_title = empty($cht_widget_title)?"Widget-1":$cht_widget_title;
    $status = get_option("cht_active");
    $date = get_option("cht_created_on");
    $date_status = ($date === false || empty($date))?0:1;
    $widget = array(
        'title' => $cht_widget_title,
        'index' => 0,
        'nonce' => wp_create_nonce("chaty_remove__0"),
        'status' => $status,
        'created_on' => $date
    );
    $chaty_widgets[] = $widget;
}
if(!empty($chaty_widget)) {
    foreach($chaty_widget as $i) {
        $cht_widget_title = get_option("cht_widget_title_".$i);
        if(empty($cht_widget_title) || $cht_widget_title == "" || $cht_widget_title == null) {
            $cht_widget_title = "Settings Widget #".($i+$total_widgets);
        } else {
            $cht_widget_title = "Settings ".$cht_widget_title;
        }
        $status = get_option("cht_active_".$i);
        $date = get_option("cht_created_on_".$i);
        $widget = array(
            'title' => $cht_widget_title,
            'index' => $i,
            'nonce' => wp_create_nonce("chaty_remove__".$i),
            'status' => $status,
            'created_on' => $date
        );
        $chaty_widgets[] = $widget;
        $date_status = (($date === false || empty($date)) && !$date_status)?0:1;
    }
}
//echo "<pre>"; print_r($chaty_widgets); die;
?>
<div class="wrap">
    <h2></h2>
    <div class="container" dir="ltr">
        <header class="header">
            <img src="<?php echo esc_url(plugins_url('../../admin/assets/images/logo.svg', __FILE__)); ?>" alt="Chaty" class="logo">
            <div class="mobile-button">
                <?php if (!$this->data_check() && !$this->is_pro()) { ?>
                    <a class="btn-white" href="<?php echo esc_url($this->getUpgradeMenuItemUrl()); ?>">
                        <?php esc_attr_e('Create New Widget', CHT_OPT); ?>
                    </a>
                    <a class="btn-red" target="_blank" href="<?php echo esc_url($this->getUpgradeMenuItemUrl()); ?>">
                        <?php esc_attr_e('Enter License Key', CHT_OPT); ?>
                        <svg width="17" height="19" viewBox="0 0 17 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.4674 7.42523L11.8646 0.128021C11.7548 0.128021 11.6449 0 11.4252 0C11.3154 0 11.0956 0 10.9858 0.128021L9.44777 1.92032C9.22806 2.17636 9.22806 2.56042 9.33791 2.81647L11.7548 6.017H0.549289C0.219716 6.017 0 6.27304 0 6.6571V9.21753C0 9.60159 0.219716 9.85763 0.549289 9.85763H11.8646L9.44777 13.0582C9.22806 13.3142 9.22806 13.6983 9.44777 13.9543L11.0956 15.6186C11.2055 15.7466 11.3154 15.7466 11.4252 15.7466C11.5351 15.7466 11.7548 15.6186 11.8646 15.4906L17.4674 8.19336C17.5772 8.06534 17.5772 7.68127 17.4674 7.42523Z" transform="translate(0.701416 18.3653) rotate(-90)" fill="white"/>
                        </svg>
                    </a>
                <?php } else { ?>
                    <a class="btn-white" href="<?php echo esc_url(admin_url("admin.php?page=chaty-widget-settings")) ?>">
                        <?php esc_attr_e('Create New Widget', CHT_OPT); ?>
                    </a>
                <?php } ?>
            </div>
            <?php settings_errors(); ?>
            <div class="ml-auto">
                <?php if ($this->data_check() && $this->is_pro()) { ?>
                    <a class="btn-red" target="_blank" href="<?php echo esc_url($this->getUpgradeMenuItemUrl()); ?>">
                        <?php esc_attr_e('Renew now', CHT_OPT); ?>
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.0746 9.2C2.5746 7 4.2746 5 6.4746 5H6.9746V6.5C6.9746 6.7 7.0746 6.9 7.2746 7C7.4746 7 7.6746 7 7.7746 6.9L10.7746 3.9C10.9746 3.7 10.9746 3.4 10.7746 3.2L7.7746 0.2C7.6746 0 7.4746 0 7.2746 0C7.0746 0.1 6.9746 0.3 6.9746 0.5V2H6.4746C2.5746 2 -0.525402 5.4 0.0745975 9.4C0.274598 10.9 1.0746 12.3 2.1746 13.3C2.3746 13.5 2.6746 13.5 2.8746 13.3L4.2746 11.9C4.4746 11.7 4.4746 11.4 4.2746 11.2C3.5746 10.6 3.1746 10 3.0746 9.2Z" fill="white"/>
                            <path d="M8.95 0.15C8.75 -0.0500001 8.45 -0.0500001 8.25 0.15L6.85 1.55C6.65 1.75 6.65 2.05 6.85 2.25C7.35 2.75 7.75 3.35 7.95 4.15C8.45 6.35 6.75 8.35 4.55 8.35H4.05V6.85C4.05 6.65 3.95 6.45 3.75 6.35C3.55 6.25 3.35 6.35 3.15 6.55L0.15 9.55C-0.0500001 9.75 -0.0500001 10.05 0.15 10.25L3.15 13.25C3.35 13.35 3.55 13.35 3.75 13.35C3.95 13.25 4.05 13.05 4.05 12.85V11.35H4.55C8.45 11.35 11.55 7.95 10.95 3.95C10.75 2.45 10.05 1.15 8.95 0.15Z" transform="translate(4.92456 2.64999)" fill="white"/>
                        </svg>
                    </a>
                    <?php if ($this->is_pro()) {
                        $active_license = $this->active_license();
                        ?>
                        <p class="text_update" style="color:#fff; left: 0px;"><?php esc_attr_e('Your Pro plan expires on', CHT_OPT); ?> <?php esc_attr_e(date('F jS, Y', strtotime($active_license))); ?></p>
                    <?php } ?>
                <?php } else if ($this->is_expired()) {
                    $licenseKey = $this->get_token();
                    $expired_on = $this->is_expired()
                    ?>
                    <span class="expired-message">Your pro plan has expired on <?php esc_attr_e(date('F jS, Y', strtotime($expired_on))) ?></span>
                    <a target="_blank" href="<?php echo esc_url(CHT_CHATY_PLUGIN_URL . "/checkout/?edd_license_key=" . $licenseKey . "&download_id=" . CHT_CHATY_PLUGIN_ID) ?>" class="renew-button"><?php esc_attr_e("Renew Now", CHT_OPT) ?></a>
                <?php } else if (!$this->data_check() && !$this->is_pro()) { ?>
                    <a class="btn-red" target="_blank" href="<?php echo esc_url($this->getUpgradeMenuItemUrl()); ?>">
                        <?php esc_attr_e('Enter License Key', CHT_OPT); ?>
                        <svg width="17" height="19" viewBox="0 0 17 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.4674 7.42523L11.8646 0.128021C11.7548 0.128021 11.6449 0 11.4252 0C11.3154 0 11.0956 0 10.9858 0.128021L9.44777 1.92032C9.22806 2.17636 9.22806 2.56042 9.33791 2.81647L11.7548 6.017H0.549289C0.219716 6.017 0 6.27304 0 6.6571V9.21753C0 9.60159 0.219716 9.85763 0.549289 9.85763H11.8646L9.44777 13.0582C9.22806 13.3142 9.22806 13.6983 9.44777 13.9543L11.0956 15.6186C11.2055 15.7466 11.3154 15.7466 11.4252 15.7466C11.5351 15.7466 11.7548 15.6186 11.8646 15.4906L17.4674 8.19336C17.5772 8.06534 17.5772 7.68127 17.4674 7.42523Z" transform="translate(0.701416 18.3653) rotate(-90)" fill="white"/>
                        </svg>
                    </a>
                <?php } else {
                    $licenseKey = get_option("cht_token");
                    $licenseData = $this->getLicenseKeyInformation($licenseKey);
                    $isLicenseActive = 0;
                    if (!empty($licenseData)) {
                        if ($licenseData['license'] == "valid") {
                            $isLicenseActive = 1;
                        }
                        if ($licenseData['license'] == "expired") {
                            $isLicenseActive = 2;
                        }
                    }
                    $newVersion = "";
                    $active_license = $this->active_license();
                    if ($isLicenseActive == 1 && $licenseData['expires'] == "lifetime") { ?>
                        <p class="plan_date">You have a lifetime license</p>
                    <?php } else { ?>
                        <p class="plan_date">Your pro plan is valid until <?php esc_attr_e(date('F jS, Y', strtotime($active_license))); ?></p>
                    <?php } ?>
                <?php } ?>
            </div>
        </header>
        <div class="chaty-table">
            <div class="chaty-table-header">
                <span>Dashboard</span>
                <div class="pull-right">
                    <?php if (!$this->data_check() && !$this->is_pro()) { ?>
                        <a class="cht-add-new-widget" href="<?php echo esc_url($this->getUpgradeMenuItemUrl()); ?>">
                            <?php esc_attr_e('Create New Widget', CHT_OPT); ?>
                        </a>
                    <?php } else { ?>
                        <a class="cht-add-new-widget" href="<?php echo esc_url(admin_url("admin.php?page=chaty-widget-settings&copy-from=")) ?>">
                            <?php esc_attr_e('Create New Widget', CHT_OPT); ?>
                        </a>
                    <?php } ?>
                </div>
                <div class="clear"></div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th class="fix-width"><?php esc_html_e("Status", CHT_OPT); ?></th>
                        <th><?php esc_html_e("Widget name", CHT_OPT); ?></th>
                        <?php if($date_status) { ?>
                            <th><?php esc_html_e("Created On", CHT_OPT); ?></th>
                        <?php } ?>
                        <th class="fix-width"><?php esc_html_e("Actions", CHT_OPT); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($chaty_widgets as $widget) { ?>
                        <tr id="widget_<?php echo esc_attr($widget['index']) ?>" data-widget="<?php echo esc_attr($widget['index']) ?>" data-nonce="<?php echo esc_attr($widget['nonce']) ?>">
                            <td>
                                <label class="chaty-switch" for="trigger_on_time<?php echo esc_attr($widget['index']) ?>">
                                    <input type="checkbox" class="change-chaty-status" name="chaty_trigger_on_time" id="trigger_on_time<?php echo esc_attr($widget['index']) ?>" value="yes" <?php checked($widget['status'], 1) ?>>
                                    <div class="chaty-slider round"></div>
                                </label>
                            </td>
                            <td class="widget-title"><?php echo esc_attr($widget['title']) ?></td>
                            <?php if($date_status) { ?>
                                <?php if(!empty($widget['created_on'])) {?>
                                    <td><?php echo esc_attr(date("F j, Y"), strtotime($widget['created_on'])) ?></td>
                                <?php } else { ?>
                                    <td>&nbsp;</td>
                                <?php } ?>
                            <?php } ?>
                            <td class="chaty-actions">
                                <a href="<?php echo esc_url(admin_url("admin.php?page=chaty-app&widget=".esc_attr($widget['index']))) ?>"><span class="cht-tooltip" data-title="Edit"><span class="dashicons dashicons-edit-large"></span></span></a>
                                <a class="clone-widget" href="javascript:;"><span class="cht-tooltip" data-title="Duplicate"><span class="dashicons dashicons-admin-page"></span></span></a>
                                <a class="remove-widget" href="javascript:;"><span class="cht-tooltip" data-title="Delete"><span class="dashicons dashicons-trash"></span></span></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="chaty-popup" id="clone-widget">
    <div class="chaty-popup-outer"></div>
    <div class="chaty-popup-inner popup-pos-bottom">
        <div class="chaty-popup-content">
            <div class="chaty-popup-close">
                <a href="javascript:void(0)" class="close-delete-pop close-chaty-popup-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M15.6 15.5c-.53.53-1.38.53-1.91 0L8.05 9.87 2.31 15.6c-.53.53-1.38.53-1.91 0s-.53-1.38 0-1.9l5.65-5.64L.4 2.4C-.13 1.87-.13 1.02.4.49s1.38-.53 1.91 0l5.64 5.63L13.69.39c.53-.53 1.38-.53 1.91 0s.53 1.38 0 1.91L9.94 7.94l5.66 5.65c.52.53.52 1.38 0 1.91z"></path></svg>
                </a>
            </div>
            <form class="" action="<?php echo admin_url("admin.php?page=chaty-widget-settings") ?>" method="get">
                <div class="a-card a-card--normal">
                    <div class="chaty-popup-header">
                        Duplicate Widget?
                    </div>
                    <div class="chaty-popup-body">
                        Please select a name for your new duplicate widget
                        <div class="chaty-popup-input">
                            <input type="text" name="widget_title" id="widget_title">
                            <input type="hidden" name="copy-from" id="widget_clone_id">
                            <input type="hidden" name="page" value="chaty-widget-settings">
                        </div>
                    </div>
                    <input type="hidden" id="delete_widget_id" value="">
                    <div class="chaty-popup-footer">
                        <button type="submit" class="btn btn-primary">Create Widget</button>
                        <button type="button" class="btn btn-default close-chaty-popup-btn">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="chaty-popup" id="delete-widget">
    <div class="chaty-popup-outer"></div>
    <div class="chaty-popup-inner popup-pos-bottom">
        <div class="chaty-popup-content">
            <div class="chaty-popup-close">
                <a href="javascript:void(0)" class="close-delete-pop close-chaty-popup-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M15.6 15.5c-.53.53-1.38.53-1.91 0L8.05 9.87 2.31 15.6c-.53.53-1.38.53-1.91 0s-.53-1.38 0-1.9l5.65-5.64L.4 2.4C-.13 1.87-.13 1.02.4.49s1.38-.53 1.91 0l5.64 5.63L13.69.39c.53-.53 1.38-.53 1.91 0s.53 1.38 0 1.91L9.94 7.94l5.66 5.65c.52.53.52 1.38 0 1.91z"></path></svg>
                </a>
            </div>
            <div class="a-card a-card--normal">
                <div class="chaty-popup-header">
                    Delete Widget?
                </div>
                <div class="chaty-popup-body">
                    Are you sure you want to delete this widget?
                </div>
                <input type="hidden" id="delete_widget_id" value="">
                <div class="chaty-popup-footer">
                    <button type="button" class="btn btn-primary" id="delete-widget-btn" onclick="javascript:removeWidgetItem();">Delete Widget</button>
                    <button type="button" class="btn btn-default close-chaty-popup-btn">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
var dataWidget = -1;
jQuery(document).ready(function () {

    jQuery(document).on("click", ".clone-widget", function(){
        <?php if (!$this->data_check() && !$this->is_pro()) { ?>
            window.location = "<?php echo esc_url($this->getUpgradeMenuItemUrl()); ?>";
        <?php } else { ?>
            var WidgetId = jQuery(this).closest("tr").data("widget");
            jQuery("#widget_clone_id").val(WidgetId);
            var WidgetName = jQuery(this).closest("tr").find(".widget-title").text();
            jQuery("#widget_title").val(WidgetName);
            jQuery("#clone-widget").show();
        <?php } ?>
    });

    jQuery(document).on("click", ".change-chaty-status", function(e){
        dataWidget = jQuery(this).closest("tr").data("widget");
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'change_chaty_widget_status',
                widget_nonce: jQuery("#widget_"+dataWidget).data("nonce"),
                widget_index: "_"+jQuery("#widget_"+dataWidget).data("widget")
            },
            beforeSend: function (xhr) {

            },
            success: function (res) {

            },
            error: function (xhr, status, error) {

            }
        });
    });

    jQuery(document).on("click", ".remove-widget", function(){
        dataWidget = jQuery(this).closest("tr").data("widget");
        jQuery("#delete-widget").show();
    });
});

function removeWidgetItem() {
    if(dataWidget == -1) {
        return;
    }
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'remove_chaty_widget',
            widget_nonce: jQuery("#widget_"+dataWidget).data("nonce"),
            widget_index: "_"+jQuery("#widget_"+dataWidget).data("widget")
        },
        beforeSend: function (xhr) {

        },
        success: function (res) {
            window.location = res;
        },
        error: function (xhr, status, error) {

        }
    });
}
</script>