<?php
/**
 * Module Name: Custom JS
 *
 * Description: Allow implement custom JS code to the whole site and individual pages.
 *
 * @since 7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once vc_manager()->path( 'MODULES_DIR', 'custom-js/class-vc-custom-js-module-settings.php' );

/**
 * Module entry point.
 *
 * @since 7.7
 */
class Vc_Custom_Js_Module {

	/**
	 * Settings object.
	 *
	 * @var Vc_Custom_Js_Module_Settings
	 */
	public $settings;

	/**
	 * Init module implementation.
	 *
	 * @since 7.7
	 */
	public function init() {
		$this->settings = new Vc_Custom_Js_Module_Settings();
		$this->settings->init();

		add_action( 'vc_build_page', [ $this, 'output_custom_js_to_page' ] );

		add_filter( 'vc_post_meta_list', [ $this, 'add_custom_meta_to_update' ] );

		add_filter( 'wpb_set_post_custom_meta', [ $this, 'set_post_custom_meta' ], 10, 2 );

		add_filter( 'vc_enqueue_backend_editor_js', array(
			$this,
			'enqueue_editor_js',
		));

		add_filter( 'vc_enqueue_frontend_editor_js', array(
			$this,
			'enqueue_editor_js',
		));
	}

	/**
	 * Add custom js to page.
	 *
	 * @since 7.7
	 */
	public function output_custom_js_to_page() {
		add_filter( 'print_head_scripts', array(
			$this,
			'output_post_header_custom_js',
		), 90, 1 );
		add_action( 'wp_print_footer_scripts', array(
			$this,
			'output_post_footer_custom_js',
		), 90 );
		add_filter( 'print_head_scripts', array(
			$this,
			'output_global_header_custom_html',
		), 100, 1 );
		add_action( 'wp_print_footer_scripts', array(
			$this,
			'output_global_footer_custom_html',
		), 100 );
	}

	/**
	 * Add post custom html to the header tag of the page.
	 *
	 * @param bool $is_print
	 *
	 * @since 7.0
	 */
	public function output_post_header_custom_js( $is_print ) {
		$id = wpb_get_post_id_for_custom_output();

		if ( ! $id ) {
			return $is_print;
		}

		$post_header_html = get_post_meta( $id, '_wpb_post_custom_js_header', true );

		if ( empty( $post_header_html ) ) {
			return $is_print;
		}

		$this->output_custom_js( $post_header_html, 'header' );
		return $is_print;
	}

	/**
	 * Add post custom html to the footer tag of the page.
	 *
	 * @since 7.0
	 */
	public function output_post_footer_custom_js() {
		$id = wpb_get_post_id_for_custom_output();

		if ( ! $id ) {
			return;
		}

		$post_footer_html = get_post_meta( $id, '_wpb_post_custom_js_footer', true );

		if ( empty( $post_footer_html ) ) {
			return;
		}

		$this->output_custom_js( $post_footer_html, 'footer' );
	}

	/**
	 * Output custom on a page.
	 *
	 * @since 7.0
	 * @param string $js
	 * @param string $area
	 */
	public function output_custom_js( $js, $area ) {
		echo '<script data-type="vc_custom-js-"' . esc_attr( $area ) . '>';
		// we need to wait for iframe load on frontend editor side.
		if ( vc_is_page_editable() ) {
			echo 'setTimeout(() => {';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wp_unslash( $js );
			echo '}, 2000);';
		} else {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wp_unslash( $js );
		}
		echo '</script>';
	}

	/**
	 * Add custom html to the header tag of the page.
	 *
	 * @param bool $is_print
	 *
	 * @since 7.7
	 */
	public function output_global_header_custom_html( $is_print ) {
		$global_header_html = get_option( Vc_Settings::$field_prefix . 'custom_js_header' );

		echo '<script>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wp_unslash( $global_header_html );
		echo '</script>';

		return $is_print;
	}

	/**
	 * Add custom html to the footer tag of the page.
	 *
	 * @since 7.7
	 */
	public function output_global_footer_custom_html() {
		$global_footer_html = get_option( Vc_Settings::$field_prefix . 'custom_js_footer' );

		echo '<script>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wp_unslash( $global_footer_html );
		echo '</script>';
	}

	/**
	 * Add custom js to the plugin post custom meta list.
	 *
	 * @param array $meta_list
	 * @return array
	 */
	public function add_custom_meta_to_update( $meta_list ) {
		$meta_list[] = 'custom_js_header';
		$meta_list[] = 'custom_js_footer';

		return $meta_list;
	}

	/**
	 * Set post custom meta.
	 *
	 * @param array $post_custom_meta
	 * @param WP_Post $post
	 * @return array
	 */
	public function set_post_custom_meta( $post_custom_meta, $post ) {
		$post_custom_meta['post_custom_js_header'] = get_post_meta( $post->ID, '_wpb_post_custom_js_header', true );
		$post_custom_meta['post_custom_js_footer'] = get_post_meta( $post->ID, '_wpb_post_custom_js_footer', true );

		return $post_custom_meta;
	}

	/**
	 * Load module JS in frontend and backend editor.
	 *
	 * @since 7.8
	 * @param array $dependencies
	 * @return array
	 */
	public function enqueue_editor_js( $dependencies ) {
		$dependencies[] = 'ace-editor';
		$dependencies[] = 'wpb-code-editor';

		return $dependencies;
	}
}
