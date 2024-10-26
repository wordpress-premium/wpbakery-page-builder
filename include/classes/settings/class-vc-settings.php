<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Settings page for VC. list of tabs for function composer
 *
 * Settings page for VC creates menu item in admin menu as subpage of Settings section.
 * Settings are build with WP settings API and organized as tabs.
 *
 * List of tabs
 * 1. General Settings - set access rules and allowed content types for editors.
 * 2. Modules Manager - set access to certain plugin functionality.
 * 3. Role Manager - set access rules and allowed content types for editors.
 * 4. Product License - license key activation for automatic VC updates.
 * 5. Design Options - custom color and spacing editor for VC shortcodes elements.
 * 6. Custom CSS - add custom css to your WP pages.
 * 7. Custom JS - add custom css to your WP pages.
 * 8. WPBakery AI - access to AI options.
 * 9. My Shortcodes - automated mapping tool for shortcodes.
 *
 * @link http://codex.wordpress.org/Settings_API WordPress settings API
 * @since 3.4
 */
class Vc_Settings {
	public $tabs;
	public $deactivate;
	public $locale;
	/**
	 * @var string
	 */
	protected $option_group = 'wpb_js_composer_settings';
	/**
	 * @var string
	 */
	protected $page = 'vc_settings';
	/**
	 * @var string
	 */
	public static $field_prefix = 'wpb_js_';
	/**
	 * @var string
	 */
	protected static $notification_name = 'wpb_js_notify_user_about_element_class_names';
	/**
	 * @var
	 */
	protected static $defaults;
	/**
	 * @var
	 */
	protected $composer;

	/**
	 * @var array
	 */
	protected $google_fonts_subsets_default = array( 'latin' );
	/**
	 * @var array
	 */
	protected $google_fonts_subsets = array(
		'latin',
		'vietnamese',
		'cyrillic',
		'latin-ext',
		'greek',
		'cyrillic-ext',
		'greek-ext',
	);

	/**
	 * @var array
	 */
	public $google_fonts_subsets_excluded = array();

	protected $google_fonts_subsets_settings;

	/**
	 * @param string $field_prefix
	 */
	public static function setFieldPrefix( $field_prefix ) {
		self::$field_prefix = $field_prefix;
	}

	/**
	 * @return string
	 */
	public function page() {
		return $this->page;
	}

	/**
	 * @return bool
	 */
	public function isEditorEnabled() {
		global $current_user;
		wp_get_current_user();

		/** @var $settings - get use group access rules */
		$settings = $this->get( 'groups_access_rules' );

		$show = true;
		foreach ( $current_user->roles as $role ) {
			if ( isset( $settings[ $role ]['show'] ) && 'no' === $settings[ $role ]['show'] ) {
				$show = false;
				break;
			}
		}

		return $show;
	}

	public function setTabs() {
		$this->tabs = array();

		if ( $this->showConfigurationTabs() ) {
			$this->tabs['vc-general'] = esc_html__( 'General Settings', 'js_composer' );
			$this->tabs['vc-modules'] = esc_html__( 'Module Manager', 'js_composer' );
		}

		if ( ! vc_is_network_plugin() || ( vc_is_network_plugin() && is_network_admin() ) ) {
			if ( ! vc_is_updater_disabled() && ! wpb_check_wordpress_com_env() ) {
				$this->tabs['vc-updater'] = esc_html__( 'Product License', 'js_composer' );
			}
		}
	}

	/**
	 * @return mixed|void
	 */
	public function getTabs() {
		if ( ! isset( $this->tabs ) ) {
			$this->setTabs();
		}

		return apply_filters( 'vc_settings_tabs', $this->tabs );
	}

	/**
	 * @return bool
	 */
	public function showConfigurationTabs() {
		return ! vc_is_network_plugin() || ! is_network_admin();
	}

