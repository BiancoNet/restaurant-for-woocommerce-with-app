<?php
/**
 * It is the Type Title file.
 *
 * @package woorestaurant
 */

/**
 * WRMB title field type.
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Title extends WRMB2_Type_Base {

	/**
	 * Handles outputting an 'title' element.
	 *
	 * @return string Heading element
	 */
	public function render() {
		$name = $this->field->args( 'name' );
		$tag  = 'span';

		if ( ! empty( $name ) ) {
			$tag = 'post' == $this->field->object_type ? 'h5' : 'h3';
		}

		$a = $this->parse_args(
			'title',
			array(
				'tag'   => $tag,
				'class' => empty( $name ) ? 'wrmb2-metabox-title-anchor' : 'wrmb2-metabox-title',
				'name'  => $name,
				'desc'  => $this->_desc( true ),
				'id'    => str_replace( '_', '-', sanitize_html_class( $this->field->id() ) ),
			)
		);

		return $this->rendered(
			sprintf(
				'<%1$s %2$s>%3$s</%1$s>%4$s',
				$a['tag'],
				$this->concat_attrs( $a, array( 'tag', 'name', 'desc' ) ),
				$a['name'],
				$a['desc']
			)
		);
	}
}
