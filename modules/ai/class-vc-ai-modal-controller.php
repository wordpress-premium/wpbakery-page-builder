<?php
/**
 * AI modal controller.
 *
 * @since 7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class respond for AI modal interaction.
 *
 * @since 7.2
 */
class Vc_Ai_Modal_Controller {
	/**
	 * Credits limit per a site.
	 * we use it value only if we do not have response value.
	 *
	 * @since 7.2
	 * @var int
	 */
	public $credits_limit;

	/**
	 * Get AI modal data.
	 *
	 * @since 7.2
	 * @param array $modal_param
	 * @return array
	 */
	public function get_modal_data( $modal_param ) {
		$response['type'] = 'promo';
		if ( ! vc_license()->isActivated() ) {
			$response['content'] =
				$this->get_ai_promo_template( 'happy', 'access-ai', $modal_param );
			return $response;
		}

		$api_connector = $this->set_api_connector_with_response_status( $modal_param );
		$access_status = $this->get_access_ai_api_response_status( $api_connector->api_response_data );

		if ( is_wp_error( $access_status ) ) {
			$response['content'] =
				$this->get_ai_promo_template(
					'sad',
					'custom',
					$modal_param,
					$access_status->get_error_message()
				);
			return $response;
		}

		switch ( $access_status ) {
			case 'license_not_valid':
				$response['content'] =
					$this->get_ai_promo_template( 'happy', 'access-ai', $modal_param );
				break;
			case 'credits_expired':
				$response['content'] =
					$this->get_ai_promo_template( 'sad', 'more-credits', $modal_param );
				break;
			default:
				$response['type'] = 'content';
				$response['content'] = $this->get_ai_form_template( $modal_param );
				$response['tokens_left'] = $api_connector->api_response_data['tokens-left'];
				$response['tokens_total'] = $api_connector->api_response_data['tokens-total'];
		}

		return $response;
	}

	/**
	 * Set API connector with response status.
	 *
	 * @since 7.8
	 * @param array $data
	 * @return Vc_Ai_Api_Connector
	 */
	public function set_api_connector_with_response_status( $data ) {
		require_once vc_path_dir( 'MODULES_DIR', 'ai/class-vc-ai-api-connector.php' );

		$api_connector = new Vc_Ai_Api_Connector();
		$data = $api_connector->add_license_key_to_request_data( $data );
		return $api_connector->set_api_response_data( $data, 'status' );
	}

	/**
	 * Get token usage request.
	 *
	 * @since 7.7
	 */
	public function get_token_usage_request() {
		if ( ! vc_license()->isActivated() ) {
			return new WP_Error(
				'ai_error_token_usage_license_not_active',
				esc_html__( 'Credit usage update error (Code: 621): license not active', 'js_composer' )
			);
		}

		$api_connector = $this->set_api_connector_with_response_status( [] );

		$access_status = $this->get_access_ai_api_response_status( $api_connector->api_response_data );
		if ( is_wp_error( $access_status ) ) {
			return $access_status;
		}

		switch ( $access_status ) {
			case 'license_not_valid':
				$response = new WP_Error(
					'ai_error_token_usage_license_not_active',
					esc_html__( 'Credit usage update error (Code: 621): license not active', 'js_composer' )
				);
				break;
			case 'credits_expired':
				$response['tokens_left'] = 0;
				$response['tokens_total'] = $this->credits_limit;
				break;
			default:
				$response['tokens_left'] = $api_connector->api_response_data['tokens-left'];
				$response['tokens_total'] = $api_connector->api_response_data['tokens-total'];
		}

		return $response;
	}

	/**
	 * Get AI promo template.
	 *
	 * @since 7.7
	 * @param string $logo_type
	 * @param string $message_template
	 * @param array $modal_param
	 * @param string $error_message
	 * @return string
	 */
	public function get_ai_promo_template( $logo_type, $message_template, $modal_param, $error_message = '' ) {
		$params = [
			'logo_template_path' => 'editors/popups/ai/' . $logo_type . '-ai-logo.tpl.php',
			'message_template_path' => 'editors/popups/ai/message-modal-' . $message_template . '.tpl.php',
			'modal_controller' => $this,
			'modal_param' => $modal_param,
		];

		if ( $error_message ) {
			$params['error_message'] = $error_message;
		}

		if ( empty( $modal_param['is_settings_page'] ) ) {
			return vc_get_template(
				'editors/popups/ai/promo-modal.tpl.php',
				$params
			);
		}
		return vc_get_template(
			'editors/popups/ai/promo-settings.tpl.php',
			$params
		);
	}

