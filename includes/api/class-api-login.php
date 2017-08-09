<?php

/**
 * CCB GRAVITY API Call
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_login extends CCB_GRAVITY_api_main
{
    protected $api_name = "individual_profile_from_login_password";
    protected $api_req_str = "srv=individual_profile_from_login_password";
    protected $api_url = "";
    protected $api_fields;

    public static $link_api_fields = [
        'login'       => ['required' => TRUE],
        'password' => ['required' => TRUE],
    ];

    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

    public function gform_api_map()
    {
        $this->map_fields();
        $this->call_ccb_api();
        $this->process_api_response();
        CCB_GRAVITY_manage_session::save_api_login_session($this->api_response_arr, $this->api_error);
    }

    public function map_fields() {
        if(!isset($this->plugin->gravity_render->gform_api_field)) {
            return new WP_Error('not_found', 'Form Post fields are not present');
        }

        $post_fields = $this->plugin->gravity_render->gform_api_field;

        if(empty($post_fields[$this->api_name . '.login']) || empty($this->api_name . '.password')) {
            return new WP_Error('not_found', 'Username and Password fields are required');
        }

        $this->api_fields = array(
            'login' => isset($post_fields[$this->api_name . '.login']) ? $post_fields[$this->api_name . '.login'] : '',
            'password' => isset($post_fields[$this->api_name . '.password']) ? $post_fields[$this->api_name . '.password'] : '',
        );
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
//        ccb_debug('add', array($this->api_name . ' -> raw_api_response', json_encode($this->api_response), 0, 'ccb-api-calls'));
    }

}