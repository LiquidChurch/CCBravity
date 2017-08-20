<?php

/**
 * CCB GRAVITY API Event Attendance Profile
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_attendance_profile extends CCB_GRAVITY_api_main
{
    protected $api_name = "attendance_profile";
    protected $api_req_str = "srv=attendance_profile";
    protected $api_url = "";
    protected $api_fields;

	/**
	 * CCB_GRAVITY_api_attendance_profile constructor.
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
    public function gform_api_map()
    {
        $this->map_fields();
        $this->mod_req_str();
        $this->call_ccb_api();
        $this->process_api_response();
    }

	/**
	 * Map Gravity Forms / CCB API Fields
	 *
	 * @return WP_Error
	 */
    public function map_fields()
    {
        if (!isset($this->plugin->gravity_render->gform_api_field)) {
            return new WP_Error('not_found', 'Form Post fields are not present');
        }

        $post_fields = $this->plugin->gravity_render->gform_api_field;
        $event_profile = $this->plugin->gravity_api_get_event_profile->api_response_arr;

        if (!isset($post_fields['event.id'])) {
            return new WP_Error('not_found', 'Event ID values are required');
        }

        if (isset($event_profile['ccb_api']['response']['events']['event']['start_datetime'])) {

            $event_occurence = date('Y-m-d', strtotime($event_profile['ccb_api']['response']['events']['event']['start_datetime']));
        } else {
            return new WP_Error('not_found', 'Event Occurrence not found');
        }

        $this->api_fields = array(
            'id' => isset($post_fields['event.id']) ? $post_fields['event.id'] : '',
            'occurrence' => isset($event_occurence) ? $event_occurence : '',
        );
    }

	/**
	 * Build CCB URL String
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