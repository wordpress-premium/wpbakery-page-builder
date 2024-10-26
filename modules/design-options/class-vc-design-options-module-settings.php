<?php
/**
 * Module Settings.
 *
 * @since 7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Module settings.
 *
 * @since 7.7
 */
class Vc_Design_Options_Module_Settings {

	/**
	 * Init point.
	 *
	 * @since 7.7
	 */
	public function init() {
		add_filter( 'vc_settings_tabs', [ $this, 'set_setting_tab' ], 11 );

		add_action( 'vc_settings_set_sections', [ $this, 'add_settings_section' ] );

		add_action( 'update_option_wpb_js_compiled_js_composer_less', array(
			$this,
			'build_custom_color_css',
		) );

		add_action( 'add_option_wpb_js_compiled_js_composer_less', array(
			$this,
			'build_custom_color_css',
		) );

		add_action( 'add_option_wpb_js_use_custom', array(
			$this,
			'build_custom_color_css',
		) );

		add_action( 'update_option_wpb_js_use_custom', array(
			$this,
			'build_custom_color_css',
		) );

		add_action( 'vc_after_init', [ $this, 'restore_default' ] );

		add_action( 'vc_before_init', [ $this, 'check_for_custom_css_build' ] );

		add_action( 'vc-settings-render-tab-vc-color', [ $this, 'load_module_settings_assets' ] );
	}

	/**
	 * Function check is system has custom-built css
	 *  and check it version in comparison with current VC version
	 *
	 * @since 7.7
	 */
	public function check_for_custom_css_build() {
		$version = $this->get_custom_css_version();
		if ( vc_user_access()->wpAny( 'manage_options' )->part( 'settings' )->can( 'vc-color-tab' )->get() && $this->use_custom_css() && ( ! $version || version_compare( WPB_VC_VERSION, $version, '<>' ) ) ) {
			add_action( 'admin_notices', [ $this, 'custom_css_admin_notice' ] );
		}
	}

	/**
	 * Display admin notice about outdated custom css
	 *
	 * @since 7.7
	 */
	public function custom_css_admin_notice() {
		global $current_screen;
		vc_settings()->set( 'compiled_js_composer_less', '' );
		$class = 'notice notice-warning vc_settings-custom-design-notice';
		$message_important = esc_html__( 'Important notice', 'js_composer' );
		$message = esc_html__( 'You have an outdated version of WPBakery Page Builder Design Options. It is required to review and save it.', 'js_composer' );
		if ( is_object( $current_screen ) && isset( $current_screen->id ) && 'wpbakery-page-builder_page_vc-color' === $current_screen->id ) {
			echo '<div class="' . esc_attr( $class ) . '"><p><strong>' . esc_html( $message_important ) . '</strong>: ' . esc_html( $message ) . '</p></div>';
		} else {
			$btn_class = 'button button-primary button-large vc_button-settings-less';
			echo '<div class="' . esc_attr( $class ) . '"><p><strong>' . esc_html( $message_important ) . '</strong>: ' . esc_html( $message ) . '</p><p>';
			echo '<a ' . implode( ' ', array(
				'href="' . esc_url( admin_url( 'admin.php?page=vc-color' ) ) . '"',
				'class="' . esc_attr( $btn_class ) . '"',
				'id="vc_less-save-button"',
				'style="vertical-align: baseline;"',
					// needed to fix ":active bug".
			) ) . '>';
			echo esc_html__( 'Open Design Options', 'js_composer' ) . '</a>';
			echo '</p></div>';
		}
	}

