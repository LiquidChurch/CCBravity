<?php

/**
 * Class CCB_GRAVITY_Config_Page
 *
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_Api_Config extends CCB_GRAVITY_Abstract
{
    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

    private $acceptable_post_action = array(
        'ccb-gravity-field-config-form',
        'ccb-gravity-service-config-form'
    );

    public static $ccb_field_config_option = 'ccb_field_config_option';
    public static $ccb_service_config_option = 'ccb_service_config_option';

    private $form_submitted = false;
    private $form_handle_status = false;

    public function hooks()
    {
        add_action('admin_menu', array($this, 'add_admin_menu_page'));
        $this->check_post_action();
    }

    private function check_post_action()
    {
        if (!empty($_POST['action']) && !empty($_POST['_wpnonce'])) {
            $this->form_submitted = true;
            $nonce = sanitize_text_field($_POST['_wpnonce']);
            $action = sanitize_text_field($_POST['action']);
            if (in_array($action, $this->acceptable_post_action)) {
                if (wp_verify_nonce($nonce, $action)) {
                    $method_key = str_replace('-', '_', $action) . '_handler';
                    $this->form_handle_status = $this->{$method_key}();
                }
            }
        }
    }

    public function add_admin_menu_page()
    {
        add_submenu_page('ccb-gravity', __('CCB Service Config', 'ccb-gravity'), __('CCB Service Config', 'ccb-gravity'), 'manage_options', 'ccb-service-config', array($this, 'page_ccb_gravity_service_config'));
        add_submenu_page('ccb-gravity', __('CCB Field Config', 'ccb-gravity'), __('CCB Field Config', 'ccb-gravity'), 'manage_options', 'ccb-field-config', array($this, 'page_ccb_gravity_field_config'));
        remove_submenu_page('ccb-gravity', 'ccb-gravity');
    }

    public function page_ccb_gravity_service_config()
    {
        $arg = array();
        $serviceNames = get_option(self::$ccb_service_config_option);
        if(!empty($serviceNames)) {
            $arg['serviceNames'] = json_decode($serviceNames, 1);
        }
        $view = CCB_GRAVITY_Template_Loader::get_template('pages/ccb-service-config', $arg);

        $this->enqueu_js();
        $this->enqueu_css();
        echo $view;
    }

    public function page_ccb_gravity_field_config()
    {
        $arg = array();
        $fieldnames = get_option(self::$ccb_field_config_option);
        if(!empty($fieldnames)) {
            $arg['fieldnames'] = json_decode($fieldnames, 1);
        }
        $view = CCB_GRAVITY_Template_Loader::get_template('pages/ccb-field-config', $arg);

        $this->enqueu_js();
        $this->enqueu_css();
        echo $view;
    }

    public function enqueu_js()
    {
        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script(
            'ccb-duplicate-fields',
            CCB_GRAVITY_Functionality::url("assets/js/duplicate-field/duplicateFields{$min}.js"),
            array('jquery'),
            CCB_GRAVITY_Functionality::VERSION
        );

        wp_enqueue_script(
            'ccb-block-ui',
            CCB_GRAVITY_Functionality::url("assets/node_modules/block-ui/jquery.blockUI{$min}.js"),
            array('jquery'),
            CCB_GRAVITY_Functionality::VERSION
        );

        wp_enqueue_script(
            'ccb-gravity-field-config',
            CCB_GRAVITY_Functionality::url("assets/js/ccb-gravity-field-config{$min}.js"),
            array('jquery'),
            CCB_GRAVITY_Functionality::VERSION
        );

        wp_localize_script('ccb-gravity-field-config', 'CCB_GRAVITY', array(
            'path' => CCB_GRAVITY_Functionality::url(),
            'blockui_message' => __('Please wait...', 'ccb-gravity'),
            'required_message' => __('Please fill all the required values', 'ccb-gravity'),
            'ajax_nonce' => wp_create_nonce('sermon_message_config_page'),
        ));
    }

    public function enqueu_css()
    {
        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_style(
            'ccb-gravity-field-config',
            CCB_GRAVITY_Functionality::url("assets/css/ccb-gravity-field-config{$min}.css"),
            array(),
            CCB_GRAVITY_Functionality::VERSION
        );
    }

    public static function get_config_option($key) {
        $option = get_option($key);
        if(!empty($option)) {
            $option = json_decode($option, 1);

            if(!empty($option)) {
                return $option;
            }
        }
        return array();
    }

    /**
     * Below methods are for Ajax calls and form submit handlers
     */
    private function ccb_gravity_field_config_form_handler()
    {
        $fieldnames = array_unique($_POST['fieldName']);
        $fieldNames = json_encode($fieldnames);
        return update_option(self::$ccb_field_config_option, $fieldNames);
    }

    private function ccb_gravity_service_config_form_handler()
    {
        $serviceNames = array_unique($_POST['serviceName']);
        $serviceNames = json_encode($serviceNames);
        return update_option(self::$ccb_service_config_option, $serviceNames);
    }

    public static function import_default_settings() {
        $form_config = '["individual_profile_from_login_password","add_individual_to_event"]';
        $field_config = '["login.username","login.password","individual.first_name","individual.last_name","individual.email","individual.phone","individual.address.line_1","individual.address.line_2","individual.address.city","individual.address.state","individual.address.zip","event.id","event.register_user_type","campus.id","individual.family.position","individual.id","autofill_with_user_data","individual.family.id","individual.member.ids","individual.age","event.ids","group.community.new.name","individual.group.id","ccb.individual.data","null"]';

        update_option(self::$ccb_service_config_option, $form_config);
        update_option(self::$ccb_field_config_option, $field_config);
    }

}