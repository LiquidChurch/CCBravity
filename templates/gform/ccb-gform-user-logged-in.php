<div id='gform_confirmation_wrapper' class='gform_confirmation_wrapper '>
    <div id='gform_confirmation_message' class='gform_confirmation_message gform_confirmation_message'>
        <div class="ccb-logged-in">
            <?php
            $user_data       = $this->get('user_data');
            $welcome_message = sprintf(__('<h3>Welcome %s.</h3>', 'ccb-gravity'), $user_data['individual.full_name']);
            echo $welcome_message;
            ?>
            <p style="padding-left: 0;">
                <a href="<?php echo get_page_uri() . '?ccb-action=logout&ccb-verify=' . wp_create_nonce('ccb-gravity') ?>"
                   class="btn btn-info"
                ><?php echo __('Logout of LiquidConnect', 'ccb-gravity') ?></a>
            </p>
        </div>
    </div>
</div>