	/**
	 * Attributes for colors.
	 *
	 * @since 7.7
	 *
	 * @param array $submit_button_attributes
	 * @return mixed
	 */
	public function page_settings_tab_color_submit_attributes( $submit_button_attributes ) {
		$submit_button_attributes['data-vc-less-path'] = vc_str_remove_protocol( vc_asset_url( 'less/js_composer.less' ) );
		$submit_button_attributes['data-vc-less-root'] = vc_str_remove_protocol( vc_asset_url( 'less' ) );
        // phpcs:ignore:WordPress.NamingConventions.ValidHookName.UseUnderscores
		$submit_button_attributes['data-vc-less-variables'] = wp_json_encode( apply_filters( 'vc_settings-less-variables', array(
			// Main accent color.
			'vc_grey' => array(
				'key' => 'wpb_js_vc_color',
				'default' => $this->get_default( 'vc_color' ),
			),
			// Hover color.
			'vc_grey_hover' => array(
				'key' => 'wpb_js_vc_color_hover',
				'default' => $this->get_default( 'vc_color_hover' ),
			),
			'vc_image_slider_link_active' => 'wpb_js_vc_color_hover',
			// Call to action background color.
			'vc_call_to_action_bg' => 'wpb_js_vc_color_call_to_action_bg',
			'vc_call_to_action_2_bg' => 'wpb_js_vc_color_call_to_action_bg',
			'vc_call_to_action_border' => array(
				'key' => 'wpb_js_vc_color_call_to_action_border',
				// darken 5%.
				'default_key' => 'wpb_js_vc_color',
				'modify_output' => array(
					array(
						'plain' => array(
							'darken({{ value }}, 5%)',
						),
					),
				),
			),
			// Google Maps background color.
			'vc_google_maps_bg' => 'wpb_js_vc_color_google_maps_bg',
			// Post slider caption background color.
			'vc_post_slider_caption_bg' => 'wpb_js_vc_color_post_slider_caption_bg',
			// Progress bar background color.
			'vc_progress_bar_bg' => 'wpb_js_vc_color_progress_bar_bg',
			// Separator border color.
			'vc_separator_border' => 'wpb_js_vc_color_separator_border',
			// Tabs navigation background color.
			'vc_tab_bg' => 'wpb_js_vc_color_tab_bg',
			// Active tab background color.
			'vc_tab_bg_active' => 'wpb_js_vc_color_tab_bg_active',
			// Elements bottom margin.
			'vc_element_margin_bottom' => array(
				'key' => 'wpb_js_margin',
				'default' => $this->get_default( 'margin' ),
			),
			// Grid gutter width.
			'grid-gutter-width' => array(
				'key' => 'wpb_js_gutter',
				'default' => $this->get_default( 'gutter' ),
				'modify_output' => array(
					array(
						'plain' => array(
							'{{ value }}px',
						),
					),
				),
			),
			'screen-sm-min' => array(
				'key' => 'wpb_js_responsive_max',
				'default' => $this->get_default( 'responsive_max' ),
				'modify_output' => array(
					array(
						'plain' => array(
							'{{ value }}px',
						),
					),
				),
			),
			'screen-md-min' => array(
				'key' => 'wpb_js_responsive_md',
				'default' => $this->get_default( 'responsive_md' ),
				'modify_output' => array(
					array(
						'plain' => array(
							'{{ value }}px',
						),
					),
				),
			),
			'screen-lg-min' => array(
				'key' => 'wpb_js_responsive_lg',
				'default' => $this->get_default( 'responsive_lg' ),
				'modify_output' => array(
					array(
						'plain' => array(
							'{{ value }}px',
						),
					),
				),
			),
		) ) );

		return $submit_button_attributes;
	}

	/**
	 * Get color settings.
	 *
	 * @since 7.7
	 * @return array
	 */
	public function get_color_settings() {
		return array(
			array( 'vc_color' => array( 'title' => esc_html__( 'Main accent color', 'js_composer' ) ) ),
			array( 'vc_color_hover' => array( 'title' => esc_html__( 'Hover color', 'js_composer' ) ) ),
			array( 'vc_color_call_to_action_bg' => array( 'title' => esc_html__( 'Call to action background color', 'js_composer' ) ) ),
			array( 'vc_color_google_maps_bg' => array( 'title' => esc_html__( 'Google maps background color', 'js_composer' ) ) ),
			array( 'vc_color_post_slider_caption_bg' => array( 'title' => esc_html__( 'Post slider caption background color', 'js_composer' ) ) ),
			array( 'vc_color_progress_bar_bg' => array( 'title' => esc_html__( 'Progress bar background color', 'js_composer' ) ) ),
			array( 'vc_color_separator_border' => array( 'title' => esc_html__( 'Separator border color', 'js_composer' ) ) ),
			array( 'vc_color_tab_bg' => array( 'title' => esc_html__( 'Tabs navigation background color', 'js_composer' ) ) ),
			array( 'vc_color_tab_bg_active' => array( 'title' => esc_html__( 'Active tab background color', 'js_composer' ) ) ),
		);
	}

	/**
	 * Get default color settings.
	 *
	 * @since 7.7
	 * @return array
	 */
	public function get_default_color_settings() {
		return array(
			'vc_color' => '#f7f7f7',
			'vc_color_hover' => '#F0F0F0',
			'margin' => '35px',
			'gutter' => '15',
			'responsive_max' => '768',
			'responsive_md' => '992',
			'responsive_lg' => '1200',
			'compiled_js_composer_less' => '',
		);
	}

