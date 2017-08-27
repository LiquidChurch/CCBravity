<?php

/**
 * CCB GRAVITY form render
 *
 * @since   1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_form_render extends CCB_GRAVITY_Abstract
{
    /**
     * Gravity Form CCB API Settings
     *
     * @var string $gform_ccb_api_settings
     */
    public $gform_ccb_api_settings = '';

    /**
     * Gravity Form
     *
     * @var array $gform_form
     */
    public $gform_form = array();

    /**
     * Gravity Form Entry
     *
     * @var array $gform_entry
     */
    public $gform_entry = array();

    /**
     * Gravity Form Entry ID
     *
     * @var string $gform_entry_id
     */
    public $gform_entry_id = '';

    /**
     * Gravity Form API Field
     *
     * @var string $gform_api_field
     */
    public $gform_api_field = '';

    /**
     * Referrer URL
     *
     * @var false|string|void $referrer_url
     */
    public $referrer_url = '';

    /**
     * CCB_GRAVITY_form_render constructor.
     *
     * @param object $plugin
     */
    public function __construct($plugin)
    {
        $wp_referrer_url    = wp_get_referer();
        $this->referrer_url = ! empty($wp_referrer_url) ? $wp_referrer_url : (! empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : home_url());

        parent::__construct($plugin);

    }

    /**
     * Hooks
     *
     * will be called when class initiated
     * from abstract class
     */
    public function hooks()
    {
        $this->enqueue_js();

        add_action('gform_pre_submission', array($this, 'gform_before_submission'), 5, 1);
        add_action('gform_after_submission', array($this, 'gform_after_submission'), 5, 2);

        add_filter('gform_validation', array($this, 'gform_validation_api_call'), 10);
        add_filter('gform_pre_render', array($this, 'gform_pre_render'), 10);
        add_filter('gform_field_content', array($this, 'gform_add_custom_attr'), 10, 5);
        add_filter('gform_disable_notification', array($this, 'gform_disable_notification'), 10, 4);
        add_filter("gform_address_types", array($this, "address_compatibily_to_api"), 10, 2);

        $this->set_custom_confirmation();
    }

    /**
     * enqueue js for render pages
     */
    public function enqueue_js()
    {
        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script(
            'ccb-blockui',
            CCB_GRAVITY_Functionality::url("assets/node_modules/block-ui/jquery.blockUI{$min}.js"),
            array('jquery'),
            CCB_GRAVITY_Functionality::VERSION
        );
    }

    /**
     * general custom confirmation method
     *
     */
    private function set_custom_confirmation()
    {
        $ccb_form = $this->get_all_ccb_form();
        foreach ($ccb_form as $index => $item)
        {
            $priority = 20;
            if ($item == 'add_individual_to_event')
            {
                $priority = 15;
            }
            add_filter('gform_confirmation_' . $index, array($this, 'gform_custom_confirmation_' . $item), $priority, 4);
        }
    }

    /**
     * get all CCB Service forms
     *
     * @return array
     */
    public static function get_all_ccb_form()
    {
        $select   = '<select>';
        $forms    = RGFormsModel::get_forms(NULL, 'title');
        $form_arr = array();
        foreach ($forms as $form):
            $form_obj = RGFormsModel::get_form_meta($form->id);
            if (isset($form_obj['ccb_api_settings']) && ! empty($form_obj['ccb_api_settings']))
            {
                $form_arr[$form->id] = $form_obj['ccb_api_settings'];
            }
        endforeach;

        return $form_arr;
    }

    /**
     * US Zip Code Address Compatibility for CCB API
     *
     * @param $address_types
     * @param $form_id
     *
     * @return mixed
     */
    function address_compatibily_to_api($address_types, $form_id) // TODO: Refactor as address_compatibility_to_api
    {
        $address_types["us"] = array(
            "label"       => "United States",
            "country"     => "USAB",
            "zip_label"   => "Zip Code",
            "state_label" => "State",
            "states"      => array(
                ""   => "",
                "AL" => "Alabama",
                "AK" => "Alaska",
                "AZ" => "Arizona",
                "AR" => "Arkansas",
                "CA" => "California",
                "CO" => "Colorado",
                "CT" => "Connecticut",
                "DE" => "Delaware",
                "DC" => "District of Columbia",
                "FL" => "Florida",
                "GA" => "Georgia",
                "GU" => "Guam",
                "HI" => "Hawaii",
                "ID" => "Idaho",
                "IL" => "Illinois",
                "IN" => "Indiana",
                "IA" => "Iowa",
                "KS" => "Kansas",
                "KY" => "Kentucky",
                "LA" => "Louisiana",
                "ME" => "Maine",
                "MD" => "Maryland",
                "MA" => "Massachusetts",
                "MI" => "Michigan",
                "MN" => "Minnesota",
                "MS" => "Mississippi",
                "MO" => "Missouri",
                "MT" => "Montana",
                "NE" => "Nebraska",
                "NV" => "Nevada",
                "NH" => "New Hampshire",
                "NJ" => "New Jersey",
                "NM" => "New Mexico",
                "NY" => "New York",
                "NC" => "North Carolina",
                "ND" => "North Dakota",
                "OH" => "Ohio",
                "OK" => "Oklahoma",
                "OR" => "Oregon",
                "PA" => "Pennsylvania",
                "PR" => "Puerto Rico",
                "RI" => "Rhode Island",
                "SC" => "South Carolina",
                "SD" => "South Dakota",
                "TN" => "Tennessee",
                "TX" => "Texas",
                "UT" => "Utah",
                "VT" => "Vermont",
                "VA" => "Virginia",
                "WA" => "Washington",
                "WV" => "West Virginia",
                "WI" => "Wisconsin",
                "WY" => "Wyoming"
            )
        );

        return $address_types;
    }

    /**
     * gform disable email notification for a form
     *
     * @param $is_disabled
     * @param $notification
     * @param $form
     * @param $entry
     *
     * @return bool
     */
    public function gform_disable_notification($is_disabled, $notification, $form, $entry)
    {
        $this->gform_form             = $form;
        $this->gform_ccb_api_settings = $this->_get_gform_ccb_api_settings();
        if ($this->gform_ccb_api_settings == 'individual_profile_from_login_password')
        {
            return TRUE;
        }
        else
        {
            return $is_disabled;
        }
    }

    /**
     * get form related API
     *
     * @return bool
     */
    protected function _get_gform_ccb_api_settings()
    {
        if ( ! empty($this->gform_form['ccb_api_settings']))
        {
            return $this->gform_form['ccb_api_settings'];
        }

        return FALSE;
    }

    /**
     * gform add custom attributes for fields
     *
     * @param $field_content
     * @param $field
     * @param $value
     * @param $lead_id
     * @param $form_id
     *
     * @return mixed|string
     */
    public function gform_add_custom_attr($field_content, $field, $value, $lead_id, $form_id)
    {
        $user_profile = isset($_SESSION['ccb_plugin']['user_profile']) ? $_SESSION['ccb_plugin']['user_profile'] : NULL;

        if (isset($field->ccbField) && ! empty($field->ccbField != ''))
        {
            if (is_array($field->ccbField) && ((int)1 < count($field->ccbField)))
            {
                $temp        = explode('name=', $field_content);
                $start_index = 1;
                foreach ($field->ccbField as $index => $item)
                {
                    if (isset($temp[$start_index]))
                    {
                        $temp[$start_index] = "ccb-field='" . $item . "' name=" . $temp[$start_index];

                        // pre-fill value
                        $prefill_val = '';

                        if ($item == 'create_individual.first_name')
                        {
                            $prefill_val = isset($user_profile['individual.first_name']) ? $user_profile['individual.first_name'] : '';

                        }
                        else if ($item == 'create_individual.last_name')
                        {
                            $prefill_val = isset($user_profile['individual.last_name']) ? $user_profile['individual.last_name'] : '';
                        }

                        if ( ! empty($prefill_val))
                        {
                            $temp[$start_index] = str_replace("value=''", "value='{$prefill_val}'", $temp[$start_index]);
                        }
                    }
                    $start_index++;
                }

                $field_content = implode(' ', $temp);

            }
            else if (is_array($field->ccbField) && ((int)1 == count($field->ccbField)))
            {
                $item = $field->ccbField[0];
                if ($item == 'autofill_with_user_data' && ! CCB_GRAVITY_manage_session::if_user_logged_in())
                {
                    $field_content = '';
                }
                else
                {
                    $field_content = str_replace('name=', "ccb-field='" . $item . "' name=", $field_content);

                    global $post;
                    global $wp;
                    $current_url = home_url(add_query_arg(array(), $wp->request));

                    $prefill_val = '';

                    if ($item == 'create_individual.email')
                    {
                        $prefill_val = isset($user_profile['individual.email']) ? $user_profile['individual.email'] : '';

                    }
                    else if (in_array($item, ['create_individual.contact_phone', 'create_individual.home_phone', 'create_individual.work_phone', 'create_individual.mobile_phone']))
                    {
                        $prefill_val = isset($user_profile['individual.phone']) ? $user_profile['individual.phone'] : '';

                    }
                    else if ($item == 'add_individual_to_event.event_id')
                    {
                        $event_id    = NULL;
                        if (isset($post->post_type) && $post->post_type == 'lo-events')
                        {
                            $event_id = get_post_meta($post->ID, 'lo_ccb_events_ccb_event_id', TRUE);
                        }
                        $prefill_val = ! empty($event_id) ? $event_id : $current_url;

                    }
                    else if ($item == 'add_individual_to_event.id')
                    {
                        $prefill_val = isset($user_profile['individual.id']) ? $user_profile['individual.id'] : '';
                    }
                    else if ($item == 'add_individual_to_group.id')
                    {
                        $prefill_val = isset($user_profile['individual.id']) ? $user_profile['individual.id'] : '';
                    }
                    else if ($item == 'add_individual_to_group.group_id')
                    {
                        $group_id    = NULL;
                        if (isset($post->post_type) && $post->post_type == 'lo-events')
                        {
                            $group_id = get_post_meta($post->ID, 'lo_ccb_events_group_id', TRUE);
                        }
                        $prefill_val = ! empty($group_id) ? $group_id : $current_url;
                    }

                    if ( ! empty($prefill_val))
                    {
                        $field_content = str_replace("value=''", "value='{$prefill_val}'", $field_content);
                    }

                }
            }
            else
            {
                $field_content = str_replace('name=', "ccb-field='" . $field->ccbField . "' name=", $field_content);
            }
        }

        return $field_content;
    }

    /**
     * pre render changes for gform
     *
     * @param $form
     *
     * @return array
     */
    public function gform_pre_render($form)
    {
        $this->gform_form             = $form;
        $this->gform_ccb_api_settings = $this->_get_gform_ccb_api_settings();
        $this->gform_api_field        = $this->_get_gform_api_field();

        if ($this->gform_ccb_api_settings == 'add_individual_to_event')
        {
            if (CCB_GRAVITY_manage_session::if_user_logged_in())
            {
//                $args = array();
//                $this->gform_form['description'] = CCB_GRAVITY_Template_Loader::get_template('gform/ccb-gform-autofill', $args);
                $this->gform_form['description'] = '';
            }
        }

        return $this->gform_form;
    }

    /**
     * custom confirmation for gform - add to event
     *
     * @param $confirmation
     * @param $form
     * @param $entry
     * @param $ajax
     *
     * @return string
     */
    public function gform_custom_confirmation_add_individual_to_event($confirmation, $form, $entry, $ajax)
    {
        $this->gform_form             = $form;
        $this->gform_ccb_api_settings = $this->_get_gform_ccb_api_settings();

        $args = array(
            'form_id' => $form['id']
        );

        return CCB_GRAVITY_Template_Loader::get_template('gform/ccb-gform-add-to-event-confirm', $args);
    }

    /**
     * custom confirmation for gform
     *
     * @param $confirmation
     * @param $form
     * @param $entry
     * @param $ajax
     *
     * @return string
     */
    public function gform_custom_confirmation_individual_profile_from_login_password($confirmation, $form, $entry, $ajax)
    {
        $this->gform_form             = $form;
        $this->gform_ccb_api_settings = $this->_get_gform_ccb_api_settings();

        if (CCB_GRAVITY_manage_session::if_user_logged_in())
        {
            $args = array(
                'user_data' => isset($_SESSION['ccb_plugin']['user_profile']) ? $_SESSION['ccb_plugin']['user_profile'] : array()
            );

            return CCB_GRAVITY_Template_Loader::get_template('gform/ccb-gform-user-logged-in', $args);
        }
    }

    /**
     * Gravity Form Validate API Call
     *
     * @param $validation_result
     *
     * @return array
     */
    function gform_validation_api_call($validation_result)
    {
        $this->gform_form             = $validation_result['form'];
        $this->gform_ccb_api_settings = $this->_get_gform_ccb_api_settings();
        $this->gform_api_field        = $this->_get_gform_api_field();

        $api_validation = array();

        /**
         * API Call class
         */
        if ($this->gform_ccb_api_settings == 'individual_profile_from_login_password')
        {

            $api_validation = $this->_initiate_user_login();

        }
        else if ($this->gform_ccb_api_settings == 'add_individual_to_event')
        {

            $api_validation = $this->_check_event_limit();

        }

        $validation_result = array_merge($validation_result, $api_validation);

        $validation_result['form'] = $this->gform_form;

        return $validation_result;

    }

    /**
     * get ccb api field lists with value
     *
     * @return array
     */
    protected function _get_gform_api_field()
    {
        if ( ! isset($_POST['gform_submit']))
        {
            return array();
        }

        $repeaterChildren = $nonRepeaterFields = array();

        $api_field = array();

        if ( ! empty($this->gform_form['fields']))
        {

            foreach ($this->gform_form['fields'] as $index => $item)
            {

                if (key_exists('ccbField', $item))
                {

                    if (is_a($item, 'GF_Field_Repeater'))
                    {

                        $repeaterVal = json_decode(rgpost('input_' . str_replace('.', '_', $item['id'])), 1);

                        foreach ($repeaterVal['children'] as $rindex => $rchild)
                        {

                            foreach ($rchild['inputs'] as $cindex => $cinput)
                            {

                                for ($i = 1; $i <= $repeaterVal['repeatCount']; $i++)
                                {

                                    $repeaterInputIds[$rindex][$cinput][] = str_replace('.', '_', $cinput) . '-' . $repeaterVal['repeaterId'] . '-' . $i;

                                }
                            }
                        }

                        $repeaterChildren = $repeaterInputIds;
                        break;
                    }
                }
            }

            foreach ($this->gform_form['fields'] as $index => $item)
            {

                $ccbIndex = 0;

                if (key_exists('ccbField', $item))
                {

                    if ( ! is_array($item['ccbField']))
                    {
                        $ccbField = array($item['ccbField']);
                    }
                    else
                    {

                        $ccbField = $item['ccbField'];
                    }

                    if ($ccbField[0] == 'null')
                    {
                        continue;
                    }

                    if (key_exists('inputs', $item) && $item['inputs'] != NULL && is_array($item['inputs']))
                    {

                        $input_id = array();

                        foreach ($item['inputs'] as $k => $v)
                        {

                            if (array_key_exists('isHidden', $v) && $v['isHidden'] === TRUE)
                            {
                                continue;
                            }

                            if (array_key_exists($item['id'], $repeaterChildren))
                            {

                                foreach ($repeaterChildren[$item['id']]['input_' . $v['id']] as $rcindex => $repeaterChild)
                                {

                                    $input_id[$ccbField[$ccbIndex]][] = $repeaterChild;
                                }

                            }
                            else
                            {

                                $input_id[$ccbField[$ccbIndex]] = 'input_' . str_replace('.', '_', $v['id']);
                            }

                            $ccbIndex++;

                        }

                    }
                    else
                    {

                        if (array_key_exists($item['id'], $repeaterChildren))
                        {

                            $input_id = array();

                            foreach ($repeaterChildren[$item['id']]['input_' . $item['id']] as $rcindex => $repeaterChild)
                            {

                                $input_id[$ccbField[$ccbIndex]][] = $repeaterChild;
                            }

                        }
                        else
                        {

                            $input_id = 'input_' . $item['id'];
                        }

                    }

                    if (is_array($input_id))
                    {
                        foreach ($input_id as $inp_k => $inp_v)
                        {
                            if (is_array($inp_v))
                            {

                                foreach ($inp_v as $inp2_k => $inp2_v)
                                {
                                    $api_field[$inp_k][] = rgpost($inp2_v);
                                }

                            }
                            else
                            {

                                $api_field[$inp_k] = rgpost($inp_v);
                            }
                        }
                    }
                    else
                    {

                        if (is_array($ccbField))
                        {

                            $api_field[$ccbField[0]] = rgpost($input_id);
                        }
                        else
                        {

                            $api_field[$ccbField] = rgpost($input_id);
                        }
                    }
                }

            }

        }

        return $api_field;
    }

    /**
     * user login for gform
     *
     * @return array
     */
    protected function _initiate_user_login()
    {
        $validation = array();

        if (CCB_GRAVITY_manage_session::if_user_logged_in())
        {
            wp_redirect($this->referrer_url);
            exit();
        }

        $this->plugin->gravity_api_login->gform_api_map();

        if ( ! empty($this->plugin->gravity_api_login->api_error))
        {
            $this->_mark_gform_api_field_error($this->plugin->gravity_api_login->api_error);
            $validation['is_valid'] = FALSE;
        }
        else
        {
            $this->_get_individual_groups();
        }

        return $validation;
    }

    /**
     * mark validation error for a gform field
     *
     * @param $api_err
     */
    protected function _mark_gform_api_field_error($api_err)
    {
        if ( ! empty($this->gform_form['fields']))
        {
            foreach ($this->gform_form['fields'] as $index => &$item)
            {
                if (key_exists('ccbField', $item))
                {
                    $item->failed_validation  = TRUE;
                    $item->validation_message = $api_err['error_msg'];
                }
                break;
            }
        }
    }

    /**
     * get group for a logged in individual
     *
     * @return bool
     */
    protected function _get_individual_groups()
    {
        if ( ! CCB_GRAVITY_manage_session::if_user_logged_in())
        {
            wp_redirect($this->referrer_url);
            exit();
        }

        if ( ! isset($_SESSION['ccb_plugin']['user_profile']['individual.id']))
        {
            return FALSE;
        }

        $this->plugin->gravity_api_individual_groups->gform_api_map();
    }

	/**
	 * Check Event Limit
	 *
	 * @return array
	 */
    public function _check_event_limit()
    {
        $api_error           = FALSE;
        $event_limit_reached = FALSE;
        $validation          = array();

        if ($this->gform_api_field['add_individual_to_event.event_id'] == '')
        {
            return $validation;
        }

        $previous_event_count = get_option('ccb_event_count_' . $this->gform_api_field['add_individual_to_event.event_id']);
        if (empty($previous_event_count))
        {
            $previous_event_count = 0;
        }

        $this->plugin->gravity_api_get_event_profile->gform_api_map();

        if ( ! empty($this->plugin->gravity_api_get_event_profile->api_error))
        {

            $api_error              = TRUE;
            $validation['is_valid'] = FALSE;
        }
        else
        {
            if (isset($this->plugin->gravity_api_get_event_profile->api_response_arr['ccb_api']['response']['events']['event']['registration']['limit']))
            {

                $event_limit = $this->plugin->gravity_api_get_event_profile->api_response_arr['ccb_api']['response']['events']['event']['registration']['limit'];

                if ($previous_event_count >= $event_limit)
                {
                    $event_limit_reached    = TRUE;
                    $validation['is_valid'] = FALSE;
                }

            }
            else
            {
                $api_error              = TRUE;
                $validation['is_valid'] = FALSE;
            }

        }

        if ($api_error == TRUE || $event_limit_reached == TRUE)
        {

            if ( ! empty($this->gform_form['fields']))
            {
                foreach ($this->gform_form['fields'] as $index => &$item)
                {

                    if ($item['type'] == 'hidden')
                    {
                        continue;
                    }

                    if (key_exists('ccbField', $item))
                    {
                        $item->failed_validation = TRUE;

                        if ($api_error == TRUE)
                        {

                            $item->validation_message = 'Event limit check error, please try again and if the issue persist please contact customer care';
                        }
                        else if ($event_limit_reached == TRUE)
                        {

                            $item->validation_message = 'Event limit reached, Please try another event timing';
                        }
                    }
                    break;
                }
            }
        }

        return $validation;
    }

    /**
     * gform before form submit and entry created
     *
     * @param $form
     */
    public function gform_before_submission($form)
    {
        $ccb_gform_hash  = md5(json_encode($_POST));
        $sess_gform_hash = isset($_SESSION['ccb_plugin']['ccb_gform_hash']) ? $_SESSION['ccb_plugin']['ccb_gform_hash'] : array();
        if (in_array($ccb_gform_hash, $sess_gform_hash))
        {
            wp_redirect($this->referrer_url);
            exit();
        }
    }

    /**
     * gform after form submit and entry created
     *
     * @param $entry
     * @param $form
     */
    public function gform_after_submission($entry, $form)
    {
        $ccb_gform_sess_hash = md5(json_encode($_POST));
        if (isset($_SESSION['ccb_plugin']['ccb_gform_hash']))
        {
            $_SESSION['ccb_plugin']['ccb_gform_hash'] = array_merge($_SESSION['ccb_plugin']['ccb_gform_hash'], array($ccb_gform_sess_hash));
        }
        else
        {
            $_SESSION['ccb_plugin']['ccb_gform_hash'] = array($ccb_gform_sess_hash);
        }

        $this->gform_form             = $form;
        $this->gform_entry            = $entry;
        $this->gform_entry_id         = $entry['id'];
        $this->gform_ccb_api_settings = $this->_get_gform_ccb_api_settings();
        $this->_gform_after_submit_api_action();
    }

    /**
     * gform appropriate action after form submit and entry created
     */
    protected function _gform_after_submit_api_action()
    {
        if ($this->gform_ccb_api_settings == 'individual_profile_from_login_password')
        {

            $update_field_id = $this->get_ccb_field('login.password');
            $this->_gform_update_entry_field($update_field_id);

        }
        else if ($this->gform_ccb_api_settings == 'add_individual_to_event')
        {

            $api_data = $this->get_api_data($this->gform_form, $this->gform_entry);
            gform_update_meta($this->gform_entry_id, 'api_data', $api_data);

            do_action('ccb_gform_sync_hook', $this->gform_entry_id);
        }
    }

    /**
     * get all ccb field defined in form
     *
     * @param string $field_type
     *
     * @return bool|WP_Error
     */
    protected function get_ccb_field($field_type = '')
    {
        if (empty($this->gform_form))
        {
            return new WP_Error('form_obj_not_found', sprintf(__('Form object not found', 'ccb-gravity')), $this->gform_entry);
        }

        if (empty($field_type))
        {
            return new WP_Error('no_field_name_specified', sprintf(__('No Fieldname specified for entry', 'ccb-gravity')), $this->gform_entry);
        }

        foreach ($this->gform_form['fields'] as $k => $v)
        {
            if (isset($v['ccbField']))
            {
                $fieldFound = FALSE;
                if (is_array($v['ccbField']))
                {
                    if (in_array($field_type, $v['ccbField']))
                    {
                        $fieldFound = TRUE;
                    }
                }
                else
                {
                    if ($field_type == $v['ccbField'])
                    {
                        $fieldFound = TRUE;
                    }
                }

                if ($fieldFound == TRUE)
                {
                    return $v['id'];
                    break;
                }
            }
        }

        return FALSE;
    }

    /**
     * update entry field value
     *
     * @param null $field_id
     *
     * @return array|bool|WP_Error
     */
    protected function _gform_update_entry_field($field_id = NULL, $update_value = 'removed')
    {
        if (empty($this->gform_entry_id))
        {
            return new WP_Error('not_found', sprintf(__('Entry with id %s not found', 'ccb-gravity'), $this->gform_entry_id), $this->gform_entry_id);
        }

        if ($field_id == NULL)
        {
            return new WP_Error('not_found', sprintf(__('No Fieldname specified for entry', 'ccb-gravity')), $this->gform_entry);
        }

        return GFAPI::update_entry_field($this->gform_entry_id, $field_id, $update_value);
    }

    /**
     * get api data for entry meta after submit
     *
     * @param $form_metas
     * @param $entry
     *
     * @return array
     */
    protected function get_api_data($form_metas, $entry)
    {

        $api_data = array(
            'primary'   => array(),
            'secondary' => array(),
        );

        foreach ($form_metas['fields'] as $index => $field)
        {

            if ( ! is_array($field['ccbField']))
            {
                $ccbField = array($field['ccbField']);
            }
            else
            {
                $ccbField = $field['ccbField'];
            }

            // *********** processing values for add_individual_to_event *************** //
            if (in_array('add_individual_to_event.id', $ccbField))
            {
                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('event_individual_id' => rgar($entry, $field['id'])));
            }
            else if (in_array('add_individual_to_event.event_id', $ccbField))
            {
                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('event_id' => rgar($entry, $field['id'])));
            }
            else if (in_array('add_individual_to_event.status', $ccbField))
            {
                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('event_status' => rgar($entry, $field['id'])));
            }


            // *********** processing values for create_individual *************** //
            else if (in_array('create_individual.first_name', $ccbField))
            {
                //@todo:: make entry id dynamic array('first_name' => rgar($entry, 1.3))
                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('first_name' => rgar($entry, '1.3')));

                if (in_array('create_individual.last_name', $ccbField))
                {
                    //@todo:: make entry id dynamic array('last_name' => rgar($entry, 1.6))
                    $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('last_name' => rgar($entry, '1.6')));
                }
            }
            else if (in_array('create_individual.email', $ccbField))
            {
                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('email' => rgar($entry, $field['id'])));
            }
            else if (
                in_array('create_individual.contact_phone', $ccbField) ||
                in_array('create_individual.work_phone', $ccbField) ||
                in_array('create_individual.home_phone', $ccbField) ||
                in_array('create_individual.mobile_phone', $ccbField)
            )
            {
                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('phone' => rgar($entry, $field['id'])));
            }


            // *********** processing values for add_individual_to_group *************** //
            else if (in_array('add_individual_to_group.id', $ccbField))
            {

                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('group_individual_id' => rgar($entry, $field['id'])));

            }
            else if (in_array('add_individual_to_group.group_id', $ccbField))
            {

                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('group_id' => rgar($entry, $field['id'])));

            }
            else if (in_array('add_individual_to_group.status', $ccbField))
            {

                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('group_status' => rgar($entry, $field['id'])));

            }


            //below conditions may need modifications
            else if (in_array('individual.member.ids', $ccbField))
            {

                $field_value = rgar($entry, $field['id']);
                if ( ! empty($field_value))
                {
                    $extra_individual_ids_arr = explode('|', $field_value);
                    $extra_individual_ids     = array();
                    foreach ($extra_individual_ids_arr as $extra_indv_indx => $extra_indv_val)
                    {
                        $extra_individual_ids[] = array(
                            'individual_id' => $extra_indv_val
                        );
                    }
                    $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('extra_individual_ids' => $extra_individual_ids));
                }

            }
            else if (in_array('individual.family.id', $ccbField))
            {

                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('family_id' => rgar($entry, $field['id'])));

            }
            else if (in_array('ccb.individual.data', $ccbField))
            {

                $field_value = GFFormsModel::unserialize(rgar($entry, $field['id']));
                $tmp_api_add = array();

                foreach ($field_value as $i => $item)
                {
                    $index = 'secondary';
                    if ($i == 1)
                    {
                        $index = 'primary';
                    }

                    $tmp_api_add[$index]['individual'][] = $this->fetch_individual_data($form_metas['fields'], $item);

                }

                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], $tmp_api_add['primary']);

                if (isset($tmp_api_add['secondary']))
                {
                    $api_data['secondary'] = $this->arr_check_empty_merge($api_data['secondary'], $tmp_api_add['secondary']);
                }

            }
            else if (in_array('event.ids', $ccbField))
            {
                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('event_set' => rgar($entry, $field['id'])));

            }
            else if (in_array('campus.id', $ccbField))
            {

                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('campus_id' => rgar($entry, $field['id'])));

            }
            else if (in_array('event.register_user_type', $ccbField))
            {

                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('register_type' => rgar($entry, $field['id'])));

            }
            else if (in_array('group.community.new.name', $ccbField))
            {

                $api_data['primary'] = $this->arr_check_empty_merge($api_data['primary'], array('community_group_name' => rgar($entry, $field['id'])));

            }
        }

        return $api_data;
    }

    /**
     * merge array if not empty else assign
     *
     * @param $main
     * @param $data
     *
     * @return array
     */
    public function arr_check_empty_merge($main, $data)
    {
        if ( ! empty($main))
        {
            $main = array_merge($main, $data);
        }
        else
        {
            $main = $data;
        }

        return $main;
    }

    /**
     * getting individual data in a specific format
     * (fname, lname, email, age)
     *
     * @param $form_metas
     * @param $item
     *
     * @return array
     */
    public function fetch_individual_data($form_metas, $item)
    {
        $tmp = array();
        foreach ($item as $index => $value)
        {

            $form_meta = $this->search_array_assoc($form_metas, 'id', $index);
            array(
                'fname' => '',
                'lname' => '',
                'email' => '',
                'age'   => '',

            );

            if (in_array('individual.first_name', $form_meta['ccbField']))
            {
                $tmp = array_merge($tmp, array(
                    'fname' => isset($value[0]) ? $value[0] : '',
                    'lname' => isset($value[1]) ? $value[1] : '',
                ));
            }
            else if (in_array('individual.email', $form_meta['ccbField']))
            {
                $tmp = array_merge($tmp, array(
                    'email' => isset($value[0]) ? $value[0] : '',
                ));
            }
            else if (in_array('individual.age', $form_meta['ccbField']))
            {
                $tmp = array_merge($tmp, array(
                    'age' => isset($value[0]) ? $value[0] : '',
                ));
            }

        }

        return $tmp;
    }

    /**
     * search array for a matching value - single level
     *
     * @param $arr
     * @param $field
     * @param $value
     *
     * @return mixed
     */
    function search_array_assoc($arr, $field, $value)
    {
        foreach ($arr as $data)
        {
            if ($data[$field] == $value)
            {
                return $data;
            }
        }
    }

    /**
     * gform delete entry after form submission
     *
     * @return bool|mixed
     */
    protected function _gform_delete_entry()
    {
        if (empty($this->gform_entry_id))
        {
            return FALSE;
        }

        return GFAPI::delete_entry($this->gform_entry_id);
    }

}