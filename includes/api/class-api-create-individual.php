<?php

/**
 * CCB GRAVITY API Create Individual
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_create_individual extends CCB_GRAVITY_api_main
{
    protected $api_name = "create_individual";
    protected $api_req_str = "srv=create_individual";
    protected $api_url = "";
    protected $api_fields;

    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

    public function gform_api_map($save_user_session = false)
    {
        $this->map_fields();
        $this->call_ccb_api();
        $this->process_api_response();
        if ($save_user_session == true) {
            CCB_GRAVITY_manage_session::save_api_login_session($this->api_response_arr, $this->api_error, $add_sess = array(
                'new_user_created' => true
            ));
        }
    }

    public function map_fields()
    {
        if (!isset($this->plugin->gravity_render->gform_api_field)) {
            return new WP_Error('not_found', 'Form Post fields are not present');
        }

        $post_fields = $this->plugin->gravity_render->gform_api_field;

        if (empty($post_fields['individual.first_name']) || empty($post_fields['individual.last_name'])) {
            return new WP_Error('not_found', 'First Name and Last Name are required');
        }

        $this->api_fields = array(
            'first_name' => isset($post_fields['individual.first_name']) ? $post_fields['individual.first_name'] : '',
            'last_name' => isset($post_fields['individual.last_name']) ? $post_fields['individual.last_name'] : '',
            'email' => isset($post_fields['individual.email']) ? $post_fields['individual.email'] : '',
            'contact_phone' => isset($post_fields['individual.phone']) ? $post_fields['individual.phone'] : '',
            'mailing_street_address' => isset($post_fields['individual.address.line_1']) ? $post_fields['individual.address.line_1'] : '',
            'mailing_city' => isset($post_fields['individual.address.city']) ? $post_fields['individual.address.city'] : '',
            'mailing_state' => isset($post_fields['individual.address.state']) ? $post_fields['individual.address.state'] : '',
            'mailing_zip' => isset($post_fields['individual.address.zip']) ? $post_fields['individual.address.zip'] : '',
        );

        if (isset($post_fields['individual.family.id']) && ($post_fields['individual.family.id'] != '')) {
            $this->api_fields = array_merge($this->api_fields, array('family_id' => $post_fields['individual.family.id']));
        }
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

    public function get_new_profile_id()
    {
        if (isset($this->api_response_arr['ccb_api']['response']['individuals']['individual']['id'])) {
            return $this->api_response_arr['ccb_api']['response']['individuals']['individual']['id'];
        }
        return null;
    }

    public function ccb_sync_create_individual($data, $event, $extra_data = array())
    {

        $this->api_error = array();
        $this->api_response = array();
        $this->api_response_arr = array();

        $this->api_fields = array(
            'first_name' => isset($data['fname']) ? $data['fname'] : '',
            'last_name' => isset($data['lname']) ? $data['lname'] : '',
            'email' => isset($data['email']) ? $data['email'] : '',
        );

        if($event == 'secondary_individual_family') {
            $this->api_fields = array_merge($this->api_fields, array('family_id' => $extra_data['family_id']));
        }

        $this->call_ccb_api();
        $this->process_api_response();
    }

}