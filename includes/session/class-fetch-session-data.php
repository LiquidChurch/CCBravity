<?php

/**
 * CCB GRAVITY Session fetch data
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_fetch_session_data extends CCB_GRAVITY_Abstract
{
    private static $individual_profile = array();
    private static $individual_group_profile = array();
    private static $group_participants = array();

	/**
	 * CCB_GRAVITY_fetch_session_data constructor.
	 *
	 * @param object $plugin
	 */
    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

	/**
	 * Get User Profile Data
	 *
	 * @param $raw_user_data_from_api
	 *
	 * @return array
	 */
    public static function get_user_profile_data($raw_user_data_from_api)
    {
        $return = array();

        if (isset($raw_user_data_from_api['ccb_api']['response']['individuals'])) {
            if ($raw_user_data_from_api['ccb_api']['response']['individuals']['count'] != '0') {
                if (isset($raw_user_data_from_api['ccb_api']['response']['individuals']['individual'])) {
                    self::$individual_profile = $raw_user_data_from_api['ccb_api']['response']['individuals']['individual'];

                    $return = array(
                        'individual.id' => self::check_is_val(self::$individual_profile, array('id')),
                        'individual.other_id' => self::check_is_val(self::$individual_profile, array('other_id')),
                        'individual.family_members' => self::check_is_val(self::$individual_profile, array('family_members')),
                        'individual.first_name' => self::check_is_val(self::$individual_profile, array('first_name')),
                        'individual.last_name' => self::check_is_val(self::$individual_profile, array('last_name')),
                        'individual.full_name' => self::check_is_val(self::$individual_profile, array('full_name')),
                        'individual.email' => self::check_is_val(self::$individual_profile, array('email')),
                        'individual.phone' => self::check_is_val(self::$individual_profile, array('phones', 'phone', 0, 'value')),
                        'individual.address.line_1' => self::check_arr_implode_val(self::$individual_profile, array('addresses', 'address', 0, 'street_address')),
                        'individual.address.line_2' => self::check_arr_implode_val(self::$individual_profile, array('addresses', 'address', 0, 'line_2')),
                        'individual.address.city' => self::check_arr_implode_val(self::$individual_profile, array('addresses', 'address', 0, 'city')),
                        'individual.address.state' => self::check_arr_implode_val(self::$individual_profile, array('addresses', 'address', 0, 'state')),
                        'individual.address.zip' => self::check_arr_implode_val(self::$individual_profile, array('addresses', 'address', 0, 'zip')),
                        'individual.address.country' => self::check_arr_implode_val(self::$individual_profile, array('addresses', 'address', 0, 'country')),
                        'individual.family.id' => self::check_is_val(self::$individual_profile, array('family', 'id')),
                        'individual.family.value' => self::check_is_val(self::$individual_profile, array('family', 'value')),
                        'individual.gender' => self::check_is_val(self::$individual_profile, array('gender')),
                        'individual.marital_status' => self::check_is_val(self::$individual_profile, array('marital_status')),
                        'individual.birthday' => self::check_is_val(self::$individual_profile, array('birthday')),
                        'individual.anniversary' => self::check_is_val(self::$individual_profile, array('anniversary')),
                        'individual.baptized' => self::check_is_val(self::$individual_profile, array('baptized')),
                        'individual.limited_access_user' => self::check_is_val(self::$individual_profile, array('limited_access_user')),
                        'individual.deceased' => self::check_is_val(self::$individual_profile, array('deceased')),
                        'individual.membership_type' => self::check_is_val(self::$individual_profile, array('membership_type')),
                        'individual.membership_date' => self::check_is_val(self::$individual_profile, array('membership_date')),
                        'individual.membership_end' => self::check_is_val(self::$individual_profile, array('membership_end')),
                        'individual.receive_email_from_church' => self::check_is_val(self::$individual_profile, array('receive_email_from_church')),
                        'individual.privacy_settings' => self::check_is_val(self::$individual_profile, array('privacy_settings')),
                        'individual.created' => self::check_is_val(self::$individual_profile, array('created')),
                        'individual.modified' => self::check_is_val(self::$individual_profile, array('modified')),
                        'campus.id' => self::check_is_val(self::$individual_profile, array('campus', 'id')),
                        'campus.value' => self::check_is_val(self::$individual_profile, array('campus', 'value')),
                    );
                }
            }
        }

        return $return;
    }

	/**
	 * Get User Groups Data
	 *
	 * @param $raw_groups_data_from_api
	 *
	 * @return array
	 */
    public static function get_user_groups_data($raw_groups_data_from_api) {
        $return = array();

        if (isset($raw_groups_data_from_api['ccb_api']['response']['individuals'])) {
            if ($raw_groups_data_from_api['ccb_api']['response']['individuals']['count'] != '0') {
                if (isset($raw_groups_data_from_api['ccb_api']['response']['individuals']['individual'])) {
                    self::$individual_group_profile = $raw_groups_data_from_api['ccb_api']['response']['individuals']['individual'];

                    $return['count'] = $total_group_count = self::check_is_val(self::$individual_group_profile, array('groups', 'count'));

                    for($i = 0; $i < $total_group_count; $i++) {
                        $return['group'][] = self::check_is_val(self::$individual_group_profile, array('groups', 'group', $i));
                    }
                }
            }
        }

        return $return;
    }

	/**
	 * Get User Group Participants Data
	 *
	 * @param $raw_data_from_api
	 *
	 * @return array
	 */
    public static function get_user_group_participants_data($raw_data_from_api) {
        $return = array();

        if (isset($raw_data_from_api['ccb_api']['response']['groups'])) {
            if ($raw_data_from_api['ccb_api']['response']['groups']['count'] != '0') {
                if (isset($raw_data_from_api['ccb_api']['response']['groups']['group'])) {
                    self::$group_participants = $raw_data_from_api['ccb_api']['response']['groups']['group'];

                    $return['count'] = $total_group_count = self::check_is_val(self::$group_participants, array('participants', 'count'));

                    for($i = 0; $i < $total_group_count; $i++) {
                        $return['participant'][] = self::check_is_val(self::$group_participants, array('participants', 'participant', $i));
                    }
                }
            }
        }

        return $return;
    }

	/**
	 * Check If Value Equals
	 *
	 * @param $data
	 * @param $value
	 *
	 * @return string
	 */
    public static function check_is_val($data, $value)
    {
        if ((count($value) > '1') == true) {
            foreach ($value as $key => $val) {
                unset($value[$key]);
                if (isset($data[$val])) {
                    return self::check_is_val($data[$val], $value);
                } else {
                    return '';
                }
            }
        } else {
            $tmp = array_values($value);
            if (isset($data[$tmp[0]])) {
                return $data[$tmp[0]];
            } else {
                return '';
            }
        }
    }

	/**
	 * Check Array Implode Value
	 *
	 * @param $data
	 * @param $val
	 *
	 * @return string
	 */
    public static function check_arr_implode_val($data, $val)
    {
        $val = self::check_is_val($data, $val);

        if (is_array($val)) {
            return implode(', ', $val);
        } else {
            return $val;
        }
    }

    public function hooks()
    {
        // TODO: Implement hooks() method.
    }

}