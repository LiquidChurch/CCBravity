<?php
/**
 * CCB Gravity Functionality
 *
 * @since   NEXT
 * @package CCB Gravity Functionality
 */

/**
 * CCB Gravity Functionality Shortcodes Resources Run.
 *
 * @since NEXT
 */
class CCB_Shortcodes_Resources_Run extends WDS_Shortcodes
{

    protected $plugin = NULL;

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
     *
     * @var string $shortcode
     * @since 0.1.0
     */
    public $shortcode = 'ccb_gform';

    /**
     * Default attributes applied to the shortcode.
     *
     * @var array $atts_defaults
     * @since 0.1.0
     */
    public $atts_defaults = array(
        'id'          => '', // ID of the gform
        'title'       => 'true', // Title for the gform
        'description' => 'true', // Description for the gform
        'ajax'        => 'true', // is Ajax gform
        'tabindex'    => '', // Tabindex the gform
        'form_type'   => '', // form_type for the gform
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
        $user_login_form         = FALSE;
        $event_registration_form = FALSE;
        $form_id                 = $this->att('id');
        $form_type               = $this->att('form_type');
        if ($form_type == 'login_form')
        {
            $user_login_form = TRUE;
        }
        else if ($form_type == 'event_registration_form')
        {
            $event_registration_form = TRUE;
        }

        if (empty($user_login_form) && empty($event_registration_form))
        {
            echo __('Notice - Please select both the login and user form from the shortcode options', 'ccb-gravity');
        }
        else
        {
            $args = array(
                'form_type'                 => $form_type,
                'gform'                     => ! empty($user_login_form) ? $this->process_login_form($form_id) : $this->process_user_form($form_id),
                'autofill_btn'              => CCB_GRAVITY_Template_Loader::get_template('gform/ccb-gform-autofill'),
                'login_authenticated'       => isset($_SESSION['ccb_plugin']['login_authenticated']) ? $_SESSION['ccb_plugin']['login_authenticated'] : FALSE,
                'user_profile_data'         => isset($_SESSION['ccb_plugin']['user_profile']) ? $_SESSION['ccb_plugin']['user_profile'] : array(),
                'user_group_data'           => isset($_SESSION['ccb_plugin']['user_groups']) ? $_SESSION['ccb_plugin']['user_groups'] : array(),
                'all_ccb_form'              => CCB_GRAVITY_form_render::get_all_ccb_form(),
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
        if (empty($form_id))
        {
            return FALSE;
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
    public function process_login_form($form_id)
    {
        if (CCB_GRAVITY_manage_session::if_user_logged_in())
        {
            $args = array(
                'user_data' => isset($_SESSION['ccb_plugin']['user_profile']) ? $_SESSION['ccb_plugin']['user_profile'] : array()
            );

            return CCB_GRAVITY_Template_Loader::get_template('gform/ccb-gform-user-logged-in', $args);
        }
        else
        {
            $title       = $this->att('title');
            $description = $this->att('description');
            $ajax        = $this->att('ajax');
            $tabindex    = $this->att('tabindex');
            if (empty($tabindex))
            {
                $tabindex = 1;
            }
            $gform_shortcode_str = sprintf('[gravityform id=%s title=%s description=%s ajax=%s tabindex=%s]', $form_id, $title, $description, 'false', $tabindex);
            $gform_shortcode     = do_shortcode($gform_shortcode_str);

            return $gform_shortcode;
        }
    }

    /**
     * Process User Form
     *
     * @param $user_form_id
     *
     * @return string
     */
    public function process_user_form($form_id)
    {
        $title    = $this->att('title');
        $ajax     = $this->att('ajax');
        $tabindex = $this->att('tabindex');

        if (CCB_GRAVITY_manage_session::if_user_logged_in())
        {
            $description = $this->att('description');
        }
        else
        {
            $description = '';
        }

        if (empty($tabindex))
        {
            $tabindex = 10;
        }
        $gform_shortcode_str = sprintf('[gravityform id=%s title=%s description=%s ajax=%s tabindex=%s]', $form_id, $title, $description, $ajax, $tabindex);
        $gform_shortcode     = do_shortcode($gform_shortcode_str);

        return $gform_shortcode;
    }

}
