<?php
/**
 * It is the Oembed file.
 *
 * @package woorestaurant
 */

/**
 * WRMB oembed field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Oembed extends WRMB2_Type_Text {
	/**
	 * It is the Render function.
	 *
	 * @param array $args it is the argument method.
	 */
	public function render( $args = array() ) {
		$field = $this->field;

		$meta_value = trim( $field->escaped_value() );

		$oembed = ! empty( $meta_value )
			? wrmb2_ajax()->get_oembed(
				array(
					'url'         => $field->escaped_value(),
					'object_id'   => $field->object_id,
					'object_type' => $field->object_type,
					'oembed_args' => array(
						'width' => '640',
					),
					'field_id'    => $this->_id( '', false ),
				)
			)
			: '';

		return parent::render(
			array(
				'class'           => 'wrmb2-oembed regular-text',
				'data-objectid'   => $field->object_id,
				'data-objecttype' => $field->object_type,
			)
		)
		. '<p class="wrmb-spinner spinner"></p>'
		. '<div id="' . $this->_id( '-status' ) . '" class="wrmb2-media-status ui-helper-clearfix embed_wrap">' . $oembed . '</div>';
	}
}
