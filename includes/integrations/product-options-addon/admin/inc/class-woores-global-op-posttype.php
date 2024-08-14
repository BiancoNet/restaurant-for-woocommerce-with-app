<?php
/**
 * This is the Global op Post Type.
 *
 * @package woorestaurant
 */

/**
 * Class Global OP PostType.
 */
class Woores_Global_Op_Posttype {
	/**
	 * This is the Constructor Function.
	 */
	public function __construct() {
		add_action( 'init', array( &$this, 'register_post_type' ) );
		add_action( 'wrmb2_admin_init', array( &$this, 'register_metabox' ) );
		add_filter( 'manage_woores_modifiers_posts_columns', array( &$this, '_edit_columns' ), 99 );
		add_action( 'manage_woores_modifiers_posts_custom_column', array( &$this, '_custom_columns_content' ), 12 );
	}

	/**
	 * To register post type.
	 */
	public function register_post_type() {
		$labels  = array(
			'menu_name'          => esc_html__( 'Create Modifiers', 'product-options-addon' ),
			'name'               => esc_html__( 'Modifiers', 'product-options-addon' ),
			'singular_name'      => esc_html__( 'Option', 'product-options-addon' ),
			'add_new'            => esc_html__( 'Add New Option', 'product-options-addon' ),
			'add_new_item'       => esc_html__( 'Add New Option', 'product-options-addon' ),
			'edit_item'          => esc_html__( 'Edit Option', 'product-options-addon' ),
			'new_item'           => esc_html__( 'New Option', 'product-options-addon' ),
			'all_items'          => esc_html__( 'Modifiers', 'product-options-addon' ),
			'view_item'          => esc_html__( 'View Option', 'product-options-addon' ),
			'search_items'       => esc_html__( 'Search Option', 'product-options-addon' ),
			'not_found'          => esc_html__( 'No Option found', 'product-options-addon' ),
			'not_found_in_trash' => esc_html__( 'No Option found in Trash', 'product-options-addon' ),
			'parent_item_colon'  => '',
		);
		$rewrite = false;
		$args    = array(
			'labels'             => $labels,
			'supports'           => array( 'title' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'woo-restaurant',
			'menu_icon'          => '',
			'query_var'          => true,
			'capability_type'    => 'page',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 1,
			'rewrite'            => $rewrite,
			'taxonomies'         => array( 'product_cat' ),
			'show_in_rest'       => false,
		);
		register_post_type( 'woores_modifiers', $args );
	}

	/**
	 * Register metadata.
	 */
	public function register_metabox() {
		$prefix = 'woores_';
	}

	/**
	 * To edit column.
	 *
	 * @param array $columns it is having column names.
	 */
	public function _edit_columns( $columns ) {
		global $wpdb;
		$columns['cate'] = esc_html__( 'Categories', 'product-options-addon' );
		return $columns;
	}

	/**
	 * Custom columns content.
	 *
	 * @param array $column it is having column content.
	 */
	public function _custom_columns_content( $column ) {
		global $post;
		switch ( $column ) {
			case 'cate':
				// Get the genres for the post.
				$terms = get_the_terms( $post->ID, 'product_cat' );

				// If terms were found.
				if ( ! empty( $terms ) ) {

					$out = array();

					// Loop through each term, linking to the 'edit posts' page for the specific term.
					foreach ( $terms as $term ) {
						/* translators: %s is replaced with the URL of post */
						$out[] = sprintf(
							'<a href="%s">%s</a>',
							esc_url(
								add_query_arg(
									array(
										'post_type'   => $post->post_type,
										'product_cat' => $term->slug,
									),
									'edit.php'
								)
							),
							esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'product_cat', 'display' ) )
						);
					}

					// Join the terms, separating them with a comma.
					// phpcs:ignore
					echo join( ', ', $out );
				} else {
					esc_html_e( 'No Categories', 'product-options-addon' );
				}
				break;
			default:
				break;
		}
	}
}
$woores_global_pp_posttype = new Woores_Global_Op_Posttype();
