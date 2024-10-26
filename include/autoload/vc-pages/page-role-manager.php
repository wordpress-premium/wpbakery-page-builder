<?php
/**
 * Autoload lib related to our plugin role manager functionality.
 *
 * @note we require our autoload files everytime and everywhere after plugin load.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Add tab 'Role Manager' to settings.
 *
 * @param array $tabs
 * @return array
 */
function vc_settings_tabs_vc_roles( $tabs ) {
	// insert after vc-modules tab.
	if ( array_key_exists( 'vc-modules', $tabs ) ) {
		$new = array();
		foreach ( $tabs as $key => $value ) {
			$new[ $key ] = $value;
			if ( 'vc-modules' === $key ) {
				$new['vc-roles'] = esc_html__( 'Role Manager', 'js_composer' );
			}
		}
		$tabs = $new;
	} else {
		$tabs['vc-roles'] = esc_html__( 'Roles Manager', 'js_composer' );
	}

	return $tabs;
}

if ( ! is_network_admin() ) {
	add_filter( 'vc_settings_tabs', 'vc_settings_tabs_vc_roles' );
}

/**
 * Render tab 'Role Manager'.
 *
 * @return string
 */
function vc_settings_render_tab_vc_roles() {
	return 'pages/vc-settings/tab-vc-roles.php';
}

add_filter( 'vc_settings-render-tab-vc-roles', 'vc_settings_render_tab_vc_roles' );

/**
 * Save roles settings.
 */
function vc_roles_settings_save() {
	if ( check_admin_referer( 'vc_settings-roles-action', 'vc_nonce_field' ) && current_user_can( 'manage_options' ) ) {
		require_once vc_path_dir( 'SETTINGS_DIR', 'class-vc-roles.php' );
		$vc_roles = new Vc_Roles();
		$data = $vc_roles->save( vc_request_param( 'vc_roles', array() ) );
		echo wp_json_encode( $data );
		die();
	}
}

add_action( 'wp_ajax_vc_roles_settings_save', 'vc_roles_settings_save' );
if ( 'vc-roles' === vc_get_param( 'page' ) ) {
	/**
	 * Enqueue scripts for roles manager.
	 */
	function vc_settings_render_tab_vc_roles_scripts() {
		wp_register_script( 'vc_accordion_script', vc_asset_url( 'lib/vc/vc_accordion/vc-accordion.min.js' ), array( 'jquery-core' ), WPB_VC_VERSION, true );
	}

	add_action( 'admin_init', 'vc_settings_render_tab_vc_roles_scripts' );
}

/**
 * Filter state
 *
 * @param null|bool $state
 * @param WP_Role $role
 *
 * @return bool
 */
function wpb_unfiltered_html_state( $state, $role ) {
	if ( is_null( $state ) ) {
		if ( is_network_admin() && is_super_admin() ) {
			return true;
		}

		return isset( $role, $role->name ) && $role->has_cap( 'unfiltered_html' );
	}

	return $state;
}

/**
 * Filter access state.
 *
 * @param null|bool $state
 * @param WP_Role $role
 *
 * @return bool
 */
function wpb_editor_access( $state, $role ) {
	if ( is_null( $state ) ) {
		if ( is_network_admin() && is_super_admin() ) {
			return true;
		}

		return isset( $role, $role->name ) && in_array( $role->name, array(
			'administrator',
			'editor',
			'author',
		), true );
	}

	return $state;
}

add_filter( 'vc_role_access_with_unfiltered_html_get_state', 'wpb_unfiltered_html_state', 10, 2 );
add_filter( 'vc_role_access_with_grid_builder_get_state', 'wpb_editor_access', 10, 2 );
add_filter( 'vc_role_access_with_backend_editor_get_state', 'wpb_editor_access', 10, 2 );
add_filter( 'vc_role_access_with_frontend_editor_get_state', 'wpb_editor_access', 10, 2 );

/**
 * Check access for custom html elements.
 *
 * @param bool $state
 * @param string $shortcode
 *
 * @return bool
 */
function wpb_custom_html_elements_access( $state, $shortcode ) {
	if ( in_array( $shortcode, wpb_get_elements_with_custom_html() ) ) {
		$state = vc_user_access()->part( 'unfiltered_html' )->checkStateAny( true, null )->get();
	}

	return $state;
}

add_filter( 'vc_user_access_check-shortcode_edit', 'wpb_custom_html_elements_access', 10, 2 );
add_filter( 'vc_user_access_check-shortcode_all', 'wpb_custom_html_elements_access', 10, 2 );
