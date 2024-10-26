jQuery( document ).ready( function ( $ ) {
	'use strict';

	if (window.Vc_postSettingsEditor) {
		function setEditorNewValue($editor_input, editor_slug) {
			// set new value to textarea
			$editor_input.val( window[editor_slug].getValue() );
		}

		var editor_css = new Vc_postSettingsEditor();
		editor_css.sel = 'wpb_css_editor';
		editor_css.mode = 'css';
		editor_css.is_focused = true;

		var $editor = $( '#wpb_css_editor' );
		if ( $editor.length ) {
			var $editor_input = $editor.prev();
			var editor_slug = 'editorcss';
			window[editor_slug] = editor_css;
			window[editor_slug].setEditor( $editor_input.val() );

			window[editor_slug].getEditor().on( "change", setEditorNewValue.bind(null, $editor_input, editor_slug));
		}
	}
} );
