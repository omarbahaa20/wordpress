<?php
if (!defined('ABSPATH')) { exit; }
?>
<link type="text/css" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins" />
<div class="chaty-new-widget-wrap">
    <h2 class="text-center"><?php esc_attr_e( 'Unlock Chaty Analytics. What can you use it for?', CHT_OPT ); ?></h2>
    <div class="chaty-new-widget-row">
        <div class="chaty-features">
            <ul>
                <li>
                    <div class="chaty-feature analytics">
                        <div class="chaty-feature-top">
                            <img src="<?php echo CHT_PLUGIN_URL ?>/admin/assets/images/analytics-search.png" />
                        </div>
                        <div class="feature-description">Discover which chat channels are most commonly used</div>
                    </div>
                </li>
                <li>
                    <div class="chaty-feature analytics">
                        <div class="chaty-feature-top">
                            <img src="<?php echo CHT_PLUGIN_URL ?>/admin/assets/images/analytics-progress.png" />
                        </div>
                        <div class="feature-description">Get full stats on your widgets and turn data into actionable steps to increase conversation rate</div>
                    </div>
                </li>
                <li>
                    <div class="chaty-feature analytics">
                        <div class="chaty-feature-top">
                            <img src="<?php echo CHT_PLUGIN_URL ?>/admin/assets/images/analytics-unlock.png" />
                        </div>
                        <div class="feature-description">Unlock your widgets’ open-rate and find out how your call-to-action messages are performing</div>
                    </div>
                </li>
            </ul>
            <div class="clear clearfix"></div>
        </div>
        <div class="demo-buttons">
            <a href="<?php echo esc_url($this->getUpgradeMenuItemUrl()); ?>" class="new-upgrade-button">Activate your License key</a>
        </div>
    </div>
</div>