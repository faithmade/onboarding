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
			//add_action('wp_ajax_plupload_action', array( $this, 'ajax_logo_action' ) );  
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
		//PLUpload
		wp_enqueue_script('pluplaoad-all');
		// Typecase 
		global $typecase;
		$typecase->admin_styles();
    	// Default Scripts
    	//wp_register_script( 'dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/min/dropzone.min.js', array(), false, true );
    	//wp_register_style( 'dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/dropzone.css');
    	wp_enqueue_script( $this->slug . 'modal', plugins_url( '/js/onboarding.js', FAITHMADE_OB_PLUGIN_URL ), array('jquery','underscore'), false, true );
    	wp_localize_script( $this->slug . 'modal', 'FMOnboarding',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'current_step' => $this->current_step,
					'plupload_config' => array(
				        'runtimes' => 'html5,silverlight,flash,html4',
				        'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
				        'container' => 'plupload-upload-ui', // will be adjusted per uploader
				        'drop_element' => 'logo-drop-target', // will be adjusted per uploader
				        'file_data_name' => 'async-upload', // will be adjusted per uploader
				        'multiple_queues' => true,
				        'max_file_size' => wp_max_upload_size() . 'b',
				        'url' => admin_url('admin-ajax.php'),
				        'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
				        'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
				        'filters' => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
				        'multipart' => true,
				        'urlstream_upload' => true,
				        'multi_selection' => false, // will be added per uploader
				         // additional post data to send to our ajax hook
				        'multipart_params' => array(
				            '_ajax_nonce' => "", // will be added per uploader
				            'action' => 'plupload_action', // the ajax action name
				            'current_step' => 'logo',
				            'imgid' => 0 // will be added per uploader
				        )
				    ),
				));

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
	 * Ajax Route: Color
	 *
	 * The Ajax Route for Color Request.  Called by ajax_listener()
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
	 * Ajax Route: Fonts2
	 *
	 * The Ajax Route for Fonts2 Request.  Called by ajax_listener()
	 * 
	 * @return void
	 */
	protected function ajax_route_fonts2() {
		if( $_POST['get_markup'] ) {
			$this->send_font_selection_markup();
		}

		if( $_POST['location'] && $_POST['font'] ) {
			set_theme_mod( $_POST['location'], $_POST['font'] );
			$this->obj_response->code = 200;
			$this->obj_response->updated = true;
		}
	}

	protected function send_font_selection_markup() {
		global $typecase;
		$this->obj_response->markup = '';
		$theme_support = (array) get_theme_support('typecase');
		$font_locations = $theme_support[0];
		$font_collection = get_option('typecase_fonts');

		// placeholder array for parsed font names
		$font_names = array();

		// loop through typecase font collection
		foreach( $font_collection as $font ){

			$family = explode( "|",$font[0] );
			$family = $family[0];

			// add each font family to font options array
			$font_names[$family] = $family;
		}
		ob_start();
		echo '<div id="locations-wrap">';
		foreach( $font_locations as $location_index => $location_meta ) {
			echo sprintf( '<div class="font-location" id="location-%1$s"><h1>Select %2$s Font</h1>', sanitize_title($location_meta['label']), $location_meta['label'] );
			?>
				<div class="fonts_available">
					<select class="font-select" name="<?php echo sanitize_title($location_meta['label']);?>">
						<option>Select a Font</option>
						<option value="<?php echo $location_meta['default'];?>">Default</option>
					<?php foreach( $font_names as $indexed_name => $font_name ) : ?>
						<option value="<?php echo $font_name;?>" data-font-class="<?php echo sanitize_title( $font_name );?>">
							<?php echo $font_name;?>
						</option>
					<?php endforeach; ?>
					</select>
				</div>
				<br>
			<?php
			echo '</div> <!-- /.font-location -->';
			
		}
		echo '</div> <!-- /#locations-wrap -->';
		?>
		<div id="font-preview">
			<span>Typography Preview</span>
			<h1 class="font-preview-heading">Heading 1</heading>
			<h2 class="font-preview-heading">Heading 2</h2>
			<h3 class="font-preview-heading">Heading 3</h3>
			<p class="font-preview-content">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sagittis suscipit congue. Duis et porttitor nunc, consequat feugiat ligula. Pellentesque vehicula, tellus a blandit vestibulum, erat eros viverra dui, id facilisis magna lectus sit amet tellus. Vivamus tempor et diam eu posuere. Ut at sollicitudin arcu. Cras urna nunc, hendrerit et est a, dictum sodales tellus. Phasellus hendrerit, magna eget ornare tempor, nisl urna varius nisl, eget varius eros felis non massa. Integer volutpat maximus sagittis. Vivamus facilisis massa quis lectus consequat, eu scelerisque est varius. Pellentesque tincidunt luctus turpis. Nam congue urna sed ante bibendum, in venenatis turpis imperdiet. Sed vel ante dictum, venenatis nunc at, tempus nisl.</p>
		</div>
		<?php
		$this->obj_response->markup = ob_get_clean();

		ob_start();
		$typecase->display_frontend();
		$this->obj_response->head = ob_get_clean();
	}

	/**
	 * Get Logo Markup
	 *
	 * Build the markup for the logo uploader.
	 *
	 * @return STRING Valid HTML
	 */
	public static function get_logo_markup() {
		$id = "img1"; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $id == “img1” then $_POST[“img1”] will have all the image urls
 
		$svalue = ""; // this will be initial value of the above form field. Image urls.
		 		 
		$width = null; // If you want to automatically resize all uploaded images then provide width here (in pixels)
		 
		$height = null; // If you want to automatically resize all uploaded images then provide height here (in pixels)

		ob_start();
		?>
			<div class="onboarding-logo--title">Upload Your Logo</div>
			<div class="onboarding-logo--description">Drag and drop your logo here or click the box to select an image.</div>
			<p></p>
			<input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $svalue; ?>" />  
			<div class="plupload-upload-uic hide-if-no-js" id="<?php echo $id; ?>plupload-upload-ui">  
			    <input id="<?php echo $id; ?>plupload-browse-button" type="button" value="<?php esc_attr_e('Browse Files'); ?>" class="onboarding-logo--file" />
			    <span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($id . 'pluploadan'); ?>"></span>
			    <?php if ($width && $height): ?>
			            <span class="plupload-resize"></span><span class="plupload-width" id="plupload-width<?php echo $width; ?>"></span>
			            <span class="plupload-height" id="plupload-height<?php echo $height; ?>"></span>
			    <?php endif; ?>
			    <div class="filelist"></div>
			</div>  
			<div class="plupload-thumbs <?php if ($multiple): ?>plupload-thumbs-multiple<?php endif; ?>" id="<?php echo $id; ?>plupload-thumbs">  
			</div>  
			<div class="clear"></div>  
		<?php
		return ob_get_clean();
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

/**
 * Plupload Action
 *
 * Hooking into pluploader to do our stuff, for some reason this is erroring out when we put
 * it in the class;
 * 
 * @return void 
 */
function faithmade_onboarding_plupload_action() {
 
    // check ajax noonce
    $imgid = $_POST["imgid"];
    check_ajax_referer($imgid . 'pluploadan');
 
    // handle file upload
    $uploaded_file = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'plupload_action'));
 	// If the wp_handle_upload call returned a local path for the image
    if(isset($uploaded_file['file'])) {

        // The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
        $file_name_and_location = $uploaded_file['file'];

        // Generate a title for the image that'll be used in the media library
        $file_title_for_media_library = 'logo';

        // Set up options array to add this file as an attachment
        $attachment = array(
            'post_mime_type' => $uploaded_file['type'],
            'post_title' => 'Uploaded image ' . addslashes($file_title_for_media_library),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        // Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails. If you wanted to attch this image to a post, you could pass the post id as a third param and it'd magically happen.
        $attach_id = wp_insert_attachment( $attachment, $file_name_and_location );
        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_name_and_location );
        wp_update_attachment_metadata($attach_id,  $attach_data);

        // Set the feedback flag to false, since the upload was successful
        $upload_feedback = false;


    } else { // wp_handle_upload returned some kind of error. the return does contain error details, so you can use it here if you want.

        $upload_feedback = 'There was a problem with your upload.';
    }
    // Set it as the site logo
    update_option( 'site_logo', array(
    	'url' => wp_get_attachment_url($attach_id ),
    	'id' => $attach_id
    	) 
    );
    // send back the url of the new image on amazon s3
    echo wp_get_attachment_url($attach_id );
    exit;
}
add_action('wp_ajax_plupload_action', "faithmade_onboarding_plupload_action");