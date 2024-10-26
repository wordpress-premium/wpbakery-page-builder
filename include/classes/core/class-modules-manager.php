<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class help manage modules components.
 * Modules - independent plugin functionality that can be enabled/disabled.
 *
 * @since   7.7
 */
class Vc_Modules_Manager {
	/**
	 * Option name to store modules settings.
	 * @note we always provide settings prefix for our options
	 * @see $this->get_option_name()
	 *
	 * @since 7.7
	 * @var string
	 */
	public $option_slug = 'modules';

	/**
	 * Modules settings value.
	 *
	 * @since 7.7
	 * @var array
	 */
	public static $settings_value;

	/**
	 * List turned on modules.
	 *
	 * @var array
	 */
	public static $turn_on_list = [];

	/**
	 * Get plugin module data list.
	 *
	 * @return array [ 'module_slug' => [
	 *                      'name' => 'Module Name', // Required
	 *                      'main_file_path' => 'Path to main module file', // Required
	 *                      'module_class' => 'Module Class Name' // Optional
	 *                      'is_active' => true || false // Optional - default true
	 *                ] ]
	 */
	public function get_plugin_module_list() {
		$modules_dir = vc_manager()->path( 'MODULES_DIR' );

		return [
			'vc-seo' => [
				'name' => esc_html__( 'SEO', 'js_composer' ),
				'main_file_path' => $modules_dir . '/seo/module.php',
				'module_class' => 'Vc_Seo_Module',
				'is_active' => true,
			],
			'vc-ai' => [
				'name' => esc_html__( 'WPB AI', 'js_composer' ),
				'main_file_path' => $modules_dir . '/ai/module.php',
				'module_class' => 'Vc_Ai_Module',
				'is_active' => true,
			],
			'vc-automapper' => [
				'name' => esc_html__( 'Shortcode Mapper', 'js_composer' ),
				'main_file_path' => $modules_dir . '/automapper/module.php',
				'module_class' => 'Vc_Automapper',
				'is_active' => true,
			],
			'vc-custom-js' => [
				'name' => esc_html__( 'Custom JS', 'js_composer' ),
				'main_file_path' => $modules_dir . '/custom-js/module.php',
				'module_class' => 'Vc_Custom_Js_Module',
				'is_active' => true,
			],
			'vc-custom-css' => [
				'name' => esc_html__( 'Custom CSS', 'js_composer' ),
				'main_file_path' => $modules_dir . '/custom-css/module.php',
				'module_class' => 'Vc_Custom_Css_Module',
				'is_active' => true,
			],
			'vc-design-options' => [
				'name' => esc_html__( 'Design Option (Skin builder)', 'js_composer' ),
				'main_file_path' => $modules_dir . '/design-options/module.php',
				'module_class' => 'Vc_Design_Options_Module',
				'is_active' => true,
			],
			'vc-post-custom-layout' => [
				'name' => esc_html__( 'Post Custom Layout', 'js_composer' ),
				'main_file_path' => $modules_dir . '/post-custom-layout/module.php',
				'module_class' => 'Vc_Post_Custom_Layout_Module',
				'is_active' => true,
			],
			'vc-scroll-to-element' => [
				'name' => esc_html__( 'Scroll to element', 'js_composer' ),
				'main_file_path' => $modules_dir . '/scroll-to-element/module.php',
				'module_class' => 'Vc_Scroll_To_Element_Module',
				'is_active' => true,
			],
			'vc-color-picker' => [
				'name' => esc_html__( 'Color Picker Settings', 'js_composer' ),
				'main_file_path' => $modules_dir . '/color-picker/module.php',
				'module_class' => 'Vc_Color_Picker_Module',
				'is_active' => true,
			],
		];
	}

	/**
	 * Get all modules.
	 * Please note we get here all our modules and 3 party modules, not just enabled.
	 *
	 * @return array
	 * @see Vc_Modules_Manager::get_enabled for enabled modules.
	 *
	 * @since 7.7
	 */
	public function get_all() {
		$modules = $this->get_plugin_module_list();
		$third_party_modules = $this->get_third_party_list();

		return array_merge( $modules, $third_party_modules );
	}

