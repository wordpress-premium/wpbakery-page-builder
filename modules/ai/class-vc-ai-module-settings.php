<?php
/**
 * Settings tab for AI module.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Module settings.
 *
 * @since 7.8
 */
class Vc_Ai_Module_Settings {

	/**
	 * Init point.
	 *
	 * @since 7.8
	 */
	public function init() {
		add_filter( 'vc_settings_tabs', [ $this, 'set_setting_tab' ], 15 );

		add_filter( 'vc_settings-render-tab-vc-ai', [ $this, 'render_setting_tab_html' ] );

		add_action( 'vc-settings-render-tab-vc-ai', [ $this, 'load_module_settings_assets' ] );
	}

	/**
	 * Add module tab to settings.
	 *
	 * @since 7.8
	 * @param array $tabs
	 * @return array
	 */
	public function set_setting_tab( $tabs ) {
		if ( vc_settings()->showConfigurationTabs() ) {
			$tabs['vc-ai'] = esc_html__( 'WPBakery AI', 'js_composer' );
		}

		return $tabs;
	}

	/**
	 * Render tab content.
	 *
	 * @since 7.8
	 */
	public function render_setting_tab_html() {
		return '/pages/vc-settings/vc-ai.php';
	}

	/**
	 * Load assets related to module settings.
	 *
	 * @since 7.8
	 */
	public function load_module_settings_assets() {
		wp_enqueue_style( 'wpb_ai_module', vc_asset_url( '../modules/ai/assets/dist/module.min.css' ), false, WPB_VC_VERSION );
	}
}
