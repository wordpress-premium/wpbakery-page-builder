<?php
/**
 * Module toggle template
 *
 * @var array $module_data
 * @var string $module_slug
 * @var string $module_value
 */
?>

<div class="wpb-module-wrapper">
	<p><?php echo esc_html( $module_data['name'] ) ?></p>
	<div class="wpb-toggle-wrapper">
		<input type="checkbox" <?php esc_attr_e( $module_value ); ?> id="<?php esc_html_e( $module_slug ); ?>" class="module-toggle" />
		<label for="<?php esc_html_e( $module_slug ); ?>"><?php esc_html_e( $module_data['name'] ); ?></label>
	</div>
</div>
