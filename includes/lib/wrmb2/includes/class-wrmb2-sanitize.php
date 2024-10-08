<?php
/**
 * It is the WRMB2 Sanitize file.
 *
 * @package woorestaurant
 */

/**
 * WRMB2 field sanitization
 *
 * @since  0.0.4
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 *
 * @method string _id()
 */
class WRMB2_Sanitize {

	/**
	 * A WRMB field object
	 *
	 * @var WRMB2_Field object
	 */
	public $field;

	/**
	 * Field's value
	 *
	 * @var mixed
	 */
	public $value;

	/**
	 * Setup our class vars
	 *
	 * @since 1.1.0
	 * @param WRMB2_Field $field A WRMB2 field object.
	 * @param mixed       $value Field value.
	 */
	public function __construct( WRMB2_Field $field, $value ) {
		$this->field = $field;
		$this->value = $value;
	}

	/**
	 * Catchall method if field's 'sanitization_cb' is NOT defined,
	 * or field type does not have a corresponding validation method.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $name      Non-existent method name.
	 * @param  array  $arguments All arguments passed to the method.
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		return $this->default_sanitization();
	}

	/**
	 * Default fallback sanitization method. Applies filters.
	 *
	 * @since  1.0.2
	 */
	public function default_sanitization() {
		$field_type = $this->field->type();

		/**
		 * This exists for back-compatibility, but validation
		 * is not what happens here.
		 *
		 * @deprecated See documentation for "wrmb2_sanitize_{$field_type}".
		 */
		if ( function_exists( 'apply_filters_deprecated' ) ) {
			$override_value = apply_filters_deprecated( "wrmb2_validate_{$field_type}", array( null, $this->value, $this->field->object_id, $this->field->args(), $this ), '2.0.0', "wrmb2_sanitize_{$field_type}" );
		} else {
			$override_value = apply_filters( "wrmb2_validate_{$field_type}", null, $this->value, $this->field->object_id, $this->field->args(), $this );
		}

		if ( null !== $override_value ) {
			return $override_value;
		}

		$sanitized_value = '';
		switch ( $field_type ) {
			case 'wysiwyg':
			case 'textarea_small':
			case 'oembed':
				$sanitized_value = $this->textarea();
				break;
			case 'taxonomy_select':
			case 'taxonomy_select_hierarchical':
			case 'taxonomy_radio':
			case 'taxonomy_radio_inline':
			case 'taxonomy_radio_hierarchical':
			case 'taxonomy_multicheck':
			case 'taxonomy_multicheck_hierarchical':
			case 'taxonomy_multicheck_inline':
				$sanitized_value = $this->taxonomy();
				break;
			case 'multicheck':
			case 'multicheck_inline':
			case 'file_list':
			case 'group':
				// no filtering.
				$sanitized_value = $this->value;
				break;
			default:
				// Handle repeatable fields array.
				// We'll fallback to 'sanitize_text_field'.
				$sanitized_value = $this->_default_sanitization();
				break;
		}

		return $this->_is_empty_array( $sanitized_value ) ? '' : $sanitized_value;
	}

	/**
	 * Default sanitization method, sanitize_text_field. Checks if value is array.
	 *
	 * @since  2.2.4
	 * @return mixed  Sanitized value.
	 */
	protected function _default_sanitization() {
		// Handle repeatable fields array.
		return is_array( $this->value ) ? array_map( 'sanitize_text_field', $this->value ) : sanitize_text_field( $this->value );
	}

	/**
	 * Sets the object terms to the object (if not options-page) and optionally returns the sanitized term values.
	 *
	 * @since  2.2.4
	 * @return mixed  Blank value, or sanitized term values if "wrmb2_return_taxonomy_values_{$wrmb_id}" is true.
	 */
	public function taxonomy() {
		$sanitized_value = '';

		if ( ! $this->field->args( 'taxonomy' ) ) {
			WRMB2_Utils::log_if_debug( __METHOD__, __LINE__, "{$this->field->type()} {$this->field->_id( '', false )} is missing the 'taxonomy' parameter." );
		} else {

			if ( in_array( $this->field->object_type, array( 'options-page', 'term' ), true ) ) {
				$return_values = true;
			} else {
				wp_set_object_terms( $this->field->object_id, $this->value, $this->field->args( 'taxonomy' ) );
				$return_values = false;
			}

			$wrmb_id = $this->field->wrmb_id;

			/**
			 * Filter whether 'taxonomy_*' fields should return their value when being sanitized.
			 *
			 * By default, these fields do not return a value as we do not want them stored to meta
			 * (as they are stored as terms). This allows overriding that and is used by WRMB2::get_sanitized_values().
			 *
			 * The dynamic portion of the hook, $wrmb_id, refers to the this field's WRMB2 box id.
			 *
			 * @since 2.2.4
			 *
			 * @param bool          $return_values By default, this is only true for 'options-page' boxes. To enable:
			 *                                     `add_filter( "wrmb2_return_taxonomy_values_{$wrmb_id}", '__return_true' );`
			 * @param WRMB2_Sanitize $sanitizer This object.
			 */
			if ( apply_filters( "wrmb2_return_taxonomy_values_{$wrmb_id}", $return_values, $this ) ) {
				$sanitized_value = $this->_default_sanitization();
			}
		}

		return $sanitized_value;
	}

