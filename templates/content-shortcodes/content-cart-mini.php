<?php
/**
 * It is the content cart mini shortcode template.
 *
 * @package woorestaurant
 */

?>

<span class="wrr_cart_count_btn wrr_floating_cart_btn"><span class="wrr_cart_count" ><?php echo esc_attr( WC()->cart->get_cart_contents_count() ); ?> Items </span>
		<span class="wrr_cart_icon"><i class="icon ion-ios-cart-outline"></i></span>
	</span>
   

	<div class="wrr_cart_popup_modal" id="wrr_cart_popup_modal">
	<div class="wrr_cart_modal_wrap wrr_cart">
	<div class="wrr_cart_modal">
		<div class="wrr_cart_modal_inner">
		<span class="wrr_close_mini_cart_modal">
			<i class="icon ion-close-circled"></i>
		</span>

		<div class="wrr_cart_modal_content">
			<div class="wrr_cart_steps_wrapper">
			<div class="step-cart mini-cart-content">
	<h3>Your Cart: </h3>
	<div class="cart_table">
		<!-- Cart table Header -->
		<div class="cart_table_header wrr_product_details_form ">
			<span class="minicartbag"><i class="icon ion-bag"></i></span>
			<h4>
			My Orders:         	</h4>
		</div>
		<!-- End Cart table Header -->
		<div class="mini-cart-content-inner"> 
		<div class="wrsfd-cart-mini"><?php woocommerce_mini_cart(); ?></div>
		</div>
	</div>
</div>
			<div class="mini-cart-bottom-block">
				<?php do_action( 'woore_sidecart_after_content' ); ?>
				 
				<!--<a href="#" class="wrr_btn_fill wrr_mini_cart_checkout_btn">Check Out</a>-->
				<!--<a href="#" class="wrr_btn_fill back-cart" data-back="cart" style="display: none;">Back</a>-->
				<!--<a href="#" class="wrr_btn_fill fb-order-payment" style="display: none;">Order &amp; Payment</a>-->
			</div>
			</div>
		</div>
		<!-- End Modal Content -->
		</div>
	</div>
	</div>
	<!-- End Modal -->
</div>
<div class="wrsfd-overlay"></div>

<?php do_action( 'woore_sidecart_after_content' ); ?>