<?php

/**
 * CCB GRAVITY ajax handler
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_ajax_handler extends CCB_GRAVITY_Abstract
{
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

    public function sync_entry_with_ccb($entry_id = null)
    {

        $entry_id = !empty($entry_id) ? $entry_id : (isset($_POST['entry_id']) ? $_POST['entry_id'] : null);

        if ($entry_id == null) {
            die('no entry id provided');
        }

        $api_data = gform_get_meta($entry_id, 'api_data');

        if (isset($api_data['api_sync']) && $api_data['api_sync'] == true) {
            echo json_encode(array(
                'error' => false,
                'duplicate' => true,
                'success' => true,
                'status' => 'success',
                'msg' => 'Data already synced to CCB API',
                'api_data' => $api_data
            ));
            die();
        }

        $api_data_backup = gform_get_meta($entry_id, 'api_data_before_sync');
        $primary_data = isset($api_data['primary']) ? $api_data['primary'] : array();
        $secondary_data = isset($api_data['secondary']) ? $api_data['secondary'] : array();

        $api_events = array(
            'create_primary_individual' => false,
            'individual_only' => false,
            'individual_family' => false,
            'individual_group' => false,
            'individual_community_group' => false,
        );

        if (empty($primary_data['individual_id'])) {
            $api_events['create_primary_individual'] = true;
        }

        if (empty($primary_data['register_type']) || $primary_data['register_type'] == 'individual') {

            $api_events['individual_only'] = true;

        } elseif ($primary_data['register_type'] == 'family') {

            $api_events['individual_family'] = true;

        } elseif ($primary_data['register_type'] == 'liquid_group') {

            $api_events['individual_group'] = true;

        } elseif ($primary_data['register_type'] == 'community_group') {

            $api_events['individual_community_group'] = true;

        }

        if (empty($api_data_backup)) {
            gform_update_meta($entry_id, 'api_data_before_sync', $api_data);
        }
        $api_data = $this->plugin->gravity_api_sync_ccb->sync_gform_data_ccb_api($api_data, $api_events);
        gform_update_meta($entry_id, 'api_data', $api_data);

        echo json_encode($api_data);
        die();
    }

    public function get_individual_profile()
    {
        $api_response = $this->plugin->gravity_api_get_individual_profile->gform_api_map();
        echo json_encode($api_response);
        exit();
    }

    public function get_group_participants()
    {
        $group_id = isset($_POST['group_id']) ? $_POST['group_id'] : null;

        if ($group_id == null) {
            $api_response = array(
                'error' => true,
                'error_details' => array(
                    'msg' => 'No group id provided'
                )
            );
        } else {
            if (isset($_SESSION['ccb_plugin']['group_participants'][$group_id])) {
                $api_response = $_SESSION['ccb_plugin']['group_participants'][$group_id];
            } else {
                $api_response = $this->plugin->gravity_api_group_participants->gform_api_map();
            }
        }

        echo json_encode($api_response);
        exit();
    }

    public function check_api_user_logged_in()
    {
        $response = array(
            'success' => true,
            'api_user_logged_in' => CCB_GRAVITY_manage_session::if_user_logged_in(),
            'user_profile' => isset($_SESSION['ccb_plugin']['user_profile']) ? $_SESSION['ccb_plugin']['user_profile'] : array(),
        );
        echo json_encode($response);
        exit();
    }
}