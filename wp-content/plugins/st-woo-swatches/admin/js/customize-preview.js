(function($){
	"use strict";
	/**
	 * Single Product Settings
	 */	
		// Swatch Size
		wp.customize( "st_swatch_product_single_swatch_size", function( value ){
			value.bind( function( to ){

				$("body.single-product form.variations_form ul.st-swatch-preview").removeClass( function( index, className ){
					return ( className.match(/\bst-swatch-size-\S+/g)||[] ).join(' ');
				}).addClass( to );
			});
		});

		// Swatch Shape
		wp.customize( "st_swatch_product_single_swatch_shape", function( value ){
			value.bind( function( to ){

				$("body.single-product form.variations_form ul.st-swatch-preview").removeClass( function( index, className ){
					return ( className.match(/\bst-swatch-shape-\S+/g)||[] ).join(' ');
				}).addClass( to );
			});
		});

		// Swatch Border Color
		wp.customize( "st_swatch_product_single_swatch_border_color", function( value ){
			value.bind(function( to ){

				if( to.length ) {

					$("body.single-product form.variations_form ul.st-swatch-preview li:not(.selected)").css({
						'border-color':to
					});

					$("body.single-product form.variations_form ul.st-swatch-preview li").attr("data-border-color", to );					
				}
			});
		});

		// Active Swatch Border Color
		wp.customize( "st_swatch_product_single_swatch_active_border_color", function( value ){
			value.bind(function( to ){

				if( to.length ) {

					$("body.single-product form.variations_form ul.st-swatch-preview li.selected").css({
						'border-color':to
					});

					$("body.single-product form.variations_form ul.st-swatch-preview li").attr("data-active-border-color", to );
				}
			});
		});

	/**
	 * Cart and Checkout Page
	 */
		// Swatch Size
		wp.customize( "st_swatch_cart_chkout_swatch_size", function( value ){
			value.bind( function( to ){

				$("body.st-swatch-apply-border span.st-swatch-preview").removeClass( function( index, className ){
					return ( className.match(/\bst-swatch-size-\S+/g)||[] ).join(' ');
				}).addClass( to );
			});		
		});

		// Swatch Shape
		wp.customize( "st_swatch_cart_chkout_swatch_shape", function( value ){
			value.bind( function( to ){

				$("body.st-swatch-apply-border span.st-swatch-preview").removeClass( function( index, className ){
					return ( className.match(/\bst-swatch-shape-\S+/g)||[] ).join(' ');
				}).addClass( to );
			});
		});

		// Swatch Border Color
		wp.customize( "st_swatch_cart_chkout_swatch_border_color", function( value ){
			value.bind(function( to ){

				if( to.length ) {

					$("body.st-swatch-apply-border span.st-swatch-preview").css({
						'border-color':to
					});
				}
			});
		});

	/**
	 * Shop Page
	 */
		// Swatch Size
		wp.customize( "st_swatch_shop_swatch_size", function( value ){
			value.bind( function( to ){

				$("body.post-type-archive-product ul.st-swatch-preview").removeClass( function( index, className ){
					return ( className.match(/\bst-swatch-size-\S+/g)||[] ).join(' ');
				}).addClass( to );
			});			
		});

		// Swatch Shape
		wp.customize( "st_swatch_shop_swatch_shape", function( value ){
			value.bind( function( to ){

				$("body.post-type-archive-product ul.st-swatch-preview").removeClass( function( index, className ){
					return ( className.match(/\bst-swatch-shape-\S+/g)||[] ).join(' ');
				}).addClass( to );
			});
		});

		// Swatch Border Color
		wp.customize( "st_swatch_shop_swatch_border_color", function( value ){
			value.bind(function( to ){

				if( to.length ) {

					$("body.post-type-archive-product ul.st-swatch-preview li:not(.selected)").css({
						'border-color':to
					});

					$("body.post-type-archive-product ul.st-swatch-preview li").attr("data-border-color", to );					
				}
			});
		});

		// Active Swatch Border Color
		wp.customize( "st_swatch_shop_swatch_active_border_color", function( value ){
			value.bind(function( to ){

				if( to.length ) {

					$("body.post-type-archive-product ul.st-swatch-preview li.selected").css({
						'border-color':to
					});

					$("body.post-type-archive-product ul.st-swatch-preview li").attr("data-active-border-color", to );
				}
			});
		});

	/**
	 * Archive Page
	 */
		// Swatch Size
		wp.customize( "st_swatch_archive_swatch_size", function( value ){
			value.bind( function( to ){

				$("body.tax-product_cat ul.st-swatch-preview").removeClass( function( index, className ){
					return ( className.match(/\bst-swatch-size-\S+/g)||[] ).join(' ');
				}).addClass( to );
			});			
		});

		// Swatch Shape
		wp.customize( "st_swatch_archive_swatch_shape", function( value ){
			value.bind( function( to ){

				$("body.tax-product_cat ul.st-swatch-preview").removeClass( function( index, className ){
					return ( className.match(/\bst-swatch-shape-\S+/g)||[] ).join(' ');
				}).addClass( to );
			});
		});

		// Swatch Border Color
		wp.customize( "st_swatch_archive_swatch_border_color", function( value ){
			value.bind(function( to ){

				if( to.length ) {

					$("body.tax-product_cat ul.st-swatch-preview li:not(.selected)").css({
						'border-color':to
					});

					$("body.tax-product_cat ul.st-swatch-preview li").attr("data-border-color", to );					
				}
			});
		});

		// Active Swatch Border Color
		wp.customize( "st_swatch_archive_swatch_active_border_color", function( value ){
			value.bind(function( to ){

				if( to.length ) {

					$("body.tax-product_cat ul.st-swatch-preview li.selected").css({
						'border-color':to
					});

					$("body.tax-product_cat ul.st-swatch-preview li").attr("data-active-border-color", to );
				}
			});
		});
})(jQuery);