	/**
	 * Here third party developers can add their modules.
	 *
	 * @since 7.7
	 * @return array ['module_slug' => [
	 *                      'name' => 'Module Name', // Required
	 *                      'main_file_path' => 'Path to main module file', // Required
	 *                      'module_class' => 'Module Class Name' // Optional
	 *                      'is_active' => true || false // Optional - default true
	 *                ] ]
	 */
	public function get_third_party_list() {
		$third_party_module_list = apply_filters( 'vc_third_party_modules_list', [] );

		foreach ( $third_party_module_list as $module_data ) {
			$this->validate_module( $module_data );
		}

		return $third_party_module_list;
	}

	/**
	 * Here we try to return main module class object.
	 *
	 * @note we do not restrict 3 party devs,
	 * but on our plugin core level we avoid directly use modules classes
	 * Usually we use wp hook system to communicate with our modules.
	 *
	 * @since 7.7
	 * @param $slug
	 * @return false | object
	 */
	public function get_module( $slug ) {
		$modules = $this->get_all();

		if ( ! isset( $modules[ $slug ], $modules[ $slug ]['main_file_path'], $modules[ $slug ]['module_class'] ) ) {
			return false;
		}

		if ( ! file_exists( $modules[ $slug ]['main_file_path'] ) ) {
			return false;
		}

		require_once $modules[ $slug ]['main_file_path'];

		if ( ! class_exists( $modules[ $slug ]['module_class'] ) ) {
			return false;
		}

		return new $modules[ $slug ]['module_class']();
	}

	/**
	 * Get option name for modules settings.
	 *
	 * @return string
	 *
	 * @since 7.7
	 */
	public function get_option_name() {
		return vc_settings()::$field_prefix . $this->option_slug;
	}

	/**
	 * Get settings option for our modules plugin section.
	 *
	 * @since 7.7
	 * @return array
	 */
	public function get_settings() {
		if ( ! isset( self::$settings_value ) ) {
			$value = json_decode( get_option( $this->get_option_name(), '' ), true );
			if ( is_array( $value ) ) {
				self::$settings_value = $value;
			} else {
				self::$settings_value = [];
			}
		}
		return self::$settings_value;
	}

	/**
	 * Check current module status.
	 * First we check if user enabled module in plugin settings.
	 * Second we check if module is active in module attributes.
	 *
	 * @note we use this check before we turn module on
	 *
	 * @since 7.7
	 * @param $module_slug
	 * @return bool
	 */
	public function get_module_status( $module_slug ) {
		if ( $this->get_module_setting_status( $module_slug ) !== null ) {
			return $this->get_module_setting_status( $module_slug );
		}

		if ( $this->get_module_attribute_status( $module_slug ) !== null ) {
			return $this->get_module_attribute_status( $module_slug );
		}

		// fallback for case when module not set in settings and not set in attributes.
		return true;
	}

	/**
	 * Check if module is on.
	 * @see $this->turn_on()
	 *
	 * @since 7.7
	 * @return bool
	 */
	public function is_module_on( $module_slug ) {
		return in_array( $module_slug, self::$turn_on_list );
	}

	/**
	 * Check if module status in settings.
	 * Module can be enabled by user in modules plugin tab of plugin settings.
	 *
	 * @since 7.7
	 * @param $module_slug
	 * @return bool | null - null in case when module not set in settings for example when plugin activated first time.
	 */
	public function get_module_setting_status( $module_slug ) {
		$settings = $this->get_settings();
		if ( ! isset( $settings[ $module_slug ] ) ) {
			return null;
		}

		return (bool) $settings[ $module_slug ];
	}