	/**
	 * Add module tab to settings.
	 *
	 * @since 7.7
	 *
	 * @param array $tabs
	 * @return array
	 */
	public function set_setting_tab( $tabs ) {
		if ( vc_settings()->showConfigurationTabs() ) {
			if ( ! vc_is_as_theme() || apply_filters( 'vc_settings_page_show_design_tabs', false ) ) {
				$tabs['vc-color'] = esc_html__( 'Design Options', 'js_composer' );
			}
		}

		return $tabs;
	}

	/**
	 * Create css file with custom colors.
	 *
	 * @since 7.7
	 */
	public function build_custom_color_css() {
		// Filesystem API init.
		$settings = vc_settings();
		$url = wp_nonce_url( 'admin.php?page=vc-color&build_css=1', 'wpb_js_settings_save_action' );
		$settings::getFileSystem( $url );
		// WP_Filesystem_Direct $wp_filesystem.
		global $wp_filesystem;
		// Building css file.
		$js_composer_upload_dir = $settings::checkCreateUploadDir( $wp_filesystem, 'use_custom', 'js_composer_front_custom.css' );
		if ( ! $js_composer_upload_dir ) {
			return;
		}

		$filename = $js_composer_upload_dir . '/js_composer_front_custom.css';
		$use_custom = get_option( $settings::$field_prefix . 'use_custom' );
		if ( ! $use_custom ) {
			$wp_filesystem->put_contents( $filename, '', FS_CHMOD_FILE );

			return;
		}
		$css_string = get_option( $settings::$field_prefix . 'compiled_js_composer_less' );
		if ( strlen( trim( $css_string ) ) > 0 ) {
			update_option( $settings::$field_prefix . 'less_version', WPB_VC_VERSION );
			delete_option( $settings::$field_prefix . 'compiled_js_composer_less' );
			$css_string = wp_strip_all_tags( $css_string );
			// HERE goes the magic.
			if ( ! $wp_filesystem->put_contents( $filename, $css_string, FS_CHMOD_FILE ) ) {
				if ( is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
					add_settings_error( $settings::$field_prefix . 'main_color', $wp_filesystem->errors->get_error_code(), esc_html__( 'Something went wrong: js_composer_front_custom.css could not be created.', 'js_composer' ) . ' ' . $wp_filesystem->errors->get_error_message() );
				} elseif ( ! $wp_filesystem->connect() ) {
					add_settings_error( $settings::$field_prefix . 'main_color', $wp_filesystem->errors->get_error_code(), esc_html__( 'js_composer_front_custom.css could not be created. Connection error.', 'js_composer' ) );
				} elseif ( ! $wp_filesystem->is_writable( $filename ) ) {
					add_settings_error( $settings::$field_prefix . 'main_color', $wp_filesystem->errors->get_error_code(), sprintf( esc_html__( 'js_composer_front_custom.css could not be created. Cannot write custom css to "%s".', 'js_composer' ), $filename ) );
				} else {
					add_settings_error( $settings::$field_prefix . 'main_color', $wp_filesystem->errors->get_error_code(), esc_html__( 'js_composer_front_custom.css could not be created. Problem with access.', 'js_composer' ) );
				}
				delete_option( $settings::$field_prefix . 'use_custom' );
				delete_option( $settings::$field_prefix . 'less_version' );
			}
		}
	}

