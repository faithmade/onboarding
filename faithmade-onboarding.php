<?php
/**
 * Plugin Name: Faithmade Onboarding
 * Description: The onboarding experience for the Faithmade Network
 * Version: 0.1
 * Author: Faithmade
 * Author URI: http://faithmade.com
 * Text Domain: faithmade_ob
 */

if( ! defined( 'ABSPATH' ) )
	die;

define( 'FAITHMADE_OB_PLUGIN_URL', __FILE__ );
define( 'FAITHMADE_OB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );


/**
 * Faithmade Onboarding Trigger Setup for New Sites
 *
 * When a new site is created, forces the user who just created the site to see onboarding
 * the next time they reach wp-admin.
 */
function faithmade_onboarding_setup_trigger( $site_id, $user_id, $pass, $title, $meta ) {
	update_user_meta( $user_id, 'faithmade_onboarding_force_onboarding', 'true' );
	update_user_meta( $user_id, 'faithmade_onboarding_step', 'intro' );
	update_option('faithmade_onboarding_complete', 'false' );
}
add_action( 'wpmu_activate_blog', 'faithmade_onboarding_setup_trigger', 10, 5 );

/**
 * Faithmade Onboarding Trigger Setup for New Users
 * @param  int $user_id The User Id of the newly created user
 * @return void
 */
function faithmade_onboarding_setup_trigger_new_user( $user_id ) {
	update_user_meta( $user_id, 'faithmade_onboarding_force_onboarding', 'true' );
	update_user_meta( $user_id, 'faithmade_onboarding_step', 'intro' );
}
add_action( 'user_register', 'faithmade_onboarding_setup_trigger_new_user', 10, 1 );

/**
 * Faithmade Onboarding Trigger Setup for Existing Users
 *
 * When existing users login, this checks to see if there is value set for their current
 * onboarding step.  If they have not completed onboarding, they are redirected there.
 * 
 * @param  string $user_login WP_User->user_login
 * @param  object $user       WP_User
 * @return void
 */
function faithmade_onboarding_setup_trigger_existing_user( $user_login, $user ) {
	if( defined( 'DOING_AJAX') && DOING_AJAX ) {
		return;
	}
	$current_step = get_user_meta( $user->ID , 'faithmade_onboarding_step', true );
	//wp_die( $current_step );
	if( '' === $current_step ) {
		$current_step = 'intro';
		update_user_meta( $user->ID, 'faithmade_onboarding_step', $current_step );
	}
	if( 'final' !==  $current_step  && ! isset( $_REQUEST['onboarding'] ) ) {

		wp_redirect( admin_url('?onboarding'), $status = 302 ); exit;
	}
}
add_action( 'wp_login', 'faithmade_onboarding_setup_trigger_existing_user', 10, 2 );

/**
 * Redirect Logins to Onboarding
 */
function faithmade_maybe_redirect_onboarding() {
	$force = get_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_force_onboarding', true);
	if( 'true' === $force ) {
		delete_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_force_onboarding' );
		require_once( FAITHMADE_OB_PLUGIN_PATH . 'classes/class-faithmade-onboarding.php' );
		$_GLOBALS['faithmade_onboarding'] = new Faithmade_Onboarding();
		return;
	}

	faithmade_onboarding_setup_trigger_existing_user( wp_get_current_user()->user_login, wp_get_current_user() );	
}
if( ! is_main_site() && ( ! defined('DOING_AJAX') || ! DOING_AJAX ) && ! isset( $_REQUEST['onboarding'] ) ) {
	add_action( 'admin_init', 'faithmade_maybe_redirect_onboarding', 10 );
}

function faithmade_onboarding_nag() {
	if( 'false' === get_option( 'faithmade_onboarding_complete' ) ) {
		update_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_nag_user', 'true' );
	}
}
add_action('wp_logout', 'faithmade_onboarding_nag' );

/**
 * Starts onboarding
 */
function faithmade_start_onboarding() {
	$onboarding = false;
	$ajax_actions = array('faithmade_onboarding', 'plupload_action' );

	if( defined( 'DOING_AJAX') && DOING_AJAX && in_array($_REQUEST['action'], $ajax_actions ) ) {
		require_once( FAITHMADE_OB_PLUGIN_PATH . 'classes/class-faithmade-onboarding.php' );
		$_GLOBALS['faithmade_onboarding'] = new Faithmade_Onboarding();
		return;
	}
	if( is_admin() && isset( $_REQUEST['onboarding'] ) ) {

		if( 'reset' === $_REQUEST['onboarding'] ) {
			update_option('faithmade_onboarding_complete', 'false' );
			update_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_step', 'intro' );
		}
		if( '' === get_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_step', true ) ) {
			update_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_step', 'intro' );
		} elseif( 'final' === get_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_step', true ) ){
			return;
		} else {
			$onboarding = get_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_step', true );
		}
		$onboarding = apply_filters( 'faithmade_onboarding_step', $onboarding );
		if( $onboarding ) {
			require_once( FAITHMADE_OB_PLUGIN_PATH . 'classes/class-faithmade-onboarding.php' );
			$_GLOBALS['faithmade_onboarding'] = new Faithmade_Onboarding();
		}
	}
}
add_action( 'admin_init', 'faithmade_start_onboarding', 11 );

