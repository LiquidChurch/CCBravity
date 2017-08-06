<?php
/**
 * Plugin Name: CCB Gravity Functionality
 * Plugin URI:  http://www.liquidchurch.com/
 * Description: CCB API and Gravity Form Integration
 * Version:     1.0.0
 * Author:      Suraj Gupta, Dave Mackey, Liquidchurch
 * Author URI:  http://www.liquidchurch.com/
 * Donate link: http://www.liquidchurch.com/
 * License:     GPLv2
 * Text Domain: ccb-gravity
 * Domain Path: /languages
 *
 * @link    http://www.liquidchurch.com/
 *
 * @package CCB Gravity
 * @version 1.0.0
 */

/**
 * Copyright (c) 2016 Suraj Pr Gupta (email : suraj.gupta@scripterz.in)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

//define CCB_ENV
define('CCB_ENV', 'testing');

// User composer autoload.
require __DIR__ . '/vendor/autoload.php';

/**
 * Main initiation class
 *
 * @since  1.0.0
 */
class CCB_GRAVITY_Functionality
{

    /**
     * Current version
     *
     * @var  string
     * @since  1.0.0
     */
    const VERSION                       = '1.0.0';
    const CCB_COMMUNITY_GROUP_LEADER_ID = 35917;
    /**
     * Path of plugin directory
     *
     * @var string
     * @since  1.0.0
     */
    public static $path = '';
    /**
     * Singleton instance of plugin
     *
     * @var CCB_GRAVITY_Functionality
     * @since  1.0.0
     */
    protected static $single_instance = NULL;
    /**
     * URL of plugin directory
     *
     * @var string
     * @since  1.0.0
     */
    protected $url = '';
    /**
     * Plugin basename
     *
     * @var string
     * @since  1.0.0
     */
    protected $basename = '';
    /**
     * Instance of CCB_GRAVITY_Config_Page
     *
     * @since scripterz-mods
     * @var CCB_GRAVITY_Config_Page
     */
    protected $config_page;
    protected $gravity_mods;


    /**
     * @var array
     * @since 0.1.0
     */
    protected $gform_enabled_ccb_services = [
        'individual_profile_from_login_password' => 'User login form',
        'add_individual_to_event'                => 'Individual Event registration form',
    ];

    /**
     * @var array
     * @since 0.1.0
     */
    protected $gform_api_field_map = [];

    /**
     * Sets up our plugin
     *
     * @since  1.0.0
     */
    protected function __construct()
    {
        $this->basename            = plugin_basename(__FILE__);
        $this->url                 = plugin_dir_url(__FILE__);
        self::$path                = plugin_dir_path(__FILE__);
        $this->gform_api_field_map = [
            'individual_profile_from_login_password' => CCB_GRAVITY_api_login::$link_api_fields,
            'add_individual_to_event'                => CCB_GRAVITY_api_add_to_event::$link_api_fields,
        ];
    }

    /**
     * Creates or returns an instance of this class.
     *
     * @since  1.0.0
     * @return CCB_GRAVITY_Functionality A single instance of this class.
     */
    public static function get_instance()
    {
        if (NULL === self::$single_instance)
        {
            self::$single_instance = new self();
        }

        return self::$single_instance;
    }

    /**
     * Include a file from the includes directory
     *
     * @since  1.0.0
     *
     * @param  string $filename Name of the file to be included.
     *
     * @return bool   Result of include call.
     */
    public static function include_file($filename)
    {
        $file = self::dir($filename . '.php');
        if (file_exists($file))
        {
            return include_once($file);
        }

        return FALSE;
    } // END OF PLUGIN CLASSES FUNCTION

    /**
     * This plugin's directory
     *
     * @since  1.0.0
     *
     * @param  string $path (optional) appended path.
     *
     * @return string       Directory and path
     */
    public static function dir($path = '')
    {
        static $dir;
        $dir = $dir ? $dir : trailingslashit(dirname(__FILE__));

        return $dir . $path;
    }

    /**
     * Add hooks and filters
     *
     * @since  1.0.0
     * @return void
     */
    public function hooks()
    {
        if (class_exists("GFForms"))
        {

            new GFRepeater();
            GF_Fields::register(new GF_Field_Repeater());
            GF_Fields::register(new GF_Field_Repeater_End());
        }

        add_action('init', array($this, 'init'));
    }

