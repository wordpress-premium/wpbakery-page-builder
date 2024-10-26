<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Base functionality for WpBakery editors.
 *
 * @since 7.4
 */
abstract class Vc_Editor {
	/**
	 * @since 7.7
	 * @var array
	 */
	public $post_custom_meta;

	/**
	 * Set post meta related to VC.
	 *
	 * @since 7.4
	 * @param WP_Post | null $post
	 */
	public function set_post_meta( $post ) {
		/**
		 * Filter post custom meta related to our plugin.
		 *
		 * @since 7.7
		 * @param array $post_custom_meta
		 * @param WP_Post $post
		 */
		$this->post_custom_meta = apply_filters( 'wpb_set_post_custom_meta', [], $post );
	}
}
