<?php
/**
 * It is the Type Textarea file.
 *
 * @package woorestaurant
 */

/**
 * WRMB textarea field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Textarea extends WRMB2_Type_Counter_Base {

	/**
	 * Handles outputting an 'textarea' element.
	 *
	 * @since  1.1.0
	 * @param  array $args Override arguments.
	 * @return string       Form textarea element.
	 */
	public function render( $args = array() ) {
		$args = empty( $args ) ? $this->args : $args;
		$a    = $this->parse_args(
			'textarea',
			array(
				'class' => 'wrmb2_textarea',
				'name'  => $this->_name(),
				'id'    => $this->_id(),
				'cols'  => 60,
				'rows'  => 10,
				'value' => $this->field->escaped_value( 'esc_textarea' ),
				'desc'  => $this->_desc( true ),
			),
			$args
		);

		// Add character counter?
		$a = $this->maybe_update_attributes_for_char_counter( $a );

		return $this->rendered(
			sprintf( '<textarea%s>%s</textarea>%s', $this->concat_attrs( $a, array( 'desc', 'value' ) ), $a['value'], $a['desc'] )
		);
	}
}
