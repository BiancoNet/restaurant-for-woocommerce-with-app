<?php
/**
 * This is the view init file.
 *
 * @package woorestaurant
 */

// Option addon.
$all_options = get_option( 'woo_restaurant_options' );

// shortcode.
require plugin_dir_path( __FILE__ ) . 'shortcodes/woo-food-list.php';
require plugin_dir_path( __FILE__ ) . 'shortcodes/woo-food-grid.php';
require plugin_dir_path( __FILE__ ) . 'shortcodes/woo-food-table.php';
require plugin_dir_path( __FILE__ ) . 'shortcodes/woo-food-carousel.php';
require plugin_dir_path( __FILE__ ) . 'shortcodes/woo-food-opcls-time.php';
require plugin_dir_path( __FILE__ ) . 'shortcodes/woo-food-menu-group.php';
require plugin_dir_path( __FILE__ ) . 'shortcodes/woo-food-orderbt.php';

// woo hook.
require plugin_dir_path( __FILE__ ) . 'part/woo-hook.php';
// Menu by date.
if ( isset( $all_options['woo_restaurant_foodby_date'] ) && 'yes' == $all_options['woo_restaurant_foodby_date'] ) {
	include plugin_dir_path( __FILE__ ) . 'part/class-wooresta-menu-by-date.php';
}
if ( isset( $all_options['woo_restaurant_foodby_odmt'] ) && 'yes' == $all_options['woo_restaurant_foodby_odmt'] ) {
	include plugin_dir_path( __FILE__ ) . 'part/food-by-odmt.php';
}
if ( isset( $all_options['woo_restaurant_enable_loc'] ) && 'yes' == $all_options['woo_restaurant_enable_loc'] ) {
	include plugin_dir_path( __FILE__ ) . 'part/food-by-loc.php';
}
require plugin_dir_path( __FILE__ ) . 'part/class-wooresta-minmax-quantity.php';
// Radius shipping.
require plugin_dir_path( __FILE__ ) . 'part/shipping.php';
// Tipping.
require plugin_dir_path( __FILE__ ) . 'part/tip.php';
// Order on whatsapp.
require plugin_dir_path( __FILE__ ) . 'part/order-whatsapp.php';

if ( ! function_exists( 'woo_restaurant_starts_with' ) ) {
	/**
	 * Restaurant Start with.
	 *
	 * @param string $haystack it is the haystack variable.
	 * @param string $needle it is the needle variable.
	 */
	function woo_restaurant_starts_with( $haystack, $needle ) {
		// phpcs:ignore
		return ! strncmp( $haystack, $needle, strlen( $needle ) );
	}
}
if ( ! function_exists( 'woo_restaurant_get_google_fonts_url' ) ) {
	/**
	 * Get google fonts url.
	 *
	 * @param array $font_names it is having font names.
	 */
	function woo_restaurant_get_google_fonts_url( $font_names ) {
		$font_url = '';
		$font_url = add_query_arg( 'family', urlencode( implode( '|', $font_names ) ), '//fonts.googleapis.com/css' );
		return $font_url;
	}
}
if ( ! function_exists( 'woo_restaurant_get_google_font_name' ) ) {
	/**
	 * Get google fonts name.
	 *
	 * @param string $family_name it is the font family name.
	 */
	function woo_restaurant_get_google_font_name( $family_name ) {
		$name = $family_name;
		if ( woo_restaurant_startsWith( $family_name, 'http' ) ) {
			// $family_name is a full link, so first, we need to cut off the link.
			// phpcs:ignore
			$idx = strpos( $name, '=' );
			if ( $idx > -1 ) {
				$name = substr( $name, $idx );
			}
		}
		// phpcs:ignore
		$idx = strpos( $name, ':' );
		if ( $idx > -1 ) {
			$name = substr( $name, 0, $idx );
			$name = str_replace( '+', ' ', $name );
		}
		return $name;
	}
}
if ( ! function_exists( 'woo_restaurant_template_plugin' ) ) {
	/**
	 * Restaurant template plugin.
	 *
	 * @param string $page_name it is the page name.
	 * @param string $shortcode it is the shortcode.
	 */
	function woo_restaurant_template_plugin( $page_name, $shortcode = false ) {
		if ( isset( $shortcode ) && true == $shortcode ) {
			if ( locate_template( 'woorestaurant/content-shortcodes/content-' . $page_name . '.php' ) != '' ) {
				get_template_part( 'woorestaurant/content-shortcodes/content', $page_name );
			} else {
				$page_name = 'content-' . $page_name;
				$temp_url  = WOORESTAURANT_DIR . 'templates/content-shortcodes/' . $page_name . '.php';
				$temp_url  = apply_filters( 'woore_default_sctemplate_locate', $temp_url, $page_name );
				// phpcs:ignore
				include $temp_url;
			}
		} elseif ( '' != locate_template( 'woorestaurant/' . $page_name . '.php' ) ) {
			get_template_part( 'woorestaurant/' . $page_name );
		} else {
			$temp_url = WOORESTAURANT_DIR . 'templates/' . $page_name . '.php';
			$temp_url = apply_filters( 'woore_default_template_locate', $temp_url, $page_name, $shortcode );
			// phpcs:ignore
			include $temp_url;
		}
	}
}

if ( ! function_exists( 'woo_restaurant_query' ) ) {
	/**
	 * Restaurant Query.
	 *
	 * @param string $posttype it is the posttype.
	 * @param int    $count it is the count.
	 * @param object $order it is the order variable.
	 * @param string $orderby it is the orderby variable.
	 * @param string $cat it is the category variable.
	 * @param string $tag it is the tag variable.
	 * @param string $taxonomy it is the taxonomy variable.
	 * @param string $meta_key it is having meta keys.
	 * @param string $ids it is having ids.
	 * @param string $meta_value it is having values.
	 * @param string $page it is the page name.
	 * @param string $mult it is the mult.
	 * @param string $active_filter it is the active filters.
	 * @param string $feature it is the name of features.
	 * @param string $sloc it is the sloc.
	 */
	function woo_restaurant_query( $posttype, $count, $order, $orderby, $cat, $tag, $taxonomy, $meta_key, $ids, $meta_value = false, $page = false, $mult = false, $active_filter = false, $feature = false, $sloc = false ) {
		if ( isset( $active_filter ) && '' != $active_filter ) {
			$cat = $active_filter;
		}

		$posttype = 'product';
		if ( 'order_field' == $orderby ) {
			$meta_key = 'woo_restaurant_order';
			$orderby  = 'meta_value_num';
		}

		$posttype = explode( ',', $posttype );
		if ( ! empty( $ids ) ) { // specify IDs.
			$ids  = explode( ',', $ids );
			$args = array(
				'post_type'           => $posttype,
				'posts_per_page'      => $count,
				'post_status'         => array( 'publish' ),
				'post__in'            => $ids,
				'order'               => $order,
				'orderby'             => $orderby,
				'ignore_sticky_posts' => 1,
			);
		} elseif ( '' == $ids ) {
			$args = array(
				'post_type'           => $posttype,
				'posts_per_page'      => $count,
				'post_status'         => array( 'publish' ),
				'order'               => $order,
				'orderby'             => $orderby,
				'meta_key'            => $meta_key,
				'ignore_sticky_posts' => 1,
			);
			if ( 'sale' == $orderby ) {
				$ids = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
				if ( is_array( $ids ) && ! empty( $ids ) ) {
					$args['post__in'] = $ids;
				}
			}
		}

		$loc = WC()->session->get( 'wr_userloc' );
		if ( isset( $sloc ) && '' != $sloc && 'OFF' != $sloc ) {
			$loc = $sloc;
		}
		if ( '' != $tag ) {
			if ( '' == $taxonomy ) {
				$taxonomy = 'product_tag';
			}
			$tags = explode( ',', $tag );
			if ( is_numeric( $tags[0] ) ) {
				$field_tag = 'term_id';
			} else {
				$field_tag = 'slug';
			}
			if ( count( $tags ) > 1 ) {
				$texo = array(
					'relation' => 'OR',
				);
				foreach ( $tags as $iterm ) {
					$texo[] =
						array(
							'taxonomy' => $taxonomy,
							'field'    => $field_tag,
							'terms'    => $iterm,
						);
				}
				if ( '' != $loc ) {
					$texo = array( $texo );
				}
			} else {
				$texo = array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => $field_tag,
						'terms'    => $tags,
					),
				);
			}
		}
		// cats.
		if ( '' != $cat ) {
			if ( '' == $taxonomy || ( '' != $taxonomy && '' != $tag ) ) {
				$taxonomy = 'product_cat';
			}

			$cats = explode( ',', $cat );
			if ( is_numeric( $cats[0] ) ) {
				$field = 'term_id';
			} else {
				$field = 'slug';
			}
			if ( count( $cats ) > 1 ) {
				$texo = array(
					'relation' => 'OR',
				);
				foreach ( $cats as $iterm ) {
					$texo[] =
						array(
							'taxonomy' => $taxonomy,
							'field'    => $field,
							'terms'    => $iterm,
						);
				}
				if ( '' != $loc ) {
					$texo = array( $texo );
				}
			} else {
				$texo = array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => $field,
						'terms'    => $cats,
					),
				);
			}
		}
		// user select loc.
		if ( 'yes' == woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) {
			$loc   = explode( ',', $loc );
			$field = 'slug';
			if ( ! isset( $texo ) || ! is_array( $texo ) ) {
				$texo = array();
			}
			$texo['relation'] = 'AND';
			if ( count( $loc ) > 1 ) {
				foreach ( $loc as $iterm ) {
					$texo[] =
						array(
							'taxonomy' => 'woorestaurant_loc',
							'field'    => $field,
							'terms'    => $iterm,
						);
				}
			} else {
				$texo[] =
					array(
						'taxonomy' => 'woorestaurant_loc',
						'field'    => $field,
						'terms'    => $loc,
					);
			}
		}

		if ( isset( $feature ) && 1 == $feature ) {
			$args['tax_query']['relation'] = 'AND';
			$args['tax_query'][]           = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
			);
			if ( isset( $texo ) ) {
				$args['tax_query'][] = $texo;
			}
		} elseif ( isset( $texo ) ) {
			$args['tax_query'] = $texo;
		}
		if ( isset( $meta_value ) && '' != $meta_value && '' != $meta_key ) {
			if ( ! empty( $args['meta_query'] ) ) {
				$args['meta_query']['relation'] = 'AND';
			}
			$args['meta_query'][] = array(
				'key'     => $meta_key,
				'value'   => $meta_value,
				'compare' => '=',
			);
		}
		if ( isset( $page ) && '' != $page ) {
			$args['paged'] = $page;
		}
		return apply_filters( 'woo_restaurant_query', $args, $cat );
	}
}

if ( ! function_exists( 'woore_remove_items_outofstock' ) ) {
	/**
	 * Remove items out of stock.
	 *
	 * @param array $args it is having arguments.
	 */
	function woore_remove_items_outofstock( $args ) {
		if ( 'yes' == get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$args['meta_query']['relation'] = 'AND';
			$args['post_status']            = array( 'publish' );
			$args['meta_query']['relation'] = 'AND';
			$args['meta_query'][]           = array(
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => 'NOT IN',
			);
		}
		return $args;
	}
}
add_filter( 'woo_restaurant_query', 'woore_remove_items_outofstock' );
add_filter( 'woore_ajax_query_args', 'woore_remove_items_outofstock' );
add_filter( 'woore_ajax_filter_query_args', 'woore_remove_items_outofstock' );

if ( ! function_exists( 'woo_restuaran_customlink' ) ) {
	/**
	 * Restuarant Custom Link.
	 *
	 * @param int    $id it is the id.
	 * @param string $dislbox it is the dislbox.
	 */
	function woo_restuaran_customlink( $id, $dislbox = false ) {
		if ( isset( $dislbox ) && 'yes' == $dislbox ) {
			return 'javascript:;';
		}
		return get_the_permalink( $id );
	}
}

