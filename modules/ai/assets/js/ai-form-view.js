(function ( $ ) {
	'use strict';

	window.vc.AiFormView = Backbone.View.extend( {
		events: {
			'click .vc_ai-generate-button': 'generateContent',
			'change [name="contentType"]': 'changeContentType',
			'input [name="prompt"]': 'changePrompt',
			'click [data-vc-ui-element="button-save"]': 'insertContent',
			'click .wpb-copy-output': 'copyContent'
		},
		seconds: 0,
		minutes: 0,
		timerInterval: null,
		isGenerating: false,
		maxWaitingCacheInterval: 900000,
		maxPromptLength: 2000,
		initialize: function ( options ) {
			this.toggleModalPromoClass( options.data.type );
			this.$el.find( '.vc_ui-helper-modal-ai-preloader' ).after( options.data.content );
			this.setFormElements();
			$('.edit-form-info').initializeTooltips();

		},
		render: function ( options ) {
			if (this.timerInterval) {
				this.clearTimer();
			}
			this.toggleModalPromoClass( options.type );
			this.$form.after( options.content );
			this.$form.remove();
			this.setFormElements();

			return this;
		},
		setFormElements: function () {
			this.$form = this.$el.find( '.vc_ui-panel-content-container' );
			this.$generate_button = this.$el.find( '.vc_ai-generate-button' );
			this.$close_button = this.$el.find( '[data-vc-ui-element="button-close"]' );
			this.$insert_button = this.$el.find( '[data-vc-ui-element="button-save"]' );
			this.$generated_content = this.$el.find( '.wpb_ai-generated-content' );
			this.$prompt_field = this.$el.find( '[name="prompt"]' );
			this.$generate_placeholder = this.$el.find( '.vc_ui-helper-modal-ai-placeholder' );
			this.$generate_placeholder_timer = this.$generate_placeholder.find( '.vc_ai-timer' );
			this.initialButtonText = this.$generate_button.text().trim();
			this.contentType = this.$el.find( '[name="contentType"]' ).val();

			if ( 'new_content' === this.contentType && !this.$prompt_field.val().trim() ) {
				this.disableButton();
			}
		},
		generateContent: function ( e ) {
			e.preventDefault();
			var _this = this;
			// trim the prompt field value if user has set it programmatically
			var promptWords = this.$prompt_field.val().split( ' ' );
			if ( this.maxPromptLength < promptWords.length ) {
				this.$prompt_field.val(promptWords.slice(0, this.maxPromptLength).join( ' ' ));
			}

			var init_data = this.$form.find(':visible:not([style*="display: none"]), [name="prompt"], input[type="hidden"]').serializeArray();
			var cache_id = this.getUniqueCacheId();
			init_data.push( {name: 'cacheId', value: cache_id} );
			this.$generated_content.val('');

			var data = {
				action: 'wpb_ai_api_get_response',
				data: init_data,
				_vcnonce: window.vcAdminNonce
			};

			this.isGenerating = true;
			this.$generate_placeholder.removeClass( 'vc_ui-hidden' );
			this.timerInterval = setInterval( this.updateTimer.bind( this ), 1000 );

			// we break first request if you do not get response in 20 seconds.
			// if we do not get response in 20 seconds then we save response data in cache on the remote server
			// and then process another bunch of ajax requests to check if cache is ready
			$.ajax( {
				type: 'POST',
				url: window.ajaxurl,
				timeout: 20000,
				data: data
			} ).done( function ( response ) {
				if ( !_this.isGenerating ) {
					return false;
				}
				if ( true === response.success ) {
					_this.$generated_content.val( response.data );
					_this.resetButton( true );
					_this.$insert_button.show();
					_this.tokenUsageUpdate();
					_this.toggleCopyButton();
				} else {
					// error returned by wpbakery server api
					if ( response && response.data && response.data[0] && response.data[0].code && response.data[0].message ) {
						console.error( response.data[0].code, response.data[0].message );
						_this.resetButton( false );
						var message = response.data[0].message.replace(/\\/g, '');
						_this.showErrorMessage( message );
					} else {
						console.error( _this.getLocale().ai_response_error );
						_this.resetButton( false );
						_this.showErrorMessage( _this.getLocale().ai_response_error );
					}
				}
			} ).fail( function ( response ) {
				if ( !_this.isGenerating ) {
					return false;
				}
				if ( response && !response.statusText ) {
					console.error( _this.getLocale().ai_response_error );
					_this.resetButton( false );
					_this.showErrorMessage( _this.getLocale().ai_response_error );
					return;
				}

				if ( 'timeout' !== response.statusText ) {
					console.error( _this.getLocale().ai_response_error );
					_this.resetButton( false );
					_this.showErrorMessage( _this.getLocale().ai_response_error );
					return;
				}

				var data = {
					action: 'wpb_ai_generate_content_check_cache',
					data: {
						type: 'generate-text',
						messaged_data: true,
						cacheId: cache_id
					},
					_vcnonce: window.vcAdminNonce
				};

				// we create a timer to check if cache is ready every 10 seconds for 5 minutes
				var timeouts = [];
				for (var time_interval = 10000; time_interval <= _this.maxWaitingCacheInterval; time_interval += 10000) {
					createTimeout(time_interval);
				}

				function createTimeout(interval) {
					timeouts.push(setTimeout(function () {
						var output_value = _this.$generated_content.val();

						if (output_value) {
							// stop all other timeouts related to cache checking
							for (var i = 0; i < timeouts.length; i++) {
								if ('stop_cache_timeouts' === output_value) {
									_this.$generated_content.val('');
								}
								clearTimeout(timeouts[i]);
							}
						} else {
							_this.processCachedRequest(_this, data, interval);
						}
					}, interval));
				}

			});
		},

		processCachedRequest: function ( _this, data, time_interval ) {
			// last timer is pass then it mean that we still do not have a content
			if ( this.maxWaitingCacheInterval === time_interval ) {
				console.error( _this.getLocale().ai_response_error );
				_this.resetButton( false );
				_this.showErrorMessage( _this.getLocale().ai_response_error );
			} else {
				// any other timer then last we process request to check cache
				$.ajax( {
					type: 'POST',
					url: window.ajaxurl,
					timeout: 10000,
					data: data
				} ).done( function ( response ) {
					if ( !_this.isGenerating ) {
						return false;
					}
					if ( true === response.success && response.data && 'cache_in_process' !== response.data ) {
						_this.$generated_content.val( response.data );
						_this.resetButton( true );
						_this.$insert_button.show();
					}
					// we have a cache response but first request that created cache was failed
					if ( false === response.success && response && response.data && response.data[0] && response.data[0].code && response.data[0].message ) {
						_this.$generated_content.val( 'stop_cache_timeouts' );
						_this.resetButton( false );
						var message = response.data[0].message.replace(/\\/g, '');
						_this.showErrorMessage( message );
					}
				} );
			}
		},

		tokenUsageUpdate: function () {
			var data = {
				action: 'wpb_ai_get_token_usage',
				data: {},
				_vcnonce: window.vcAdminNonce
			};
			var _this = this;

			$.ajax( {
				type: 'POST',
				url: window.ajaxurl,
				data: data
			} ).done( function ( response ) {
				var is_token_text = undefined !== response.data.tokens_left && undefined !== response.data.tokens_total;
				if ( true === response.success && is_token_text ) {
					var token_usage_text =
						_this.getLocale().ai_credit_usage + response.data.tokens_left + ' / ' + response.data.tokens_total;
					$('.vc-ai-tokens-usage').text(token_usage_text);
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
						_this.showErrorMessage( response.data[0].message );
					} else {
						console.error( _this.getLocale().ai_response_error );
						_this.showErrorMessage( _this.getLocale().ai_response_error );
					}
				}
			} ).fail( function ( response ) {
				console.error( _this.getLocale().ai_response_error );
				_this.resetButton();
				_this.showErrorMessage( _this.getLocale().ai_response_error );
			});
		},

		getUniqueCacheId: function () {
			return Date.now().toString( 36 ) + Math.random().toString( 36 ).slice( 2 );
		},

		disableButton: function () {
			this.$generate_button.prop( 'disabled', function ( _, val ) {
				return !val;
			} );
			this.isGenerateDisabled = true;
		},

		resetButton: function ( isGenerated ) {
			var buttonText = isGenerated ? 'Regenerate' : this.initialButtonText;
			this.$generate_button.removeAttr( 'disabled style' );
			this.$generate_button.text( buttonText );
			this.$generate_button.blur();
			this.clearTimer();
		},

		clearTimer: function () {
			this.$generate_placeholder.addClass( 'vc_ui-hidden' );
			this.$generate_placeholder_timer.text( '00:00' );
			clearInterval( this.timerInterval );
			this.seconds = 0;
			this.minutes = 0;
			this.isGenerating = false;
		},

		updateTimer: function () {
			this.seconds++;

			if ( 60 === this.seconds ) {
				this.seconds = 0;
				this.minutes++;
			}

			var formattedMinutes = String( this.minutes ).padStart( 2, '0' );
			var formattedSeconds = String( this.seconds ).padStart( 2, '0' );

			this.$generate_placeholder_timer.text( formattedMinutes + ':' + formattedSeconds );
		},

		changeContentType: function ( e ) {
			this.contentType = e.target.value;
			var elementData = this.$el.data();

			// hide form fields that do not match selected content type
			var formFieldOptionalityList = $(e.target).find('option:selected').attr('data-form-fields-optionality');
			formFieldOptionalityList = formFieldOptionalityList ? formFieldOptionalityList.split('|') : [];
			this.hideFormFields( formFieldOptionalityList );

			// Set all form fields to default values except content type
			this.$form.trigger('reset');
			this.$form.find( '[name="contentType"]' ).val(this.contentType);

			// set the value of the "prompt" textarea with existing field value
			if ( 'improve_existing' === e.target.value || 'translate' === e.target.value ) {
				this.$generate_button.text( this.getLocale().regenerate );
				var existingContent = elementData.element.val();
				if ( 'textarea_raw_html' === elementData.param_type ) {
					existingContent = rawurldecode( base64_decode( existingContent.trim() ) );
				} else if ( 'textarea_html' === elementData.param_type ) {
					existingContent = window.tinymce.get( elementData.element.attr( 'id' ) ).getContent();
				}
				this.$form.find('[name="prompt"]').val(existingContent);
				this.resetButton( true );
			} else {
				this.$generate_button.text( this.getLocale().generate );
				this.$form.find('[name="prompt"]').val('');
				this.disableButton();
			}
		},

		changePrompt: function ( e ) {
			if ( this.isGenerateDisabled && e.target.value ) {
				this.resetButton( false );
				this.isGenerateDisabled = false;
			} else if ( !e.target.value && !this.isGenerateDisabled ) {
				this.disableButton();
			}
			// trim prompt value if it exceeds maxPromptLength
			var promptWords = e.target.value.split( ' ' );
			if (promptWords.length > this.maxPromptLength) {
				e.target.value = promptWords.slice(0, this.maxPromptLength).join( ' ' );
			}
		},

		showErrorMessage: function ( message ) {
			window.vc.showMessage( message, 'error', 10000, '#vc_ui-helper-modal-ai .vc_ui-panel-window-inner' );
		},

		insertContent: function () {
			var generatedContent = this.$generated_content.val();
			if ( !generatedContent ) {
				return false;
			}
			var currentParamData = this.$el.data();
			var aiFields = [ 'textarea', 'textfield', 'textarea_raw_html' ];
			var aceEditorFields = [ 'wpb_css_editor', 'wpb_js_header_editor', 'wpb_js_footer_editor' ];

			if ( 'textarea_html' === currentParamData.param_type || 'content' === currentParamData.fieldId ) {
				var $textareaElement = currentParamData.element;
				var textareaId = $textareaElement.attr( 'id' );
				if ( 'new_content' === this.contentType ) {
					var currentTextareaValue = $textareaElement.val();
					generatedContent = currentTextareaValue + ' ' + generatedContent;
				}

				var tinyMCE = window.tinymce.get( textareaId );
				if (tinyMCE) {
					tinyMCE.setContent( generatedContent );
				}
				$textareaElement.val( generatedContent ).trigger('input').trigger('change').trigger('blur');
			} else if ( aiFields.includes( currentParamData.param_type ) ) {
				var $inputElement = currentParamData.element;
				if ( 'new_content' === this.contentType ) {
					var currentInputValue = $inputElement.val();
					generatedContent = currentInputValue + ' ' + generatedContent;
				}
				$inputElement.val( generatedContent ).trigger('input').trigger('change').trigger('blur');
			} else if ( currentParamData.fieldId ) {
				if ( aceEditorFields.includes( currentParamData.fieldId ) ) {
					var aceEditor = window.ace.edit( currentParamData.fieldId );
					var currentValue = aceEditor.getValue();
					var $codeTextarea = $(aceEditor.container).find('textarea');
					var emptySpace = '';
					if(currentValue !== '') {
						emptySpace = '\n\n';
					}
					generatedContent = currentValue + emptySpace + generatedContent;
					aceEditor.setValue( generatedContent );
					$codeTextarea.trigger('input').trigger('change').trigger('blur');
				} else if ( currentParamData.element && currentParamData.element.length ) {
					if ( 'new_content' === this.contentType ) {
						var currentElementValue = currentParamData.element.val();
						generatedContent = currentElementValue + ' ' + generatedContent;
					}
					currentParamData.element.val(generatedContent).trigger('input').trigger('change').trigger('blur');
				}
			}
			this.$close_button.click();
		},

		toggleModalPromoClass: function ( type ) {
			if ( 'promo' === type ) {
				this.$el.addClass( 'vc_modal-ai-container--promo' );
			} else {
				this.$el.removeClass( 'vc_modal-ai-container--promo' );
			}
		},

		hideFormFields: function ( optionalityList ) {
			// hide form fields that do not match selected content type
			this.$form.find('div[data-optional-form-field]').each(function () {
				var $formField = $(this);
				var formFieldSlug = $formField.attr('data-optional-form-field');
				if ( optionalityList.includes( formFieldSlug ) ) {
					$formField.show();
				} else {
					$formField.hide();
				}
			});
		},
		getLocale: function () {
			if ( window.i18nLocale ) {
				return window.i18nLocale;
			} else {
				return window.i18nLocaleSettings;
			}
		},
		toggleCopyButton: function () {
			var $copyButton = this.$el.find( '.wpb-copy-output' );
			if ( this.$generated_content.val() ) {
				$copyButton.removeClass('disabled');
			} else {
				$copyButton.addClass('disabled');
			}
		},
		copyContent: function ( e ) {
			e.preventDefault();
			var content = this.$generated_content.val();
			if ( !content ) {
				return false;
			}
			try {
				window.vc.utils.copyTextToClipboard( content );
				vc.showMessage( this.getLocale().copied, 'success', 2000, '#vc_ui-helper-modal-ai .vc_ui-panel-window-inner' );
			} catch ( error ) {
				console.error( 'Unable to copy content:', error );
			}
		}
	});
})( window.jQuery );
