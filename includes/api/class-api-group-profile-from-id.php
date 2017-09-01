<?php

/**
 * CCB GRAVITY API Call
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_group_profile_from_id extends CCB_GRAVITY_api_main
{
    protected $api_name = "group_profile_from_id";
    protected $api_req_str = "srv=group_profile_from_id";
    protected $api_url = "";

	/**
	 * CCB_GRAVITY_api_group_profile_from_id constructor.
	 *
	 * @param $plugin
	 */
    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

	/**
	 * Gravity Forms API Map
	 */
    public function gform_api_map($group_id = null)
    {
        $this->map_fields($group_id);
        $this->mod_req_str();
        $this->call_ccb_api();
        $this->process_api_response();
    }

	/**
	 * Map Fields
	 */
    public function map_fields($group_id) {
        $this->api_fields = array(
            'id' => $group_id,
        );
    }

	/**
	 * Build the string for the CCB API
	 */
    public function mod_req_str()
    {
        $add_req_str = http_build_query($this->api_fields);
        $this->api_req_str .= '&' . $add_req_str;
    }

    /**
     * Call CCB API
     */
    public function call_ccb_api()
    {
        $this->api_url = $this->api_base . '?' . $this->api_req_str;

        $this->api_args = array_merge(
            $this->api_args,
            array(
                'method' => 'POST',
                'timeout' => 300,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'body' => $this->api_fields,
                'cookies' => array()
            ));

        $this->api_response = wp_remote_post($this->api_url, $this->api_args);
//        ccb_debug('add', array($this->api_name . ' -> raw_api_response', json_encode($this->api_response), 0, 'ccb-api-calls'));
    }

}