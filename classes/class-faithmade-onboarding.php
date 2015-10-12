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
    	wp_enqueue_script( $this->slug . 'modal', plugins_url( '/js/onboarding.js', FAITHMADE_OB_PLUGIN_URL ), array('jquery','backbone','underscore','wp-util'), false, true );
    	wp_localize_script( $this->slug . 'modal', 'FMOnboarding',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'current_step' => $this->current_step,
				) );

    	wp_enqueue_style( $this->slug . 'modal', plugins_url( '/css/onboarding.css', FAITHMADE_OB_PLUGIN_URL ) );
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
	 * Initialize Ajax Listener
	 */
	public function ajax_listener() {
		if( ! isset( $_POST['current_step'] ) ) {
			wp_send_json( array( 'success' => 'false' ) );
		}
		$step = sanitize_option( 'faithmade_onboarding_step', $_POST['current_step'] );

		if( update_user_meta( $this->current_user->ID, 'faithmade_onboarding_step', $step ) ) {
			wp_send_json( array( 'success' => 'true', 'current_step' => $step ) );
		}

		die('0');
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
	 * Die Quietly
	 *
	 * Cancels all of our actions and filters so as not to bother the user.
	 * 
	 * @return OBJECT $this instance
	 */
	protected function die_quietly() {
		remove_action( 'admin_footer', array( $this, 'print_modal' ) );
		remove_action( 'wp_ajax_faithmade_onboarding', array( $this, 'ajax_listener' ) );
		wp_dequeue_script( $this->slug . 'modal' );
		wp_dequeue_style( $this->slug . 'modal' );
		
		return $this;
	}
}