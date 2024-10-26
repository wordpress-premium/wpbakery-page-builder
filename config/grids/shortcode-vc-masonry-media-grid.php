<?php
/**
 * Configuration file for [vc_masonry_media_grid] shortcode of 'Masonry Media Grid' element.
 *
 * @see https://kb.wpbakery.com/docs/inner-api/vc_map/ for more detailed information about element attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once __DIR__ . '/class-vc-grids-common.php';
$masonry_media_grid_params = VcGridsCommon::getMasonryMediaCommonAtts();

return array(
	'name' => esc_html__( 'Masonry Media Grid', 'js_composer' ),
	'base' => 'vc_masonry_media_grid',
	'icon' => 'vc_icon-vc-masonry-media-grid',
	'element_default_class' => 'wpb_content_element',
	'category' => esc_html__( 'Content', 'js_composer' ),
	'description' => esc_html__( 'Masonry media grid from Media Library', 'js_composer' ),
	'params' => $masonry_media_grid_params,
);
