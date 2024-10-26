<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * @var Vc_Ai_Modal_Controller $ai_modal_controller
 * @var string $ai_element_type
 * @var string $ai_element_id
 */
?>
<div class="vc_col-xs-12 wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param vc_column" data-optional-form-field="contentType">
	<div class="wpb-param-heading">
		<div class="wpb_element_label"><?php esc_html_e( 'Content type', 'js_composer' ); ?></div>
		<?php $content_type_info = vc_get_template( 'editors/partials/param-info.tpl.php', ['description' => esc_html__( 'Select whether you want to generate new or improve the existing content.', 'js_composer' )] );
		// phpcs:ignore
		if ( is_string( $content_type_info ) ) { echo $content_type_info; }
		?>
	</div>
	<div class="edit_form_line">
		<select name="contentType" class="wpb_vc_param_value wpb-input wpb-select source dropdown">
			<?php
			foreach ( $ai_modal_controller->get_content_generate_variant() as $slug => $value ) {
				// phpcs:ignore
				echo '<option value="' . esc_html( $slug ) . '"' .  $ai_modal_controller->output_optionality_data_attr('content_type', $slug) . '>' . esc_html( $value ) . '</option>';
			}
			?>
		</select>
	</div>
</div>
<div class="vc_col-xs-12 wpb_el_type_textarea vc_wrapper-param-type-textarea vc_shortcode-param vc_column" data-optional-form-field="prompt">
	<div class="wpb_element_label"><?php esc_html_e( 'Describe content', 'js_composer' ); ?></div>
	<div class="edit_form_line">
		<textarea name="prompt" class="wpb_vc_param_value wpb-textarea text textarea"></textarea>
	</div>
</div>
<div class="vc_col-xs-12 wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param vc_column" data-optional-form-field="language" style="display: none">
	<div class="wpb_element_label"><?php esc_html_e( 'Language', 'js_composer' ); ?></div>
	<div class="edit_form_line">
		<select name="language" class="wpb_vc_param_value wpb-input wpb-select source dropdown">
			<?php
			foreach ( $ai_modal_controller->get_languages_list() as $value ) {
				echo '<option value="' . esc_html( $value ) . '">' . esc_html( $value ) . '</option>';
			}
			?>
		</select>
	</div>
</div>
<div class="vc_col-xs-12 wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param vc_column" data-optional-form-field="toneOfVoice">
	<div class="wpb-param-heading">
		<div class="wpb_element_label"><?php esc_html_e( 'Tone of voice', 'js_composer' ); ?></div>
		<?php $voice_tone_info = vc_get_template( 'editors/partials/param-info.tpl.php', ['description' => esc_html__( 'Select the tone of voice that is appealing to your audience.', 'js_composer' )] );
		// phpcs:ignore
		if ( is_string( $voice_tone_info ) ) { echo $voice_tone_info; }
		?>
	</div>
	<div class="edit_form_line">
		<select name="toneOfVoice" class="wpb_vc_param_value wpb-input wpb-select source dropdown">
			<?php
			foreach ( $ai_modal_controller->get_ton_of_voice_list() as $slug => $value ) {
				echo '<option value="' . esc_html( $slug ) . '">' . esc_html( $value ) . '</option>';
			}
			?>
		</select>
	</div>
</div>
<div class="vc_col-xs-12 wpb_el_type_dropdown vc_wrapper-param-type-dropdown vc_shortcode-param vc_column" data-optional-form-field="length">
	<div class="wpb-param-heading">
		<div class="wpb_element_label"><?php esc_html_e( 'Length', 'js_composer' ); ?></div>
		<?php $length_info = vc_get_template( 'editors/partials/param-info.tpl.php', ['description' => esc_html__( 'Select the length of the content.', 'js_composer' )] );
		// phpcs:ignore
		if ( is_string( $length_info ) ) { echo $length_info; }
		?>
	</div>
	<div class="edit_form_line">
		<select name="length" class="wpb_vc_param_value wpb-input wpb-select source dropdown">
			<?php
			foreach ( $ai_modal_controller->get_number_of_symbols_list( $ai_element_type ) as $slug => $value ) {
				echo '<option value="' . esc_html( $slug ) . '">' . esc_html( $value ) . '</option>';
			}
			?>
		</select>
	</div>
</div>
<div class="vc_col-sm-12 vc_column" data-optional-form-field="keyWords">
	<div class="wpb-param-heading">
		<div class="wpb_element_label"><?php esc_html_e( 'Keywords', 'js_composer' ); ?></div>
		<?php $keywords_info = vc_get_template( 'editors/partials/param-info.tpl.php', ['description' => esc_html__( 'Enter keywords to be added to the content (separate keywords with comma).', 'js_composer' )] );
		// phpcs:ignore
		if ( is_string( $keywords_info ) ) { echo $keywords_info; }
		?>
	</div>
	<div class="edit_form_line">
		<input name="keyWords" class="wpb-textinput vc_title_name" type="text" value="" id="vc_page-title-field" placeholder="<?php esc_attr_e( 'Enter keywords to be added to the content (separate keywords with comma)', 'js_composer' ); ?>">
	</div>
</div>
<div class="vc_col-sm-12 vc_column">
	<button class="vc_general vc_ui-button vc_ui-button-action vc_ui-button-shape-rounded vc_ui-button-fw vc_ai-generate-button">
		<?php
		esc_html_e( 'Generate', 'js_composer' );
		?>
	</button>
</div>
<div class="vc_col-xs-12 wpb_el_type_textarea vc_wrapper-param-type-textarea vc_shortcode-param vc_column">
	<div class="wpb-param-heading">
		<div class="wpb_element_label"><?php esc_html_e( 'Output', 'js_composer' ); ?></div>
		<?php $output_info = vc_get_template( 'editors/partials/param-info.tpl.php', ['description' => esc_html__( 'WPBakery AI generated content will appear here.', 'js_composer' )] );
		// phpcs:ignore
		if ( is_string( $output_info ) ) { echo $output_info; }
		?>
		<?php
			$icon = vc_get_template( 'editors/partials/copy-output.tpl.php', [] );
			echo wp_kses_post( $icon );
		?>
	</div>
	<div class="edit_form_line">
		<textarea name="text" class="wpb_vc_param_value wpb-textarea text textarea wpb_ai-generated-content" rows="10" disabled></textarea>
	</div>
</div>
<input type="hidden" name="wpb-ai-element-type" value="<?php echo empty( $ai_element_type ) ? 'textarea' : esc_attr( $ai_element_type ); ?>">
<input type="hidden" name="wpb-ai-element-id" value="<?php echo empty( $ai_element_id ) ? 'textarea' : esc_attr( $ai_element_id ); ?>">
