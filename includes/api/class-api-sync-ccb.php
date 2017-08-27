<?php

/**
 * CCB GRAVITY API Call
 *
 * @since   1.0.0
 * @package CCB Gravity Functionality
 *
 * @var $plugin
 */
class CCB_GRAVITY_api_sync_ccb
{
    /**
     * CCB_GRAVITY_api_sync_ccb constructor.
     *
     * @param object $plugin
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;

        $this->api_data   = NULL;
        $this->api_events = NULL;
        $this->api_sync   = TRUE;
        $this->api_error  = [];

        $this->ajax_call = defined('DOING_AJAX') && DOING_AJAX;
        $this->api_args  = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('ccbgravity:HR%0ZPAeZM@n')
            )
        );
    }

    /**
     * Sync Gravity Forms Data to CCB API
     *
     * @param $api_data
     * @param $api_events
     *
     * @return mixed
     */
    public function sync_gform_data_ccb_api($api_data, $api_events)
    {
        $this->api_data   = $api_data;
        $this->api_events = $api_events;

        /***
         * adding primary user to the event
         */
        $this->_sync_gform_data_create_individual();

        /**
         * create community group and
         * add user to community group
         *
         * condition to check if community group create api call is required or it's created or not
         */
        if ($api_events['individual_community_group'] == TRUE)
        {
            $this->_sync_gform_data_create_group();

            $this->_sync_gform_data_add_to_group();
        }

        /***
         * adding primary user to the event
         */
        $this->_sync_gform_data_add_to_event();

        /**
         * condition to check if add group api call is required
         */
        if ($api_events['add_individual_to_group'] == TRUE)
        {
            $this->_sync_gform_data_add_to_group();
        }

        /**
         * adding existing family / group members to event
         */
        if ( ! empty($this->api_data['primary']['extra_individual_ids']))
        {
            foreach ($this->api_data['primary']['extra_individual_ids'] as $index => $extra_individual_id)
            {
                $this->_sync_gform_data_add_to_event('family|group', $extra_individual_id);
            }
        }

        /**
         * conditions for secondary individual creation
         */
        $create_individual_api_event = '';
        if ($api_events['individual_only'] == TRUE)
        {
            $create_individual_api_event = 'secondary_individual_only';
        }
        else if ($api_events['individual_family'] == TRUE && $this->api_data['primary']['family_id'] != '')
        {
            $create_individual_api_event = 'secondary_individual_family';
        }
        else if (($api_events['individual_group'] == TRUE || $api_events['individual_community_group'] == TRUE) && $this->api_data['primary']['group_id'] != '')
        {
            $individual_group_api_event = 'secondary_individual_group';
        }

        #1
        if ( ! empty($secondary_data))
        {
            foreach ($secondary_data['individual'] as $index => $item)
            {

                /**
                 * condition to check secondary individual is created or not
                 */
                if ( ! isset($item['api_sync']['create_individual']['success']) || ($item['api_sync']['create_individual']['success'] == FALSE))
                {
                    /**
                     * API Call
                     */
                    $this->_sync_gform_data_create_individual('secondary', $index);
                }

                /**
                 * if secondary individual is successfully created
                 * for adding secondary members to related group
                 */
                if ($secondary_data['individual'][$index]['api_sync']['create_individual']['success'] == TRUE)
                {

                    if ($individual_group_api_event == 'secondary_individual_group')
                    {

                        if (( ! isset($secondary_data['individual'][$index]['api_sync']['add_to_group']['success'])) || ($secondary_data['individual'][$index]['api_sync']['add_to_group']['success'] == FALSE))
                        {

                            /**
                             * API call for adding secondary members to group
                             */
                            $this->_sync_gform_data_add_to_group('secondary', $index);
                        }
                    }

                    /**
                     * adding secondary user to event
                     */
                    $this->_sync_gform_data_add_to_event('secondary', $index);
                }
            }
        }

        $api_data['primary']   = $this->api_data['primary'];
        $api_data['secondary'] = $this->api_data['secondary'];
        $api_data['api_sync']  = $this->api_sync;
        $api_data['error']     = $this->api_error;

        return $api_data;
    }

