<?php
if (!defined('ABSPATH')) { exit; }

//echo $widget_index = $this->widget_index; die;
if(isset($_GET['copy-from']) && is_numeric($_GET['copy-from']) && $_GET['copy-from'] >= 0) {
    if($_GET['copy-from'] == 0) {
        $this->widget_index = "";
    } else {
        $this->widget_index = "_" . $_GET['copy-from'];
    }
}
//echo $this->widget_index; die;
if (!$this->is_pro()) {
    /* initialize with default values if key is not activated */
    if (get_option('cht_position'.$this->widget_index) == 'custom') {
        update_option('cht_position'.$this->widget_index, 'right');
    }
    $social = get_option('cht_numb_slug'.$this->widget_index);
    $social = explode(",", $social);
    $social = array_splice($social, 0, 3);
    $social = implode(',', $social);
    update_option('cht_numb_slug'.$this->widget_index, $social);
    if (get_option('cht_custom_color'.$this->widget_index) != '') {
        update_option('cht_custom_color'.$this->widget_index, '');
        update_option('cht_color'.$this->widget_index, '#A886CD');
    }
}
$is_pro = $this->is_pro();
$cht_token = get_option("cht_token");
$pro_class = (!$is_pro && $cht_token != '') ? 'none_pro' : '';
?>

<div class="container <?php esc_attr_e($pro_class) ?>" dir="ltr">
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
            <?php if (!$this->data_check() && !$this->is_pro()) { ?>
                <a class="btn-white" href="<?php echo esc_url($this->getUpgradeMenuItemUrl()); ?>">
                    <?php esc_attr_e('Create New Widget', CHT_OPT); ?>
                </a>
            <?php } else { ?>
                <a class="btn-white" href="<?php echo esc_url(admin_url("admin.php?page=chaty-widget-settings")) ?>">
                    <?php esc_attr_e('Create New Widget', CHT_OPT); ?>
                </a>
            <?php } ?>
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
    <div class="mobile-message">
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
            <span class="expired-message"><a target="_blank" href="<?php echo esc_url(CHT_CHATY_PLUGIN_URL . "/checkout/?edd_license_key=" . $licenseKey . "&download_id=" . CHT_CHATY_PLUGIN_ID) ?>" class="renew-button"><?php esc_attr_e("Renew Now", CHT_OPT) ?></a> Your pro plan has expired on <?php esc_attr_e(date('F jS, Y', strtotime($expired_on))) ?></span>
        <?php } else if (!$this->data_check() && !$this->is_pro()) { ?>

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

    <main class="main">
        <form id="cht-form" action="options.php" method="POST" enctype="multipart/form-data">
            <?php
            settings_fields($this->plugin_slug);

            /* Social channel list section */
            require_once 'channels-section.php';

            /* Customize widget section */
            require_once 'customize-widget-section.php';

            /* Customize widget section */
            require_once 'trigger-and-target.php';

            /* Widget launch section */
            require_once 'launch-section.php';

            /* form submit button */
            submit_button(null, null, null, false);
            ?>
        </form>

        <input type="hidden" id="widget_index" value="<?php esc_attr_e($this->widget_index) ?>" />
    </main>
</div>

<?php require_once 'popup.php'; ?>
<?php if(0 && isset($_GET['page']) && $_GET['page'] == "chaty-widget-settings" && !isset($_GET['copy-from'])) { ?>
    <div class="chaty-popup-form">
        <div class="chaty-popup-overlay"></div>
        <div class="chaty-popup-content">
            <div class="popup-title">Create a new widget</div>
            <div class="popup-description">Would you like to load settings from an existing widget?</div>
            <form action="<?php echo admin_url("admin.php") ?>" method="get">
                <div class="select-field">
                    <input type="hidden" name="page" value="chaty-widget-settings" />
                    <select name="copy-from" >
                        <option value="">No thanks</option>
                        <?php
                        $total_widgets = $this->get_total_widgets();
                        $menu_text = esc_attr__('Settings', CHT_OPT);
                        if($total_widgets > 0) {
                            $menu_text = "Settings Widget #1";
                            $total_widgets = 1;

                            $text = get_option("cht_widget_title");
                            if(!empty($text) && $text != "" && $text != null ) {
                                $menu_text = "Settings ".$text;
                            }
                        } else {
                            $total_widgets = 0;
                        }

                        $deleted_list = get_option("chaty_deleted_settings");
                        if(empty($deleted_list) || !is_array($deleted_list)) {
                            $deleted_list = array();
                        }

                        echo "<option value='0'>{$menu_text}</option>";
                        $chaty_widgets = get_option("chaty_total_settings");
                        if (!empty($chaty_widgets) && $chaty_widgets != null && is_numeric($chaty_widgets) && $chaty_widgets > 0) {
                            for ($i = 1; $i <= $chaty_widgets; $i++) {
                                if(!in_array($i, $deleted_list)) {
                                    $cht_widget_title = get_option("cht_widget_title_".$i);
                                    if(empty($cht_widget_title) || $cht_widget_title == "" || $cht_widget_title == null) {
                                        $cht_widget_title = "Settings Widget #".($i+$total_widgets);
                                    } else {
                                        $cht_widget_title = "Settings ".$cht_widget_title;
                                    }
                                    echo "<option value='{$i}'>{$cht_widget_title}</option>";
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="select-field-btn">
                    <button type="submit" class="popup-form-btn">Start Editing</button>
                </div>
            </form>
        </div>
    </div>
<?php } ?>