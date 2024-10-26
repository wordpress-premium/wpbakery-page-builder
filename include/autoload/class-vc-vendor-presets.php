<?php
/**
 * Autoload preset for vendors.
 *
 * @note we require our autoload files everytime and everywhere after plugin load.
 * @since 4.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Singleton to hold all vendor presets
 *
 * @since 4.8
 */
class Vc_Vendor_Preset {

	/**
	 * Instance of Vc_Vendor_Preset
	 *
	 * @var Vc_Vendor_Preset
	 */
	private static $instance;

	/**
	 * Collection of vendor presets
	 *
	 * @var array
	 */
	private static $presets = array();

	/**
	 * Get instance of Vc_Vendor_Preset.
	 *
	 * @return \Vc_Vendor_Preset
	 */
	public static function getInstance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Protected constructor.
	 */
	protected function __construct() {
	}

	/**
	 * Add vendor preset to collection
	 *
	 * @param string $title
	 * @param string $shortcode
	 * @param array $params
	 * @param bool $default_value
	 *
	 * @return bool
	 * @since 4.8
	 */
	public function add( $title, $shortcode, $params, $default_value = false ) {
		if ( ! $title || ! is_string( $title ) || ! $shortcode || ! is_string( $shortcode ) || ! $params || ! is_array( $params ) ) {
			return false;
		}

		$preset = array(
			'shortcode' => $shortcode,
			'default' => $default_value,
			'params' => $params,
			'title' => $title,
		);

		// @codingStandardsIgnoreLine
		$id = md5( serialize( $preset ) );

		self::$presets[ $id ] = $preset;

		return true;
	}

	/**
	 * Get specific vendor preset
	 *
	 * @param string $id
	 *
	 * @return mixed array|false
	 * @since 4.8
	 */
	public function get( $id ) {
		if ( isset( self::$presets[ $id ] ) ) {
			return self::$presets[ $id ];
		}

		return false;
	}

	/**
	 * Get all vendor presets for specific shortcode
	 *
	 * @param string $shortcode
	 *
	 * @return array
	 * @since 4.8
	 */
	public function getAll( $shortcode ) {
		$list = array();

		foreach ( self::$presets as $id => $preset ) {
			if ( $shortcode === $preset['shortcode'] ) {
				$list[ $id ] = $preset;
			}
		}

		return $list;
	}

	/**
	 * Get all default vendor presets
	 *
	 * Include only one default preset per shortcode
	 *
	 * @return array
	 * @since 4.8
	 */
	public function getDefaults() {
		$list = array();

		$added = array();

		foreach ( self::$presets as $id => $preset ) {
			if ( $preset['default'] && ! in_array( $preset['shortcode'], $added, true ) ) {
				$added[] = $preset['shortcode'];
				$list[ $id ] = $preset;
			}
		}

		return $list;
	}

	/**
	 * Get ID of default preset for specific shortcode
	 *
	 * If multiple presets are default, return first
	 *
	 * @param string $shortcode
	 *
	 * @return string|null
	 * @since 4.8
	 */
	public function getDefaultId( $shortcode ) {
		foreach ( self::$presets as $id => $preset ) {
			if ( $shortcode === $preset['shortcode'] && $preset['default'] ) {
				return $id;
			}
		}

		return null;
	}
}