	/**
	 * Render
	 *
	 * @param $tab
	 * @throws \Exception
	 */
	public function renderTab( $tab ) {
		require_once vc_path_dir( 'CORE_DIR', 'class-vc-page.php' );

		$tabs = $this->getTabs();
		foreach ( $tabs as $key => $value ) {
			if ( ! vc_user_access()->part( 'settings' )->can( $key . '-tab' )->get() ) {
				unset( $tabs[ $key ] );
			}
		}
		do_action( 'vc-settings-render-tab-' . $tab );
		$page = new Vc_Page();
		$page->setSlug( $tab )->setTitle( isset( $tabs[ $tab ] ) ? $tabs[ $tab ] : '' )->setTemplatePath( apply_filters( 'vc_settings-render-tab-' . $tab, 'pages/vc-settings/tab.php' ) );
		vc_include_template( 'pages/vc-settings/index.php', array(
			'pages' => $tabs,
			'active_page' => $page,
			'vc_settings' => $this,
		) );
	}

	/**
	 * Init settings page && menu item
	 * vc_filter: vc_settings_tabs - hook to override settings tabs
	 */
	public function initAdmin() {
		$this->setTabs();

		add_action( 'update_option_wpb_js_modules', array(
			$this,
			'reset_modules_dependency',
		), 10, 2 );

		add_action( 'add_option_wpb_js_modules', array(
			$this,
			'reset_modules_dependency',
		), 10, 2 );

		$this->set_sections();

		/**
		 * Custom Tabs
		 */
		foreach ( $this->getTabs() as $tab => $title ) {
			do_action( 'vc_settings_tab-' . preg_replace( '/^vc\-/', '', $tab ), $this );
		}

		/**
		 * Tab: Updater
		 */
		$tab = 'updater';
		$this->addSection( $tab );
	}

	/**
	 * Set sections
	 *
	 * @since 7.7
	 */
	public function set_sections() {
		$this->set_general_section();
		$this->set_modules_section();

		/**
		 * Set settings sections tabs.
		 *
		 * @since 7.7
		 */
		do_action( 'vc_settings_set_sections', $this );
	}

	/**
	 * Set general section
	 *
	 * @since 7.7
	 */
	public function set_general_section() {
		$tab = 'general';
		$this->addSection( $tab );

		$this->addField( $tab, esc_html__( 'Disable responsive content elements', 'js_composer' ), 'not_responsive_css', array(
			$this,
			'sanitize_not_responsive_css_callback',
		), array(
			$this,
			'not_responsive_css_field_callback',
		), array(
			'info' => esc_html__( 'Disable content elements from "stacking" one on top other on small media screens (Example: mobile devices).', 'js_composer' ),
		)	 );

		$this->addField( $tab, esc_html__( 'Google fonts subsets', 'js_composer' ), 'google_fonts_subsets', array(
			$this,
			'sanitize_google_fonts_subsets_callback',
		), array(
			$this,
			'google_fonts_subsets_callback',
		), array(
			'info' => esc_html__( 'Select subsets for Google Fonts available to content elements.', 'js_composer' ),
		)	 );

		$this->addField( $tab, esc_html__( 'Local Google Fonts', 'js_composer' ), 'local_google_fonts', array(
			$this,
			'sanitize_local_google_fonts_callback',
		), array(
			$this,
			'local_google_fonts_callback',
		) );
	}

	/**
	 * Set modules section
	 *
	 * @since 7.7
	 */
	public function set_modules_section() {
		$tab = 'modules';

		$this->addField($tab, '', vc_modules_manager()->option_slug, [
			$this,
			'sanitize_modules_callback',
		],  [
			$this,
			'use_modules_callback',
		]);

		$this->addSection( $tab );
	}

	/**
	 * Creates new section.
	 *
	 * @param $tab - tab key name as tab section
	 * @param $title - Human title
	 * @param $callback - function to build section header.
	 */
	public function addSection( $tab, $title = null, $callback = null ) {
		add_settings_section( $this->option_group . '_' . $tab, $title, ( null !== $callback ? $callback : array(
			$this,
			'setting_section_callback_function',
		) ), $this->page . '_' . $tab );
	}

