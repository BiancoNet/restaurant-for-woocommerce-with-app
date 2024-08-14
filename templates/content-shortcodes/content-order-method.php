<?php
/**
 * It is the content order method shortcode template.
 *
 * @package woorestaurant
 */

$ship_mode = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );

$method_ship = woo_restaurant_get_option( 'woo_restaurant_enable_method', 'woo_restaurant_shpping_options' );
$dine_in     = woo_restaurant_get_option( 'woo_restaurant_enable_dinein', 'woo_restaurant_shpping_options' );
$limit       = woo_restaurant_get_option( 'woo_restaurant_autocomplete_limit', 'woo_restaurant_shpping_options' );
$limit       = '' != str_replace( ' ', '', $limit ) ? json_encode( explode( ',', $limit ) ) : '';
if ( ( '' == $method_ship && 'yes' != $dine_in ) || ! woore_check_open_close_time() ) {
	return;
}
$class         = '';
$user_address  = WC()->session->get( '_user_deli_adress' );
$user_odmethod = WC()->session->get( '_user_order_method' );

global $location;
$loc_sl     = woorestaurant_loc_field_html( $location );
$api        = woo_restaurant_get_option( 'woo_restaurant_gg_api', 'woo_restaurant_shpping_options' );
$cls_method = woo_restaurant_get_option( 'woo_restaurant_cls_method', 'woo_restaurant_shpping_options' );
?>
<!--<div class="wrsf-order-method">-->
	<script type="text/javascript">
		jQuery(document).ready(function() {
			<?php if ( 'yes' == $cls_method && 'yes' != woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) { ?>
				jQuery('body').on('click','.wrsf-opcls-info .wrsf-method-ct .wr_close',function(event) {
					jQuery(this).closest('.wrsf-opcls-info').remove();
					sessionStorage.setItem("woore_cls_method", '1');
					if(jQuery('.wrs-popup-location').length){
						var $popup_loc = jQuery(".wrs-popup-location");
						$popup_loc.addClass("wrs-popup-active");
					}
				});
				jQuery('body').on('click', '.wrsf-opcls-info', function (event) {
					if (event.target.className == 'wrsf-opcls-info wrs-popup-active') {
						jQuery('.wrsf-opcls-info').remove();
						sessionStorage.setItem("woore_cls_method", '1');
					}
				});
				var woore_at_method = sessionStorage.getItem("woore_cls_method");
				if(woore_at_method !== '1'){      
					jQuery('.wrsf-opcls-info.wrsf-odtype').addClass('wrs-popup-active');
				}
				if(!jQuery('.wrsf-order-method .wrsf-opcls-info.wrsf-odtype.wrs-popup-active').length && jQuery('.wrs-popup-location').length){
					var $popup_loc = jQuery(".wrs-popup-location");
					$popup_loc.addClass("wrs-popup-active");
				}
			<?php } else { ?>
				jQuery('.wrsf-opcls-info.wrsf-odtype').addClass('wrs-popup-active');
			<?php } ?>
			jQuery('body').on('click', '.wrsf-button', function (event) {
				console.log('clicked button')
				var $method = 'delivery';

				if(jQuery(this).closest(".wrsf-method-ct").find('.wrsf-order-take.at-method').length){
					$method = 'takeaway';
				}else if(jQuery(this).closest(".wrsf-method-ct").find('.wrsf-order-dinein.at-method').length){
					$method = 'dinein';
				}
				jQuery('.wrsf-add-error').fadeOut();
				var $addr ='';
				var $cnt = 1;
				if($method != 'takeaway' && $method != 'dinein'){
					$addr = jQuery(this).closest(".wrsf-method-ct").find('[name=wrsf-user-address]').val();
					if($addr==''){ 
						jQuery('.wrsf-del-address .wrsf-add-error').fadeIn();
						$cnt = 0;
					}
				}
				var $loc = jQuery(this).closest(".wrsf-method-ct").find('.wrsf-del-log select').val();
				if(jQuery('.wrsf-del-log select.wrs-logreq').length && ($loc==null || $loc=='' )){ 
					jQuery('.wrsf-del-log .wrsf-add-error').fadeIn();
					$cnt = 0;
				}
				if($cnt == 0){ return;}
				jQuery('.wrsf-method-ct').addClass('wrs-loading');
				var ajax_url        = jQuery('.wrs-fdlist input[name=ajax_url]').val();
				var param = {
					action: 'woore_check_distance',
					address: $addr,
					log: $loc,
					method: $method,
				};
				var url_cr = window.location.href;
				if($method=='takeaway' || $method=='dinein'){
					if(jQuery('.wrsf-del-log select.wrs-logreq').length){ 
						if($loc!='' && $loc!=null){
							if (url_cr.indexOf("?") > -1){s}else{
								url_cr = url_cr+"?loc="+$loc+"&change-method="+$method;
							}
						}
					}else{
						if (url_cr.indexOf("?") > -1){
							url_cr = url_cr+"&change-method="+$method;
						}else{
							url_cr = url_cr+"?change-method="+$method;
						}
						
					}
					url_cr = url_cr.replace("change-address=1","");
					url_cr = url_cr.replace("change-address=1","");
					if(window.location.hash) {
						url_cr = url_cr.replace(location.hash , "" );
						}
					window.location = url_cr;
					return false;
				}
				jQuery.ajax({
					type: "post",
					url: ajax_url,
					dataType: 'json',
					data: (param),
					success: function(data){
						if(data != '0'){
							jQuery('.wrsf-method-ct').removeClass('wrs-loading');
							if(data.mes!=''){
								jQuery('.wrsf-del-address .wrsf-add-error').html(data.mes).fadeIn();
							}else{
								jQuery( document.body ).trigger( 'wc_fragment_refresh' );
								if(jQuery('.wrsf-del-log select.wrs-logreq').length){ 
									if($loc!='' && $loc!=null){
										if (url_cr.indexOf("?") > -1){
											url_cr = url_cr+"&loc="+$loc;
										}else{
											url_cr = url_cr+"?loc="+$loc;
										}
									}
								}else{
									
								}
								url_cr = url_cr.replace("change-address=1","");
								if(window.location.hash) {
									url_cr = url_cr.replace(location.hash , "" );
								}
								window.location = url_cr;
								return false;
							}
						}else{jQuery('#'+id_crsc+' .loadmore-wooresf').html('error');}
					}
				});
			});
			jQuery('body').on('click', '.wrsf-method-title > div', function (event) {
				jQuery('.wrsf-method-title > div').removeClass('at-method');
				jQuery(this).addClass('at-method');
				if(jQuery(this).hasClass('wrsf-order-take') || jQuery(this).hasClass('wrsf-order-dinein')){
					jQuery('.wrsf-del-address').fadeOut(); 
				}else{
					jQuery('.wrsf-del-address').fadeIn('fast');
				}
			});
			<?php if ( '' != $api ) { ?>
				jQuery('body').on('click', '#woore_geolo', function (event) {
					if (navigator.geolocation) {
						navigator.geolocation.getCurrentPosition(woore_showPosition);
					} else {
						alert("<?php esc_html_e( 'Geolocation is not supported by this browser.', 'woorestaurant' ); ?>");
					}
				});

				function geocodeLatLng(latitude,longitude) {
					const latlng = {
						lat: parseFloat(latitude),
						lng: parseFloat(longitude),
					};
					const geocoder = new google.maps.Geocoder();
					geocoder.geocode({ location: latlng }, (results, status) => {
						if (status === "OK") {
							if (results[0]) {
								var address = results[0].formatted_address;
								var postcode =  results[0].address_components[6] && results[0].address_components[6].long_name ?  results[0].address_components[6].long_name : '';
								<?php if ( 'postcode' != $ship_mode ) { ?>
									document.getElementById("wrsf-user-address").value = address;
								<?php } else { ?>
									document.getElementById("wrsf-user-postcode").value = postcode;
								<?php } ?>
							} else {
								window.alert("No results found");
							}
						} else {
							window.alert("Geocoder failed due to: " + status);
						}
					});
				}
				
				function woore_showPosition(position) {
					var lat = position.coords.latitude;
					var lang = position.coords.longitude;
					geocodeLatLng(lat,lang);
				}
			<?php } ?>
			jQuery('.wrsf-method-title > div').on("click", function($){
				if(jQuery('.wrs-ck-select.wrsfd-choice-locate').length){
					var $html_locs = jQuery('.wrsf-method-title').attr('data-all_locs');
					jQuery('.wrsf-method-content .wrsf-del-log div').empty().append($html_locs);
					var $disable_log = jQuery(this).attr('disable-locs');
					jQuery.each(JSON.parse($disable_log) , function(index, val) { 
						jQuery('.wrsf-method-content .wrsf-del-log div').find('select option[value='+val+']').remove();
					});            
				}
			});
		});
	</script> 

