<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Promo popup class.
 *
 * @since   7.3
 */
class Vc_Promo_Popup {
	/**
	 * Render UI template.
	 */
	public function render_ui_template() {
		$user_id = get_current_user_id();
		$user_meta = get_user_meta( $user_id, '_vc_editor_promo_popup', true );

		if ( WPB_VC_VERSION == $user_meta ) {
			return;
		}

		vc_include_template( 'editors/popups/promo/promo-popup.tpl.php' );
		update_user_meta( $user_id, '_vc_editor_promo_popup', WPB_VC_VERSION );
	}
}