	/**
	 * Create field in section.
	 *
	 * @param $tab
	 * @param $title
	 * @param $field_name
	 * @param $sanitize_callback
	 * @param $field_callback
	 * @param array $args
	 *
	 * @return $this
	 */
	public function addField( $tab, $title, $field_name, $sanitize_callback, $field_callback, $args = array() ) {
		register_setting( $this->option_group . '_' . $tab, self::$field_prefix . $field_name, $sanitize_callback );
		add_settings_field( self::$field_prefix . $field_name, $title, $field_callback, $this->page . '_' . $tab, $this->option_group . '_' . $tab, $args );

		return $this; // chaining
	}

	/**
	 * @param $option_name
	 *
	 * @param bool $defaultValue
	 *
	 * @return mixed
	 */
	public static function get( $option_name, $defaultValue = false ) {
		return get_option( self::$field_prefix . $option_name, $defaultValue );
	}

	/**
	 * @param $option_name
	 * @param $value
	 *
	 * @return bool
	 */
	public static function set( $option_name, $value ) {
		return update_option( self::$field_prefix . $option_name, $value );
	}

	/**
	 * Set up the enqueue for the CSS & JavaScript files.
	 *
	 */
	public function adminLoad() {
		wp_register_script( 'wpb_js_composer_settings', vc_asset_url( 'js/dist/settings.min.js' ), array(), WPB_VC_VERSION, true );
		wp_register_script( 'popper', vc_asset_url( 'lib/vendor/node_modules/@popperjs/core/dist/umd/popper.min.js' ), array(), WPB_VC_VERSION, true );
		wp_enqueue_style( 'js_composer_settings', vc_asset_url( 'css/js_composer_settings.min.css' ), false, WPB_VC_VERSION );
		wp_enqueue_script( 'backbone' );
		wp_enqueue_script( 'shortcode' );
		wp_enqueue_script( 'underscore' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'wpb_js_composer_settings' );
		wp_enqueue_script( 'popper' );

		$this->locale = apply_filters( 'vc_get_settings_locale', array(
			'are_you_sure_reset_css_classes' => esc_html__( 'Are you sure you want to reset to defaults?', 'js_composer' ),
			'are_you_sure_reset_color' => esc_html__( 'Are you sure you want to reset to defaults?', 'js_composer' ),
			'saving' => esc_html__( 'Saving...', 'js_composer' ),
			'save' => esc_html__( 'Save Changes', 'js_composer' ),
			'saved' => esc_html__( 'Design Options successfully saved.', 'js_composer' ),
			'save_error' => esc_html__( 'Design Options could not be saved', 'js_composer' ),
			'form_save_error' => esc_html__( 'Problem with AJAX request execution, check internet connection and try again.', 'js_composer' ),
			'are_you_sure_delete' => esc_html__( 'Are you sure you want to delete this shortcode?', 'js_composer' ),
			'are_you_sure_delete_param' => esc_html__( "Are you sure you want to delete the shortcode's param?", 'js_composer' ),
			'my_shortcodes_category' => esc_html__( 'My shortcodes', 'js_composer' ),
			'error_shortcode_name_is_required' => esc_html__( 'Shortcode name is required.', 'js_composer' ),
			'error_enter_valid_shortcode_tag' => esc_html__( 'Please enter valid shortcode tag.', 'js_composer' ),
			'error_enter_required_fields' => esc_html__( 'Please enter all required fields for params.', 'js_composer' ),
			'new_shortcode_mapped' => esc_html__( 'New shortcode mapped from string!', 'js_composer' ),
			'shortcode_updated' => esc_html__( 'Shortcode updated!', 'js_composer' ),
			'error_content_param_not_manually' => esc_html__( 'Content param can not be added manually, please use checkbox.', 'js_composer' ),
			'error_param_already_exists' => esc_html__( 'Param %s already exists. Param names must be unique.', 'js_composer' ),
			'error_wrong_param_name' => esc_html__( 'Please use only letters, numbers and underscore for param name', 'js_composer' ),
			'error_enter_valid_shortcode' => esc_html__( 'Please enter valid shortcode to parse!', 'js_composer' ),
			'copied' => esc_html__( 'Copied', 'js_composer' ),
		));

		wp_localize_script( 'wpb_js_composer_settings', 'vcData', apply_filters( 'vc_global_js_data', array(
			'version' => WPB_VC_VERSION,
			'debug' => false,
		) ) );
		wp_localize_script( 'wpb_js_composer_settings', 'i18nLocaleSettings', $this->locale );
		$wpb_settings_data = apply_filters( 'vc_get_settings_wpb_data', [] );
		wp_localize_script( 'wpb_js_composer_settings', 'wpbData', $wpb_settings_data );
	}

