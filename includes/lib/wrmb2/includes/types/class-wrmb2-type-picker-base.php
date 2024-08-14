<?php
/**
 * It is the type picker base.
 *
 * @package woorestaurant
 */

/**
 * WRMB Picker base field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
abstract class WRMB2_Type_Picker_Base extends WRMB2_Type_Text {

	/**
	 * Parse the picker attributes.
	 *
	 * @since  2.2.0
	 * @param  string $arg  'date' or 'time'.
	 * @param  array  $args Optional arguments to modify (else use $this->field->args['attributes']).
	 * @return array         Array of field attributes.
	 */
	public function parse_picker_options( $arg = 'date', $args = array() ) {
		$att    = 'data-' . $arg . 'picker';
		$update = empty( $args );
		$atts   = array();
		$format = $this->field->args( $arg . '_format' );

		$js_format = WRMB2_Utils::php_to_js_dateformat( $format );
		if ( $js_format ) {

			if ( $update ) {
				$atts = $this->field->args( 'attributes' );
			} else {
				$atts = isset( $args['attributes'] )
					? $args['attributes']
					: $atts;
			}

			// Don't override user-provided datepicker values.
			$data = isset( $atts[ $att ] )
				? json_decode( $atts[ $att ], true )
				: array();

			$data[ $arg . 'Format' ] = $js_format;
			$atts[ $att ]            = function_exists( 'wp_json_encode' )
				? wp_json_encode( $data )
				: json_encode( $data );
		}

		if ( $update ) {
			$this->field->args['attributes'] = $atts;
		}

		return array_merge( $args, $atts );
	}
}
