jQuery( document ).ready( function ( $ ) {
	'use strict';

	if (window.Vc_postSettingsEditor) {
		function setEditorNewValue($editor_input, editor_slug) {
			// set new value to textarea
			$editor_input.val( window[editor_slug].getValue() );
		}

		var editor_js_header = new Vc_postSettingsEditor();
		editor_js_header.sel = 'wpb_js_header_editor';
		editor_js_header.mode = 'javascript';
		var editor_js_footer = new Vc_postSettingsEditor();
		editor_js_footer.sel = 'wpb_js_footer_editor';
		editor_js_footer.mode = 'javascript';

		var editor_list = {
			js_header : editor_js_header,
			js_footer : editor_js_footer
		};

		for (var editor_name in editor_list) {
			var $editor = $( '#wpb_' + editor_name + '_editor' );
			if ( $editor.length ) {
				var $editor_input = $editor.prev();
				var editor_slug = 'editor' + editor_name;
				window[editor_slug] = editor_list[editor_name];
				window[editor_slug].setEditor( $editor_input.val() );

				window[editor_slug].getEditor().on( "change", setEditorNewValue.bind(null, $editor_input, editor_slug));
			}
		}
	}
} );
