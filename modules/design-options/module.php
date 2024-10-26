<?php
/**
 * Module Name: Design Options
 * Description: Add in our setting tab 'Design Options'
 * With fields that helps change colors, sizes etc.
 * Globally for pages created with our editor and for some elements individually.
 *
 * @since 7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once vc_manager()->path( 'MODULES_DIR', 'design-options/class-vc-design-options-module-settings.php' );

/**
 * Module entry point.
 *
 * @since 7.7
 */
class Vc_Design_Options_Module {

	/**
	 * Settings object.
	 *
	 * @since 7.7
	 * @var Vc_Design_Options_Module_Settings
	 */
	public $settings;

	/**
	 * Init module implementation.
	 *
	 * @since 7.7
	 */
	public function init() {
		$this->settings = new Vc_Design_Options_Module_Settings();
		$this->settings->init();

		add_action( 'vc_base_register_front_css', [ $this, 'register_from_custom_css_styles' ], 11 );
	}

	/**
	 * Register custom css styles.
	 *
	 * @since 7.7
	 */
	public function register_from_custom_css_styles() {

		$upload_dir = wp_upload_dir();
		$vc_upload_dir = vc_upload_dir();
		if ( '1' === vc_settings()->get( 'use_custom' ) && is_file( $upload_dir['basedir'] . '/' . $vc_upload_dir . '/js_composer_front_custom.css' ) ) {
			$front_css_file = $upload_dir['baseurl'] . '/' . $vc_upload_dir . '/js_composer_front_custom.css';
			$front_css_file = vc_str_remove_protocol( $front_css_file );
			wp_deregister_style( 'js_composer_front' );
			wp_register_style( 'js_composer_front', $front_css_file, array(), WPB_VC_VERSION );
		}
	}
}
