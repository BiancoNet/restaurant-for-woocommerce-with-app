<?php
/**
 * It is type text date file.
 *
 * @package woorestaurant
 */

/**
 * WRMB text_date field type.
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Text_Date extends WRMB2_Type_Picker_Base {
	/**
	 * To render function.
	 *
	 * @param array $args It is the arguments.
	 */
	public function render( $args = array() ) {
		$args = $this->parse_args(
			'text_date',
			array(
				'class'           => 'wrmb2-text-small wrmb2-datepicker',
				'value'           => $this->field->get_timestamp_format(),
				'desc'            => $this->_desc(),
				'js_dependencies' => array( 'jquery-ui-core', 'jquery-ui-datepicker' ),
			)
		);

		// phpcs:ignore
		if ( false === strpos( $args['class'], 'timepicker' ) ) {
			$this->parse_picker_options( 'date' );
		}

		return parent::render( $args );
	}
}
