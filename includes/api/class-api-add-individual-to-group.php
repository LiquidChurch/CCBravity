<?php

/**
 * CCB GRAVITY API Add Individual to Group
 *
 * Adds an individual to a CCB Group using the CCB API.
 *
 * @since   1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_api_add_individual_to_group extends CCB_GRAVITY_api_main
{
    protected $api_name    = "add_individual_to_group";
    protected $api_req_str = "srv=add_individual_to_group";
    protected $api_url     = "";
    protected $api_fields;

    public static $link_api_fields = [
        'id'       => ['required' => TRUE],
        'group_id' => ['required' => TRUE],
        'status'   => ['required' => FALSE],
    ];

    /**
     * CCB_GRAVITY_api_add_individual_to_group constructor.
     *
     * @param $plugin
     */
    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

    /**
     * Gravity Forms API Map Error
     *
     * If API Map doesn't exist, die
     */
    public function gform_api_map()
    {
        die('need implementation'); // TODO: We need to handle this somehow.
    }

    /**
     * Build the string for CCB API Call
     */
    public function mod_req_str()
    {
        $add_req_str       = http_build_query($this->api_fields);
        $this->api_req_str .= '&' . $add_req_str;
    }

    public function map_fields()
    {
    }

    /**
     * Call the CCB API
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
     * Add Individual to Group via CCB API
     *
     * @param       $data
     * @param       $event
     * @param array $extra_info
     */
    public function ccb_sync_add_to_group($data)
    {

        $this->api_error        = array();
        $this->api_response     = array();
        $this->api_response_arr = array();

        $this->api_fields = array(
            'id'       => isset($data['group_individual_id']) ? $data['group_individual_id'] : '',
            'group_id' => isset($data['group_id']) ? $data['group_id'] : '',
            'status'   => isset($data['group_status']) ? $data['group_status'] : 'add',
        );

        $this->mod_req_str();

        $this->call_ccb_api();
        $this->process_api_response();

    }

}