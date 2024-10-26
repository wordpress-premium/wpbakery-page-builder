<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * @var bool $can_unfiltered_html_cap
 */
?>
<div class="vc_ui-font-open-sans vc_ui-panel-window vc_media-xs vc_ui-panel" data-vc-panel=".vc_ui-panel-header-header" data-vc-ui-element="panel-post-settings" id="vc_ui-panel-post-settings">
	<div class="vc_ui-panel-window-inner">
		<?php
		vc_include_template('editors/popups/vc_ui-header.tpl.php', array(
			'title' => esc_html__( 'Page Settings', 'js_composer' ),
			'controls' => array( 'minimize', 'close' ),
			'header_css_class' => 'vc_ui-post-settings-header-container',
			'content_template' => '',
		));
		?>
		<div class="vc_ui-panel-content-container">
			<div class="vc_ui-panel-content vc_properties-list vc_edit_form_elements" data-vc-ui-element="panel-content">
				<div class="vc_row">
					<div class="vc_col-sm-12 vc_column" id="vc_settings-title-container">
						<div class="wpb_settings-title">
							<div class="wpb_element_label"><?php esc_html_e( 'Page title', 'js_composer' ); ?></div>
							<?php if ( is_string( $title_info ) ) { echo $title_info ; } ?>
						</div>
						<div class="edit_form_line">
							<?php
							if ( vc_modules_manager()->is_module_on( 'vc-ai' ) ) {
								wpb_add_ai_icon_to_text_field( 'textfield', 'vc_page-title-field' );
							}
							?>
							<input name="page_title" class="wpb-textinput vc_title_name" type="text" value="" id="vc_page-title-field" placeholder="<?php esc_attr_e( 'Please enter page title', 'js_composer' ); ?>">
						</div>
					</div>
					<?php
					if ( vc_modules_manager()->is_module_on( 'vc-post-custom-layout' ) ) {
						?>
						<div class="vc_col-sm-12 vc_column" id="vc_settings-post_custom_layout">
							<div class="wpb_element_label"><?php esc_html_e( 'Layout Option', 'js_composer' ); ?></div>
							<?php
							vc_include_template(
								'editors/partials/vc_post_custom_layout.tpl.php',
								[ 'location' => 'settings' ]
							);
							?>
						</div>
						<?php
					}
					?>
					<?php
					if ( vc_modules_manager()->is_module_on( 'vc-custom-css' ) ) {
						?>
						<div class="vc_col-sm-12 vc_column">
							<div class="wpb_settings-title">
								<div class="wpb_element_label">
									<?php esc_html_e( 'Custom CSS settings', 'js_composer' ); ?>
								</div>
								<?php if ( is_string( $css_info ) ) { echo $css_info ; } ?>	
							</div>
							<div class="edit_form_line">
								<div class="vc_ui-settings-text-wrapper">
									<p><?php esc_html_e( '<style>' ) ?></p>
									<?php
									if ( vc_modules_manager()->is_module_on( 'vc-ai' ) ) {
										wpb_add_ai_icon_to_code_field( 'custom_css', 'wpb_css_editor' );
									}
									?>
								</div>
								<pre id="wpb_css_editor" class="wpb_content_element custom_code wpb_frontend"></pre>
								<p><?php esc_html_e( '</style>' ) ?></p>
							</div>
						</div>
						<?php
					}
					?>
					<?php
					if ( vc_modules_manager()->is_module_on( 'vc-custom-js' ) ) {
						?>
						<div class="vc_col-sm-12 vc_column">
							<div class="wpb_settings-title">
								<div class="wpb_element_label">
									<?php esc_html_e( 'Custom JavaScript in <head>', 'js_composer' ); ?>
								</div>
								<?php if ( is_string( $js_head_info ) ) { echo $js_head_info ; } ?>	
							</div>
							<div class="edit_form_line">
								<div class="vc_ui-settings-text-wrapper">
									<p><?php esc_html_e( '<script>' ) ?></p>
									<?php
									if ( vc_modules_manager()->is_module_on( 'vc-ai' ) ) {
										wpb_add_ai_icon_to_code_field( 'custom_js', 'wpb_js_header_editor' );
									}
									?>
								</div>
								<pre id="wpb_js_header_editor" class="wpb_content_element custom_code wpb_frontend <?php echo $can_unfiltered_html_cap ?: 'wpb_missing_unfiltered_html'; ?>"><?php echo $can_unfiltered_html_cap ? '' : wpbakery()->getEditorsLocale()['unfiltered_html_access']; ?></pre>
								<p><?php esc_html_e( '</script>' ) ?></p>
							</div>
						</div>
						<div class="vc_col-sm-12 vc_column">
							<div class="wpb_settings-title">
								<div class="wpb_element_label">
									<?php esc_html_e( 'Custom JavaScript before </body>', 'js_composer' ); ?>
								</div>
								<?php if ( is_string( $js_body_info ) ) { echo $js_body_info ; } ?>	
							</div>
							<div class="edit_form_line">
								<div class="vc_ui-settings-text-wrapper">
									<p><?php esc_html_e( '<script>' ) ?></p>
									<?php
									if ( vc_modules_manager()->is_module_on( 'vc-ai' ) ) {
										wpb_add_ai_icon_to_code_field( 'custom_js', 'wpb_js_footer_editor' );
									}
									?>
								</div>
								<pre id="wpb_js_footer_editor" class="wpb_content_element custom_code wpb_frontend <?php echo $can_unfiltered_html_cap ?: 'wpb_missing_unfiltered_html'; ?>"><?php echo $can_unfiltered_html_cap ? '' : wpbakery()->getEditorsLocale()['unfiltered_html_access']; ?></pre>
								<p><?php esc_html_e( '</script>' ) ?></p>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		<!-- param window footer-->
		<?php
		vc_include_template('editors/popups/vc_ui-footer.tpl.php', array(
			'controls' => array(
				array(
					'name' => 'close',
					'label' => esc_html__( 'Close', 'js_composer' ),
				),
				array(
					'name' => 'save',
					'label' => esc_html__( 'Save changes', 'js_composer' ),
					'css_classes' => 'vc_ui-button-fw',
					'style' => 'action',
				),
			),
		));
		?>
	</div>
</div>
