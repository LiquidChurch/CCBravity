<?php
/**
 * CCB Gravity option Settings.
 *
 * @since 1.0.0
 * @package CCB_Gravity
 */


/**
 * CCB Gravity Ccb Events Page Settings class.
 *
 * @since 1.0.0
 */
class CCB_GRAVITY_option_settings extends CCB_GRAVITY_Base_Option_Page
{

    /**
     * Holds an instance of the object
     *
     * @var CCB_GRAVITY_option_settings
     * @since 1.0.0
     */
    protected static $instance = NULL;

    /**
     * Option key, and option page slug
     *
     * @var string
     * @since 1.0.0
     */
    protected $key = 'ccb_gravity_option_settings';
    /**
     * Options page metabox id
     *
     * @var string
     * @since 1.0.0
     */
    protected $metabox_id = 'ccb_gravity_option_settings_metabox';
    /**
     * Options page meta prefix
     *
     * @var string
     * @since 1.0.0
     */
    protected $meta_prefix = 'ccb_gravity_option_';
    /**
     * Options Page title
     *
     * @var string
     * @since 1.0.0
     */
    protected $title = '';
    /**
     * Options Page hook
     *
     * @var string
     * @since 1.0.0
     */
    protected $options_page = '';

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        // Set our title
        $this->title = __('CCB Gravity Settings', 'ccb-gravity');

        $this->hooks();
    }

    /**
     * parent hook override
     *
     * @since 1.0.0
     */
    public function hooks()
    {
        parent::hooks(); // TODO: Change the autogenerated stub
    }

    /**
     * Returns the running object
     *
     * @return CCB_GRAVITY_option_settings
     * @since 1.0.0
     */
    public static function get_instance()
    {
        if (NULL === self::$instance)
        {
            self::$instance = new self();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Add the options metabox to the array of metaboxes
     *
     * @since  1.0.0
     */
    function add_options_page_metabox()
    {
//        wp_enqueue_style('lc-plugin', CCB_Gravity::$url . 'assets/css/lc-plugin.css');

        // hook in our save notices
        add_action("cmb2_save_options-page_fields_{$this->metabox_id}",
            array($this, 'settings_notices'), 10, 2);

        $cmb = new_cmb2_box(array(
            'id'         => $this->metabox_id,
            'hookup'     => FALSE,
            'cmb_styles' => FALSE,
            'show_on'    => array(
                // These are important, don't remove
                'key'   => 'options-page',
                'value' => array($this->key,)
            ),
        ));

        // Set our CMB2 fields

        $cmb->add_field(array(
            'name'       => __('CCB API Username', 'ccb-gravity'),
            'desc'       => __('Please enter your username for accessing CCB API.', 'ccb-gravity'),
            'id'         => $this->meta_prefix . 'ccb_api_username',
            'type'       => 'text',
            'attributes' => ['required' => 'required'],
            'default'    => '',
        ));

        $cmb->add_field(array(
            'name'       => __('CCB API Password', 'ccb-gravity'),
            'desc'       => __('Please enter your password for accessing CCB API.', 'ccb-gravity'),
            'id'         => $this->meta_prefix . 'ccb_api_password',
            'type'       => 'text',
            'attributes' => [
                'required' => 'required',
                'type'     => 'password'
            ],
            'default'    => '',
        ));

        $cmb->add_field(array(
            'name'       => __('CCB API Default Community Group Leader ID', 'ccb-gravity'),
            'desc'       => __('Please enter group leader ID.', 'ccb-gravity'),
            'id'         => $this->meta_prefix . 'ccb_api_comm_group_id',
            'type'       => 'text',
            'attributes' => [
                'required' => 'required',
                'type'     => 'number'
            ],
            'default'    => '',
        ));

        //gravity form select option
        $cmb->add_field(array(
            'name' => __('Select Gravity Form for CCB Login', 'ccb-gravity'),
            'desc' => __('', 'ccb-gravity'),
            'id'   => $this->meta_prefix . 'ccb_login_gform',
            'type' => 'select',
            'options_cb' => ['CCB_GRAVITY_Base_Option_Page', 'get_gform_list'],
        ));

    }

    /**
     * Public getter method for retrieving protected/private variables
     *
     * @since  1.0.0
     *
     * @param  string $field Field to retrieve
     *
     * @return mixed          Field value or exception is thrown
     */
    public function __get($field)
    {
        // Allowed fields to retrieve
        if (in_array($field, array('key', 'metabox_id', 'meta_prefix', 'title', 'options_page'), TRUE))
        {
            return $this->{$field};
        }

        throw new Exception('Invalid property: ' . $field);
    }

}