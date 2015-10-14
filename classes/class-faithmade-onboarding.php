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
    	wp_register_script( 'dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/min/dropzone.min.js', array(), false, true );
    	wp_enqueue_script( $this->slug . 'modal', plugins_url( '/js/onboarding.js', FAITHMADE_OB_PLUGIN_URL ), array('jquery','underscore','dropzone'), false, true );
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

	public function upload_image() {
		// Get the post type. Since this function will run for ALL post saves (no matter what post type), we need to know this.
	    // It's also important to note that the save_post action can runs multiple times on every post save, so you need to check and make sure the
	    // post type in the passed object isn't "revision"
	    $post_type = $post->post_type;

	    // Make sure our flag is in there, otherwise it's an autosave and we should bail.
	    if($post_id && isset($_POST['xxxx_manual_save_flag'])) { 

	        // Logic to handle specific post types
	        switch($post_type) {

	            // If this is a post. You can change this case to reflect your custom post slug
	            case 'post':

	                // HANDLE THE FILE UPLOAD

	                // If the upload field has a file in it
	                if(isset($_FILES['xxxx_image']) && ($_FILES['xxxx_image']['size'] > 0)) {

	                    // Get the type of the uploaded file. This is returned as "type/extension"
	                    $arr_file_type = wp_check_filetype(basename($_FILES['xxxx_image']['name']));
	                    $uploaded_file_type = $arr_file_type['type'];

	                    // Set an array containing a list of acceptable formats
	                    $allowed_file_types = array('image/jpg','image/jpeg','image/gif','image/png');

	                    // If the uploaded file is the right format
	                    if(in_array($uploaded_file_type, $allowed_file_types)) {

	                        // Options array for the wp_handle_upload function. 'test_upload' => false
	                        $upload_overrides = array( 'test_form' => false ); 

	                        // Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
	                        $uploaded_file = wp_handle_upload($_FILES['xxxx_image'], $upload_overrides);

	                        // If the wp_handle_upload call returned a local path for the image
	                        if(isset($uploaded_file['file'])) {

	                            // The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
	                            $file_name_and_location = $uploaded_file['file'];

	                            // Generate a title for the image that'll be used in the media library
	                            $file_title_for_media_library = 'your title here';

	                            // Set up options array to add this file as an attachment
	                            $attachment = array(
	                                'post_mime_type' => $uploaded_file_type,
	                                'post_title' => 'Uploaded image ' . addslashes($file_title_for_media_library),
	                                'post_content' => '',
	                                'post_status' => 'inherit'
	                            );

	                            // Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails. If you wanted to attch this image to a post, you could pass the post id as a third param and it'd magically happen.
	                            $attach_id = wp_insert_attachment( $attachment, $file_name_and_location );
	                            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	                            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_name_and_location );
	                            wp_update_attachment_metadata($attach_id,  $attach_data);

	                            // Before we update the post meta, trash any previously uploaded image for this post.
	                            // You might not want this behavior, depending on how you're using the uploaded images.
	                            $existing_uploaded_image = (int) get_post_meta($post_id,'_xxxx_attached_image', true);
	                            if(is_numeric($existing_uploaded_image)) {
	                                wp_delete_attachment($existing_uploaded_image);
	                            }

	                            // Now, update the post meta to associate the new image with the post
	                            update_post_meta($post_id,'_xxxx_attached_image',$attach_id);

	                            // Set the feedback flag to false, since the upload was successful
	                            $upload_feedback = false;


	                        } else { // wp_handle_upload returned some kind of error. the return does contain error details, so you can use it here if you want.

	                            $upload_feedback = 'There was a problem with your upload.';
	                            update_post_meta($post_id,'_xxxx_attached_image',$attach_id);

	                        }

	                    } else { // wrong file type

	                        $upload_feedback = 'Please upload only image files (jpg, gif or png).';
	                        update_post_meta($post_id,'_xxxx_attached_image',$attach_id);

	                    }

	                } else { // No file was passed

	                    $upload_feedback = false;

	                }

	                // Update the post meta with any feedback
	                update_post_meta($post_id,'_xxxx_attached_image_upload_feedback',$upload_feedback);

	            break;

	            default:

	        } // End switch

	    return;
		}
		return;
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