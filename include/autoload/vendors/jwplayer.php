<?php
/**
 * Backward compatibility with "JW Player" WordPress plugin.
 *
 * @see https://www.ilghera.com/product/jw-player-7-for-wordpress-premium
 *
 * Used to initialize plugin jwplayer vendor for frontend editor.
 *
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

add_action( 'plugins_loaded', 'vc_init_vendor_jwplayer' );
/**
 * Initialize JW Player vendor.
 */
function vc_init_vendor_jwplayer() {
	if ( is_plugin_active( 'jw-player-plugin-for-wordpress/jwplayermodule.php' ) || defined( 'JWP6' ) || class_exists( 'JWP6_Plugin' ) ) {
		require_once vc_path_dir( 'VENDORS_DIR', 'plugins/class-vc-vendor-jwplayer.php' );
		$vendor = new Vc_Vendor_Jwplayer();
		$vendor->load();
	}
}
