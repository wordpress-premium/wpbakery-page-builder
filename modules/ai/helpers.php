<?php
/**
 * Helpers function related to particular module.
 *
 * @note to use you should check if module is enabled
 * vc_modules_manager()->is_module_on( 'vc-ai' )
 */

if ( ! function_exists( 'wpb_add_ai_to_text_field' ) ) {
	/**
	 * Add AI icon to text field.
	 *
	 * @param string $type
	 * @param string $field_id
	 * @since 7.4
	 */
	function wpb_add_ai_icon_to_text_field( $type, $field_id ) {
		if ( ! vc_user_access()->part( 'text_ai' )->can()->get() ) {
			return;

		}
		wpb_get_ai_icon_template( $type, $field_id );
	}
}

if ( ! function_exists( 'wpb_add_ai_to_code_field' ) ) {
	/**
	 * Add AI icon to code field.
	 *
	 * @param string $type
	 * @param string $field_id
	 * @since 7.4
	 */
	function wpb_add_ai_icon_to_code_field( $type, $field_id ) {
		if ( ! vc_user_access()->part( 'code_ai' )->can()->get() ) {
			return;
		}
		wpb_get_ai_icon_template( $type, $field_id );
	}
}

if ( ! function_exists( 'wpb_get_ai_icon_template' ) ) {
	/**
	 * Get AI icon.
	 *
	 * @param string $type
	 * @param string $field_id
	 * @param bool $is_include
	 * @since 7.4
	 *
	 * @return mixed
	 */
	function wpb_get_ai_icon_template( $type, $field_id, $is_include = true ) {
		$template = apply_filters( 'wpb_get_ai_icon_template', 'editors/partials/icon-ai.tpl.php', $type, $field_id );
		if ( $is_include ) {
			vc_include_template(
				$template,
				[
					'type' => $type,
					'field_id' => $field_id,
				]
			);
		} else {
			return vc_get_template(
				$template,
				[
					'type' => $type,
					'field_id' => $field_id,
				]
			);
		}
	}
}
