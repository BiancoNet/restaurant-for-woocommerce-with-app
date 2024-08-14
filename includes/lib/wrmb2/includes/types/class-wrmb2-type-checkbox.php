<?php
/**
 * It is the type checkbox.
 *
 * @package woorestaurant
 */

/**
 * WRMB checkbox field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Checkbox extends WRMB2_Type_Text {

	/**
	 * If checkbox is checked
	 *
	 * @var mixed
	 */
	public $is_checked = null;

	/**
	 * Constructor
	 *
	 * @since 2.2.2
	 *
	 * @param WRMB2_Types $types      Object for the field type.
	 * @param array       $args       Array of arguments for the type.
	 * @param mixed       $is_checked Whether or not the field is checked, or default value.
	 */
	public function __construct( WRMB2_Types $types, $args = array(), $is_checked = null ) {
		parent::__construct( $types, $args );
		$this->is_checked = $is_checked;
	}

	/**
	 * Render the field for the field type.
	 *
	 * @since 2.2.2
	 *
	 * @param array $args Array of arguments for the rendering.
	 * @return WRMB2_Type_Base|string
	 */
	public function render( $args = array() ) {
		$defaults = array(
			'type'  => 'checkbox',
			'class' => 'wrmb2-option wrmb2-list ',
			'value' => 'on',
			'desc'  => '',
		);

		$meta_value = $this->field->escaped_value();

		$is_checked = null === $this->is_checked
			? ! empty( $meta_value )
			: $this->is_checked;

		if ( $is_checked ) {
			$defaults['checked'] = 'checked';
		}
		if ( isset( $this->types->field->args['woo_switch'] ) && true == $this->types->field->args['woo_switch'] ) {
			$defaults['class'] = 'wpcafe-admin-control-input';
		}

		$attrs = $this->concat_attrs( $defaults, array( 'desc', 'options' ) );
		$args  = $this->parse_args( 'checkbox', $defaults );
		if ( isset( $this->types->field->args['woo_switch'] ) && true == $this->types->field->args['woo_switch'] ) {
			return parent::render( $args ) . '<label for="' . $this->_id( '', false ) . '" class="wpcafe_switch_button_label" data-text="" data-textalt=""></label>';

		}

		return $this->rendered(
			sprintf(
				'%s <label for="%s">%s</label>',
				parent::render( $args ),
				$this->_id( '', false ),
				$this->_desc()
			)
		);
	}
}
