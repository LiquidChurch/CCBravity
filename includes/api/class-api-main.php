<?php

/**
 * CCB GRAVITY API Call
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */
abstract class CCB_GRAVITY_api_main
{
    public $api_error = array();
    public $api_response_arr = array();

    /**
     * Parent plugin class
     *
     * @var   class $plugin
     * @since 1.0.0
     */
    protected $plugin = null;

    /**
     * ajax call detect
     *
     * @var bool|null
     */
    protected $api_name = '';
    protected $ajax_call = null;
    protected $api_base = "https://liquidchurch.ccbchurch.com/api.php";
    protected $api_args = array();
    protected $api_http_resp_code = '';
    protected $api_response = array();
    protected $valid_http_resp_code = array(200, 201);

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->ajax_call = defined('DOING_AJAX') && DOING_AJAX;
        $api_uname = ccb_gravity_get_option('option', 'ccb_gravity_option_ccb_api_username');
        $api_pass = ccb_gravity_get_option('option', 'ccb_gravity_option_ccb_api_password');
        $this->api_args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode("{$api_uname}:{$api_pass}")
            )
        );
    }

    public function hooks()
    {
    }

    /**
     * gform map form with appropriate API call
     */
    public abstract function gform_api_map();

    protected function process_api_response()
    {
        $this->api_wp_error();
        $this->get_api_http_resp_code();
        $this->api_http_resp_code_validation();
        $this->get_api_xml_resp_to_array();
        $this->after_xml_resp_to_array_check_error();
        $this->after_xml_resp_to_array_check_nodata();
    }

    protected function api_wp_error()
    {
        if (is_wp_error($this->api_response)) {
            $this->api_error = array(
                'error_type' => 'wp_error',
                'error' => !empty($this->api_response->get_error_code()),
                'error_code' => $this->api_response->get_error_code(),
                'error_msg' => $this->api_response->get_error_message(),
                'wp_error_obj' => $this->api_response
            );
            ccb_debug('add', array($this->api_name . ' -> api_wp_error', json_encode($this->api_error), 0, 'ccb-api-calls'));
        }
    }

    protected function get_api_http_resp_code()
    {
        if (empty($this->api_error)) {
            $this->api_http_resp_code = wp_remote_retrieve_response_code($this->api_response);
            ccb_debug('add', array($this->api_name . ' -> api_http_resp_code', json_encode($this->api_http_resp_code), 0, 'ccb-api-calls'));
        }
    }

    protected function api_http_resp_code_validation()
    {
        if (!in_array($this->api_http_resp_code, $this->valid_http_resp_code)) {
            $this->api_error = array(
                'error_type' => 'api_error',
                'error' => __('HTTP-' . $this->api_http_resp_code, 'ccb-gravity'),
                'error_code' => __('HTTP-' . $this->api_http_resp_code, 'ccb-gravity'),
                'error_msg' => __('Critical Error, Please contact site administrator', 'ccb-gravity')
            );
            ccb_debug('add', array($this->api_name . ' -> api_http_resp_code_error', json_encode($this->api_error), 0, 'ccb-api-calls'));
        }
    }

    protected function get_api_xml_resp_to_array()
    {
        if (empty($this->api_error)) {
            $body = wp_remote_retrieve_body($this->api_response);
            $this->api_response_arr = $this->xmlToArray(simplexml_load_string($body));
            ccb_debug('add', array($this->api_name . ' -> api_response_arr', json_encode($this->api_response_arr), 0, 'ccb-api-calls'));
        }
    }

	/**
	 * Convert XML to Array
	 *
	 * Convert XML response from CCB API into a PHP Array
	 *
	 * @param $xml
	 * @param array $options
	 *
	 * @return array
	 */
    protected function xmlToArray($xml, $options = array())
    {
        $defaults = array(
            'namespaceSeparator' => ':',//you may want this to be something other than a colon
            'attributePrefix' => '',   //to distinguish between attributes and nodes with the same name
            'alwaysArray' => array(),   //array of xml tag names which should always become arrays
            'autoArray' => true,        //only create arrays for tags which appear more than once
            'textContent' => 'value',       //key used for the text content of elements
            'autoText' => true,         //skip textContent key if node has no attributes or child nodes
            'keySearch' => false,       //optional search and replace on tag and attribute names
            'keyReplace' => false       //replace values for above search values (as passed to str_replace())
        );
        $options = array_merge($defaults, $options);
        $namespaces = $xml->getDocNamespaces();
        $namespaces[''] = null; //add base (empty) namespace

        //get attributes from all namespaces
        $attributesArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
                //replace characters in attribute name
                if ($options['keySearch']) $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                $attributeKey = $options['attributePrefix']
                    . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                    . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }

        //get child nodes from all namespaces
        $tagsArray = array();
        foreach ($namespaces as $prefix => $namespace) {
            foreach ($xml->children($namespace) as $childXml) {
                //recurse into child nodes
                $childArray = $this->xmlToArray($childXml, $options);
                list($childTagName, $childProperties) = each($childArray);

                //replace characters in tag name
                if ($options['keySearch']) $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                //add namespace prefix, if any
                if ($prefix) $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;

                if (!isset($tagsArray[$childTagName])) {
                    //only entry with this key
                    //test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                            ? array($childProperties) : $childProperties;
                } elseif (
                    is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                    === range(0, count($tagsArray[$childTagName]) - 1)
                ) {
                    //key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                } else {
                    //key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
                }
            }
        }

        //get text content of node
        $textContentArray = array();
        $plainText = trim((string)$xml);
        if ($plainText !== '') $textContentArray[$options['textContent']] = $plainText;

        //stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
            ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        //return node as array
        return array(
            $xml->getName() => $propertiesArray
        );
    }

	/**
	 * Check for Errors in XML Response to Array Conversion
	 */
    protected function after_xml_resp_to_array_check_error()
    {
        $api_resp = isset($this->api_response_arr['ccb_api']['response']) ? $this->api_response_arr['ccb_api']['response'] : array();
        if (!empty($api_resp['errors']['error'])) {
            $this->api_error = array(
                'error_type' => 'api_error',
                'error' => isset($api_resp['errors']['error']['number']) ? $api_resp['errors']['error']['number'] : '',
                'error_code' => isset($api_resp['errors']['error']['type']) ? $api_resp['errors']['error']['type'] : '',
                'error_msg' => isset($api_resp['errors']['error']['value']) ? $api_resp['errors']['error']['value'] : ''
            );
            ccb_debug('add', array($this->api_name . ' -> api_response_error', json_encode($this->api_error), 0, 'ccb-api-calls'));
        }
    }

	/**
	 * Check if there is actually data in Response Array
	 */
    protected function after_xml_resp_to_array_check_nodata()
    {
        $api_resp = isset($this->api_response_arr['ccb_api']['response']) ? $this->api_response_arr['ccb_api']['response'] : array();
        if (isset($api_resp['individuals']['count']) && ($api_resp['individuals']['count'] == '0')) {
            $this->api_error = array(
                'error_type' => 'api_error',
                'error' => __('no data', 'ccb-gravity'),
                'error_code' => __('no data', 'ccb-gravity'),
                'error_msg' => __('No data found!!!', 'ccb-gravity')
            );
            ccb_debug('add', array($this->api_name . ' -> api_response_nodata_error', json_encode($this->api_error), 0, 'ccb-api-calls'));
        }
    }

}