<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Edit row layout
 *
 * @since   4.3
 */
class Vc_Edit_Layout {
	public function renderUITemplate() {
		global $vc_row_layouts;
		$row_info = vc_get_template( 'editors/partials/param-info.tpl.php', ['description' => esc_html__( 'Select row layout from predefined options.', 'js_composer' )] );
		$custom_row_info = vc_get_template( 'editors/partials/param-info.tpl.php', ['description' => esc_html__( 'Change particular row layout manually by specifying number of columns and their size value.', 'js_composer' )] );
		vc_include_template( 'editors/popups/vc_ui-panel-row-layout.tpl.php', array(
			'vc_row_layouts' => $vc_row_layouts,
			'rowInfo' => $row_info,
			'customRowInfo' => $custom_row_info
		)
		);
	}
}
