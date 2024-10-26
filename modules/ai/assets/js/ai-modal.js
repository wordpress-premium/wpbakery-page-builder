(function ( $ ) {
	'use strict';

	var $aiModal = $( '#vc_ui-helper-modal-ai' );
	var $wpwrap = $( '#wpwrap' );
	var $insertButton = $aiModal.find( '[data-vc-ui-element="button-save"]' );

	$wpwrap.on( 'click', '.vc_ui-icon-ai', openModal );

	function openModal ( e ) {
		var $currentBtn = $( e.currentTarget );
		var $currentParamContainer = $currentBtn.closest( '.vc_shortcode-param' );
		var currentParamData = null;
		var iconData = $currentBtn.data();
		if ($currentParamContainer.length) {
			currentParamData = $currentParamContainer.data();
		}
		var aiElementType = iconData.wpbAiElementType || 'textarea';
		var aiElementId = iconData.fieldId || aiElementType;

		if (!isModalPreloader()) {
			setModalPreloader();
		}

		setModalContent(aiElementType, aiElementId);

		if ( currentParamData ) {
			var $currentParamField = $currentParamContainer.find( '.' + currentParamData.param_type );

			$aiModal.data( 'element', $currentParamField );
			$.each( currentParamData, function ( key, value ) {
				$aiModal.data( key, value );
			});
		} else if ( iconData.fieldId ) {
			var $editFormLine = $currentBtn.closest( '.edit_form_line' );
			var $textWrapper = $currentBtn.closest( '.vc_ui-settings-text-wrapper' );
			var $classicEditorWrapper = $currentBtn.closest( '#postdivrich' );
			var $fieldElement = null;
			if ( $editFormLine.length ) {
				$fieldElement = $editFormLine.find( '#' + iconData.fieldId );
			} else if ( $textWrapper.length ) {
				$fieldElement = $textWrapper.siblings( '#' + iconData.fieldId );
			} else if ( $classicEditorWrapper.length ) {
				$fieldElement = $classicEditorWrapper.find( '#' + iconData.fieldId );
			}

			$aiModal.data( 'fieldId', iconData.fieldId );
			$aiModal.data( 'element', $fieldElement );
		}

		$aiModal.addClass( 'vc_active' );
		$aiModal.on( 'click', closeModal );
	}

	function isModalPreloader() {
		return $aiModal.find( '.vc_ui-helper-modal-ai-preloader' ).length;
	}

	function setModalPreloader() {
		$aiModal.find( '.vc_ui-post-settings-header-container' ).after( '<div class="vc_ui-helper-modal-ai-preloader"><div class="vc_ui-wp-spinner vc_ui-wp-spinner-dark vc_ui-wp-spinner-lg"></div></div>' );
	}

	function setModalContent(aiElementType, aiElementId) {
		var data = {
			action: 'wpb_ai_get_modal_data',
			data: {
				ai_element_type: aiElementType,
				ai_element_id: aiElementId,
			},
			_vcnonce: window.vcAdminNonce
		};

		$.ajax( {
			type: 'POST',
			url: window.ajaxurl,
			data: data
		} ).done( function ( response ) {
			if ( true === response.success ) {
				if ( undefined === window.vc.ai_modal_view) {
					window.vc.ai_modal_view = new vc.AiFormView( { el: '#vc_ui-helper-modal-ai', data: response.data } );
				} else {
					window.vc.ai_modal_view.render( response.data );
				}

				if (response.data.tokens_left && response.data.tokens_total) {
					var token_usage_text =
						get_locale().ai_credit_usage + response.data.tokens_left + ' / ' + response.data.tokens_total;
					$aiModal.find('.vc-ai-tokens-usage').text(token_usage_text);
				}

				$aiModal.find(' .vc_ui-panel-content-container' ).scrollTop( 0 );
				$aiModal.find( '.vc_ui-helper-modal-ai-preloader' ).remove();
				$aiModal.find(' .vc_ui-panel-content-container' ).removeClass( 'vc_ui-hidden' );
			} else {
				// error returned by wpbakery server api
				var is_error_message =
					response &&
					response.data &&
					response.data[0] &&
					response.data[0].code &&
					response.data[0].message;

				if ( is_error_message ) {
					console.error( response.data[0].message );
					showErrorMessage( response.data[0].message );
				} else {
					console.error( get_locale().ai_response_error );
					showErrorMessage( get_locale().ai_response_error );
				}
			}
		} ).fail( function ( response ) {
			console.error( get_locale().ai_response_error );
			_this.resetButton();
			showErrorMessage( get_locale().ai_response_error );
		});
	}

	function showErrorMessage ( message ) {
		window.vc.showMessage( message, 'error', 10000, '#vc_ui-helper-modal-ai .vc_ui-panel-window-inner' );
	}

	function closeModal ( e ) {
		var $target = $( e.target );
		var isCloseButton = $target.closest( '[data-vc-ui-element="button-close"]' ).length;

		if ( isCloseButton ) {
			$aiModal.removeClass( 'vc_active' );
			$aiModal.off( 'click', closeModal );
			$aiModal.removeData();
			$aiModal.find(' .vc_ui-panel-content-container' ).addClass( 'vc_ui-hidden' );
			$aiModal.find(' .vc_ui-helper-modal-ai-placeholder' ).addClass( 'vc_ui-hidden' );
			$insertButton.hide();
		}
	}

	function get_locale() {
		if ( window.i18nLocale ) {
			return window.i18nLocale;
		} else {
			return window.i18nLocaleSettings;
		}
	}
})( window.jQuery );
