<?php

/**
 * CCB GRAVITY API Call
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 *
 * @var $plugin
 */
class CCB_GRAVITY_api_sync_ccb
{
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->ajax_call = defined('DOING_AJAX') && DOING_AJAX;
        $this->api_args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('ccbgravity:HR%0ZPAeZM@n')
            )
        );
    }

    public function sync_gform_data_ccb_api(&$api_data, &$api_events)
    {
        $primary_data = isset($api_data['primary']) ? $api_data['primary'] : array();
        $secondary_data = isset($api_data['secondary']) ? $api_data['secondary'] : array();
        $api_sync = true;
        $individual_group_api_event = '';
        $error = array();

        /**
         * codition to check if create individual api call is required or it's created or not
         */
        if (($api_events['create_primary_individual'] == true) && (!isset($primary_data['api_sync']['create_individual']['success']) || ($primary_data['api_sync']['create_individual']['success'] == false))) {

            $create_individual_api_event = 'create_primary_individual';

            /**
             * API Call for creating individual
             */
            $this->plugin->gravity_api_create_individual->ccb_sync_create_individual($primary_data, $create_individual_api_event);

            if (!empty($this->plugin->gravity_api_create_individual->api_error)) {
                $api_sync = false;
                $primary_data['api_sync']['create_individual']['success'] = false;
                $primary_data['api_sync']['create_individual']['error'] = $this->plugin->gravity_api_create_individual->api_error;

                $error[] = $this->send_response_after_single_api_call_fail($this->plugin->gravity_api_create_individual->api_error);
            } else {
                $primary_data['individual_id'] = $this->get_individual_id($this->plugin->gravity_api_create_individual->api_response_arr);
                $primary_data['family_id'] = $this->get_family_id($this->plugin->gravity_api_create_individual->api_response_arr);
                $primary_data['api_sync']['create_individual']['success'] = true;

            }
        }

        /**
         * condition to check if community group create api call is required or it's created or not
         */
        if ($api_events['individual_community_group'] == true) {

            if (!isset($primary_data['api_sync']['create_community_group']['success']) || ($primary_data['api_sync']['create_community_group']['success'] == false)) {

                $individual_group_api_event = 'primary_individual_group';

                $campus_id = $primary_data['event_set'];
                $campus_id = explode('|', $campus_id);
                $campus_id = $campus_id[0];

                /**
                 * API call for creating community group
                 */

                $this->plugin->gravity_api_create_group->ccb_sync_create_group($data = array(
                    'campus_id' => $campus_id,
                    'name' => $primary_data['community_group_name'],
                    'main_leader_id' => CCB_GRAVITY_Functionality::CCB_COMMUNITY_GROUP_LEADER_ID
                ));

                if (!empty($this->plugin->gravity_api_create_group->api_error)) {
                    $api_sync = false;
                    $primary_data['api_sync']['create_community_group']['success'] = false;
                    $primary_data['api_sync']['create_community_group']['error'] = $this->plugin->gravity_api_create_group->api_error;

                    $error[] = $this->send_response_after_single_api_call_fail($this->plugin->gravity_api_create_group->api_error);
                } else {
                    $primary_data['group_id'] = $this->get_group_id($this->plugin->gravity_api_create_group->api_response_arr);
                    $primary_data['api_sync']['create_community_group']['success'] = true;

                }
            }

            /**
             * condition to check primary user is added to the community group or not
             */
            if (($primary_data['api_sync']['create_community_group']['success'] == true) && (!isset($primary_data['api_sync']['add_to_group']['success']) || ($primary_data['api_sync']['add_to_group']['success'] == false))) {

                /**
                 * API call to add primary user to the community group
                 */
                $this->plugin->gravity_api_add_individual_to_group->ccb_sync_add_to_group($primary_data, $individual_group_api_event, $primary_data);

                if (!empty($this->plugin->gravity_api_add_individual_to_group->api_error)) {
                    $api_sync = false;
                    $primary_data['api_sync']['add_to_group']['success'] = false;
                    $primary_data['api_sync']['add_to_group']['error'] = $this->plugin->gravity_api_add_individual_to_group->api_error;

                    $error[] = $this->send_response_after_single_api_call_fail($this->plugin->gravity_api_add_individual_to_group->api_error);
                } else {
                    $primary_data['api_sync']['add_to_group']['success'] = true;
                }

            }

        }

        /***
         * adding primary user to the event
         */
        $check_error = $this->_sync_gform_data_add_to_event($primary_data, $primary_data);

        if (!empty($check_error['error'])) {
            $error[] = $check_error;
        }

        /**
         * adding existing family / group members to event
         */
        if (!empty($primary_data['extra_individual_ids'])) {
            foreach ($primary_data['extra_individual_ids'] as $index => $extra_individual_id) {
                $check_error = $this->_sync_gform_data_add_to_event($primary_data['extra_individual_ids'][$index], $primary_data);

                if (!empty($check_error['error'])) {
                    $error[] = $check_error;
                }
            }
        }

        /**
         * conditions for secondary individual creation
         */
        $create_individual_api_event = '';
        if ($api_events['individual_only'] == true) {
            $create_individual_api_event = 'secondary_individual_only';
        } elseif ($api_events['individual_family'] == true && $primary_data['family_id'] != '') {
            $create_individual_api_event = 'secondary_individual_family';
        } elseif (($api_events['individual_group'] == true || $api_events['individual_community_group'] == true) && $primary_data['group_id'] != '') {
            $individual_group_api_event = 'secondary_individual_group';
        }

        #1
        if (!empty($secondary_data)) {

            foreach ($secondary_data['individual'] as $index => $item) {

                /**
                 * condition to check secondary individual is created or not
                 */
                if (!isset($item['api_sync']['create_individual']['success']) || ($item['api_sync']['create_individual']['success'] == false)) {

                    /**
                     * API Call
                     */
                    $this->plugin->gravity_api_create_individual->ccb_sync_create_individual($item, $create_individual_api_event, $primary_data);

                    if (!empty($this->plugin->gravity_api_create_individual->api_error)) {

                        $api_sync = false;
                        $secondary_data['individual'][$index]['api_sync']['create_individual']['success'] = false;
                        $secondary_data['individual'][$index]['api_sync']['create_individual']['error'] = $this->plugin->gravity_api_create_individual->api_error;

                        $error[] = $this->send_response_after_single_api_call_fail($this->plugin->gravity_api_create_individual->api_error);
                    } else {

                        $secondary_data['individual'][$index]['individual_id'] = $this->get_individual_id($this->plugin->gravity_api_create_individual->api_response_arr);
                        $secondary_data['individual'][$index]['family_id'] = $this->get_family_id($this->plugin->gravity_api_create_individual->api_response_arr);
                        $secondary_data['individual'][$index]['api_sync']['create_individual']['success'] = true;
                    }

                }

                /**
                 * if secondary individual is successfully created
                 * for adding secondary members to related group
                 */
                if ($secondary_data['individual'][$index]['api_sync']['create_individual']['success'] == true) {

                    if ($individual_group_api_event == 'secondary_individual_group') {

                        if ((!isset($secondary_data['individual'][$index]['api_sync']['add_to_group']['success'])) || ($secondary_data['individual'][$index]['api_sync']['add_to_group']['success'] == false)) {

                            /**
                             * API call for adding secondary members to group
                             */
                            $this->plugin->gravity_api_add_individual_to_group->ccb_sync_add_to_group($secondary_data['individual'][$index], $individual_group_api_event, $primary_data);

                            if (!empty($this->plugin->gravity_api_add_individual_to_group->api_error)) {
                                $api_sync = false;
                                $secondary_data['individual'][$index]['api_sync']['add_to_group']['success'] = false;
                                $secondary_data['individual'][$index]['api_sync']['add_to_group']['error'] = $this->plugin->gravity_api_add_individual_to_group->api_error;

                                $error[] = $this->send_response_after_single_api_call_fail($this->plugin->gravity_api_add_individual_to_group->api_error);
                            } else {
                                $secondary_data['individual'][$index]['group_id'] = $primary_data['group_id'];
                                $secondary_data['individual'][$index]['api_sync']['add_to_group']['success'] = true;
                            }
                        }
                    }

                    /**
                     * adding secondary user to event
                     */
                    $check_error = $this->_sync_gform_data_add_to_event($secondary_data['individual'][$index], $primary_data);

                    if (!empty($check_error['error'])) {
                        $error[] = $check_error;
                    }

                }

            }
        }

        $api_data['primary'] = $primary_data;
        $api_data['secondary'] = $secondary_data;
        $api_data['api_sync'] = $api_sync;
        $api_data['error'] = $error;

        return $api_data;
    }

    public function send_response_after_single_api_call_fail($error)
    {
        return array(
            'error' => true,
            'success' => false,
            'error_details' => $error
        );
    }

    public function get_individual_id($data)
    {
        if (isset($data['ccb_api']['response']['individuals']['individual']['id'])) {
            return $data['ccb_api']['response']['individuals']['individual']['id'];
        }

        return null;
    }

    public function get_family_id($data)
    {
        if (isset($data['ccb_api']['response']['individuals']['individual']['family']['id'])) {
            return $data['ccb_api']['response']['individuals']['individual']['family']['id'];
        }

        return null;
    }

    public function get_group_id($data)
    {
        if (isset($data['ccb_api']['response']['groups']['group']['id'])) {
            return $data['ccb_api']['response']['groups']['group']['id'];
        }

        return null;
    }

    public function _sync_gform_data_add_to_event(&$indv_data, $evnt_data)
    {

        if (!isset($indv_data['api_sync']['add_to_event']['success']) || ($indv_data['api_sync']['add_to_event']['success'] == false)) {
            /**
             * API Call for adding individual to event
             */
            $this->plugin->gravity_api_add_to_event->ccb_sync_add_to_event($indv_data, $evnt_data);

            if (!empty($this->plugin->gravity_api_add_to_event->api_error)) {
                $api_sync = false;
                $indv_data['api_sync']['add_to_event']['success'] = false;
                $indv_data['api_sync']['add_to_event']['error'] = $this->plugin->gravity_api_add_to_event->api_error;

                return $this->send_response_after_single_api_call_fail($this->plugin->gravity_api_add_to_event->api_error);
            } else {
                $previous_event_count = get_option('ccb_event_count_' . $evnt_data['event_id']);

                if(empty($previous_event_count)) {
                    $previous_event_count = 0;
                }

                $current_event_count = ++$previous_event_count;

                update_option('ccb_event_count_' . $evnt_data['event_id'], $current_event_count);

                $indv_data['api_sync']['add_to_event']['success'] = true;

                return $indv_data;
            }
        }
    }
}

