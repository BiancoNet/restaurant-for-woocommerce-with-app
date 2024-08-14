/**
 * It is the search script file.
 *
 * @package woorestaurant
 */

(function () {
	"use strict";
	jQuery( document ).ready(
		function ($) {
			if ($( '.wrmb2-post-search-button' ).length || $( '.wrmb-type-post-search-text' ).length ) {
				var SearchView = window.Backbone.View.extend(
					{
						el         : '#find-posts',
						overlaySet : false,
						$overlay   : false,
						$idInput   : false,
						$checked   : false,

						events : {
							'keypress .find-box-search :input' : 'maybeStartSearch',
							'keyup #find-posts-input'  : 'escClose',
							'click #find-posts-submit' : 'selectPost',
							'click #find-posts-search' : 'send',
							'click #find-posts-close'  : 'close',
						},

						initialize: function () {
							this.$spinner  = this.$el.find( '.find-box-search .spinner' );
							this.$input    = this.$el.find( '#find-posts-input' );
							this.$response = this.$el.find( '#find-posts-response' );
							this.$overlay  = $( '.ui-find-overlay' );

							this.listenTo( this, 'open', this.open );
							this.listenTo( this, 'close', this.close );
						},

						escClose: function ( evt ) {
							if ( evt.which && 27 === evt.which ) {
								this.close();
							}
						},

						close: function () {
							this.$overlay.hide();
							this.$el.hide();
						},

						open: function () {
							this.$response.html( '' );

							// WP, why you so dumb? (why isn't text in its own dom node?).
							this.$el.show().find( '#find-posts-head' ).html( this.findtxt + '<div id="find-posts-close"></div>' );

							this.$input.focus();

							if ( ! this.$overlay.length ) {
								$( 'body' ).append( '<div class="ui-find-overlay"></div>' );
								this.$overlay = $( '.ui-find-overlay' );
							}

							this.$overlay.show();

							// Pull some results up by default.
							this.send();

							return false;
						},

						maybeStartSearch: function ( evt ) {
							if ( 13 == evt.which ) {
								this.send();
								return false;
							}
						},

						send: function () {

							var search = this;
							search.$spinner.addClass( 'is-active' );

							$.ajax(
								ajaxurl,
								{
									type     : 'POST',
									dataType : 'json',
									data     : {
										ps               : search.$input.val(),
										action           : 'find_posts',
										wrmb2_post_search : true,
										post_search_cpt  : search.posttype,
										_ajax_nonce      : $( '#find-posts #_ajax_nonce' ).val()
									}
								}
							).always(
								function () {

									search.$spinner.removeClass( 'is-active' );

								}
							).done(
								function ( response ) {

									if ( ! response.success ) {
											search.$response.text( search.errortxt );
									}

									var data = response.data;

									if ( 'checkbox' === search.selecttype ) {
										data = data.replace( /type="radio"/gi, 'type="checkbox"' );
									}

									search.$response.html( data );
									if (search.$idInput.val() != '') {
										var $ids = search.$idInput.val().replace( / /g,'' );
										$ids     = $ids.split( ',' );
										var i;
										var id_length = $ids.length;
										for (i = 0; i < id_length; ++i) {
											$( '.found-posts #found-' + $ids[i] ).prop( 'disabled', true );
										}
									}

								}
							).fail(
								function () {
									search.$response.text( search.errortxt );
								}
							);
						},

						selectPost: function ( evt ) {
							evt.preventDefault();

							this.$checked = $( '#find-posts-response input[type="' + this.selecttype + '"]:checked' );

							var checked = this.$checked.map(
								function () {
									return this.value; }
							).get();

							if ( ! checked.length ) {
								this.close();
								return;
							}

							this.handleSelected( checked );
						},

						handleSelected: function ( checked ) {
							checked = checked.join( ', ' );

							if ( 'add' === this.selectbehavior ) {
								var existing = this.$idInput.val();
								if ( existing ) {
									checked = existing + ', ' + checked;
								}
							}

							this.$idInput.val( checked ).trigger( 'change' );
							this.close();
						}

					}
				);

				window.wrmb2_post_search = new SearchView();

				window.wrmb2_post_search.closeSearch = function () {
					window.wrmb2_post_search.trigger( 'close' );
				};

				window.wrmb2_post_search.openSearch = function ( evt ) {
					var search = window.wrmb2_post_search;

					search.$idInput = $( evt.currentTarget ).parents( '.wrmb-type-post-search-text' ).find( '.wrmb-td input[type="text"]' );
					// Setup our variables from the field data.
					$.extend( search, search.$idInput.data( 'search' ) );

					search.trigger( 'open' );
				};

				window.wrmb2_post_search.addSearchButtons = function () {
					var $this = $( this );
					var data  = $this.data( 'search' );
					$this.after( '<div title="' + data.findtxt + '" class="dashicons dashicons-search wrmb2-post-search-button"></div>' );
				};

				$( '.wrmb-type-post-search-text .wrmb-td input[type="text"]' ).each( window.wrmb2_post_search.addSearchButtons );

				$( '.wrmb2-wrap' ).on( 'click', '.wrmb-type-post-search-text .wrmb-td .dashicons-search', window.wrmb2_post_search.openSearch );
				$( 'body' ).on( 'click', '.ui-find-overlay', window.wrmb2_post_search.closeSearch );
			}

		}
	);
})( jQuery );