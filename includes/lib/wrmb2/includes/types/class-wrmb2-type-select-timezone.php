<?php
/**
 * It is the Type Select Timezone.
 *
 * @package woorestaurant
 */

/**
 * WRMB select_timezone field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Select_Timezone extends WRMB2_Type_Select {
	/**
	 * It is the Render function.
	 */
	public function render() {

		$this->field->args['default'] = $this->field->get_default()
			? $this->field->get_default()
			: WRMB2_Utils::timezone_string();

		$this->args = wp_parse_args(
			$this->args,
			array(
				'class'   => 'wrmb2_select wrmb2-select-timezone',
				'options' => wp_timezone_choice( $this->field->escaped_value() ),
				'desc'    => $this->_desc(),
			)
		);

		return parent::render();
	}
}
