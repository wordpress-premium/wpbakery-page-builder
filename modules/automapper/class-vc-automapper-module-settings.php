<?php
/**
 * Module settings.
 *
 * @since 7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Module settings.
 *
 * @since 7.7
 */
class Vc_Automapper_Module_Settings {

	/**
	 * Settings tab title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Vc_Automapper constructor.
	 *
	 * @since 7.7
	 */
	public function __construct() {
		$this->title = esc_attr__( 'Shortcode Mapper', 'js_composer' );

		add_action( 'vc-settings-render-tab-vc-automapper', [ $this, 'load_module_settings_assets' ] );
	}

	/**
	 * Init point.
	 *
	 * @since 7.7
	 */
	public function init() {
		add_filter( 'vc_settings-render-tab-vc-automapper', [ $this, 'page_automapper_build' ] );

		add_filter( 'vc_settings_tabs', [ $this, 'set_setting_tab' ], 20 );
	}

	/**
	 * Setter/Getter for Automapper title
	 *
	 * @since 7.7
	 * @param string $title
	 */
    public function setTitle( $title ) { // @codingStandardsIgnoreLine
		$this->title = $title;
	}

	/**
	 * Getter for Automapper title tab settings.
	 *
	 * @since 7.7
	 * @return string|void
	 */
	public function title() {
		return $this->title;
	}

	/**
	 * Build page for automapper tab settings.
	 *
	 * @since 7.7
	 * @return string
	 */
	public function page_automapper_build() {
		return '/pages/vc-settings/vc-automapper.php';
	}

	/**
	 * Add module tab to settings.
	 *
	 * @since 7.7
	 * @param array $tabs
	 * @return array
	 */
	public function set_setting_tab( $tabs ) {
		if ( ! is_network_admin() ) {
			$tabs['vc-automapper'] = $this->title();
		}

		return $tabs;
	}

	/**
	 * Load assets related to module settings.
	 *
	 * @since 7.8
	 */
	public function load_module_settings_assets() {
		wp_enqueue_script( 'wpb_automapper_module', vc_asset_url( '../modules/automapper/assets/dist/module.min.js' ), [], WPB_VC_VERSION, true );
		wp_enqueue_style( 'wpb_automapper_module', vc_asset_url( '../modules/automapper/assets/dist/module.min.css' ), false, WPB_VC_VERSION );
	}
}
