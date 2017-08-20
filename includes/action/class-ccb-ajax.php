<?php

/**
 * CCB GRAVITY ajax handler
 *
 * @since   1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_ajax_handler extends CCB_GRAVITY_Abstract
{
	/**
	 * CCB_GRAVITY_ajax_handler constructor.
	 *
	 * @param object $plugin
	 */
    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

    public function hooks()
    {
        add_action('wp_ajax_check_api_user_logged_in', array($this, 'check_api_user_logged_in'));
        add_action('wp_ajax_nopriv_check_api_user_logged_in', array($this, 'check_api_user_logged_in'));

        add_action('wp_ajax_get_individual_profile', array($this, 'get_individual_profile'));
        add_action('wp_ajax_nopriv_get_individual_profile', array($this, 'get_individual_profile'));

        add_action('wp_ajax_get_group_participants', array($this, 'get_group_participants'));
        add_action('wp_ajax_nopriv_get_group_participants', array($this, 'get_group_participants'));

        add_action('wp_ajax_sync_entry_with_ccb', array($this, 'sync_entry_with_ccb'));

    }

	/**
	 * @param null $entry_id
	 */
    public function sync_entry_with_ccb($entry_id = NULL)
    {

        $entry_id = ! empty($entry_id) ? $entry_id : (isset($_POST['entry_id']) ? $_POST['entry_id'] : NULL);

        if ($entry_id == NULL)
        {
            die('no entry id provided');
        }

        $api_data = gform_get_meta($entry_id, 'api_data');

        if (isset($api_data['api_sync']) && $api_data['api_sync'] == TRUE)
        {
            echo json_encode(array(
                'error'     => FALSE,
                'duplicate' => TRUE,
                'success'   => TRUE,
                'status'    => 'success',
                'msg'       => 'Data already synced to CCB API',
                'api_data'  => $api_data
            ));
            die();
        }

        $api_data_backup = gform_get_meta($entry_id, 'api_data_before_sync');
        $primary_data    = isset($api_data['primary']) ? $api_data['primary'] : array();
        $secondary_data  = isset($api_data['secondary']) ? $api_data['secondary'] : array();

        $api_events = array(
            'create_primary_individual'  => FALSE,
            'individual_only'            => FALSE,
            'individual_family'          => FALSE,
            'individual_group'           => FALSE,
            'individual_community_group' => FALSE,
        );

        if (empty($primary_data['individual_id']))
        {
            $api_events['create_primary_individual'] = TRUE;
        }

        if (empty($primary_data['register_type']) || $primary_data['register_type'] == 'individual')
        {

            $api_events['individual_only'] = TRUE;

        }
        else if ($primary_data['register_type'] == 'family')
        {

            $api_events['individual_family'] = TRUE;

        }
        else if ($primary_data['register_type'] == 'liquid_group')
        {

            $api_events['individual_group'] = TRUE;

        }
        else if ($primary_data['register_type'] == 'community_group')
        {

            $api_events['individual_community_group'] = TRUE;

        }

        if (empty($api_data_backup))
        {
            gform_update_meta($entry_id, 'api_data_before_sync', $api_data);
        }
        $api_data = $this->plugin->gravity_api_sync_ccb->sync_gform_data_ccb_api($api_data, $api_events);
        gform_update_meta($entry_id, 'api_data', $api_data);

        if (isset($api_data['primary']['event_id']))
        {
            if (has_action('lo_ccb_cron_event_member_sync'))
            {
                do_action('lo_ccb_cron_event_member_sync', $api_data['primary']['event_id']);
            }
        }

        if ($this->ajax_call)
        {
            echo json_encode($api_data);
            die();
        }
    }

    public function get_individual_profile()
    {
        $api_response = $this->plugin->gravity_api_get_individual_profile->gform_api_map();
        echo json_encode($api_response);
        exit();
    }

    public function get_group_participants()
    {
        $group_id = isset($_POST['group_id']) ? $_POST['group_id'] : NULL;

        if ($group_id == NULL)
        {
            $api_response = array(
                'error'         => TRUE,
                'error_details' => array(
                    'msg' => 'No group id provided'
                )
            );
        }
        else
        {
            if (isset($_SESSION['ccb_plugin']['group_participants'][$group_id]))
            {
                $api_response = $_SESSION['ccb_plugin']['group_participants'][$group_id];
            }
            else
            {
                $api_response = $this->plugin->gravity_api_group_participants->gform_api_map();
            }
        }

        echo json_encode($api_response);
        exit();
    }

    public function check_api_user_logged_in()
    {
        $response = array(
            'success'            => TRUE,
            'api_user_logged_in' => CCB_GRAVITY_manage_session::if_user_logged_in(),
            'user_profile'       => isset($_SESSION['ccb_plugin']['user_profile']) ? $_SESSION['ccb_plugin']['user_profile'] : array(),
        );
        echo json_encode($response);
        exit();
    }
}