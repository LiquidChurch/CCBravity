<?php
/**
 * CCB Gravity Functionality
 *
 * @since NEXT
 * @package CCB Gravity Functionality
 */

/**
 * CCB Gravity Functionality Shortcodes Resources Run.
 *
 * @since NEXT
 */
class CCB_Shortcodes_Resources_Run extends WDS_Shortcodes
{

    protected $plugin = null;

	/**
	 * CCB_Shortcodes_Resources_Run constructor.
	 *
	 * @param $plugin
	 */
    public function __construct($plugin)
    {
        parent::__construct();
        $this->plugin = $plugin;
    }

    /**
     * The Shortcode Tag
     * @var string $shortcode
     * @since 0.1.0
     */
    public $shortcode = 'ccb_gform';

    /**
     * Default attributes applied to the shortcode.
     * @var array   $atts_defaults
     * @since 0.1.0
     */
    public $atts_defaults = array(
        //login form
        'login_form_id' => '', // ID of the gform
        'login_form_title' => 'true', // Title for the gform
        'login_form_description' => 'true', // Description for the gform
        'login_form_ajax' => 'true', // is Ajax gform
        'login_form_tabindex' => '', // Tabindex the gform

        //user form
        'user_form_id' => '', // Tabindex the gform
        'user_form_title' => 'true', // Title for the gform
        'user_form_description' => 'true', // Description for the gform
        'user_form_ajax' => 'true', // is Ajax gform
        'user_form_tabindex' => '', // Tabindex the gform
    );

    /**
     * Shortcode Output
     */
    public function shortcode()
    {
        $output = $this->_shortcode();

        return apply_filters('ccb_shortcode_output', $output, $this);
    }

	/**
	 * Shortcode
	 *
	 * @return string
	 */
    protected function _shortcode()
    {
        $login_form_id = $this->att('login_form_id');
        $login_form = $this->get_gform($login_form_id);

        $user_form_id = $this->att('user_form_id');
        $user_form = $this->get_gform($user_form_id);

        if (empty($login_form) || empty($user_form)) {
            echo __('Notice - Please select both the login and user form from the shortcode options', 'ccb-gravity');
        } else {
            $args = array(
                'gform_login' => $this->process_login_form($login_form_id),
                'gform_user' => $this->process_user_form($user_form_id),
                'autofill_btn' => CCB_GRAVITY_Template_Loader::get_template('gform/ccb-gform-autofill'),
                'login_authenticated' => isset($_SESSION['ccb_plugin']['login_authenticated']) ? $_SESSION['ccb_plugin']['login_authenticated'] : false,
                'user_profile_data' => isset($_SESSION['ccb_plugin']['user_profile']) ? $_SESSION['ccb_plugin']['user_profile'] : array(),
                'user_group_data' => isset($_SESSION['ccb_plugin']['user_groups']) ? $_SESSION['ccb_plugin']['user_groups'] : array(),
                'all_ccb_form' => CCB_GRAVITY_form_render::get_all_ccb_form(),
                'gform_submitted_ccb_field' => $this->plugin->gravity_render->gform_api_field,
            );

            return CCB_GRAVITY_Template_Loader::get_template('gform/ccb-gform-shortcode', $args);
        }
    }

	/**
	 * Get Gravity Form by ID
	 *
	 * @param string $form_id
	 *
	 * @return bool
	 */
    public function get_gform($form_id = '')
    {
        if (empty($form_id)) {
            return false;
        }
        $form = GFAPI::get_form($form_id);
        return $form;
    }

	/**
	 * Process Login Form
	 *
	 * @param $login_form_id
	 *
	 * @return string
	 */
    public function process_login_form($login_form_id)
    {
        if (CCB_GRAVITY_manage_session::if_user_logged_in()) {
            $args = array(
                'user_data' => isset($_SESSION['ccb_plugin']['user_profile']) ? $_SESSION['ccb_plugin']['user_profile'] : array()
            );
            return CCB_GRAVITY_Template_Loader::get_template('gform/ccb-gform-user-logged-in', $args);
        } else {
            $login_title = $this->att('login_form_title');
            $login_description = $this->att('login_form_description');
            $login_ajax = $this->att('login_form_ajax');
            $login_tabindex = $this->att('login_form_tabindex');
            if(empty($login_tabindex))
                $login_tabindex = 1;
            $login_gform_shortcode_str = sprintf('[gravityform id=%s title=%s description=%s ajax=%s tabindex=%s]', $login_form_id, $login_title, $login_description, 'false', $login_tabindex);
            $login_gform_shortcode = do_shortcode($login_gform_shortcode_str);
            return $login_gform_shortcode;
        }
    }

	/**
	 * Process User Form
	 *
	 * @param $user_form_id
	 *
	 * @return string
	 */
    public function process_user_form($user_form_id)
    {
        $user_title = $this->att('user_form_title');
        if (CCB_GRAVITY_manage_session::if_user_logged_in()) {
            $user_description = $this->att('user_form_description');
        } else {
            $user_description = 'true';
        }
        $user_ajax = $this->att('user_form_ajax');
        $user_tabindex = $this->att('user_form_tabindex');
        if(empty($user_tabindex))
            $user_tabindex = 10;
        $user_gform_shortcode_str = sprintf('[gravityform id=%s title=%s description=%s ajax=%s tabindex=%s]', $user_form_id, $user_title, $user_description, 'false', $user_tabindex);
        $user_gform_shortcode = do_shortcode($user_gform_shortcode_str);
        return $user_gform_shortcode;
    }

}