	/**
	 * Check module status in module attributes.
	 * Module can be activated with module attributes.
	 *
	 * @see Vc_Modules_Manager::get_plugin_module_list
	 *
	 * @since 7.7
	 * @param string $module_slug
	 * @return bool | null - null in case when we or third party not set is_active attribute.
	 */
	public function get_module_attribute_status( $module_slug ) {
		$all_modules = $this->get_all();

		if ( ! isset( $all_modules[ $module_slug ]['is_active'] ) ) {
			return null;
		}

		return (bool) $all_modules[ $module_slug ]['is_active'];
	}

	/**
	 * Autoload required modules components to enable useful functionality.
	 *
	 * @since 7.7
	 */
	public function load() {
		$modules = $this->get_all();
		foreach ( $modules as $module_slug => $module_data ) {
			if ( ! $this->get_module_status( $module_slug ) ) {
				continue;
			}
			$this->turn_on( $module_slug );
		}
	}

	/**
	 * Activate module.
	 * When we activate module, we include main module and init his class if we can.
	 * We use this method in plugin core only for deprecated functionality.
	 * We do not connect modules independently cos we have only one place for it in $this->load()
	 *
	 * @since 7.7
	 * @param string $module_slug
	 *
	 * @return bool
	 */
	public function turn_on( $module_slug ) {
		if ( isset( self::$turn_on_list[ $module_slug ] ) ) {
			return true;
		}

		$module_data = $this->get_module_data( $module_slug );

		if ( ! $this->validate_module( $module_data ) ) {
			return false;
		}

		require_once $module_data['main_file_path'];

		// 3 party modules can do anything inside their main module file
		// but wpbakery modules can have module class and init method.
		if ( isset( $module_data['module_class'] ) && class_exists( $module_data['module_class'] ) ) {
			$module = new $module_data['module_class']();

			if ( method_exists( $module, 'init' ) ) {
				$module->init();
			}
		}

		self::$turn_on_list[] = $module_slug;

		return true;
	}

	/**
	 * Get module data from module data list
	 *
	 * @since 7.7
	 * @param string $module_slug
	 * @return array
	 */
	public function get_module_data( $module_slug ) {
		$modules = $this->get_all();
		if ( ! isset( $modules[ $module_slug ] ) ) {
			return [];
		}

		if ( ! is_array( $modules[ $module_slug ] ) ) {
			return [];
		}

		return $modules[ $module_slug ];
	}

	/**
	 * Get value of modules settings option.
	 * We keep there modules tab plugin settings parameters.
	 *
	 * @since 7.7
	 */
	public function output_error( $message ) {
		// we want to prevent user to do wrong things with module, so it ok that error looks like fatal there
        // phpcs:ignore
        trigger_error( esc_html($message), E_USER_ERROR );
	}

	/**
	 * Check module validation params and fall with fatal error if something wrong.
	 *
	 * @param array $module_data
	 * @since 7.7
	 *
	 * @return bool
	 */
	public function validate_module( $module_data ) {
		if ( ! isset( $module_data['name'] ) || ! isset( $module_data['main_file_path'] ) ) {
			$this->output_error( 'Your WPBakery module name and main_file_path is required in module attributes' );
			return false;
		}

		if ( ! file_exists( $module_data['main_file_path'] ) ) {
			$this->output_error( 'Your WPBakery main module file' . esc_html( $module_data['main_file_path'] ) . ' does not exist' );
			return false;
		}

		return true;
	}

	/**
	 * Register modules script file.
	 *
	 * @since 7.8
	 */
	public function register_modules_script() {
		wp_register_script( 'wpb-modules-js', vc_asset_url( 'js/dist/modules.min.js' ), [], WPB_VC_VERSION, true );
	}

	/**
	 * Check if user set custom modules optionality in plugin settings.
	 *
	 * @since 7.8
	 * @return bool
	 */
	public function is_modules_set_in_setting() {
		$module_settings = json_decode( get_option( $this->get_option_name(), '' ), true );

		if ( empty( $module_settings ) ) {
			return false;
		}

		// if all modules has true value we consider that user not changed anything
		foreach ( $module_settings as $module_status ) {
			if ( false === $module_status ) {
				return true;
			}
		}

		return false;
	}
}