	/**
	 * Get AI form template.
	 *
	 * @since 7.2
	 * @param array $data
	 * @return string|WP_Error
	 */
	public function get_ai_form_template( $data ) {
		if ( ! is_string( $data['ai_element_type'] ) || ! is_string( $data['ai_element_id'] ) ) {
			return new WP_Error(
				'ai_error_invalid_user_data',
				esc_html__( 'An error occurred when requesting a response from WPBakery AI (Code: 620): wrong api response format', 'js_composer' )
			);
		}

		$element_form_fields_template_path =
			$this->get_modal_template_path( $data['ai_element_type'], $data['ai_element_id'] );

		if ( is_wp_error( $element_form_fields_template_path ) ) {
			return $element_form_fields_template_path;
		}

		return vc_get_template(
			'editors/popups/ai/generate-form.tpl.php',
			[
				'element_form_fields_template_path' => $element_form_fields_template_path,
				'ai_element_type' => $data['ai_element_type'],
				'ai_element_id' => $data['ai_element_id'],
				'ai_modal_controller' => $this,
			]
		);
	}

	/**
	 * Get access status from AI API response.
	 *
	 * @since 7.7
	 * @param string | WP_Error $response
	 * @return string | WP_Error
	 */
	public function get_access_ai_api_response_status( $response ) {
		if ( ! is_wp_error( $response ) ) {
			return 'success';
		}
		if ( ! isset( $response->errors['ai_error_response'][0] ) ) {
			return $response;
		}

		$message = $response->errors['ai_error_response'][0];

		if ( strpos( $message, 'license has expired' ) !== false ) {
			$response = 'license_not_valid';
			// user disabled it on a support portal, but still has in options.
		} elseif ( strpos( $message, 'WPBakery Page Builder license not activated' ) !== false ) {
			$response = 'license_not_valid';
		} elseif ( strpos( $message, 'reached your monthly limit' ) !== false ) {
			preg_match( '/free (\d+) WPBakery/', $message, $matches );

			if ( isset( $matches[1] ) ) {
				$this->credits_limit = (int) $matches[1];
			}

			$response = 'credits_expired';
		}

		return $response;
	}

	/**
	 * Get AI modal template path.
	 *
	 * @since 7.2
	 * @param string $ai_element_type
	 * @param string $ai_element_id
	 * @return string | WP_Error
	 */
	public function get_modal_template_path( $ai_element_type, $ai_element_id ) {
		$template_list = $this->get_modal_type_of_template_dependency_list();
		if ( ! is_array( $template_list ) ) {
			return new WP_Error(
				'ai_error_type_of_template_dependency_list_data',
				esc_html__( 'An error occurred when requesting a response from WPBakery AI (Code: 621): template file missing', 'js_composer' )
			);
		}

		if ( ! array_key_exists( $ai_element_type, $template_list ) ) {
			return new WP_Error(
				'ai_error_type_of_template_dependency_list_do_not_has_template',
				esc_html__( 'An error occurred when requesting a response from WPBakery AI (Code: 622): template file missing', 'js_composer' )
			);
		}

		$template_path = $this->get_modal_template_path_from_list_dependency( $ai_element_type, $ai_element_id, $template_list );
		if ( ! file_exists( vc_template( $template_path ) ) ) {
			return new WP_Error(
				'ai_error_type_of_template_dependency_list_do_not_has_template',
				esc_html__( 'An error occurred when requesting a response from WPBakery AI (Code: 622): file template does not exist', 'js_composer' )
			);
		}

		return $template_path;
	}

	/**
	 * Get AI modal type of template dependency list.
	 *
	 * @since 7.2
	 * @return mixed
	 */
	public function get_modal_type_of_template_dependency_list() {
		$type_dependency = [
			'textarea_html' => 'editors/popups/ai/generate-text.php',
			'textarea' => 'editors/popups/ai/generate-text.php',
			'textarea_raw_html' => [
				'default' => 'editors/popups/ai/generate-text.php',
				'textarea_raw_html_javascript_code' => 'editors/popups/ai/generate-code.php',
			],
			'textfield' => 'editors/popups/ai/generate-text.php',
			'custom_css' => 'editors/popups/ai/generate-code.php',
			'custom_js' => 'editors/popups/ai/generate-code.php',
		];

		return apply_filters( 'wpb_ai_modal_type_dependency', $type_dependency );
	}

	/**
	 * Get modal template path from list dependency.
	 *
	 * @since 7.2
	 * @param string $ai_element_type
	 * @param string $ai_element_id
	 * @param array $template_list
	 * @return string
	 */
	public function get_modal_template_path_from_list_dependency( $ai_element_type, $ai_element_id, $template_list ) {
		if ( is_string( $template_list[ $ai_element_type ] ) ) {
			return $template_list[ $ai_element_type ];
		}

		if ( ! empty( $template_list[ $ai_element_type ][ $ai_element_id ] ) ) {
			$template_path = $template_list[ $ai_element_type ][ $ai_element_id ];
		} elseif ( ! empty( $template_list[ $ai_element_type ]['default'] ) ) {
			$template_path = $template_list[ $ai_element_type ]['default'];
		} else {
			$template_path = '';
		}

		return $template_path;
	}