	/**
	 * Not responsive checkbox callback function
	 */
	public function not_responsive_css_field_callback() {
		$checked = get_option( self::$field_prefix . 'not_responsive_css' );
		if ( empty( $checked ) ) {
			$checked = false;
		}
		?>
		<label>
			<input type="checkbox"<?php echo $checked ? ' checked' : ''; ?> value="1" id="wpb_js_not_responsive_css" name="<?php echo esc_attr( self::$field_prefix . 'not_responsive_css' ); ?>">
			<?php esc_html_e( 'Disable', 'js_composer' ); ?>
		</label>
		<?php
	}

	/**
	 * Modules html settings
	 *
	 * @since 7.7
	 */
	public function use_modules_callback() {
		vc_include_template( 'pages/vc-settings/partials/modules/title.php' );

		$modules_manager = vc_modules_manager();
		$all_modules = $modules_manager->get_all();
		$hidden_value = [];

		foreach ( $all_modules as $module_slug => $module_data ) {
			if ( $modules_manager->get_module_status( $module_slug ) ) {
				$hidden_value[ $module_slug ] = true;
				$module_value = 'checked';
			} else {
				$hidden_value[ $module_slug ] = false;
				$module_value = '';
			}
			vc_include_template(
				'pages/vc-settings/partials/modules/toggle.php',
				[
					'module_data' => $module_data,
					'module_slug' => $module_slug,
					'module_value' => $module_value,
				]
			);
		}

		vc_include_template(
			'pages/vc-settings/partials/modules/hidden-input.php',
			[
				'hidden_value' => $hidden_value,
				'option_name' => $modules_manager->get_option_name(),
			]
		);
	}

	/**
	 * Google fonts subsets callback
	 */
	public function google_fonts_subsets_callback() {
		$pt_array = get_option( self::$field_prefix . 'google_fonts_subsets' );
		$pt_array = $pt_array ? $pt_array : $this->googleFontsSubsets();
		foreach ( $this->getGoogleFontsSubsets() as $pt ) {
			if ( ! in_array( $pt, $this->getGoogleFontsSubsetsExcluded(), true ) ) {
				$checked = ( in_array( $pt, $pt_array, true ) ) ? ' checked' : '';
				?>
				<label>
					<input type="checkbox"<?php echo esc_attr( $checked ); ?> value="<?php echo esc_attr( $pt ); ?>"
						id="wpb_js_gf_subsets_<?php echo esc_attr( $pt ); ?>"
						name="<?php echo esc_attr( self::$field_prefix . 'google_fonts_subsets' ); ?>[]">
					<?php echo esc_html( $pt ); ?>
				</label><br>
				<?php
			}
		}
		?>
		<?php
	}

	public function local_google_fonts_callback() {
		$checked = get_option( self::$field_prefix . 'local_google_fonts' );
		if ( empty( $checked ) ) {
			$checked = false;
		}
		?>
		<label>
			<input type="checkbox"<?php echo $checked ? ' checked' : ''; ?> value="1" id="local_google_fonts" name="<?php echo esc_attr( self::$field_prefix . 'local_google_fonts' ); ?>">
			<?php esc_html_e( 'Enable', 'js_composer' ); ?>
		</label>
		<?php
	}

	/**
	 * Get subsets for google fonts.
	 *
	 * @return array
	 * @since  4.3
	 * @access public
	 */
	public function googleFontsSubsets() {
		if ( ! isset( $this->google_fonts_subsets_settings ) ) {
			$pt_array = vc_settings()->get( 'google_fonts_subsets' );
			$this->google_fonts_subsets_settings = $pt_array ? $pt_array : $this->googleFontsSubsetsDefault();
		}

		return $this->google_fonts_subsets_settings;
	}

	/**
	 * @return array
	 */
	public function googleFontsSubsetsDefault() {
		return $this->google_fonts_subsets_default;
	}

