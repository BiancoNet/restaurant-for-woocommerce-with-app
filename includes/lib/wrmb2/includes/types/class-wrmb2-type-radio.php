<?php
/**
 * It is the type radio.
 *
 * @package woorestaurant
 */

/**
 * WRMB radio field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Radio extends WRMB2_Type_Multi_Base {

	/**
	 * The type of radio field
	 *
	 * @var string
	 */
	public $type = 'radio';

	/**
	 * Constructor
	 *
	 * @since 2.2.2
	 *
	 * @param WRMB2_Types $types It is the types.
	 * @param array       $args It is having arguments.
	 * @param string      $type It is type.
	 */
	public function __construct( WRMB2_Types $types, $args = array(), $type = '' ) {
		parent::__construct( $types, $args );
		$this->type = $type ? $type : $this->type;
	}

	/**
	 * It is the Render function.
	 */
	public function render() {
		$args = $this->parse_args(
			$this->type,
			array(
				'class'   => 'wrmb2-radio-list wrmb2-list',
				'options' => $this->concat_items(
					array(
						'label'  => 'test',
						'method' => 'list_input',
					)
				),
				'desc'    => $this->_desc( true ),
			)
		);

		return $this->rendered( $this->ul( $args ) );
	}

	/**
	 * It is the ul function.
	 *
	 * @param array $a it is the argument method.
	 */
	protected function ul( $a ) {
		return sprintf( '<ul class="%s">%s</ul>%s', $a['class'], $a['options'], $a['desc'] );
	}
}
