<?php
/**
 * Configuration file for [vc_media_grid] shortcode of 'Media Grid' element.
 *
 * @see https://kb.wpbakery.com/docs/inner-api/vc_map/ for more detailed information about element attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once __DIR__ . '/class-vc-grids-common.php';
$media_grid_params = VcGridsCommon::getMediaCommonAtts();

return array(
	'name' => esc_html__( 'Media Grid', 'js_composer' ),
	'base' => 'vc_media_grid',
	'icon' => 'vc_icon-vc-media-grid',
	'element_default_class' => 'wpb_content_element',
	'category' => esc_html__( 'Content', 'js_composer' ),
	'description' => esc_html__( 'Media grid from Media Library', 'js_composer' ),
	'params' => $media_grid_params,
);
