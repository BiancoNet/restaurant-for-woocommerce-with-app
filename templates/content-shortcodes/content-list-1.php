<?php
/**
 * It is the content list 1 shortcode template.
 *
 * @package woorestaurant
 */

$customlink = Woo_restuaran_customlink( get_the_ID() );
global $number_excerpt, $img_size, $hide_atc;
$custom_price = get_post_meta( get_the_ID(), 'woo_restaurant_custom_price', true );
$price        = woo_restaurant_price_with_currency();
if ( '' != $custom_price ) {
	$price = $custom_price;
}
$class_add = '';
if ( ! has_excerpt( get_the_ID() ) ) {
	$class_add = ' wrs-no-description';
}
if ( '' == $img_size ) {
	$img_size = 'woo_restaurant_80x80';
}
// phpcs:ignore
$id      = get_the_ID();
$excerpt = '';
if ( has_excerpt( $id ) ) {
	if ( 'full' == $number_excerpt ) {
		$excerpt = get_the_excerpt();
	} elseif ( '0' != $number_excerpt ) {
		$excerpt = wp_trim_words( get_the_excerpt(), $number_excerpt, '...' );
	}
	$excerpt = '<p>' . $excerpt . '</p>';
}
?>
<figure class="fdstyle-list-1 <?php echo esc_attr( $class_add ); ?>">
	<a class="wooresfd_modal_click" href="<?php echo esc_url( $customlink ); ?>">
	<?php if ( has_post_thumbnail( get_the_ID() ) ) { ?>
		<div class="wrff-img">
		<?php the_post_thumbnail( $img_size ); ?>
		<?php woore_icon_color( get_the_ID() ); ?>
		</div>
	<?php } ?>
	</a>
	<div class="fdlist_1_detail">
	<div class="fdlist_1_title">
		<div class="fdlist_1_name wrsfd-list-name">
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
			
		</div>
		<div class="fdlist_1_price">
		<span>
		<?php echo wp_kses_post( $price ); ?>
		</span>
		
		</div>
	</div>
	</div>
	<div class="fdlist_1_des <?php echo esc_attr( $cls_ost ); ?>">
	<?php
	if ( has_excerpt( $id ) ) {
		?>
		<div class="wrsf-sdes"><?php echo wp_kses_post( $excerpt ); ?></div>
		<?php
	}
	do_action( 'woore_sc_after_shortdes' );
	if ( 'yes' != $hide_atc ) {
		echo '<div class="wrs-hidden">';
		woo_restaurant_booking_button_html( 1 );
		echo '</div>';
		?>
		<?php if ( woore_check_open_close_time( $id ) && 'disable' != woo_restaurant_get_option( 'woo_restaurant_booking' ) ) { ?>
		<button class="wooresfd_modal_click wrsfd-choice"><div class="wrsfd-icon-plus"></div></button>
			<?php
		}
	}
	?>
	</div>
</figure>
