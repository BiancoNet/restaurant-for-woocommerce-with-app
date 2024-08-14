<?php
/**
 * WRMB2 Post Search field.
 *
 * @package woorestaurant
 */

/**
 * It is the class WRMB2_Post_Search_Field.
 */
class WRMB2_Post_Search_Field {

	/**
	 * It is the single instance variable.
	 *
	 * @var $single_instance
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  0.2.4
	 * @return WRMB2_Post_Search_Field A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * It is the constructor method.
	 */
	protected function __construct() {
		add_action( 'wrmb2_render_post_search_text', array( $this, 'render_field' ), 10, 5 );
		add_action( 'wrmb2_after_form', array( $this, 'render_js' ), 10, 4 );
		add_action( 'wrmb2_post_search_field_add_find_posts_div', array( $this, 'add_find_posts_div' ) );
		add_action( 'admin_init', array( $this, 'ajax_find_posts' ) );
	}

	/**
	 * To render field.
	 *
	 * @param object $field it is the field.
	 * @param string $escaped_value it is the escaped value.
	 * @param int    $object_id it is the object id.
	 * @param string $object_type it is the object type.
	 * @param object $field_type it is the field type.
	 */
	public function render_field( $field, $escaped_value, $object_id, $object_type, $field_type ) {
		// phpcs:ignore
		echo ' ' . ( $field_type->input(
			array(
				'data-search' => json_encode(
					array(
						'posttype'       => $field->args( 'post_type' ),
						'selecttype'     => 'radio' == $field->args( 'select_type' ) ? 'radio' : 'checkbox',
						'selectbehavior' => 'replace' == $field->args( 'select_behavior' ) ? 'replace' : 'add',
						'errortxt'       => wp_kses_post( $field_type->_text( 'error_text', __( 'An error has occurred. Please reload the page and try again.' ) ) ),
						'findtxt'        => wp_kses_post( $field_type->_text( 'find_text', __( 'Find Posts or Pages' ) ) ),
					)
				),
			)
		) );
	}

	/**
	 * To render js.
	 *
	 * @param int    $wrmb_id it is the id.
	 * @param int    $object_id it is the object id.
	 * @param string $object_type it is the object type.
	 * @param object $wrmb it is the wrmb object.
	 */
	public function render_js( $wrmb_id, $object_id, $object_type, $wrmb ) {
		static $rendered;

		if ( $rendered ) {
			return;
		}

		$fields = $wrmb->prop( 'fields' );

		if ( ! is_array( $fields ) ) {
			return;
		}

		$has_post_search_field = $this->has_post_search_text_field( $fields );

		if ( ! $has_post_search_field ) {
			return;
		}

		// JS needed for modal.
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wp-backbone' );

		if ( ! is_admin() ) {
			// Will need custom styling!.
			// @todo add styles for front-end.
			require_once ABSPATH . 'wp-admin/includes/template.php';
			do_action( 'wrmb2_post_search_field_add_find_posts_div' );
		}

		// markup needed for modal.
		add_action( 'admin_footer', 'find_posts_div' );
		wp_enqueue_script( 'wrmb2-post-search-ex', plugins_url( '/js/search.js', __FILE__ ), array( 'jquery' ), '1.0' . time() );
		// @TODO this should really be in its own JS file.
		?>
		<?php

		$rendered = true;
	}

	/**
	 * Check it have post search text field.
	 *
	 * @param array $fields it is having fields.
	 * @return bool
	 */
	public function has_post_search_text_field( $fields ) {
		foreach ( $fields as $field ) {
			if ( isset( $field['fields'] ) ) {
				if ( $this->has_post_search_text_field( $field['fields'] ) ) {
					return true;
				}
			}
			if ( 'post_search_text' == $field['type'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add the find posts div via a hook so we can relocate it manually
	 */
	public function add_find_posts_div() {
		add_action( 'wp_footer', 'find_posts_div' );
	}


	/**
	 * Check to see if we have a post type set and, if so, add the.
	 * pre_get_posts action to set the queried post type.
	 */
	public function ajax_find_posts() {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'find-posts' ) ) {
				return;
			}
		}

		if (
			defined( 'DOING_AJAX' )
			&& DOING_AJAX
			&& isset( $_POST['wrmb2_post_search'], $_POST['action'], $_POST['post_search_cpt'] )
			&& 'find_posts' == $_POST['action']
			&& ! empty( $_POST['post_search_cpt'] )
		) {
			add_action( 'pre_get_posts', array( $this, 'set_post_type' ) );
		}
	}

	/**
	 * Set the post type via pre_get_posts.
	 *
	 * @param object $query  The query instance.
	 */
	public function set_post_type( $query ) {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'post-type' ) ) {
				return;
			}
		}

		if ( isset( $_POST['post_search_cpt'] ) ) {
			$types = wp_kses_post( wp_unslash( $_POST['post_search_cpt'] ) );
		}
		$types = is_array( $types ) ? array_map( 'wp_kses_post', $types ) : wp_kses_post( $types );
		$query->set( 'post_type', $types );
		$query->set( 'post_status', array( 'publish' ) );
	}
}
WRMB2_Post_Search_Field::get_instance();

// preserve a couple functions for back-compat.

if ( ! function_exists( 'wrmb2_post_search_render_field' ) ) {
	/**
	 * Post search render field.
	 *
	 * @param object $field it is the field.
	 * @param string $escaped_value it is the escaped value.
	 * @param int    $object_id it is the object id.
	 * @param string $object_type it is the object type.
	 * @param object $field_type it is the field type.
	 */
	// phpcs:ignore
	function wrmb2_post_search_render_field( $field, $escaped_value, $object_id, $object_type, $field_type ) {
		_deprecated_function( __FUNCTION__, '0.2.4', 'Please access these methods through the WRMB2_Post_Search_Field::get_instance() object.' );

		return WRMB2_Post_Search_Field::get_instance()->render_field( $field, $escaped_value, $object_id, $object_type, $field_type );
	}
}

// Remove old versions.
remove_action( 'wrmb2_render_post_search_text', 'wrmb2_post_search_render_field', 10, 5 );
remove_action( 'wrmb2_after_form', 'wrmb2_post_search_render_js', 10, 4 );

if ( ! function_exists( 'wrmb2_has_post_search_text_field' ) ) {
	/**
	 * Check post search text field.
	 *
	 * @param object $fields The query instance.
	 */
	function wrmb2_has_post_search_text_field( $fields ) {
		_deprecated_function( __FUNCTION__, '0.2.4', 'Please access these methods through the WRMB2_Post_Search_Field::get_instance() object.' );

		return WRMB2_Post_Search_Field::get_instance()->has_post_search_text_field( $fields );
	}
}

add_action(
	'wrmb2_render_post_search_text',
	function ( $field ) {
		if ( 'woores_product_ids' != $field->args['_id'] ) {
			return;
		}
		$args = array(
			'post_type'  => $field->args['post_type'],
			'post_count' => -1,
			'orderby'    => 'post__in',
			'post__in'   => explode( ',', $field->escaped_value ),
		);

		$posts = get_posts( $args );
		echo '<div class="p-list-title">';
		foreach ( $posts as $post ) {
			echo '<p style="padding:0; margin: 0;">';
			printf( '%d: <a href="%s">%s</a>', wp_kses_post( $post->ID ), wp_kses_post( get_edit_post_link( $post->ID ) ), wp_kses_post( $post->post_title ) );
			echo '</p>';
		}
		echo '</div>';
	},
	20
);