if ( ! function_exists( 'woo_restaurant_page_number_html' ) ) {
	if ( ! function_exists( 'woo_restaurant_page_number_html' ) ) {
		/**
		 * It is the HTML of page number.
		 *
		 * @param array $the_query it is the query.
		 * @param int   $id it is the id.
		 * @param array $atts it is the attribute.
		 * @param int   $num_pg it is the page number.
		 * @param array $args it is the argument.
		 * @param array $arr_ids it is having array of ids.
		 */
		function woo_restaurant_page_number_html( $the_query, $id, $atts, $num_pg, $args, $arr_ids ) {
			if ( isset( $atts['cat'] ) ) {
				$atts['cat'] = str_replace( ' ', '', $atts['cat'] );
			}
			if ( isset( $atts['ids'] ) ) {
				$atts['ids'] = str_replace( ' ', '', $atts['ids'] );
			}
			if ( function_exists( 'paginate_links' ) ) {
				echo '<div class="wrsfd-pagination">';
				echo '
					<input type="hidden"  name="id_grid" value="' . esc_attr( $id ) . '">
					<input type="hidden"  name="num_page" value="' . esc_attr( $num_pg ) . '">
					<input type="hidden"  name="num_page_uu" value="1">
					<input type="hidden"  name="current_page" value="1">
					<input type="hidden"  name="ajax_url" value="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '">
					<input type="hidden"  name="param_query" value="' . esc_attr( json_encode( $args ) ) . '">
					<input type="hidden"  name="param_ids" value="' . esc_attr( json_encode( $arr_ids ) ) . '">
					<input type="hidden" id="param_shortcode" name="param_shortcode" value="' . esc_attr( json_encode( $atts ) ) . '">
				';
				if ( $num_pg > 1 ) {
					$page_link = paginate_links(
						array(
							'base'      => esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) ),
							'format'    => '?paged=%#%',
							'add_args'  => false,
							'show_all'  => true,
							'current'   => max( 1, get_query_var( 'paged' ) ),
							'total'     => $num_pg,
							'prev_next' => false,
							'type'      => 'array',
							'end_size'  => 3,
							'mid_size'  => 3,
						)
					);
					$class     = '';
					if ( get_query_var( 'paged' ) < 2 ) {
						$class = 'disable-click';
					}
					$prev_link = '<a class="prev-ajax ' . esc_attr( $class ) . '" href="javascript:;">&larr;</a>';
					$next_link = '<a class="next-ajax" href="javascript:;">&rarr;</a>';
					array_unshift( $page_link, $prev_link );
					$page_link[] = $next_link;
					echo '<div class="page-navi">' . wp_kses_post( implode( $page_link ) ) . '</div>';
				}
				echo '</div>';
			}
		}
	}
}

if ( ! function_exists( 'woo_restaurant_ajax_navigate_html' ) ) {
	/**
	 * Restaurant Navigate HTMl.
	 *
	 * @param int   $id it is the id.
	 * @param array $atts it is the attribute.
	 * @param int   $num_pg it is the page number.
	 * @param array $args it is the argument.
	 * @param array $arr_ids it is having array of ids.
	 */
	function woo_restaurant_ajax_navigate_html( $id, $atts, $num_pg, $args, $arr_ids ) {
		echo '
			<div class="wrs-loadmore">
				<input type="hidden" name="id_grid" value="' . esc_attr( $id ) . '">
				<input type="hidden" name="num_page" value="' . esc_attr( $num_pg ) . '">
				<input type="hidden" name="num_page_uu" value="1">
				<input type="hidden" name="current_page" value="1">
				<input type="hidden" name="ajax_url" value="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '">
				<input type="hidden" name="param_query" value="' . esc_attr( str_replace( '\/', '/', htmlentities( json_encode( $args ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) ) ) . '">
				<input type="hidden" name="param_ids" value="' . esc_attr( str_replace( '\/', '/', json_encode( $arr_ids ) ) ) . '">
				<input type="hidden" id="param_shortcode" name="param_shortcode" value="' . esc_attr( str_replace( '\/', '/', json_encode( $atts ) ) ) . '">';
		if ( $num_pg > 1 ) {
			echo '
					<a href="javascript:void(0)" class="loadmore-wooresf" data-id="' . esc_attr( $id ) . '">
						<span class="load-text">' . esc_html__( 'Load more', 'woorestaurant' ) . '</span><span></span>&nbsp;<span></span>&nbsp;<span></span>
					</a>';
		}
		echo '
		</div>';
	}
	
}

add_action( 'wp_ajax_woo_restaurant_loadmore', 'ajax_woo_restaurant_loadmore' );
add_action( 'wp_ajax_nopriv_woo_restaurant_loadmore', 'ajax_woo_restaurant_loadmore' );
/**
 * Restaurant Loadmore.
 */
function ajax_woo_restaurant_loadmore() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'restaurant-loadmore' ) ) {
			return;
		}
	}

	global $columns, $number_excerpt, $show_time, $orderby, $img_size, $id;
	global $id, $number_excerpt, $img_size, $hide_atc;
	if ( isset( $_POST['param_shortcode'] ) || isset( $_POST['page'] ) || isset( $_POST['layout'] ) || isset( $_POST['param_query'] ) ) {
		$atts        = json_decode( stripslashes( wp_kses_post( wp_unslash( $_POST['param_shortcode'] ) ) ), true );
		$page        = isset( $_POST['page'] ) ? wp_kses_post( wp_unslash( $_POST['page'] ) ) : '';
		$layout      = isset( $_POST['layout'] ) ? wp_kses_post( wp_unslash( $_POST['layout'] ) ) : '';
		$param_query = json_decode( stripslashes( wp_kses_post( wp_unslash( $_POST['param_query'] ) ) ), true );
	}
	// phpcs:ignore
	$id             = isset( $atts['ID'] ) && '' != $atts['ID'] ? $atts['ID'] : 'wrs-' . rand( 10, 9999 );
	$style          = isset( $atts['style'] ) && '' != $atts['style'] ? $atts['style'] : '1';
	$column         = isset( $atts['column'] ) && '' != $atts['column'] ? $atts['column'] : '2';
	$posttype       = isset( $atts['posttype'] ) && '' != $atts['posttype'] ? $atts['posttype'] : 'product';
	$ids            = isset( $atts['ids'] ) ? $atts['ids'] : '';
	$taxonomy       = isset( $atts['taxonomy'] ) ? $atts['taxonomy'] : '';
	$cat            = isset( $atts['cat'] ) ? $atts['cat'] : '';
	$tag            = isset( $atts['tag'] ) ? $atts['tag'] : '';
	$count          = isset( $atts['count'] ) && '' != $atts['count'] ? $atts['count'] : '9';
	$posts_per_page = isset( $atts['posts_per_page'] ) && '' != $atts['posts_per_page'] ? $atts['posts_per_page'] : '3';
	$order          = isset( $atts['order'] ) ? $atts['order'] : '';
	// phpcs:ignore
	$orderby        = isset( $atts['orderby'] ) ? $atts['orderby'] : '';
	$meta_key       = isset( $atts['meta_key'] ) ? $atts['meta_key'] : '';
	$meta_value     = isset( $atts['meta_value'] ) ? $atts['meta_value'] : '';
	$class          = isset( $atts['class'] ) ? $atts['class'] : '';
	$img_size       = isset( $atts['img_size'] ) ? $atts['img_size'] : '';
	$number_excerpt = isset( $atts['number_excerpt'] ) && '' != $atts['number_excerpt'] ? $atts['number_excerpt'] : '10';
	$hide_atc       = isset( $atts['hide_atc'] ) ? $atts['hide_atc'] : '';
	$page_navi      = isset( $atts['page_navi'] ) ? $atts['page_navi'] : '';
	$param_ids      = '';
	if ( isset( $_POST['param_ids'] ) && '' != $_POST['param_ids'] ) {
		$param_ids = '' != json_decode( stripslashes( wp_kses_post( wp_unslash( $_POST['param_ids'] ) ) ), true ) ? json_decode( stripslashes( wp_kses_post( wp_unslash( $_POST['param_ids'] ) ) ), true ) : explode( ',', wp_kses_post( wp_unslash( $_POST['param_ids'] ) ) );
	}
	$end_it_nb = '';
	if ( '' != $page ) {
		$param_query['paged'] = $page;
		$count_check          = $page * $posts_per_page;
		if ( ( $count_check > $count ) && ( ( $count_check - $count ) < $posts_per_page ) ) {
			$end_it_nb = $count - ( ( $page - 1 ) * $posts_per_page );
		} elseif ( ( $count_check > $count ) ) {
			die;
		}
	}
	if ( ( 'rand' == $orderby || '_price' == $meta_key ) && is_array( $param_ids ) ) {
		$param_query['post__not_in'] = $param_ids;
		$param_query['paged']        = 1;
	}
	if ( 'sale' == $orderby ) {
		$ids = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
		if ( is_array( $ids ) && ! empty( $ids ) ) {
			$param_query['post__in'] = $ids;
		}
	}
	$param_query = apply_filters( 'woore_ajax_query_args', $param_query, $atts, $param_ids );
	$the_query   = new WP_Query( $param_query );
	$it          = $the_query->post_count;
	ob_start();
	if ( $the_query->have_posts() ) {
		$i          = 0;
		$arr_ids    = array();
		$html_modal = '';
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			global $product;
			$disable_is_visible = apply_filters( 'woore_disable_is_visible', 'no', $product );
			if ( 'yes' == $disable_is_visible || $product && $product->is_visible() ) {
				++$i;
				$arr_ids[] = get_the_ID();
				if ( 'table' == $layout ) {
					woo_restaurant_template_plugin( 'table-' . $style, 1 );
				} elseif ( 'list' == $layout ) {
					echo '<div class="fditem-list item-grid" data-id="wr_id-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '" data-id_food="' . esc_attr( get_the_ID() ) . '" id="ctc-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '"> ';
					woore_custom_color( 'list', $style, 'ctc-' . esc_attr( $id ) . '-' . get_the_ID() );
					?>
					<div class="exp-arrow">
						<?php
						woo_restaurant_template_plugin( 'list-' . $style, 1 );
						?>
						<div class="wooresfd_clearfix"></div>
					</div>
					<?php
					echo '</div>';
				} else {
					echo '<div class="item-grid" data-id="wr_id-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '" data-id_food="' . esc_attr( get_the_ID() ) . '" id="ctc-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '"> ';
					woore_custom_color( 'grid', $style, 'ctc-' . esc_attr( $id ) . '-' . get_the_ID() );
					?>
					<div class="exp-arrow">
						<?php
						woo_restaurant_template_plugin( 'grid-' . $style, 1 );
						?>
						<div class="wooresfd_clearfix"></div>
					</div>
					<?php
					echo '</div>';
				}
				if ( '' != $end_it_nb && $end_it_nb == $i ) {
					break;
				}
			}
		}
		wp_reset_postdata();

		if ( is_array( $param_ids ) && ( 'loadmore' == $page_navi || 'rand' == $orderby ) ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					<?php if ( isset( $_POST['id_crsc'] ) ) { ?>
						// phpcs:ignore
						jQuery('#<?php echo wp_kses_post( wp_unslash( $_POST['id_crsc'] ) ); ?> input[name=param_ids]').val(<?php echo esc_attr( str_replace( '\/', '/', json_encode( array_merge( $param_ids, $arr_ids ) ) ) ); ?>);
						setTimeout(function() {
							// phpcs:ignore
							jQuery('#<?php echo wp_kses_post( wp_unslash( $_POST['id_crsc'] ) ); ?> .ctgrid > script').each(function() {
								jQuery(this).remove();
							});
						}, 150);
						// phpcs:ignore
						var $cr_page = jQuery('#<?php echo wp_kses_post( wp_unslash( $_POST['id_crsc'] ) ); ?> input[name=current_page]').val();
						if ($cr_page > '<?php echo esc_attr( $page ); ?>') {
							// phpcs:ignore
							jQuery('#<?php echo wp_kses_post( wp_unslash( $_POST['id_crsc'] ) ); ?> input[name=param_ids]').val('');
						}
						// phpcs:ignore
						jQuery('#<?php echo wp_kses_post( wp_unslash( $_POST['id_crsc'] ) ); ?> input[name=current_page]').val('<?php echo esc_attr( $page ); ?>');
					<?php } ?>
				});
			</script>
			<?php
		}
		?>
		</div>
		<?php
		do_action( 'woore_after_content_loadmore_html' );
	}
	$html   = ob_get_clean();
	$output = array(
		'html_content' => $html,
		'html_modal'   => $html_modal,
	);
	// phpcs:ignore
	echo ( str_replace( '\/', '/', json_encode( $output ) ) );
	die;
}

add_action( 'woocommerce_init', 'woore_wc_session_user' );
add_action( 'init', 'woore_wc_session_user' );
/**
 * Register sesion.
 */
function woore_wc_session_user() {
	if ( is_user_logged_in() || is_admin() ) {
		return;
	}

	if ( isset( WC()->session ) && ! WC()->session->has_session() ) {
		WC()->session->set_customer_session_cookie( true );
	}
}

add_action( 'wp_ajax_wooresf_menuegory', 'ajax_wooresf_menuegory' );
add_action( 'wp_ajax_nopriv_wooresf_menuegory', 'ajax_wooresf_menuegory' );
/**
 * Ajax Menuegory.
 */