	/**
	 * @return array
	 */
	public function getGoogleFontsSubsets() {
		return $this->google_fonts_subsets;
	}

	/**
	 * @param $subsets
	 *
	 * @return bool
	 */
	public function setGoogleFontsSubsets( $subsets ) {
		if ( is_array( $subsets ) ) {
			$this->google_fonts_subsets = $subsets;

			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getGoogleFontsSubsetsExcluded() {
		return $this->google_fonts_subsets_excluded;
	}

	/**
	 * @param $excluded
	 *
	 * @return bool
	 */
	public function setGoogleFontsSubsetsExcluded( $excluded ) {
		if ( is_array( $excluded ) ) {
			$this->google_fonts_subsets_excluded = $excluded;

			return true;
		}

		return false;
	}

	/**
	 * Callback function for settings section
	 *
	 * @param $tab
	 */
	public function setting_section_callback_function( $tab ) {
		if ( 'wpb_js_composer_settings_color' === $tab['id'] ) {
			echo '<div class="tab_intro">
				<p>' . esc_html__( 'Here you can tweak default WPBakery Page Builder content elements visual appearance. By default WPBakery Page Builder is using neutral light-grey theme. Changing "Main accent color" will affect all content elements if no specific "content block" related color is set.', 'js_composer' ) . '
				</p>
			</div>';
		}
	}

	/**
	 * @param $rules
	 *
	 * @return mixed
	 */
	public function sanitize_not_responsive_css_callback( $rules ) {
		return (bool) $rules;
	}

	/**
	 * @param $checkbox
	 *
	 * @return mixed
	 */
	public function sanitize_local_google_fonts_callback( $checkbox ) {
		return (bool) $checkbox;
	}

	/**
	 * @param $checkbox
	 *
	 * @since 7.7
	 *
	 * @return mixed
	 */
	public function sanitize_modules_callback( $field ) {
		return $field;
	}

	/**
	 * @param $subsets
	 *
	 * @return array
	 */
	public function sanitize_google_fonts_subsets_callback( $subsets ) {
		$pt_array = array();
		if ( isset( $subsets ) && is_array( $subsets ) ) {
			foreach ( $subsets as $pt ) {
				if ( ! in_array( $pt, $this->getGoogleFontsSubsetsExcluded(), true ) && in_array( $pt, $this->getGoogleFontsSubsets(), true ) ) {
					$pt_array[] = $pt;
				}
			}
		}

		return $pt_array;
	}

	public function rebuild() {
		/** WordPress Template Administration API */
		require_once ABSPATH . 'wp-admin/includes/template.php';
		/** WordPress Administration File API */
		require_once ABSPATH . 'wp-admin/includes/file.php';
		delete_option( self::$field_prefix . 'compiled_js_composer_less' );
		$this->initAdmin();
	}

	/**
	 * @deprecated 7.7
	 */
	public static function buildCustomColorCss() {
		_deprecated_function( __METHOD__, '7.7', "vc_modules_manager()->get_module('vc-design-options')->settings->build_custom_color_css()" );
		if ( ! vc_modules_manager()->is_module_on( 'vc-design-options' ) ) {
			vc_modules_manager()->turn_on( 'vc-design-options' );
		}
		vc_modules_manager()->get_module( 'vc-design-options' )->settings->build_custom_color_css();
	}

	/**
	 * Builds custom css file using css options from vc settings.
	 *
	 * @deprecated 7.7
	 * @return bool
	 */
	public static function buildCustomCss() {
		_deprecated_function( __METHOD__, '7.7', "vc_modules_manager()->get_module('vc-custom-css')->settings->build_custom_css()" );
		if ( ! vc_modules_manager()->is_module_on( 'vc-custom-css' ) ) {
			vc_modules_manager()->turn_on( 'vc-custom-css' );
		}
		vc_modules_manager()->get_module( 'vc-custom-css' )->settings->build_custom_css();
	}

	/**
	 * @param \WP_Filesystem_Direct $wp_filesystem
	 * @param $option
	 * @param $filename
	 *
	 * @return bool|string
	 */
	public static function checkCreateUploadDir( $wp_filesystem, $option, $filename ) {
		$js_composer_upload_dir = self::uploadDir();
		if ( ! $wp_filesystem->is_dir( $js_composer_upload_dir ) ) {
			if ( ! $wp_filesystem->mkdir( $js_composer_upload_dir, 0777 ) ) {
				add_settings_error( self::$field_prefix . $option, $wp_filesystem->errors->get_error_code(), sprintf( esc_html__( '%1$s could not be created. Not available to create js_composer directory in uploads directory (%2$s).', 'js_composer' ), $filename, $js_composer_upload_dir ), 'error' );

				return false;
			}
		}

		return $js_composer_upload_dir;
	}

	/**
	 * @return string
	 */
	public static function uploadDir() {
		$upload_dir = wp_upload_dir();
		/** @var \WP_Filesystem_Direct $wp_filesystem */ global $wp_filesystem;

		return $wp_filesystem->find_folder( $upload_dir['basedir'] ) . vc_upload_dir();
	}

	/**
	 * @return string
	 */
	public static function uploadURL() {
		$upload_dir = wp_upload_dir();

		return $upload_dir['baseurl'] . vc_upload_dir();
	}


	/**
	 * @return string
	 */
	public static function getFieldPrefix() {
		return self::$field_prefix;
	}

	/**
	 * @param string $url
	 * @return \WP_Filesystem_Direct|bool
	 */
	public static function getFileSystem( $url = '' ) {
		/** @var \WP_Filesystem_Direct $wp_filesystem */ global $wp_filesystem;
		$status = true;
		if ( ! $wp_filesystem || ! is_object( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			$status = WP_Filesystem( false, false, true );
		}

		return $status ? $wp_filesystem : false;
	}

	/**
	 * @return string
	 */
	public function getOptionGroup() {
		return $this->option_group;
	}

	/**
	 * @deprecated 7.7
	 */
	public function useCustomCss() {
		_deprecated_function( __METHOD__, '7.7', "vc_modules_manager()->get_module('vc-design-options')->settings->use_custom_css()" );
		if ( ! vc_modules_manager()->is_module_on( 'vc-design-options' ) ) {
			vc_modules_manager()->turn_on( 'vc-design-options' );
		}
		return vc_modules_manager()->get_module( 'vc-design-options' )->settings->use_custom_css();
	}

	/**
	 * @deprecated 7.7
	 */
	public function getCustomCssVersion() {
		_deprecated_function( __METHOD__, '7.7', "vc_modules_manager()->get_module('vc-design-options')->settings->get_custom_css_version()" );
		if ( ! vc_modules_manager()->is_module_on( 'vc-design-options' ) ) {
			vc_modules_manager()->turn_on( 'vc-design-options' );
		}
		return vc_modules_manager()->get_module( 'vc-design-options' )->settings->get_custom_css_version();
	}

	/**
	 * @deprecated 7.7
	 */
	public function get_default( $key ) {
		_deprecated_function( __METHOD__, '7.7', "vc_modules_manager()->get_module('vc-design-options')->settings->get_default()" );
		if ( ! vc_modules_manager()->is_module_on( 'vc-design-options' ) ) {
			vc_modules_manager()->turn_on( 'vc-design-options' );
		}
		return vc_modules_manager()->get_module( 'vc-design-options' )->settings->get_default( $key );
	}

	/**
	 * @deprecated 7.7
	 */
	public function restoreColor() {
		_deprecated_function( __METHOD__, '7.7', "vc_modules_manager()->get_module('vc-design-options')->settings->restore_color()" );
		if ( ! vc_modules_manager()->is_module_on( 'vc-design-options' ) ) {
			vc_modules_manager()->turn_on( 'vc-design-options' );
		}
		vc_modules_manager()->get_module( 'vc-design-options' )->settings->restore_color();
	}

	/**
	 * We should reset some optionality of other modules when modules option changed.
	 *
	 * @since 7.7
	 */
	public function reset_modules_dependency( $old_value, $new_value ) {
		$options = json_decode( $new_value, true );

		if ( isset( $options['vc-design-options'] ) && ! $options['vc-design-options'] ) {
			delete_option( self::$field_prefix . 'use_custom' );
		}
	}
}
