<?php
/**
 * It is the content grid 2 shortcode template.
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
<figure class="wrstyle-2 tppost-<?php the_ID(); ?>">
	<div class="wrstyle-2-image">
	<a class="wooresfd_modal_click" href="<?php echo esc_url( $customlink ); ?>">
		<?php
		echo get_the_post_thumbnail( get_the_ID(), $img_size );
		woore_icon_color();
		?>
	</a>
	<?php woo_restaurant_sale_badge(); ?>
	</div><figcaption>
	<h3><a class="wooresfd_modal_click" href="<?php echo esc_url( $customlink ); ?>"><?php the_title(); ?></a></h3>
	<h5>
		<span><?php echo wp_kses_post( $price ); ?></span>
	</h5>
	<?php
	if ( has_excerpt( get_the_ID() ) ) {
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
		do_action( 'woore_sc_after_shortdes' );
		woo_restaurant_booking_button_html( 2, $hide_atc );
	?>
	
	</figcaption>
</figure>
