<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * WPBakery Page Builder basic class.
 * @since 4.2
 */
class Vc_Base {
	/**
	 * Shortcode's edit form.
	 *
	 * @since  4.2
	 * @access protected
	 * @var bool|Vc_Shortcode_Edit_Form
	 */
	protected $shortcode_edit_form = false;

	/**
	 * Templates management panel editor.
	 * @since  4.4
	 * @access protected
	 * @var bool|Vc_Templates_Panel_Editor
	 */
	protected $templates_panel_editor = false;
	/**
	 * Presets management panel editor.
	 * @since  5.2
	 * @access protected
	 * @var bool|Vc_Preset_Panel_Editor
	 */
	protected $preset_panel_editor = false;
	/**
	 * Post object for VC in Admin.
	 *
	 * @since  4.4
	 * @access protected
	 * @var bool|Vc_Post_Admin
	 */
	protected $post_admin = false;
	/**
	 * Post object for VC.
	 *
	 * @since  4.4.3
	 * @access protected
	 * @var bool|Vc_Post_Admin
	 */
	protected $post = false;
	/**
	 * List of shortcodes map to VC.
	 *
	 * @since  4.2

	 * @var array WPBakeryShortCodeFishBones
	 */
	protected $shortcodes = array();

	/** @var  Vc_Shared_Templates */
	public $shared_templates;

	/**
	 * Load default object like shortcode parsing.
	 *
	 * @since  4.2
	 */
	public function init() {
		do_action( 'vc_before_init_base' );
		$this->postAdmin()->init();
		add_filter( 'body_class', array(
			$this,
			'bodyClass',
		) );
		add_filter( 'the_excerpt', array(
			$this,
			'excerptFilter',
		) );
		add_action( 'wp_head', array(
			$this,
			'addMetaData',
		) );
		if ( is_admin() ) {
			$this->initAdmin();
		} else {
			$this->initPage();
		}
		do_action( 'vc_after_init_base' );
	}

	/**
	 * Post object for interacting with Current post data.
	 * @return Vc_Post_Admin
	 * @since 4.4
	 */
	public function postAdmin() {
		if ( false === $this->post_admin ) {
			require_once vc_path_dir( 'CORE_DIR', 'class-vc-post-admin.php' );
			$this->post_admin = new Vc_Post_Admin();
		}

		return $this->post_admin;
	}

	/**
	 * Build VC for frontend pages.
	 *
	 * @since  4.2
	 */
	public function initPage() {
		do_action( 'vc_build_page' );
		add_action( 'template_redirect', array(
			$this,
			'frontCss',
		) );
		add_action( 'template_redirect', array(
			'WPBMap',
			'addAllMappedShortcodes',
		) );
		add_action( 'wp_head', array(
			$this,
			'addShortcodesCss',
		), 1000 );
		add_action( 'wp_head', array(
			$this,
			'addNoScript',
		), 1000 );
		add_action( 'template_redirect', array(
			$this,
			'frontJsRegister',
		) );
		add_filter( 'the_content', array(
			$this,
			'fixPContent',
		), 11 );
	}

	/**
	 * Load admin required modules and elements
	 *
	 * @since  4.2
	 */
	public function initAdmin() {
		do_action( 'vc_build_admin_page' );
		// editors actions:
		$this->editForm()->init();
		$this->templatesPanelEditor()->init();
		$this->shared_templates->init();

		// plugins list page actions links
		add_filter( 'plugin_action_links', array(
			$this,
			'pluginActionLinks',
		), 10, 2 );
	}

	/**
	 * Setter for edit form.
	 * @param Vc_Shortcode_Edit_Form $form
	 * @since 4.2
	 *
	 */
	public function setEditForm( Vc_Shortcode_Edit_Form $form ) {
		$this->shortcode_edit_form = $form;
	}

	/**
	 * Get Shortcodes Edit form object.
	 *
	 * @return Vc_Shortcode_Edit_Form
	 * @since  4.2
	 * @see    Vc_Shortcode_Edit_Form::__construct
	 */
	public function editForm() {
		return $this->shortcode_edit_form;
	}

	/**
	 * Setter for Templates editor.
	 * @param Vc_Templates_Panel_Editor $editor
	 * @since 4.4
	 *
	 */
	public function setTemplatesPanelEditor( Vc_Templates_Panel_Editor $editor ) {
		$this->templates_panel_editor = $editor;
	}

	/**
	 * Setter for Preset editor.
	 * @param Vc_Preset_Panel_Editor $editor
	 * @since 5.2
	 *
	 */
	public function setPresetPanelEditor( Vc_Preset_Panel_Editor $editor ) {
		$this->preset_panel_editor = $editor;
	}

	/**
	 * Get templates manager.
	 * @return bool|Vc_Templates_Panel_Editor
	 * @since  4.4
	 * @see    Vc_Templates_Panel_Editor::__construct
	 */
	public function templatesPanelEditor() {
		return $this->templates_panel_editor;
	}

	/**
	 * Get preset manager.
	 * @return bool|Vc_Preset_Panel_Editor
	 * @since  5.2
	 * @see    Vc_Preset_Panel_Editor::__construct
	 */
	public function presetPanelEditor() {
		return $this->preset_panel_editor;
	}

