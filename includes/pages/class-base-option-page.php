<?php
/**
 * CCB Gravity Base_Option_Page.
 *
 * @since   1.0.0
 * @package CCB_Gravity
 */


/**
 * CCB Gravity Base_Option_Page
 *
 * @since 1.0.0
 */
abstract class CCB_GRAVITY_Base_Option_Page {

    /**
     * Option key, and option page slug
     *
     * @var string
     * @since 1.0.0
     */
    protected $key = '';

    /**
     * Options Page title
     *
     * @var string
     * @since 0.1.0
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
     * Initiate our hooks
     *
     * @since 1.0.0
     */
    public function hooks() {
        add_action( 'admin_init', array( $this, 'init' ) );
        add_action( 'admin_menu', array( $this, 'add_options_page' ), 10 );
        add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
    }

    /**
     * Register our setting to WP
     *
     * @since  1.0.0
     */
    public function init() {
        register_setting( $this->key, $this->key );
    }

    /**
     * Add menu options page
     *
     * @since 1.0.0
     */
    public function add_options_page() {
        $this->options_page = add_submenu_page(
            'ccb-gravity',
            $this->title,
            $this->title,
            'manage_options',
            $this->key,
            array( $this, 'admin_page_display' )
        );

        // Include CMB CSS in the head to avoid FOUC
        add_action( "admin_print_styles-{$this->options_page}",
            array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
    }

    /**
     * Admin page markup. Mostly handled by CMB2
     *
     * @since  1.0.0
     */
    public function admin_page_display() {
        ?>
        <div class="wrap cmb2-options-page <?php echo $this->key; ?>">
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
            <?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
        </div>
        <?php
    }

    public abstract function add_options_page_metabox();

    /**
     * Register settings notices for display
     *
     * @since  1.0.0
     *
     * @param  int   $object_id Option key
     * @param  array $updated   Array of updated fields
     *
     * @return void
     */
    public function settings_notices( $object_id, $updated ) {
        if ( $object_id !== $this->key || empty( $updated ) ) {
            return;
        }

        add_settings_error( $this->key . '-notices', '',
            __( 'Settings updated.', 'ccb-gravity' ), 'updated' );
        settings_errors( $this->key . '-notices' );
    }

    /**
     * get all gform list
     * @return array
     * @since 1.0.0
     */
    public static function get_gform_list()
    {
        $form_array = array();

        // Gravity Form
        if (class_exists('RGFormsModel'))
        {
            $forms = RGFormsModel::get_forms(NULL, 'title');
            if ( ! empty($forms) && is_array($forms))
            {
                $form_array[''] = 'Select';
                foreach ($forms as $form)
                {
                    if (isset($form->title, $form->id))
                    {
                        $form_array[$form->id] = $form->title;
                    }
                }
            }
        }

        return $form_array;
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
    public function __get( $field ) {
        // Allowed fields to retrieve
        if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
            return $this->{$field};
        }

        throw new Exception( 'Invalid property: ' . $field );
    }

}

/**
 * Helper function to get/return the Myprefix_Admin object
 *
 * @since  1.0.0
 * @return CCB_GRAVITY_Base_Option_Page object
 */
function ccb_gravity_settings_admin($page = null) {
    if($page == 'option') {
        return CCB_GRAVITY_option_settings::get_instance();
    } else {
        die(__('Invalid page setting key provided', 'ccb-gravity'));
    }
}

/**
 * Wrapper function around cmb2_get_option
 *
 * @since  1.0.0
 *
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 *
 * @return mixed           Option value
 */
function ccb_gravity_get_option( $page = '', $key = '', $default = null ) {
    if ( function_exists( 'cmb2_get_option' ) ) {
        // Use cmb2_get_option as it passes through some key filters.
        return cmb2_get_option( ccb_gravity_settings_admin( $page )->key, $key, $default );
    }

    // Fallback to get_option if CMB2 is not loaded yet.
    $opts = get_option( ccb_gravity_settings_admin( $page )->key, $key, $default );

    $val = $default;

    if ( 'all' == $key ) {
        $val = $opts;
    } elseif ( array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
        $val = $opts[ $key ];
    }

    return $val;
}