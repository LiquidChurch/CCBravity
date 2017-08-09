<?php

if (class_exists('WP_Logging') && defined('CCB_ENV') && CCB_ENV == 'development') {

    class CCB_Logging extends WP_Logging
    {

        public function __construct()
        {
            parent::__construct();
            add_filter('wp_log_types', array($this, 'pw_add_log_types'));
        }

        public function pw_add_log_types($types)
        {
            $types[] = 'ccb-api-calls';
            return $types;
        }
    }

    global $CCB_Logging;
    $CCB_Logging = new CCB_Logging();

    function ccb_debug($call_back, $param_arr)
    {
        global $CCB_Logging;
        call_user_func_array(array($CCB_Logging, $call_back), $param_arr);
    }

    function ccb_delete_log()
    {
        global $CCB_Logging;
        add_filter('wp_logging_should_we_prune', function(){
            return true;
        });
        call_user_func_array(array($CCB_Logging, 'prune_logs'), []);
    }

    if(!empty($_GET['delete-ccb-log']) && is_admin()) {
        ccb_delete_log();
    }

} else {

    function ccb_debug($call_back, $param_arr)
    {
        return false;
    }

}

