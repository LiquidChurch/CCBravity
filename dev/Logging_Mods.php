<?php

if (class_exists('WP_Logging')) {

    class CCB_Logging extends WP_Logging
    {

        public function __construct()
        {
            parent::__construct();
            add_filter('wp_log_types', array($this, 'pw_add_log_types'));
        }

        public function pw_add_log_types($types)
        {
            $types[] = 'API';
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

} else {

    function ccb_debug($call_back, $param_arr)
    {
        return false;
    }

}

