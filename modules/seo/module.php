<?php
/**
 * Module Name: SEO
 * Description: Correspond for SEO plugin functionality.
 *
 * @since 7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Module entry point.
 *
 * @since 7.7
 */
class Vc_Seo_Module {
	/**
	 * Post plugin settings seo meta.
	 *
	 * @since 7.7
	 * @var array
	 */
	public $post_seo_meta;

	/**
	 * Post plugin settings seo meta key.
	 *
	 * @since 7.7
	 * @var string
	 */
	const MODULE_POST_META_KEY = '_wpb_post_custom_seo_settings';

	/**
	 * Init module implementation.
	 */
	public function init() {
		add_action( 'wp', function () {
			if ( vc_mode() !== 'page' || ! is_singular() ) {
				return;
			}

			$this->set_plugin_seo_post_meta();
			if ( ! $this->post_seo_meta ) {
				return;
			}

			add_filter( 'wp_title', [ $this, 'filter_title' ], 15 );
			add_filter( 'pre_get_document_title', [ $this, 'filter_title' ], 15 );
			add_filter( 'wp_head', [ $this, 'add_seo_head' ], 15 );
		});

		add_action( 'wp_ajax_wpb_seo_check_key_phrase', function () {
			$is_key_phrase_in_other_posts = $this->check_key_phrase_in_other_posts();

			wp_send_json_success( $is_key_phrase_in_other_posts );
		} );

		add_action( 'vc_nav_control_list', [ $this, 'add_seo_to_nav_control_list' ], 10, 1 );

		add_action( 'vc_editor_footer', [ $this, 'add_setting_popup' ], 10, 1 );

		add_filter( 'vc_nav_controls', [ $this, 'add_seo_button_to_nav_controls' ], 10, 1 );

		add_filter( 'vc_nav_front_controls', [ $this, 'add_seo_button_to_nav_controls' ], 10, 1 );

		add_filter( 'vc_post_meta_list', [ $this, 'add_custom_meta_to_update' ] );

		add_filter( 'vc_before_update_post_data', [ $this, 'set_post_slug' ] );

		add_filter( 'wp_insert_post_data', [ $this, 'change_post_fields' ], 10, 2 );

		add_filter( 'wpb_set_post_custom_meta', [ $this, 'set_post_custom_meta' ], 10, 2 );
	}

	/**
	 * Set plugin seo post meta.
	 *
	 * @since 7.7
	 */
	public function set_plugin_seo_post_meta() {
		$this->post_seo_meta = $this->get_plugin_seo_post_meta();
	}

	/**
	 * Get plugin seo post meta.
	 *
	 * @since 7.7
	 * @return array
	 */
	public function get_plugin_seo_post_meta() {

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return [];
		}

		$post_seo_meta = get_post_meta( get_the_ID(), self::MODULE_POST_META_KEY, true );
		if ( empty( $post_seo_meta ) ) {
			return [];
		}

		$post_seo_meta = json_decode( $post_seo_meta, true );
		if ( ! is_array( $post_seo_meta ) ) {
			return [];
		}