function ajax_wooresf_menuegory() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'ajax-menuegory' ) ) {
			return;
		}
	}

	global $id, $number_excerpt, $img_size, $hide_atc;
	if ( isset( $_POST['param_shortcode'] ) || isset( $_POST['page'] ) || isset( $_POST['layout'] ) || isset( $_POST['param_query'] ) ) {
		$atts        = json_decode( stripslashes( wp_kses_post( wp_unslash( $_POST['param_shortcode'] ) ) ), true );
		$page        = isset( $_POST['page'] ) ? wp_kses_post( wp_unslash( $_POST['page'] ) ) : '';
		$layout      = isset( $_POST['layout'] ) ? wp_kses_post( wp_unslash( $_POST['layout'] ) ) : '';
		$param_query = json_decode( stripslashes( wp_kses_post( wp_unslash( $_POST['param_query'] ) ) ), true );
	}
	// phpcs:ignore
	$id             = isset( $atts['ID'] ) && '' != $atts['ID'] ? $atts['ID'] : 'wrs-' . rand( 10, 9999 );
	$ids            = isset( $atts['ids'] ) ? $atts['ids'] : '';
	$count          = isset( $atts['count'] ) && '' != $atts['count'] ? $atts['count'] : '9';
	$style          = isset( $atts['style'] ) && '' != $atts['style'] ? $atts['style'] : '1';
	$posts_per_page = isset( $atts['posts_per_page'] ) && '' != $atts['posts_per_page'] ? $atts['posts_per_page'] : '3';
	$number_excerpt = isset( $atts['number_excerpt'] ) && '' != $atts['number_excerpt'] ? $atts['number_excerpt'] : '10';
	$cat            = isset( $atts['cat'] ) ? $atts['cat'] : '';
	$orderby        = isset( $atts['orderby'] ) ? $atts['orderby'] : '';
	$page_navi      = isset( $atts['page_navi'] ) ? $atts['page_navi'] : '';
	$img_size       = isset( $atts['img_size'] ) ? $atts['img_size'] : '';
	$featured       = isset( $atts['featured'] ) ? $atts['featured'] : '';
	$hide_atc       = isset( $atts['hide_atc'] ) ? $atts['hide_atc'] : '';
	$param_ids      = '';
	if ( isset( $_POST['param_ids'] ) && '' != $_POST['param_ids'] ) {
		$param_ids = '' != json_decode( stripslashes( wp_kses_post( wp_unslash( $_POST['param_ids'] ) ) ), true ) ? json_decode( stripslashes( wp_kses_post( wp_unslash( $_POST['param_ids'] ) ) ), true ) : explode( ',', wp_kses_post( wp_unslash( $_POST['param_ids'] ) ) );
	}
	$end_it_nb = '';
	if ( '' != $page ) {
		$param_query['paged'] = $page;
		$count_check          = $page * $posts_per_page;
		if ( ( $count_check > $count ) && ( ( $count_check - $count ) < $posts_per_page ) ) {
			$end_it_nb = $count - ( ( $page - 1 ) * $posts_per_page );
		} elseif ( ( $count_check > $count ) ) {
			die;
		}
	}
	$param_query['post__in'] = '';
	$loc                     = '';
	if ( 'yes' == woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) || true ) {
		$loc = WC()->session->get( 'wr_userloc' );
	}
	$atts_cr = $atts;
	if ( isset( $_POST['cat'] ) && '' != $_POST['cat'] ) {
		$atts_cr['cat'] = wp_kses_post( wp_unslash( $_POST['cat'] ) );
		$texo           = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => wp_kses_post( wp_unslash( $_POST['cat'] ) ),
			),
		);
	} else {
		$param_query['tax_query'] = '';
		if ( '' != $cat ) {
			$taxonomy = 'product_cat';
			$cats     = explode( ',', $cat );
			if ( is_numeric( $cats[0] ) ) {
				$field = 'term_id';
			} else {
				$field = 'slug';
			}
			if ( count( $cats ) > 1 ) {
				$texo = array( 'relation' => 'OR' );
				foreach ( $cats as $iterm ) {
					$texo[] = array(
						'taxonomy' => $taxonomy,
						'field'    => $field,
						'terms'    => $iterm,
					);
				}
				if ( '' != $loc ) {
					$texo = array( $texo );
				}
			} else {
				$texo = array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => $field,
						'terms'    => $cats,
					),
				);
			}
		}
	}
	if ( 'yes' == woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) {
		$loc   = explode( ',', $loc );
		$field = 'slug';
		if ( ! isset( $texo ) || ! is_array( $texo ) ) {
			$texo = array();
		}
		$texo['relation'] = 'AND';
		if ( count( $loc ) > 1 ) {
			foreach ( $loc as $iterm ) {
				$texo[] =
					array(
						'taxonomy' => 'woorestaurant_loc',
						'field'    => $field,
						'terms'    => $iterm,
					);
			}
		} else {
			$texo[] =
				array(
					'taxonomy' => 'woorestaurant_loc',
					'field'    => $field,
					'terms'    => $loc,
				);
		}
	}
	if ( isset( $texo ) ) {
		$param_query['tax_query'] = $texo;
	}
	if ( '' != $ids ) {
		$ids                     = explode( ',', $ids );
		$param_query['post__in'] = $ids;
	}
	if ( 'sale' == $orderby ) {
		$ids = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
		if ( is_array( $ids ) && ! empty( $ids ) ) {
			$param_query['post__in'] = $ids;
		}
	}
	if ( isset( $featured ) && 1 == $featured ) {
		$param_query['tax_query'][] = array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => 'featured',
		);
	}
	if ( isset( $_POST['key_word'] ) && '' != $_POST['key_word'] ) {
		$param_query['s'] = wp_kses_post( wp_unslash( $_POST['key_word'] ) );
	} else {
		$param_query['s'] = '';
	}
	$param_query = apply_filters( 'woore_ajax_filter_query_args', $param_query, $atts_cr );

	$the_query = new WP_Query( $param_query );
	$it        = $the_query->post_count;
	ob_start();
	if ( $the_query->have_posts() ) {
		$it = $the_query->found_posts;
		if ( $it < $count || '-1' == $count ) {
			$count = $it;
		}
		if ( $count > $posts_per_page ) {
			$num_pg = ceil( $count / $posts_per_page );
			$it_ep  = $count % $posts_per_page;
		} else {
			$num_pg = 1;
		}
		$arr_ids    = array();
		$html_modal = '';
		$i          = 0;
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			global $product;
			$disable_is_visible = apply_filters( 'woore_disable_is_visible', 'no', $product );
			if ( 'yes' == $disable_is_visible || $product && $product->is_visible() ) {
				++$i;
				$arr_ids[] = get_the_ID();
				if ( 'list' == $layout ) {
					echo '<div class="fditem-list item-grid" data-id="wr_id-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '" data-id_food="' . esc_attr( get_the_ID() ) . '" id="ctc-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '"> ';
					woore_custom_color( 'list', $style, 'ctc-' . esc_attr( $id ) . '-' . get_the_ID() );
					?>
					<div class="exp-arrow">
						<?php
						woo_restaurant_template_plugin( 'list-' . $style, 1 );
						?>
						<div class="wooresfd_clearfix"></div>
					</div>
					<?php
					echo '</div>';
				} elseif ( 'table' == $layout ) {
					woo_restaurant_template_plugin( 'table-' . $style, 1 );
				} else {
					echo '<div class="item-grid" data-id="wr_id-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '" data-id_food="' . esc_attr( get_the_ID() ) . '" id="ctc-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '"> ';
					woore_custom_color( 'grid', $style, 'ctc-' . esc_attr( $id ) . '-' . get_the_ID() );
					?>
					<div class="exp-arrow">
						<?php
						woo_restaurant_template_plugin( 'grid-' . $style, 1 );
						?>
						<div class="wooresfd_clearfix"></div>
					</div>
					<?php
					echo '</div>';
				}
				if ( '' != $end_it_nb && $end_it_nb == $i ) {
					break;
				}
			}
		}
		wp_reset_postdata();
		?>
		</div>
		<?php
	}

	$html = ob_get_contents();
	ob_end_clean();
	$html_dcat = '';
	if ( '' == $html ) {
		$html = '<span class="wrsf-no-rs">' . esc_html__( 'No matching records found', 'woorestaurant' ) . '</span>';
	} elseif ( isset( $_POST['cat'] ) && '' != $_POST['cat'] ) {
		$term = get_term_by( 'slug', wp_kses_post( wp_unslash( $_POST['cat'] ) ), 'product_cat' );
		if ( '' != $term->description ) {
			$html_dcat = '<div class="wrsf-dcat" style="display:block;">' . $term->description . '</div>';
		}
	}
	ob_start();
	if ( 'loadmore' == $page_navi ) {
		woo_restaurant_ajax_navigate_html( $id, $atts, $num_pg, $param_query, $arr_ids );
	} else {
		woo_restaurant_page_number_html( $the_query, $id, $atts, $num_pg, $param_query, $arr_ids );
	}
	$page_navihtml = ob_get_contents();
	ob_end_clean();
	$output = array(
		'html_content' => $html,
		'page_navi'    => $page_navihtml,
		'html_modal'   => $html_modal,
		'html_dcat'    => $html_dcat,
	);
	// phpcs:ignore
	echo ( str_replace( '\/', '/', json_encode( $output ) ) );
	die;
}
if ( ! function_exists( 'woo_restaurant_search_form_html' ) ) {
	/**
	 * Restaurant Search form HTML.
	 *
	 * @param string $cats it is having category.
	 * @param string $order_cat it is having order category.
	 * @param string $pos it is having pos.
	 * @param string $active_filter it is having active filters.
	 * @param string $filter_style it is having filter styles.
	 * @param string $hide_ftall it is check to hide ftall.
	 * @param string $show_count it is show count.
	 */
	function woo_restaurant_search_form_html( $cats, $order_cat, $pos = false, $active_filter = false, $filter_style = false, $hide_ftall = false, $show_count = false ) {
		$args = array(
			'hide_empty' => true,
			'parent'     => '0',
		);
		if ( '' != $cats ) {
			unset( $args['parent'] );
		}
		$cats = '' != $cats ? explode( ',', $cats ) : array();
		if ( empty( $cats ) ) {
			return;
		}
		$menucats = function_exists( 'woore_menuby_cats_included' ) ? woore_menuby_cats_included() : '';
		if ( is_array( $menucats ) && ! empty( $menucats ) ) {
			$cats = $menucats;
			if ( ! empty( $cats ) && is_numeric( $cats[0] ) ) {
				$cats = array_diff( $cats, $menucats );
			} elseif ( empty( $cats ) ) {
				$cats = $menucats;
			}
		}
		if ( ! empty( $cats ) && ! is_numeric( $cats[0] ) ) {
			$args['slug']    = $cats;
			$args['orderby'] = 'slug__in';
		} elseif ( ! empty( $cats ) ) {
			$args['include'] = $cats;
			$args['orderby'] = 'include';
		}
		if ( 'yes' == $order_cat ) {
			$args['meta_key'] = 'woo_restaurant_menu_order';
			$args['orderby']  = 'meta_value_num';
		}
		$loc_selected = woore_get_loc_selected();
		$exclude      = array();
		if ( '' != $loc_selected ) {
			$exclude = get_term_meta( $loc_selected, 'wrsp_loc_hide_menu', true );
		}
		$count_stop = 5;
		// phpcs:ignore
		$terms         = get_terms( 'product_cat', $args );
		$css_class     = isset( $filter_style ) && 'icon' == $filter_style ? 'wrsf-fticon-style' : '';
		$mobile_filter = woo_restaurant_get_option( 'woo_restaurant_mb_filter' );
		if ( 'slider' == $mobile_filter ) {
			$css_class .= ' wrsf-filter-slider';
		}
		$active_filter = isset( $active_filter ) ? $active_filter : '';
		?>
		<div class="wrsfd-filter <?php echo esc_attr( $css_class ); ?>">
			<div class="wrsfd-filter-group">
				<?php
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$select_option = '';
					$list_item     = '';
					?>
					<div class="wrs-menu-list">
						<?php
						if ( isset( $pos ) && 'left' == $pos ) {
							$act_cls = 'wrs-active-left';
						} else {
							$act_cls = 'wrs-menu-item-active';
						}
						$all_atcl = $act_cls;
						if ( isset( $active_filter ) && '' != $active_filter ) {
							$all_atcl = '';
						}
						if ( isset( $hide_ftall ) && 'yes' != $hide_ftall ) {
							?>
							<a class="wrs-menu-item <?php echo esc_attr( $all_atcl ); ?>" href="javascript:;" data-value=""><?php echo esc_html__( 'All', 'woorestaurant' ); ?></a>
							<?php
						}
						foreach ( $terms as $term ) {
							if ( is_array( $exclude ) && ! empty( $exclude ) && in_array( $term->slug, $exclude ) ) {
								// if exclude.
								$exclude;
							} else {
								$all_atcl = '';
								global $woore_child_atv;
								$woore_child_atv = false;
								global $wp;
								$curent_url = home_url( $wp->request );
								$curent_url = apply_filters( 'woore_current_link', $curent_url );
								ob_start();
								wooresfd_show_child_inline( $cats, $term, $count_stop, $order_cat, 'inline', $exclude, $active_filter, $show_count );
								if ( isset( $active_filter ) && $active_filter == $term->slug || true == $woore_child_atv ) {
									$all_atcl = $act_cls;
								}
								$child_c = ob_get_contents();
								ob_end_clean();
								echo '<a class="wrs-menu-item ' . esc_attr( $all_atcl ) . ' ' . ( '' != $child_c ? ' wrsf-has-child' : '' ) . '" href="' . esc_url( add_query_arg( array( 'menu' => $term->slug ), $curent_url ) ) . '" data-value="' . esc_attr( $term->slug ) . '">';
								if ( isset( $filter_style ) && 'icon' == $filter_style ) {
									$_iconsc = get_term_meta( $term->term_id, 'woo_restaurant_menu_iconsc', true );
									if ( '' != $_iconsc ) {
										echo '<span class="wrsf-caticon wrsf-iconsc">' . esc_attr( $_iconsc ) . '</span>';
									} else {
										$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
										if ( '' != $thumbnail_id ) {
											// get the medium-sized image url.
											$image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
											// Output in img tag.
											if ( isset( $image[0] ) && '' != $image[0] ) {
												echo '<span class="wrsf-caticon"><img src="' . esc_attr( $image[0] ) . '" alt="" /></span>';
											}
										}
									}
								}
								echo wp_kses_post( $term->name );
								if ( isset( $show_count ) && 'yes' == $show_count ) {
									echo '<span class="wrsf-cat-count"> (' . esc_attr( $term->count ) . ')</span>';
								}
								// phpcs:ignore
								echo ( $child_c );
								echo '</a>';
							}
						}
						if ( 'slider' != $mobile_filter ) {
							?>
							<div class="wooresfd_clearfix"></div>
						<?php } ?>
					</div>
					<div class="wrs-menu-select">
						<div>
							<select name="wooresf_menu">
								<?php if ( isset( $hide_ftall ) && 'yes' != $hide_ftall ) { ?>
									<option value=""><?php echo esc_html__( 'All', 'woorestaurant' ); ?></option>
									<?php
								}
								foreach ( $terms as $term ) {
									if ( is_array( $exclude ) && ! empty( $exclude ) && in_array( $term->slug, $exclude ) ) {
										// if exclude.
										$exclude;
									} else {
										$selected = '';
										if ( isset( $active_filter ) && $active_filter == $term->slug ) {
											$selected = 'selected';
										}
										$t_count = '';
										if ( isset( $show_count ) && 'yes' == $show_count ) {
											$t_count = ' (' . $term->count . ')';
										}
										echo '<option value="' . esc_attr( $term->slug ) . '" ' . esc_attr( $selected ) . '>' . wp_kses_post( $term->name ) . esc_attr( $t_count ) . '</option>';
										echo esc_attr( wooresfd_show_child_inline( $cats, $term, $count_stop, $order_cat, '', $exclude, $active_filter, $show_count ) );
									}
								}
								?>
							</select>
						</div>
					</div>
					<?php
				} //if have terms.
				?>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'wooresfd_show_child_inline' ) ) {
	/**
	 * Show child inline.
	 *
	 * @param string $cats it is having category.
	 * @param object $term it is the term.
	 * @param string $count_stop it is the stop count.
	 * @param string $order_cat it is having order category.
	 * @param string $inline it is having inline.
	 * @param string $exclude it is having exclude.
	 * @param string $active_filter it is having active filters.
	 * @param string $show_count it is show count.
	 */
	function wooresfd_show_child_inline( $cats, $term, $count_stop, $order_cat, $inline, $exclude = false, $active_filter = false, $show_count = false ) {
		global $woore_child_atv;
		if ( $count_stop < 2 ) {
			return;
		}
		$charactor = '';
		if ( 5 == $count_stop ) {
			$charactor = '— ';
		} elseif ( 4 == $count_stop ) {
			$charactor = '—— ';
		} elseif ( 3 == $count_stop ) {
			$charactor = '——— ';
		} elseif ( 2 == $count_stop ) {
			$charactor = '——— ';
		}
		$args_child = array(
			'child_of'   => $term->term_id,
			'parent'     => $term->term_id,
			'hide_empty' => false,
		);
		if ( 'yes' == $order_cat ) {
			$args_child['meta_key'] = 'woo_restaurant_menu_order';
			$args_child['orderby']  = 'meta_value_num';
		}
		$active_filter = isset( $active_filter ) ? $active_filter : '';
		$show_count    = isset( $show_count ) ? $show_count : '';
		$show_count    = apply_filters( 'woore_show_count_child_cat', $show_count );
		// phpcs:ignore
		$second_level_terms = get_terms( 'product_cat', $args_child );
		if ( $second_level_terms ) {
			$count_stop = $count_stop--;
			if ( 'inline' != $inline ) {
				foreach ( $second_level_terms as $second_level_term ) {
					if ( is_array( $exclude ) && ! empty( $exclude ) && in_array( $second_level_term->slug, $exclude ) ) {
						// if exclude.
						$exclude;
					} else {
						$selected = '';
						if ( isset( $active_filter ) && $active_filter == $second_level_term->slug ) {
							$selected = 'selected';
						}
						$t_count = '';
						if ( isset( $show_count ) && 'yes' == $show_count ) {
							$t_count = ' (' . $second_level_term->count . ')';
						}
						echo '<option value="' . esc_attr( $second_level_term->slug ) . '" ' . esc_attr( $selected ) . '>' . wp_kses_post( $charactor . $second_level_term->name ) . esc_attr( $t_count ) . '</option>';
						wooresfd_show_child_inline( $cats, $second_level_term, $count_stop, $order_cat, '', '', $active_filter, $show_count );
					}
				}
			} else {
				echo '<span class="wrsfd-caret"></span>';
				echo '<ul class="wrsfd-ul-child">';
				global $wp;
				$curent_url = home_url( $wp->request );
				$curent_url = apply_filters( 'woore_current_link', $curent_url );
				foreach ( $second_level_terms as $second_level_term ) {
					if ( is_array( $exclude ) && ! empty( $exclude ) && in_array( $second_level_term->slug, $exclude ) ) {
						// if exclude.
						$exclude;
					} else {
						$second_term_name = $second_level_term->name;
						$atcl             = '';
						if ( isset( $active_filter ) && $active_filter == $second_level_term->slug ) {
							$atcl            = 'wrs-menu-item-active';
							$woore_child_atv = true;
						}
						$t_count = '';
						if ( isset( $show_count ) && 'yes' == $show_count ) {
							$t_count = '<span class="wrsf-cat-count"> (' . $second_level_term->count . ')</span>';
						}
						echo '<li class="wrsfd-child-click wrs-menu-item ' . esc_attr( $atcl ) . '" data-value="' . esc_attr( $second_level_term->slug ) . '" data-url="' . esc_url( add_query_arg( array( 'menu' => $second_level_term->slug ), $curent_url ) ) . '">
			            ' . wp_kses_post( $second_term_name ) . esc_attr( $t_count );
						wooresfd_show_child_inline( $cats, $second_level_term, $count_stop, $order_cat, 'inline', '', $active_filter, $show_count );
						echo '</li>';
					}
				}

				echo '</ul>';
			}
		}
	}
}

