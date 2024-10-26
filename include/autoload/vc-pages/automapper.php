<?php
/**
 * Autoload lib related to our plugin automapper functionality.
 *
 * @note we require our autoload files everytime and everywhere after plugin load.
 * @depreacted 7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Helpers.
if ( ! function_exists( 'vc_atm_build_categories_array' ) ) {
	/**
	 * Build categories array from string.
	 *
	 * @depreacted 7.7
	 * @param string $category
	 *
	 * @return array
	 */
	function vc_atm_build_categories_array( $category ) {
		_deprecated_function( __FUNCTION__, '7.7', "vc_modules_manager()->get_module( 'vc-automapper' )->build_categories_array()" );
		if ( ! vc_modules_manager()->is_module_on( 'vc-automapper' ) ) {
			vc_modules_manager()->turn_on( 'vc-automapper' );
		}
		return vc_modules_manager()->get_module( 'vc-automapper' )->build_categories_array( $category );
	}
}
if ( ! function_exists( 'vc_atm_build_params_array' ) ) {
	/**
	 * Build params array from string.
	 *
	 * @depreacted 7.7
	 * @param array $init
	 *
	 * @return array
	 */
	function vc_atm_build_params_array( $init ) {
		_deprecated_function( __FUNCTION__, '7.7', "vc_modules_manager()->get_module( 'vc-automapper' )->build_params_array()" );
		if ( ! vc_modules_manager()->is_module_on( 'vc-automapper' ) ) {
			vc_modules_manager()->turn_on( 'vc-automapper' );
		}
		return vc_modules_manager()->get_module( 'vc-automapper' )->build_params_array( $init );
	}
}
