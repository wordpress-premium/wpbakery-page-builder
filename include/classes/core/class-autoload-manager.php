<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class help manage autoload components.
 * Autoload components is functionality that we want to see everywhere after plugin loaded.
 *
 * @since   7.7
 */
class Vc_Autoload_Manager {

	/**
	 * Name for autoload components manifest file.
	 *
	 * @since 7.7
	 * @var string
	 */
	private $components_manifest = 'components.json';

	/**
	 * Autoload required components to enable useful functionality.
	 *
	 * @since 7.7
	 */
	public function load() {
		$data = $this->get_autoload_manifest();

		if ( ! $data ) {
			return;
		}

		$components = (array) json_decode( $data );
		$components = apply_filters( 'vc_autoload_components_list', $components );
		$dir = vc_path_dir( 'AUTOLOAD_DIR' );
		foreach ( $components as $component => $description ) {
			$component_path = $dir . '/' . $component;
			if ( false === strpos( $component_path, '*' ) ) {
				require_once $component_path;
				continue;
			}

			$components_paths = glob( $component_path );
			if ( ! is_array( $components_paths ) ) {
				continue;
			}

			foreach ( $components_paths as $path ) {
				if ( false === strpos( $path, '*' ) ) {
					require_once $path;
				}
			}
		}
	}

	/**
	 * Get autoload manifest file data.
	 *
	 * @since 7.7
	 *
	 * @return false|string
	 */
	public function get_autoload_manifest() {
		$manifest_file_path = vc_path_dir( 'AUTOLOAD_DIR', $this->components_manifest );
		$manifest_file = apply_filters( 'vc_autoload_components_manifest_file', $manifest_file_path );
		if ( ! is_file( $manifest_file ) ) {
			return false;
		}

		ob_start();
		require_once $manifest_file;
		$data = ob_get_clean();

		return $data;
	}
}