	/**
	 * Simple checkbox validation
	 *
	 * @since  1.0.1
	 * @return string|false 'on' or false
	 */
	public function checkbox() {
		return 'on' === $this->value ? 'on' : false;
	}

	/**
	 * Validate url in a meta value.
	 *
	 * @since  1.0.1
	 * @return string        Empty string or escaped url
	 */
	public function text_url() {
		$protocols = $this->field->args( 'protocols' );
		// for repeatable.
		if ( is_array( $this->value ) ) {
			foreach ( $this->value as $key => $val ) {
				$this->value[ $key ] = $val ? esc_url_raw( $val, $protocols ) : $this->field->get_default();
			}
		} else {
			$this->value = $this->value ? esc_url_raw( $this->value, $protocols ) : $this->field->get_default();
		}

		return $this->value;
	}

	/**
	 * It is the colorpicker method.
	 */
	public function colorpicker() {
		// for repeatable.
		if ( is_array( $this->value ) ) {
			$check       = $this->value;
			$this->value = array();
			foreach ( $check as $key => $val ) {
				if ( $val && '#' != $val ) {
					$this->value[ $key ] = esc_attr( $val );
				}
			}
		} else {
			$this->value = ! $this->value || '#' == $this->value ? '' : esc_attr( $this->value );
		}
		return $this->value;
	}

	/**
	 * Validate email in a meta value
	 *
	 * @since  1.0.1
	 * @return string       Empty string or sanitized email
	 */
	public function text_email() {
		// for repeatable.
		if ( is_array( $this->value ) ) {
			foreach ( $this->value as $key => $val ) {
				$val                 = trim( $val );
				$this->value[ $key ] = is_email( $val ) ? $val : '';
			}
		} else {
			$this->value = trim( $this->value );
			$this->value = is_email( $this->value ) ? $this->value : '';
		}

		return $this->value;
	}

	/**
	 * Validate money in a meta value
	 *
	 * @since  1.0.1
	 * @return string Empty string or sanitized money value
	 */
	public function text_money() {
		if ( ! $this->value ) {
			return '';
		}

		global $wp_locale;

		$search  = array( $wp_locale->number_format['thousands_sep'], $wp_locale->number_format['decimal_point'] );
		$replace = array( '', '.' );

		// Strip slashes. Example: 2\'180.00.
		// See https://github.com/WRMB2/WRMB2/issues/1014.
		$this->value = wp_unslash( $this->value );

		// for repeatable.
		if ( is_array( $this->value ) ) {
			foreach ( $this->value as $key => $val ) {
				if ( $val ) {
					$this->value[ $key ] = number_format_i18n( (float) str_ireplace( $search, $replace, $val ), 2 );
				}
			}
		} else {
			$this->value = number_format_i18n( (float) str_ireplace( $search, $replace, $this->value ), 2 );
		}

		return $this->value;
	}

	/**
	 * Converts text date to timestamp
	 *
	 * @since  1.0.2
	 * @return string Timestring
	 */
	public function text_date_timestamp() {
		// date_create_from_format if there is a slash in the value.
		$this->value = wp_unslash( $this->value );

		return is_array( $this->value )
			? array_map( array( $this->field, 'get_timestamp_from_value' ), $this->value )
			: $this->field->get_timestamp_from_value( $this->value );
	}

