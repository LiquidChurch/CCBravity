<?php
/**
 * CCB Gravity Functionality
 *
 * @since NEXT
 * @package CCB Gravity Functionality
 */

/**
 * CCB Gravity Functionality Shortcodes Resources Admin.
 *
 * @since NEXT
 */
class CCB_Shortcodes_Resources_Admin extends WDS_Shortcode_Admin
{
    /**
     * Shortcode Run object
     *
     * @var   CCB_Shortcodes_Resources_Run
     * @since 0.1.0
     */
    protected $run;

    /**
     * Constructor
     *
     * @since  0.1.0
     * @param  object $run CCB_Shortcodes_Resources_Run object.
     * @return void
     */
    public function __construct(CCB_Shortcodes_Resources_Run $run)
    {
        $this->run = $run;

        parent::__construct(
            $this->run->shortcode,
            CCB_GRAVITY_Functionality::VERSION,
            $this->run->atts_defaults
        );

        add_action('cmb2_render_text_number', array($this, 'meta_addtnl_type_text_number'), 10, 5);
    }

    /**
     * Sets up the button
     *
     * @return array
     */
    function js_button_data()
    {
        return array(
            'qt_button_text' => __('CCB Gravity Form', 'ccb-gravity'),
            'button_tooltip' => __('Insert CCB Gravity Form', 'ccb-gravity'),
            'icon' => 'dashicons-media-interactive',
            // 'mceView'        => true, // The future
        );
    }

    /**
     * Adds fields to the button modal using CMB2
     *
     * @param $fields
     * @param $button_data
     *
     * @return array
     */
    function fields($fields, $button_data)
    {

        $fields[] = array(
            'name' => __('Login Form', 'ccb-gravity'),
            'desc' => __('Select Login Form', 'ccb-gravity'),
            'id' => 'login_form_id',
            'type' => 'select',
            'default' => '',
            'options' => $this->get_gform_list(true),
        );

        $fields[] = array(
            'name' => __('Login Form Title', 'ccb-gravity'),
            'desc' => __('Toggle form title', 'ccb-gravity'),
            'id' => 'login_form_title',
            'type' => 'checkbox',
            'default' => true
        );

        $fields[] = array(
            'name' => __('Login Form Description', 'ccb-gravity'),
            'desc' => __('Toggle form description', 'ccb-gravity'),
            'id' => 'login_form_description',
            'type' => 'checkbox',
            'default' => true
        );

//        $fields[] = array(
//            'name' => __('Login Form Ajax Submit', 'ccb-gravity'),
//            'desc' => __('Form will be submitted via ajax ?', 'ccb-gravity'),
//            'id' => 'login_form_ajax',
//            'type' => 'checkbox',
//            'default' => true
//        );

        $fields[] = array(
            'name' => __('Login Form Tabindex', 'ccb-gravity'),
            'desc' => __('Tabindex for the form', 'ccb-gravity'),
            'default' => '',
            'id' => 'login_form_tabindex',
            'type' => 'text_number',
        );

        $fields[] = array(
            'name' => __('User Form', 'ccb-gravity'),
            'desc' => __('Select User Form', 'ccb-gravity'),
            'id' => 'user_form_id',
            'type' => 'select',
            'default' => '',
            'options' => $this->get_gform_list(true),
        );

        $fields[] = array(
            'name' => __('User Form Title', 'ccb-gravity'),
            'desc' => __('Toggle form title', 'ccb-gravity'),
            'id' => 'user_form_title',
            'type' => 'checkbox',
            'default' => true
        );

        $fields[] = array(
            'name' => __('User Form Description', 'ccb-gravity'),
            'desc' => __('Toggle form description', 'ccb-gravity'),
            'id' => 'user_form_description',
            'type' => 'checkbox',
            'default' => true
        );

//        $fields[] = array(
//            'name' => __('User Form Ajax Submit', 'ccb-gravity'),
//            'desc' => __('Form will be submitted via ajax ?', 'ccb-gravity'),
//            'id' => 'user_form_ajax',
//            'type' => 'checkbox',
//            'default' => true
//        );

        $fields[] = array(
            'name' => __('User Form Tabindex', 'ccb-gravity'),
            'desc' => __('Tabindex for the form', 'ccb-gravity'),
            'default' => '',
            'id' => 'user_form_tabindex',
            'type' => 'text_number',
        );

        return $fields;
    }

	/**
	 * Get Gravity Forms List
	 *
	 * @param bool $form_with_api_linked
	 *
	 * @return array
	 */
    public function get_gform_list($form_with_api_linked = false)
    {
        $forms = GFAPI::get_forms();
        $formListArr = array('' => __('Please select a form', 'ccb-gravity'));
        foreach ($forms as $index => $form) {
            if (($form_with_api_linked == true)) {
                if (!empty($form['ccb_api_settings']))
                    $formListArr[$form['id']] = $form['title'];
            } else {
                if (empty($form['ccb_api_settings']))
                    $formListArr[$form['id']] = $form['title'];
            }
        }
        return $formListArr;
    }

    /**
     * input type number for meta fields
     *
     * @param $field
     * @param $escaped_value
     * @param $object_id
     * @param $object_type
     * @param $field_type_object
     */
    function meta_addtnl_type_text_number($field, $escaped_value, $object_id, $object_type, $field_type_object)
    {
        echo $field_type_object->input(array('type' => 'number', 'min' => 0));
    }
}
