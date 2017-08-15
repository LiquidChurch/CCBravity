<?php

/**
 * CCB GRAVITY wp cron handler
 *
 * @since   1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_cron_handler extends CCB_GRAVITY_Abstract
{
    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

    public function schedule_ccb_sync($entry_args)
    {
        $this->plugin->action_ajax->sync_entry_with_ccb($entry_args);
    }

    public function hooks()
    {
        add_action('ccb_gform_sync_hook', array($this, 'schedule_ccb_sync'), 10, 1);
    }

}