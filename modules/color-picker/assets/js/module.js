jQuery( document ).ready( function ( $ ) {
	vc.initColorPicker();

	var previewPickerElement = document.querySelector( '#preview-picker' );
	if ( previewPickerElement ) {
		var container = previewPickerElement && previewPickerElement.closest( '#wpb-js-composer-settings' );
		var input = container.querySelector( '.vc_color-control' );
		var initialColor = input.value || '#EEEEEE';
		var pickrOptions = {
			single: true,
			el: previewPickerElement,
			default: initialColor,
			container: container,
			appClass: 'wpb-pickr-preview',
			showAlways: true,
			position: 'bottom-start'
		};
		vc.initColorPicker( null, pickrOptions );
	}

	function repositionPreview() {
		var $previewElement = $( '.wpb-pickr-preview' );
		if ( $previewElement ) {
			var $previewInput = $( '#picker-preview-container p' );
			var inputOffset = $previewInput.offset();
			var $menuWidth = $( '#adminmenuback' ).width();
			var inputLeft =  inputOffset && inputOffset.left;
			var inputTop =  inputOffset && inputOffset.top;
			var newLeft = inputLeft - $menuWidth - 20;
			var newTop = inputTop + $previewInput.outerHeight();
			$previewElement.css( {
				position: 'absolute',
				left: newLeft + 'px',
				top: newTop + 'px'
			} );
		}
	}

	$( window ).on( 'resize', repositionPreview );
	$( window ).on( 'load', repositionPreview );

	$( '#vc_settings-color-picker-restore-default' ).on( 'click', function ( e ) {
		e.preventDefault();
		if ( confirm( window.i18nLocaleSettings.are_you_sure_reset_color ) ) {
			$( '#vc_settings-color-picker-action' ).val( 'restore_color-picker' );
			$( '#vc_settings-color-picker' ).attr( 'action', window.location.href ).find( '[type=submit]' ).click();
		}
	} );
} );

