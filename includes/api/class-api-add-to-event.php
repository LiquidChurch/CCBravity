<?php

/**
 * CCB GRAVITY API Add Individual To Event
 *
 * Adds an individual to an event using the CCB API.
 *
 * @since   1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_add_to_event extends CCB_GRAVITY_api_main
{
    protected $api_name    = "add_individual_to_event";
    protected $api_req_str = "srv=add_individual_to_event";
    protected $api_url     = "";
    protected $api_fields;

    public static $link_api_fields = [
        'id'                    => ['required' => TRUE],
        'event_id'              => ['required' => TRUE],
        'family_individual_ids' => ['required' => FALSE],
        'group_individual_ids'  => ['required' => FALSE],
        'list_groups'           => ['required' => FALSE],
        'status'                => [
            'required' => TRUE,
            'option'   => ['add', 'invite', 'decline', 'maybe', 'request']
        ]
    ];

    /**
     * CCB_GRAVITY_api_add_to_event constructor.
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
     * Build CCB API Request String
     */
    public function mod_req_str()
    {
        $add_req_str       = http_build_query($this->api_fields);
        $this->api_req_str .= '&' . $add_req_str;
    }

    /**
     * Map Gravity Forms / CCB Fields
     *
     * @return WP_Error
     */
    public function map_fields()
    {
        if ( ! isset($this->plugin->gravity_render->gform_api_field))
        {
            return new WP_Error('not_found', 'Form Post fields are not present');
        }

        $post_fields = $this->plugin->gravity_render->gform_api_field;

        if ( ! isset($post_fields['individual.id']) || ! isset($post_fields['event.id']))
        {
            return new WP_Error('not_found', 'User ID and Event ID values are required');
        }

        $this->api_fields = array(
            'id'       => isset($post_fields['individual.id']) ? $post_fields['individual.id'] : '',
            'event_id' => isset($post_fields['event.id']) ? $post_fields['event.id'] : '',
            'status'   => 'add',
        );
    }

    /**
     * Call CCB API
     */
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
                'body'        => array(),
                'cookies'     => array()
            ));

        $this->api_response = wp_remote_post($this->api_url, $this->api_args);
//        ccb_debug('add', array($this->api_name . ' -> raw_api_response', json_encode($this->api_response), 0, 'ccb-api-calls'));
    }

    /**
     * Sync Add to Event to CCB
     *
     * @param       $indv_data
     * @param       $event_data
     * @param array $extra_info
     */
    public function ccb_sync_add_to_event($data)
    {

        $this->api_error        = array();
        $this->api_response     = array();
        $this->api_response_arr = array();

        $this->api_fields = array(
            'id'       => isset($data['event_individual_id']) ? $data['event_individual_id'] : (isset($data['id']) ? $data['id'] : ''),
            'event_id' => isset($data['event_id']) ? $data['event_id'] : '',
            'status'   => isset($data['event_status']) ? $data['event_status'] : 'add',
        );

        $this->mod_req_str();

        $this->call_ccb_api();
        $this->process_api_response();

    }

}