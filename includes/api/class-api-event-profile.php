<?php

/**
 * CCB GRAVITY API Event Profile
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_event_profile extends CCB_GRAVITY_api_main
{
    protected $api_name = "event_profile";
    protected $api_req_str = "srv=event_profile";
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
    }

    public function mod_req_str() {
        $add_req_str = http_build_query($this->api_fields);
        $this->api_req_str .= '&' . $add_req_str;
    }

    public function map_fields()
    {
        if (!isset($this->plugin->gravity_render->gform_api_field)) {
            return new WP_Error('not_found', 'Form Post fields are not present');
        }

        $post_fields = $this->plugin->gravity_render->gform_api_field;

        if (!isset($post_fields['add_individual_to_event.event_id'])) {
            return new WP_Error('not_found', 'Event ID values are required');
        }

        $this->api_fields = array(
            'id' => isset($post_fields['add_individual_to_event.event_id']) ? $post_fields['add_individual_to_event.event_id'] : '',
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
                'body' => array(),
                'cookies' => array()
            ));

        $this->api_response = wp_remote_post($this->api_url, $this->api_args);
        ccb_debug('add', array($this->api_name . ' -> raw_api_response', json_encode($this->api_response), 0, 'API'));
    }

}