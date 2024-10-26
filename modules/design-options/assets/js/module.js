jQuery( document ).ready( function ( $ ) {

	var isOptionsEnabled = $( '#wpb_js_use_custom' ).prop( 'checked' );
	var pickers = [];
	var pickrOptions = {
		disabled: !isOptionsEnabled
	}
	vc.initColorPicker(null, pickrOptions, null,  pickers);

	$( '#vc_settings-color-restore-default' ).on('click', function ( e ) {
		e.preventDefault();
		if ( confirm( window.i18nLocaleSettings.are_you_sure_reset_color ) ) {
			$( '#vc_settings-color-action' ).val( 'restore_color' );
			$( '#vc_settings-color' ).attr( 'action', window.location.href ).find( '[type=submit]' ).click();
		}
	} );
	$( '#wpb_js_use_custom' ).on( 'change', function () {
		if ( this.checked ) {
			$( '#vc_settings-color' ).addClass( 'color_enabled' );
			pickers.forEach(function (pickr) {
				pickr.enable();
			});
		} else {
			$( '#vc_settings-color' ).removeClass( 'color_enabled' );
			pickers.forEach(function (pickr) {
				pickr.disable();
			});
		}
	} );

	function showMessageMore( text, typeClass, timeout, remove ) {
		if ( remove ) {
			$( '.vc_atm-message' ).remove();
		}
		var $message = $( '<div class="vc_atm-message ' + (typeClass ? typeClass : '') + '" style="display: none;"><p></p></div>' );
		$message.find( 'p' ).text( text );
		if ( !_.isUndefined( timeout ) ) {
			window.setTimeout( function () {
				$message.fadeOut( 500, function () {
					$( this ).remove();
				} );
			}, timeout );
		}
		return $message;
	}

	var lessBuilding = false;
	$( '#vc_settings-color' ).on( 'submit', function ( e ) {
		e.preventDefault();
		if ( lessBuilding ) {
			return;
		}
		var form, $submitButton, $designCheckBox;

		form = this;
		$submitButton = $( '#submit_btn' );
		$designCheckBox = $( '#wpb_js_use_custom' );
		if ( $designCheckBox.prop( 'checked' ) && 'restore_color' !== $( '#vc_settings-color-action' ).val() ) {
			var modifyVars, variablesDataLinker, $spinner;

			lessBuilding = true;
			modifyVars = $( form ).serializeArray();
			variablesDataLinker = $submitButton.data( 'vc-less-variables' );
			$spinner = $( '<span class="vc_settings-spinner vc_ui-wp-spinner"></span>' );
			$submitButton.val( window.i18nLocaleSettings.saving );
			$spinner.insertBefore( $submitButton ).show();

			_.delay( function () {
				vc.less.build( {
					modifyVars: modifyVars,
					variablesDataLinker: variablesDataLinker,
					lessPath: $submitButton.data( 'vc-less-path' ),
					rootpath: $submitButton.data( 'vc-less-root' )
				}, function ( output, error ) {
					if ( !_.isUndefined( output ) && !_.isUndefined( output.css ) ) {
						$( '[name="wpb_js_compiled_js_composer_less"]' ).val( output.css );
						var $form = $( '#vc_settings-color' );
						$.ajax( {
							type: 'POST',
							url: $form.attr( 'action' ),
							data: $form.eq( 0 ).serializeArray(),
							success: function () {
								showMessageMore( window.i18nLocaleSettings.saved,
									'updated',
									5000,
									true ).insertBefore( $submitButton.parent() ).fadeIn( 500 );
								$submitButton.val( window.i18nLocaleSettings.save );
								lessBuilding = false;
								$spinner.remove();
							},
							error: function () {
								showMessageMore( window.i18nLocaleSettings.form_save_error,
									'error',
									undefined,
									true ).insertBefore( $submitButton.parent() ).fadeIn( 500 );
								$submitButton.val( window.i18nLocaleSettings.save );
								lessBuilding = false;
								$spinner.remove();
							}
						} );

					} else if ( !_.isUndefined( error ) ) {
						if ( window.console && window.console.warn ) {
							window.console.warn( 'build error', error );
						}
						showMessageMore( window.i18nLocaleSettings.save_error + ". " + error,
							'error',
							undefined,
							true ).insertBefore( $submitButton.parent() ).fadeIn( 500 );
						$submitButton.val( window.i18nLocaleSettings.save );
						lessBuilding = false;
						$spinner.remove();
					}
				} );
			}, 100 );
		} else {
			form.submit();
		}
	} );
});
