<?php
/**
 * Backward compatibility with native "Gutenberg" WordPress editor.
 *
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Disable Gutenberg for classic editor.
 *
 * @param WP_Post $post
 * @return bool
 */
function vcv_disable_gutenberg_for_classic_editor( $post ) {
	return false;
}

/**
 * Add field to settings.
 *
 * @param \Vc_Settings $settings
 */
function vc_gutenberg_add_settings( $settings ) {
	global $wp_version;
	if ( function_exists( 'the_gutenberg_project' ) || version_compare( $wp_version, '4.9.8', '>' ) ) {
		$settings->addField( 'general', esc_html__( 'Disable Gutenberg Editor', 'js_composer' ), 'gutenberg_disable', 'vc_gutenberg_sanitize_disable_callback', 'vc_gutenberg_disable_render_callback', array( 'info' => esc_html__( 'Disable Gutenberg Editor.', 'js_composer' ) ) );
	}
}
/**
 * Sanitize disable callback.
 *
 * @param mixed $rules
 *
 * @return bool
 */
function vc_gutenberg_sanitize_disable_callback( $rules ) {
	return (bool) $rules;
}

/**
 * Not responsive checkbox callback function
 */
function vc_gutenberg_disable_render_callback() {
    // phpcs:ignore
	$checked = ( $checked = get_option( 'wpb_js_gutenberg_disable' ) ) ? $checked : false;
	?>
	<label>
		<input type="checkbox"<?php echo esc_attr( $checked ) ? ' checked' : ''; ?> value="1"
			name="<?php echo 'wpb_js_gutenberg_disable'; ?>">
		<?php esc_html_e( 'Disable', 'js_composer' ); ?>
	</label>
	<?php
}

/**
 * Check if Gutenberg is disabled.
 *
 * @param bool $result
 * @param string $post_type
 * @return bool
 */
function vc_gutenberg_check_disabled( $result, $post_type ) {
	global $pagenow;
	if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
		// we are in single post type editing.
        // phpcs:ignore:WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['classic-editor'] ) && ! isset( $_GET['classic-editor__forget'] ) ) {
			return false;
		}
        // phpcs:ignore:WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['classic-editor__forget'] ) ) {
			return true;
		}
		if ( 'wpb_gutenberg_param' === $post_type ) {
			return true;
		}
        // phpcs:ignore:WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['vcv-gutenberg-editor'] ) && ( get_option( 'wpb_js_gutenberg_disable' ) || vc_is_wpb_content() || isset( $_GET['classic-editor'] ) ) ) {
			return false;
		}
	}

	return $result;
}

/**
 * Check if Gutenberg is disabled for certain post type.
 *
 * @param array $editors
 * @param string $post_type
 * @return bool|array
 */
function vc_gutenberg_check_disabled_regular( $editors, $post_type ) {
	if ( 'wpb_gutenberg_param' === $post_type ) {
		$editors['gutenberg_editor'] = false;
	}
    // phpcs:ignore:WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $_GET['vcv-gutenberg-editor'] ) && ( get_option( 'wpb_js_gutenberg_disable' ) || vc_is_wpb_content() || isset( $_GET['classic-editor'] ) ) ) {
		$editors['gutenberg_editor'] = false;
		$editors['classic_editor'] = false;
	}

	return $editors;
}

/**
 * Unset classic editor state.
 *
 * @param array $state
 * @return array
 */
function vc_classic_editor_post_states( $state ) {
	if ( vc_is_wpb_content() ) {
		unset( $state['classic-editor-plugin'] );
	}

	return $state;
}

/**
 * Check if current post is WPBakery content.
 *
 * @return bool
 */
function vc_is_wpb_content() {
	$post = get_post();
	if ( ! empty( $post ) && isset( $post->post_content ) && wpb_get_post_editor_status( $post->ID ) ) {
		return true;
	}

	return false;
}

/**
 * Register [vc_lean_map] shortcode.
 */
function vc_gutenberg_map() {
	global $wp_version;
	if ( function_exists( 'the_gutenberg_project' ) || version_compare( $wp_version, '4.9.8', '>' ) ) {
		vc_lean_map( 'vc_gutenberg', null, __DIR__ . '/shortcode-vc-gutenberg.php' );
	}
}

add_filter( 'classic_editor_enabled_editors_for_post', 'vc_gutenberg_check_disabled_regular', 10, 2 );
add_filter( 'use_block_editor_for_post_type', 'vc_gutenberg_check_disabled', 10, 2 );
add_filter( 'display_post_states', 'vc_classic_editor_post_states', 11, 2 );
add_action( 'vc_settings_tab-general', 'vc_gutenberg_add_settings' );
add_action( 'init', 'vc_gutenberg_map' );

// @see include/params/gutenberg/class-vc-gutenberg-param.php.
require_once vc_path_dir( 'PARAMS_DIR', 'gutenberg/class-vc-gutenberg-param.php' );
new Vc_Gutenberg_Param();
