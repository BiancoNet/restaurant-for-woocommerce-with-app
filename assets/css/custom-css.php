<?php
/**
 * It is the content cart mini shortcode template.
 *
 * @package woorestaurant
 */

/**
 * Restaurant Custom CSS method.
 */
function woo_restaurant_custom_css() {
	ob_start();
	$woo_restaurant_color = woo_restaurant_get_option( 'woo_restaurant_color' );

	$hex = str_replace( '#', '', $woo_restaurant_color );
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
	if ( '' != $woo_restaurant_color ) {

		?>#wrr_cart_popup_modal .wrr_close_mini_cart_modal .icon{ color:  <?php echo esc_attr( $woo_restaurant_color ); ?>;}
		.wrr_floating_cart_btn .wrr_cart_icon .icon{ color:  <?php echo esc_attr( $woo_restaurant_color ); ?>;}
		.wrr_custom_checkbox label .wrr_custom_checkmark:after {background-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;}
		#wrr_cart_popup_modal .minicartbag{ color:  <?php echo esc_attr( $woo_restaurant_color ); ?>;}
		.wrr_popup_modal .wrr_close_modal .icon {color: <?php echo esc_attr( $woo_restaurant_color ); ?>;}

		.wrsfd-ribbon > span {  background: <?php echo esc_attr( $woo_restaurant_color ); ?>; }
		.wrr_custom_checkbox label input:checked ~ .wrr_custom_checkmark { border-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;}
		.wrr_cart_count_btn .wrr_cart_count {background-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;}
		.wrsfd-cart-mini span.woocommerce-Price-amount {color: <?php echo esc_attr( $woo_restaurant_color ); ?>;}
	
		.wrsf-user-dl-info {  border-color: <?php echo esc_attr( $woo_restaurant_color ); ?>; }
		p.woocommerce-mini-cart__buttons > * {background-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;}
		.wrs-fdlist .wrstyle-1 figcaption .wrstyle-1-button,
		.wrs-fdlist[id^=ex] .woorst-woocommerce.woocommerce form.cart button[type="submit"],
		.woorst-woocommerce.woocommerce form.cart button[type="submit"],
		.woorst-woocommerce.woocommerce .cart:not(.grouped_form) .quantity input[type=button],
		.wrs-fdlist .wrstyle-2 figcaption .wrstyle-2-button,
		.wrs-fdlist .wrstyle-3 figcaption .wrstyle-3-button,
		.wrs-fdlist .wrstyle-4 figcaption h5,
		.wrs-fdlist .wrstyle-4 .wrsfd-icon-plus:before,
		.wrs-fdlist .wrstyle-4 .wrsfd-icon-plus:after,
		.wrsfd-table-1 .wrs-fd-table-order .wrsfd-icon-plus:before,
		.wrsfd-table-1 .wrs-fd-table-order .wrsfd-icon-plus:after,
		.wrs-loadmore .loadmore-wooresf:hover,
		.wrsfd-cart-content .wrsfd-close-cart,
		.wrsfd-cart-content .woocommerce-mini-cart__buttons a,
		.wrs-fdlist .wrstyle-4 figcaption .wrsbt-inline .wrstyle-4-button,
		.wr_close,
		.wrsf-lbicon,
		.wrsf-tip-form input[name=wrsf-remove-tip],
		.wrsf-tip-form input[name=wrsf-add-tip],
		.wrsf-tip-form input[name=wrsf-remove-tip]:focus,.wrsf-tip-form input[name=wrsf-add-tip]:focus,
		.wrs-fdlist.category_left .wrsfd-filter .wrs-menu-list .wrs-active-left:after{background:<?php echo esc_attr( $woo_restaurant_color ); ?>;}
		.wrs-fdlist .wrsfd-filter .wrsfd-filter-group .wrs-menu-list a ul li:hover,
		.wrs-fdlist .wrsfd-filter .wrsfd-filter-group .wrs-menu-list .wrs-menu-item-active{
			background:<?php echo esc_attr( $woo_restaurant_color ); ?>;
			border-color:<?php echo esc_attr( $woo_restaurant_color ); ?>;
		}
		.wrs-fdlist .wrsfd-filter .wrsfd-filter-group .wrs-menu-list .wrs-menu-item-active:not(.wrsfd-child-click):after,
		.wrs-fdlist .wrsfd-filter .wrsfd-filter-group .wrs-menu-list .wrs-menu-item-active:after,
		.wrs-fdlist .wrstyle-4 figcaption{
			border-top-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;
		}
		.fdstyle-list-1 .fdlist_1_des button,
		.fdstyle-list-2 .fdlist_2_title .fdlist_2_price button,
		.fdstyle-list-3 .fdlist_3_order button,
		.wrs-fdlist .wrstyle-4 figcaption .wrstyle-4-button.wrsfd-choice,
		.wrsf-method-ct .wrsf-method-title .at-method,
		.wrsf-search .wrsf-s-field,
		.wrsfd-cart-mini .wrsf-quantity .wrsf-con-quantity,
		.wrsf-mngroup .mnheading-1 .mn-namegroup span:after, .wrsf-mngroup .mnheading-2 .mn-namegroup span:after,
		.wrsf-mngroup .wrsf-mnheading.mnheading-4 h2 > span,
		.wrsfd-table-1 .wrs-fd-table-order button{
			border-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;
		}
		.wrs-fdlist.style-4 .item-grid{
			border-bottom-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;
		}
		.woorst-mulit-steps >div.active:after {
			border-left-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;
		}
		.wrs-fdlist .wrstyle-1 figcaption h5,
		.wrs-fdlist .wrstyle-2 figcaption h5,
		.wrs-fdlist .wrstyle-3 figcaption h5,
		.wrsfd-table-1 td.wrs-fd-name h3 a,
		.fdstyle-list-1 .fdlist_1_title .fdlist_1_price,
		.wrs-fdlist .wrs-popup-location .wrs-popup-content .wrs-popup-info h1,
		.wrs-fdlist.category_left .wrsfd-filter .wrs-menu-list a:hover,
		.wrs-fdlist.category_left .wrsfd-filter .wrs-menu-list .wrs-active-left,
		.wrs-fdlist.wrs-fdcarousel .wr_s_lick-dots li.wr_s_lick-active button:before,
		.wrsfd-admin-review > span > i.icon,
		.wr_modal .modal-content .wr_s_lick-dots li button:before,
		.wrs-fdlist .wrsfd-filter.wrsf-fticon-style .wrsfd-filter-group .wrs-menu-list .wrs-menu-item-active:not(li),
		.wrsf-method-ct .wrsf-method-title .at-method,
		.wrs-fdlist.wrs-fdcarousel .wr_s_lick-dots li button:before{
			color: <?php echo esc_attr( $woo_restaurant_color ); ?>;
		}
		.wrsf-cksp-method.wrsf-method-ct .wrsf-method-title .at-method,
		.wrsfd-pagination .page-navi .page-numbers.current {
			background-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;
			border-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;
		}
		.wrs-loadmore .loadmore-wooresf{
			border-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;
			color: <?php echo esc_attr( $woo_restaurant_color ); ?>;
		}
		.wrsf-button,
		.wrs-loadmore .loadmore-wooresf span:not(.load-text),
		.wrs-fdlist .wrsfd-shopping-cart,
		.fdstyle-list-1 .wrsfd-icon-plus:before,
		.fdstyle-list-1 .wrsfd-icon-plus:after,
		.fdstyle-list-2 .wrsfd-icon-plus:before,
		.fdstyle-list-3 .wrsfd-icon-plus:before,
		.fdstyle-list-2 .wrsfd-icon-plus:after,
		.fdstyle-list-3 .wrsfd-icon-plus:after,
		.wrsfd-cart-mini .wrsf-quantity .wrsf-con-quantity > input,
		.wrsfd-table-1 th{
			background-color: <?php echo esc_attr( $woo_restaurant_color ); ?>;
		}
		@media screen and (max-width: 768px){

		}
		@media screen and (max-width: 992px) and (min-width: 769px){

		}
		<?php
	}
	$woo_restaurant_font_family = woo_restaurant_get_option( 'woo_restaurant_font_family' );
	$main_font_family           = explode( ':', $woo_restaurant_font_family );
	$main_font_family           = $main_font_family[0];
	if ( '' != $woo_restaurant_font_family ) {
		?>
		.wrs-fdlist,.wrsf-mngroup{font-family: "<?php echo esc_html( $main_font_family ); ?>", sans-serif;}
		<?php
	}
	$woo_restaurant_font_size = woo_restaurant_get_option( 'woo_restaurant_font_size' );
	if ( '' != $woo_restaurant_font_size ) {
		?>
		.wrs-fdlist,.wrsf-mngroup{font-size: <?php echo esc_html( $woo_restaurant_font_size ); ?>;}
		<?php
	}
	$woo_restaurant_ctcolor = woo_restaurant_get_option( 'woo_restaurant_ctcolor' );
	if ( '' != $woo_restaurant_ctcolor ) {
		?>
		.wrs-fdlist,
		.wrsfd-table-1 td,.wrsf-mngroup{color: <?php echo esc_html( $woo_restaurant_ctcolor ); ?>;}
		<?php
	}

	$woo_restaurant_headingfont_family = woo_restaurant_get_option( 'woo_restaurant_headingfont_family' );
	$h_font_family                     = explode( ':', $woo_restaurant_headingfont_family );
	$h_font_family                     = $h_font_family[0];
	if ( '' != $h_font_family ) {
		?>
	.wrr_cart h3 {font-family: "<?php echo esc_html( $h_font_family ); ?>", sans-serif;}
	.cart_table .cart_table_header h4 {font-family: "<?php echo esc_html( $h_font_family ); ?>", sans-serif;}
		.wrs-fdlist .wrstyle-1 h3 a,
		.wrs-fdlist .wrstyle-2 h3 a,
		.wrs-fdlist .wrstyle-3 h3 a,
		.wrs-fdlist .wrstyle-4 h3 a,
		.wrsf-mngroup .mn-namegroup span,
		.wrs-popup-location .wrs-popup-content .wrs-popup-info h1,
		.wrsfd-table-1 td.wrs-fd-name h3 a,
		.fdstyle-list-1 .fdlist_1_title .fdlist_1_name,
		.fdstyle-list-2 .fdlist_2_title .fdlist_2_name,
		.fdstyle-list-3 .fdlist_3_title h3,
		.wr_modal .modal-content .fd_modal_des h3,
		.wrs-fdlist .wrsfd-filter .wrsfd-filter-group .wrs-menu-list a,
		.wrs-fdlist .wrsfd-filter .wrsfd-filter-group .wrs-menu-select,
		.wrs-fdlist .wrsfd-filter .wrsfd-filter-group .wrs-menu-select select{
			font-family: "<?php echo esc_html( $h_font_family ); ?>", sans-serif;
		}
		<?php
	}
	$woo_restaurant_headingfont_size = woo_restaurant_get_option( 'woo_restaurant_headingfont_size' );
	if ( '' != $woo_restaurant_headingfont_size ) {
		?>
		.wrs-fdlist .wrstyle-1 h3 a,
		.wrs-fdlist .wrstyle-2 h3 a,
		.wrs-fdlist .wrstyle-3 h3 a,
		.wrs-fdlist .wrstyle-4 h3 a,
		.woorst-thankyou h3,
		.wrs-popup-location .wrs-popup-content .wrs-popup-info h1,
		.wrsfd-table-1 td.wrs-fd-name h3 a,
		.fdstyle-list-1 .fdlist_1_title .fdlist_1_name,
		.fdstyle-list-2 .fdlist_2_title .fdlist_2_name,
		.fdstyle-list-3 .fdlist_3_title h3,
		.wrs-fdlist .wrsfd-filter .wrsfd-filter-group .wrs-menu-list a,
		.wrs-fdlist .wrsfd-filter .wrsfd-filter-group .wrs-menu-select select{font-size: <?php echo esc_html( $woo_restaurant_headingfont_size ); ?>;}
		<?php
	}
	$woo_restaurant_hdcolor = woo_restaurant_get_option( 'woo_restaurant_hdcolor' );
	if ( '' != $woo_restaurant_hdcolor ) {
		?>
		.wrs-fdlist .wrstyle-1 h3 a,
		.wrs-fdlist .wrstyle-2 h3 a,
		.wrs-fdlist .wrstyle-4 h3 a,
		.wrs-popup-location .wrs-popup-content .wrs-popup-info h1,
		.wrs-fdlist .wrsfd-filter .wrsfd-filter-group .wrs-menu-list a,
		.wr_modal .modal-content .fd_modal_des h3,
		.fdstyle-list-1 .fdlist_1_title .fdlist_1_name,
		.fdstyle-list-2 .fdlist_2_title .fdlist_2_name,
		.fdstyle-list-3 .fdlist_3_title h3,
		.wrsfd-table-1 td.wrs-fd-name h3 a,
		.wrs-fdlist .wrsfd-filter .wrsfd-filter-group .wrs-menu-select select{color: <?php echo esc_html( $woo_restaurant_hdcolor ); ?>;}
		<?php
	}
	// price font.
	$woo_restaurant_pricefont_family = woo_restaurant_get_option( 'woo_restaurant_pricefont_family' );
	$price_font_family               = explode( ':', $woo_restaurant_pricefont_family );
	$price_font_family               = $price_font_family[0];
	if ( '' != $price_font_family ) {
		?>
		.wrs-fdlist .wrstyle-1 figcaption h5,
		.wrs-fdlist .wrstyle-2 figcaption h5,
		.wrs-fdlist .wrstyle-3 figcaption h5,
		.wrs-fdlist .wrstyle-4 figcaption h5,
		.wrsfd-table-1 td .wrsfd-price-detail,
		.fdstyle-list-1 .fdlist_1_title .fdlist_1_price,
		.fdstyle-list-2 .fdlist_2_title .fdlist_2_price,
		.wr_modal .modal-content .fd_modal_des h5{
			font-family: "<?php echo esc_html( $price_font_family ); ?>", sans-serif;
		}
		.wrsfd-cart-mini span.woocommerce-Price-amount {
			font-family: "<?php echo esc_html( $price_font_family ); ?>", sans-serif;}
		<?php
	}
	$woo_restaurant_pricefont_size = woo_restaurant_get_option( 'woo_restaurant_pricefont_size' );
	if ( '' != $woo_restaurant_pricefont_size ) {
		?>
		.wrs-fdlist .wrstyle-1 figcaption h5,
		.wrs-fdlist .wrstyle-2 figcaption h5,
		.wrs-fdlist .wrstyle-3 figcaption h5,
		.wrs-fdlist .wrstyle-4 figcaption h5,
		.wrsfd-table-1 td .wrsfd-price-detail,
		.fdstyle-list-1 .fdlist_1_title .fdlist_1_price,
		.fdstyle-list-2 .fdlist_2_title .fdlist_2_price,
		.wr_modal .modal-content .fd_modal_des h5{font-size: <?php echo esc_html( $woo_restaurant_pricefont_size ); ?>;}
		<?php
	}
	$woo_restaurant_pricecolor = woo_restaurant_get_option( 'woo_restaurant_pricecolor' );
	if ( '' != $woo_restaurant_pricecolor ) {
		?>
		.wrs-fdlist .wrstyle-1 figcaption h5,
		.wrs-fdlist .wrstyle-2 figcaption h5,
		.wrs-fdlist .wrstyle-3 figcaption h5,
		.wrs-fdlist .wrstyle-4 figcaption h5,
		.wrsfd-table-1 td .wrsfd-price-detail,
		.fdstyle-list-1 .fdlist_1_title .fdlist_1_price,
		.fdstyle-list-2 .fdlist_2_title .fdlist_2_price,
		.wr_modal .modal-content .fd_modal_des h5{color: <?php echo esc_html( $woo_restaurant_pricecolor ); ?>;}
		<?php
	}
	// end price font.

	$woo_restaurant_metafont_family = woo_restaurant_get_option( 'woo_restaurant_metafont_family' );
	$m_font_family                  = explode( ':', $woo_restaurant_metafont_family );
	$m_font_family                  = $m_font_family[0];
	if ( '' != $m_font_family ) {
		?>
		.wr_modal .modal-content .fd_modal_des .wooresfd_nutrition li{
			font-family: "<?php echo esc_html( $m_font_family ); ?>", sans-serif;
		}
		<?php
	}
	$woo_restaurant_metafont_size = woo_restaurant_get_option( 'woo_restaurant_metafont_size' );
	if ( '' != $woo_restaurant_metafont_size ) {
		?>
		.wr_modal .modal-content .fd_modal_des .wooresfd_nutrition li{font-size: <?php echo esc_html( $woo_restaurant_metafont_size ); ?>;}
		<?php
	}
	$woo_restaurant_mtcolor = woo_restaurant_get_option( 'woo_restaurant_mtcolor' );
	if ( '' != $woo_restaurant_mtcolor ) {
		?>
		.wr_modal .modal-content .fd_modal_des .wooresfd_nutrition li{color: <?php echo esc_html( $woo_restaurant_mtcolor ); ?>;}
		<?php
	}
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
}
