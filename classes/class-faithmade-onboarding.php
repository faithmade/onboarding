<?php
/**
 * Faithmade Onboarding
 *
 * Provides an Onboarding experience for users
 */

class Faithmade_Onboarding {
	/**
	 * $instance
	 *
	 * OBJECT instance of $this class
	 */
	protected $instance;

	/**
	 * $current_user
	 *
	 * OBJECT WP_User
	 */
	protected $current_user;

	/**
	 * $cap
	 *
	 * edit_theme_options allows access to customizer, menus, widgets, etc.
	 *
	 * @todo  Perhaps make this a user defined option.
	 */
	protected $cap = 'edit_theme_options';

	/**
	 * $current_user_can;
	 *
	 * If the current user can make use of the modal functions
	 *
	 * @todo  Show users who can't do stuff, only the stuff they can do.
	 */
	protected $current_user_can = false;

	/**
	 * $current_step
	 *
	 * STRING The current step, could be intro, colors, fonts, etc.  Passed as javascript var.
	 */
	protected $current_step;

	/**
	 * $slug
	 *
	 * STRING The slug to preface actions/filters with
	 */
	protected $slug = 'faithmade_ob_';

	/**
	 * $dependencies
	 *
	 * The functions and methods this functionality of our plugin relies on.
	 *
	 * @todo Make this do something
	 */
	public $dependencies = array();

	/**
	 * $modal_markup
	 *
	 * The markup for the modal window
	 */
	public $modal_markup = '';

	/**
	 * $obj_response
	 *
	 * A PHP Object to be encoded into JSON
	 */
	public $obj_response;

	/**
	 * $json_response
	 *
	 * A JSON formatted response
	 */
	public $json_response;
	
