<?php
/**
 * It is the content grid 4 shortcode template.
 *
 * @package woorestaurant
 */

$customlink = Woo_restuaran_customlink( get_the_ID() );
global $number_excerpt, $img_size, $hide_atc;
if ( '' == $img_size ) {
	$img_size = 'woo_restaurant_400x400';
}
$custom_price = get_post_meta( get_the_ID(), 'woo_restaurant_custom_price', true );
$price        = woo_restaurant_price_with_currency();
if ( '' != $custom_price ) {
	$price = $custom_price;
}
?>
<figure class="wrstyle-4 tppost-<?php the_ID(); ?>">
  
	<div class="wrstyle-4-image">
	<a class="wooresfd_modal_click" href="<?php echo esc_url( $customlink ); ?>">
		<?php
		echo get_the_post_thumbnail( get_the_ID(), $img_size );
		woore_icon_color();
		?>
	</a>
	<?php
	woo_restaurant_sale_badge();
	$prod    = wc_get_product( get_the_ID() );
	$cls_ost = '';
	$st_stt  = '';
	if ( is_object( $prod ) && method_exists( $prod, 'get_stock_status' ) && 'outofstock' == $prod->get_stock_status() ) {
		$cls_ost = 'wrsf-ofstock';
		$st_stt  = '<span>' . esc_html__( ' - Sold Out', 'woorestaurant' ) . '</span>';
	}
	?>
	</div><figcaption class="<?php echo esc_attr( $cls_ost ); ?>">
	<h3><a class="wooresfd_modal_click" href="<?php echo esc_url( $customlink ); ?>">
	<?php
		the_title();
		echo esc_attr( $st_stt );
	?>
	</a></h3>
	<?php
	// phpcs:ignore
	$id = get_the_ID();
	if ( has_excerpt( $id ) ) {
		echo '<div class="wrsf-shdes">';
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
		echo '</div>';
	}
	?>
	<h5>
		<?php echo wp_kses_post( $price ); ?>
	</h5>
	<?php
	do_action( 'woore_sc_after_shortdes' );
	if ( 'yes' != $hide_atc ) {
		echo '<div class="wrs-hidden">';
		woo_restaurant_booking_button_html( 1 );
		echo '</div>';
		?>
		<?php if ( woore_check_open_close_time( $id ) && 'disable' != woo_restaurant_get_option( 'woo_restaurant_booking' ) ) { ?>
		<button class="wrstyle-4-button wrsfd-choice"><div class="wrsfd-icon-plus"></div></button>
			<?php
		}
	}
	?>
	</figcaption>
</figure>