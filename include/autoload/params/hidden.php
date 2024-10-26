<?php
/**
 * Autoload hooks related to form hidden fields.
 *
 * @note we require our autoload files everytime and everywhere after plugin load.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Required hooks for hidden field.
 *
 * @since 4.5
 */
require_once vc_path_dir( 'PARAMS_DIR', 'hidden/hidden.php' );

vc_add_shortcode_param( 'hidden', 'vc_hidden_form_field' );
add_filter( 'vc_edit_form_fields_render_field_hidden_before', 'vc_edit_form_fields_render_field_hidden_before' );
add_filter( 'vc_edit_form_fields_render_field_hidden_after', 'vc_edit_form_fields_render_field_hidden_after' );
