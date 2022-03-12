<?php
if (!defined('ABSPATH')) { exit; }
?>
<div class="upgrade-wrap">
    <a href="<?php echo esc_url(admin_url('admin.php?page=chaty-app')) ?>" class="link-back"><?php esc_attr_e('Back', CHT_OPT); ?></a>

    <div class="" id="upgrade-modal">
        <div class="easy-modal-inner">
            <div class="wrap wrap-licenses">
                <?php
                $class_name = "";
                $message = "";
                $m = filter_input(INPUT_GET, 'm', FILTER_SANITIZE_STRING);
                if (isset($m) && !empty($m)) {
                    switch ($m) {
                        case "error":
                            $class_name = "error";
                            $message = esc_attr__("Your license key is not valid", CHT_OPT);
                            break;
                        case "valid":
                            $class_name = "success";
                            $message = esc_attr__("Your license key is activated successfully", CHT_OPT);
                            break;
                        case "unactivated":
                            $class_name = "success";
                            $message = esc_attr__("Your license key is deactivated successfully", CHT_OPT);
                            break;
                        case "expired":
                            $class_name = "error";
                            $message = esc_attr__("Your license has been expired", CHT_OPT);
                            break;
                        case "invalid":
                            $class_name = "error";
                            $message = esc_attr__("Your request is not valid", CHT_OPT);
                            break;
                        case "no_activations":
                            $class_name = "error";
                            $message = esc_attr__("Your request is not valid", CHT_OPT);
                            break;
                    }
                    ?>
                    <div class='testimonial-<?php esc_html_e($class_name) ?>-message'>
                    <?php
                    if($m != "no_activations") {
                        esc_html_e($message);
                    } else {
                        ?>
                        Your license was activated for another domain, please visit your <a target="_blank" href="https://go.premio.io">Premio account</a>
                        <?php
                    }
                    ?>
                    </div>
                <?php } ?>
                <form action="" method="post" id="license_action_form">
                    <?php
                    delete_transient("cht_token_data");
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
                    ?>
                    <div class="license-key">
                        <div class="license-key-header">
                            <?php esc_attr_e("License Key", CHT_OPT) ?>
                        </div>
                        <div class="license-key-content">
                            <input type="text" value="<?php esc_attr_e($licenseKey) ?>" name="license_key">

                            <div class="license-key-message">
                                <?php if ($isLicenseActive) { ?>
                                    <button type="submit" class="button-secondary remove-testimonial-license-key"><?php esc_attr_e("Deactivate License", CHT_OPT) ?></button>
                                <?php } ?>
                                <input type="hidden" name="action" value="activate_deactivate_chaty_license_key">
                                <input type="hidden" id="license_action_type" name="chaty_license_action" value="">
                            </div>
                        </div>
                        <div class="license-key-footer">
                            <?php
                            if (!$isLicenseActive) {
                                esc_attr_e("To receive updates, please enter your valid Software Licensing license key.", CHT_OPT);
                            } else if ($isLicenseActive == 1 && $licenseData['expires'] == "lifetime") {
                                esc_attr_e("You have a lifetime license");
                            } else if ($isLicenseActive == 1) {
                                esc_attr_e("Your license will expire on " . date("d M, Y", strtotime($licenseData['expires'])));
                            } else if ($isLicenseActive == 2) {
                                ?> <span class='error-message'> <?php
                                    esc_attr_e("Your license has been expired on " . date("d M, Y", strtotime($licenseData['expires'])));
                                ?> </span> <?php
                            }
                            ?>
                        </div>
                    </div>
                    <button type="submit" id="submit" class="button button-primary save-testimonial-license-key"><?php esc_attr_e("Save Changes", CHT_OPT) ?></button>
                    <?php if ($isLicenseActive == 2) { ?>
                        <a target="_blank" href="<?php echo esc_url(CHT_CHATY_PLUGIN_URL."/checkout/?edd_license_key=".$licenseKey."&download_id=".CHT_CHATY_PLUGIN_ID) ?>" class="button button-primary renew-form-btn"><?php esc_attr_e("Renew Now", CHT_OPT) ?></a>
                    <?php } ?>
                    <input type="hidden" name="activate_token" value="<?php esc_attr_e(wp_create_nonce("chaty_activate_nonce")) ?>">
                    <input type="hidden" name="deactivate_token" value="<?php esc_attr_e(wp_create_nonce("chaty_deactivate_nonce")) ?>">
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function(){
        jQuery("#license_action_form").submit(function(){
            return false;
        });
        jQuery(".save-testimonial-license-key").on("click", function(){
            jQuery("#license_action_type").val("save");
            jQuery(this).attr("disabled",true);
            submitChatyLicenceForm();
        });
        jQuery(".remove-testimonial-license-key").on("click", function(){
            jQuery("#license_action_type").val("remove");
            jQuery(this).attr("disabled",true);
            submitChatyLicenceForm();
        });
    });

    function submitChatyLicenceForm() {
        formData = jQuery("#license_action_form").serialize();
        jQuery.ajax({
            url: "<?php echo esc_url(admin_url( 'admin-ajax.php' )); ?>",
            data: formData,
            type: "post",
            success: function(response) {
                response = response.slice(0, - 1);
                window.location = "<?php echo admin_url("admin.php?page=chaty-app-upgrade&m=") ?>"+response;
            }
        })
    }
</script>