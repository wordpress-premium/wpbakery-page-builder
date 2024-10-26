<?php
/**
 * Module Name: Post Custom Layout
 * Description: Add users optionality to change post initial layout.
 *
 * @since 7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Module entry point.
 *
 * @since 7.7
 */
class Vc_Post_Custom_Layout_Module {
	/**
	 * Module meta key.
	 *
	 * @since 7.7
	 * @var string
	 */
	const CUSTOM_CSS_META_KEY = '_wpb_post_custom_layout';
	/**
	 * Init module implementation.
	 *
	 * @since 7.7
	 */
	public function init() {
		add_filter( 'vc_post_meta_list', [ $this, 'add_custom_meta_to_update' ] );

		add_filter( 'wpb_set_post_custom_meta', [ $this, 'set_post_custom_meta' ], 10, 1 );

		add_action( 'template_include', [ $this, 'switch_post_custom_layout' ], 11 );
	}

	/**
	 * Add module meta to the plugin post custom meta list.
	 *
	 * @since 7.7
	 * @param array $meta_list
	 * @return array
	 */
	public function add_custom_meta_to_update( $meta_list ) {
		$meta_list[] = 'custom_layout';

		return $meta_list;
	}

	/**
	 * Set post custom meta.
	 *
	 * @since 7.7
	 * @param array $post_custom_meta
	 * @return array
	 */
	public function set_post_custom_meta( $post_custom_meta ) {
		$post_custom_meta['post_custom_layout'] = $this->get_custom_layout_name();

		return $post_custom_meta;
	}

	/**
	 * Change the path of the current template to our custom layout.
	 *
	 * @since 7.7
	 *
	 * @param string $template The path of the template to include.
	 * @return string
	 */
	public function switch_post_custom_layout( $template ) {
		if ( ! is_singular() ) {
			return $template;
		}
		$layout_name = $this->get_custom_layout_name();
		if ( ! $layout_name || 'default' === $layout_name ) {
			return $template;
		}

		$custom_layout_path = $this->get_custom_layout_path( $layout_name );
		if ( $custom_layout_path ) {
			$template = $custom_layout_path;
		}

		return apply_filters( 'vc_post_custom_layout_template', $template, $layout_name );
	}

	/**
	 * Get name of the custom layout.
	 *
	 * @note on a plugin core level right now we have only 'blank' layout.
	 * @since 7.7
	 *
	 * @return string
	 */
	public function get_custom_layout_name() {
		global $post;
		if ( $this->is_layout_switched_in_frontend_editor() ) {
			$layout_name = $this->get_layout_name_from_get_params();
		} else {
			$layout_name = $this->get_layout_from_meta();
		}

		$layout_name = empty( $layout_name ) ? '' : $layout_name;

		if ( ! empty( $post->post_content ) && ! $layout_name ) {
			$layout_name = 'default';
		}

		return apply_filters( 'vc_post_custom_layout_name', $layout_name );
	}

	/**
	 * Check if user switched layout in frontend editor.
	 *
	 * @note in such cases we should reload the page
	 * @since 7.7
	 *
	 * @return bool
	 */
	public function is_layout_switched_in_frontend_editor() {
		$params = $this->get_request_params();

		return isset( $params['vc_post_custom_layout'] );
	}

	/**
	 * For a frontend editor we keep layout as get param
	 * when we switching it inside editor and show user new layout inside editor.
	 *
	 * @since 7.7
	 *
	 * @return false|string
	 */
	public function get_layout_name_from_get_params() {
		$params = $this->get_request_params();

		return empty( $params['vc_post_custom_layout'] ) ? false : $params['vc_post_custom_layout'];
	}

	/**
	 * Retrieve get params.
	 *
	 * @description  we should obtain params from $_SERVER['HTTP_REFERER']
	 * if we try to get params inside iframe and from regular $_GET when outside
	 * @since 7.7
	 *
	 * @return array|false
	 */
	public function get_request_params() {
		if ( ! vc_is_page_editable() && ! vc_is_inline() ) {
			return false;
		}

		// inside iframe.
		if ( vc_is_page_editable() ) {
			$params = $this->get_params_from_server_referer();
			// outside iframe.
		} else {
            // phpcs:ignore
			$params = $_GET;
		}

		return $params;
	}

	/**
	 * Parse $_SERVER['HTTP_REFERER'] and get params from it.
	 *
	 * @since 7.7
	 *
	 * @return array|false
	 */
	public function get_params_from_server_referer() {
		if ( ! isset( $_SERVER['HTTP_REFERER'] ) ) {
			return false;
		}
        // phpcs:ignore
        $query = parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_QUERY );
		if ( ! $query ) {
			return false;
		}

		$params = [];
		parse_str( $query, $params );

		return $params;
	}

	/**
	 * Get previously saved layout from post meta.
	 *
	 * @since 7.7
	 *
	 * @return mixed
	 */
	public function get_layout_from_meta() {
		return get_post_meta( get_the_ID(), self::CUSTOM_CSS_META_KEY, true );
	}

	/**
	 * Get path of the custom layout.
	 *
	 * @note we keep all plugin layouts in include/templates/pages/layouts/ folder.
	 * @since 7.7
	 *
	 * @param string $layout_name
	 * @return string|false
	 */
	public function get_custom_layout_path( $layout_name ) {
		$custom_layout_path = vc_template( '/pages/layouts/' . sanitize_file_name( $layout_name ) . '.php' );
		if ( ! is_file( $custom_layout_path ) ) {
			return false;
		}

		return $custom_layout_path;
	}

	/**
	 * Get href for the custom layout by layout name.
	 *
	 * @since 7.7
	 *
	 * @param string $layout_name
	 * @return string
	 */
	public function get_layout_href_by_layout_name( $layout_name ) {
		if ( vc_is_page_editable() || vc_is_inline() ) {
			$frontend_editor = new Vc_Frontend_Editor();
			$href = $frontend_editor->getInlineUrl() . '&vc_post_custom_layout=' . $layout_name;
		} else {
			$href = '#';
		}

		return $href;
	}

	/**
	 * Check if layout active on current location.
	 *
	 * @since 7.7
	 *
	 * @param string $check_name
	 * @param string $location settings or welcome.
	 * @return bool
	 */
	public function check_if_layout_active( $check_name, $location ) {
		$current_name = $this->get_custom_layout_name();

		if ( $current_name && $current_name === $check_name ) {
			return true;
		}

		if ( ! $current_name && 'settings' === $location && 'default' === $check_name ) {
			return true;
		}

		return false;
	}
}
