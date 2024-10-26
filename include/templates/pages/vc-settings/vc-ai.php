<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
require_once vc_path_dir( 'MODULES_DIR', 'ai/class-vc-ai-modal-controller.php' );
$ai_modal = new Vc_Ai_Modal_Controller();

$request = [
	'ai_element_type' => 'textarea_html',
	'ai_element_id' => 'textarea_html_text',
	'is_settings_page' => true,
];

$modal = $ai_modal->get_modal_data( $request );
if ( 'promo' === $modal['type'] ) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $modal['content'];
	return;
}
?>

<div class="tab_intro">
	<p><?php esc_html_e( 'WPBakery AI credits allow you to use artificial intelligence within the editor to generate, improve, and translate content.', 'js_composer' ); ?></p>
	<h4 class="vc-ai-tokens-usage">
		<?php esc_html_e( 'Your monthly credit usage: ', 'js_composer' ); ?>
		<span class="vc-ai-tokens-left"><strong><?php esc_html_e( $modal['tokens_left'] ); ?></strong></span>/<span class="vc-ai-tokens-total"><strong><?php esc_html_e( $modal['tokens_total'] ); ?></strong></span>
	</h4>
</div>
