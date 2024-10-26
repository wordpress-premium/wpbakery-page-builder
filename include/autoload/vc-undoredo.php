<?php
/**
 * Autoload hooks related shortcode undo/redo plugin functionality.
 *
 * @note we require our autoload files everytime and everywhere after plugin load.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Load undo/redo functionality for frontend editor.
 */
function vc_navbar_undoredo() {
	if ( vc_is_frontend_editor() || is_admin() ) {
		require_once vc_path_dir( 'EDITORS_DIR', 'navbar/class-vc-navbar-undoredo.php' );
		new Vc_Navbar_Undoredo();
	}
}

add_action( 'admin_init', 'vc_navbar_undoredo' );
