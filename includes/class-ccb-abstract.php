<?php

/**
 * Class CCB_GRAVITY_Abstract
 *
 * @package CCB Gravity Functionality
 */
abstract class CCB_GRAVITY_Abstract
{
    /**
     * Parent plugin class
     *
     * @var   class
     * @since 1.0.0
     */
    protected $plugin = null;

    /**
     * ajax call detect
     *
     * @var bool|null
     */
    protected $ajax_call = null;

    /**
     * Constructor
     *
     * @since  1.0.0
     * @param  object $plugin Main plugin object.
     * @return void
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->ajax_call = defined('DOING_AJAX') && DOING_AJAX;

        $this->hooks();
    }

    public abstract function hooks();
}