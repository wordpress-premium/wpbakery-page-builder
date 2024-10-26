<?php
/**
 * Automapper model.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Vc_Automap_Model' ) ) {
	/**
	 * Shortcode as model for automapper. Provides crud functionality for storing data for shortcodes that mapped by ATM
	 *
	 * @see Vc_Automapper
	 * @since 7.7
	 */
	#[\AllowDynamicProperties]
	class Vc_Automap_Model {
		/**
		 * Option name for storing modules option.
		 *
		 * @since 7.7
		 * @var string
		 */
		protected static $option_name = 'vc_automapped_shortcodes';
		/**
		 * Option data for storing modules option.
		 *
		 * @since 7.7
		 * @var array
		 */
		protected static $option_data;
		/**
		 * Shortcode id.
		 *
		 * @since 7.7
		 * @var array|bool
		 */
		public $id = false;
		/**
		 * Shortcode tag.
		 *
		 * @since 7.7
		 * @var string
		 */
		public $tag;
		/**
		 * Shortcode data.
		 *
		 * @since 7.7
		 * @var mixed
		 */
		protected $data;
		/**
		 * Shortcode vars.
		 *
		 * @since 7.7
		 * @var array
		 */
		protected $vars = array(
			'tag',
			'name',
			'category',
			'description',
			'params',
		);

		/**
		 * Shortcode name.
		 *
		 * @var string
		 */
		public $name;

		/**
		 * Vc_Automap_Model constructor.
		 *
		 * @param array $data
		 * @since 7.7
		 */
		public function __construct( $data ) {
			$this->loadOptionData();
			$this->id = is_array( $data ) && isset( $data['id'] ) ? esc_attr( $data['id'] ) : $data;
			if ( is_array( $data ) ) {
				$this->data = stripslashes_deep( $data );
			}
			foreach ( $this->vars as $var ) {
				$this->{$var} = $this->get( $var );
			}
		}

		/**
		 * Find all mapped shortcodes.
		 *
		 * @since 7.7
		 * @return array
		 */
		public static function findAll() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
			self::loadOptionData();
			$records = array();
			foreach ( self::$option_data as $id => $record ) {
				$record['id'] = $id;
				$model = new self( $record );
				if ( $model ) {
					$records[] = $model;
				}
			}

			return $records;
		}

		/**
		 * Find shortcode by tag.
		 *
		 * @since 7.7
		 * @return array|mixed
		 */
		final protected static function loadOptionData() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
			if ( is_null( self::$option_data ) ) {
				self::$option_data = get_option( self::$option_name );
			}
			if ( ! self::$option_data ) {
				self::$option_data = array();
			}

			return self::$option_data;
		}

		/**
		 * Get shortcode by key.
		 *
		 * @since 7.7
		 * @param string $key
		 *
		 * @return mixed
		 */
		public function get( $key ) {
			if ( is_null( $this->data ) ) {
				$this->data = isset( self::$option_data[ $this->id ] ) ? self::$option_data[ $this->id ] : array();
			}

			return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
		}

		/**
		 * Set shortcode by key.
		 *
		 * @since 7.7
		 * @param mixed $attr
		 * @param mixed $value
		 */
		public function set( $attr, $value = null ) {
			if ( is_array( $attr ) ) {
				foreach ( $attr as $key => $value ) {
					$this->set( $key, $value );
				}
			} elseif ( ! is_null( $value ) ) {
				$this->{$attr} = $value;
			}
		}

		/**
		 * Save automapper optionality.
		 *
		 * @since 7.7
		 * @return bool
		 */
		public function save() {
			if ( ! $this->isValid() ) {
				return false;
			}
			foreach ( $this->vars as $var ) {
				$this->data[ $var ] = $this->{$var};
			}

			return $this->saveOption();
		}

		/**
		 * Delete automapper optionality.
		 *
		 * @since 7.7
		 * @return bool
		 */
		public function delete() {
			return $this->deleteOption();
		}

		/**
		 * Validate shortcode.
		 *
		 * @since 7.7
		 * @return bool
		 */
		public function isValid() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
			if ( ! is_string( $this->name ) || empty( $this->name ) ) {
				return false;
			}
			if ( ! preg_match( '/^\S+$/', $this->tag ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Save automapper optionality.
		 *
		 * @since 7.7
		 * @return bool
		 */
		protected function saveOption() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
			self::$option_data[ $this->id ] = $this->data;

			return update_option( self::$option_name, self::$option_data );
		}

		/**
		 * Delete automapper optionality.
		 *
		 * @since 7.7
		 * @return bool
		 */
		protected function deleteOption() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
			unset( self::$option_data[ $this->id ] );

			return update_option( self::$option_name, self::$option_data );
		}
	}
}
