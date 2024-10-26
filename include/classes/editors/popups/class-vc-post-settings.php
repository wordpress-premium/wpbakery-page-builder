<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Post settings like custom css for page are displayed here.
 *
 * @since 4.3
 */
class Vc_Post_Settings {
	protected $editor;

	/**
	 * @param $editor
	 */
	public function __construct( $editor ) {
		$this->editor = $editor;
	}

	public function editor() {
		return $this->editor;
	}

	public function renderUITemplate() {

		$title_info = vc_get_template( 'editors/partials/param-info.tpl.php', ['description' => sprintf( esc_html__( 'Change title of the current %s (Note: changes may not be displayed in a preview, but will take effect after saving page).', 'js_composer' ), esc_html( get_post_type() ) )] );
		$css_info = vc_get_template( 'editors/partials/param-info.tpl.php', ['description' => esc_html__( 'Enter custom CSS (Note: it will be outputted only on this particular page).', 'js_composer' )] );
		$js_head_info = vc_get_template( 'editors/partials/param-info.tpl.php', ['description' => esc_html__( 'Enter custom JS (Note: it will be outputted only on this particular page inside <head> tag).', 'js_composer' )] );
		$js_body_info = vc_get_template( 'editors/partials/param-info.tpl.php', ['description' => esc_html__( 'Enter custom JS (Note: it will be outputted only on this particular page before closing', 'js_composer' )] );

		vc_include_template( 'editors/popups/vc_ui-panel-post-settings.tpl.php',
		array(
			'box' => $this,
			'can_unfiltered_html_cap' =>
				vc_user_access()->part( 'unfiltered_html' )->checkStateAny( true, null )->get(),
			'title_info' => $title_info,
			'css_info' => $css_info,
			'js_head_info' => $js_head_info,
			'js_body_info' => $js_body_info,
		) );
	}
}
