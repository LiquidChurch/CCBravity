<?php

/**
 * CCB GRAVITY Session Management
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_manage_session extends CCB_GRAVITY_Abstract
{
	/**
	 * CCB_GRAVITY_manage_session constructor.
	 *
	 * @param object $plugin
	 */
    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

	/**
	 * Save API Login Session
	 *
	 * @param $api_resp_arr
	 * @param $api_err
	 * @param array $extra_sess_val
	 */
    public static function save_api_login_session($api_resp_arr, $api_err, $extra_sess_val = array())
    {
    	// If an error occurs, we didn't successfully login.
        if (!empty($api_err)) {
            $_SESSION['ccb_plugin'] = array(
                'login_authenticated' => false,
                'login_error' => $api_err,
            );
        } else { // We successfully logged in, get profile data.
            $sess_arr = array_merge(array(
                'login_authenticated' => true,
                'user_profile' => CCB_GRAVITY_fetch_session_data::get_user_profile_data($api_resp_arr),
            ), $extra_sess_val);
            $_SESSION['ccb_plugin'] = $sess_arr;
        }
    }

	/**
	 * Save API Individual Groups Session
	 *
	 * @param $api_resp_arr
	 * @param $api_err
	 * @param array $extra_sess_val
	 */
    public static function save_api_individual_groups_session($api_resp_arr, $api_err, $extra_sess_val = array())
    {
        if (!empty($api_err)) {
            $ccb_plugin = array(
                'user_groups' => array(),
                'user_groups_error' => $api_err,
            );
        } else {
            $ccb_plugin = array_merge(array(
                'user_groups' => CCB_GRAVITY_fetch_session_data::get_user_groups_data($api_resp_arr),
                'user_groups_error' => false,
            ), $extra_sess_val);
        }

        if (isset($_SESSION['ccb_plugin'])) {
            $_SESSION['ccb_plugin'] = array_merge($_SESSION['ccb_plugin'], $ccb_plugin);
        } else {
            $_SESSION['ccb_plugin'] = $ccb_plugin;
        }
    }

	/**
	 * Save API Group Participants Session
	 *
	 * @param $api_resp_arr
	 * @param $api_err
	 * @param array $extra_sess_val
	 *
	 * @return mixed
	 */
    public static function save_api_group_participants_session($api_resp_arr, $api_err, $extra_sess_val = array())
    {
        if (!empty($api_err)) {
            $ccb_plugin['group_participants'][$extra_sess_val['group_id']] = array(
                'participants_data' => array(),
                'error' => true,
                'error_details' => $api_err,
            );
        } else {
            $ccb_plugin['group_participants'][$extra_sess_val['group_id']] = array_merge(array(
                'participants_data' => CCB_GRAVITY_fetch_session_data::get_user_group_participants_data($api_resp_arr),
                'error' => false,
            ), $extra_sess_val);
        }

        if (isset($_SESSION['ccb_plugin'])) {
            if (isset($_SESSION['ccb_plugin']['group_participants'])) {
                $_SESSION['ccb_plugin']['group_participants'] = $_SESSION['ccb_plugin']['group_participants'] + $ccb_plugin['group_participants'];
            } else {
                $_SESSION['ccb_plugin'] = array_merge($_SESSION['ccb_plugin'], $ccb_plugin);
            }
        } else {
            $_SESSION['ccb_plugin'] = $ccb_plugin;
        }

        return $ccb_plugin['group_participants'][$extra_sess_val['group_id']];
    }

	/**
	 * Is User Logged In?
	 *
	 * @return bool
	 */
    public static function if_user_logged_in()
    {
        if (isset($_SESSION['ccb_plugin']['login_authenticated']) && ($_SESSION['ccb_plugin']['login_authenticated'] == true))
            return true;
        return false;
    }

	/**
	 * Logout User
	 *
	 * @return bool
	 */
    public static function logout_user()
    {
        if (isset($_SESSION['ccb_plugin'])) {
            unset($_SESSION['ccb_plugin']);
            return true;
        }
        return false;
    }

	/**
	 * Hooks
	 */
    public function hooks()
    {
        $this->session_exist();
    }

    /**
     * Does a session exist currently? If not, start one.
     */
    protected function session_exist()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

}