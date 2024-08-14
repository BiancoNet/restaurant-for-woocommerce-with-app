<?php
/**
 * It is the Type Text file.
 *
 * @package woorestaurant
 */

/**
 * WRMB text field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Text extends WRMB2_Type_Counter_Base {

	/**
	 * The type of field
	 *
	 * @var string
	 */
	public $type = 'input';

	/**
	 * Constructor.
	 *
	 * @since 2.2.2
	 *
	 * @param WRMB2_Types $types It is types.
	 * @param array       $args It is arguments.
	 * @param array       $type It is type.
	 */
	public function __construct( WRMB2_Types $types, $args = array(), $type = '' ) {
		parent::__construct( $types, $args );
		$this->type = $type ? $type : $this->type;
	}

	/**
	 * Handles outputting an 'input' element.
	 *
	 * @since  1.1.0
	 * @param  array $args Override arguments.
	 * @return string       Form input element
	 */
	public function render( $args = array() ) {
		$args = empty( $args ) ? $this->args : $args;
		$a    = $this->parse_args(
			$this->type,
			array(
				'type'            => 'text',
				'class'           => 'wpc-settings-input',
				'name'            => $this->_name(),
				'id'              => $this->_id(),
				'value'           => $this->field->escaped_value(),
				'desc'            => $this->_desc( true ),
				'js_dependencies' => array(),
			),
			$args
		);

		// Add character counter?
		$a = $this->maybe_update_attributes_for_char_counter( $a );
		return $this->rendered(
			sprintf( '<input%s/>', $this->concat_attrs( $a, array( 'desc' ) ), $a['desc'] )
		);
	}
}