	/**
	 * Get tone of voice options.
	 *
	 * @since 7.2
	 * @return array
	 */
	public function get_ton_of_voice_list() {
		$list = apply_filters(
			'wpb_ai_tone_of_voice_list',
			[
				'approachable' => esc_html__( 'Approachable', 'js_composer' ),
				'excited' => esc_html__( 'Excited', 'js_composer' ),
				'playful' => esc_html__( 'Playful', 'js_composer' ),
				'assertive' => esc_html__( 'Assertive', 'js_composer' ),
				'formal' => esc_html__( 'Formal', 'js_composer' ),
				'poetic' => esc_html__( 'Poetic', 'js_composer' ),
				'bold' => esc_html__( 'Bold', 'js_composer' ),
				'friendly' => esc_html__( 'Friendly', 'js_composer' ),
				'positive' => esc_html__( 'Positive', 'js_composer' ),
				'candid' => esc_html__( 'Candid', 'js_composer' ),
				'funny' => esc_html__( 'Funny', 'js_composer' ),
				'powerful' => esc_html__( 'Powerful', 'js_composer' ),
				'caring' => esc_html__( 'Caring', 'js_composer' ),
				'gentle' => esc_html__( 'Gentle', 'js_composer' ),
				'professional' => esc_html__( 'Professional', 'js_composer' ),
				'casual' => esc_html__( 'Casual', 'js_composer' ),
				'helpful' => esc_html__( 'Helpful', 'js_composer' ),
				'quirky' => esc_html__( 'Quirky', 'js_composer' ),
				'cheerful' => esc_html__( 'Cheerful', 'js_composer' ),
				'hopeful' => esc_html__( 'Hopeful', 'js_composer' ),
				'reassuring' => esc_html__( 'Reassuring', 'js_composer' ),
				'clear' => esc_html__( 'Clear', 'js_composer' ),
				'humorous' => esc_html__( 'Humorous', 'js_composer' ),
				'reflective' => esc_html__( 'Reflective', 'js_composer' ),
				'commanding' => esc_html__( 'Commanding', 'js_composer' ),
				'informal' => esc_html__( 'Informal', 'js_composer' ),
				'respectful' => esc_html__( 'Respectful', 'js_composer' ),
				'comprehensive' => esc_html__( 'Comprehensive', 'js_composer' ),
				'informative' => esc_html__( 'Informative', 'js_composer' ),
				'romantic' => esc_html__( 'Romantic', 'js_composer' ),
				'confident' => esc_html__( 'Confident', 'js_composer' ),
				'inspirational' => esc_html__( 'Inspirational', 'js_composer' ),
				'sarcastic' => esc_html__( 'Sarcastic', 'js_composer' ),
				'conversational' => esc_html__( 'Conversational', 'js_composer' ),
				'inspiring' => esc_html__( 'Inspiring', 'js_composer' ),
				'scientific' => esc_html__( 'Scientific', 'js_composer' ),
				'curious' => esc_html__( 'Curious', 'js_composer' ),
				'lively' => esc_html__( 'Lively', 'js_composer' ),
				'serious' => esc_html__( 'Serious', 'js_composer' ),
				'detailed' => esc_html__( 'Detailed', 'js_composer' ),
				'melancholic' => esc_html__( 'Melancholic', 'js_composer' ),
				'technical' => esc_html__( 'Technical', 'js_composer' ),
				'educational' => esc_html__( 'Educational', 'js_composer' ),
				'motivational' => esc_html__( 'Motivational', 'js_composer' ),
				'thought-provoking' => esc_html__( 'Thought-provoking', 'js_composer' ),
				'eloquent' => esc_html__( 'Eloquent', 'js_composer' ),
				'negative' => esc_html__( 'Negative', 'js_composer' ),
				'thoughtful' => esc_html__( 'Thoughtful', 'js_composer' ),
				'emotional' => esc_html__( 'Emotional', 'js_composer' ),
				'neutral' => esc_html__( 'Neutral', 'js_composer' ),
				'uplifting' => esc_html__( 'Uplifting', 'js_composer' ),
				'empathetic' => esc_html__( 'Empathetic', 'js_composer' ),
				'nostalgic' => esc_html__( 'Nostalgic', 'js_composer' ),
				'urgent' => esc_html__( 'Urgent', 'js_composer' ),
				'empowering' => esc_html__( 'Empowering', 'js_composer' ),
				'offbeat' => esc_html__( 'Offbeat', 'js_composer' ),
				'vibrant' => esc_html__( 'Vibrant', 'js_composer' ),
				'encouraging' => esc_html__( 'Encouraging', 'js_composer' ),
				'passionate' => esc_html__( 'Passionate', 'js_composer' ),
				'visionary' => esc_html__( 'Visionary', 'js_composer' ),
				'engaging' => esc_html__( 'Engaging', 'js_composer' ),
				'personal' => esc_html__( 'Personal', 'js_composer' ),
				'witty' => esc_html__( 'Witty', 'js_composer' ),
				'enthusiastic' => esc_html__( 'Enthusiastic', 'js_composer' ),
				'persuasive' => esc_html__( 'Persuasive', 'js_composer' ),
				'zealous' => esc_html__( 'Zealous', 'js_composer' ),
			]
		);

		$list = is_array( $list ) ? $list : [];
		asort( $list );

		return $list;
	}

