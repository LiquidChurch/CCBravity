<?php
/**
 * CCB GRAVITY Template Loader
 *
 * @since 1.0.0
 * @package CCB Gravity Functionality
 */

/**
 * CCB Gravity Template Loader.
 *
 * @since 1.0.0
 */
class CCB_GRAVITY_Template_Loader {

	/**
	 * Array of arguments for template
	 *
	 * @var array $args
	 * @since 1.0.0
	 */
	public $args = array();

	/**
	 * Template names array
	 *
	 * @var array $templates
	 * @since 1.0.0
	 */
	public $templates = array();

	/**
	 * Template name
	 *
	 * @var string $template
	 * @since 1.0.0
	 */
	public $template = '';

	/**
	 * Template file extension
	 *
	 * @var string $extension
	 * @since 1.0.0
	 */
	protected $extension = '.php';

	/**
	 * HTML view template loader constructor.
	 *
	 * @since  1.0.0
	 *
	 * @param string  $template The template file name, relative to the includes/templates/ folder - with or without .php extension
	 * @param string  $name     The name of the specialised template. If array, will take the place of the $args.
	 * @param array   $args     An array of arguments to extract as variables into the template
	 * @throws Exception
	 *
	 */
	public function __construct( $template, $name = null, array $args = array() ) {
		if ( empty( $template ) ) {
			throw new Exception( 'Template variable required for '. __CLASS__ .'.' );
		}

		$file = $this->template = "{$template}{$this->extension}";

		if ( is_array( $name ) ) {
			$this->args = $name;
		} else {
			$this->args = $args;

			$name = (string) $name;
			if ( '' !== $name ) {
				$this->templates[] = $this->template = "{$template}-{$name}{$this->extension}";
			}
		}

		$this->templates[] = $file;
	}

	/**
	 * Loads the view and outputs it
	 *
	 * @since  1.0.0
	 *
	 * @param  boolean $echo Whether to output or return the template
	 *
	 * @return string        Rendered template
	 */
	public function load( $echo = false ) {
		$template = $this->locate_template();

		// No template found.
		if ( ! $template ) {
			return;
		}

		// Filter args before outputting template.
		$this->args = apply_filters( "template_args_for_{$this->template}", $this->args, $this );

		try {
			ob_start();
			// Do html
			include $template;
			// grab the data from the output buffer and add it to our $content variable
			$content = ob_get_clean();
		} catch ( Exception $e ) {
			wp_die( $e->getMessage() );
		}

		$content = apply_filters( "template_output_for_{$this->template}", $content, $this );

		if ( ! $echo ) {
			return $content;
		}

		echo $content;
	}

	/**
	 * Retrieve the name of the highest priority template file that exists.
	 *
	 * Searches in the STYLESHEETPATH before TEMPLATEPATH and then this plugin's /templates
	 * so that themes which inherit from a parent theme can just overload one file.
	 *
	 * @since  1.0.0
	 *
	 * @return string The located template filename.
	 */
	protected function locate_template() {
		$located = '';

		foreach ( $this->templates as $template ) {
			if ( $located = $this->_locate( $template ) ) {
				return $located;
			}
		}

		return $located;
	}

	/**
	 * Searches for template in 1) child theme, 2) parent theme, 3) this plugin.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $template Template file to search for.
	 *
	 * @return void
	 */
	protected function _locate( $template ) {
		$locations = apply_filters( "template_locations_for_{$this->template}", array(
			STYLESHEETPATH . '/ccb-gravity/assets/css/',
			TEMPLATEPATH . '/ccb-gravity/',
			CCB_GRAVITY_Functionality::$path . 'templates/',
			CCB_GRAVITY_Functionality::$path . 'templates/assets/css/'
		), $this );

		$located = '';
		foreach ( $locations as $location ) {
			if ( file_exists( $location . $template ) ) {
				$located = $location . $template;
				break;
			}
		}

		return $located;
	}

	/**
	 * Get one of the $args values.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $arg     The $args key.
	 * @param  mixed   $default Mixed value.
	 *
	 * @return mixed            Value or default.
	 */
	public function get( $arg, $default = null ) {
		if ( isset( $this->args[ $arg ] ) ) {
			return $this->args[ $arg ];
		}

		return $default;
	}

	/**
	 * Output one of the $args values.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $arg     The $args key.
	 * @param  mixed   $esc_cb  An escaping function callback.
	 * @param  mixed   $default Mixed value.
	 *
	 * @return mixed            Value or default.
	 */
	public function output( $arg, $esc_cb = '', $default = null ) {
		$val = $this->get( $arg, $default );

		echo $esc_cb ? $esc_cb( $val ) : $val;
	}

	/**
	 * Conditionally output one of the $args values,
	 * if the value (or another one specified) exists.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $arg          The $args key.
	 * @param  mixed   $esc_cb       An escaping function callback.
	 * @param  mixed   $arg_to_check Alternate arg to check instead of $arg.
	 *
	 * @return mixed                 Value if condition is met.
	 */
	public function maybe_output( $arg, $esc_cb = '', $arg_to_check = null ) {
		$arg_to_check = null === $arg_to_check ? $arg : $arg_to_check;

		if ( $this->get( $arg_to_check ) ) {
			$this->output( $arg, $esc_cb );
		}
	}

	/**
	 * Magic method to fetch the rendered view when calling the call as a string.
	 *
	 * @since  1.0.0
	 *
	 * @return string  Rendered template's HTML output.
	 */
	public function __toString() {
		return $this->load();
	}

	/**
	 * Get a rendered HTML view with the given arguments and return the view's contents.
	 *
	 * @since  1.0.0
	 *
	 * @param string  $template The template file name, relative to the includes/templates/ folder
	 *                          - without .php extension
	 * @param string  $name     The name of the specialised template. If array, will take the place of the $args.
	 * @param array   $args     An array of arguments to extract as variables into the template
	 *
	 * @return string           Rendered template output
	 */
	public static function get_template( $template, $name = null, array $args = array() ) {
		$view = new self( $template, $name, $args );
		return $view->load();
	}

	/**
	 * Render an HTML view with the given arguments and output the view's contents.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $template The template file name, relative to the includes/templates/ folder
	 *                          - without .php extension
	 * @param  string $name     The name of the specialised template. If array, will take the place of the $args.
	 * @param  array  $args     An array of arguments to extract as variables into the template
	 *
	 * @return void
	 */
	public static function output_template( $template, $name = null, array $args = array() ) {
		$view = new self( $template, $name, $args );
		$view->load( 1 );
	}

}
