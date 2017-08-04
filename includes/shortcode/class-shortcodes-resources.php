<?php
/**
 * CCB Gravity Functionality
 *
 * @since NEXT
 * @package CCB Gravity Functionality
 */

/**
 * CCB Gravity Functionality Shortcodes Resources.
 *
 * @since NEXT
 */
class CCB_Shortcodes_Resources {

	/**
	 * Instance of CCB_Shortcodes_Resources_Run
	 *
	 * @var CCB_Shortcodes_Resources_Run
	 */
	protected $run;

	/**
	 * Instance of CCB_Shortcodes_Resources_Admin
	 *
	 * @var CCB_Shortcodes_Resources_Admin
	 */
	protected $admin;

	/**
	 * Constructor
	 *
	 * @since  0.1.0
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->run = new CCB_Shortcodes_Resources_Run($plugin);
		$this->run->hooks();

		if ( is_admin() ) {
			$this->admin = new CCB_Shortcodes_Resources_Admin( $this->run );
			$this->admin->hooks();
		}
	}

}
