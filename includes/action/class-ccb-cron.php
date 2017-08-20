<?php

/**
 * CCB GRAVITY wp cron handler
 *
 * @since   1.0.0
 * @package CCB Gravity Functionality
 */
class CCB_GRAVITY_cron_handler extends CCB_GRAVITY_Abstract
{
	/**
	 * CCB_GRAVITY_cron_handler constructor.
	 *
	 * @param object $plugin
	 */
    public function __construct($plugin)
    {
        parent::__construct($plugin);
    }

	/**
	 * Schedule CCB Sync Cron
	 *
	 * @param $entry_args
	 */
    public function schedule_ccb_sync($entry_args)
    {
        $this->plugin->action_ajax->sync_entry_with_ccb($entry_args);
    }

    public function hooks()
    {
        add_action('ccb_gform_sync_hook', array($this, 'schedule_ccb_sync'), 10, 1);
    }

}