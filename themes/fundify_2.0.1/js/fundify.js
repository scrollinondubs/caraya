/**
 * Functionality specific to Fundify
 *
 * Provides helper functions to enhance the theme experience.
 */

var Fundify = {}

Fundify.App = (function($) {
	function fixedHeader() {
		fixHeader();

		$(window).scroll(function () {
			var y = $(window).scrollTop();    

			if ( y >= 400 ) {
				$( '#header' ).addClass( 'mini' );
			} else {
				$( '#header' ).removeClass( 'mini' );
			}
		});

		$(window).resize(function() {
			fixHeader();
		});

		function fixHeader() {
			var x = $(window).width();

			if ( ! $( 'body' ).hasClass( 'fixed-header' ) ) {
				$( 'body' ).css( 'padding-top', 0 );
			} else {
				$( 'body' ).css( 'padding-top', $( '#header' ).outerHeight() - 4 );
			}
		}
	}

	return {
		init : function() {
			fixedHeader();

			this.menuToggle();

			/*$( '.login a, .register a' ).click(function(e) {
				e.preventDefault();
				
				Fundify.App.fancyBox( $(this), {
					items: {
						'src'  : '#' + $(this).parent().attr( 'id' ) + '-wrap'
					}
				});
			});*/

			$( '.fancybox' ).click( function(e) {
				e.preventDefault();

				Fundify.App.fancyBox( $(this ), {
					items : {
						'src'  : '#' + $(this).attr( 'href' )
					}
				} );
			} );
		},

		/**
		 * Check if we are on a mobile device (or any size smaller than 980).
		 * Called once initially, and each time the page is resized.
		 */
		isMobile : function( width ) {
			var isMobile = false;

			var width = 1180;
			
			if ( $(window).width() <= width )
				isMobile = true;

			return isMobile;
		},

		fancyBox : function( _this, args ) {
			console.log(_this);
			$.magnificPopup.open( $.extend( args, {
				'type' : 'inline'
			}) );
		},

		menuToggle : function() {
			$( '.menu-toggle' ).click(function(e) {
				e.preventDefault();

				$( '#menu' ).slideToggle();
			});
		}
	}
}(jQuery));

Fundify.Campaign = (function($) {
	function campaignGrid() {
		if ( ! $().masonry )
			return;

		var container = $( '#projects section' );

		if ( container.masonry() )
			container.masonry( 'reload' );
		
		container.imagesLoaded( function() {
			container.masonry({
				itemSelector : '.hentry'
			});
		});
	}

	function campaignTabs() {
		var tabs     = $( '.campaign-tabs' ),
		    overview = $( '.campaign-view-descrption' ),
		    tablinks = $( '.sort-tabs.campaign a' );
		
		tabs.children( 'div' ).hide();
		overview.hide();

		tabs.find( ':first-child' ).show();

		tablinks.click(function(e) {
			if ( $(this).hasClass( 'tabber' ) ) {
				var link = $(this).attr( 'href' );
					
				tabs.children( 'div' ).hide();
				overview.show();
				tabs.find( link ).show();
				
				$( 'body' ).animate({
					scrollTop: $(link).offset().top - 200
				});
			}
		});
	}

	function campaignPledgeLevels() {
		$( '.single-reward-levels li' ).click( function(e) {
			e.preventDefault();

			if ( $( this ).hasClass( 'inactive' ) )
				return false;

			var price = $( this ).data( 'price' );

			Fundify.App.fancyBox( $(this), {
				items : {
					src  : '#contribute-modal-wrap'
				},
				callbacks: {
					beforeOpen : function() {
						$( '#contribute-modal-wrap .edd_price_options' )
							.find( 'li[data-price="' + price + '"]' )
							.trigger( 'click' );
					}
				}
			});
		} );
	}

	function campaignWidget() {
		$( 'body.campaign-widget a' ).attr( 'target', '_blank' );
	}

	return {
		init : function() {
			campaignGrid();
			campaignTabs();
			//campaignPledgeLevels();
			campaignWidget();
		},

		resizeGrid : function() {
			campaignGrid();
		}
	}
} )(jQuery);

Fundify.Checkout = (function($) {
	return function() {
		$( '.contribute, .contribute a' ).click(function(e) {
			e.preventDefault();

			Fundify.App.fancyBox( $(this), {
				items : {
					'src' : '#contribute-modal-wrap'
				}
			});
		});
	}
}(jQuery));

jQuery(document).ready(function($) {
	Fundify.App.init();
	Fundify.Campaign.init();
	//Fundify.Checkout();

	$( window ).on( 'resize', function() {
		Fundify.Campaign.resizeGrid();
	});
	
	/**
	 * Repositions the window on jump-to-anchor to account for
	 * navbar height.
	 */
	var fundifyAdjustAnchor = function() {
		if ( window.location.hash )
			window.scrollBy( 0, -150 );
	};

	$( window ).on( 'hashchange', fundifyAdjustAnchor );
});