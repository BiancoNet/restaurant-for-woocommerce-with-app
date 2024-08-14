<?php
/**
 * It is the WRMB2 Types file.
 *
 * @package woorestaurant
 */

/**
 * WRMB field type objects
 *
 * @since  1.0.0
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Types {

	/**
	 * An iterator value for repeatable fields
	 *
	 * @var   integer
	 * @since 1.0.0
	 */
	public $iterator = 0;

	/**
	 * Current WRMB2_Field field object
	 *
	 * @var   WRMB2_Field object
	 * @since 1.0.0
	 */
	public $field;

	/**
	 * Current WRMB2_Type_Base object
	 *
	 * @var   WRMB2_Type_Base object
	 * @since 2.2.2
	 */
	public $type = null;

	/**
	 * It is the Constructor method.
	 *
	 * @param WRMB2_Field $field It is the field.
	 */
	public function __construct( WRMB2_Field $field ) {
		$this->field = $field;
	}

	/**
	 * Default fallback. Allows rendering fields via "wrmb2_render_$fieldtype" hook
	 *
	 * @since 1.0.0
	 * @param string $fieldtype Non-existent field type name.
	 * @param array  $arguments All arguments passed to the method.
	 */
	public function __call( $fieldtype, $arguments ) {

		// Check for methods to be proxied to the WRMB2_Type_Base object.
		$exists = $this->maybe_proxy_method( $fieldtype, $arguments );
		if ( $exists ) {
			return $exists['value'];
		}

		// Check for custom field type class.
		$object = $this->maybe_custom_field_object( $fieldtype, $arguments );
		if ( $object ) {
			return $object->render();
		}

		/**
		 * Pass non-existent field types through an action.
		 *
		 * The dynamic portion of the hook name, $fieldtype, refers to the field type.
		 *
		 * @param array  $field              The passed in `WRMB2_Field` object
		 * @param mixed  $escaped_value      The value of this field escaped.
		 *                                   It defaults to `sanitize_text_field`.
		 *                                   If you need the unescaped value, you can access it
		 *                                   via `$field->value()`
		 * @param int    $object_id          The ID of the current object
		 * @param string $object_type        The type of object you are working with.
		 *                                   Most commonly, `post` (this applies to all post-types),
		 *                                   but could also be `comment`, `user` or `options-page`.
		 * @param object $field_type_object  This `WRMB2_Types` object
		 */
		do_action( "wrmb2_render_{$fieldtype}", $this->field, $this->field->escaped_value(), $this->field->object_id, $this->field->object_type, $this );
	}

	/**
	 * Render a field (and handle repeatable)
	 *
	 * @since  1.1.0
	 */
	public function render() {
		if ( $this->field->args( 'repeatable' ) ) {
			$this->render_repeatable_field();
		} else {
			$this->_render();
		}
	}

	/**
	 * Render a field type
	 *
	 * @since  1.1.0
	 */
	protected function _render() {
		$this->field->peform_param_callback( 'before_field' );
		// phpcs:ignore
		echo ( $this->{$this->field->type()}() );
		$this->field->peform_param_callback( 'after_field' );
	}

	/**
	 * Proxies the method call to the WRMB2_Type_Base object, if it exists, otherwise returns a default fallback value.
	 *
	 * @since  2.2.2
	 *
	 * @param  string $method  Method to call on the WRMB2_Type_Base object.
	 * @param  mixed  $default Default fallback value if method is not found.
	 * @param  array  $args    Optional arguments to pass to proxy method.
	 *
	 * @return mixed           Results from called method.
	 */
	protected function proxy_method( $method, $default, $args = array() ) {
		if ( ! is_object( $this->type ) ) {
			$this->guess_type_object( $method );
		}

		if ( is_object( $this->type ) && method_exists( $this->type, $method ) ) {

			return empty( $args )
				? $this->type->$method()
				: call_user_func_array( array( $this->type, $method ), $args );
		}

		return $default;
	}

	/**
	 * If no WRMB2_Types::$type object is initiated when a proxy method is called, it means
	 * it's a custom field type (which SHOULD be instantiating a Type), but let's try and
	 * guess the type object for them and instantiate it.
	 *
	 * @since  2.2.3
	 *
	 * @param string $method  Method attempting to be called on the WRMB2_Type_Base object.
	 * @return bool
	 */
	protected function guess_type_object( $method ) {
		$fieldtype = $this->field->type();

		// Try to "guess" the Type object based on the method requested.
		switch ( $method ) {
			case 'select_option':
			case 'list_input':
			case 'list_input_checkbox':
			case 'concat_items':
				$this->get_new_render_type( $fieldtype, 'WRMB2_Type_Select' );
				break;
			case 'is_valid_img_ext':
			case 'img_status_output':
			case 'file_status_output':
				$this->get_new_render_type( $fieldtype, 'WRMB2_Type_File_Base' );
				break;
			case 'parse_picker_options':
				$this->get_new_render_type( $fieldtype, 'WRMB2_Type_Text_Date' );
				break;
			case 'get_object_terms':
			case 'get_terms':
				$this->get_new_render_type( $fieldtype, 'WRMB2_Type_Taxonomy_Multicheck' );
				break;
			case 'date_args':
			case 'time_args':
				$this->get_new_render_type( $fieldtype, 'WRMB2_Type_Text_Datetime_Timestamp' );
				break;
			case 'parse_args':
				$this->get_new_render_type( $fieldtype, 'WRMB2_Type_Text' );
				break;
		}

		return null !== $this->type;
	}

	/**
	 * Check for methods to be proxied to the WRMB2_Type_Base object.
	 *
	 * @since  2.2.4
	 * @param  string $method    The possible method to proxy.
	 * @param  array  $arguments All arguments passed to the method.
	 * @return bool|array        False if not proxied, else array with 'value' key being the return of the method.
	 */
	public function maybe_proxy_method( $method, $arguments ) {
		$exists = false;

		$proxied = array(
			'get_object_terms'     => array(),
			'is_valid_img_ext'     => false,
			'parse_args'           => array(),
			'concat_items'         => '',
			'select_option'        => '',
			'list_input'           => '',
			'list_input_checkbox'  => '',
			'img_status_output'    => '',
			'file_status_output'   => '',
			'parse_picker_options' => array(),
		);
		if ( isset( $proxied[ $method ] ) ) {
			$exists = array(
				// Ok, proxy the method call to the WRMB2_Type_Base object.
				'value' => $this->proxy_method( $method, $proxied[ $method ], $arguments ),
			);
		}

		return $exists;
	}

	/**
	 * Checks for a custom field WRMB2_Type_Base class to use for rendering.
	 *
	 * @since 2.2.4
	 *
	 * @param string $fieldtype Non-existent field type name.
	 * @param array  $args      Optional field arguments.
	 *
	 * @return bool|WRMB2_Type_Base Type object if custom field is an object, false if field was added with
	 *                             `wrmb2_render_{$field_type}` action.
	 * @throws Exception If custom field type class does not extend WRMB2_Type_Base.
	 */
	public function maybe_custom_field_object( $fieldtype, $args = array() ) {
		$render_class_name = $this->get_render_type_class( $fieldtype );
		if ( $render_class_name ) {
			$this->type = new $render_class_name( $this, $args );

			if ( ! ( $this->type instanceof WRMB2_Type_Base ) ) {
				throw new Exception( esc_attr__( 'Custom WRMB2 field type classes must extend WRMB2_Type_Base.', 'wrmb2' ) );
			}

			return $this->type;
		}

		return false;
	}

	/**
	 * Gets the render type WRMB2_Type_Base object to use for rendering the field.
	 *
	 * @since  2.2.4
	 * @param  string $fieldtype         The type of field being rendered.
	 * @param  string $render_class_name The default field type class to use. Defaults to null.
	 * @param  array  $args              Optional arguments to pass to type class.
	 * @param  mixed  $additional        Optional additional argument to pass to type class.
	 * @return WRMB2_Type_Base            Type object.
	 */
	public function get_new_render_type( $fieldtype, $render_class_name = null, $args = array(), $additional = '' ) {
		$render_class_name = $this->get_render_type_class( $fieldtype, $render_class_name );
		$this->type        = new $render_class_name( $this, $args, $additional );

		return $this->type;
	}

	/**
	 * Checks for the render type class to use for rendering the field.
	 *
	 * @since  2.2.4
	 * @param  string $fieldtype         The type of field being rendered.
	 * @param  string $render_class_name The default field type class to use. Defaults to null.
	 * @return string                    The field type class to use.
	 */
	public function get_render_type_class( $fieldtype, $render_class_name = null ) {
		$render_class_name = $this->field->args( 'render_class' ) ? $this->field->args( 'render_class' ) : $render_class_name;

		if ( has_action( "wrmb2_render_class_{$fieldtype}" ) ) {

			/**
			 * Filters the custom field type class used for rendering the field. Class is required to extend WRMB2_Type_Base.
			 *
			 * The dynamic portion of the hook name, $fieldtype, refers to the (custom) field type.
			 *
			 * @since 2.2.4
			 *
			 * @param string $render_class_name The custom field type class to use. Default null.
			 * @param object $field_type_object This `WRMB2_Types` object.
			 */
			$render_class_name = apply_filters( "wrmb2_render_class_{$fieldtype}", $render_class_name, $this );
		}

		return $render_class_name && class_exists( $render_class_name ) ? $render_class_name : false;
	}

	/**
	 * Retrieve text parameter from field's options array (if it has one), or use fallback text
	 *
	 * @since 2.0.0
	 * @param  string $text_key Key in field's options array.
	 * @param  string $fallback Fallback text.
	 * @return string
	 */
	public function _text( $text_key, $fallback = '' ) {
		return $this->field->get_string( $text_key, $fallback );
	}

	/**
	 * Determine a file's extension.
	 *
	 * @since  1.0.0
	 * @param  string $file File url.
	 * @return string|false File extension or false
	 */
	public function get_file_ext( $file ) {
		return WRMB2_Utils::get_file_ext( $file );
	}

	/**
	 * Get the file name from a url
	 *
	 * @since  2.0.0
	 * @param  string $value File url or path.
	 * @return string        File name
	 */
	public function get_file_name_from_path( $value ) {
		return WRMB2_Utils::get_file_name_from_path( $value );
	}

	/**
	 * Combines attributes into a string for a form element
	 *
	 * @since  1.1.0
	 * @param  array $attrs        Attributes to concatenate.
	 * @param  array $attr_exclude Attributes that should NOT be concatenated.
	 * @return string               String of attributes for form element
	 */
	public function concat_attrs( $attrs, $attr_exclude = array() ) {
		return WRMB2_Utils::concat_attrs( $attrs, $attr_exclude );
	}

	/**
	 * Generates repeatable field table markup
	 *
	 * @since  1.0.0
	 */
	public function render_repeatable_field() {
		$table_id = $this->field->id() . '_repeat';

		$this->_desc( true, true, true );
		?>

		<div id="<?php echo wp_kses_post( $table_id ); ?>" class="wrmb-repeat-table wrmb-nested">
			<div class="wrmb-tbody wrmb-field-list">
				<?php $this->repeatable_rows(); ?>
			</div>
		</div>
		<p class="wrmb-add-row">
			<button type="button" data-selector="<?php echo wp_kses_post( $table_id ); ?>" class="wrmb-add-row-button button-secondary"><?php echo esc_html( $this->_text( 'add_row_text', esc_html__( 'Add Row', 'wrmb2' ) ) ); ?></button>
		</p>

		<?php
		// reset iterator.
		$this->iterator = 0;
	}

	/**
	 * Generates repeatable field rows
	 *
	 * @since  1.1.0
	 */
	public function repeatable_rows() {
		$meta_value = array_filter( (array) $this->field->escaped_value() );
		// check for default content.
		$default = $this->field->get_default();

		// check for saved data.
		if ( ! empty( $meta_value ) ) {
			$meta_value = is_array( $meta_value ) ? array_filter( $meta_value ) : $meta_value;
			$meta_value = ! empty( $meta_value ) ? $meta_value : $default;
		} else {
			$meta_value = $default;
		}

		// Loop value array and add a row.
		if ( ! empty( $meta_value ) ) {
			foreach ( (array) $meta_value as $val ) {
				$this->field->escaped_value = $val;
				$this->repeat_row();
				++$this->iterator;
			}
		} else {

			// If value is empty (including empty array), then clear the value.
			$this->field->escaped_value = null;
			$this->field->value         = null;

			// Otherwise add one row.
			$this->repeat_row();
		}

		// Then add an empty row.
		$this->field->escaped_value = $default;
		$this->iterator             = $this->iterator ? $this->iterator : 1;
		$this->repeat_row( 'empty-row hidden' );
	}

	/**
	 * Generates a repeatable row's markup
	 *
	 * @since 1.1.0
	 * @param string $classes Repeatable table row's class.
	 */
	protected function repeat_row( $classes = 'wrmb-repeat-row' ) {
		$classes = explode( ' ', $classes );
		$classes = array_map( 'sanitize_html_class', $classes );
		?>

		<div class="wrmb-row <?php echo wp_kses_post( implode( ' ', $classes ) ); ?>">
			<div class="wrmb-td">
				<?php $this->_render(); ?>
			</div>
			<div class="wrmb-td wrmb-remove-row">
				<button type="button" class="button-secondary wrmb-remove-row-button" title="<?php echo wp_kses_post( $this->_text( 'remove_row_button_title', esc_html__( 'Remove Row', 'wrmb2' ) ) ); ?>"><?php echo esc_html( $this->_text( 'remove_row_text', esc_html__( 'Remove', 'wrmb2' ) ) ); ?></button>
			</div>
		</div>

		<?php
	}

	/**
	 * Generates description markup.
	 *
	 * @since 1.0.0
	 * @param bool $paragraph    Paragraph tag or span.
	 * @param bool $echo         Whether to echo description or only return it.
	 * @param bool $repeat_group Whether to repeat the group.
	 * @return string Field's description markup.
	 */
	public function _desc( $paragraph = false, $echo = false, $repeat_group = false ) {
		// Prevent description from printing multiple times for repeatable fields.
		if ( ! $repeat_group && ( $this->field->args( 'repeatable' ) || $this->iterator > 0 ) ) {
			return '';
		}

		$desc = $this->field->args( 'description' );

		if ( ! $desc ) {
			return;
		}

		$tag  = $paragraph ? 'p' : 'span';
		$desc = sprintf( "\n" . '<%1$s class="wrmb2-metabox-description">%2$s</%1$s>' . "\n", $tag, $desc );

		if ( $echo ) {
			echo wp_kses_post( $desc );
		}

		return $desc;
	}

	/**
	 * Generate field name attribute
	 *
	 * @since  1.1.0
	 * @param  string $suffix For multi-part fields.
	 * @return string          Name attribute
	 */
	public function _name( $suffix = '' ) {
		return $this->field->args( '_name' ) . ( $this->field->args( 'repeatable' ) ? '[' . $this->iterator . ']' : '' ) . $suffix;
	}

	/**
	 * Generate field id attribute
	 *
	 * @since  1.1.0
	 * @param  string $suffix                     For multi-part fields.
	 * @param  bool   $append_repeatable_iterator Whether to append the iterator attribue if the field is repeatable.
	 * @return string                             Id attribute
	 */
	public function _id( $suffix = '', $append_repeatable_iterator = true ) {
		$id = $this->field->id() . $suffix . ( $this->field->args( 'repeatable' ) ? '_' . $this->iterator : '' );

		if ( $append_repeatable_iterator && $this->field->args( 'repeatable' ) ) {
			$id .= '" data-iterator="' . $this->iterator;
		}

		return $id;
	}

	/**
	 * Handles outputting an 'input' element
	 *
	 * @since  1.1.0
	 * @param  array  $args Override arguments.
	 * @param  string $type Field type.
	 * @return string       Form input element
	 */
	public function input( $args = array(), $type = __FUNCTION__ ) {
		return $this->get_new_render_type( 'text', 'WRMB2_Type_Text', $args, $type )->render();
	}

	/**
	 * Handles outputting an 'textarea' element
	 *
	 * @since  1.1.0
	 * @param  array $args Override arguments.
	 * @return string       Form textarea element
	 */
	public function textarea( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Textarea', $args )->render();
	}

	/**
	 * Begin Field Types
	 */
	public function text() {
		return $this->input();
	}

	/**
	 * It is the hidden method.
	 */
	public function hidden() {
		$args = array(
			'type'  => 'hidden',
			'desc'  => '',
			'class' => 'wrmb2-hidden',
		);
		if ( $this->field->group ) {
			$args['data-groupid']  = $this->field->group->id();
			$args['data-iterator'] = $this->iterator;
		}

		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Text', $args, 'input' )->render();
	}

	/**
	 * It is the text small method.
	 */
	public function text_small() {
		return $this->get_new_render_type(
			__FUNCTION__,
			'WRMB2_Type_Text',
			array(
				'class' => 'wrmb2-text-small',
				'desc'  => $this->_desc(),
			),
			'input'
		)->render();
	}

	/**
	 * It is the text medium method.
	 */
	public function text_medium() {
		return $this->get_new_render_type(
			__FUNCTION__,
			'WRMB2_Type_Text',
			array(
				'class' => 'wrmb2-text-medium',
				'desc'  => $this->_desc(),
			),
			'input'
		)->render();
	}

	/**
	 * It is the text email method.
	 */
	public function text_email() {
		return $this->get_new_render_type(
			__FUNCTION__,
			'WRMB2_Type_Text',
			array(
				'class' => 'wrmb2-text-email wrmb2-text-medium',
				'type'  => 'email',
			),
			'input'
		)->render();
	}

	/**
	 * It is the text url method.
	 */
	public function text_url() {
		return $this->get_new_render_type(
			__FUNCTION__,
			'WRMB2_Type_Text',
			array(
				'class' => 'wrmb2-text-url wrmb2-text-medium regular-text',
				'value' => $this->field->escaped_value( 'esc_url' ),
			),
			'input'
		)->render();
	}

	/**
	 * It is the text money method.
	 */
	public function text_money() {
		$input = $this->get_new_render_type(
			__FUNCTION__,
			'WRMB2_Type_Text',
			array(
				'class' => 'wrmb2-text-money',
				'desc'  => $this->_desc(),
			),
			'input'
		)->render();
		return ( ! $this->field->get_param_callback_result( 'before_field' ) ? '$ ' : ' ' ) . $input;
	}

	/**
	 * It is the textarea small method.
	 */
	public function textarea_small() {
		return $this->get_new_render_type(
			__FUNCTION__,
			'WRMB2_Type_Textarea',
			array(
				'class' => 'wrmb2-textarea-small',
				'rows'  => 4,
			)
		)->render();
	}

	/**
	 * It is the textarea code method.
	 *
	 * @param array $args It is the argument.
	 */
	public function textarea_code( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Textarea_Code', $args )->render();
	}

	/**
	 * It is the wysiwyg method.
	 *
	 * @param array $args It is the argument.
	 */
	public function wysiwyg( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Wysiwyg', $args )->render();
	}

	/**
	 * It is the text date method.
	 *
	 * @param array $args It is the argument.
	 */
	public function text_date( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Text_Date', $args )->render();
	}

	/**
	 * It is the text date timestamp method.
	 *
	 * @param array $args It is the argument.
	 */
	public function text_date_timestamp( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Text_Date', $args )->render();
	}

	/**
	 * It is the text time method.
	 *
	 * @param array $args It is the argument.
	 */
	public function text_time( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Text_Time', $args )->render();
	}

	/**
	 * It is the text datetime timestamp method.
	 *
	 * @param array $args It is the argument.
	 */
	public function text_datetime_timestamp( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Text_Datetime_Timestamp', $args )->render();
	}

	/**
	 * It is the text datetime timestamp timezone method.
	 *
	 * @param array $args It is the argument.
	 */
	public function text_datetime_timestamp_timezone( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Text_Datetime_Timestamp_Timezone', $args )->render();
	}

	/**
	 * It is the select timezone method.
	 *
	 * @param array $args It is the argument.
	 */
	public function select_timezone( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Select_Timezone', $args )->render();
	}

	/**
	 * It is the colorpicker method.
	 *
	 * @param array  $args It is the argument.
	 * @param string $meta_value It is the meta value.
	 */
	public function colorpicker( $args = array(), $meta_value = '' ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Colorpicker', $args, $meta_value )->render();
	}

	/**
	 * It is the title method.
	 *
	 * @param array $args It is the argument.
	 */
	public function title( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Title', $args )->render();
	}

	/**
	 * It is the select method.
	 *
	 * @param array $args It is the argument.
	 */
	public function select( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Select', $args )->render();
	}

	/**
	 * It is the taxonomy select method.
	 *
	 * @param array $args It is the argument.
	 */
	public function taxonomy_select( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Taxonomy_Select', $args )->render();
	}

	/**
	 * It is the taxonomy select hierarchical method.
	 *
	 * @param array $args It is the argument.
	 */
	public function taxonomy_select_hierarchical( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Taxonomy_Select_Hierarchical', $args )->render();
	}

	/**
	 * It is the radio method.
	 *
	 * @param array    $args It is the argument.
	 * @param FUNCTION $type It is the argument.
	 */
	public function radio( $args = array(), $type = __FUNCTION__ ) {
		return $this->get_new_render_type( $type, 'WRMB2_Type_Radio', $args, $type )->render();
	}

	/**
	 * It is the radio inline method.
	 *
	 * @param array $args It is the argument.
	 */
	public function radio_inline( $args = array() ) {
		return $this->radio( $args, __FUNCTION__ );
	}

	/**
	 * It is the multicheck method.
	 *
	 * @param string $type It is the argument.
	 */
	public function multicheck( $type = 'checkbox' ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Multicheck', array(), $type )->render();
	}

	/**
	 * It is the multicheck inline method.
	 */
	public function multicheck_inline() {
		return $this->multicheck( 'multicheck_inline' );
	}

	/**
	 * It is the checkbox method.
	 *
	 * @param array $args It is the argument.
	 * @param bool  $is_checked It is the argument.
	 */
	public function checkbox( $args = array(), $is_checked = null ) {
		// Avoid get_new_render_type since we need a different default for the 3rd argument than ''.
		$render_class_name = $this->get_render_type_class( __FUNCTION__, 'WRMB2_Type_Checkbox' );
		$this->type        = new $render_class_name( $this, $args, $is_checked );

		return $this->type->render();
	}

	/**
	 * It is the taxonomy radio method.
	 *
	 * @param array $args It is the argument.
	 */
	public function taxonomy_radio( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Taxonomy_Radio', $args )->render();
	}

	/**
	 * It is the taxonomy radio hierarchical method.
	 *
	 * @param array $args It is the argument.
	 */
	public function taxonomy_radio_hierarchical( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Taxonomy_Radio_Hierarchical', $args )->render();
	}

	/**
	 * It is the taxonomy radio inline method.
	 *
	 * @param array $args It is the argument.
	 */
	public function taxonomy_radio_inline( $args = array() ) {
		return $this->taxonomy_radio( $args );
	}

	/**
	 * It is the taxonomy multicheck method.
	 *
	 * @param array $args It is the argument.
	 */
	public function taxonomy_multicheck( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Taxonomy_Multicheck', $args )->render();
	}

	/**
	 * It is the taxonomy multicheck hierarchical method.
	 *
	 * @param array $args It is the argument.
	 */
	public function taxonomy_multicheck_hierarchical( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Taxonomy_Multicheck_Hierarchical', $args )->render();
	}

	/**
	 * It is the taxonomy multicheck inline method.
	 *
	 * @param array $args It is the argument.
	 */
	public function taxonomy_multicheck_inline( $args = array() ) {
		return $this->taxonomy_multicheck( $args );
	}

	/**
	 * It is the oembed method.
	 *
	 * @param array $args It is the argument.
	 */
	public function oembed( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_Oembed', $args )->render();
	}

	/**
	 * It is the file list method.
	 *
	 * @param array $args It is the argument.
	 */
	public function file_list( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_File_List', $args )->render();
	}

	/**
	 * It is the file method.
	 *
	 * @param array $args It is the argument.
	 */
	public function file( $args = array() ) {
		return $this->get_new_render_type( __FUNCTION__, 'WRMB2_Type_File', $args )->render();
	}
}
