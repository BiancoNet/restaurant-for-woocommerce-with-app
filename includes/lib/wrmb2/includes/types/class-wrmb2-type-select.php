<?php
/**
 * It is the Type Select file.
 *
 * @package woorestaurant
 */

/**
 * WRMB select field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Select extends WRMB2_Type_Multi_Base {
	/**
	 * It is the Render function.
	 */
	public function render() {
		$a = $this->parse_args(
			'select',
			array(
				'class'   => 'wrmb2_select wpc-form-control wpc_select2 wpc-settings-input',
				'name'    => $this->_name(),
				'id'      => $this->_id(),
				'desc'    => $this->_desc( true ),
				'options' => $this->concat_items(),
			)
		);

		$attrs = $this->concat_attrs( $a, array( 'desc', 'options' ) );

		return $this->rendered(
			sprintf( '<select%s>%s</select>', $attrs, $a['options'] )
		);
	}
}