	/**
	 * Get shortcode class instance.
	 *
	 * @param string $tag
	 *
	 * @return Vc_Shortcodes_Manager|null
	 * @see    WPBakeryShortCodeFishBones
	 * @since  4.2
	 *
	 */
	public function getShortCode( $tag ) {
		return Vc_Shortcodes_Manager::getInstance()->setTag( $tag );
	}

	/**
	 * Remove shortcode from shortcodes list of VC.
	 *
	 * @param $tag - shortcode tag
	 * @since  4.2
	 *
	 */
	public function removeShortCode( $tag ) {
		remove_shortcode( $tag );
	}

	/**
	 * Set or modify new settings for shortcode.
	 *
	 * This function widely used by WPBMap class methods to modify shortcodes mapping
	 *
	 * @param $tag
	 * @param $name
	 * @param $value
	 * @throws \Exception
	 * @since 4.3
	 */
	public function updateShortcodeSetting( $tag, $name, $value ) {
		Vc_Shortcodes_Manager::getInstance()->getElementClass( $tag )->setSettings( $name, $value );
	}

	/**
	 * Build custom css styles for page from shortcodes attributes created by VC editors.
	 *
	 * Called by save method, which is hooked by edit_post action.
	 * Function creates metadata for post with the key '_wpb_shortcodes_custom_css'
	 * and value as css string, which will be added to the footer of the page.
	 *
	 * @param $id
	 * @throws \Exception
	 * @since  4.2
	 * @deprecated 7.6 Use buildShortcodesCss()
	 */
	public function buildShortcodesCustomCss( $id ) {
		_deprecated_function( 'Vc_Base::buildShortcodesCustomCss()', '7.6', 'Vc_Base::buildShortcodesCss()' );
		$this->buildShortcodesCss( $id, 'custom' );
	}

	/**
	 * Parse shortcodes custom css string.
	 *
	 * @param $content
	 *
	 * @return string
	 * @throws \Exception
	 * @see    WPBakeryCssEditor
	 * @since  4.2
	 * @deprecated 7.6 Use parseShortcodesCss()
	 */
	public function parseShortcodesCustomCss( $content ) {
		_deprecated_function( 'Vc_Base::parseShortcodesCustomCss()', '7.6', 'Vc_Base::parseShortcodesCss()' );
		return $this->parseShortcodesCss( $content, 'custom' );
	}

	/**
	 * Builds custom css styles for page from shortcodes attributes created by VC editors,
	 * builds default css styles from shortcodes. Based on type custom or default.
	 *
	 * Called by save method, which is hooked by edit_post action.
	 * Function creates metadata for post with the
	 * '_wpb_shortcodes_custom_css' and '_wpb_shortcodes_default_css' keys
	 * and value as css string, which will be added to the footer of the page.
	 *
	 * @param $id
	 * @param $type
	 * @throws \Exception
	 * @since  7.6
	 */
	public function buildShortcodesCss( $id, $type ) {
		if ( 'dopreview' === vc_post_param( 'wp-preview' ) && wp_revisions_enabled( get_post( $id ) ) ) {
			$latest_revision = wp_get_post_revisions( $id );
			if ( ! empty( $latest_revision ) ) {
				$array_values = array_values( $latest_revision );
				$id = $array_values[0]->ID;
			}
		}

		$post = get_post( $id );
		/**
		 * vc_filter: vc_base_build_shortcodes_custom_css
		 * @since 4.4
		 */
		$css = apply_filters( 'vc_base_build_shortcodes_' . esc_html__( $type ) . '_css', $this->parseShortcodesCss( $post->post_content, $type ), $id );

		if ( empty( $css ) ) {
			delete_metadata( 'post', $id, '_wpb_shortcodes_' . esc_html__( $type ) . '_css' );
		} else {
			update_metadata( 'post', $id, '_wpb_shortcodes_' . esc_html__( $type ) . '_css', $css );
			update_metadata( 'post', $id, '_wpb_shortcodes_' . esc_html__( $type ) . '_css_updated', true );
		}
	}

	/**
	 * Parse shortcodes css string.
	 *
	 * This function creates css string from shortcodes attributes like 'css_editor'.
	 *
	 * @param $content
	 *
	 * @return string
	 * @throws \Exception
	 * @see    WPBakeryCssEditor
	 * @since  7.6
	 */
	public function parseShortcodesCss( $content, $type ) {
		$css = '';
		// Following RegExp pattern only applies for when custom CSS is set
		if ( ! preg_match( '/\s*(\.[^\{]+)\s*\{\s*([^\}]+)\s*\}\s*/', $content ) && 'custom' == $type ) {
			return $css;
		}
		WPBMap::addAllMappedShortcodes();
		preg_match_all( '/' . get_shortcode_regex() . '/', $content, $shortcodes );
		$tag_list = $shortcodes[2];
		foreach ( $tag_list as $index => $tag ) {
			$shortcode = WPBMap::getShortCode( $tag );
			if ( empty( $shortcode['params'] ) ) {
				continue;
			}

			$shortcode_css_list = $shortcodes[3];
			$attr_array = shortcode_parse_atts( trim( $shortcode_css_list[ $index ] ) );
			$css = $this->get_css_from_shortcode_params( $type, $shortcode, $attr_array, $css );
		}

		$css_lib = [];
		$shortcode_inner_content_list = $shortcodes[5];
		foreach ( $shortcode_inner_content_list as $shortcode_content ) {
			$shortcode_css = $this->parseShortcodesCss( $shortcode_content, $type );

			if ( in_array( $shortcode_css, $css_lib ) ) {
				continue;
			}

			$css .= $shortcode_css;

			$css_lib[] = $shortcode_css;
		}

		return $css;
	}

