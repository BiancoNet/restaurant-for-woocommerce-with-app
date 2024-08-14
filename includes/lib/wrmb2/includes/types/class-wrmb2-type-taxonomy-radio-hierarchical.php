<?php
/**
 * It is the type taxonomy radio hierarchical file.
 *
 * @package woorestaurant
 */

/**
 * WRMB taxonomy_radio_hierarchical field type
 *
 * @since  2.2.5
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Taxonomy_Radio_Hierarchical extends WRMB2_Type_Taxonomy_Radio {

	/**
	 * Parent term ID when looping hierarchical terms.
	 *
	 * @var integer
	 */
	protected $parent = 0;

	/**
	 * It is the render function.
	 */
	public function render() {
		return $this->rendered(
			$this->types->radio(
				array(
					'options' => $this->get_term_options(),
				),
				'taxonomy_radio_hierarchical'
			)
		);
	}

	/**
	 * List term input.
	 *
	 * @param array $term It is the term.
	 * @param array $saved_term It is the saved term.
	 */
	protected function list_term_input( $term, $saved_term ) {
		$options  = parent::list_term_input( $term, $saved_term );
		$children = $this->build_children( $term, $saved_term );

		if ( ! empty( $children ) ) {
			$options .= $children;
		}

		return $options;
	}
}