/**
 * Restaurant Convert color.
 *
 * @param string $color it is the color.
 */
function woo_restaurant_convert_color( $color ) {
	if ( '' == $color ) {
		return;
	}
	$hex = str_replace( '#', '', $color );
	// phpcs:ignore
	if ( strlen( $hex ) == 3 ) {
		$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
		$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
		$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
	} else {
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
	}
	$rgb = $r . ',' . $g . ',' . $b;
	return $rgb;
}

if ( ! function_exists( 'woo_restaurant_sale_badge' ) ) {
	/**
	 * Sale Badge.
	 */
	function woo_restaurant_sale_badge() {
		global $product;
		if ( is_object( $product ) && method_exists( $product, 'is_on_sale' ) && $product->is_on_sale() ) {
			?>
			<div class="wrsfd-ribbon wrsf-sale-bg"><span><?php esc_html_e( 'Sale', 'woorestaurant' ); ?></span></div>
			<?php
		} elseif ( '' != wooresfd_show_reviews( '', $product ) ) {
			echo '<div class="wrsfd-ribbon wrsf-review-bg"><span>' . esc_attr( wooresfd_show_reviews( '', $product ) ) . '</span></div>';
		}
	}
}

if ( ! function_exists( 'woo_restaurant_add_to_cart_form_shortcode' ) ) {
	/**
	 * Add to cart from shortcode.
	 *
	 * @param array $atts it is the attribute.
	 */
	function woo_restaurant_add_to_cart_form_shortcode( $atts ) {
		global $cart_itemkey;
		$cart_itemkey = isset( $atts['woore_cart_itemkey'] ) ? $atts['woore_cart_itemkey'] : '';
		if ( ! woore_check_open_close_time( $atts['id'] ) ) {
			return wooresfd_open_closing_message();
		}
		$hide_pm = isset( $atts['hide_pm'] ) ? $atts['hide_pm'] : '';

		if ( empty( $atts ) || ! function_exists( 'woocommerce_template_single_add_to_cart' ) ) {
			return '';
		}
		if ( ! isset( $atts['id'] ) && ! isset( $atts['sku'] ) ) {
			return '';
		}
		$args = array(
			'posts_per_page'      => 1,
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'no_found_rows'       => 1,
		);
		if ( isset( $atts['sku'] ) ) {
			$args['meta_query'][] = array(
				'key'     => '_sku',
				'value'   => sanitize_text_field( $atts['sku'] ),
				'compare' => '=',
			);
			$args['post_type']    = array( 'product', 'product_variation' );
		}
		if ( isset( $atts['id'] ) ) {
			$args['p'] = absint( $atts['id'] );
		}
		// Change form action to avoid redirect.
		add_filter( 'woocommerce_add_to_cart_form_action', '__return_empty_string' );
		$single_product = new WP_Query( $args );
		$preselected_id = '0';
		global $wp_food;
		$wp_food = 'woo';
		// Check if sku is a variation.
		if ( isset( $atts['sku'] ) && $single_product->have_posts() && 'product_variation' === $single_product->post->post_type ) {
			$variation  = new WC_Product_Variation( $single_product->post->ID );
			$attributes = $variation->get_attributes();
			// Set preselected id to be used by JS to provide context.
			$preselected_id = $single_product->post->ID;
			// Get the parent product object.
			$args           = array(
				'posts_per_page'      => 1,
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => 1,
				'p'                   => $single_product->post->post_parent,
			);
			$single_product = new WP_Query( $args );
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var $variations_form = $('[data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>"]').find('form.variations_form');
					<?php foreach ( $attributes as $attr => $value ) { ?>
						$variations_form.find('select[name="<?php echo esc_attr( $attr ); ?>"]').val('<?php echo esc_js( $value ); ?>');
					<?php } ?>
				});
			</script>
			<?php
		}
		// For "is_single" to always make load comments_template() for reviews.
		$single_product->is_single = false;
		ob_start();
		global $wp_query;
		// Backup query object so following loops think this is a product page.
		$previous_wp_query = $wp_query;
		// phpcs:ignore
		$wp_query          = $single_product;
		wp_enqueue_script( 'wc-single-product' );
		while ( $single_product->have_posts() ) {
			$single_product->the_post();
			?>
			<div class="single-product" data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>">
				<?php
				woocommerce_template_single_add_to_cart();
				do_action( 'woore_after_atc_form' );
				if ( '1' != $hide_pm ) {
					?>
					<script type="text/javascript">
						jQuery(document).ready(function() {
							jQuery('#food_modal .woorst-woocommerce .cart div.quantity:not(.wrsbuttons_added):not(.hidden)').addClass('wrsbuttons_added').append('<input type="button" value="+" id="wrsadd_ticket" class="explus" />').prepend('<input type="button" value="-" id="wrsminus_ticket" class="wrs-minus" />');
							jQuery('#food_modal:not(.wrff-dis-bt) .woorst-woocommerce .wrsbuttons_added').on('click', '#wrsminus_ticket', function() {
								var value = parseInt(jQuery(this).closest(".quantity").find('.qty').val()) - 1;
								var min = jQuery(this).closest(".quantity").find('.qty').attr('min');
								min = min != '' ? parseInt(min) : '';
								if (isNaN(min) || value >= min || min == '') {
									if (value > 0) {
										jQuery(this).closest(".quantity").find('.qty').val(value);
									} else if (value == 0 && jQuery('#food_modal .grouped_form').length) {
										jQuery(this).closest(".quantity").find('.qty').val(value);
									}
								}
							});
							jQuery('#food_modal:not(.wrff-dis-bt) .woorst-woocommerce .wrsbuttons_added').on('click', '#wrsadd_ticket', function() {
								var value = jQuery(this).closest(".quantity").find('.qty').val();
								var max = jQuery(this).closest(".quantity").find('.qty').attr('max');
								value = value != '' ? parseInt(value) : 1;
								max = max != '' ? parseInt(max) : '';
								if (isNaN(max) || value < max || max == '') {
									value = value + 1;
									jQuery(this).closest(".quantity").find('.qty').val(value);
								}
							});
						});
						if (typeof woore_change_img == 'function') {} else {
							function woore_change_img() {
								var defimg = '';
								jQuery(document).on("found_variation.first", function(e, variation) {
									if (variation.image.full_src != '') {
										jQuery('#food_modal .fd_modal_img .wrsf-vari-img').fadeOut("normal", function() {
											jQuery(this).remove();
										});
										jQuery('#food_modal .fd_modal_img').prepend('<div class="wrsf-vari-img"><img src="' + variation.image.full_src + '"/></div>').fadeIn('normal');
									}
								});
								jQuery(".variations_form").on("woocommerce_variation_select_change", function() {
									setTimeout(function() {
										var $_cr_img = jQuery('#food_modal .woorst-woocommerce form.variations_form').attr("current-image");
										if ($_cr_img == '') {
											jQuery('#food_modal .fd_modal_img .wrsf-vari-img').remove();
											jQuery('#food_modal .wrsfd-modal-carousel:not(.wrsp-no-galle)').wr_wr_s_lick('setPosition');
										}
									}, 500);
								});
							}
							woore_change_img();
						}
					</script>
					<?php
				}
				?>
			</div>
			<?php
		}
		// Restore $previous_wp_query and reset post data.
		// phpcs:ignore
		$wp_query = $previous_wp_query;
		wp_reset_postdata();
		return '<div class="woorst-woocommerce woocommerce">' . ob_get_clean() . '</div>';
	}
}
add_shortcode( 'wr_food_wooform', 'woo_restaurant_add_to_cart_form_shortcode' );

