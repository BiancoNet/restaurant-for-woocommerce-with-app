<?php
/**
 * It is the content table 1 shortcode template.
 *
 * @package woorestaurant
 */

$customlink = Woo_restuaran_customlink( get_the_ID() );
global $number_excerpt, $product, $img_size, $hide_atc;
$order_price = $product->get_price();
$category    = get_the_terms( get_the_ID(), 'product_cat' );
// phpcs:ignore
$menu        = '';
if ( ! empty( $category ) ) {
	foreach ( $category as $cd ) {
		// phpcs:ignore
		$cat   = get_category( $cd );
		// phpcs:ignore
		$menu .= '<p>' . $cat->name . '</p>';
	}
}
$custom_price = get_post_meta( get_the_ID(), 'woo_restaurant_custom_price', true );
$price        = woo_restaurant_price_with_currency();
if ( '' != $custom_price ) {
	$price = $custom_price;
}
// phpcs:ignore
$id = 'ctc-' . rand( 1, 10000 ) . '-' . get_the_ID();
if ( '' == $img_size ) {
	$img_size = 'woo_restaurant_80x80';
}
?>
<tr data-id_food="<?php echo esc_attr( get_the_ID() ); ?>" id="<?php echo esc_attr( $id ); ?>">
	<td><a href="<?php echo esc_url( $customlink ); ?>"><?php the_post_thumbnail( $img_size ); ?><?php woore_icon_color( get_the_ID() ); ?></a></td>
	<td id="extd-<?php echo esc_attr( get_the_ID() ); ?>" class="wrs-fd-name" data-sort="<?php echo esc_attr( get_the_title() ); ?>">
	<?php echo '<div class="item-grid tppost-' . esc_attr( get_the_ID() ) . '" '; ?>
		<div class="exp-arrow">
		<h3><a href="<?php echo esc_url( $customlink ); ?>"><?php the_title(); ?></a></h3>
		<div class="wrsfd-show-tablet">
			<?php echo esc_html_e( 'Category:', 'woorestaurant' ) . wp_kses_post( $menu ); ?>
		</div>
		<div class="wrsfd-hide-mb">
			<div class="wrsfd-price-detail">
			<?php
				echo wp_kses_post( $price );
			?>
			</div>
			<?php if ( '0' != $number_excerpt ) { ?>
				<?php
				if ( has_excerpt( get_the_ID() ) ) {
					if ( 'full' == $number_excerpt ) {
						$excerpt = get_the_excerpt();
						?>
				<p><?php echo wp_kses_post( $excerpt ); ?></p>
								<?php
					} elseif ( '0' != $number_excerpt ) {
						$excerpt = wp_trim_words( get_the_excerpt(), $number_excerpt, '...' );
						?>
				<p><?php echo wp_kses_post( $excerpt ); ?></p>
								<?php
					}
				}
				?>
			  
			<?php } ?>
		</div>
		</div>
	</div>
	</td>
	<?php if ( '0' != $number_excerpt ) { ?>
	<td class="wrsfd-hide-screen wrs-fd-table-des">
		<?php if ( has_excerpt( get_the_ID() ) ) { ?>
			<p><?php echo wp_kses_post( $excerpt ); ?></p>
		<?php } ?>
	</td>
	<?php } ?>
  
	<td class="wrsfd-hide-screen wrsfd-hide-tablet wrs-fd-category" data-sort="<?php echo esc_attr( $menu ); ?>">
	<?php echo wp_kses_post( $menu ); ?>
	</td>

	<td class="wrsfd-hide-screen wrsfd-price" data-sort="<?php echo esc_attr( $order_price ); ?>">
	<div class="wrsfd-price-detail">
	<?php echo wp_kses_post( $price ); ?>
	</div>
	</td>
	<?php if ( 'yes' != $hide_atc ) { ?>
	<td class="wrs-fd-table-order">
		<?php woore_custom_color( 'table', '', $id ); ?>
		<?php
		if ( woore_check_open_close_time( get_the_ID() ) ) {
			echo '<div class="wrs-hidden">';
			woo_restaurant_booking_button_html( 1 );
			echo '</div>';
		} else {
			echo '<div class="wrs-hidden"><a href="' . esc_attr( get_the_permalink( get_the_ID() ) ) . '" class="wrstyle-1-button"></a></div>';
		}
		$prod = wc_get_product( get_the_ID() );
		if ( is_object( $prod ) && method_exists( $prod, 'get_stock_status' ) && $prod->get_stock_status() == 'outofstock' ) {
			echo '<span>' . esc_html__( 'Sold Out', 'woorestaurant' ) . '</span>';
		} else {
			echo '<button class="wooresfd_modal_click wrsfd-choice" data="food_id=' . esc_attr( get_the_ID() ) . '&food_qty=1"><div class="wrsfd-icon-plus"></div></button>';
		}
		?>
		 
	</td>
	<?php } ?>
</tr>