	/**
	 * Initialize this class 
	 * @return OBJECT Instance of Faithmade_Onboarding
	 */
	protected static function &init() {
		if( NULL === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * The Class Constructor
	 *
	 * Constructs the necessary environment to run Faithmade Onboarding.
	 *
	 * @return  OBJECT $this instance
	 */
	public function __construct() {
		$this->set_user();

		$this->check_permissions();

		$this->set_step();
		
		$this->set_modal_base_template();

		// Init Styles and Scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'init_scripts' ) );

		// Setup the Onboarding Window
		add_action( 'admin_footer', array( $this, 'print_modal' ) );

		// Setup the Ajax Listener
		if( defined( 'DOING_AJAX' ) && DOING_AJAX ){
			add_action( 'wp_ajax_faithmade_onboarding', array( $this, 'ajax_listener' ) );
		}

		// Check Dependencies
		$this->ensure_dependencies();

		do_action( $this->slug . 'init' );;
		return $this;
	}

	/**
	 * Initialize User
	 *
	 * Sets up the current user property
	 *
	 * @return  OBJECT $this instance
	 */
	public function set_user() {
		$current_user = wp_get_current_user();
		
		if( ! $current_user ) {
			$this->die_quietly();
		}
		
		$this->current_user = $current_user;
		
		return $this;
	}

	/**
	 * Check Permissions
	 * 
	 * @return OBJECT  $this instance
	 */
	public function check_permissions() {
		if( ! isset( $this->cap ) ) {
			$this->die_quietly();
		}

		if( ! current_user_can( $this->cap ) ) {
			$this->die_quietly();
		} else {
			$this->current_user_can = true;
		}
		return $this;
	}

	/**
	 * Set Step
	 *
	 * Reads the current onboarding step from the user meta table, sets it as the class prop.
	 *
	 * @return  OBJECT $this instance
	 */
	public function set_step() {
		if( empty( $this->current_user ) ) {
			$this->die_quietly();
		}

		$step = get_user_meta( $this->current_user->ID, 'faithmade_onboarding_step', true );

		if( false === $step ) {
			$this->die_quietly();
		} else {
			$this->current_step = $step;
		}

		return $this;
	}

	/**
	 * Initialize Scripts
	 *
	 * Enqueues required javascript files and stylesheets
	 * 
	 * @return OJBECT $this instance;
	 */
	public function init_scripts() {
		wp_enqueue_media();
    	// Default Scripts
    	//wp_register_script( 'dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/min/dropzone.min.js', array(), false, true );
    	//wp_register_style( 'dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/dropzone.css');
    	wp_enqueue_script( $this->slug . 'modal', plugins_url( '/js/onboarding.js', FAITHMADE_OB_PLUGIN_URL ), array('jquery','underscore'), false, true );
    	wp_localize_script( $this->slug . 'modal', 'FMOnboarding',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'current_step' => $this->current_step,
				) );

    	wp_enqueue_style( $this->slug . 'modal', plugins_url( '/css/onboarding.css', FAITHMADE_OB_PLUGIN_URL ), array('dropzone') );
		return $this;
	}

	/**
	 * Sets up the modal base
	 *
	 * Sets up the onboarding window that will appear to the user when they begin
	 * the onboarding experience.
	 *
	 * $return OBJECT $this instance
	 */
	protected function set_modal_base_template() {
		$file = apply_filters( $this->slug . 'modal_file', FAITHMADE_OB_PLUGIN_PATH . 'modal.php' );
		if( ! is_file( $file ) || ! is_readable( $file ) ) {
			$this->die_quietly();
		}
		ob_start();
		include( $file );
		$this->modal_markup = apply_filters( $this->slug . 'modal_markup', ob_get_clean() );
		
		return $this;
	}

	/**
	 * Print Window
	 *
	 * @return void
	 */
	public function print_modal() {
		if( ! empty( $this->modal_markup ) ) {
			echo $this->modal_markup;
		}
	}

	/**
	 * Check Dependencies
	 *
	 * Checks for the existence of the classes and functions this plugin relies on
	 *
	 * @return  OBJECT $this instance;
	 */
	protected function ensure_dependencies() {
		if( empty( $this->dependencies ) ) {
			return $this;
		}

		/**
		 * Filter: faithmade_ob_dependencies
		 */
		$this->dependencies = apply_filters( $this->slug . 'dependencies', $this->dependencies );

		foreach( $this->dependencies as $index => $dependency ) {
			if( ! class_exists( $dependency ) && ! function_exists( $dependency ) ) {
				$this->die_quietly();
				return $this;
			}
		}
		return $this;
	}

	/**
	 * Ajax Listener
	 *
	 * Method is called via WordPress Ajax Api.  Sets current step to the step passed in POST
	 * data and calls the callback method for each step if a method exists.
	 *
	 * @return  void 
	 */
	public function ajax_listener() {
		if( ! empty ( $this->obj_response ) ) {
			unset( $this->obj_response );
			$this->obj_response = new StdClass();
		}
		$this->obj_response->code = 0;
		$this->obj_response->messages = new StdClass();
		$this->obj_response->data = new StdClass();
		if( ! isset( $_REQUEST['current_step'] ) ) {
			$this->obj_response->messages->error = "Current step is undefined.";
			$this->send_json_response();
		}
		$step = sanitize_option( 'faithmade_onboarding_step', $_REQUEST['current_step'] );

		if( update_user_meta( $this->current_user->ID, 'faithmade_onboarding_step', $step ) ) {
			$this->obj_response->code = 200;
			$this->obj_response->messages->updated = true;
		}

		// We've updated the current step, let's do anything additional
		$method_name = 'ajax_route_' . $step;
		if( method_exists( $this, $method_name ) ) {
			call_user_func( array( $this, $method_name ) );
		}

		// Send the response
		$this->send_json_response();

		// If we get here, absolutely nothing happened.
		die('Error 500: Nothing happened.');
	}

	public function send_json_response() {
		$this->json_response = json_encode( $this->obj_response );
		echo $this->json_response;
		die();
	}

	/**
	 * Ajax Route: Logo
	 *
	 * The Ajax Route for Logo Request.  Called by ajax_listener()
	 * 
	 * @return void
	 */
	public function ajax_route_colors() {
		if( isset( $_POST['palette'] ) ) {
			$theme_support = (array) get_theme_support( 'colorcase' );
			$theme_support = $theme_support[0];
			foreach($theme_support['palettes'] as $palette => $locations ) {
				if( sanitize_title($palette) === sanitize_title( $_POST['palette' ] ) ) {
					foreach( $locations as $location => $elements ) {
						foreach( $elements as $color_location_label => $theme_color ) {
							$slug = sanitize_title( $location . '_' . $color_location_label);
							set_theme_mod( $slug, $theme_color );							
							$this->obj_response->messages->updated = true;
						}
					}
				} else {
					$this->obj_response->messages->updated = false;
				}
			}
		} else {
			$this->obj_response->messages->updated = false;
		}
	}

	/**
	 * Ajax Route: Logo
	 *
	 * The Ajax Route for Logo Request.  Called by ajax_listener()
	 * 
	 * @return void
	 */
	protected function ajax_route_logo() {
		
	}

	/**
	 * Get Color Markup
	 * 
	 * Builds the Markup for Color Palette Selection from the list of color palettes defined
	 * by the current theme.
	 *
	 * @return  STRING Valid HTML
	 */
	public static function get_color_markup() {
		// get color palettes
		$color_palettes = (array) Colorcase::colorcase_get_palettes();

		// bail if no color palettes
		if( $color_palettes == false || empty( $color_palettes ) ){
			return;
		}
		ob_start();

		foreach( $color_palettes as $color_palette_label => $color_palette_sections ){
			// create unique slug
			$color_palette_slug = sanitize_title( $color_palette_label );

			// Pluck the colors out.
			$primaries = wp_list_pluck( $color_palette_sections, 'Background Color');
			$secondaries = wp_list_pluck( $color_palette_sections, 'Text Color');
			$tertiaries = wp_list_pluck( $color_palette_sections, 'Link Color');
			$alts = wp_list_pluck( $color_palette_sections, 'Link Hover Color');

			// Define or empty $colors array
			$colors = array();
			// Merge our colors into a master array of colors ordered by precedent
			$colors = array_merge( 
				array_values($primaries), 
				array_values($secondaries), 
				array_values($tertiaries), 
				array_values($alts) 
			);
			// Remove Duplicates
			$colors = array_unique($colors);
			
			// Define each color by popping off the first color from our list of colors.
			// If there aren't any more colors, reuse the previous color.
			$primary = array_shift( $colors );
			$secondary = ( $color = array_shift( $colors ) ) ? $color : $primary;
			$tertiary = ( $color = array_shift( $colors ) ) ? $color : $secondary;
			$alt1 = ( $color = array_shift( $colors ) ) ? $color : $tertiary;
			$alt2 = ( $color = array_shift( $colors ) ) ? $color : $alt1;
		?>
			<div class="onboarding-colors--color">
				<div class="onboarding-colors--color-bar onboarding-colors--color-bar_secondary" style="background-color: <?php echo $secondary; ?>;"></div>
				<div class="onboarding-colors--color-bar onboarding-colors--color-bar_alt" style="background-color: <?php echo $alt1; ?>;"></div>
				<div class="onboarding-colors--color-bar onboarding-colors--color-bar_primary" style="background-color: <?php echo $primary; ?>;"></div>
				<div class="onboarding-colors--color-bar onboarding-colors--color-bar_alt" style="background-color: <?php echo $alt2; ?>;"></div>
				<div class="onboarding-colors--color-bar onboarding-colors--color-bar_tertiary" style="background-color: <?php echo $tertiary; ?>;">
					<button class="palette-selector" data-palette-value="<?php echo $color_palette_slug;?>"><?php echo $color_palette_label;?></button>
				</div>
			</div>
		<?php
		}
        return ob_get_clean();
	}

	/**
	 * Die Quietly
	 *
	 * Cancels all of our actions and filters so as not to bother the user.
	 * 
	 * @return OBJECT $this instance
	 */
	protected function die_quietly() {
		if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			die('0');
		}
		remove_action( 'admin_footer', array( $this, 'print_modal' ) );
		remove_action( 'wp_ajax_faithmade_onboarding', array( $this, 'ajax_listener' ) );
		wp_dequeue_script( $this->slug . 'modal' );
		wp_dequeue_style( $this->slug . 'modal' );
		
		return $this;
	}
}