	/**
	 * Add sections to plugin settings tab.
	 *
	 * @since 7.7
	 */
	public function add_settings_section() {
		$tab = 'color';
		$settings = vc_settings();
		$settings->addSection( $tab );

		// Use custom checkbox.
		$settings->addField( $tab, esc_html__( 'Use custom design options', 'js_composer' ), 'use_custom', array(
			$this,
			'sanitize_use_custom_callback',
		), array(
			$this,
			'use_custom_callback',
		), array(
			'info' => esc_html__( 'Enable the use of custom design options (Note: when checked - custom css file will be used).', 'js_composer' ),
		) );

		foreach ( $this->get_color_settings() as $color_set ) {
			foreach ( $color_set as $key => $data ) {
				$settings->addField( $tab, $data['title'], $key, array(
					$this,
					'sanitize_color_callback',
				), array(
					$this,
					'color_callback',
				), array(
					'id' => $key,
				) );
			}
		}

		// Margin.
		$settings->addField( $tab, esc_html__( 'Elements bottom margin', 'js_composer' ), 'margin', array(
			$this,
			'sanitize_margin_callback',
		), array(
			$this,
			'margin_callback',
		), array(
			'info' => esc_html__( 'Change default vertical spacing between content elements (Example: 20px).', 'js_composer' ),
		) );

		// Gutter.
		$settings->addField( $tab, esc_html__( 'Grid gutter width', 'js_composer' ), 'gutter', array(
			$this,
			'sanitize_gutter_callback',
		), array(
			$this,
			'gutter_callback',
		), array(
			'info' => esc_html__( 'Change default horizontal spacing between columns, enter new value in pixels.', 'js_composer' ),
		) );

		// Responsive max width.
		$settings->addField( $tab, esc_html__( 'Mobile breakpoint', 'js_composer' ), 'responsive_max', array(
			$this,
			'sanitize_responsive_max_callback',
		), array(
			$this,
			'responsive_max_callback',
		), array(
			'info' => esc_html__( 'Content elements stack one on top other when the screen size is smaller than entered value. Change it to control when your layout stacks and adopts to a particular viewport or device size.', 'js_composer' ),
		) );
		$settings->addField( $tab, esc_html__( 'Desktop breakpoint', 'js_composer' ), 'responsive_md', array(
			$this,
			'sanitize_responsive_md_callback',
		), array(
			$this,
			'responsive_md_callback',
		), array(
			'info' => esc_html__( 'Content elements stack one on top other when the screen size is smaller than entered value. Change it to control when your layout stacks and adopts to a particular viewport or device size.', 'js_composer' ),
		) );
		$settings->addField( $tab, esc_html__( 'Large Desktop breakpoint', 'js_composer' ), 'responsive_lg', array(
			$this,
			'sanitize_responsive_lg_callback',
		), array(
			$this,
			'responsive_lg_callback',
		), array(
			'info' => esc_html__( 'Content elements stack one on top other when the screen size is smaller than entered value. Change it to control when your layout stacks and adopts to a particular viewport or device size.', 'js_composer' ),
		) );
		$settings->addField( $tab, false, 'compiled_js_composer_less', array(
			$this,
			'sanitize_compiled_js_composer_less_callback',
		), array(
			$this,
			'compiled_js_composer_less_callback',
		) );
	}

	/**
	 * Filed output callback.
	 *
	 * @since 7.7
	 * @param array $args
	 */
	public function color_callback( $args ) {
		$field = $args['id'];
		$value = get_option( vc_settings()::$field_prefix . $field );
		$value = $value ?: $this->get_default( $field );
		echo '<div class="color-group"><div class="wpb-color-picker"></div><input type="text" name="' . esc_attr( vc_settings()::$field_prefix . $field ) . '" value="' . esc_attr( $value ) . '" class="vc_color-control css-control vc_ui-hidden"></div>';
	}

	/**
	 * Filed output callback.
	 *
	 * @since 7.7
	 */
	public function margin_callback() {
		$field = 'margin';
		$value = get_option( vc_settings()::$field_prefix . $field );
		$value = $value ?: $this->get_default( $field );
		echo '<input type="text" name="' . esc_attr( vc_settings()::$field_prefix . $field ) . '" value="' . esc_attr( $value ) . '" class="css-control">';
	}

	/**
	 * Filed output callback.
	 *
	 * @since 7.7
	 */
	public function gutter_callback() {
		$field = 'gutter';
		$value = get_option( vc_settings()::$field_prefix . $field );
		$value = $value ?: $this->get_default( $field );
		echo '<input type="text" name="' . esc_attr( vc_settings()::$field_prefix . $field ) . '" value="' . esc_attr( $value ) . '" class="css-control"> px';
	}

	/**
	 * Filed output callback.
	 *
	 * @since 7.7
	 */
	public function responsive_max_callback() {
		$field = 'responsive_max';
		$value = get_option( vc_settings()::$field_prefix . $field );
		$value = $value ?: $this->get_default( $field );
		echo '<input type="text" name="' . esc_attr( vc_settings()::$field_prefix . $field ) . '" value="' . esc_attr( $value ) . '" class="css-control"> px';
	}

	/**
	 * Filed output callback.
	 *
	 * @since 7.7
	 */
	public function responsive_md_callback() {
		$field = 'responsive_md';
		$value = get_option( vc_settings()::$field_prefix . $field );
		$value = $value ?: $this->get_default( $field );
		echo '<input type="text" name="' . esc_attr( vc_settings()::$field_prefix . $field ) . '" value="' . esc_attr( $value ) . '" class="css-control"> px';
	}