	/**
	 * Get css from shortcode params.
	 *
	 * @since 7.6
	 *
	 * @param string $type
	 * @param array $shortcode
	 * @param array $attr
	 * @param string $css
	 * @return string
	 */
	public function get_css_from_shortcode_params( $type, $shortcode, $attr, $css ) {
		foreach ( $shortcode['params'] as $param ) {
			if ( $this->is_custom_css_type( $type, $param, $attr ) ) {
				$css .= $attr[ $param['param_name'] ];
			} elseif ( $this->is_default_css_type( $type, $param, $shortcode ) ) {
				$do_values = $param['value'];
				$css .= '.' . esc_attr( $shortcode['element_default_class'] ) . '{';
				foreach ( $do_values as $key => $default_do_value ) {
					$css .= esc_attr( $key ) . ':' . esc_attr( $default_do_value ) . ';';
				}
				if ( '' !== $css ) {
					$css = $css . '}';
				}
			}
		}

		return $css;
	}

	/**
	 * Check if CSS type is custom and param type is css_editor
	 *
	 * @since  7.6
	 * @return bool
	 */
	public function is_custom_css_type( $type, $param, $attr_array ) {
		return 'custom' == $type &&
				isset( $param['type'] ) &&
				'css_editor' === $param['type'] &&
				isset( $attr_array[ $param['param_name'] ] );
	}

	/**
	 * Check if CSS type is default and 'element_default_class' property is set
	 *
	 * @since  7.6
	 * @return bool
	 */
	public function is_default_css_type( $type, $param, $shortcode ) {
		return  'default' == $type &&
				isset( $param['param_name'] ) &&
				'css' === $param['param_name'] &&
				isset( $param['value'] ) &&
				isset( $shortcode['element_default_class'] ) &&
				'wpb_content_element' !== $shortcode['element_default_class'];
	}

	/**
	 * Hooked class method by wp_footer WP action to output shortcodes css editor settings from page meta data.
	 *
	 * Method gets post meta value for page by key '_wpb_shortcodes_custom_css' and if it is not empty
	 * outputs css string wrapped into style tag.
	 *
	 * @param int $id
	 *
	 * @since  4.2
	 * @access public
	 * @deprecated 7.6
	 */
	public function addShortcodesCustomCss( $id = null ) {
		_deprecated_function( __METHOD__, '7.6', 'Vc_Base::addShortcodesCss' );
		$this->addShortcodesCss( $id );
	}

	/**
	 * Add css styles for current page and elements design options added w\ editor.
	 *
	 * @depreacted 7.7
	 */
	public function addFrontCss( $id = null ) {
		_deprecated_function( __METHOD__, '7.6', 'Vc_Base::addShortcodesCss' );

		$this->addPageCustomCss( $id );
		$this->addShortcodesCss( $id );
	}

	/**
	 * Hooked class method by wp_head WP action to output post custom css.
	 *
	 * Method gets post meta value for page by key '_wpb_post_custom_css' and if it is not empty
	 * outputs css string wrapped into style tag.
	 *
	 * @param int $id
	 * @since  4.2
	 * @deprecated 7.7
	 */
	public function addPageCustomCss( $id = null ) {
		_deprecated_function( __METHOD__, '7.7', "vc_modules_manager()->get_module( 'vc-custom-css' )->output_custom_css_to_page()" );
		if ( vc_modules_manager()->is_module_on( 'vc-custom-css' ) ) {
			vc_modules_manager()->get_module( 'vc-custom-css' )->output_custom_css_to_page();
		}
	}

	/**
	 * Get the custom and default (Design Options - css_editor parameter) values of shortcodes,
	 * parse the values, compile them into a CSS string and output it inside the respective style tag.
	 *
	 * @param int $id
	 *
	 * @since  7.6
	 */
	public function addShortcodesCss( $id = null ) {
		if ( ! $id && is_singular() ) {
			$id = get_the_ID();
		}
		// if is woocommerce shop page
		if ( ! $id && function_exists( 'is_shop' ) && is_shop() ) {
			$id = get_option( 'woocommerce_shop_page_id' );
		}

		if ( ! $id ) {
			return;
		}

		if ( 'true' === vc_get_param( 'preview' ) && wp_revisions_enabled( get_post( $id ) ) ) {
			$latest_revision = wp_get_post_revisions( $id );
			if ( ! empty( $latest_revision ) ) {
				$array_values = array_values( $latest_revision );
				$id = $array_values[0]->ID;
			}
		}

		$types = [
			'default',
			'custom',
		];

		foreach ( $types as $type ) {
			$shortcodes_css = $this->get_shortcodes_css( $id, $type );
			if ( ! empty( $shortcodes_css ) ) {
				$shortcodes_css = wp_strip_all_tags( $shortcodes_css );
				echo '<style type="text/css" data-type="vc_shortcodes-' . esc_attr( $type ) . '-css">';
				echo $shortcodes_css;
				echo '</style>';
			}
		}
	}

	/**
	 * Get custom css of all shortcodes for particular post.
	 *
	 * @param int $id
	 * @return mixed
	 *
	 * @since  6.2
	 * @access public
	 * @deprecated 7.6 Use get_shortcodes_css()
	 */
	public function get_shortcodes_custom_css( $id ) {
		_deprecated_function( 'Vc_Base::get_shortcodes_custom_css()', '7.6', 'Vc_Base::get_shortcodes_css()' );
		return $this->get_shortcodes_css( $id, 'custom' );
	}

