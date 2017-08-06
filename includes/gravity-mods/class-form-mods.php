<?php

/**
 * CCB GRAVITY form mods
 *
 * @since   1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_form_mods extends CCB_GRAVITY_Abstract
{
    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

    public function hooks()
    {
        /**
         * custom form fields for CCB
         */
        add_action('gform_field_standard_settings', array($this, 'add_related_ccb_field'), 10, 2);
        add_action('gform_editor_js', array($this, 'add_related_ccb_field_script'));
        add_filter('gform_tooltips', array($this, 'add_related_ccb_field_tooltips'));

        /**
         * Custom form settings for CCB
         */
        add_filter('gform_form_settings', array($this, 'add_ccb_settings'), 10, 2);
        add_filter('gform_pre_form_settings_save', array($this, 'save_ccb_setting'));

        /**
         * entries mods
         */
        add_action('gform_entries_first_column_actions', array($this, 'first_column_actions'), 10, 4);
        add_filter('gform_form_actions', array($this, 'add_addtnl_form_action'), 10, 4);

        add_action('admin_menu', array($this, 'add_admin_menu_page'));
    }

    public function add_admin_menu_page()
    {
        add_submenu_page(
            'gf_edit_forms',
            'CCB Report',
            'CCB Report',
            'manage_options',
            'ccb_report',
            array($this, 'ccb_report_page')
        );
    }

    function ccb_report_page()
    {
        $form_id = rgget('form_id');
        if ( ! empty($form_id))
        {

            $args    = array();
            $form    = GFAPI::get_form($form_id);
            $entries = GFAPI::get_entries($form_id);

            $args = array(
                'form'    => $form,
                'entries' => $entries
            );

            if ($form['ccb_api_settings'] == 'add_individual_to_event')
            {
                return CCB_GRAVITY_Template_Loader::output_template('report/show', $args);
            }

        } else
        {

            $args          = array();
            $forms         = GFAPI::get_forms();
            $filter_forms  = array_filter($forms, function ($v)
            {
                if ($v['ccb_api_settings'] == 'add_individual_to_event')
                {
                    return TRUE;
                }
            });
            $args['forms'] = $filter_forms;

            return CCB_GRAVITY_Template_Loader::output_template('report/index', $args);
        }
    }

    public function add_addtnl_form_action($actions, $form_id)
    {
        $form_details = GFFormsModel::get_form_meta($form_id);

        if (isset($form_details['ccb_api_settings']) && ($form_details['ccb_api_settings'] == 'add_individual_to_event'))
        {

            $actions['ccb_report'] = "| <a href='" . admin_url() . "admin.php?page=ccb_report&form_id=" . $form_id . "' class='ccb-report-btn'>Report</a> ";

        }

        return $actions;

    }

    public function first_column_actions($form_id, $field_id, $value, $entry)
    {
        $form_details = GFFormsModel::get_form_meta($form_id);

        if (isset($form_details['ccb_api_settings']) && ($form_details['ccb_api_settings'] == 'add_individual_to_event'))
        {

            $entry_meta = gform_get_meta($entry['id'], 'api_data');

            if ( ! isset($entry_meta['api_sync']) || ($entry_meta['api_sync'] == FALSE))
            {

                echo "| <a href='javascript:void(0);' data-form-id='" . $form_id . "' data-entry-id='" . $entry['id'] . "' class='sync-ccb'>CCB Sync</a>";
            } else
            {

                echo "| <a href='javascript:void(0);' data-form-id='" . $form_id . "' data-entry-id='" . $entry['id'] . "' class='sync-ccb-complete'>CCB Sync Done</a> ";
            }
        }
    }

    public function add_related_ccb_field($position, $form_id)
    {

        //create settings on position 25 (right after Field Label)
        if ($position == 25)
        {
            ?>
            <li class="ccb_field_settings field_setting">
                <label for="field_admin_label">
                    <?php esc_html_e('CCB Field', 'ccb-gravity'); ?>
                    <?php gform_tooltip('form_ccb_field_value') ?>
                </label>

                <?php
                $this->gen_ccb_select_tag($form_id);
                ?>
            </li>
            <li class="ccb_field_settings field_setting">
                <?php
                $this->gen_ccb_field_report_tag($form_id);
                ?>
                <label for="field_admin_label" class="inline">
                    <?php esc_html_e('Enable CCB Report', 'ccb-gravity'); ?>
                    <?php gform_tooltip('form_ccb_field_report') ?>
                </label>
            </li>
            <?php
        }
    }

    private function gen_ccb_field_report_tag($form_id)
    {
        echo '<input type="checkbox" id="ccb_field_report_' . $form_id . '" class="ccb_field_report" />';
//        echo '<select id="ccb_field_report_' . $form_id . '" class="ccb_field_report">';
//        echo '<option value="0"></option>';
//        echo '<option value="1">Yes</option>';
//        echo '<option value="0">No</option>';
//        echo '</select>';
    }

    private function gen_ccb_select_tag($form_id)
    {
        $ccb_service      = $this->plugin->gform_enabled_ccb_services;
        $ccb_field_values = $this->plugin->gform_api_field_map;

        echo '<select id="ccb_field_value_' . $form_id . '" class="ccb_field_value" multiple="multiple">';
        if ( ! empty($ccb_field_values))
        {
            foreach ($ccb_field_values as $i => $items)
            {
                echo '<optgroup label="' . $ccb_service[$i] . '">';
                foreach ($items as $index => $item)
                {
                    echo '<option value="' . $i . '.' . $index . '">' . $index . '</option>';
                }
                echo '</optgroup>';
            }
        }
        echo '</select>';
    }

    public function add_related_ccb_field_script()
    {
        $this->enqueu_js();
        $this->enqueu_css();

        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script(
            'ccb-garvity-mods',
            CCB_GRAVITY_Functionality::url("assets/js/ccb-gravity-form-mods{$min}.js"),
            array('ccb-select2'),
            CCB_GRAVITY_Functionality::VERSION
        );

        wp_localize_script('ccb-garvity-mods', 'CCB',
            array(
                'page' => 'field_settings',
            )
        );

        wp_enqueue_style(
            'ccb-garvity-mods-style',
            CCB_GRAVITY_Functionality::url("assets/css/ccb-gravity-form-mods{$min}.css"),
            array(),
            CCB_GRAVITY_Functionality::VERSION
        );
    }

    public function enqueu_js()
    {
        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script(
            'ccb-select2',
            CCB_GRAVITY_Functionality::url("assets/node_modules/select2/dist/js/select2{$min}.js"),
            array('jquery'),
            CCB_GRAVITY_Functionality::VERSION
        );
    }

    public function enqueu_css()
    {
        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_style(
            'ccb-select2-style',
            CCB_GRAVITY_Functionality::url("assets/node_modules/select2/dist/css/select2{$min}.css"),
            array(),
            CCB_GRAVITY_Functionality::VERSION
        );
    }

    public function add_related_ccb_field_tooltips($tooltips)
    {
        $tooltips['form_ccb_field_value']  = "<h6>CCB Field</h6>" . _('Please enter and select the CCB Field');
        $tooltips['form_ccb_field_report'] = "<h6>Add to Report</h6>" . _('Select the checkbox if you want to include this field into the report');

        return $tooltips;
    }

    public function add_ccb_settings($settings, $form)
    {
        $this->enqueu_js();
        $this->enqueu_css();

        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script(
            'ccb-garvity-mods',
            CCB_GRAVITY_Functionality::url("assets/js/ccb-gravity-form-mods{$min}.js"),
            array('ccb-select2'),
            CCB_GRAVITY_Functionality::VERSION
        );

        wp_localize_script('ccb-garvity-mods', 'CCB',
            array(
                'page'             => 'form_settings',
                'ccb_api_settings' => rgar($form, 'ccb_api_settings')
            )
        );

        $ccb_service_values = $this->plugin->gform_enabled_ccb_services;

        $settings['Form Basics']['ccb_api_settings'] = '<tr><th><label for="ccb_api_settings">CCB API Service</label></th>' .
                                                       '<td><select id="ccb_service_value" class="ccb_service_value" name="ccb_api_settings">';
        if ( ! empty($ccb_service_values))
        {
            foreach ($ccb_service_values as $i => $item)
            {
                $settings['Form Basics']['ccb_api_settings'] .= '<option>' . $item . '</option>';
            }
        }
        $settings['Form Basics']['ccb_api_settings'] .= '</select></td></tr>';

        return $settings;
    }

    public function save_ccb_setting($form)
    {
        $form['ccb_api_settings'] = rgpost('ccb_api_settings');

        return $form;
    }

}