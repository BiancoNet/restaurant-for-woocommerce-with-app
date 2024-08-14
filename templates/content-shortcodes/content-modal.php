<?php
/**
 * It is the content modal shortcode template.
 *
 * @package woorestaurant
 */

global $atts, $id_food, $inline_bt, $param_shortcode;
$customlink = Woo_restuaran_customlink( $id_food );
global $number_excerpt;

$custom_price = get_post_meta( $id_food, 'woo_restaurant_custom_price', true );
$price        = woo_restaurant_price_with_currency( $id_food );
if ( '' != $custom_price ) {
	$price = $custom_price;
}

$protein = get_post_meta( $id_food, 'woo_restaurant_protein', true );
$calo    = get_post_meta( $id_food, 'woo_restaurant_calo', true );
$choles  = get_post_meta( $id_food, 'woo_restaurant_choles', true );
$fibel   = get_post_meta( $id_food, 'woo_restaurant_fibel', true );
$sodium  = get_post_meta( $id_food, 'woo_restaurant_sodium', true );
$carbo   = get_post_meta( $id_food, 'woo_restaurant_carbo', true );
$fat     = get_post_meta( $id_food, 'woo_restaurant_fat', true );
$gallery = get_post_meta( $id_food, '_product_image_gallery', true );

$custom_data               = get_post_meta( $id_food, 'woo_restaurant_custom_data_gr', true );
$woo_restaurant_enable_rtl = woo_restaurant_get_option( 'woo_restaurant_enable_rtl' );
$rtl_modal_mode            = ( 'yes' == $woo_restaurant_enable_rtl ) ? 'yes' : 'no';
if ( class_exists( 'WPBMap' ) && method_exists( 'WPBMap', 'addAllMappedShortcodes' ) ) {
	WPBMap::addAllMappedShortcodes();
	/**
	 * VC custom css method.
	 */
	function woore_vc_custom_css() {
		if ( $id_food ) {
			$shortcodes_custom_css = get_post_meta( $id_food, '_wpb_shortcodes_custom_css', true );
			if ( ! empty( $shortcodes_custom_css ) ) {
				echo '<style type="text/css" data-type="vc_shortcodes-custom-css-' . esc_attr( $id_food ) . '">';
				echo esc_attr( $shortcodes_custom_css );
				echo '</style>';
			}
		}
	}
}
$p_content = get_post_field( 'post_content', $id_food );
$content   = apply_filters( 'the_content', $p_content );
if ( '' != $p_content && '' == $content ) {
	$content = do_shortcode( wpautop( $p_content ) );
}
$cls_sli = '';
if ( '' == $gallery ) {
	$cls_sli = 'wr_s_lick-initialized wrsp-no-galle';
}
$close_popup = woo_restaurant_get_option( 'woo_restaurant_clsose_pop' );
?>
<!-- The Modal -->
<div class="wrr_popup_modal " <?php echo class_exists( 'WPCleverWoosb' ) ? 'id="woosq-popup"' : 'id="wrr_popup_modal"'; ?> >

	<div class="wrr_modal_wrap">
	<div class="wrr_modal">
		<div class="wrr_modal_inner">
		<span class="wrr_close_modal wrr_close_modal_btn wr_close">
			<i class="icon ion-close-circled"></i>
			
		</span>
		<div class="wrr_modal_content modal-content <?php echo '' == $gallery && ! has_post_thumbnail( $id_food ) ? ' wrsmd-no-img' : ''; ?>"  data-close-popup="<?php echo esc_attr( $close_popup ); ?>"id="product-<?php echo esc_attr( $id_food ); ?>">
			<div class="wrr_steps_content step-product-info">
				<div class="fd_modal_img">
			<div class="wrsfd-modal-carousel <?php echo esc_attr( $cls_sli ); ?>" rtl_mode="<?php echo esc_attr( $rtl_modal_mode ); ?>">
				<?php
				$hide_ftr = apply_filters( 'woore_hide_featured_img', false, $gallery );
				if ( true != $hide_ftr ) {
					?>
					<div><?php echo get_the_post_thumbnail( $id_food, 'full' ); ?></div>
					<?php
				}
				if ( '' != $gallery ) {
					$gallery = explode( ',', $gallery );
					foreach ( $gallery as $item ) {
						$item = wp_get_attachment_image_url( $item, 'full' );
						echo '<div><img src="' . esc_attr( $item ) . '" alt="' . esc_attr( get_the_title( $id_food ) ) . '"/></div>';
					}
				}
				?>
			</div>
			<?php woore_icon_color( $id_food ); ?>
			<?php do_action( 'woore_modal_after_image', $id_food ); ?>
		</div>
		<div class="fd_modal_des"> 
			<h3><?php echo esc_attr( get_the_title( $id_food ) ); ?></h3>
			<div class="wooresfd_nutrition">
				<ul>
				<?php if ( '' != $protein ) { ?>
						<li>
							<span><?php esc_html_e( 'Protein', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $protein ); ?>
						</li>
					<?php }if ( '' != $calo ) { ?>
						<li><span><?php esc_html_e( 'Calories', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $calo ); ?></li>
					<?php }if ( '' != $choles ) { ?>
						<li><span><?php esc_html_e( 'Cholesterol', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $choles ); ?></li>
					<?php }if ( '' != $fibel ) { ?>
						<li><span><?php esc_html_e( 'Dietary fibre', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $fibel ); ?></li>
					<?php }if ( '' != $sodium ) { ?>
						<li><span><?php esc_html_e( 'Sodium', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $sodium ); ?></li>
					<?php }if ( '' != $cawrro ) { ?>
						<li><span><?php esc_html_e( 'Cawrrohydrates', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $cawrro ); ?></li>
					<?php }if ( '' != $fat ) { ?>
						<li><span><?php esc_html_e( 'Fat total', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $fat ); ?></li>
						<?php
					}
					if ( '' != $custom_data ) {
						foreach ( $custom_data as $data_it ) {
							?>
							<li><span><?php echo wp_kses_post( $data_it['_name'] ); ?></span><?php echo wp_kses_post( $data_it['_value'] ); ?></li>
							<?php
						}
					}
					?>
					<div class="wooresfd_clearfix"></div>
				</ul>
				<?php echo esc_attr( wooresfd_show_reviews( $id_food ) ); ?>
			</div>
			<h5>
				<?php echo wp_kses_post( $price ); ?>
			</h5>
			<?php
			do_action( 'woore_modal_after_price', $id_food );
			?>
			<div class="wrsf-md-details wrsf-ct-tab wrsf-act-tab">
			<?php
			if ( '' != $content ) {
				?>
					<div class="woorst-ct"><?php echo wp_kses_post( $content ); ?></div>
				<?php
			}
				$inline_bt = 'yes';
				$hide_atc  = isset( $param_shortcode['hide_atc'] ) ? $param_shortcode['hide_atc'] : '';
			if ( 'yes' != $hide_atc ) {
				// phpcs:ignore
				echo ( woo_restaurant_add_to_cart_form_shortcode( $atts ) );
			}
			?>
			</div>
			<?php
			do_action( 'woore_modal_after_content', $id_food );
			if ( function_exists( 'woore_vc_custom_css' ) ) {
				woore_vc_custom_css();}
			?>
		</div>
		</div>  
			</div>
		<!-- End Modal Content -->
		</div>
	</div>
	</div>
	<!-- End Modal -->
</div>
