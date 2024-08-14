<?php
/**
 * It is the taxonomy radio file.
 *
 * @package woorestaurant
 */

/**
 * WRMB taxonomy_radio field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_Taxonomy_Radio extends WRMB2_Type_Taxonomy_Base {
	/**
	 * It is the counter.
	 *
	 * @var $counter.
	 */
	protected $counter = 0;

	/**
	 * To render method.
	 */
	public function render() {
		return $this->rendered(
			$this->types->radio(
				array(
					'options' => $this->get_term_options(),
				),
				'taxonomy_radio'
			)
		);
	}

	/**
	 * To get term options.
	 */
	protected function get_term_options() {
		$all_terms = $this->get_terms();

		if ( ! $all_terms || is_wp_error( $all_terms ) ) {
			return $this->no_terms_result( $all_terms );
		}

		$saved_term  = $this->get_object_term_or_default();
		$option_none = $this->field->args( 'show_option_none' );
		$options     = '';

		if ( ! empty( $option_none ) ) {

			$field_id = $this->_id( '', false );

			/**
			 * Default (option-none) taxonomy-radio value.
			 *
			 * @since 1.3.0
			 *
			 * @param string $option_none_value Default (option-none) taxonomy-radio value.
			 */
			$option_none_value = apply_filters( 'wrmb2_taxonomy_radio_default_value', '' );

			/**
			 * Default (option-none) taxonomy-radio value.
			 *
			 * The dynamic portion of the hook name, $field_id, refers to the field id attribute.
			 *
			 * @since 1.3.0
			 *
			 * @param string $option_none_value Default (option-none) taxonomy-radio value.
			 */
			$option_none_value = apply_filters( "wrmb2_taxonomy_radio_{$field_id}_default_value", $option_none_value );

			$options .= $this->list_term_input(
				(object) array(
					'slug' => $option_none_value,
					'name' => $option_none,
				),
				$saved_term
			);
		}

		$options .= $this->loop_terms( $all_terms, $saved_term );

		return $options;
	}

	/**
	 * Loop terms.
	 *
	 * @param array $all_terms It is the all terms.
	 * @param array $saved_term It is the saved terms.
	 */
	protected function loop_terms( $all_terms, $saved_term ) {
		$options = '';
		foreach ( $all_terms as $term ) {
			$options .= $this->list_term_input( $term, $saved_term );
		}

		return $options;
	}

	/**
	 * List term input.
	 *
	 * @param object $term It is the term.
	 * @param object $saved_term It is the saved term.
	 */
	protected function list_term_input( $term, $saved_term ) {
		$args = array(
			'value' => $term->slug,
			'label' => $term->name,
		);

		if ( $saved_term == $term->slug ) {
			$args['checked'] = 'checked';
		}

		return $this->list_input( $args, ++$this->counter );
	}
}
