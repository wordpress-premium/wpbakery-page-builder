<?php
/**
 * Module Name: Automapper
 * Description: Automated shortcode mapping module.
 *
 * Module help user to map shortcodes to other shortcodes.
 * Automapper adds settings tab for VC settings tabs with ability to map custom shortcodes to VC editors,
 * if shortcode is not mapped by default or developers haven't done this yet.
 * No more shortcode copy/paste. Add any third party shortcode to the list of VC menu elements for reuse.
 * Edit params, values and description.
 *
 * @since 7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once vc_manager()->path( 'MODULES_DIR', 'automapper/class-vc-automap-model.php' );
require_once vc_manager()->path( 'MODULES_DIR', 'automapper/class-vc-automapper-module-settings.php' );

/**
 * Module entry point.
 *
 * @since 7.7
 */
class Vc_Automapper {

	/**
	 * Is automapper disabled.
	 *
	 * @depreacted since 7.7
	 * @var bool
	 */
	protected static $disabled = false;

	/**
	 * Settings object.
	 *
	 * @since 7.7
	 * @var Vc_Automapper_Module_Settings
	 */
	public $settings;

	/**
	 * Suffix for shortcodes
	 *
	 * @var string
	 */
	public static $shortcode_suffix = '-vc-automapper-';

	/**
	 * Init module implementation.
	 *
	 * @since 7.7
	 */
	public function init() {

		$this->settings = new Vc_Automapper_Module_Settings();
		$this->settings->init();

		$this->add_ajax_actions();

		is_admin() && ( strpos( (string) vc_request_param( 'action' ), 'vc_automapper' ) !== false ||
			'vc-automapper' === vc_get_param( 'page' ) ) &&
		add_action( 'admin_init', [ $this, 'automapper_init' ] );

		add_action( 'vc_after_mapping', [
			$this,
			'map',
		] );

		add_filter( 'the_content', [
			$this,
			'clear_shortcode_suffix',
		], 10 );

		add_filter( 'vc_fe_render_shortcode', [
			$this,
			'clear_render_shortcode_suffix',
		] );

		add_filter( 'vc_clear_shortcode_suffix', [
			$this,
			'clear_output_shortcode_suffix',
		] );
	}

	/**
	 * Init automapper module.
	 *
	 * @since 7.7
	 */
	public function automapper_init() {
		if ( vc_user_access()->wpAny( 'manage_options' )->part( 'settings' )->can( 'vc-automapper-tab' )->get() ) {
			vc_automapper()->add_ajax_actions();
		}
	}

	/**
	 * Init ajax module implementation.
	 *
	 * @since 7.7
	 */
	public function add_ajax_actions() {
		add_action( 'wp_ajax_vc_automapper_create', array(
			$this,
			'create',
		) );
		add_action( 'wp_ajax_vc_automapper_read', array(
			$this,
			'read',
		) );
		add_action( 'wp_ajax_vc_automapper_update', array(
			$this,
			'update',
		) );
		add_action( 'wp_ajax_vc_automapper_delete', array(
			$this,
			'delete',
		) );

		return $this;
	}

	/**
	 * Builds html for Automapper CRUD like administration block
	 *
	 * @since 7.7
	 * @return bool
	 */
	public function render_html() {
		?>
		<div class="tab_intro">
			<p><?php esc_html_e( 'WPBakery Page Builder Shortcode Mapper adds custom 3rd party vendors shortcodes to the list of WPBakery Page Builder content elements menu (Note: to map shortcode it needs to be installed on site).', 'js_composer' ); ?></p>
		</div>
		<div class="vc_automapper-toolbar">
			<a href=javascript:;" class="button button-primary"
				id="vc_automapper-add-btn"><?php esc_html_e( 'Map Shortcode', 'js_composer' ); ?></a>
		</div>
		<ul class="vc_automapper-list">
		</ul>
		<?php $this->render_templates(); ?>
		<?php
		return true;
	}

