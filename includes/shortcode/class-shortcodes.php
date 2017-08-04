<?php
/**
 * CCB Gravity Functionality
 *
 * @since NEXT
 * @package CCB Gravity Functionality
 */

/**
 * CCB Gravity Functionality Shortcodes.
 *
 * @since NEXT
 */
class CCB_Shortcodes {

	/**
	 * Instance of CCB_Shortcodes_Resources
	 *
	 * @var CCB_Shortcodes_Resources
	 */
	protected $resources;

	/**
	 * Constructor
	 *
	 * @since  NEXT
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->resources = new CCB_Shortcodes_Resources( $plugin );
	}

	/**
	 * Magic getter for our object. Allows getting but not setting.
	 *
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		return $this->{$field};
	}

}
