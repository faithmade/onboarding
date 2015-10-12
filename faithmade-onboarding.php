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
 * Starts onboarding
 */
function faithmade_onboarding() {
	$onboarding = false;
	if( is_admin() ) {
		if( empty( get_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_step', true ) ) ) {
			$onboarding = 'intro';
			update_user_meta( wp_get_current_user()->ID, 'faithmade_onboarding_step', $onboarding );
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
add_action( 'admin_init', 'faithmade_onboarding' );