add_action( 'wp_ajax_woo_restaurant_booking_info', 'ajax_woo_restaurant_booking_info' );
add_action( 'wp_ajax_nopriv_woo_restaurant_booking_info', 'ajax_woo_restaurant_booking_info' );
/**
 * Booking info.
 */
function ajax_woo_restaurant_booking_info() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'booking-info' ) ) {
			return;
		}
	}

	if ( isset( $_POST['id_food'] ) && '' != $_POST['id_food'] ) {
		$product_exist = wp_kses_post( wp_unslash( $_POST['id_food'] ) );
		global $atts, $id_food, $param_shortcode, $woore_itemkey;
		$atts['woore_cart_itemkey'] = isset( $_POST['cart_itemkey'] ) ? wp_kses_post( wp_unslash( $_POST['cart_itemkey'] ) ) : '';
		$param_shortcode            = isset( $_POST['param_shortcode'] ) ? wp_kses_post( wp_unslash( $_POST['param_shortcode'] ) ) : '';
		$param_shortcode            = json_decode( stripslashes( $param_shortcode ), true );
		$id_food                    = wp_kses_post( wp_unslash( $_POST['id_food'] ) );
		if ( '' != $product_exist && is_numeric( $product_exist ) ) {
			$atts['id'] = $product_exist;
		}
		woo_restaurant_template_plugin( 'modal', true );
	} else {
		echo 'error';
	}
	exit;
}

/**
 * Error Notices.
 */
function woore_get_error_notices() {
	return array( 'error' );
}

add_action( 'wp_ajax_woo_restaurant_add_to_cart', 'woo_restaurant_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_woo_restaurant_add_to_cart', 'woo_restaurant_ajax_add_to_cart' );
/**
 * Ajax Add to cart.
 */
function woo_restaurant_ajax_add_to_cart() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'ajax-add-to-cart' ) ) {
			return;
		}
	}

	do_action( 'woore_before_ajaxadd_to_cart', $_POST );
	add_filter( 'woocommerce_notice_types', 'woore_get_error_notices' );
	$notice = wc_print_notices( true );
	if ( '' != $notice ) {
		$data = array(
			'error'   => true,
			'message' => '<div class="wrsfd-out-notice">' . $notice . '</div>',
		);
		wc_clear_notices();
		echo esc_attr( wp_send_json( $data ) );
	}
	wp_die();
}

add_action( 'wp_ajax_woo_restaurant_refresh_cart', 'woo_restaurant_refresh_cart' );
add_action( 'wp_ajax_nopriv_woo_restaurant_refresh_cart', 'woo_restaurant_refresh_cart' );
/**
 * To refresh cart.
 */
function woo_restaurant_refresh_cart() {
	WC_AJAX::get_refreshed_fragments();
	wp_die();
}

if ( ! function_exists( 'woo_restaurant_booking_button_html' ) ) {
	/**
	 * Booking button.
	 *
	 * @param string $style it is the style.
	 * @param string $hide_atc it is the check.
	 */
	function woo_restaurant_booking_button_html( $style, $hide_atc = false ) {
		if ( ! woore_check_open_close_time( get_the_ID() ) || ( isset( $hide_atc ) && 'yes' == $hide_atc ) ) {
			return;
		}
		$order_text = apply_filters( 'woore_order_button_text', esc_html__( 'Order', 'woorestaurant' ) );
		$html       = '<a href="' . get_the_permalink( get_the_ID() ) . '" class="wrstyle-' . esc_attr( $style ) . '-button">' . $order_text . '</a>';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		$product_exist = get_the_ID();
		$product       = wc_get_product( $product_exist );
		if ( false !== $product ) {
			$type          = $product->get_type();
			$disable_addon = apply_filters( 'woore_disable_default_options', 'no' );
			$disable_atc   = apply_filters( 'woore_disable_add_to_cart', 'no' );
			if ( 'variable' == $type || 'yes' == $disable_atc ) {
				$type;
			} elseif ( function_exists( 'woo_restaurantget_options' ) && 'yes' != $disable_addon ) {
				$data_options = woo_restaurantget_options( $product_exist );
				$ck_buin      = 0;
				if ( is_array( $data_options ) && ! empty( $data_options ) ) {
					$data_options;
				} else {
					if ( 'simple' == $type && is_array( $data_options ) && empty( $data_options ) || 'simple' == $type && '' == $data_options ) {
						$html = do_shortcode( '[wr_food_wooform id="' . $product_exist . '" hide_pm="1"]' );
					} else {
						$ck_buin = 1;
					}
					if ( is_plugin_active( 'woocommerce-tm-extra-product-options/tm-woo-extra-product-options.php' ) ) {
						$has_epo = THEMECOMPLETE_EPO_API()->has_options( $product_exist );

						if ( THEMECOMPLETE_EPO_API()->is_valid_options( $has_epo ) ) {
							$html = '<a href="' . get_the_permalink( $product_exist ) . '" class="wrstyle-' . esc_attr( $style ) . '-button">' . esc_html__( 'Order', 'woorestaurant' ) . '</a>';
						} else {
							$html = do_shortcode( '[wr_food_wooform id="' . $product_exist . '" hide_pm="1"]' );
						}
					}
					if ( '1' != $ck_buin && is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {
						if ( function_exists( 'get_product_addons' ) ) {
							$product_addons = get_product_addons( $product_exist, false );
						} else {
							$product_addons = WC_Product_Addons_Helper::get_product_addons( $product_exist );
							wp_enqueue_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
						}
						if ( is_array( $product_addons ) && count( $product_addons ) > 0 ) {
							$html = '<a href="' . get_the_permalink( $product_exist ) . '" class="wrstyle-' . esc_attr( $style ) . '-button">' . esc_html__( 'Order', 'woorestaurant' ) . '</a>';
						} elseif ( 'simple' == $type ) {
							$html = do_shortcode( '[wr_food_wooform id="' . $product_exist . '" hide_pm="1"]' );
						}
					}
				}
			} elseif ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {
				if ( function_exists( 'get_product_addons' ) ) {
					$product_addons = get_product_addons( $product_exist, false );
				} else {
					$product_addons = WC_Product_Addons_Helper::get_product_addons( $product_exist );
					wp_enqueue_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
				}
				if ( is_array( $product_addons ) && count( $product_addons ) > 0 ) {
					$product_addons;
				} elseif ( 'simple' == $type ) {
					$html = do_shortcode( '[wr_food_wooform id="' . $product_exist . '" hide_pm="1"]' );
				}
			} elseif ( is_plugin_active( 'woocommerce-tm-extra-product-options/tm-woo-extra-product-options.php' ) ) {
				// alway open lightbox.
				$type;
			} elseif ( 'simple' == $type ) {
				$html = do_shortcode( '[wr_food_wooform id="' . $product_exist . '" hide_pm="1"]' );
			}
		}
		// inline button.
		// phpcs:ignore
		echo '<div class="wrsbt-inline">' . ( $html ) . '</div>';
	}
}

add_filter( 'woocommerce_add_to_cart_fragments', 'woo_restaurant_woo_cart_count_fragments', 10, 1 );
/**
 * Cart count fragments.
 *
 * @param array $fragments it is the cart data.
 */
function woo_restaurant_woo_cart_count_fragments( $fragments ) {
	$fragments['span.wrr_cart_count'] = '<span class="wrr_cart_count">' . WC()->cart->get_cart_contents_count() . ' Items</span>';

	return $fragments;
}

add_filter( 'woocommerce_add_to_cart_fragments', 'woo_restaurant_woo_cart_content_fragments', 10, 1 );
/**
 * Cart content fragments.
 *
 * @param array $fragments it is the content fragments.
 */
function woo_restaurant_woo_cart_content_fragments( $fragments ) {
	ob_start();
	?>
	<div class="wrsfd-cart-mini"><?php woocommerce_mini_cart(); ?></div>
	<?php
	$fragments['div.wrsfd-cart-mini'] = ob_get_contents();
	ob_get_clean();
	return $fragments;
}

/**
 * Wooresf price.
 *
 * @param int $id_food it is the id of food.
 */
function woo_restaurant_price_with_currency( $id_food = false ) {
	global $product;
	if ( isset( $id_food ) && is_numeric( $id_food ) ) {
		$product = wc_get_product( $id_food );
	}
	$price      = '';
	$price_html = $product->get_price_html();
	if ( $price_html ) :
		$price = $price_html;
	endif;
	return $price;
}

/**
 * It is the cart icon html.
 *
 * @param string $show it is the show check.
 */
function woo_restaurant_woo_cart_icon_html( $show ) {
	global $cart_icon;
	if ( ! isset( $cart_icon ) || 'on' != $cart_icon || 'yes' == $show ) {
		$cart_icon = 'on';
	} elseif ( 'on' == $cart_icon ) {
		return;
	}
	if ( ! function_exists( 'woocommerce_mini_cart' ) ) {
		return;
	}
	ob_start();
	woo_restaurant_template_plugin( 'cart-mini', 1 );
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
}

/**
 * Select loc HTML.
 *
 * @param array $atts it is the attribute.
 */
function woo_restaurant_select_loc_html( $atts ) {
	$locations = isset( $atts['locations'] ) ? $atts['locations'] : '';
	$args      = array(
		'hide_empty' => true,
		'parent'     => '0',
	);
	$locations = '' != $locations ? explode( ',', $locations ) : array();
	if ( ! empty( $locations ) && ! is_numeric( $locations[0] ) ) {
		$args['slug'] = $locations;
	} elseif ( ! empty( $locations ) ) {
		$args['include'] = $locations;
	}
	$args = apply_filters( 'woore_location_args', $args );
	// phpcs:ignore
	$terms = get_terms( 'woorestaurant_loc', $args );
	ob_start();
	$loc_selected = isset( WC()->session ) ? WC()->session->get( 'wr_userloc' ) : '';
	?>
	<div class="woorst-select-loc">
		<div>
			<select class="wrs-loc-select" data-text="<?php esc_html_e( 'The products added to cart for this location will reset. Are you sure you want to change the location ?', 'woorestaurant' ); ?>">
				<?php
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					global $wp;
					$cr_url        = home_url( $wp->request );
					$cr_url        = apply_filters( 'woore_current_link', $cr_url );
					$select_option = '';
					echo '<option disabled selected value>' . esc_html__( '-- Select --', 'woorestaurant' ) . '</option>';
					$count_stop = 5;
					foreach ( $terms as $term ) {
						$url      = add_query_arg( array( 'loc' => $term->slug ), $cr_url );
						$selected = $loc_selected == $term->slug ? 'selected' : '';
						echo '<option value="' . esc_url( $url ) . '" ' . esc_attr( $selected ) . ' >' . wp_kses_post( $term->name ) . '</option>';
						wooresfd_show_child_location( $locations, $term, $count_stop, $loc_selected, '' );
					}
				} //if have terms.
				?>
			</select>
		</div>
	</div>
	<?php
	$cart_content = ob_get_contents();
	ob_end_clean();
	return $cart_content;
}

add_shortcode( 'woo_restaurant_sllocation', 'woo_restaurant_select_loc_html' );
if ( ! function_exists( 'wooresfd_show_child_location' ) ) {
	/**
	 * Show child location.
	 *
	 * @param array  $locations it is the locations.
	 * @param object $term it is the term.
	 * @param int    $count_stop it is the count.
	 * @param string $loc_selected it is the location selected.
	 * @param string $checkout it is the checkout.
	 * @param string $s_all it is the string.
	 */
	function wooresfd_show_child_location( $locations, $term, $count_stop, $loc_selected, $checkout, $s_all = false ) {
		if ( $count_stop < 2 ) {
			return;
		}
		$charactor = '';
		if ( 5 == $count_stop ) {
			$charactor = ' ';
		} elseif ( 4 == $count_stop ) {
			$charactor = '—— ';
		} elseif ( 3 == $count_stop ) {
			$charactor = '—— ';
		} elseif ( 2 == $count_stop ) {
			$charactor = '——— ';
		}
		$args_child = array(
			'child_of'   => $term->term_id,
			'parent'     => $term->term_id,
			'hide_empty' => false,
		);
		$args_child = apply_filters( 'woore_location_args', $args_child );
		if ( isset( $s_all ) && true == $s_all ) {
			$args_child['exclude'] = '';
		} else {
			$s_all = '';
		}
		// phpcs:ignore
		$second_level_terms = get_terms( 'woorestaurant_loc', $args_child );
		$loc_current        = '';

		ob_start();
		if ( $second_level_terms ) {
			$count_stop = $count_stop--;
			foreach ( $second_level_terms as $second_level_term ) {
				if ( 'yes' != $checkout ) {
					global $wp;
					$cr_url   = home_url( $wp->request );
					$cr_url   = apply_filters( 'woore_current_link', $cr_url );
					$url      = add_query_arg( array( 'loc' => $second_level_term->slug ), $cr_url );
					$selected = $loc_selected == $second_level_term->slug ? 'selected' : '';
					echo '<option value="' . esc_url( $url ) . '" ' . esc_attr( $selected ) . ' >' . esc_attr( $charactor ) . wp_kses_post( $second_level_term->name ) . '</option>';
				} else {
					$select_loc = '';
					if ( '' != $second_level_term->slug && $second_level_term->slug == $loc_selected ) {
						$select_loc = ' selected="selected"';
					}
					echo '<option value="' . esc_attr( $second_level_term->slug ) . '" ' . esc_attr( $select_loc ) . '>' . esc_attr( $charactor ) . wp_kses_post( $second_level_term->name ) . '</option>';
				}

				wooresfd_show_child_location( $locations, $second_level_term, $count_stop, $loc_selected, $checkout, $s_all );
			}
		}
		$output_string = ob_get_contents();
		ob_end_clean();
		if ( 'yes' == $checkout ) {
			return $output_string;
		} else {
			echo esc_attr( $output_string );
		}
	}
}

