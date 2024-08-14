<?php
/**
 * It is the Type Text Time.
 *
 * @package woorestaurant
 */

/**
 * WRMB text_time field type.
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Text_Time extends WRMB2_Type_Text_Date {
	/**
	 * To render methods.
	 *
	 * @param array $args It is the arguments.
	 */
	public function render( $args = array() ) {
		$this->args = $this->parse_picker_options(
			'time',
			wp_parse_args(
				$this->args,
				array(
					'class'           => 'wrmb2-timepicker text-time wpc-settings-input',
					'value'           => $this->field->get_timestamp_format( 'time_format' ),
					'js_dependencies' => array( 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-datetimepicker' ),
				)
			)
		);

		return parent::render();
	}
}
