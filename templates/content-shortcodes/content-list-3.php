<?php
/**
 * It is the content list 3 shortcode template.
 *
 * @package woorestaurant
 */

$customlink = Woo_restuaran_customlink( get_the_ID() );
global $number_excerpt, $img_size, $hide_atc;
if ( '' == $img_size ) {
	$img_size = 'woo_restaurant_80x80';
}
$custom_price = get_post_meta( get_the_ID(), 'woo_restaurant_custom_price', true );
$price        = woo_restaurant_price_with_currency();
if ( '' != $custom_price ) {
	$price = $custom_price;
}
?>
<figure class="fdstyle-list-3">
  
	<a class="wooresfd_modal_click" href="<?php echo esc_url( $customlink ); ?>">
		<?php if ( has_post_thumbnail( get_the_ID() ) ) { ?>
		<div class="wrff-img">
			<?php the_post_thumbnail( $img_size ); ?>
			<?php woore_icon_color( get_the_ID() ); ?>  
		</div>
		<?php } ?>
	</a>
	<div class="fdlist_3_title">
		<div class="fdlist_3_name wrsfd-list-name"><h3>
		<a class="wooresfd_modal_click" href="<?php echo esc_url( $customlink ); ?>">
			<?php the_title(); ?>
		</a>
		<?php
		$prod    = wc_get_product( get_the_ID() );
		$cls_ost = '';
		if ( is_object( $prod ) && method_exists( $prod, 'get_stock_status' ) && 'outofstock' == $prod->get_stock_status() ) {
			$cls_ost = 'wrsf-ofstock';
			echo '<span>' . esc_html__( ' - Sold Out', 'woorestaurant' ) . '</span>';
		}
		?>
		</h3></div>
	</div>
	<div class="fdlist_3_des">
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
			do_action( 'woore_sc_after_shortdes' );
		?>
	</div>
	<div class="fdlist_3_price">
		<span>
			<?php echo wp_kses_post( $price ); ?>
		</span>
	</div>
	<?php
	// phpcs:ignore
	$id = get_the_ID();
	if ( woore_check_open_close_time( $id ) && 'yes' != $hide_atc ) {
		?>
	<div class="fdlist_3_order <?php echo esc_attr( $cls_ost ); ?>">
		<?php
		echo '<div class="wrs-hidden">';
		woo_restaurant_booking_button_html( 1 );
		echo '</div>';
		?>
		<button class="wooresfd_modal_click wrsfd-choice" data="food_id=<?php echo esc_attr( get_the_ID() ); ?>&food_qty=1"><div class="wrsfd-icon-plus"></div></button>
	</div>
	<?php } ?>
</figure>