/**
 * Select location HTML.
 *
 * @param string $locations it is the locations.
 */
function woo_restaurant_select_location_html( $locations ) {
	if ( 'yes' != woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) {
		return;
	}
	global $loc_exits;
	$loc_selected = WC()->session->get( 'wr_userloc' );
	if ( '' != $loc_selected ) {
		return;
	}
	if ( ! isset( $loc_exits ) || 'on' != $loc_exits ) {
		$loc_exits = 'on';
	} elseif ( 'on' == $loc_exits ) {
		return;
	}
	$atts              = array();
	$atts['locations'] = $locations;
	?>
	<div class="wrs-popup-location">
		<div class="wrs-popup-content">
			<?php
			$icon = woo_restaurant_get_option( 'woo_restaurant_loc_icon' );
			if ( '' != $icon ) {
				?>
				<div class="wrs-pop-icon">
					<img src="<?php echo esc_url( $icon ); ?>" alt="image">
				</div>
			<?php } ?>
			<div class="wrs-popup-info">
				<h1><?php esc_html_e( 'Please choose area you want to order', 'woorestaurant' ); ?></h1>
				<?php echo esc_attr( woo_restaurant_select_loc_html( $atts ) ); ?>
			</div>
		</div>

	</div>
	<?php
}

add_action( 'init', 'woo_restaurant_user_select_location', 20 );
add_action( 'template_redirect', 'woo_restaurant_user_select_location', 20 );
/**
 * User Selection Location.
 */
function woo_restaurant_user_select_location() {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) || ! isset( WC()->session ) ) {
		return;
	}
	if ( isset( $_GET['loc'] ) ) {
		$term = term_exists( wp_kses_post( wp_unslash( $_GET['loc'] ) ), 'woorestaurant_loc' );
		if ( 0 !== $term && null !== $term ) {
			$wr_userloc = WC()->session->get( 'wr_userloc' );
			if ( '' == $wr_userloc || $wr_userloc != $_GET['loc'] ) {
				if ( 'yes' == woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) {
					global $woocommerce;
					$woocommerce->cart->empty_cart();
				}
				WC()->session->set( 'wr_userloc', wp_kses_post( wp_unslash( $_GET['loc'] ) ) );
				if ( '' == WC()->session->get( '_user_deli_log' ) ) {
					WC()->session->set( '_user_deli_log', wp_kses_post( wp_unslash( $_GET['loc'] ) ) );
				}
			}
		} else {
			WC()->session->set( 'wr_userloc', '' );
		}
	}

	$location = woo_restaurant_get_option( 'woo_restaurant_multi_location', 'woo_restaurant_advanced_options' );
	if ( 'yes' != woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) {
		WC()->session->set( 'wr_userloc', '' );
	}
}

/**
 * Location field HTML.
 */
function woo_restaurant_location_field_html() {
	$args = array(
		'hide_empty' => true,
		'parent'     => '0',
	);
	$args = apply_filters( 'woore_location_args', $args );
	// phpcs:ignore
	$terms = get_terms( 'woorestaurant_loc', $args );
	ob_start();
	$loc_selected = WC()->session->get( 'wr_userloc' );
	$loc_current  = '';
	?>
	<select class="wrs-ck-select wrsfd-choice-locate" name="_location">
		<?php
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			global $wp;
			if ( woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) != 'yes' ) {
				$select_option = '';
				$count_stop    = 5;
				echo '<option disabled selected value>' . esc_html__( '-- Select --', 'woorestaurant' ) . '</option>';

				foreach ( $terms as $term ) {
					$select_loc = '';
					if ( '' != $term->slug && $term->slug == $loc_current ) {
						$select_loc = ' selected="selected"';
					}
					echo '<option value="' . esc_attr( $term->slug ) . '" ' . esc_attr( $select_loc ) . '>' . wp_kses_post( $term->name ) . '</option>';
					wooresfd_show_child_location( '', $term, $count_stop, $loc_selected, 'yes' );
				}
			} else {
				$term = get_term_by( 'slug', $loc_selected, 'woorestaurant_loc' );
				echo '<option selected value="' . esc_attr( $loc_selected ) . '">' . wp_kses_post( $term->name ) . '</option>';
			}
		}
		?>
	</select>
	<?php
	$loca = ob_get_contents();
	ob_end_clean();
	return $loca;
}

add_action( 'wp_ajax_woo_restaurant_loadstore', 'ajax_woo_restaurant_loadstore' );
add_action( 'wp_ajax_nopriv_woo_restaurant_loadstore', 'ajax_woo_restaurant_loadstore' );
/**
 * Ajax Restaurant Loadstore.
 */
function ajax_woo_restaurant_loadstore() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'reataurant-loadstore' ) ) {
			return;
		}
	}

	if ( isset( $_POST['param_query'] ) || isset( $_POST['locate_param'] ) ) {
		$param_query  = json_decode( stripslashes( wp_kses_post( wp_unslash( $_POST['param_query'] ) ) ), true );
		$locate_param = sanitize_text_field( wp_kses_post( wp_unslash( $_POST['locate_param'] ) ) );
	}
	$locate_param = '';
	if ( '' == $locate_param ) {
		return;
	}
	ob_start();
	$posts_array = get_posts(
		array(
			'post_status' => array( 'publish' ),
			'post_type'   => 'woo_restaurant_store',
			'tax_query'   => array(
				array(
					'taxonomy' => 'woorestaurant_loc',
					'field'    => 'slug',
					'terms'    => $locate_param,
				),
			),
		)
	);

	$count = count( $posts_array );
	if ( 0 == $count ) {
		echo '0';
	} else {
		echo '<label class="wrsfd-label">' . esc_html__( 'Select store', 'woorestaurant' ) . '</label>';
		$number = 1;
		$check  = '';
		foreach ( $posts_array as $it ) {
			if ( 1 == $number ) {
				$check = 'checked="checked"';
			} else {
				$check = '';
			}
			$number = $number++;
			echo '<label class="wrsfd-container"><p>' . wp_kses_post( $it->post_title ) . '</p>
				<span>' . esc_attr( $it->post_content ) . '</span>
				<input class="wrsfd-choice-order" type="radio" name="_store" ' . esc_attr( $check ) . ' value="' . esc_attr( $it->ID ) . '">
				<span class="wrsfd-checkmark"></span>
	        </label>';
		}
	}
	$html   = ob_get_clean();
	$output = array( 'html_content' => $html );
	echo esc_attr( str_replace( '\/', '/', json_encode( $output ) ) );
	die;
}


if ( ! function_exists( 'woo_restaurant_pagenavi_no_ajax' ) ) {
	/**
	 * Page navigation no ajax.
	 *
	 * @param array $the_query it is the query.
	 */
	function woo_restaurant_pagenavi_no_ajax( $the_query ) {
		if ( function_exists( 'paginate_links' ) ) {
			echo '<div class="woorst-no-ajax-pagination">';
			echo esc_attr(
				paginate_links(
					array(
						'base'      => esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) ),
						'format'    => '',
						'add_args'  => false,
						'current'   => max( 1, get_query_var( 'paged' ) ),
						'total'     => $the_query->max_num_pages,
						'prev_text' => '&larr;',
						'next_text' => '&rarr;',
						'type'      => 'list',
						'end_size'  => 3,
						'mid_size'  => 3,
					)
				)
			);
			echo '</div>';
		}
	}
}

