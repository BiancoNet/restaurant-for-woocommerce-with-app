<?php
/**
 * It is the Type text datetime timestamp timezone file.
 *
 * @package woorestaurant
 */

/**
 * WRMB text_datetime_timestamp_timezone field type.
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Text_Datetime_Timestamp_Timezone extends WRMB2_Type_Base {
	/**
	 * It is the render method.
	 *
	 * @param array $args It is the argument.
	 */
	public function render( $args = array() ) {
		$field = $this->field;

		$value = $field->escaped_value();
		if ( empty( $value ) ) {
			$value = $field->get_default();
		}

		$args = wp_parse_args(
			$this->args,
			array(
				'value'                   => $value,
				'desc'                    => $this->_desc( true ),
				'text_datetime_timestamp' => array(),
				'select_timezone'         => array(),
			)
		);

		$args['value'] = $value;
		if ( is_array( $args['value'] ) ) {
			$args['value'] = '';
		}

		$datetime = maybe_unserialize( $args['value'] );
		$value    = '';
		$tzstring = '';

		if ( $datetime && $datetime instanceof DateTime ) {
			$tz       = $datetime->getTimezone();
			$tzstring = $tz->getName();
			$value    = $datetime->getTimestamp();
		}

		$timestamp_args     = wp_parse_args(
			$args['text_datetime_timestamp'],
			array(
				'desc'     => '',
				'value'    => $value,
				'rendered' => true,
			)
		);
		$datetime_timestamp = $this->types->text_datetime_timestamp( $timestamp_args );

		$timezone_select_args = wp_parse_args(
			$args['select_timezone'],
			array(
				'class'    => 'wrmb2_select wrmb2-select-timezone',
				'name'     => $this->_name( '[timezone]' ),
				'id'       => $this->_id( '_timezone' ),
				'options'  => wp_timezone_choice( $tzstring ),
				'desc'     => $args['desc'],
				'rendered' => true,
			)
		);
		$select               = $this->types->select( $timezone_select_args );

		return $this->rendered(
			$datetime_timestamp . "\n" . $select
		);
	}
}
