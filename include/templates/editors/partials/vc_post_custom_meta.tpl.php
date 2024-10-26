<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/** @var Vc_Backend_Editor|Vc_Frontend_Editor $editor */

foreach ( $editor->post_custom_meta as $meta_key => $meta_value ) {
	$id = 'vc_' .  str_replace( '_', '-', $meta_key );
	?>
	<input type="hidden" name="vc_<?php echo esc_attr( $meta_key ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $meta_value ); ?>" autocomplete="off"/>
	<?php
}
?>