if ( ! function_exists( 'wooresfd_get_current_time' ) ) {
	/**
	 * Get current time.
	 */
	function wooresfd_get_current_time() {
		$cure_time  = strtotime( 'now' );
		$gmt_offset = get_option( 'gmt_offset' );
		if ( '' != $gmt_offset ) {
			$cure_time = $cure_time + ( $gmt_offset * 3600 );
		}
		return $cure_time;
	}
}
if ( ! function_exists( 'woore_get_loc_selected' ) ) {
	/**
	 * Get location selected.
	 */
	function woore_get_loc_selected() {
		$loc_selected = WC()->session->get( 'wr_userloc' );
		$user_log     = '';
		if ( '' == $loc_selected ) {
			$user_log     = WC()->session->get( '_user_deli_log' );
			$loc_selected = $user_log;
		}
		if ( '' != $loc_selected ) {
			$term = get_term_by( 'slug', $loc_selected, 'woorestaurant_loc' );
			if ( isset( $term->term_id ) ) {
				return $term->term_id;
			}
		}
		return;
	}
}
if ( ! function_exists( 'woore_check_open_close_time' ) ) {
	/**
	 * Check open close time.
	 *
	 * @param int $id_cr it is the id.
	 */
	function woore_check_open_close_time( $id_cr = false ) {
		$enable_time = woo_restaurant_get_option( 'woo_restaurant_open_close', 'woo_restaurant_advanced_options' );
		if ( 'closed' == $enable_time ) {
			return false;
		}
		$check_pr = false;
		if ( isset( $id_cr ) && is_numeric( $id_cr ) ) {
			$al_products = woo_restaurant_get_option( 'woo_restaurant_ign_op', 'woo_restaurant_advanced_options' );
			if ( '' != $al_products ) {
				$al_products = explode( ',', $al_products );
				if ( in_array( $id_cr, $al_products ) ) {
					$check_pr = true;
				}
			}
		}
		if ( '' == $enable_time || true == $check_pr ) {
			return true;
		}
		$cure_time = wooresfd_get_current_time();
		// phpcs:ignore
		date_default_timezone_set( 'UTC' );
		$hours_current   = intval( gmdate( 'H', $cure_time ) );
		$minutes_current = intval( gmdate( 'i', $cure_time ) );
		// $times is time stamp start 00:00:00.
		$times = $cure_time - $hours_current * 3600 - $minutes_current * 60;
		// Closed from date to date.
		$closed_dates = woo_restaurant_get_option( 'woores_opcl_datetodate', 'woo_restaurant_advanced_options' );
		if ( is_array( $closed_dates ) && ! empty( $closed_dates ) ) {
			foreach ( $closed_dates as $key => $closed_date ) {
				$cls_start = isset( $closed_date['opcl_start'] ) ? $closed_date['opcl_start'] : '';
				$cls_end   = isset( $closed_date['opcl_end'] ) ? $closed_date['opcl_end'] : '';
				if ( '' != $cls_start && $cure_time >= $cls_start && '' != $cls_end && $cure_time <= $cls_end ) {
					return false;
					break;
				}
			}
		}
		// advanced open closing time by day of week.
		$opcl_time = woo_restaurant_get_option( 'woores_' . gmdate( 'D', $cure_time ) . '_opcl_time', 'woo_restaurant_advanced_options' );
		// for each location.
		$log_selected = woore_get_loc_selected();
		if ( '' != $log_selected ) {
			$loc_opcls = woo_restaurant_get_option( 'woo_restaurant_open_close_loc', 'woo_restaurant_advanced_options' );
			if ( 'yes' == $loc_opcls ) {
				$opcl_time_log = get_term_meta( $log_selected, 'woores_' . gmdate( 'D', $cure_time ) . '_opcl_time', true );
				$opcl_time     = is_array( $opcl_time_log ) && ! empty( $opcl_time_log ) ? $opcl_time_log : $opcl_time;
			}
		}
		if ( is_array( $opcl_time ) && ! empty( $opcl_time ) ) {
			$check = true;
			foreach ( $opcl_time as $it_time ) {
				$open_hours  = isset( $it_time['open-time'] ) ? intval( gmdate( 'H', strtotime( $it_time['open-time'] ) ) ) * 3600 + intval( gmdate( 'i', strtotime( $it_time['open-time'] ) ) ) * 60 : 0;
				$close_hours = isset( $it_time['close-time'] ) ? intval( gmdate( 'H', strtotime( $it_time['close-time'] ) ) ) * 3600 + intval( gmdate( 'i', strtotime( $it_time['close-time'] ) ) ) * 60 : 0;
				if ( $close_hours < $open_hours ) {
					$close_hours = $close_hours + 86400;
				}
				$open_hours_unix  = $times + $open_hours;
				$close_hours_unix = $times + $close_hours;
				if ( $open_hours_unix > $close_hours_unix || $cure_time < $open_hours_unix || $cure_time > $close_hours_unix ) {
					$check = false;
				} else {
					$check = true;
					break;
				}
			}
			return $check;
		} else {
			$open_hours  = woo_restaurant_get_option( 'woo_restaurant_ck_open_hour', 'woo_restaurant_advanced_options' );
			$close_hours = woo_restaurant_get_option( 'woo_restaurant_ck_close_hour', 'woo_restaurant_advanced_options' );
			if ( '' == $open_hours || '' == $close_hours ) {
				return false;
			}
			$open_hours_unix  = $times + intval( gmdate( 'H', strtotime( $open_hours ) ) ) * 3600 + intval( gmdate( 'i', strtotime( $open_hours ) ) ) * 60;
			$close_hours_unix = $times + intval( gmdate( 'H', strtotime( $close_hours ) ) ) * 3600 + intval( gmdate( 'i', strtotime( $close_hours ) ) ) * 60;
			if ( $open_hours_unix > $close_hours_unix || $cure_time < $open_hours_unix || $cure_time > $close_hours_unix ) {
				return false;
			}
			return true;
		}
		return $check_pr;
	}
}
if ( ! function_exists( 'wooresfd_get_next_open_close_time' ) ) {
	/**
	 * Get next open hour.
	 */
	function wooresfd_get_next_open_close_time() {
		$cure_time = wooresfd_get_current_time();
		// phpcs:ignore
		date_default_timezone_set( 'UTC' );
		$hours_current   = intval( gmdate( 'H', $cure_time ) );
		$minutes_current = intval( gmdate( 'i', $cure_time ) );
		// $times is time stamp start 00:00:00.
		$times       = $cure_time - $hours_current * 3600 - $minutes_current * 60;
		$open_hours  = '';
		$close_hours = '';
		for ( $i = 0; $i < 7; $i++ ) {
			$check     = false;
			$timck     = $cure_time + ( $i * 86400 );
			$opcl_time = woo_restaurant_get_option( 'woores_' . gmdate( 'D', $timck ) . '_opcl_time', 'woo_restaurant_advanced_options' );
			// for each location.
			$log_selected = woore_get_loc_selected();
			if ( '' != $log_selected ) {
				$loc_opcls = woo_restaurant_get_option( 'woo_restaurant_open_close_loc', 'woo_restaurant_advanced_options' );
				if ( 'yes' == $loc_opcls ) {
					$opcl_time_log = get_term_meta( $log_selected, 'woores_' . gmdate( 'D', $cure_time ) . '_opcl_time', true );
					$opcl_time     = is_array( $opcl_time_log ) && ! empty( $opcl_time_log ) ? $opcl_time_log : $opcl_time;
				}
			}
			if ( is_array( $opcl_time ) && ! empty( $opcl_time ) ) {

				foreach ( $opcl_time as $it_time ) {
					$open_hours  = $it_time['open-time'];
					$close_hours = $it_time['close-time'];
					if ( 0 == $i ) {
						$open_hours_unix  = $times + intval( gmdate( 'H', strtotime( $open_hours ) ) ) * 3600 + intval( gmdate( 'i', strtotime( $open_hours ) ) ) * 60;
						$close_hours_unix = $times + intval( gmdate( 'H', strtotime( $close_hours ) ) ) * 3600 + intval( gmdate( 'i', strtotime( $close_hours ) ) ) * 60;
						if ( $open_hours_unix > $cure_time ) {
							$check = true;
							break;
						}
					} elseif ( $open_hours != $close_hours ) {

						$check = true;
						break;
					}
				}
			}
			if ( true == $check ) {
				break;
			}
		}
		if ( true == $check ) {
			if ( 0 == $i ) {
				$timck = '';
			}
			return array( $open_hours, $close_hours, $timck );
		}
	}
}
if ( ! function_exists( 'wooresfd_open_closing_message' ) ) {
	/**
	 * Open closing time message.
	 *
	 * @param bool $rhtml it is the check.
	 */
	function wooresfd_open_closing_message( $rhtml = false ) {
		$enable_time = woo_restaurant_get_option( 'woo_restaurant_open_close', 'woo_restaurant_advanced_options' );
		ob_start();
		$next_op = wooresfd_get_next_open_close_time();
		// Closed from date to date.
		$closed_dates = woo_restaurant_get_option( 'woores_opcl_datetodate', 'woo_restaurant_advanced_options' );
		$open_after   = '';
		if ( is_array( $closed_dates ) && ! empty( $closed_dates ) ) {
			$cure_time = wooresfd_get_current_time();
			foreach ( $closed_dates as $key => $closed_date ) {
				$cls_start = isset( $closed_date['opcl_start'] ) ? $closed_date['opcl_start'] : '';
				$cls_end   = isset( $closed_date['opcl_end'] ) ? $closed_date['opcl_end'] : '';
				if ( '' != $cls_start && $cure_time >= $cls_start && '' != $cls_end && $cure_time <= $cls_end ) {
					$open_after = $cls_end;
					break;
				}
			}
		}
		if ( 'closed' == $enable_time ) {
			$text = esc_html__( 'Ordering food is now closed', 'woorestaurant' );
		} elseif ( '' != $open_after ) {
			$date_cls = date_i18n( get_option( 'date_format' ), $open_after );
			$time_cls = date_i18n( get_option( 'time_format' ), $open_after );
			/* translators: %1$s is replaced with the date, %2$s is replaced with the time */
			$text = sprintf( esc_html__( 'Ordering food is now closed, please come back after %1$s %2$s', 'woorestaurant' ), $date_cls, $time_cls );
		} elseif ( array( $next_op ) && ! empty( $next_op ) ) {
			$fp   = date_i18n( get_option( 'time_format' ), strtotime( $next_op[0] ) );
			$to   = date_i18n( get_option( 'time_format' ), strtotime( $next_op[1] ) );
			$nday = isset( $next_op[2] ) && is_numeric( $next_op[2] ) ? date_i18n( 'l', $next_op[2] ) : esc_html__( 'Today', 'woorestaurant' );
			/* translators: %1$s is replaced with the fp variable, %2$s is replaced with the to variable, %3$s is replaced with the day */
			$text = sprintf( esc_html__( 'Ordering food is now closed, please come back from %1$s to %2$s %3$s', 'woorestaurant' ), $fp, $to, $nday );
		} else {
			$text = esc_html__( 'Ordering food is now closed', 'woorestaurant' );
		}

		$text = apply_filters( 'woores_opcl_text', $text, $next_op );
		if ( isset( $rhtml ) && true == $rhtml ) {
			echo esc_attr( $text );
		} else {
			echo '<p class="wrsfd-out-notice excls-notice">' . esc_attr( $text ) . '</p>';
		}
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}

if ( ! function_exists( 'wooresfd_show_reviews' ) ) {
	/**
	 * Show reviews.
	 *
	 * @param int    $id_food it is the id of the food.
	 * @param object $product it is the object of the product.
	 */
	function wooresfd_show_reviews( $id_food, $product = false ) {
		if ( ! isset( $product ) || '' == $product ) {
			$product = wc_get_product( $id_food );
		}
		if ( function_exists( 'wc_get_rating_html' ) ) {
			$rating_html = wc_get_rating_html( $product->get_average_rating() );
		} else {
			$rating_html = $product->get_rating_html();
		}
		$rating_count = $product->get_rating_count();
		if ( $rating_count > 0 && 'no' != get_option( 'woocommerce_enable_review_rating' ) && $rating_html ) {
			return '<div class="wrsf-rating woocommerce">' . $rating_html . '</div>';
		}
	}
}

/**
 * Custom color method.
 *
 * @param string $sc it is the grid style.
 * @param string $style it is the style.
 * @param int    $id it is the id.
 */
function woore_custom_color( $sc, $style, $id ) {
	$color = get_post_meta( get_the_ID(), 'woo_restaurant_custom_color', true );
	if ( '' == $color ) {
		return;
	}
	?>
	<style type="text/css">
		<?php
		if ( 'grid' == $sc ) {
			if ( '1' == $style || '2' == $style ) {
				?>
		.wrs-fdlist #<?php echo esc_attr( $id ); ?>figcaption .wrsbt-inline>a,
		#<?php echo esc_attr( $id ); ?>.woorst-woocommerce.woocommerce form.cart button[type="submit"] {
			background: <?php echo esc_attr( $color ); ?>;
		}

		#<?php echo esc_attr( $id ); ?>figcaption h5 {
			color: <?php echo esc_attr( $color ); ?>;
		}

				<?php
			} elseif ( '3' == $style ) {
				?>
		#<?php echo esc_attr( $id ); ?>figcaption h5 {
			color: <?php echo esc_attr( $color ); ?>;
		}

				<?php
			} elseif ( '4' == $style ) {
				?>
		.wrs-fdlist #<?php echo esc_attr( $id ); ?>.wrsfd-icon-plus:before,
		.wrs-fdlist #<?php echo esc_attr( $id ); ?>.wrsfd-icon-plus:after,
		#<?php echo esc_attr( $id ); ?>figcaption h5 {
			background: <?php echo esc_attr( $color ); ?>;
		}

		.wrs-fdlist #<?php echo esc_attr( $id ); ?>.wrstyle-4-button.wrsfd-choice {
			border-color: <?php echo esc_attr( $color ); ?>;
		}

				<?php
			}
		} elseif ( 'list' == $sc || 'table' == $sc ) {
			?>
		.wrs-fdlist #<?php echo esc_attr( $id ); ?>.wrsfd-icon-plus:before,
		.wrs-fdlist #<?php echo esc_attr( $id ); ?>.wrsfd-icon-plus:after,
		#<?php echo esc_attr( $id ); ?>figcaption h5 {
			background: <?php echo esc_attr( $color ); ?>;
		}

		.wrs-fdlist #<?php echo esc_attr( $id ); ?>.wrsfd-choice {
			border-color: <?php echo esc_attr( $color ); ?>;
		}

			<?php
		}
		?>
	</style>
	<?php
}

/**
 * Search html.
 *
 * @param string $enable_search it is the search check.
 */
function woore_search_html( $enable_search ) {
	if ( 'yes' != $enable_search ) {
		return;
	}
	?>
	<div class="wrsf-search">
		<form role="search" method="get" class="wrsf-search-form" action="<?php echo esc_attr( home_url() ); ?>/">
			<input type="hidden" name="post_type" value="product" />
			<input type="text" value="<?php the_search_query(); ?>" name="s" id="s" placeholder="<?php echo esc_html__( 'Type Keywords', 'woorestaurant' ); ?>" class="wrsf-s-field" />
			<button type="submit" class="wrsf-s-submit"><img src="<?php echo esc_attr( WOORESTAURANT_ASSETS ) . 'css/img/search-outline.svg'; ?>" alt="image-cart"></button>
		</form>
	</div>
	<?php
}

add_filter( 'woores_accordion_style', 'woo_restaurant_extra_option_accordion_style', 10, 1 );
/**
 * Extra option accordian style.
 *
 * @param bool $style it is the style.
 */
function woo_restaurant_extra_option_accordion_style( $style ) {
	if ( 'accordion' == woo_restaurant_get_option( 'woo_restaurant_wrsoptions_style' ) ) {
		$style = true;
	}
	return $style;
}

if ( ! function_exists( 'woores_add_info_to_invoice' ) ) {
	add_action( 'wpo_wcpdf_after_order_data', 'woores_add_info_to_invoice', 10, 3 );
	/**
	 * Add info to pdf invoice.
	 *
	 * @param string $type it is the type.
	 * @param object $order it is the object of the order.
	 */
	function woores_add_info_to_invoice( $type, $order ) {
		$dv_date = get_post_meta( $order->get_id(), 'woores_date_deli', true );
		$dv_time = get_post_meta( $order->get_id(), 'woores_time_deli', true );
		$loc_ar  = get_post_meta( $order->get_id(), 'woo_restaurant_location', true );

		$text_datedel = woore_date_time_text( 'date', $order );
		$text_timedel = woore_date_time_text( 'time', $order );
		if ( '' != $dv_date ) {
			?>
			<tr>
				<th><?php echo esc_attr( $text_datedel ); ?></th>
				<td><?php echo esc_attr( $dv_date ); ?></td>
			</tr>
			<?php
		}
		if ( '' != $dv_time ) {
			?>
			<tr>
				<th><?php echo esc_attr( $text_timedel ); ?></th>
				<td><?php echo esc_attr( $dv_time ); ?></td>
			</tr>
			<?php
		}
		$log_name = get_term_by( 'slug', $loc_ar, 'woorestaurant_loc' );
		if ( $log_name->name ) {
			?>
			<tr>
				<th><?php echo esc_html__( 'Location', 'woorestaurant' ); ?></th>
				<td><?php echo esc_attr( $log_name->name ); ?></td>
			</tr>
			<?php
		}
	}
}

/**
 * Datetime text.
 *
 * @param string $text it is the text.
 * @param object $order it is the order object.
 */
