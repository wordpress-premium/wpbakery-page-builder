<?php
/**
 * Backward compatibility with "LayerSlider" WordPress plugin.
 *
 * @see https://layerslider.com
 *
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

add_action( 'plugins_loaded', 'vc_init_vendor_layerslider' );
/**
 * Init vendor LayerSlider.
 */
function vc_init_vendor_layerslider() {
	include_once ABSPATH . 'wp-admin/includes/plugin.php'; // Require class-vc-wxr-parser-plugin.php to use is_plugin_active() below.
	if ( is_plugin_active( 'LayerSlider/layerslider.php' ) || class_exists( 'LS_Sliders' ) || defined( 'LS_ROOT_PATH' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'plugins/class-vc-vendor-layerslider.php' );
		$vendor = new Vc_Vendor_Layerslider();
		$vendor->load();
	}
}