	/**
	 * Datetime to timestamp
	 *
	 * @since  1.0.1
	 *
	 * @param bool $repeat Whether or not to repeat.
	 * @return string|array Timestring
	 */
	public function text_datetime_timestamp( $repeat = false ) {
		// date_create_from_format if there is a slash in the value.
		$this->value = wp_unslash( $this->value );

		$test = is_array( $this->value ) ? array_filter( $this->value ) : '';
		if ( empty( $test ) ) {
			return '';
		}

		$repeat_value = $this->_check_repeat( __FUNCTION__, $repeat );
		if ( false !== $repeat_value ) {
			return $repeat_value;
		}

		if ( isset( $this->value['date'], $this->value['time'] ) ) {
			$this->value = $this->field->get_timestamp_from_value( $this->value['date'] . ' ' . $this->value['time'] );
		}

		$tz_offset = $this->field->field_timezone_offset();
		if ( $tz_offset ) {
			$this->value += (int) $tz_offset;
		}

		return $this->value;
	}

	/**
	 * Datetime to timestamp with timezone
	 *
	 * @since  1.0.1
	 *
	 * @param bool $repeat Whether or not to repeat.
	 * @return string       Timestring
	 */
	public function text_datetime_timestamp_timezone( $repeat = false ) {
		static $utc_values = array();

		$test = is_array( $this->value ) ? array_filter( $this->value ) : '';
		if ( empty( $test ) ) {
			return '';
		}

		// date_create_from_format if there is a slash in the value.
		$this->value = wp_unslash( $this->value );

		$utc_key = $this->field->_id( '', false ) . '_utc';

		$repeat_value = $this->_check_repeat( __FUNCTION__, $repeat );
		if ( false !== $repeat_value ) {
			if ( ! empty( $utc_values[ $utc_key ] ) ) {
				$this->_save_utc_value( $utc_key, $utc_values[ $utc_key ] );
				unset( $utc_values[ $utc_key ] );
			}

			return $repeat_value;
		}

		$tzstring = null;

		if ( is_array( $this->value ) && array_key_exists( 'timezone', $this->value ) ) {
			$tzstring = $this->value['timezone'];
		}

		if ( empty( $tzstring ) ) {
			$tzstring = WRMB2_Utils::timezone_string();
		}

		$offset = WRMB2_Utils::timezone_offset( $tzstring );

		if ( 'UTC' === substr( $tzstring, 0, 3 ) ) {
			$tzstring = timezone_name_from_abbr( '', $offset, 0 );
			/**
			 * The timezone_name_from_abbr() returns false if not found based on offset.
			 * Since there are currently some invalid timezones in wp_timezone_dropdown(),
			 * fallback to an offset of 0 (UTC+0)
			 * https://core.trac.wordpress.org/ticket/29205
			 */
			$tzstring = false !== $tzstring ? $tzstring : timezone_name_from_abbr( '', 0, 0 );
		}

		$full_format = $this->field->args['date_format'] . ' ' . $this->field->args['time_format'];
		$full_date   = $this->value['date'] . ' ' . $this->value['time'];

		try {

			$datetime = date_create_from_format( $full_format, $full_date );

			if ( ! is_object( $datetime ) ) {
				$this->value = '';
				$utc_stamp   = '';
			} else {
				$datetime->setTimezone( new DateTimeZone( $tzstring ) );
				$utc_stamp   = date_timestamp_get( $datetime ) - $offset;
				$this->value = serialize( $datetime );
			}

			if ( $this->field->group ) {
				$this->value = array(
					'supporting_field_value' => $utc_stamp,
					'supporting_field_id'    => $utc_key,
					'value'                  => $this->value,
				);
			} elseif ( ! $this->field->group ) {
				// Save the utc timestamp supporting field.
				if ( $repeat ) {
					$utc_values[ $utc_key ][] = $utc_stamp;
				} else {
					$this->_save_utc_value( $utc_key, $utc_stamp );
				}
			}
		} catch ( Exception $e ) {
			$this->value = '';
			WRMB2_Utils::log_if_debug( __METHOD__, __LINE__, $e->getMessage() );
		}

		return $this->value;
	}

	/**
	 * Sanitize textareas and wysiwyg fields
	 *
	 * @since  1.0.1
	 * @return string       Sanitized data
	 */
	public function textarea() {
		return is_array( $this->value ) ? array_map( 'wp_kses_post', $this->value ) : wp_kses_post( $this->value );
	}

