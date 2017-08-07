<?php

/**
 * CCB GRAVITY API Create Individual
 *
 * @since   1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_create_individual extends CCB_GRAVITY_api_main
{
    protected $api_name    = "create_individual";
    protected $api_req_str = "srv=create_individual";
    protected $api_url     = "";
    protected $api_fields;

    public static $link_api_fields = [
        'first_name'             => ['required' => TRUE],
        'last_name'              => ['required' => TRUE],
        'middle_name'            => ['required' => FALSE],
        'salutation'             => ['required' => FALSE],
        'suffix'                 => ['required' => FALSE],
        'campus_id'              => ['required' => FALSE],
        'family_id'              => ['required' => FALSE],
        'family_position'        => ['required' => FALSE],
        'marital_status'         => ['required' => FALSE],
        'gender'                 => ['required' => FALSE],
        'birthday'               => ['required' => FALSE],
        'anniversary'            => ['required' => FALSE],
        'email'                  => ['required' => FALSE],
        'mailing_street_address' => ['required' => FALSE],
        'mailing_city'           => ['required' => FALSE],
        'mailing_state'          => ['required' => FALSE],
        'mailing_zip'            => ['required' => FALSE],
        'mailing_country'        => ['required' => FALSE],
        'home_street_address'    => ['required' => FALSE],
        'home_city'              => ['required' => FALSE],
        'home_state'             => ['required' => FALSE],
        'home_zip'               => ['required' => FALSE],
        'home_country'           => ['required' => FALSE],
        'work_street_address'    => ['required' => FALSE],
        'work_city'              => ['required' => FALSE],
        'work_state'             => ['required' => FALSE],
        'work_zip'               => ['required' => FALSE],
        'work_country'           => ['required' => FALSE],
        'work_title'             => ['required' => FALSE],
        'contact_phone'          => ['required' => FALSE],
        'home_phone'             => ['required' => FALSE],
        'work_phone'             => ['required' => FALSE],
        'mobile_phone'           => ['required' => FALSE],
        'baptized'               => ['required' => FALSE],
        'creator_id'             => ['required' => FALSE],
    ];

    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

    public function gform_api_map($save_user_session = FALSE)
    {
        $this->map_fields();
        $this->call_ccb_api();
        $this->process_api_response();
        if ($save_user_session == TRUE)
        {
            CCB_GRAVITY_manage_session::save_api_login_session($this->api_response_arr, $this->api_error, $add_sess = array(
                'new_user_created' => TRUE
            ));
        }
    }

    public function map_fields()
    {
        if ( ! isset($this->plugin->gravity_render->gform_api_field))
        {
            return new WP_Error('not_found', 'Form Post fields are not present');
        }

        $post_fields = $this->plugin->gravity_render->gform_api_field;

        if (empty($post_fields['individual.first_name']) || empty($post_fields['individual.last_name']))
        {
            return new WP_Error('not_found', 'First Name and Last Name are required');
        }

        $this->api_fields = array(
            'first_name'             => isset($post_fields['individual.first_name']) ? $post_fields['individual.first_name'] : '',
            'last_name'              => isset($post_fields['individual.last_name']) ? $post_fields['individual.last_name'] : '',
            'email'                  => isset($post_fields['individual.email']) ? $post_fields['individual.email'] : '',
            'contact_phone'          => isset($post_fields['individual.phone']) ? $post_fields['individual.phone'] : '',
            'mailing_street_address' => isset($post_fields['individual.address.line_1']) ? $post_fields['individual.address.line_1'] : '',
            'mailing_city'           => isset($post_fields['individual.address.city']) ? $post_fields['individual.address.city'] : '',
            'mailing_state'          => isset($post_fields['individual.address.state']) ? $post_fields['individual.address.state'] : '',
            'mailing_zip'            => isset($post_fields['individual.address.zip']) ? $post_fields['individual.address.zip'] : '',
        );

        if (isset($post_fields['individual.family.id']) && ($post_fields['individual.family.id'] != ''))
        {
            $this->api_fields = array_merge($this->api_fields, array('family_id' => $post_fields['individual.family.id']));
        }
    }

    public function call_ccb_api()
    {
        $this->api_url  = $this->api_base . '?' . $this->api_req_str;
        $this->api_args = array_merge(
            $this->api_args,
            array(
                'method'      => 'POST',
                'timeout'     => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking'    => TRUE,
                'body'        => $this->api_fields,
                'cookies'     => array()
            ));

        $this->api_response = wp_remote_post($this->api_url, $this->api_args);
        ccb_debug('add', array($this->api_name . ' -> raw_api_response', json_encode($this->api_response), 0, 'API'));
    }

    public function get_new_profile_id()
    {
        if (isset($this->api_response_arr['ccb_api']['response']['individuals']['individual']['id']))
        {
            return $this->api_response_arr['ccb_api']['response']['individuals']['individual']['id'];
        }

        return NULL;
    }

    public function ccb_sync_create_individual($data, $event, $extra_data = array())
    {

        $this->api_error        = array();
        $this->api_response     = array();
        $this->api_response_arr = array();

        $this->api_fields = array(
            'first_name' => isset($data['fname']) ? $data['fname'] : '',
            'last_name'  => isset($data['lname']) ? $data['lname'] : '',
            'email'      => isset($data['email']) ? $data['email'] : '',
        );

        if ($event == 'secondary_individual_family')
        {
            $this->api_fields = array_merge($this->api_fields, array('family_id' => $extra_data['family_id']));
        }

        $this->call_ccb_api();
        $this->process_api_response();
    }

}