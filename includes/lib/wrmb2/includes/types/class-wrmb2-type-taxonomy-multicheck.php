<?php
/**
 * It is the taxonomy multicheck.
 *
 * @package woorestaurant
 */

/**
 * WRMB taxonomy_multicheck field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Taxonomy_Multicheck extends WRMB2_Type_Taxonomy_Base {
	/**
	 * It is the counter.
	 *
	 * @var $counter
	 */
	protected $counter = 0;

	/**
	 * It is the render function.
	 */
	public function render() {
		return $this->rendered(
			$this->types->radio(
				array(
					'class'   => $this->get_wrapper_classes(),
					'options' => $this->get_term_options(),
				),
				'taxonomy_multicheck'
			)
		);
	}

	/**
	 * It is the get terms options function.
	 */
	protected function get_term_options() {
		$all_terms = $this->get_terms();

		if ( ! $all_terms || is_wp_error( $all_terms ) ) {
			return $this->no_terms_result( $all_terms );
		}

		return $this->loop_terms( $all_terms, $this->get_object_term_or_default() );
	}

	/**
	 * It is t he loop terms function.
	 *
	 * @param array $all_terms it is the all terms array.
	 * @param array $saved_terms it is the saved terms array.
	 */
	protected function loop_terms( $all_terms, $saved_terms ) {
		$options = '';
		foreach ( $all_terms as $term ) {
			$options .= $this->list_term_input( $term, $saved_terms );
		}

		return $options;
	}

	/**
	 * It is the list term input.
	 *
	 * @param object $term it is the object term.
	 * @param array  $saved_terms it is the saved terms.
	 */
	protected function list_term_input( $term, $saved_terms ) {
		$args = array(
			'value' => $term->slug,
			'label' => $term->name,
			'type'  => 'checkbox',
			'name'  => $this->_name() . '[]',
		);

		if ( is_array( $saved_terms ) && in_array( $term->slug, $saved_terms ) ) {
			$args['checked'] = 'checked';
		}

		return $this->list_input( $args, ++$this->counter );
	}

	/**
	 * TO get object term or default.
	 */
	public function get_object_term_or_default() {
		$saved_terms = $this->get_object_terms();

		return is_wp_error( $saved_terms ) || empty( $saved_terms )
			? $this->field->get_default()
			: wp_list_pluck( $saved_terms, 'slug' );
	}

	/**
	 * To get wrapper classes.
	 */
	protected function get_wrapper_classes() {
		$classes = 'wrmb2-checkbox-list wrmb2-list';
		if ( false === $this->field->args( 'select_all_button' ) ) {
			$classes .= ' no-select-all';
		}

		return $classes;
	}
}
