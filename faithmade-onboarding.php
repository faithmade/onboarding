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
 * Redirect Logins to Onboarding
 */
function faithmade_maybe_redirect_onboarding($redirect_to, $requested_redirect_to, $user ) {
	if( 'false' === get_option('faithmade_onboarding_complete') ) {
		return admin_url('?onboarding');
	}
	return $redirect_to;
}
add_filter( 'login_redirect', 'faithmade_maybe_redirect_onboarding', 100, 3 );

/**
 * Starts onboarding
 */
function faithmade_start_onboarding() {
	$onboarding = false;
	$ajax_actions = array('faithmade_onboarding', 'plupload_action' );
	if( ( is_admin() && isset( $_REQUEST['onboarding'] ) ) 
		|| ( defined( 'DOING_AJAX' ) && DOING_AJAX && in_array($_REQUEST['action'], $ajax_actions ) ) ) 
	{
		if( 'reset' == $_REQUEST['onboarding'] ) {
			update_option('faithmade_onboarding_complete', 'false' );
			update_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_step', 'intro' );
		}
		if( empty( get_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_step', true ) ) ) {
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
add_action( 'admin_init', 'faithmade_start_onboarding' );