	/**
	 * Sanitize code textareas
	 *
	 * @since  1.0.2
	 *
	 * @param bool $repeat Whether or not to repeat.
	 * @return string       Sanitized data
	 */
	public function textarea_code( $repeat = false ) {
		$repeat_value = $this->_check_repeat( __FUNCTION__, $repeat );
		if ( false !== $repeat_value ) {
			return $repeat_value;
		}
	
		// Explicitly set the $flags parameter for compatibility with PHP 8.1 and earlier
		return htmlspecialchars_decode( stripslashes( $this->value ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );
	}
	

	/**
	 * Handles saving of attachment post ID and sanitizing file url
	 *
	 * @since  1.1.0
	 * @return string        Sanitized url
	 */
	public function file() {
		$file_id_key = $this->field->_id( '', false ) . '_id';

		if ( $this->field->group ) {
			// Return an array with url/id if saving a group field.
			$this->value = $this->_get_group_file_value_array( $file_id_key );
		} else {
			$this->_save_file_id_value( $file_id_key );
			$this->text_url();
		}

		return $this->value;
	}

	/**
	 * Gets the values for the `file` field type from the data being saved.
	 *
	 * @since  2.2.0
	 *
	 * @param mixed $id_key ID key to use.
	 * @return array
	 */
	public function _get_group_file_value_array( $id_key ) {
		$alldata = $this->field->group->data_to_save;
		$base_id = $this->field->group->_id( '', false );
		$i       = $this->field->group->index;

		// Check group $alldata data.
		$id_val = isset( $alldata[ $base_id ][ $i ][ $id_key ] )
			? absint( $alldata[ $base_id ][ $i ][ $id_key ] )
			: '';

		// We don't want to save 0 to the DB for file fields.
		if ( 0 === $id_val ) {
			$id_val = '';
		}

		return array(
			'value'                  => $this->text_url(),
			'supporting_field_value' => $id_val,
			'supporting_field_id'    => $id_key,
		);
	}

	/**
	 * Peforms saving of `file` attachement's ID
	 *
	 * @since  1.1.0
	 *
	 * @param mixed $file_id_key ID key to use.
	 * @return mixed
	 */
	public function _save_file_id_value( $file_id_key ) {
		$id_field = $this->_new_supporting_field( $file_id_key );

		// Check standard data_to_save data.
		$id_val = isset( $this->field->data_to_save[ $file_id_key ] )
			? $this->field->data_to_save[ $file_id_key ]
			: null;

		// If there is no ID saved yet, try to get it from the url.
		if ( $this->value && ! $id_val ) {
			$id_val = WRMB2_Utils::image_id_from_url( $this->value );

			// If there is an ID but user emptied the input value, remove the ID.
		} elseif ( ! $this->value && $id_val ) {
			$id_val = null;
		}

		return $id_field->save_field( $id_val );
	}

	/**
	 * Peforms saving of `text_datetime_timestamp_timezone` utc timestamp
	 *
	 * @since  2.2.0
	 *
	 * @param mixed $utc_key   UTC key.
	 * @param mixed $utc_stamp UTC timestamp.
	 * @return mixed
	 */
	public function _save_utc_value( $utc_key, $utc_stamp ) {
		return $this->_new_supporting_field( $utc_key )->save_field( $utc_stamp );
	}

	/**
	 * Returns a new, supporting, WRMB2_Field object based on a new field id.
	 *
	 * @since  2.2.0
	 *
	 * @param mixed $new_field_id New field ID.
	 * @return WRMB2_Field
	 */
	public function _new_supporting_field( $new_field_id ) {
		return $this->field->get_field_clone(
			array(
				'id'              => $new_field_id,
				'sanitization_cb' => false,
			)
		);
	}

	/**
	 * If repeating, loop through and re-apply sanitization method
	 *
	 * @since  1.1.0
	 * @param  string $method Class method.
	 * @param  bool   $repeat Whether repeating or not.
	 * @return mixed          Sanitized value
	 */
	public function _check_repeat( $method, $repeat ) {
		if ( $repeat || ! $this->field->args( 'repeatable' ) ) {
			return false;
		}

		$values_array = $this->value;

		$new_value = array();
		foreach ( $values_array as $iterator => $this->value ) {
			if ( $this->value ) {
				$val = $this->$method( true );
				if ( ! empty( $val ) ) {
					$new_value[] = $val;
				}
			}
		}

		$this->value = $new_value;

		return empty( $this->value ) ? null : $this->value;
	}

	/**
	 * Determine if passed value is an empty array
	 *
	 * @since  2.0.6
	 * @param  mixed $to_check Value to check.
	 * @return boolean         Whether value is an array that's empty
	 */
	public function _is_empty_array( $to_check ) {
		if ( is_array( $to_check ) ) {
			$cleaned_up = array_filter( $to_check );
			return empty( $cleaned_up );
		}
		return false;
	}
}