	/**
	 * Get the CSS of all the shortcodes for particular post based on parameter (custom or default).
	 *
	 * @param int $id
	 * @param string $type
	 * @return mixed
	 *
	 * @since  7.6
	 */
	public function get_shortcodes_css( $id, $type ) {
		$is_updated = get_metadata( 'post', $id, '_wpb_shortcodes_' . esc_html__( $type ) . '_css_updated', true );

		if ( empty( $is_updated ) ) {
			$this->buildShortcodesCss( $id, $type );
		}

		$shortcodes_css = get_metadata( 'post', $id, '_wpb_shortcodes_' . esc_html__( $type ) .'_css', true );

		return apply_filters( 'vc_shortcodes_'. esc_html__( $type ) . '_css', $shortcodes_css, $id );
	}

	public function addNoScript() {
		$custom_tag = 'style';
		$second_tag = 'noscript';
		echo '<' . esc_attr( $second_tag ) . '>';
		echo '<' . esc_attr( $custom_tag ) . '>';
		echo ' .wpb_animate_when_almost_visible { opacity: 1; }';
		echo '</' . esc_attr( $custom_tag ) . '>';
		echo '</' . esc_attr( $second_tag ) . '>';
	}

	/**
	 * Register front css styles.
	 *
	 * Calls wp_register_style for required css libraries files.
	 *
	 * @since  3.1
	 */
	public function frontCss() {
		wp_register_style( 'wpb_flexslider', vc_asset_url( 'lib/vendor/node_modules/flexslider/flexslider.min.css' ), array(), WPB_VC_VERSION );
		wp_register_style( 'nivo-slider-css', vc_asset_url( 'lib/vendor/node_modules/nivo-slider/nivo-slider.min.css' ), array(), WPB_VC_VERSION );
		wp_register_style( 'nivo-slider-theme', vc_asset_url( 'lib/vendor/node_modules/nivo-slider/themes/default/default.min.css' ), array( 'nivo-slider-css' ), WPB_VC_VERSION );
		wp_register_style( 'prettyphoto', vc_asset_url( 'lib/vendor/prettyphoto/css/prettyPhoto.min.css' ), array(), WPB_VC_VERSION );
		wp_register_style( 'isotope-css', vc_asset_url( 'css/lib/isotope/isotope.min.css' ), array(), WPB_VC_VERSION );
		wp_register_style( 'vc_font_awesome_5_shims', vc_asset_url( 'lib/vendor/node_modules/@fortawesome/fontawesome-free/css/v4-shims.min.css' ), array(), WPB_VC_VERSION );
		wp_register_style( 'vc_font_awesome_5', vc_asset_url( 'lib/vendor/node_modules/@fortawesome/fontawesome-free/css/all.min.css' ), array( 'vc_font_awesome_5_shims' ), WPB_VC_VERSION );
		wp_register_style( 'vc_animate-css', vc_asset_url( 'lib/vendor/node_modules/animate.css/animate.min.css' ), array(), WPB_VC_VERSION );
		wp_register_style( 'lightbox2', vc_asset_url( 'lib/vendor/node_modules/lightbox2/dist/css/lightbox.min.css' ), array(), WPB_VC_VERSION );
		$front_css_file = vc_asset_url( 'css/js_composer.min.css' );

		wp_register_style( 'js_composer_front', $front_css_file, array(), WPB_VC_VERSION );

		add_action( 'wp_enqueue_scripts', array(
			$this,
			'enqueueStyle',
		) );

		/**
		 * @since 4.4
		 */
		do_action( 'vc_base_register_front_css' );
	}

	/**
	 * Enqueue base css class for VC elements and enqueue custom css if exists.
	 */
	public function enqueueStyle() {
		$post = get_post();
		if ( $post && strpos( $post->post_content, '[vc_row' ) !== false ) {
			wp_enqueue_style( 'js_composer_front' );
		}
	}

	/**
	 * Register front javascript libs.
	 *
	 * Calls wp_register_script for required css libraries files.
	 *
	 * @since  3.1
	 */
	public function frontJsRegister() {
		wp_register_script( 'prettyphoto', vc_asset_url( 'lib/vendor/prettyphoto/js/jquery.prettyPhoto.min.js' ), array( 'jquery-core' ), WPB_VC_VERSION, true );
		wp_register_script( 'lightbox2', vc_asset_url( 'lib/vendor/node_modules/lightbox2/dist/js/lightbox.min.js' ), array( 'jquery-core' ), WPB_VC_VERSION, true );
		wp_register_script( 'vc_waypoints', vc_asset_url( 'lib/vc/vc_waypoints/vc-waypoints.min.js' ), array( 'jquery-core' ), WPB_VC_VERSION, true );

		// @deprecated used in old tabs
		wp_register_script( 'jquery_ui_tabs_rotate', vc_asset_url( 'lib/vendor/jquery-ui-tabs-rotate/jquery-ui-tabs-rotate.min.js' ), array(
			'jquery-core',
			'jquery-ui-tabs',
		), WPB_VC_VERSION, true );

		// used in vc_gallery, old grid
		wp_register_script( 'isotope', vc_asset_url( 'lib/vendor/node_modules/isotope-layout/dist/isotope.pkgd.min.js' ), array( 'jquery-core' ), WPB_VC_VERSION, true );

		wp_register_script( 'twbs-pagination', vc_asset_url( 'lib/vendor/node_modules/twbs-pagination/jquery.twbsPagination.min.js' ), array( 'jquery-core' ), WPB_VC_VERSION, true );
		wp_register_script( 'nivo-slider', vc_asset_url( 'lib/vendor/node_modules/nivo-slider/jquery.nivo.slider.pack.js' ), array( 'jquery-core' ), WPB_VC_VERSION, true );
		wp_register_script( 'wpb_flexslider', vc_asset_url( 'lib/vendor/node_modules/flexslider/jquery.flexslider-min.js' ), array( 'jquery-core' ), WPB_VC_VERSION, true );
		wp_register_script( 'wpb_composer_front_js', vc_asset_url( 'js/dist/js_composer_front.min.js' ), array( 'jquery-core' ), WPB_VC_VERSION, true );

		/**
		 * @since 4.4
		 */
		do_action( 'vc_base_register_front_js' );
	}