		return $post_seo_meta;
	}

	/**
	 * Replace title with plugin seo title.
	 *
	 * @since 7.7
	 * @param string $title
	 * @return string
	 */
	public function filter_title( $title ) {
		$seo_title = $this->get_settings_seo_title();
		if ( ! $seo_title ) {
			return $title;
		}

		remove_filter( 'pre_get_document_title', [ $this, 'filter_title' ], 15 );
		$title = $seo_title;
		add_filter( 'pre_get_document_title', [ $this, 'filter_title' ], 15 );

		return $title;
	}


	/**
	 * Get plugin seo title.
	 *
	 * @since 7.7
	 * @return string
	 */
	public function get_settings_seo_title() {
		if ( empty( $this->post_seo_meta['title'] ) ) {
			return '';
		}

		$title = $this->post_seo_meta['title'];
		// Remove excess whitespace.
		$title = preg_replace( '[\s\s+]', ' ', $title );

		$title = wp_strip_all_tags( stripslashes( $title ), true );
		return convert_smilies( esc_html( $title ) );
	}

	/**
	 * Presents the head in the front-end. Resets wp_query if it's not the main query.
	 *
	 * @since 7.7
	 */
	public function add_seo_head() {
		global $wp_query;

		$old_wp_query = $wp_query;
		// Reason: The recommended function, wp_reset_postdata, doesn't reset wp_query.
        // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query
		wp_reset_query();

		$this->output_seo_head();

		// Reason: we have to restore the query.
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_query'] = $old_wp_query;
	}

	/**
	 * Output seo tags in the head.
	 *
	 * @since 7.7
	 */
	public function output_seo_head() {
		$this->output_meta_description();

		$this->output_meta_facebook();
		$this->output_meta_twitter();
	}

	/**
	 * Output meta description.
	 *
	 * @since 7.7
	 */
	public function output_meta_description() {
		if ( empty( $this->post_seo_meta['description'] ) ) {
			return;
		}

		$description = trim( wp_strip_all_tags( stripslashes( $this->post_seo_meta['description'] ) ) );

		echo '<meta name="description" content="' . esc_attr( $description ) . '">';
	}

	/**
	 * Check if post key phrase is present in other posts.
	 *
	 * @since 7.7
	 * @return bool
	 */
	public function check_key_phrase_in_other_posts() {
		$key_phrase = trim( sanitize_text_field( vc_post_param( 'key_phrase', '' ) ) );

		if ( empty( $key_phrase ) ) {
			return false;
		}

		$current_post_id = vc_post_param( 'post_id', '' );
		$args = [
			'post_type' => 'any',
			'posts_per_page' => 2,
			'meta_query' => [
				[
					'key' => self::MODULE_POST_META_KEY,
					'value' => '"focus-keyphrase":"' . $key_phrase . '"',
					'compare' => 'LIKE',
				],
			],
			'post__not_in' => [ $current_post_id ],
		];
		$post = get_posts( $args );

		return is_array( $post ) && count( $post ) >= 2;
	}

	/**
	 * Output meta facebook.
	 *
	 * @since 7.7
	 */
	public function output_meta_facebook() {
		if ( empty( $this->post_seo_meta['social-title-facebook'] ) || empty( $this->post_seo_meta['social-description-facebook'] ) ) {
			return;
		}

		$meta = $this->collect_page_social_meta();

		$meta['og:title'] = $this->post_seo_meta['social-title-facebook'];
		$meta['og:description'] = $this->post_seo_meta['social-description-facebook'];

		$meta = $this->add_facebook_image_meta( $meta );

		foreach ( $meta as $key => $value ) {
			echo '<meta property="' . esc_attr( $key ) . '" content="' . esc_attr( $value ) . '">';
		}
	}

	/**
	 * Add facebook page social meta.
	 *
	 * @since 7.7
	 * @return array
	 */
	public function collect_page_social_meta() {
		$site_social_meta['og:locale'] = get_locale();
		$site_social_meta['og:type'] = 'article';
		$site_social_meta['og:url'] = get_permalink();
		$site_social_meta['og:site_name'] = get_bloginfo();

		return $site_social_meta;
	}

	/**
	 * Add facebook image meta.
	 *
	 * @since 7.7
	 * @param array $meta
	 * @return array
	 */
	public function add_facebook_image_meta( $meta ) {
		if ( empty( $this->post_seo_meta['social-image-facebook'] ) ) {
			return $meta;
		}

		$image_id = $this->post_seo_meta['social-image-facebook'];
		$image_data = wp_get_attachment_image_src( $image_id, 'full' );
		if ( is_array( $image_data ) ) {
			$meta['og:image'] = $image_data[0];
			$meta['og:image:width'] = $image_data[1];
			$meta['og:image:height'] = $image_data[2];
		}

		$path = wp_get_original_image_path( $image_id );
		if ( $path ) {
			$meta['og:image:type'] = wp_get_image_mime( wp_get_original_image_path( $image_id ) );
		}

		return $meta;
	}

	/**
	 * Output meta X (twitter).
	 *
	 * @since 7.7
	 */
	public function output_meta_twitter() {
		if ( empty( $this->post_seo_meta['social-title-x'] ) || empty( $this->post_seo_meta['social-description-x'] ) ) {
			return;
		}

		$meta['twitter:card'] = 'summary_large_image';
		$meta['twitter:title'] = $this->post_seo_meta['social-title-x'];
		$meta['twitter:description'] = $this->post_seo_meta['social-description-x'];

		$meta = $this->add_twitter_image_meta( $meta );

		foreach ( $meta as $key => $value ) {
			echo '<meta property="' . esc_attr( $key ) . '" content="' . esc_attr( $value ) . '">';
		}
	}

	/**
	 * Add twitter image meta.
	 *
	 * @since 7.7
	 *
	 * @param array $meta
	 * @return array
	 */
	public function add_twitter_image_meta( $meta ) {
		if ( empty( $this->post_seo_meta['social-image-twitter'] ) ) {
			return $meta;
		}

		$image_id = $this->post_seo_meta['social-image-twitter'];
		$image_data = wp_get_attachment_image_src( $image_id, 'full' );
		if ( is_array( $image_data ) ) {
			$meta['twitter:image'] = $image_data[0];
		}

		return $meta;
	}

	/**
	 * Add module to nav control list.
	 *
	 * @since 7.7
	 * @param array $controls
	 * @return array
	 */
	public function add_seo_to_nav_control_list( $controls ) {
		$controls[] = 'seo';
		return $controls;
	}

	/**
	 * Add popup seo popup to our editors.
	 *
	 * @since 7.7
	 * @param Vc_Backend_Editor | Vc_Frontend_Editor $editor
	 * @return void
	 */
	public function add_setting_popup( $editor ) {
		require_once vc_path_dir( 'MODULES_DIR', 'seo/popups/class-vc-post-seo.php' );
		$post_seo = new Vc_Post_Seo( $editor );
		$post_seo->render_ui_template();
	}

	/**
	 * Add seo button to nav controls.
	 *
	 * @param array $controls
	 *
	 * @since 7.7
	 * @return array
	 */
	public function add_seo_button_to_nav_controls( $controls ) {
		$controls[] = [
			'seo',
			'<li class="vc_pull-right"><a href="javascript:;" class="vc_icon-btn vc_seo-button" id="vc_seo-button" title="' . esc_attr__( 'WPBakery SEO', 'js_composer' ) . '"><i class="vc-composer-icon vc-c-icon-seo"></i></a></li>',
		];
		return $controls;
	}

	/**
	 * Add custom module meta to the plugin post custom meta list.
	 *
	 * @since 7.7
	 * @param array $meta_list
	 * @return array
	 */
	public function add_custom_meta_to_update( $meta_list ) {
		$meta_list[] = 'custom_seo_settings';

		return $meta_list;
	}

	/**
	 * Set post slug.
	 *
	 * @since 7.7
	 * @param WP_Post | array $post
	 * @param int $post_id
	 * @return WP_Post | array
	 */
	public function set_post_slug( $post, $post_id = 0 ) {
		$post_seo = vc_post_param( 'vc_post_custom_seo_settings' );
		if ( empty( $post_seo ) ) {
			return $post;
		}

		$post_seo = json_decode( stripslashes( $post_seo ), true );
		if ( empty( $post_seo['slug'] ) ) {
			return $post;
		}

		// in case when we update post permalink through native wp way.
		$post_seo_meta = $this->get_plugin_seo_post_meta();
		if ( ! empty( $post_seo_meta['slug'] ) && $post_seo_meta['slug'] === $post_seo['slug'] ) {
			return $post;
		}

		if ( is_array( $post ) ) {
			$slug = wp_unique_post_slug(
				sanitize_title( $post_seo['slug'] ),
				$post_id,
				$post['post_status'],
				$post['post_type'],
				$post['post_parent']
			);

			$post['post_name'] = $slug;
		} else {
			$slug = wp_unique_post_slug(
				sanitize_title( $post_seo['slug'] ),
				$post->ID,
				$post->post_status,
				$post->post_type,
				$post->post_parent
			);
			$post->post_name = $slug;
		}

		return $post;
	}

	/**
	 * Change post fields corresponding to post settings.
	 *
	 * @since 7.7
	 * @param array $post_fields
	 * @param array $post_array
	 * @return array
	 */
	public function change_post_fields( $post_fields, $post_array ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || vc_is_inline() ) {
			return $post_fields;
		}

		$post_fields = $this->set_post_slug( $post_fields, $post_array['ID'] );

		return $post_fields;
	}

	/**
	 * Set post custom meta.
	 *
	 * @since 7.7
	 * @param array $post_custom_meta
	 * @param WP_Post $post
	 * @return array
	 */
	public function set_post_custom_meta( $post_custom_meta, $post ) {
		$post_custom_meta['post_custom_seo_settings'] =
			get_post_meta( $post->ID, self::MODULE_POST_META_KEY, true );

		return $post_custom_meta;
	}
}
