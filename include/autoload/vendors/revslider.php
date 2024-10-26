<?php
/**
 * Backward compatibility with "Revolution Slider" WordPress plugin.
 *
 * @see https://www.sliderrevolution.com/
 *
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

add_action( 'plugins_loaded', 'vc_init_vendor_revslider' );
/**
 * Init vendor for "Revolution Slider" plugin.
 */
function vc_init_vendor_revslider() {
	include_once ABSPATH . 'wp-admin/includes/plugin.php'; // Require class-vc-wxr-parser-plugin.php to use is_plugin_active() below.
	if ( is_plugin_active( 'revslider/revslider.php' ) || class_exists( 'RevSlider' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'plugins/class-vc-vendor-revslider.php' );
		$vendor = new Vc_Vendor_Revslider();
		$vendor->load();
	}
}
