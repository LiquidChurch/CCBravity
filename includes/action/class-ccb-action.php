<?php

/**
 * CCB GRAVITY action handler
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_action_handler extends CCB_GRAVITY_Abstract
{
    private $action = '';
    private $verify_nonce = '';
    public $referrer_url = '';

	/**
	 * CCB_GRAVITY_action_handler constructor.
	 *
	 * @param object $plugin
	 */
    public function __construct($plugin)
    {
        $wp_referrer_url = wp_get_referer();
        $this->referrer_url = !empty($wp_referrer_url) ? $wp_referrer_url : (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : home_url());

        parent::__construct($plugin);
    }

	/**
	 * Hooks Function
	 */
    public function hooks()
    {
        $this->set_action();
        $this->handle_action();
    }

	/**
	 * Set Action
	 */
    public function set_action()
    {
        $action = sanitize_text_field(isset($_GET['ccb-action']) ? $_GET['ccb-action'] : '');
        $verify_nonce = sanitize_text_field(isset($_GET['ccb-verify']) ? $_GET['ccb-verify'] : '');

        $this->action = $action;
        $this->verify_nonce = $verify_nonce;
    }

	/**
	 * Handle Action
	 */
    public function handle_action()
    {
        if ($this->verify_nonce()) {
            if ($this->action == 'logout') {
                $this->logout();
            }
        }
    }

	/**
	 * Verify Nonce
	 *
	 * @return false|int nonce
	 */
    public function verify_nonce()
    {
        return wp_verify_nonce($this->verify_nonce, 'ccb-gravity');
    }

    public function logout()
    {
        $return = CCB_GRAVITY_manage_session::logout_user();

        if ($return === true) {

            wp_redirect($this->referrer_url);
            exit();
        }
    }
}