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
$excerpt = '';
if ( has_excerpt( get_the_ID() ) ) {
	if ( 'full' == $number_excerpt ) {
		$excerpt = get_the_excerpt();
	} elseif ( '0' != $number_excerpt ) {
		$excerpt = wp_trim_words( get_the_excerpt(), $number_excerpt, '...' );
	}
	$excerpt = '<div class="wrsf-shdes"><p>' . $excerpt . '</p></div>';
}
?>
<figure class="wrstyle-3 tppost-<?php the_ID(); ?> <?php
if ( '0' != $number_excerpt ) {
	echo 'wrstyle-3-center';
}
?>
">
	<div class="wrstyle-3-image wrs-fly-cart" style="background-image: url(<?php echo esc_html( get_the_post_thumbnail_url( get_the_ID(), $img_size ) ); ?>)">
	<a class="wooresfd_modal_click" href="<?php echo esc_url( $customlink ); ?>"></a>
	<?php
	woo_restaurant_sale_badge();
	$prod    = wc_get_product( get_the_ID() );
	$cls_ost = '';
	$st_stt  = esc_html__( 'Order', 'woorestaurant' );
	if ( is_object( $prod ) && method_exists( $prod, 'get_stock_status' ) && 'outofstock' == $prod->get_stock_status() ) {
		$cls_ost = 'wrsf-ofstock';
		$st_stt  = esc_html__( 'Sold Out', 'woorestaurant' );
	}
	woore_icon_color();
	if ( 'yes' != $hide_atc ) {
		?>
		<div class="wrsbt-inline <?php echo esc_attr( $cls_ost ); ?>">
		<span href="<?php echo esc_url( $customlink ); ?>" class="wrstyle-3-button"><?php echo esc_attr( $st_stt ); ?></span>
		</div>
	<?php } ?>
	</div><figcaption>
	<h3><a class="wooresfd_modal_click" href="<?php echo esc_url( $customlink ); ?>"><?php the_title(); ?></a></h3>
	<h5>
		<?php echo wp_kses_post( $price ); ?>
	</h5>
	<?php
	do_action( 'woore_sc_after_shortdes' );
	echo wp_kses_post( $excerpt );
	?>
	<?php
	if ( 'yes' != $hide_atc ) {
		echo '<div class="wrs-hidden wrs-hdst3">';
		woo_restaurant_booking_button_html( 1 );
		echo '</div>';}
	?>
	</figcaption>
</figure>
