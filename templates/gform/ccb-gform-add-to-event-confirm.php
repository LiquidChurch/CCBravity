<div id='gform_confirmation_wrapper' class='gform_confirmation_wrapper '>
    <div id='gform_confirmation_message' class='gform_confirmation_message gform_confirmation_message'>
        <div class="ccb-add-to-event-success">
            <p>
                <?php
                if (isset($_SESSION['ccb_plugin']['new_user_created']) && ($_SESSION['ccb_plugin']['new_user_created'] === true)) {
                    echo __('A new user profile has been created for you, user details will be sent to you via email shortly.', 'ccb-gravity');
                    unset($_SESSION['ccb_plugin']['new_user_created']);
                }
                ?>
            </p>
            <p>
                <?php
                echo __('Thank you for registering for this event', 'ccb-gravity');
//                echo __('Thank you for registering to the event, if you want to register to another event, ' .
//                    'then please click the link below link and submit the form again with the required details.', 'ccb-gravity');
                ?>
            </p>
<!--            <p>-->
<!--                <a href="--><?php //echo get_page_uri() . '#gf_' . $this->get('form_id') ?><!--">--><?php //echo __('Register Again', 'ccb-gravity') ?><!--</a>-->
<!--            </p>-->
        </div>
    </div>
</div>