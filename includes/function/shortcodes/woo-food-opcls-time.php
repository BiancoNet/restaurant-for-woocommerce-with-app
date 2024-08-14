<?php
/**
 * This is the food opcls time file.
 *
 * @package woorestaurant
 */

/**
 * This is the restraunt shortcode opcls time function.
 *
 * @param array $atts it is the attribute parameter.
 */
function woo_restaurant_shortcode_opcls_time( $atts ) {
	if ( phpversion() >= 7 ) {
		$atts = (array) $atts;
	}
	$img_url = isset( $atts['img_url'] ) && '' != $atts['img_url'] ? $atts['img_url'] : '';
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	$loc_opcls = woo_restaurant_get_option( 'woo_restaurant_open_close_loc', 'woo_restaurant_advanced_options' );
	ob_start();
	if ( ! woore_check_open_close_time() ) { ?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('body').on('click','.wrsf-opcls-info .wr_close',function(event) {
					jQuery(this).closest('.wrsf-opcls-info').remove();
					sessionStorage.setItem("woore_cls_ops", '1');
				});
				jQuery('body').on('click', '.wrsf-opcls-info', function (event) {
					if (event.target.className == 'wrsf-opcls-info wrs-popup-active') {
						jQuery('.wrsf-opcls-info').remove();
						sessionStorage.setItem("woore_cls_ops", '1');
					}
				});
				var woore_at_opcls = sessionStorage.getItem("woore_cls_ops");
				<?php if ( 'yes' == $loc_opcls ) { ?>
				if(woore_at_opcls !== '1' && !jQuery('.wrsf-order-method .wrsf-opcls-info.wrsf-odtype').length){
				<?php } else { ?>
				if(woore_at_opcls !== '1'){	
				<?php } ?>		
					jQuery('.wrsf-opcls-info').addClass('wrs-popup-active');
				}
			});
		</script>
		<div class="wrsf-opcls-info">
			<div class="wrsf-opcls-content">
				<span class="wr_close">×</span>
				<?php if ( '' != $img_url ) { ?>
					<div class="opcls-img"><img src="<?php echo esc_url( $img_url ); ?>"></div>
				<?php } ?>
				<div class="opcls-ct"><?php echo esc_attr( wrsfd_open_closing_message( true ) ); ?></div>	
			</div>
		</div>
		<?php
	}
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
}

add_shortcode( 'woores_view_opcls', 'woo_restaurant_shortcode_opcls_time' );
add_action( 'after_setup_theme', 'wr_reg_wf_opcls_vc' );
/**
 * Register OPCLS.
 */
function wr_reg_wf_opcls_vc() {
	if ( function_exists( 'vc_map' ) ) {
		vc_map(
			array(
				'name'     => esc_html__( 'Opening and Closing info', 'woorestaurant' ),
				'base'     => 'woores_view_opcls',
				'class'    => '',
				'icon'     => 'icon-grid',
				'controls' => 'full',
				'category' => esc_html__( 'Woocommerce Food', 'woorestaurant' ),
				'params'   => array(
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Image url', 'woorestaurant' ),
						'param_name'  => 'img_url',
						'value'       => '',
						'description' => esc_html__( 'Set url of image', 'woorestaurant' ),
					),
				),
			)
		);
	}
}
