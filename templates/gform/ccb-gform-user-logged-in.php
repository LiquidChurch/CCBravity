<div id='gform_confirmation_wrapper' class='gform_confirmation_wrapper '>
    <div id='gform_confirmation_message' class='gform_confirmation_message gform_confirmation_message'>
        <div class="ccb-logged-in">
            <?php
            $user_data       = $this->get('user_data');
            $welcome_message = sprintf(__('<h4>Welcome %s, For event registration please fill out the below form.</h4>', 'ccb-gravity'), $user_data['individual.full_name']);
            echo $welcome_message;
            ?>
            <p style="padding-left: 0;">
                <a href="<?php echo get_page_uri() . '?ccb-action=logout&ccb-verify=' . wp_create_nonce('ccb-gravity') ?>"
                   class="btn btn-info"
                ><?php echo __('Disconnect from LiquidConnect', 'ccb-gravity') ?></a>
            </p>
        </div>
    </div>
</div>