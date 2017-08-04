<?php

/**
 * CCB GRAVITY API Create Individual
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_add_to_event extends CCB_GRAVITY_api_main
{
    protected $api_name = "add_individual_to_event";
    protected $api_req_str = "srv=add_individual_to_event";
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

        if (!isset($post_fields['individual.id']) || !isset($post_fields['event.id'])) {
            return new WP_Error('not_found', 'User ID and Event ID values are required');
        }

        $this->api_fields = array(
            'id' => isset($post_fields['individual.id']) ? $post_fields['individual.id'] : '',
            'event_id' => isset($post_fields['event.id']) ? $post_fields['event.id'] : '',
            'status' => 'add',
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

    public function ccb_sync_add_to_event($indv_data, $event_data, $extra_info = array()) {

        $this->api_error = array();
        $this->api_response = array();
        $this->api_response_arr = array();

        $this->api_fields = array(
            'id' => isset($indv_data['individual_id']) ? $indv_data['individual_id'] : (isset($indv_data['id']) ? $indv_data['id'] : ''),
            'event_id' => isset($event_data['event_id']) ? $event_data['event_id'] : '',
            'status' => 'add',
        );

        $this->mod_req_str();

        $this->call_ccb_api();
        $this->process_api_response();

    }

}