function woore_date_time_text( $text, $order = false ) {
	$text_datedel = esc_html__( 'Delivery Date', 'woorestaurant' );
	$text_timedel = esc_html__( 'Delivery Time', 'woorestaurant' );
	if ( isset( $order ) && is_object( $order ) && method_exists( $order, 'get_id' ) ) {
		$user_odmethod = get_post_meta( $order->get_id(), 'woores_order_method', true );
	} else {
		$user_odmethod = WC()->session->get( '_user_order_method' );
	}
	if ( 'takeaway' == $user_odmethod ) {
		$text_datedel = esc_html__( 'Pickup Date', 'woorestaurant' );
		$text_timedel = esc_html__( 'Pickup Time', 'woorestaurant' );
	}
	if ( 'dinein' == $user_odmethod ) {
		$text_datedel = esc_html__( 'Date', 'woorestaurant' );
		$text_timedel = esc_html__( 'Time', 'woorestaurant' );
	}
	if ( 'date' == $text ) {
		return apply_filters( 'woore_datedeli_text', $text_datedel );
	} else {
		return apply_filters( 'woore_timedeli_text', $text_timedel );
	}
}

// add_action('woocommerce_checkout_update_order_review', 'woore_change_text_shipping_methods', 10, 1);.

/**
 * Change and using jquery to change text like above.
 *
 * @param string $post_data it is the data.
 */
function woore_change_text_shipping_methods( $post_data ) {
	parse_str( $post_data, $get_array );
	$method = isset( $get_array['shipping_method'][0] ) ? $get_array['shipping_method'][0] : '';
	if ( 'flat_rate:3' == $method ) {
		WC()->session->set( '_user_order_method', 'delivery' );
	} else {
		WC()->session->set( '_user_order_method', 'takeaway' );
	}
}

if ( ! function_exists( 'woore_change_datelb_shipping_methods' ) ) {
	// add_filter('woore_datedeli_text', 'woore_change_datelb_shipping_methods', 10, 1);.

	/**
	 * Change date shipping methods.
	 *
	 * @param object $text it is the text variable.
	 */
	function woore_change_datelb_shipping_methods( $text ) {
		if ( is_admin() ) {
			global $pagenow;
			if ( ( 'post.php' == $pagenow ) || ( 'shop_order' == get_post_type() ) ) {
				$order  = wc_get_order( get_the_ID() );
				$method = @array_shift( $order->get_shipping_methods() );
				$method = isset( $method['method_id'] ) ? $method['method_id'] : '';
			}
		} else {
			$method = WC()->session->get( 'chosen_shipping_methods' );
			$method = isset( $method[0] ) ? $method[0] : '';
		}
		// phpcs:ignore
		if ( strpos( $method, 'flat_rate' ) !== false ) {
			$text = esc_html__( 'Delivery Date', 'woorestaurant' );
		} else {
			$text = esc_html__( 'Pickup Date', 'woorestaurant' );
		}
		return $text;
	}
}

if ( ! function_exists( 'woore_change_timelb_shipping_methods' ) ) {
	// add_filter('woore_timedeli_text', 'woore_change_timelb_shipping_methods', 10, 1);.

	/**
	 * Change time shipping methods.
	 *
	 * @param string $text it is the text variable.
	 */
	function woore_change_timelb_shipping_methods( $text ) {
		if ( is_admin() ) {
			global $pagenow;
			if ( ( 'post.php' == $pagenow ) || ( 'shop_order' == get_post_type() ) ) {
				$order  = wc_get_order( get_the_ID() );
				$method = @array_shift( $order->get_shipping_methods() );
				$method = isset( $method['method_id'] ) ? $method['method_id'] : '';
			}
		} else {
			$method = WC()->session->get( 'chosen_shipping_methods' );
			$method = isset( $method[0] ) ? $method[0] : '';
		}
		// phpcs:ignore
		if ( strpos( $method, 'flat_rate' ) !== false ) {
			$text = esc_html__( 'Delivery time', 'woorestaurant' );
		} else {
			$text = esc_html__( 'Pickup time', 'woorestaurant' );
		}
		return $text;
	}
}

if ( ! function_exists( 'woore_auto_update_label_script' ) ) {
	// add_action( 'wp_footer', 'woore_auto_update_label_script', 999 );.

	/**
	 * Auto update label script.
	 */
	function woore_auto_update_label_script() {
		if ( is_checkout() ) :
			?>
			<script>
				jQuery(function($) {
					// woocommerce_params is required to continue, ensure the object exists.
					if (typeof woocommerce_params === 'undefined') {
						return false;
					}
					// Postkantoor shipping methods.
					$(document).on('change', '#shipping_method input[type="radio"]', function() {
						var $mth = $(this).val();
						var $rq = $rq_time = '';
						if ($('#woores_date_deli_field').hasClass('validate-required')) {
							$rq = '<abbr class="required" title="required">*</abbr>';
						}
						if ($('#woores_time_deli_field').hasClass('validate-required')) {
							$rq_time = '<abbr class="required" title="required">*</abbr>';
						}
						if ($mth.indexOf("flat_rate") >= 0) {
							$('#woores_date_deli_field label').html('<?php esc_html_e( 'Delivery Date', 'woorestaurant' ); ?> ' + $rq);
							$('#woores_time_deli_field label').html('<?php esc_html_e( 'Delivery Time', 'woorestaurant' ); ?> ' + $rq_time);
						} else {
							$('#woores_date_deli_field label').html('<?php esc_html_e( 'Pickup Date', 'woorestaurant' ); ?> ' + $rq);
							$('#woores_time_deli_field label').html('<?php esc_html_e( 'Pickup Time', 'woorestaurant' ); ?> ' + $rq_time);
						}
					});

				});
			</script>
			<?php
		endif;
	}
}

/**
 * Icon color.
 *
 * @param int $id it is the id.
 */
function woore_icon_color( $id = false ) {
	if ( false == $id ) {
		$id = get_the_ID();
	}
	$icons = get_post_meta( $id, 'woo_restaurant_cticon_gr', true );
	if ( is_array( $icons ) && ! empty( $icons ) ) {
		echo '<span class="wrsf-lbicons">';
		foreach ( $icons as $icon ) {
			$bg  = isset( $icon['bgcolor'] ) && '' != $icon['bgcolor'] ? 'background-color:' . $icon['bgcolor'] : '';
			$lb  = isset( $icon['lb_name'] ) ? $icon['lb_name'] : '';
			$img = isset( $icon['icon'] ) && '' != $icon['icon'] ? 'background-image:url(' . esc_url( $icon['icon'] ) . ')' : '';
			?>
			<span>
				<span class="wrsf-lbicon <?php echo '' == $img ? 'wrsf-ep-ic' : ''; ?>" style="<?php echo esc_attr( $bg ) . ';' . esc_attr( $img ); ?>"></span>
				<?php echo '' != $lb ? '<span class="wrsf-lbname">' . esc_attr( $lb ) . '</span>' : ''; ?>
			</span>
			<?php
		}
		echo '</span>';
	}
}

/**
 * Check if products from cart are not disable shiping feature.
 * Return true if one or all products is not disable shiping feature of Food.
 * Return false if all products are disable.
 */
function woore_if_check_product_notin_shipping() {
	$al_products = woo_restaurant_get_option( 'woo_restaurant_ign_deli', 'woo_restaurant_advanced_options' );
	$al_cats     = woo_restaurant_get_option( 'woo_restaurant_igncat_deli', 'woo_restaurant_advanced_options' );
	$check_ex    = true;
	if ( '' != $al_products || ( is_array( $al_cats ) && ! empty( $al_cats ) ) ) {
		$check_ex    = false;
		$al_products = '' != $al_products ? explode( ',', $al_products ) : array();
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$id_cr = $cart_item['product_id'];
			if ( is_array( $al_cats ) && ! empty( $al_cats ) ) {
				if ( ! in_array( $id_cr, $al_products ) && ! has_term( $al_cats, 'product_cat', $id_cr ) ) {
					$check_ex = true;
					break;
				}
			} elseif ( ! empty( $al_products ) && ! in_array( $id_cr, $al_products ) ) {
				$check_ex = true;
				break;
			}
		}
	}
	return $check_ex;
}

/**
 * Check if products from cart are disable shiping feature.
 * Return true if one or all products is disable shiping feature of food.
 * Return false if all products are not disable.
 */
function woore_if_check_product_in_shipping() {
	$al_products = woo_restaurant_get_option( 'woo_restaurant_ign_deli', 'woo_restaurant_advanced_options' );
	$al_cats     = woo_restaurant_get_option( 'woo_restaurant_igncat_deli', 'woo_restaurant_advanced_options' );
	$check_ex    = true;
	if ( '' != $al_products || ( is_array( $al_cats ) && ! empty( $al_cats ) ) ) {
		$check_ex    = false;
		$al_products = '' != $al_products ? explode( ',', $al_products ) : array();
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$id_cr = $cart_item['product_id'];
			if ( is_array( $al_cats ) && ! empty( $al_cats ) ) {
				if ( in_array( $id_cr, $al_products ) || has_term( $al_cats, 'product_cat', $id_cr ) ) {
					$check_ex = true;
					break;
				}
			} elseif ( ! empty( $al_products ) && in_array( $id_cr, $al_products ) ) {
				$check_ex = true;
				break;
			}
		}
	} else {
		$check_ex = false;
	}
	return $check_ex;
}

add_action( 'template_redirect', 'wooreswfredirect_single_product' );

/**
 * Open single product on popup.
 */
function wooreswfredirect_single_product() {
	$menu_url = woo_restaurant_get_option( 'woo_restaurant_menu_url', 'woo_restaurant_options' );
	if ( '' != $menu_url && is_singular( 'product' ) && filter_var( $menu_url, FILTER_VALIDATE_URL ) !== false ) {
		$postid   = get_queried_object_id();
		$menu_url = add_query_arg( 'wfproduct_id', $postid, $menu_url );
		$menu_url = apply_filters( 'woore_single_url_popup', $menu_url, $postid );
		wp_safe_redirect( $menu_url );
		exit;
	}
}

add_action( 'woo_restaurant_before_shortcode', 'woore_add_hidden_product_from_link' );
/**
 * Add hidden product from link.
 */
function woore_add_hidden_product_from_link() {
	if ( isset( $_GET['wfproduct_id'] ) && is_numeric( $_GET['wfproduct_id'] ) ) {
		remove_action( 'woo_restaurant_before_shortcode', 'woore_add_hidden_product_from_link' );
		$id_el = 'wrsf-single-' . wp_kses_post( wp_unslash( $_GET['wfproduct_id'] ) );
		// phpcs:ignore
		echo '<div class="wrs-hidden" id="' . esc_attr( $id_el ) . '">' . do_shortcode( '[woores_view_ordbutton product_id="' . wp_kses_post( wp_unslash( $_GET['wfproduct_id'] ) ) . '"]' ) . '
			<script>
			jQuery(document).ready(function($) {
				var contenId = "#' . esc_attr( $id_el ) . ' .item-grid[data-id_food=' . wp_kses_post( wp_unslash( $_GET['wfproduct_id'] ) ) . '] a";
				if($(contenId).length){
					$(contenId).first().trigger("click");
					return;
				}
			});
			</script>
		</div>';
	}
}

add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_single_add_to_cart_text' );
/**
 * Custom single add to cart text.
 *
 * @param string $button_text it is the button text.
 */
function woocommerce_custom_single_add_to_cart_text( $button_text ) {
	$is_store_close = woo_restaurant_get_option( 'woo_restaurant_store_close', 'woo_restaurant_options' );
	if ( '' != $is_store_close ) {
		$button_text = __( 'Shop is closed', 'woorestaurant' );
	}
	return $button_text;
}

add_filter( 'woocommerce_product_add_to_cart_text', 'woocommerce_custom_product_add_to_cart_text' );
/**
 * Custom product add to cart text.
 *
 * @param string $button_text it is the button text.
 */
function woocommerce_custom_product_add_to_cart_text( $button_text ) {
	$is_store_close = woo_restaurant_get_option( 'woo_restaurant_store_close', 'woo_restaurant_options' );
	if ( '' != $is_store_close ) {
		$button_text = __( 'Shop is closed', 'woorestaurant' );
	}
	return $button_text;
}

add_filter( 'woocommerce_add_to_cart_validation', 'woocommerce_change_product_add_to_cart_url' );
/**
 * Change product add to cart URL.
 *
 * @param bool $passed it is the check.
 */
function woocommerce_change_product_add_to_cart_url( $passed ) {
	$is_store_close = woo_restaurant_get_option( 'woo_restaurant_store_close', 'woo_restaurant_options' );
	if ( '' != $is_store_close ) {
		wc_add_notice( __( 'Shop is closed.', 'woorestaurant' ), 'error' );
		$passed = false;
	}
	return $passed;
}

add_action( 'woocommerce_after_checkout_validation', 'woocommerce_perform_after_checkout_validation', 10, 2 );
/**
 * After checkout validation.
 *
 * @param string $fields these are the fields.
 * @param object $errors it is the error object.
 */
function woocommerce_perform_after_checkout_validation( $fields, $errors ) {
	$is_store_close = woo_restaurant_get_option( 'woo_restaurant_store_close', 'woo_restaurant_options' );
	if ( '' != $is_store_close ) {
		$errors->add( 'validation', 'Shop is closed.' );
	}
}