    /**
     * Sync Gravity Form Data - Add to Event
     *
     * @return null
     */
    public function _sync_gform_data_add_to_event($member_type = 'primary', $index = 0)
    {
        $primary_data   = isset($this->api_data['primary']) ? $this->api_data['primary'] : array();
        $secondary_data = isset($this->api_data['secondary']) ? $this->api_data['secondary'] : array();

        if ($member_type == 'primary')
        {
            $synced   = isset($primary_data['api_sync']['add_to_event']['success']) && ($primary_data['api_sync']['add_to_event']['success'] == TRUE);
            $api_data = $primary_data;
        }
        else if ($member_type == 'secondary')
        {
            $synced   = isset($secondary_data['individual'][$index]['api_sync']['add_to_event']['success']) && ($secondary_data['individual'][$index]['api_sync']['add_to_event']['success'] == TRUE);
            $api_data = array_merge($primary_data, ['event_individual_id' => $secondary_data['individual'][$index]['event_individual_id']]);
        }
        else if ($member_type == 'family|group')
        {
            $synced   = isset($primary_data['family|group'][$index]['api_sync']['add_to_event']['success']) && ($primary_data['family|group'][$index]['api_sync']['add_to_event']['success'] == TRUE);
            $api_data = array_merge($primary_data, ['event_individual_id' => $index]);
        }

        if ( ! empty($primary_data['event_id']) && ( ! $synced))
        {
            /**
             * API Call for adding individual to event
             */
            $this->plugin->gravity_api_add_to_event->ccb_sync_add_to_event($api_data);

            if ( ! empty($this->plugin->gravity_api_add_to_event->api_error))
            {
                $this->api_sync = FALSE;

                if ($member_type == 'primary')
                {
                    $this->api_data['primary']['api_sync']['add_to_event']['success'] = FALSE;
                    $this->api_data['primary']['api_sync']['add_to_event']['error']   = $this->plugin->gravity_api_add_to_event->api_error;
                }
                else if ($member_type == 'secondary')
                {
                    $this->api_data['secondary'][$index]['api_sync']['add_to_event']['success'] = FALSE;
                    $this->api_data['secondary'][$index]['api_sync']['add_to_event']['error']   = $this->plugin->gravity_api_add_to_event->api_error;
                }
                else if ($member_type == 'family|group')
                {
                    $this->api_data['primary']['family|group'][$index]['api_sync']['add_to_event']['success'] = FALSE;
                    $this->api_data['primary']['family|group'][$index]['api_sync']['add_to_event']['error']   = $this->plugin->gravity_api_add_to_event->api_error;
                }

                $this->api_error = $this->send_response_after_single_api_call_fail($this->plugin->gravity_api_add_to_event->api_error);
            }
            else
            {
                if ($member_type == 'primary')
                {
                    $this->api_data['primary']['api_sync']['add_to_event']['success'] = TRUE;
                }
                else if ($member_type == 'secondary')
                {
                    $this->api_data['secondary'][$index]['api_sync']['add_to_event']['success'] = TRUE;
                }
                else if ($member_type == 'family|group')
                {
                    $this->api_data['primary']['family|group'][$index]['api_sync']['add_to_event']['success'] = TRUE;
                }
            }
        }
    }