	/**
	 * Filed output callback.
	 *
	 * @since 7.7
	 */
	public function responsive_lg_callback() {
		$field = 'responsive_lg';
		$value = get_option( vc_settings()::$field_prefix . $field );
		$value = $value ?: $this->get_default( $field );
		echo '<input type="text" name="' . esc_attr( vc_settings()::$field_prefix . $field ) . '" value="' . esc_attr( $value ) . '" class="css-control"> px';
	}

	/**
	 * Filed output callback.
	 *
	 * @since 7.7
	 */
	public function compiled_js_composer_less_callback() {
		$field = 'compiled_js_composer_less';
		echo '<input type="hidden" name="' . esc_attr( vc_settings()::$field_prefix . $field ) . '" value="">'; // VALUE must be empty.
	}

	/**
	 * Sanitize use custom callback.
	 *
	 * @param string $key
	 * @since 7.7
	 * @return string
	 */
	public function get_default( $key ) {
		$default = $this->get_default_color_settings();
		return ! empty( $default[ $key ] ) ? $default[ $key ] : '';
	}

	/**
	 * Restore default color settings.
	 *
	 * @since 7.7
	 */
	public function restore_default() {
		$is_restore =
			'restore_color' === vc_post_param( 'vc_action' ) &&
			vc_user_access()->check( 'wp_verify_nonce', vc_post_param( '_wpnonce' ), vc_settings()->getOptionGroup() . '_color-options' )->validateDie()->wpAny( 'manage_options' )->validateDie()->part( 'settings' )->can( 'vc-color-tab' )->validateDie()->get();

		if ( $is_restore ) {
			$this->restore_color();
		}
	}

	/**
	 * Restore default module settings.
	 *
	 * @since 7.7
	 */
	public function restore_color() {
		$settings = vc_settings();
		foreach ( $this->get_color_settings() as $color_sett ) {
			foreach ( $color_sett as $key => $value ) {
				delete_option( $settings::$field_prefix . $key );
			}
		}
		delete_option( $settings::$field_prefix . 'margin' );
		delete_option( $settings::$field_prefix . 'gutter' );
		delete_option( $settings::$field_prefix . 'responsive_max' );
		delete_option( $settings::$field_prefix . 'responsive_md' );
		delete_option( $settings::$field_prefix . 'responsive_lg' );
		delete_option( $settings::$field_prefix . 'use_custom' );
		delete_option( $settings::$field_prefix . 'compiled_js_composer_less' );
		delete_option( $settings::$field_prefix . 'less_version' );
	}

	/**
	 * Not responsive checkbox callback function
	 *
	 * @since 7.7
	 */
	public function use_custom_callback() {
		$field = 'use_custom';
		$checked = get_option( vc_settings()::$field_prefix . $field );
		$checked = $checked ? $checked : false;
		?>
		<label>
			<input type="checkbox"<?php echo( $checked ? ' checked' : '' ); ?> value="1"
					id="wpb_js_<?php echo esc_attr( $field ); ?>" name="<?php echo esc_attr( vc_settings()::$field_prefix . $field ); ?>">
			<?php esc_html_e( 'Enable', 'js_composer' ); ?>
		</label>
		<?php
	}

	/**
	 * Sanitize use custom callback.
	 *
	 * @since 7.7
	 * @param mixed $rules
	 *
	 * @return bool
	 */
	public function sanitize_use_custom_callback( $rules ) {
		return (bool) $rules;
	}

	/**
	 * Sanitize use custom callback.
	 *
	 * @since 7.7
	 * @param mixed $css
	 *
	 * @return mixed
	 */
	public function sanitize_compiled_js_composer_less_callback( $css ) {
		return $css;
	}

	/**
	 * Sanitize use custom callback.
	 *
	 * @since 7.7
	 * @param mixed $color
	 *
	 * @return mixed
	 */
	public function sanitize_color_callback( $color ) {
		return $color;
	}

	/**
	 * Sanitize use custom callback.
	 *
	 * @since 7.7
	 * @param mixed $margin
	 *
	 * @return mixed
	 */
	public function sanitize_margin_callback( $margin ) {
		$margin = preg_replace( '/\s/', '', $margin );
		if ( ! preg_match( '/^\d+(px|%|em|pt){0,1}$/', $margin ) ) {
			add_settings_error( vc_settings()::$field_prefix . 'margin', 1, esc_html__( 'Invalid Margin value.', 'js_composer' ) );
		}

		return $margin;
	}