    /**
     * Activate the plugin
     *
     * @since  1.0.0
     * @return void
     */
    public function _activate()
    {
        // Make sure any rewrite functionality has been loaded.
        flush_rewrite_rules();
    }

    /**
     * Deactivate the plugin
     * Uninstall routines should be in uninstall.php
     *
     * @since  1.0.0
     * @return void
     */
    public function _deactivate()
    {
    }

    /**
     * Init hooks
     *
     * @since  1.0.0
     * @return void
     */
    public function init()
    {
        if ($this->check_requirements())
        {
            load_plugin_textdomain('ccb-gravity', FALSE, dirname($this->basename) . '/languages/');

            add_action('admin_menu', array($this, 'add_admin_menu_page'));

            $this->plugin_classes();
            $this->enque_script();
            $this->enque_style();
        }
    }

    /**
     * Check if the plugin meets requirements and
     * disable it if they are not present.
     *
     * @since  1.0.0
     * @return boolean result of meets_requirements
     */
    public function check_requirements()
    {
        if ( ! $this->meets_requirements())
        {

            // Add a dashboard notice.
            add_action('all_admin_notices', array($this, 'requirements_not_met_notice'));

            // Deactivate our plugin.
            add_action('admin_init', array($this, 'deactivate_me'));

            return FALSE;
        }

        return TRUE;
    }

    /**
     * Check that all plugin requirements are met
     *
     * @since  1.0.0
     * @return boolean True if requirements are met.
     */
    public static function meets_requirements()
    {
        // Do checks for required classes / functions
        // function_exists('') & class_exists('').
        // We have met all requirements.
        return class_exists('GFForms');
    }

    /**
     * Attach other plugin classes to the base plugin class.
     *
     * @since  1.0.0
     * @return void
     */
    public function plugin_classes()
    {
        $this->add_dev_classes();

        $this->shortcode        = new CCB_Shortcodes($this);
        $this->session          = new CCB_GRAVITY_manage_session($this);
        $this->action           = new CCB_GRAVITY_action_handler($this);
        $this->action_ajax      = new CCB_GRAVITY_ajax_handler($this);
        $this->gravity_api_cron = new CCB_GRAVITY_cron_handler($this);

        // Only create the full metabox object if in the admin.
        if (is_admin())
        {
            $this->config_page  = CCB_GRAVITY_option_settings::get_instance();
            $this->gravity_mods = new CCB_GRAVITY_form_mods($this);
        } else
        {
            $this->gravity_render                = new CCB_GRAVITY_form_render($this);
            $this->gravity_api_login             = new CCB_GRAVITY_api_login($this);
            $this->gravity_api_individual_groups = new CCB_GRAVITY_api_individual_groups($this);
        }

        $this->gravity_api_sync_ccb                = new CCB_GRAVITY_api_sync_ccb($this);
        $this->gravity_api_create_individual       = new CCB_GRAVITY_api_create_individual($this);
        $this->gravity_api_create_group            = new CCB_GRAVITY_api_create_group($this);
        $this->gravity_api_add_individual_to_group = new CCB_GRAVITY_api_add_individual_to_group($this);
        $this->gravity_api_add_to_event            = new CCB_GRAVITY_api_add_to_event($this);
        $this->gravity_api_group_participants      = new CCB_GRAVITY_api_group_participants($this);
        $this->gravity_api_get_individual_profile  = new CCB_GRAVITY_api_get_individual_profile($this);
        $this->gravity_api_get_event_profile       = new CCB_GRAVITY_api_event_profile($this);
        $this->gravity_api_get_attendance_profile  = new CCB_GRAVITY_api_attendance_profile($this);
    }

    public function add_dev_classes()
    {
        if (defined('CCB_ENV') && CCB_ENV == 'development')
        {
            if (file_exists(__DIR__ . '/dev/WP_Logging.php'))
            {
                include __DIR__ . '/dev/WP_Logging.php';
            }
        }
        include __DIR__ . '/dev/Logging_Mods.php';
    }

