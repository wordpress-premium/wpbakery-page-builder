<?php
/**
 * Autoload hooks related autosave plugin functionality.
 *
 * @note we require our autoload files everytime and everywhere after plugin load.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'vc_auto_save_add_settings' ) ) {
	/**
	 * Adds settings for auto-save feature.
	 *
	 * @param \Vc_Settings $settings The Visual Composer settings object.
	 * @since 7.6
	 */
	function vc_auto_save_add_settings( $settings ) {
		$settings->addField(
			'general',
			esc_html__( 'Auto save', 'js_composer' ),
			'auto_save',
			'vc_auto_save_sanitize_disable_callback',
			'vc_auto_save_disable_render_callback',
			array( 'info' => esc_html__( 'Enable auto-save, or use legacy save.', 'js_composer' ) )
		);
	}
}
if ( ! function_exists( 'vc_auto_save_sanitize_disable_callback' ) ) {
	/**
	 * Sanitizes the auto-save option.
	 *
	 * @param mixed $rules The auto-save rules.
	 * @return bool Sanitized auto-save status.
	 * @since 7.6
	 */
	function vc_auto_save_sanitize_disable_callback( $rules ) {
		return (bool) $rules;
	}
}

if ( ! function_exists( 'vc_auto_save_disable_render_callback' ) ) {
	/**
	 * Renders the auto-save checkbox in the WordPress dashboard,
	 * under WPBakery -> General Settings.
	 *
	 * @since 7.6
	 */
	function vc_auto_save_disable_render_callback() {
		$checked = get_option( 'wpb_js_auto_save', false );
		?>
		<label>
			<input type="checkbox"<?php echo esc_attr( $checked ) ? ' checked' : ''; ?> value="1"
					id="<?php echo esc_attr( 'wpb_js_auto_save' ); ?>"
					name="<?php echo esc_attr( 'wpb_js_auto_save' ); ?>">
			<?php esc_html_e( 'Enable', 'js_composer' ); ?>
		</label>
		<?php
	}
}

if ( ! function_exists( 'wpb_add_element_controls' ) ) {
	/**
	 * Adds controls for elements' Edit Form panel.
	 *
	 * @since 7.6
	 */
	function wpb_add_element_controls() {
		if ( ! get_option( 'wpb_js_auto_save' ) ) {
			vc_include_template('editors/popups/vc_ui-footer.tpl.php', array(
				'controls' => array(
					array(
						'name' => 'close',
						'label' => esc_html__( 'Close', 'js_composer' ),
						'css_classes' => 'vc_ui-button-fw',
					),
					array(
						'name' => 'save',
						'label' => esc_html__( 'Save changes', 'js_composer' ),
						'css_classes' => 'vc_ui-button-fw',
						'style' => 'action',
					),
				),
			));
		}
	}
}

add_action( 'vc_settings_tab-general', 'vc_auto_save_add_settings' );
add_action( 'wpb_add_element_controls', 'wpb_add_element_controls' );