    /**
     * Sync Gravity Form Data - Add to Group
     *
     * @return null
     */
    public function _sync_gform_data_add_to_group($member_type = 'primary', $index = 0)
    {
        $primary_data = isset($this->api_data['primary']) ? $this->api_data['primary'] : array();
        $secondary_data = isset($this->api_data['secondary']) ? $this->api_data['secondary'] : array();

        if ($member_type == 'primary')
        {
            $synced   = isset($primary_data['api_sync']['add_to_group']['success']) && ($primary_data['api_sync']['add_to_group']['success'] == TRUE);
            $api_data = $primary_data;
        }
        else if ($member_type == 'secondary')
        {
            $synced   = isset($secondary_data['individual'][$index]['api_sync']['add_to_group']['success']) && ($secondary_data['individual'][$index]['api_sync']['add_to_group']['success'] == TRUE);
            $api_data = array_merge($primary_data, ['event_individual_id' => $secondary_data['individual'][$index]['group_individual_id']]);
        }

        if ( ! empty($primary_data['group_id']) && !$synced)
        {

            /**
             * API call to add primary user to the community group
             */
            $this->plugin->gravity_api_add_individual_to_group->ccb_sync_add_to_group($primary_data);

            if ( ! empty($this->plugin->gravity_api_add_individual_to_group->api_error))
            {
                $this->api_sync = FALSE;

                if ($member_type == 'primary')
                {
                    $this->api_data['primary']['api_sync']['add_to_group']['success'] = FALSE;
                    $this->api_data['primary']['api_sync']['add_to_group']['error']   = $this->plugin->gravity_api_add_individual_to_group->api_error;
                }
                else if ($member_type == 'secondary')
                {
                    $this->api_data['secondary']['individual'][$index]['api_sync']['add_to_group']['success'] = FALSE;
                    $this->api_data['secondary']['individual'][$index]['api_sync']['add_to_group']['error']   = $this->plugin->gravity_api_add_individual_to_group->api_error;
                }

                $this->api_error[] = $this->send_response_after_single_api_call_fail($this->plugin->gravity_api_add_individual_to_group->api_error);
            }
            else
            {
                if ($member_type == 'primary')
                {
                    $this->api_data['primary']['api_sync']['add_to_group']['success'] = TRUE;
                }
                else if ($member_type == 'secondary')
                {
                    $this->api_data['secondary']['individual'][$index]['api_sync']['add_to_group']['success'] = TRUE;
                }
            }

        }
    }

    public function _sync_gform_data_create_group()
    {
        $primary_data = isset($this->api_data['primary']) ? $this->api_data['primary'] : array();

        if ( ! isset($primary_data['api_sync']['create_community_group']['success']) || ($primary_data['api_sync']['create_community_group']['success'] == FALSE))
        {
            $campus_id = $primary_data['event_set'];
            $campus_id = explode('|', $campus_id);
            $campus_id = $campus_id[0];

            /**
             * API call for creating community group
             */

            $this->plugin->gravity_api_create_group->ccb_sync_create_group($data = array(
                'campus_id'      => $campus_id,
                'name'           => $primary_data['community_group_name'],
                'main_leader_id' => CCB_GRAVITY_Functionality::CCB_COMMUNITY_GROUP_LEADER_ID
            ));

            if ( ! empty($this->plugin->gravity_api_create_group->api_error))
            {
                $this->api_sync = FALSE;

                $this->api_data['primary']['api_sync']['create_community_group']['success'] = FALSE;
                $this->api_data['primary']['api_sync']['create_community_group']['error']   = $this->plugin->gravity_api_create_group->api_error;

                $this->api_error[] = $this->send_response_after_single_api_call_fail($this->plugin->gravity_api_create_group->api_error);
            }
            else
            {
                $this->api_data['primary']['group_id'] = $this->get_group_id($this->plugin->gravity_api_create_group->api_response_arr);

                $this->api_data['primary']['api_sync']['create_community_group']['success'] = TRUE;
            }
        }
    }