    protected function enque_script()
    {
        $admin_page = rgget('page');
        $min        = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        if (in_array($admin_page, array('gf_edit_forms', 'gf_entries')))
        {
            wp_enqueue_script(
                'ccb-blockui',
                CCB_GRAVITY_Functionality::url("assets/node_modules/block-ui/jquery.blockUI{$min}.js"),
                array('jquery'),
                CCB_GRAVITY_Functionality::VERSION
            );

            wp_enqueue_script(
                'ccb-gravity-admin',
                CCB_GRAVITY_Functionality::url("assets/js/ccb-gravity-admin{$min}.js"),
                array('jquery'),
                CCB_GRAVITY_Functionality::VERSION
            );
        } else if (in_array($admin_page, array('ccb_report')))
        {

            wp_enqueue_script(
                'jquery-datatable',
                '//cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js',
                array(),
                CCB_GRAVITY_Functionality::VERSION
            );

            wp_enqueue_script(
                'chart-js',
                CCB_GRAVITY_Functionality::url("assets/bower_components/chart.js/dist/Chart.bundle{$min}.js"),
                array(),
                CCB_GRAVITY_Functionality::VERSION
            );
        }
    }

    /**
     * This plugin's url
     *
     * @since  1.0.0
     *
     * @param  string $path (optional) appended path.
     *
     * @return string       URL and path
     */
    public static function url($path = '')
    {
        static $url;
        $url = $url ? $url : trailingslashit(plugin_dir_url(__FILE__));

        return $url . $path;
    }

    protected function enque_style()
    {
        $admin_page = rgget('page');
        $min        = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        if (in_array($admin_page, array('gf_edit_forms', 'gf_entries')))
        {

            wp_enqueue_style(
                'ccb-gravity-admin-css',
                CCB_GRAVITY_Functionality::url("assets/css/ccb-gravity-admin{$min}.css"),
                array(),
                CCB_GRAVITY_Functionality::VERSION
            );

        } else if (in_array($admin_page, array('ccb_report')))
        {

            wp_enqueue_style(
                'jquery-datatable-css',
                '//cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css',
                array(),
                CCB_GRAVITY_Functionality::VERSION
            );
        }
    }

    /**
     * admin menu pages for the plugin
     *
     * @since  1.0.0
     * @return void
     */
    public function add_admin_menu_page()
    {
        add_menu_page('CCB GRAVITY', __('CCB GRAVITY', 'ccb-gravity'), 'manage_options', 'ccb-gravity', array($this, 'page_ccb_gravity_index'));
    }

    public function page_ccb_gravity_index()
    {
        echo 'page_ccb_gravity_index';
    }

    /**
     * Deactivates this plugin, hook this function on admin_init.
     *
     * @since  1.0.0
     * @return void
     */
    public function deactivate_me()
    {
        deactivate_plugins($this->basename);
    }

    /**
     * Adds a notice to the dashboard if the plugin requirements are not met
     *
     * @since  1.0.0
     * @return void
     */
    public function requirements_not_met_notice()
    {
        // Output our error.
        echo '<div id="message" class="error">';
        echo '<p>' . sprintf(__('CCB GRAVITY Functionality is missing the Gravity Form plugin and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'ccb-gravity'), admin_url('plugins.php')) . '</p>';
        echo '</div>';
    }

    /**
     * Magic getter for our object.
     *
     * @since  1.0.0
     *
     * @param string $field Field to get.
     *
     * @throws Exception Throws an exception if the field is invalid.
     * @return mixed
     */
    public function __get($field)
    {
        switch ($field)
        {
            case 'version':
                return self::VERSION;
            case 'basename':
            case 'url':
            case 'path':
            case 'gform_enabled_ccb_services':
            case 'gform_api_field_map':
                return $this->{$field};
            default:
                throw new Exception('Invalid ' . __CLASS__ . ' property: ' . $field);
        }
    }
}

/**
 * Grab the CCB_GRAVITY_Functionality object and return it.
 * Wrapper for CCB_GRAVITY_Functionality::get_instance()
 *
 * @since  1.0.0
 * @return CCB_GRAVITY_Functionality  Singleton instance of plugin class.
 */
function ccb_gravity_func()
{
    return CCB_GRAVITY_Functionality::get_instance();
}

// Kick it off.
add_action('plugins_loaded', array(ccb_gravity_func(), 'hooks'));

register_activation_hook(__FILE__, array(ccb_gravity_func(), '_activate'));
register_deactivation_hook(__FILE__, array(ccb_gravity_func(), '_deactivate'));

