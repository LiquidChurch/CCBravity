<?php

/**
 * CCB GRAVITY API Call
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_individual_groups extends CCB_GRAVITY_api_main
{
    protected $api_name = "individual_groups";
    protected $api_req_str = "srv=individual_groups";
    protected $api_url = "";

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
        CCB_GRAVITY_manage_session::save_api_individual_groups_session($this->api_response_arr, $this->api_error);
    }

    public function map_fields() {
        $this->api_fields = array(
            'individual_id' => isset($_SESSION['ccb_plugin']['user_profile']['individual.id']) ? $_SESSION['ccb_plugin']['user_profile']['individual.id'] : '',
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
                'body' => $this->api_fields,
                'cookies' => array()
            ));

        $this->api_response = wp_remote_post($this->api_url, $this->api_args);
        ccb_debug('add', array($this->api_name . ' -> raw_api_response', json_encode($this->api_response), 0, 'API'));
    }

}