	/**
	 * Get number of symbols options.
	 *
	 * @since 7.2
	 * @param string $ai_element_type
	 * @return array
	 */
	public function get_number_of_symbols_list( $ai_element_type ) {
		$list = apply_filters(
			'wpb_ai_number_of_symbols_list',
			[
				'textarea_html' => [
					'[10,15]' => 'Title (up to 15 words)',
					'[15,25]' => 'Short description (up to 25 words)',
					'[20,50]' => 'Description (up to 50 words)',
					'[200,300]' => 'Long description (up to 300 words)',
					'[400,600]' => 'Short article (up to 600 words)',
					'[800,1200]' => 'Long article (800 - 1200 words)',
				],
				'textarea_raw_html' => [
					'[10,15]' => 'Title (up to 15 words)',
					'[15,25]' => 'Short description (up to 25 words)',
					'[20,50]' => 'Description (up to 50 words)',
					'[200,300]' => 'Long description (up to 300 words)',
					'[400,600]' => 'Short article (up to 600 words)',
					'[800,1200]' => 'Long article (800 - 1200 words)',
				],
				'textarea' => [
					'[10,15]' => 'Title (up to 15 words)',
					'[15,25]' => 'Short description (up to 25 words)',
					'[20,50]' => 'Description (up to 50 words)',
					'[200,300]' => 'Long description (up to 300 words)',
				],
				'textfield' => [
					'[10,15]' => 'Title (up to 15 words)',
					'[15,25]' => 'Short description (up to 25 words)',
				],
			]
		);

		if (
			! is_array( $list ) ||
			! is_array( $list[ $ai_element_type ] ) ||
			! array_key_exists( $ai_element_type, $list ) ) {

			$list = [];
		}

		return $list[ $ai_element_type ];
	}

	/**
	 * Get content type options.
	 *
	 * @since 7.2
	 * @return array
	 */
	public function get_content_generate_variant() {
		$content = apply_filters(
			'wpb_ai_content_type_list',
			[
				'new_content' => esc_html__( 'New content', 'js_composer' ),
				'improve_existing' => esc_html__( 'Improve existing', 'js_composer' ),
				'translate' => esc_html__( 'Translate', 'js_composer' ),
			]
		);

		return is_array( $content ) ? $content : [];
	}

	/**
	 * Get content type form fields optionality.
	 *
	 * @since 7.2
	 * @return array
	 */
	public function get_content_type_form_fields_optionality() {
		$optionality = apply_filters(
			'wpb_ai_form_fields_optionality_content_type',
			[
				'new_content' => [
					'contentType',
					'prompt',
					'toneOfVoice',
					'length',
					'keyWords',
				],
				'improve_existing' => [
					'contentType',
					'toneOfVoice',
					'keyWords',
				],
				'translate' => [
					'contentType',
					'language',
				],
			]
		);

		return is_array( $optionality ) ? $optionality : [];
	}

	/**
	 * Output data attribute for some form fields optionality.
	 *
	 * @since 7.2
	 * @param string $field_slug
	 * @param string $optionality_field_slug
	 */
	public function output_optionality_data_attr( $field_slug, $optionality_field_slug ) {
		$output = '';

		if ( 'content_type' === $field_slug ) {
			$optionality = $this->get_content_type_form_fields_optionality();
			if ( array_key_exists( $optionality_field_slug, $optionality ) ) {
				$output = esc_attr( implode( '|', $optionality[ $optionality_field_slug ] ) );
			}
		}

		return ' data-form-fields-optionality="' . $output . '"';
	}

	/**
	 * Get languages list.
	 *
	 * @since 7.2
	 * @return array
	 */
	public function get_languages_list() {
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';

		$language_list = [];
		$translation_list = wp_get_available_translations();

		foreach ( $translation_list as $language_data ) {
			$language_list[] = $language_data['english_name'];
		}

		asort( $language_list );

		return $language_list;
	}
}
