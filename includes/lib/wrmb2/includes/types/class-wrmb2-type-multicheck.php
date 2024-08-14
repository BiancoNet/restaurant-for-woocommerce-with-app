<?php
/**
 * It is the type multicheck.
 *
 * @package woorestaurant
 */

/**
 * WRMB multicheck field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Multicheck extends WRMB2_Type_Radio {

	/**
	 * The type of radio field
	 *
	 * @var string
	 */
	public $type = 'checkbox';

	/**
	 * It is the Render function.
	 *
	 * @param array $args it is the argument method.
	 */
	public function render( $args = array() ) {
		$classes = false === $this->field->args( 'select_all_button' )
			? 'wrmb2-checkbox-list no-select-all wrmb2-list'
			: 'wrmb2-checkbox-list wrmb2-list';

		$args = $this->parse_args(
			$this->type,
			array(
				'class'   => $classes,
				'options' => $this->concat_items(
					array(
						'name'   => $this->_name() . '[]',
						'method' => 'list_input_checkbox',
					)
				),
				'desc'    => $this->_desc( true ),
			)
		);

		return $this->rendered( $this->ul( $args ) );
	}
}