	/**
	 * Sanitize use custom callback.
	 *
	 * @since 7.7
	 * @param string $gutter
	 *
	 * @return mixed
	 */
	public function sanitize_gutter_callback( $gutter ) {
		$gutter = preg_replace( '/[^\d]/', '', $gutter );
		if ( ! $this->is_gutter_valid( $gutter ) ) {
			add_settings_error( vc_settings()::$field_prefix . 'gutter', 1, esc_html__( 'Invalid Gutter value.', 'js_composer' ) );
		}

		return $gutter;
	}

	/**
	 * Sanitize use custom callback.
	 *
	 * @since 7.7
	 * @param mixed $responsive_max
	 *
	 * @return mixed
	 */
	public function sanitize_responsive_max_callback( $responsive_max ) {
		if ( ! $this->is_number_valid( $responsive_max ) ) {
			add_settings_error( vc_settings()::$field_prefix . 'responsive_max', 1, esc_html__( 'Invalid "Responsive mobile" value.', 'js_composer' ) );
		}

		return $responsive_max;
	}

	/**
	 * Sanitize use custom callback.
	 *
	 * @since 7.7
	 * @param mixed $responsive_md
	 *
	 * @return mixed
	 */
	public function sanitize_responsive_md_callback( $responsive_md ) {
		if ( ! $this->is_number_valid( $responsive_md ) ) {
			add_settings_error( vc_settings()::$field_prefix . 'responsive_md', 1, esc_html__( 'Invalid "Responsive md" value.', 'js_composer' ) );
		}

		return $responsive_md;
	}

	/**
	 * Sanitize use custom callback.
	 *
	 * @since 7.7
	 * @param mixed $responsive_lg
	 *
	 * @return mixed
	 */
	public function sanitize_responsive_lg_callback( $responsive_lg ) {
		if ( ! $this->is_number_valid( $responsive_lg ) ) {
			add_settings_error( vc_settings()::$field_prefix . 'responsive_lg', 1, esc_html__( 'Invalid "Responsive lg" value.', 'js_composer' ) );
		}

		return $responsive_lg;
	}

	/**
	 * Validate number value.
	 *
	 * @since 7.7
	 * @param string $number
	 *
	 * @return int
	 */
	public static function is_number_valid( $number ) {
		return preg_match( '/^[\d]+(\.\d+){0,1}$/', $number );
	}

	/**
	 * Validate gutter value.
	 *
	 * @since 7.7
	 * @param string $gutter
	 *
	 * @return int
	 */
	public static function is_gutter_valid( $gutter ) {
		return self::is_number_valid( $gutter );
	}

	/**
	 * Get custom css file url.
	 *
	 * @since 7.7
	 * @return mixed|void
	 */
	public function use_custom_css() {
		return get_option( vc_settings()::$field_prefix . 'use_custom', false );
	}

	/**
	 * Get custom css version.
	 *
	 * @since 7.7
	 * @return mixed|void
	 */
	public function get_custom_css_version() {
		return get_option( vc_settings()::$field_prefix . 'less_version', false );
	}

	/**
	 * Load scripts that demand tab settings.
	 *
	 * @since 7.8
	 */
	public function load_module_settings_assets() {
		wp_enqueue_style( 'pickr', vc_asset_url( 'lib/vendor/node_modules/@simonwep/pickr/dist/themes/classic.min.css' ), array(), WPB_VC_VERSION );
		wp_enqueue_script( 'pickr', vc_asset_url( 'lib/vendor/node_modules/@simonwep/pickr/dist/pickr.es5.min.js' ), array(), WPB_VC_VERSION, true );

		add_filter( 'vc_settings-tab-submit-button-attributes-color', [ $this, 'page_settings_tab_color_submit_attributes' ] );
		wp_enqueue_script( 'vc_less_js', vc_asset_url( 'lib/vendor/node_modules/less/dist/less.min.js' ), array(), WPB_VC_VERSION, true );
		wp_enqueue_script( 'wpb_design_options_module', vc_asset_url( '../modules/design-options/assets/dist/module.min.js' ), array(), WPB_VC_VERSION, true );
		wp_enqueue_style( 'wpb_design_options_module', vc_asset_url( '../modules/design-options/assets/dist/module.min.css' ), false, WPB_VC_VERSION );
	}
}
