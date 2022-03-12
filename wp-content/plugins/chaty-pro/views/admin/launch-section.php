<?php
if (!defined('ABSPATH')) { exit; }
?>
<section class="section" id="launch-section">
    <h1 class="section-title">
        <strong><?php esc_attr_e('Launch it!', CHT_OPT);?></strong>
    </h1>

    <div class="form-horizontal">
        <input type="hidden" name="cht_active" value="0"  >
        <div class="form-horizontal__item flex-center">
            <label class="form-horizontal__item-label"><?php esc_attr_e('Active', CHT_OPT);?>:</label>
            <div>
                <label class="switch">
                    <span class="switch__label"><?php esc_attr_e('Off', CHT_OPT);?></span>
                    <?php $cht_active = get_option('cht_active'.$this->widget_index) ?>
                    <input data-gramm_editor="false" type="checkbox" class="cht_active" name="cht_active" value="1" <?php checked($cht_active, 1) ?> >
                    <span class="switch__styled"></span>
                    <span class="switch__label"><?php esc_attr_e('On', CHT_OPT);?></span>
                </label>
            </div>
        </div>
    </div>
    <input type="hidden" name="nonce" value="<?php esc_attr_e(wp_create_nonce("chaty_plugin_nonce")) ?>">
    <input type="hidden" name="cht_token" value="<?php esc_attr_e(get_option("cht_token")); ?>">
    <div class="text-center">
        <button class="btn-save">
            <?php esc_attr_e('Save Changes', CHT_OPT); ?>
        </button>
        <?php if(!empty($this->widget_index) && $this->widget_index != '_new_widget') { ?>
            <a href="javascript:;" class="remove-chaty-widget remove-chaty-options">Remove</a>
        <?php } ?>
    </div>
</section>
<?php
$created_on = get_option('cht_created_on'.$this->widget_index);
if($created_on === false) {
    $created_on = "";
}
?>
<input type="hidden" name="cht_created_on" value="<?php echo esc_attr($created_on) ?>" />