    public function _sync_gform_data_create_individual($member_type = 'primary', $index = 0)
    {
        $primary_data   = isset($this->api_data['primary']) ? $this->api_data['primary'] : array();
        $secondary_data = isset($this->api_data['secondary']) ? $this->api_data['secondary'] : array();

        if ($member_type == 'primary')
        {
            $synced              = isset($primary_data['api_sync']['add_to_event']['success']) && ($primary_data['api_sync']['add_to_event']['success'] == TRUE);
            $event_individual_id = isset($primary_data['event_individual_id']) ? $primary_data['event_individual_id'] : NULL;
            $api_data            = $primary_data;
        }
        else if ($member_type == 'secondary')
        {
            $synced              = isset($secondary_data['individual'][$index]['api_sync']['add_to_event']['success']) && ($secondary_data['individual'][$index]['api_sync']['add_to_event']['success'] == TRUE);
            $event_individual_id = isset($secondary_data['individual'][$index]['event_individual_id']) ? $secondary_data['individual'][$index]['event_individual_id'] : NULL;
            $api_data            = $secondary_data['individual'][$index];
        }

        /**
         * condition to check if create individual api call is required or it's created or not
         */
        if (empty($event_individual_id) && ! $synced)
        {
            /**
             * API Call for creating individual
             */
            $this->plugin->gravity_api_create_individual->ccb_sync_create_individual($api_data);

            if ( ! empty($this->plugin->gravity_api_create_individual->api_error))
            {
                $this->api_sync = FALSE;

                if ($member_type == 'primary')
                {
                    $this->api_data['primary']['api_sync']['create_individual']['success'] = FALSE;
                    $this->api_data['primary']['api_sync']['create_individual']['error']   = $this->plugin->gravity_api_create_individual->api_error;
                }
                else if ($member_type == 'secondary')
                {
                    $this->api_data['secondary'][$index]['api_sync']['create_individual']['success'] = FALSE;
                    $this->api_data['secondary'][$index]['api_sync']['create_individual']['error']   = $this->plugin->gravity_api_create_individual->api_error;
                }

                $this->api_error = $this->send_response_after_single_api_call_fail($this->plugin->gravity_api_create_individual->api_error);
            }
            else
            {
                $individual_id = $this->get_individual_id($this->plugin->gravity_api_create_individual->api_response_arr);

                if ($member_type == 'primary')
                {
                    $this->api_data['primary']['event_individual_id'] = $individual_id;
                    $this->api_data['primary']['group_individual_id'] = $individual_id;

                    $this->api_data['primary']['family_id'] = $this->get_family_id($this->plugin->gravity_api_create_individual->api_response_arr);

                    $this->api_data['primary']['api_sync']['create_individual']['success'] = TRUE;
                }
                else if ($member_type == 'secondary')
                {
                    $this->api_data['secondary'][$index]['event_individual_id'] = $individual_id;
                    $this->api_data['secondary'][$index]['group_individual_id'] = $individual_id;

                    $this->api_data['secondary'][$index]['family_id'] = $this->get_family_id($this->plugin->gravity_api_create_individual->api_response_arr);

                    $this->api_data['secondary'][$index]['api_sync']['create_individual']['success'] = TRUE;
                }
            }
        }

    }

    /**
     * Send Response After Single API Call Fail
     *
     * @param $error
     *
     * @return array
     */
    public function send_response_after_single_api_call_fail($error)
    {
        return array(
            'error'         => TRUE,
            'success'       => FALSE,
            'error_details' => $error
        );
    }

    /**
     * Get Individual ID
     *
     * @param $data
     *
     * @return null
     */
    public function get_individual_id($data)
    {
        if (isset($data['ccb_api']['response']['individuals']['individual']['id']))
        {
            return $data['ccb_api']['response']['individuals']['individual']['id'];
        }

        return NULL;
    }

    /**
     * Get Family ID
     *
     * @param $data
     *
     * @return null
     */
    public function get_family_id($data)
    {
        if (isset($data['ccb_api']['response']['individuals']['individual']['family']['id']))
        {
            return $data['ccb_api']['response']['individuals']['individual']['family']['id'];
        }

        return NULL;
    }

    /**
     * Get Group ID
     *
     * @param $data
     *
     * @return null
     */
    public function get_group_id($data)
    {
        if (isset($data['ccb_api']['response']['groups']['group']['id']))
        {
            return $data['ccb_api']['response']['groups']['group']['id'];
        }

        return NULL;
    }
}