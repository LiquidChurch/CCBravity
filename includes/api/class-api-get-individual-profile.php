<?php

/**
 * CCB GRAVITY API Get Individual Profile
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_get_individual_profile extends CCB_GRAVITY_api_main
{
    protected $api_name = "individual_profile_from_id";
    protected $api_req_str = "srv=individual_profile_from_id";
    protected $api_url = "";
    protected $api_fields;

	/**
	 * CCB_GRAVITY_api_get_individual_profile constructor.
	 *
	 * @param $plugin
	 */
    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

	/**
	 * @return array
	 */
    public function gform_api_map()
    {
        $this->map_fields();
        $this->mod_req_str();
        $this->call_ccb_api();
        $this->process_api_response();

        return array(
            'user_profile' => CCB_GRAVITY_fetch_session_data::get_user_profile_data($this->api_response_arr),
            'api_response_arr' => $this->api_response_arr,
            'api_error' => $this->api_error,
        );
    }

	/**
	 * @return WP_Error
	 */
    public function map_fields()
    {
        if (!isset($_POST['individual_id'])) {
            return new WP_Error('not_found', 'User ID and Event ID values are required');
        }

        $post_fields = $_POST;


        $this->api_fields = array(
            'individual_id' => isset($post_fields['individual_id']) ? $post_fields['individual_id'] : '',
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