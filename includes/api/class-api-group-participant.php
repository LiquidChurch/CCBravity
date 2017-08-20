<?php

/**
 * CCB GRAVITY API Get Group Participants
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_group_participants extends CCB_GRAVITY_api_main
{
    protected $api_name = "group_participants";
    protected $api_req_str = "srv=group_participants";
    protected $api_url = "";
    protected $api_fields;

    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

    public function gform_api_map()
    {
        $this->map_fields();
        $this->mod_req_str();
        $this->call_ccb_api();
        $this->process_api_response();
        $group_participants_details = CCB_GRAVITY_manage_session::save_api_group_participants_session($this->api_response_arr, $this->api_error, array('group_id' => $this->api_fields['id']));
        return $group_participants_details;
    }

    public function map_fields()
    {
        if (!isset($_POST['group_id'])) {
            return new WP_Error('not_found', 'Group ID is required');
        }

        $post_fields = $_POST;


        $this->api_fields = array(
            'id' => isset($post_fields['group_id']) ? $post_fields['group_id'] : '',
        );
    }

    public function mod_req_str()
    {
        $add_req_str = http_build_query($this->api_fields);
        $this->api_req_str .= '&' . $add_req_str;
    }

    public function call_ccb_api()
    {
        $this->api_url = $this->api_base . '?' . $this->api_req_str;
        $this->api_args = array_merge(
            $this->api_args,
            array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'body' => array(),
                'cookies' => array()
            ));

        $this->api_response = wp_remote_post($this->api_url, $this->api_args);
//        ccb_debug('add', array($this->api_name . ' -> raw_api_response', json_encode($this->api_response), 0, 'ccb-api-calls'));
    }

}