<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/** @var bool $is_about_page */
?>
<img class="vc-featured-img" src="<?php echo esc_url( vc_asset_url( 'vc/wpb-7-9-about.png' ) ); ?>"/>

<div class="vc-feature-text">
	<h3><?php esc_html_e( 'Introducing New Features in 7.9 Release', 'js_composer' ); ?></h3>

	<p><?php esc_html_e( 'Enjoy a modern color picker with custom presets, easy AI content copying, and quick access to your favorite elements. Set minimum height for Rows or Sections, and control scroll behavior via the Module Manager.', 'js_composer' ); ?></p>
	<ul>
		<li><?php esc_html_e( 'Utilize the advanced color picker with custom presets', 'js_composer' ); ?></li>
		<li><?php esc_html_e( 'Copy and paste AI-generated content anywhere', 'js_composer' ); ?></li>
		<li><?php esc_html_e( 'Quickly access your most-used elements', 'js_composer' ); ?></li>
		<li><?php esc_html_e( 'Set min-height for Rows or Sections', 'js_composer' ); ?></li>
		<li><?php esc_html_e( 'Control scroll behavior via the Module Manager', 'js_composer' ); ?></li>
	</ul>
	<?php
	$tabs = vc_settings()->getTabs();
	$is_license_tab_access = isset( $tabs['vc-updater'] ) && vc_user_access()->part( 'settings' )->can( 'vc-updater-tab' )->get();
	if ( $is_about_page && ! vc_license()->isActivated() && $is_license_tab_access ) : ?>
		<div class="vc-feature-activation-section">
			<?php $url = 'admin.php?page=vc-updater'; ?>
			<a href="<?php echo esc_attr( is_network_admin() ? network_admin_url( $url ) : admin_url( $url ) ); ?>" class="vc-feature-btn" id="vc_settings-updater-button" data-vc-action="activation"><?php esc_html_e( 'Activate License', 'js_composer' ); ?></a>
			<p class="vc-feature-info-text">
				<?php esc_html_e( 'Direct plugin activation only.', 'js_composer' ); ?>
				<a href="https://wpbakery.com/wpbakery-page-builder-license/?utm_source=wpdashboard&utm_medium=wpb-settings-about-whats-new&utm_content=text" target="_blank" rel="noreferrer noopener"><?php esc_html_e( 'Don\'t have a license?', 'js_composer' ); ?></a>
			</p>
		</div>
	<?php endif; ?>
</div>
