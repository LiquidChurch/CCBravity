<div class="ccb-gform">
    <div class="ccb-login-form-section">
        <?php echo $this->output('gform'); ?>
    </div>
</div>

<script type="text/javascript">

    var login_form_rendered = false;
    var all_ccb_form = <?php echo json_encode($this->get('all_ccb_form')) ?>;

    (function ($)
    {
        /**
         * gform post render event
         */
        jQuery(document).bind('gform_post_render', function (event, form_id, current_page)
        {
            if (all_ccb_form[form_id] == 'individual_profile_from_login_password' && login_form_rendered == false)
            {
            }
        });

    })(jQuery);

</script>