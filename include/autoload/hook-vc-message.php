<?php
/**
 * Autoload hooks for plugin messages.
 *
 * @note we require our autoload files everytime and everywhere after plugin load.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( 'vc_edit_form' === vc_post_param( 'action' ) ) {
	VcShortcodeAutoloader::getInstance()->includeClass( 'WPBakeryShortCode_Vc_Message' );

	add_filter( 'vc_edit_form_fields_attributes_vc_message', array(
		'WPBakeryShortCode_Vc_Message',
		'convertAttributesToMessageBox2',
	) );
}