	/**
	 * Render form for shortcode mapper.
	 *
	 * @since 7.7
	 */
	public function render_map_form_tpl() {
		$custom_tag = 'script'; // Maybe use html shadow dom or ajax response for templates.
		?>
		<<?php echo esc_attr( $custom_tag ); ?> type="text/html" id="vc_automapper-add-form-tpl">
		<label for="vc_atm-shortcode-string"
				class="vc_info"><?php esc_html_e( 'Shortcode string', 'js_composer' ); ?></label>

		<div class="vc_wrapper">
			<div class="vc_string">
				<input id="vc_atm-shortcode-string"
						placeholder="<?php esc_attr_e( 'Please enter valid shortcode', 'js_composer' ); ?>"
						type="text" class="vc_atm-string">
			</div>
			<div class="vc_buttons">
				<a href="#" id="vc_atm-parse-string"
					class="button button-primary vc_parse-btn"><?php esc_attr_e( 'Parse Shortcode', 'js_composer' ); ?></a>
				<a href="#" class="button vc_atm-cancel"><?php esc_attr_e( 'Cancel', 'js_composer' ); ?></a>
			</div>
		</div>
		<span
			class="description"><?php esc_html_e( 'Enter valid shortcode (Example: [my_shortcode first_param="first_param_value"]My shortcode content[/my_shortcode]).', 'js_composer' ); ?></span>
		</<?php echo esc_attr( $custom_tag ); ?>>
		<<?php echo esc_attr( $custom_tag ); ?> type="text/html" id="vc_automapper-item-complex-tpl">
		<div class="widget-top">
			<div class="widget-title-action">
				<button type="button" class="widget-action hide-if-no-js" aria-expanded="true">
					<span class="screen-reader-text"><?php esc_html_e( 'Edit widget: Search', 'js_composer' ); ?></span>
					<span class="toggle-indicator" aria-hidden="true"></span>
				</button>
			</div>
			<div class="widget-title"><h4>{{ name }}<span class="in-widget-title"></span></h4></div>
		</div>
		<div class="widget-inside">
		</div>
		</<?php echo esc_attr( $custom_tag ); ?>>
		<<?php echo esc_attr( $custom_tag ); ?> type="text/html" id="vc_automapper-form-tpl">
		<input type="hidden" name="name" id="vc_atm-name" value="{{ name }}">

		<div class="vc_shortcode-preview" id="vc_shortcode-preview">
			{{{ shortcode_preview }}}
		</div>
		<div class="vc_line"></div>
		<div class="vc_wrapper">
			<h4 class="vc_h"><?php esc_html_e( 'General Information', 'js_composer' ); ?></h4>

			<div class="vc_field vc_tag">
				<label for="vc_atm-tag"><?php esc_html_e( 'Tag:', 'js_composer' ); ?></label>
				<input type="text" name="tag" id="vc_atm-tag" value="{{ tag }}">
			</div>
			<div class="vc_field vc_category">
				<div class="wpb_settings-title">
					<label for="vc_atm-category"><?php esc_html_e( 'Category:', 'js_composer' ); ?></label>
					<?php
					$category_info = vc_get_template( 'editors/partials/param-info.tpl.php', [ 'description' => esc_html__( 'Comma separated categories names', 'js_composer' ) ] );
					// phpcs:ignore
					if ( is_string( $category_info ) ) { echo $category_info; }
					?>
				</div>
				<input type="text" name="category" id="vc_atm-category" value="{{ category }}">
			</div>
			<div class="vc_field vc_description">
				<label for="vc_atm-description"><?php esc_html_e( 'Description:', 'js_composer' ); ?></label>
				<textarea name="description" id="vc_atm-description">{{ description }}</textarea>
			</div>
			<div class="vc_field vc_is-container">
				<label for="vc_atm-is-container"><input type="checkbox" name="is_container"
														id="vc_atm-is-container"
														value=""> <?php esc_html_e( 'Include content param into shortcode', 'js_composer' ); ?>
				</label>
			</div>
		</div>
		<div class="vc_line"></div>
		<div class="vc_wrapper">
			<h4 class="vc_h"><?php esc_html_e( 'Shortcode Parameters', 'js_composer' ); ?></h4>
			<a href="#" id="vc_atm-add-param"
				class="button vc_add-param">+ <?php esc_html_e( 'Add Param', 'js_composer' ); ?></a>

			<div class="vc_params" id="vc_atm-params-list"></div>
		</div>
		<div class="vc_buttons">
			<a href="#" id="vc_atm-save"
				class="button button-primary"><?php esc_html_e( 'Save Changes', 'js_composer' ); ?></a>
			<a href="#" class="button vc_atm-cancel"><?php esc_html_e( 'Cancel', 'js_composer' ); ?></a>
			<a href="#" class="button vc_atm-delete"><?php esc_html_e( 'Delete', 'js_composer' ); ?></a>
		</div>
		</<?php echo esc_attr( $custom_tag ); ?>>
		<<?php echo esc_attr( $custom_tag ); ?> type="text/html" id="vc_atm-form-param-tpl">
		<div class="vc_controls vc_controls-row vc_clearfix"><a
				class="vc_control column_move vc_column-move vc_move-param" href="#"
				title="<?php esc_html_e( 'Drag row to reorder', 'js_composer' ); ?>" data-vc-control="move"><i
					class="vc-composer-icon vc-c-icon-dragndrop"></i></a><span class="vc_row_edit_clone_delete"><a
					class="vc_control column_delete vc_delete-param" href="#"
					title="<?php esc_html_e( 'Delete this param', 'js_composer' ); ?>"><i class="vc-composer-icon vc-c-icon-delete_empty"></i></a></span>
		</div>
		<div class="wpb_element_wrapper">
			<div class="vc_row vc_row-fluid wpb_row_container">
				<div class="wpb_vc_column wpb_sortable vc_col-sm-12 wpb_content_holder vc_empty-column">
					<div class="wpb_element_wrapper">
						<div class="vc_fields vc_clearfix">
							<div class="vc_param_name vc_param-field">
								<div class="wpb_settings-title">
									<label><?php esc_html_e( 'Param name', 'js_composer' ); ?></label>
								<# if ( 'content' === param_name) { #>
									</div>
									<span class="vc_content"><?php esc_html_e( 'Content', 'js_composer' ); ?></span>
									<input type="text" style="display: none;" name="param_name"
										value="{{ param_name }}"
										placeholder="<?php esc_attr_e( 'Required value', 'js_composer' ); ?>"
										class="vc_param-name"
										data-system="true">
								<span class="description"
										style="display: none;"><?php esc_html_e( 'Use only letters, numbers and underscore.', 'js_composer' ); ?></span>
								<# } else { #>
								<?php
								$param_name_info = vc_get_template( 'editors/partials/param-info.tpl.php', [ 'description' => esc_html__( 'Please use only letters, numbers and underscore.', 'js_composer' ) ] );
								// phpcs:ignore
								if ( is_string( $param_name_info ) ) { echo $param_name_info; }
								?>
								</div>
								<input type="text" name="param_name" value="{{ param_name }}"
										placeholder="<?php esc_attr_e( 'Required value', 'js_composer' ); ?>"
										class="vc_param-name">
								<# } #>
							</div>
							<div class="vc_heading vc_param-field">
								<div class="wpb_settings-title">
									<label><?php esc_html_e( 'Heading', 'js_composer' ); ?></label>
									<?php
									$heading_info = vc_get_template( 'editors/partials/param-info.tpl.php', [ 'description' => esc_html__( 'Heading for field in shortcode edit form.', 'js_composer' ) ] );
									// phpcs:ignore
									if ( is_string( $heading_info ) ) { echo $heading_info; }
									?>
								</div>
								<input type="text" name="heading" value="{{ heading }}"
										placeholder="<?php esc_attr_e( 'Input heading', 'js_composer' ); ?>"
								<# if ( 'hidden' === type) { #>
								disabled="disabled"
								<# } #>>
							</div>
							<div class="vc_type vc_param-field">
								<div class="wpb_settings-title">
									<label><?php esc_html_e( 'Field type', 'js_composer' ); ?></label>
									<?php
									$field_type_info = vc_get_template( 'editors/partials/param-info.tpl.php', [ 'description' => esc_html__( 'Field type for shortcode edit form.', 'js_composer' ) ] );
									// phpcs:ignore
									if ( is_string( $field_type_info ) ) { echo $field_type_info; }
									?>
								</div>
								<select name="type">
									<option value=""><?php esc_html_e( 'Select field type', 'js_composer' ); ?></option>
									<option
										value="textfield"<?php echo '<# if (type === "textfield") { #> selected<# } #>'; ?>><?php esc_html_e( 'Textfield', 'js_composer' ); ?></option>
									<option
										value="dropdown"<?php echo '<# if (type === "dropdown") { #> selected<# } #>'; ?>><?php esc_html_e( 'Dropdown', 'js_composer' ); ?></option>
									<option
										value="textarea"<?php echo '<# if(type==="textarea") { #> selected="selected"<# } #>'; ?>><?php esc_html_e( 'Textarea', 'js_composer' ); ?></option>
									<# if ( 'content' === param_name ) { #>
									<option
										value="textarea_html"<?php echo '<# if (type === "textarea_html") { #> selected<# } #>'; ?>><?php esc_html_e( 'Textarea HTML', 'js_composer' ); ?></option>
									<# } #>
									<option
										value="hidden"<?php echo '<# if (type === "hidden") { #> selected<# } #>'; ?>><?php esc_html_e( 'Hidden', 'js_composer' ); ?></option>

								</select>
							</div>
							<div class="vc_value vc_param-field">
								<div class="wpb_settings-title">
									<label><?php esc_html_e( 'Default value', 'js_composer' ); ?></label>
									<?php
									$default_value_info = vc_get_template( 'editors/partials/param-info.tpl.php', [ 'description' => esc_html__( 'Default value or list of values for dropdown type (Note: separate by comma).', 'js_composer' ) ] );
									// phpcs:ignore
									if ( is_string( $default_value_info ) ) { echo $default_value_info; }
									?>
								</div>
								<input type="text" name="value" value="{{ value }}" class="vc_param-value">
							</div>
							<div class="description vc_param-field">
								<div class="wpb_settings-title">
									<label><?php esc_html_e( 'Description', 'js_composer' ); ?></label>
									<?php
									$description_info = vc_get_template( 'editors/partials/param-info.tpl.php', [ 'description' => esc_html__( 'Enter description for parameter.', 'js_composer' ) ] );
									// phpcs:ignore
									if ( is_string( $description_info ) ) { echo $description_info; }
									?>
								</div>
								<textarea name="description" placeholder=""
								<# if ( 'hidden' === type ) { #>
								disabled="disabled"
								<# } #> >{{ description
								}}</textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</<?php echo esc_attr( $custom_tag ); ?>>
		<?php
	}

	/**
	 * Render templates for Automapper
	 *
	 * @since 7.7
	 */
	public function render_templates() {
		$custom_tag = 'script'; // Maybe use ajax resonse for template.
		?>
		<<?php echo esc_attr( $custom_tag ); ?> type="text/html" id="vc_automapper-item-tpl">
		<label class="vc_automapper-edit-btn">{{ name }}</label>
		<span class="vc_automapper-item-controls">
					<a href="#" class="vc_automapper-delete-btn" title="<?php esc_html_e( 'Delete', 'js_composer' ); ?>"></a>
					<a href="#" class="vc_automapper-edit-btn" title="<?php esc_html_e( 'Edit', 'js_composer' ); ?>"></a>
				</span>
		</<?php echo esc_attr( $custom_tag ); ?>>
		<?php
		$this->render_map_form_tpl();
	}

	/**
	 * CRUD create action
	 *
	 * @since 7.7
	 */
	public function create() {
		if ( ! vc_request_param( '_vcnonce' ) ) {
			return;
		}
		vc_user_access()->checkAdminNonce()->validateDie()->wpAny( 'manage_options' )->validateDie()->part( 'settings' )->can( 'vc-automapper-tab' )->validateDie();

		$data = vc_post_param( 'data' );
		$shortcode = new Vc_Automap_Model( $data );

		$this->result( $shortcode->save() );
	}


	/**
	 * CRUD update action
	 *
	 * @since 7.7
	 */
	public function update() {
		if ( ! vc_request_param( '_vcnonce' ) ) {
			return;
		}
		vc_user_access()->checkAdminNonce()->validateDie()->wpAny( 'manage_options' )->validateDie()->part( 'settings' )->can( 'vc-automapper-tab' )->validateDie();

		$id = (int) vc_post_param( 'id' );
		$data = vc_post_param( 'data' );
		$shortcode = new Vc_Automap_Model( $id );
		if ( ! isset( $data['params'] ) ) {
			$data['params'] = array();
		}
		$shortcode->set( $data );

		$this->result( $shortcode->save() );
	}

	/**
	 * CRUD delete action
	 *
	 * @since 7.7
	 */
	public function delete() {
		if ( ! vc_request_param( '_vcnonce' ) ) {
			return;
		}
		vc_user_access()->checkAdminNonce()->validateDie()->wpAny( 'manage_options' )->validateDie()->part( 'settings' )->can( 'vc-automapper-tab' )->validateDie();

		$id = vc_post_param( 'id' );
		$shortcode = new Vc_Automap_Model( $id );

		$this->result( $shortcode->delete() );
	}

	/**
	 * CRUD read action
	 *
	 * @since 7.7
	 */
	public function read() {
		if ( ! vc_request_param( '_vcnonce' ) ) {
			return;
		}
		vc_user_access()->checkAdminNonce()->validateDie()->wpAny( 'manage_options' )->validateDie()->part( 'settings' )->can( 'vc-automapper-tab' )->validateDie();

		$this->result( Vc_Automap_Model::findAll() );
	}

	/**
	 * Ajax result output
	 *
	 * @since 7.7
	 * @param mixed $data
	 */
	public function result( $data ) {
		if ( false !== $data ) {
			wp_send_json_success( $data );
		} else {
			wp_send_json_error( $data );
		}
	}

	/**
	 * Setter/Getter for Disabling Automapper
	 *
	 * @depreacted 7.7
	 * @param bool $disable
	 */
	public static function setDisabled( $disable = true ) { // @codingStandardsIgnoreLine
		_deprecated_function( __METHOD__, '7.7' );
		self::$disabled = $disable;
	}

	/**
	 * Check automapper is disabled.
	 *
	 * @depreacted 7.7
	 * @return bool
	 */
	public static function disabled() {
		_deprecated_function( __METHOD__, '7.7' );
		return self::$disabled;
	}

	/**
	 * Map our custom automapper shortcodes.
	 *
	 * @since 7.7
	 */
	public static function map() {
		$shortcodes = Vc_Automap_Model::findAll();
		foreach ( $shortcodes as $shortcode ) {
			vc_map( array(
				'name' => $shortcode->name,
				'base' => self::prepare_shortcode_tags( $shortcode->tag, WPBMap::getShortCodes() ),
				'category' => ( new self() )->build_categories_array( $shortcode->category ),
				'description' => $shortcode->description,
				'params' => ( new self() )->build_params_array( $shortcode->params ),
				'show_settings_on_create' => ! empty( $shortcode->params ),
				'atm' => true,
				'icon' => 'icon-wpb-atm',
			) );
		}
	}

	/**
	 * Adding Visual Composer Custom Shortcode prefix for custom shortcodes to prevent conflicts.
	 *
	 * @since 7.7
	 * @param string $tag
	 * @param array $shortcodes
	 * @return mixed|string
	 */
	public static function prepare_shortcode_tags( $tag, $shortcodes ) {
		$base_tag = $tag;
		$counter = 1;
		while ( isset( $shortcodes[ $tag ] ) ) {
			$tag = $base_tag . self::$shortcode_suffix . $counter;
			$counter++;
		}
		return $tag;
	}

	/**
	 * Clear custom shortcode suffixes from content.
	 *
	 * @since 7.7
	 * @param string $content
	 * @return array|string|string[]|null
	 */
	public function clear_shortcode_suffix( $content ) {
		return preg_replace( '/(' . self::$shortcode_suffix . '\d+)/', '', $content );
	}

	/**
	 * Clear shortcode suffix on render.
	 *
	 * @since 7.7
	 * @param array $shortcode
	 * @return mixed
	 */
	public function clear_render_shortcode_suffix( $shortcode ) {
		$shortcode['tag'] = preg_replace( '/(' . self::$shortcode_suffix . '\d+)/', '', $shortcode['tag'] );
		$shortcode['string'] = preg_replace( '/(' . self::$shortcode_suffix . '\d+)/', '', $shortcode['string'] );
		return $shortcode;
	}

	/**
	 * Clear render output suffix.
	 *
	 * @param string $tag
	 * @since 7.7
	 * @return string
	 */
	public function clear_output_shortcode_suffix( $tag ) {
		return preg_replace( '/(' . self::$shortcode_suffix . '\d+)/', '', $tag );
	}

	/**
	 * Build categories array.
	 *
	 * @since 7.7
	 * @param string $init_list
	 * @return false|string
	 */
	public function build_categories_array( $init_list ) {
		return explode( ',', preg_replace( '/\,\s+/', ',', trim( $init_list ) ) );
	}

	/**
	 * Build params array.
	 *
	 * @since 7.7
	 * @param array $init_params
	 * @return array
	 */
	public function build_params_array( $init_params ) {
		$params = array();
		if ( is_array( $init_params ) ) {
			foreach ( $init_params as $param ) {
				if ( 'dropdown' === $param['type'] ) {
					$param['value'] = explode( ',', preg_replace( '/\,\s+/', ',', trim( $param['value'] ) ) );
				}
				$param['save_always'] = true;
				$params[] = $param;
			}
		}

		return $params;
	}
}