	/**
	 * Register admin javascript libs.
	 *
	 * Calls wp_register_script for required css libraries files for Admin dashboard.
	 *
	 * @since  3.1
	 * vc_filter: vc_i18n_locale_composer_js_view, since 4.4 - override localization for js
	 */
	public function registerAdminJavascript() {
		/**
		 * @since 4.4
		 */
		do_action( 'vc_base_register_admin_js' );

	}

	/**
	 * Register admin css styles.
	 *
	 * Calls wp_register_style for required css libraries files for admin dashboard.
	 *
	 * @since  3.1
	 */
	public function registerAdminCss() {
		/**
		 * @since 4.4
		 */
		do_action( 'vc_base_register_admin_css' );
	}

	/**
	 * Add Settings link in plugin's page
	 * @param $links
	 * @param $file
	 *
	 * @return array
	 * @throws \Exception
	 * @since 4.2
	 */
	public function pluginActionLinks( $links, $file ) {
		if ( plugin_basename( vc_path_dir( 'APP_DIR', '/js_composer.php' ) ) === $file ) {
			$title = esc_html__( 'WPBakery Page Builder Settings', 'js_composer' );
			$html = esc_html__( 'Settings', 'js_composer' );
			if ( ! vc_user_access()->part( 'settings' )->can( 'vc-general-tab' )->get() ) {
				$title = esc_html__( 'About WPBakery Page Builder', 'js_composer' );
				$html = esc_html__( 'About', 'js_composer' );
			}
			$link = '<a title="' . esc_attr( $title ) . '" href="' . esc_url( $this->getSettingsPageLink() ) . '">' . $html . '</a>';
			array_unshift( $links, $link ); // Add to top
		}

		return $links;
	}

	/**
	 * Get settings page link
	 * @return string url to settings page
	 * @throws \Exception
	 * @since 4.2
	 */
	public function getSettingsPageLink() {
		$page = 'vc-general';
		if ( ! vc_user_access()->part( 'settings' )->can( 'vc-general-tab' )->get() ) {
			$page = 'vc-welcome';
		}

		return add_query_arg( array( 'page' => $page ), admin_url( 'admin.php' ) );
	}

	/**
	 * Hooked class method by wp_head WP action.
	 * @since  4.2
	 */
	public function addMetaData() {
		echo '<meta name="generator" content="Powered by WPBakery Page Builder - drag and drop page builder for WordPress."/>' . "\n";
	}

	/**
	 * Method adds css class to body tag.
	 *
	 * Hooked class method by body_class WP filter. Method adds custom css class to body tag of the page to help
	 * identify and build design specially for VC shortcodes.
	 *
	 * @param $classes
	 *
	 * @return array
	 * @since  4.2
	 *
	 */
	public function bodyClass( $classes ) {
		return js_composer_body_class( $classes );
	}

	/**
	 * Builds excerpt for post from content.
	 *
	 * Hooked class method by the_excerpt WP filter. When user creates content with VC all content is always wrapped by
	 * shortcodes. This methods calls do_shortcode for post's content and then creates a new excerpt.
	 *
	 * @param $output
	 *
	 * @return string
	 * @since  4.2
	 *
	 */
	public function excerptFilter( $output ) {
		global $post;
		if ( empty( $output ) && ! empty( $post->post_content ) ) {
			$text = wp_strip_all_tags( do_shortcode( $post->post_content ) );
			$excerpt_length = apply_filters( 'excerpt_length', 55 );
			$excerpt_more = apply_filters( 'excerpt_more', ' [...]' );
			$text = wp_trim_words( $text, $excerpt_length, $excerpt_more );

			return $text;
		}

		return $output;
	}

	/**
	 * Remove unwanted wrapping with p for content.
	 *
	 * Hooked by 'the_content' filter.
	 * @param null $content
	 *
	 * @return string|null
	 * @since 4.2
	 *
	 */
	public function fixPContent( $content = null ) {
		if ( $content ) {
			$s = array(
				'/' . preg_quote( '</div>', '/' ) . '[\s\n\f]*' . preg_quote( '</p>', '/' ) . '/i',
				'/' . preg_quote( '<p>', '/' ) . '[\s\n\f]*' . preg_quote( '<div ', '/' ) . '/i',
				'/' . preg_quote( '<p>', '/' ) . '[\s\n\f]*' . preg_quote( '<section ', '/' ) . '/i',
				'/' . preg_quote( '</section>', '/' ) . '[\s\n\f]*' . preg_quote( '</p>', '/' ) . '/i',
			);
			$r = array(
				'</div>',
				'<div ',
				'<section ',
				'</section>',
			);
			$content = preg_replace( $s, $r, $content );

			// if content contains vc_row for a page view or
			// vc_welcome for a frontend editor
			// then wrap with '<div>'
			if ( preg_match( '/vc_row/', $content ) || preg_match( '/vc_welcome/', $content ) ) {
				$content = '<div class="wpb-content-wrapper">' . $content . '</div>';
			}

			return $content;
		}

		return null;
	}

