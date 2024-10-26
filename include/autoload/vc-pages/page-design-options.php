<?php
/**
 * Autoload lib related to our plugin design options functionality.
 *
 * @note we require our autoload files everytime and everywhere after plugin load.
 * @depreacted 7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Function check is system has custom build of css
 *  and check it version in comparison with current VC version
 *
 * @deprecated 7.7
 * @since 4.5
 */
function vc_check_for_custom_css_build() {
	_deprecated_function( __FUNCTION__, '7.7', "vc_modules_manager()->get_module('vc-design-options')->check_for_custom_css_build()" );
	if ( ! vc_modules_manager()->is_module_on( 'vc-design-options' ) ) {
		vc_modules_manager()->turn_on( 'vc-design-options' );
	}
	vc_modules_manager()->get_module( 'vc-design-options' )->check_for_custom_css_build();
}

/**
 * Display admin notice depending on current page
 *
 * @since 4.5
 * @deprecated 7.7
 */
function vc_custom_css_admin_notice() {
	_deprecated_function( __FUNCTION__, '7.7', "vc_modules_manager()->get_module('vc-design-options')->custom_css_admin_notice()" );
	if ( ! vc_modules_manager()->is_module_on( 'vc-design-options' ) ) {
		vc_modules_manager()->turn_on( 'vc-design-options' );
	}
	vc_modules_manager()->get_module( 'vc-design-options' )->custom_css_admin_notice();
}

/**
 * Attributes for colors.
 *
 * @param array $submit_button_attributes
 * @return mixed
 * @deprecated 7.7
 */
function vc_page_settings_tab_color_submit_attributes( $submit_button_attributes ) {
	_deprecated_function( __FUNCTION__, '7.7', "vc_modules_manager()->get_module('vc-design-options')->page_settings_tab_color_submit_attributes()" );
	if ( ! vc_modules_manager()->is_module_on( 'vc-design-options' ) ) {
		vc_modules_manager()->turn_on( 'vc-design-options' );
	}
	return vc_modules_manager()->get_module( 'vc-design-options' )->page_settings_tab_color_submit_attributes( $submit_button_attributes );
}

/**
 * Load module settings.
 *
 * @deprecated 7.7
 */
function vc_page_settings_desing_options_load() {
	_deprecated_function( __FUNCTION__, '7.7', "vc_modules_manager()->get_module('vc-design-options')->load_module_settings_assets()" );
	if ( ! vc_modules_manager()->is_module_on( 'vc-design-options' ) ) {
		vc_modules_manager()->turn_on( 'vc-design-options' );
	}
	vc_modules_manager()->get_module( 'vc-design-options' )->load_module_settings_assets();
}
