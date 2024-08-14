<?php
/**
 * It is the type taxonomy multicheck hierarchical.
 *
 * @package woorestaurant
 */

/**
 * WRMB taxonomy_multicheck_hierarchical field type
 *
 * @since  2.2.5
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Taxonomy_Multicheck_Hierarchical extends WRMB2_Type_Taxonomy_Multicheck {

	/**
	 * Parent term ID when looping hierarchical terms.
	 *
	 * @var integer
	 */
	protected $parent = 0;

	/**
	 * To render method.
	 */
	public function render() {
		return $this->rendered(
			$this->types->radio(
				array(
					'class'   => $this->get_wrapper_classes(),
					'options' => $this->get_term_options(),
				),
				'taxonomy_multicheck_hierarchical'
			)
		);
	}

	/**
	 * To list term input.
	 *
	 * @param object $term It is the term object.
	 * @param object $saved_terms It is the saved terms.
	 */
	protected function list_term_input( $term, $saved_terms ) {
		$options  = parent::list_term_input( $term, $saved_terms );
		$children = $this->build_children( $term, $saved_terms );

		if ( ! empty( $children ) ) {
			$options .= $children;
		}

		return $options;
	}
}