<div class="wrr__wrapper orderadmin_popup_modal wrr_delivery_availability_checker" id="orderadmin_popup_modal">
	<div class="wrr_popup_modal open" style="display: block">
		<div class="wrr_modal_wrap wrr_modal_location">
		<div class="wrr_modal">
			<div class="wrr_modal_inner">
				<?php if ( 'yes' == $cls_method ) { ?>
			<span class="wrr_close_modal sad">
				<i class="icon ion-close-circled"></i>
			</span>
			<?php } ?>
				<!-- End Close Modal -->
	<div class="wrr_modal_content fb-ability-checker-form-wrapper">
		
				<div class="wrr_multiform">
			<!-- Form Selector Group -->  
		<label for="wrr_delivery_type" class="wrr_input_label wrr_mb_0">
				Delivery Type<span class="fb-required">*</span>
			</label>        
		<ul class="wrr_list_unstyled wrr_form_selector_list wrr_mt_5">
			<?php
			if ( 'takeaway' != $method_ship && '' != $method_ship ) {
				?>
				<li class="wrr_single_form_selector" onclick="jQuery('.wrsf-order-deli').trigger('click');">
			<span class="wrr_custom_checkbox">
				<label>
				<input type="radio" value="delivery" class="shipping_method delivery-type-delivery" name="wrr_delivery_options" checked="checked">
				<span class="wrr_label_title"><?php esc_html_e( 'Delivery', 'woorestaurant' ); ?></span>
				<span class="wrr_custom_checkmark"></span>
				</label>
			</span>
			</li><?php } ?>
		<?php
		if ( 'delivery' != $method_ship && '' != $method_ship ) {
			?>
				<li class="wrr_single_form_selector" onclick="jQuery('.wrsf-order-take').trigger('click');">
			<span class="wrr_custom_checkbox">
				<label>
				<input type="radio" value="takeway" class="shipping_method delivery-type-pickup" name="wrr_delivery_options">
				<span class="wrr_label_title"><?php esc_html_e( 'Takeaway', 'woorestaurant' ); ?></span>
				<span class="wrr_custom_checkmark"></span>
				</label>
			</span>
			</li>  <?php } ?>
			<?php
			if ( 'yes' == $dine_in ) {
				?>
				<li class="wrr_single_form_selector" onclick="jQuery('.wrsf-order-dinein').trigger('click');">
			<span class="wrr_custom_checkbox">
				<label>
				<input type="radio" value="dinein" class="shipping_method delivery-type-pickup" name="wrr_delivery_options">
				<span class="wrr_label_title"> <?php esc_html_e( 'Dine-In', 'woorestaurant' ); ?></span>
				<span class="wrr_custom_checkmark"></span>
				</label>
			</span>
			</li>   <?php } ?>   </ul>
			<!-- End Form Selector Group -->
	</div>
	
	
		
	<div>
		
	<div class="<?php esc_attr( $class ); ?>">
		<input type="hidden" name="woore_auto_limit" id="woore_auto_limit" value="<?php echo esc_attr( $limit ); ?>">
		<div class="wrsf-method-ct wrsf-opcls-content">
			<?php if ( 'yes' == $cls_method && 'yes' != woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) { ?>
				<!--<span class="wr_close">×</span>-->
				<?php
			}
			$all_locs = woorestaurant_loc_field_html( '', true );
			?>
			<div class="wrsf-method-title" style="display:none;" data-all_locs="<?php echo esc_attr( $all_locs ); ?>">
				<?php
				if ( 'takeaway' != $method_ship && '' != $method_ship ) {
					$exclude_log = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_advanced_options' );
					?>
					<div class="wrsf-order-deli 
					<?php
					if ( ( 'takeaway' != $user_odmethod && 'dinein' != $user_odmethod ) || ( 'delivery' == $method_ship && 'yes' != $dine_in ) ) {
						?>
						at-method <?php } ?>" disable-locs="<?php echo esc_attr( is_array( $exclude_log ) && ! empty( $exclude_log ) ? str_replace( '\/', '/', json_encode( $exclude_log ) ) : '[]' ); ?>">
						<?php esc_html_e( 'Delivery', 'woorestaurant' ); ?>
					</div>
					<?php
				}
				if ( 'delivery' != $method_ship && '' != $method_ship ) {
					$exclude_log = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_adv_takeaway_options' );
					?>
					<div class="wrsf-order-take 
					<?php
					if ( 'takeaway' == $user_odmethod || ( 'takeaway' == $method_ship && '' == $user_odmethod ) ) {
						?>
						at-method <?php } ?>" disable-locs="<?php echo esc_attr( is_array( $exclude_log ) && ! empty( $exclude_log ) ? str_replace( '\/', '/', json_encode( $exclude_log ) ) : '[]' ); ?>">
						<?php esc_html_e( 'Takeaway', 'woorestaurant' ); ?>
					</div>
					<?php
				}
				if ( 'yes' == $dine_in ) {
					$exclude_log = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_adv_dinein_options' );
					?>
					<div class="wrsf-order-dinein 
					<?php
					if ( 'dinein' == $user_odmethod || '' == $method_ship ) {
						?>
						at-method <?php } ?>" disable-locs="<?php echo esc_attr( is_array( $exclude_log ) && ! empty( $exclude_log ) ? str_replace( '\/', '/', json_encode( $exclude_log ) ) : '[]' ); ?>">
						<?php esc_html_e( 'Dine-In', 'woorestaurant' ); ?>
					</div>
				<?php } ?>
			</div>
			<div class="wrsf-method-content">
				<?php if ( '' != $loc_sl ) { ?>
					<div class="wrsf-del-field wrsf-del-log">
						<span><?php esc_html_e( 'Ordering area', 'woorestaurant' ); ?></span>
						<div><?php echo esc_attr( $loc_sl ); ?></div>
						<p class="wrsf-add-error"><?php esc_html_e( 'Please choose area you want to order', 'woorestaurant' ); ?></p>
					</div>
					<?php
				}
				if ( 'takeaway' != $method_ship ) {
					?>
					<div class="wrsf-del-field wrsf-del-address" 
					<?php
					if ( 'takeaway' == $user_odmethod || 'dinein' == $user_odmethod ) {
						?>
						style="display: none;" <?php } ?>>
						<span><?php echo 'postcode' != $ship_mode ? esc_html__( 'Please type your address ', 'woorestaurant' ) : esc_html__( 'Please add your Postcode', 'woorestaurant' ); ?></span>
						<div class="">
							<input type="text" name="wrsf-user-address" class="wrr_input_style" id="<?php echo 'postcode' != $ship_mode ? 'wrsf-user-address' : 'wrsf-user-postcode'; ?>" placeholder="<?php echo 'postcode' != $ship_mode ? esc_html__( 'Enter a location ', 'woorestaurant' ) : esc_html__( 'Postcode', 'woorestaurant' ); ?>" value="<?php echo '' != $user_address ? esc_attr( $user_address ) : ''; ?>">
						</div>
						<?php if ( '' != $api ) { ?>
						<span class="wrsf-crlog"><a href="javascript:;" id="woore_geolo"><i class="ion-navigate"></i><?php esc_html_e( 'Or use my current location', 'woorestaurant' ); ?></a></span>
						<?php } ?>
						<p class="wrsf-add-error"><?php esc_html_e( 'Please add your address', 'woorestaurant' ); ?></p>
					</div>
				<?php } ?>
			</div>
			<div class="wrsf-method-bt">
				<span class="wrsf-button fb-availability-check wrr_btn_fill"><?php esc_html_e( 'Start my order', 'woorestaurant' ); ?></span>
			</div>
		</div>
	</div>
</div>
		
		
	</div>
	
 
		   
		</div>
		</div>
	</div>
	</div>
