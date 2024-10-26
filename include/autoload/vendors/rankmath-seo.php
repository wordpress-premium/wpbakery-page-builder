<?php
/**
 * Backward compatibility with "RankMath SEO" WordPress plugin.
 *
 * @see https://wordpress.org/plugins/seo-by-rank-math
 *
 * @since 4.4 vendors initialization moved to hooks in autoload/vendors.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Filter for Rank Math SEO images.
 *
 * @param array $images
 * @param int $id
 *
 * @return array
 */
function vc_rank_math_seo_image_filter( $images, $id ) {
	if ( empty( $images ) ) {
		$post = get_post( $id );
		if ( $post && strpos( $post->post_content, '[vc_row' ) !== false ) {
			preg_match_all( '/(?:image|images|ids|include)\=\"([^\"]+)\"/', $post->post_content, $matches );
			foreach ( $matches[1] as $m ) {
				$ids = explode( ',', $m );
				foreach ( $ids as $id ) {
					if ( (int) $id ) {
						$images[] = array(
							'src' => wp_get_attachment_url( $id ),
							'title' => get_the_title( $id ),
						);
					}
				}
			}
		}
	}

	return $images;
}

add_filter( 'rank_math/sitemap/urlimages', 'vc_rank_math_seo_image_filter', 10, 2 );