	/**
	 * Get array of string for locale.
	 *
	 * @return array
	 * @since 4.7
	 *
	 */
	public function getEditorsLocale() {
		/**
		 * Filter for VC editor locale.
		 * @since 7.8
		 * return array
		 */
		return apply_filters( 'vc_get_editor_locale', array(
			'add_remove_picture' => esc_html__( 'Add/remove picture', 'js_composer' ),
			'finish_adding_text' => esc_html__( 'Finish Adding Images', 'js_composer' ),
			'add_image' => esc_html__( 'Add Image', 'js_composer' ),
			'add_images' => esc_html__( 'Add Images', 'js_composer' ),
			'settings' => esc_html__( 'Settings', 'js_composer' ),
			'main_button_title' => esc_html__( 'WPBakery Page Builder', 'js_composer' ),
			'main_button_title_backend_editor' => esc_html__( 'Backend Editor', 'js_composer' ),
			'main_button_title_frontend_editor' => esc_html__( 'Frontend Editor', 'js_composer' ),
			'main_button_title_revert' => esc_html__( 'Classic Mode', 'js_composer' ),
			'main_button_title_gutenberg' => esc_html__( 'Gutenberg Editor', 'js_composer' ),
			'please_enter_templates_name' => esc_html__( 'Enter template name you want to save.', 'js_composer' ),
			'confirm_deleting_template' => esc_html__( 'Confirm deleting "{template_name}" template, press Cancel to leave. This action cannot be undone.', 'js_composer' ),
			'press_ok_to_delete_section' => esc_html__( 'Press OK to delete section, Cancel to leave', 'js_composer' ),
			'drag_drop_me_in_column' => esc_html__( 'Drag and drop me in the column', 'js_composer' ),
			'press_ok_to_delete_tab' => esc_html__( 'Press OK to delete "{tab_name}" tab, Cancel to leave', 'js_composer' ),
			'slide' => esc_html__( 'Slide', 'js_composer' ),
			'tab' => esc_html__( 'Tab', 'js_composer' ),
			'section' => esc_html__( 'Section', 'js_composer' ),
			'please_enter_new_tab_title' => esc_html__( 'Please enter new tab title', 'js_composer' ),
			'press_ok_delete_section' => esc_html__( 'Press OK to delete "{tab_name}" section, Cancel to leave', 'js_composer' ),
			'section_default_title' => esc_html__( 'Section', 'js_composer' ),
			'please_enter_section_title' => esc_html__( 'Please enter new section title', 'js_composer' ),
			'error_please_try_again' => esc_html__( 'Error. Please try again.', 'js_composer' ),
			'if_close_data_lost' => esc_html__( 'If you close this window all shortcode settings will be lost. Close this window?', 'js_composer' ),
			'header_select_element_type' => esc_html__( 'Select element type', 'js_composer' ),
			'header_media_gallery' => esc_html__( 'Media gallery', 'js_composer' ),
			'header_element_settings' => esc_html__( 'Element settings', 'js_composer' ),
			'add_tab' => esc_html__( 'Add tab', 'js_composer' ),
			'are_you_sure_convert_to_new_version' => esc_html__( 'Are you sure you want to convert to new version?', 'js_composer' ),
			'loading' => esc_html__( 'Loading...', 'js_composer' ),
			// Media editor
			'set_image' => esc_html__( 'Set Image', 'js_composer' ),
			'are_you_sure_reset_css_classes' => esc_html__( 'Are you sure that you want to remove all your data?', 'js_composer' ),
			'loop_frame_title' => esc_html__( 'Loop settings', 'js_composer' ),
			'enter_custom_layout' => esc_html__( 'Custom row layout', 'js_composer' ),
			'wrong_cells_layout' => esc_html__( 'Wrong row layout format! Example: 1/2 + 1/2 or span6 + span6.', 'js_composer' ),
			'row_background_color' => esc_html__( 'Row background color', 'js_composer' ),
			'row_background_image' => esc_html__( 'Row background image', 'js_composer' ),
			'column_background_color' => esc_html__( 'Column background color', 'js_composer' ),
			'column_background_image' => esc_html__( 'Column background image', 'js_composer' ),
			'guides_on' => esc_html__( 'Guides ON', 'js_composer' ),
			'guides_off' => esc_html__( 'Guides OFF', 'js_composer' ),
			'template_save' => esc_html__( 'New template successfully saved.', 'js_composer' ),
			'template_added' => esc_html__( 'Template added to the page.', 'js_composer' ),
			'template_added_with_id' => esc_html__( 'Template added to the page. Template has ID attributes, make sure that they are not used more than once on the same page.', 'js_composer' ),
			'template_removed' => esc_html__( 'Template successfully removed.', 'js_composer' ),
			'template_is_empty' => esc_html__( 'Template is empty: There is no content to be saved as a template.', 'js_composer' ),
			'template_save_error' => esc_html__( 'Error while saving template.', 'js_composer' ),
			'page_settings_updated' => esc_html__( 'Page settings updated!', 'js_composer' ),
			'update_all' => esc_html__( 'Update all', 'js_composer' ),
			'confirm_to_leave' => esc_html__( 'The changes you made will be lost if you navigate away from this page.', 'js_composer' ),
			'inline_element_saved' => esc_html__( '%s saved!', 'js_composer' ),
			'inline_element_deleted' => esc_html__( '%s deleted!', 'js_composer' ),
            // phpcs:ignore
			'inline_element_cloned' => sprintf( __( '%%1$s cloned. %2$sEdit now?%s', 'js_composer' ), '<a href="#" class="vc_edit-cloned" data-model-id="%s">', '</a>' ),
			'gfonts_loading_google_font_failed' => esc_html__( 'Loading Google Font failed', 'js_composer' ),
			'gfonts_loading_google_font' => esc_html__( 'Loading Font...', 'js_composer' ),
			'gfonts_unable_to_load_google_fonts' => esc_html__( 'Unable to load Google Fonts', 'js_composer' ),
			'no_title_parenthesis' => sprintf( '(%s)', esc_html__( 'no title', 'js_composer' ) ),
			'error_while_saving_image_filtered' => esc_html__( 'Error while applying filter to the image. Check your server and memory settings.', 'js_composer' ),
			'ui_saved' => sprintf( '<i class="vc-composer-icon vc-c-icon-check"></i> %s', esc_html__( 'Saved!', 'js_composer' ) ),
			'ui_danger' => sprintf( '<i class="vc-composer-icon vc-c-icon-close"></i> %s', esc_html__( 'Failed to Save!', 'js_composer' ) ),
			'delete_preset_confirmation' => esc_html__( 'You are about to delete this preset. This action can not be undone.', 'js_composer' ),
			'ui_template_downloaded' => esc_html__( 'Downloaded', 'js_composer' ),
			'ui_template_update' => esc_html__( 'Update', 'js_composer' ),
			'ui_templates_failed_to_download' => esc_html__( 'Failed to download template', 'js_composer' ),
			'preset_removed' => esc_html__( 'Element successfully removed.', 'js_composer' ),
			'vc_successfully_updated' => esc_html__( 'Successfully updated!', 'js_composer' ),
			'gutenbergDoesntWorkProperly' => esc_html__( 'Gutenberg plugin doesn\'t work properly. Please check Gutenberg plugin.', 'js_composer' ),
			'unfiltered_html_access' => esc_html__( 'Custom HTML is disabled for your user role. Please contact your site Administrator to change your capabilities.', 'js_composer' ),
			'not_editable_post' => sprintf( '%s %s %s', esc_html__( 'This', 'js_composer' ), get_post_type() ? get_post_type() : 'post', esc_html__( 'can not be edited with WPBakery since it is missing a WordPress default content area.', 'js_composer' ) ),
			'generate' => esc_html__( 'Generate', 'js_composer' ),
			'regenerate' => esc_html__( 'Regenerate', 'js_composer' ),
			'problems' => esc_html__( 'Problems', 'js_composer' ),
			'warnings' => esc_html__( 'Warnings', 'js_composer' ),
			'success' => esc_html__( 'Success', 'js_composer' ),
			'goodJob' => esc_html__( 'Good job!', 'js_composer' ),
			'wellDone' => esc_html__( 'Well done!', 'js_composer' ),
			'greatWork' => esc_html__( 'Great work!', 'js_composer' ),
			'great' => esc_html__( 'Great!', 'js_composer' ),
			'internalLinks' => esc_html__( 'Internal links', 'js_composer' ),
			'outboundLinks' => esc_html__( 'Outbound links', 'js_composer' ),
			'noOutboundLinks' => esc_html__( 'No outbound links appear in this page.', 'js_composer' ),
			'noInternalLinks' => esc_html__( 'No internal links appear in this page, make sure to add some!', 'js_composer' ),
			'images' => esc_html__( 'Images', 'js_composer' ),
			'noImages' => esc_html__( 'No images appear on this page. Add some!', 'js_composer' ),
			'focusKeywordTitle' => esc_html__( 'Focus keyphrase', 'js_composer' ),
			'noFocusKeyword' => esc_html__( 'No focus keyphrase was set for this page.', 'js_composer' ),
			'seoTitle' => esc_html__( 'SEO title width', 'js_composer' ),
			'seoTitleWidthTooLong' => esc_html__( 'The SEO title is wider than the viewable limit. Try to make it shorter.', 'js_composer' ),
			'textLength' => esc_html__( 'Text Length', 'js_composer' ),
			'textLengthLess' => esc_html__( 'The text contains %1$s words. This is %2$s the recommended minimum of 300 words.', 'js_composer' ),
			'textLengthSuccess' => esc_html__( 'The text contains %s words. Good job!', 'js_composer' ),
			'seoTitleEmpty' => esc_html__( 'Please create an SEO title.', 'js_composer' ),
			'seoDescription' => esc_html__( 'Meta description length', 'js_composer' ),
			'seoDescriptionEmpty' => esc_html__( 'No meta description has been specified. Search engines will display copy from the page instead. Make sure to write one!', 'js_composer' ),
			'seoDescriptionTooShort' => esc_html__( 'The meta description is too short (under 120 characters). Up to 156 characters are available. Use the space!', 'js_composer' ),
			'seoDescriptionTooLong' => esc_html__( 'The meta description is over 156 characters. To ensure the entire description will be visible, you should reduce the length!', 'js_composer' ),
			'keyphraseInTitleText' => esc_html__( 'Keyphrase in SEO title', 'js_composer' ),
			'keyphraseInTitleWarn' => esc_html__( 'The exact match of the focus keyphrase appears in the SEO title, but not at the beginning.', 'js_composer' ),
			'keyphraseInTitleEmpty' => esc_html__( 'Not all the words from your keyphrase "%1$s" appear in the SEO title.', 'js_composer' ),
			'keyphraseInDescriptionText' => esc_html__( 'Keyphrase in meta description', 'js_composer' ),
			'keyphraseInDescriptionEmpty' => esc_html__( 'The meta description has been specified, but it does not contain the keyphrase', 'js_composer' ),
			'keyphraseInDescriptionSuccess' => esc_html__( 'Keyphrase appears in the meta description. Well done!', 'js_composer' ),
			'keyphraseInSlug' => esc_html__( 'Keyphrase in slug', 'js_composer' ),
			'keyphraseInSlugProblem' => esc_html__( '(Part of) your keyphrase does not appear in the slug.', 'js_composer' ),
			'imageKeyphrase' => esc_html__( 'Image Keyphrase', 'js_composer' ),
			'imageKeyphraseTooMuch' => esc_html__( 'Out of %1$s images on this page, %2$s have alt attributes with words from your keyphrase or synonyms. That\'s a bit much. Only include the keyphrase or its synonyms when it really fits the image.', 'js_composer' ),
			'imageKeyphraseNotEnough' => esc_html__( 'Out of %1$s images on this page, only %2$s has an alt attribute that reflects the topic of your text. Add your keyphrase or synonyms to the alt tags of more relevant images!', 'js_composer' ),
			'imageKeyphraseMissing' => esc_html__( 'Images on this page do not have alt attributes with at least half of the words from your keyphrase.', 'js_composer' ),
			'keyphraseInIntroductionText' => esc_html__( 'Keyphrase in introduction', 'js_composer' ),
			'keyphraseInIntroductionEmpty' => esc_html__( 'Your keyphrase do not appear in the first paragraph.', 'js_composer' ),
			'keyphraseDensity' => esc_html__( 'Keyphrase density', 'js_composer' ),
			'keyphraseDensitySuccess' => esc_html__( 'The keyphrase was found %1$s times. This is great!', 'js_composer' ),
			'keyphraseDensityNotEnough' => esc_html__( 'The keyphrase was found %1$s time. That\'s less than the recommended minimum of %2$s times for a text of this length.', 'js_composer' ),
			'keyphraseDensityTooMuch' => esc_html__( 'The keyphrase was found %1$s times. That\'s way more than the recommended maximum of %2$s times for a text of this length.', 'js_composer' ),
			'consecutiveSentences' => esc_html__( 'Consecutive sentences', 'js_composer' ),
			'consecutiveSentencesSuccess' => esc_html__( 'There is enough variety in your sentences. That\'s great!', 'js_composer' ),
			'consecutiveSentencesFail' => esc_html__( 'The text contains %1$s consecutive sentences starting with the same word.', 'js_composer' ),
			'passiveVoice' => esc_html__( 'Passive voice', 'js_composer' ),
			'passiveVoiceError' => esc_html__( '%s of the sentences contain passive voice, which is more than the recommended maximum of 10%', 'js_composer' ),
			'passiveVoiceSuccess' => esc_html__( 'You\'re using enough active voice. That\'s great!', 'js_composer' ),
			'paragraphLength' => esc_html__( 'Paragraph length', 'js_composer' ),
			'paragraphLengthError' => esc_html__( '%s of the paragraphs contains more than the recommended maximum of 150 words.', 'js_composer' ),
			'paragraphLengthSuccess' => esc_html__( 'None of the paragraphs are too long. Great job!', 'js_composer' ),
			'sentenceLength' => esc_html__( 'Sentence length', 'js_composer' ),
			'sentenceLengthError' => esc_html__( '%s%% of the sentences contain more than 20 words, which is more than the recommended maximum of 25%.', 'js_composer' ),
			'subheadingDistribution' => esc_html__( 'Subheading distribution', 'js_composer' ),
			'subheadingDistributionFail' => esc_html__( 'You are not using any subheadings, although your text is rather long. Try and add some subheadings.', 'js_composer' ),
			'subheadingDistributionWarn' => esc_html__( '%s section of your text is longer than 300 words and is not separated by any subheadings. Add subheadings to improve readability.', 'js_composer' ),
			'previouslyUsedKeyphrase' => esc_html__( 'Previously used keyphrase', 'js_composer' ),
			'previouslyUsedKeyphraseSuccess' => esc_html__( 'You\'ve not used this keyphrase before, very good.', 'js_composer' ),
			'previouslyUsedKeyphraseWarn' => esc_html__( 'You\'ve used this keyphrase before', 'js_composer' ),
			'copied' => esc_html__( 'Copied', 'js_composer' ),
		));
	}

	/**
	 * Get array of string for jsData.
	 *
	 * @return array
	 * @since 7.9
	 */
	public function getEditorsWpbData() {
		return apply_filters( 'vc_get_editor_wpb_data', [] );
	}
}
