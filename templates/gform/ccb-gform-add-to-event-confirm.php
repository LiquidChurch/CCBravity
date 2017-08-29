<div id='gform_confirmation_wrapper' class='gform_confirmation_wrapper '>
    <div id='gform_confirmation_message' class='gform_confirmation_message gform_confirmation_message'>
        <div class="ccb-add-to-event-success">
            <h3>
                <?php
                if (isset($_SESSION['ccb_plugin']['new_user_created']) && ($_SESSION['ccb_plugin']['new_user_created'] === TRUE))
                {
                    echo __('A new user profile has been created for you, user details will be sent to you via email shortly.', 'ccb-gravity'); // TODO: Are we actually sending them an email? Or is this happening via CCB?
                    unset($_SESSION['ccb_plugin']['new_user_created']);
                }
                ?>
            </h3>
            <h3>
                <?php
                $default_confirmation = $this->get('default_confirmation');
                if ( ! empty($default_confirmation))
                {
                    echo $default_confirmation;
                }
                else
                {
                    echo __('Thank you for registering for this event!', 'ccb-gravity'); // TODO: Need to flesh out this message, style.
                }
                ?>
            </h3>
        </div>
    </div>
</div>