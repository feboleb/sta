var $              = jQuery.noConflict(),
ST_WC_FRONT_SWATCH = ST_WC_FRONT_SWATCH || {};

(function($){
	"use strict";

	ST_WC_FRONT_SWATCH.onSelect = function() {

		$("body.single-product").on("click", "form.variations_form  ul.st-swatch-preview span.st-custom-attribute", function(){

			var $attribute = $(this).closest("[data-attribute]").data("attribute"),
				$select    = document.getElementById( $attribute ), // $("select#"+$attribute ), - it has issues in other languages ( jquery selector doesn't accepts special chars )
				$select    = $( $select ),
				$name      = $(this).data("name"),
				$value 	   = $(this).data("value"),
				$li		   = $(this).parent("li");

			if( $li.hasClass("selected") ) {

				$li.removeClass("selected");
				$select.val(" ");

				if( sten_wc_params.is_singular_product == 1 && sten_wc_params.is_customize_preview ) {

					$li.css({
						'border-color':$li.data("border-color")
					});					
				}

			} else {

				$li.addClass('selected').siblings().removeClass("selected");
				$select.val( $value );

				if( sten_wc_params.is_singular_product == 1 && sten_wc_params.is_customize_preview ) {

					$li.removeAttr("style");
					$li.siblings().css({
						'border-color':$li.data("border-color")
					});
					$li.css({
						'border-color':$li.data("active-border-color")
					});
				}				
			}

			$select.trigger("change");
		});
	},

	ST_WC_FRONT_SWATCH.onClear = function(){

		$("body.single-product").on("click", "a.reset_variations", function(){

			var $selected = $(this).closest('form.variations_form').find("ul.st-swatch-preview li.selected");

			$selected.removeClass( 'selected' );

			if( sten_wc_params.is_singular_product == 1 && sten_wc_params.is_customize_preview ) {

				$selected.css({
					'border-color':$selected.data("border-color")
				});
			}
		});
	},

	ST_WC_FRONT_SWATCH.onSelectUpdateAttribute = function() {

		$("body.single-product form.variations_form").on('woocommerce_update_variation_values', function(){
			$( this ).find("ul.st-swatch-preview").each(function(){
				var	$this      = this,
					$attribute = $($this).data("attribute"),
					$select    = document.getElementById( $attribute ), // $("select#"+$attribute ), - it has issues in other languages ( jquery selector doesn't accepts special chars )
					$options   = $($select).find("option"),
					$eq   	   = $($select).find("option").eq(1),
					$li 	   = $($this).find("li"),
					$current   = $($select).find("option:selected"),
					$selects   = [],
					$selected  = '';

				$options.each(function(){
                    if ($(this).val() !== '') {
                    	$selects.push( $(this).val() );
                        $selected = $current ? $current.val() : $eq.val();
                    }
				});

				$li.each(function(){
					var $this = this,
						$value = $($this).find("span.st-custom-attribute").attr("data-value");

					$($this).removeClass("selected sten-li-disabled").addClass("sten-li-disabled");

					if( $selects.indexOf( $value ) !== -1 ) {

						$($this).removeClass("sten-li-disabled");
						if( $value == $selected ) {
							$($this).addClass("selected");
						}
					}
				});
			});		
		});
	},

	ST_WC_FRONT_SWATCH.productLoop = {

		init : function() {

			ST_WC_FRONT_SWATCH.productLoop.itemSwatches();
			ST_WC_FRONT_SWATCH.productLoop.addToCart();
		},

		itemSwatches: function() {

			$("div.st-swatch-in-loop").each(function(){

				var	$swatches   = $(this),
					$terms      = $swatches.find("span.st-custom-attribute:not(.sten-disabled)"),
					$reset_btn  = $swatches.find("a.sten-reset-loop-variation"),
					$product    = $swatches.closest(".product-type-variable"),
					$variations = $.parseJSON( $swatches.attr("data-product_variations") ),
					$uls 		= $swatches.find("ul.has-default-attribute");

				// enable variation default attribute by default in product loop
				$uls.each( function(){

					var $ul           = $(this),
						$ul_reset_btn = $ul.parent("div.st-swatch-in-loop").find("a.sten-reset-loop-variation");

					$ul_reset_btn.show();

					var	$attributes         = ST_WC_FRONT_SWATCH.productLoop.getChosenAttributes( $swatches ),
						$current_attributes = $attributes.data;

					if( $attributes.count === $attributes.chosenCount ) {

						ST_WC_FRONT_SWATCH.productLoop.updateAttributes( $swatches, $variations );

						var $matching_variations = ST_WC_FRONT_SWATCH.productLoop.findMatchingVariations( $variations, $current_attributes ),
							$variation = $matching_variations.shift();

						if( $variation ) {

							ST_WC_FRONT_SWATCH.productLoop.foundVariation( $product, $swatches, $variation );
						} else {

							$ul_reset_btn.trigger( 'click' );							
						}
					} else {

						ST_WC_FRONT_SWATCH.productLoop.updateAttributes( $swatches, $variations );
					}
				});

				// swatch click
				$terms.on( "click", function(){

					var $term = $(this),
						$li = $term.parent("li");

					if( $term.hasClass('sten-disabled') ) {
						return false;
					}

					$product.find("span.st-custom-attribute").removeClass("sten-disabled sten-enabled");
					$product.find("span.st-custom-attribute").parent("li").removeClass("sten-li-disabled sten-li-enabled");

					if( $li.hasClass("selected") && sten_wc_params.is_customize_preview ) {

						$li.css({
							'border-color':$li.data("border-color")
						});
					}

					if( !$li.hasClass("selected") ) {

						$li.addClass("selected").siblings().removeClass("selected");

						if( sten_wc_params.is_customize_preview ) {

							$li.removeAttr("style");
							$li.siblings().css({
								'border-color':$li.data("border-color")
							});
							$li.css({
								'border-color':$li.data("active-border-color")
							});
						}

						$reset_btn.show();
					}

					var	$attributes         = ST_WC_FRONT_SWATCH.productLoop.getChosenAttributes( $swatches ),
						$current_attributes = $attributes.data;						

					if( $attributes.count === $attributes.chosenCount ) {

						ST_WC_FRONT_SWATCH.productLoop.updateAttributes( $swatches, $variations );

						var $matching_variations = ST_WC_FRONT_SWATCH.productLoop.findMatchingVariations( $variations, $current_attributes ),
							$variation = $matching_variations.shift();

						if( $variation ) {

							ST_WC_FRONT_SWATCH.productLoop.foundVariation( $product, $swatches, $variation );
						} else {

							$reset_btn.trigger( 'click' );							
						}
					} else {

						ST_WC_FRONT_SWATCH.productLoop.updateAttributes( $swatches, $variations );
					}
				});

				// reset click
				$reset_btn.on( "click", function(){

					$swatches.removeAttr( 'data-variation_id' );

					if( sten_wc_params.is_customize_preview ) {

						var $sel = $swatches.find("ul.st-swatch-preview li.selected");
						$sel.css({
							'border-color':$sel.data("border-color")
						});
					}

					$swatches.find("ul.st-swatch-preview li").removeClass("selected sten-li-disabled sten-li-enabled");
					$swatches.find("ul.st-swatch-preview span").removeClass("sten-disabled sten-enabled");

					$product.find("a.add_to_cart_button")
						.removeClass("st-swatch-btn-ready st-swatch-btn-readmore st-swatch-btn-text-changed added loading")
						.text( sten_wc_params.select_options_btn_text );
					$product.find("a.added_to_cart").remove();

					var	$price        = $product.find("span.price").not( '.price-cloned' ),
						$price_cloned = $product.find( '.price-cloned' );

					if ( $price_cloned.length ) {

						$price.html( $price_cloned.html() );
						$price_cloned.remove();
					}


					ST_WC_FRONT_SWATCH.productLoop.variationsImageUpdate( $product, false );

					$(this).hide();
				});
			});			
		},

		getChosenAttributes: function ( $swatches ) {

			var	$data   = {},
				$count  = 0,
				$chosen = 0,
				$swatch = $swatches.find("ul.st-swatch-preview");

			$swatch.each(function(){

				var $attr_name = 'attribute_' + $(this).data("attribute"),
					$value     = $(this).find("li.selected span.st-custom-attribute").attr("data-value") || '';

                if ( $value.length > 0 ) {

                	$chosen++;
                }

                $count++;
                $data[ $attr_name ] = $value;
			});

			return {
				'count': $count,
				'chosenCount': $chosen,
				'data': $data,
            };
		},

		updateAttributes: function( $swatches, $variations ) {

			var	$attributes              = ST_WC_FRONT_SWATCH.productLoop.getChosenAttributes( $swatches ),
				$current_attributes      = $attributes.data,
				$available_options_count = 0,
				$swatch                  = $swatches.find( 'ul.st-swatch-preview');

			$swatch.each(function( $idx, $ele ){

				var	$current_attribute_swatch = $( $ele ),
					$current_attribute_name   = "attribute_" + $current_attribute_swatch.data("attribute"),
					$selected_attr_val        = $current_attribute_swatch.find("li.selected span.st-custom-attribute").data("value"),
					$selected_attr_val_valid  = true,
					$check_attributes         = $.extend( true, {}, $current_attributes );

				$check_attributes[ $current_attribute_name ] = '';

				var $matching_variations = ST_WC_FRONT_SWATCH.productLoop.findMatchingVariations( $variations, $check_attributes );

				// Loop through matching variations
				for( var $num in $matching_variations ) {

                    if ( typeof $matching_variations[ $num ] !== 'undefined' ) {

                        var $variation_attributes = $matching_variations[ $num ].attributes;

                        for( var $attribute_name in $variation_attributes ) {

                        	if( $variation_attributes.hasOwnProperty( $attribute_name ) ) {

								var	$attribute_val    = $variation_attributes[ $attribute_name ],
									$variation_active = '';

                                if ( $attribute_name === $current_attribute_name ) {

                                    if ( $matching_variations[ $num ].variation_is_active ) {

                                    	$variation_active = 'sten-enabled';
                                    }

                                    if( $attribute_val ) {

                                    	$current_attribute_swatch.find('span.st-custom-attribute[data-value="' + $attribute_val + '"]').addClass("sten-enabled");
                                    } else {

                                    	// apply for all swatches
                                    	$current_attribute_swatch.find("span.st-custom-attribute").addClass("sten-enabled");
                                    }
                                }
                        	}
                        }
                    }
				}

				$available_options_count = $current_attribute_swatch.find('span.st-custom-attribute.sten-enabled' ).length;

				if ( $selected_attr_val &&
					( $available_options_count === 0 || $current_attribute_swatch
						.find('span.st-custom-attribute.sten-enabled[data-value="' + ST_WC_FRONT_SWATCH.productLoop.addSlashes( $selected_attr_val ) + '"]' )
						.length === 0 ) ) {
					$selected_attr_val_valid = false;
                }

                // Disable terms if not available
                $current_attribute_swatch.find('span.st-custom-attribute:not(.sten-enabled)' ).addClass("sten-disabled");
                $current_attribute_swatch.find('span.st-custom-attribute:not(.sten-enabled)' ).parent("li").addClass("sten-li-disabled");

                // Choose selected value.
                if ( $selected_attr_val ) {

                	// If the previously selected value is no longer available,
                    // fall back to the placeholder (it's going to be there).
                    if ( !$selected_attr_val_valid ) {

                    	$current_attribute_swatch.find("li.selected").removeClass('selected');
                    }
                } else {

                    $current_attribute_swatch.find("li.selected").removeClass('selected');
                }                
			});
		},

		findMatchingVariations: function( $variations, $current_attributes ) {

			var $matching = [];

			for( var $i = 0; $i < $variations.length; $i++ ){

				var $variation = $variations[$i];

				if(  ST_WC_FRONT_SWATCH.productLoop.isMatch( $variation.attributes, $current_attributes ) ) {

					$matching.push( $variation );
				}
			}

			return $matching;
		},

		isMatch: function( $variation_attributes, $current_attributes ) {

			var $match = true;

			for( var $attr_name in $variation_attributes ) {

				if( $variation_attributes.hasOwnProperty( $attr_name ) ) {

					var $val1 = $variation_attributes[ $attr_name ],
						$val2 = $current_attributes[ $attr_name ];

					if( ( typeof $val1 !== "undefined" ) &&
						( typeof $val2 !== "undefined" ) &&
						( $val1.length !== 0 ) &&
						( $val2.length !== 0 ) &&
						( $val1 !== $val2 ) ) {

						$match = false;
					}
				}
			}

			return $match;
		},

		foundVariation: function( $product, $swatches, $variation ) {

			var	$price       = $product.find("span.price").not( '.price-cloned' ),
				$price_clone = $price.clone().addClass( 'price-cloned' ).css( 'display', 'none' );
				
			$swatches.attr("data-variation_id", $variation.variation_id );

			if( $variation.price_html ) {

                if ( !$product.find( '.price-cloned' ).length ) {

                	$product.append($price_clone);
                }

				$price.replaceWith( $variation.price_html );
			} else {

                if ( $product.find( '.price-cloned' ).length ) {

                	$price.replaceWith( $price_clone.html() );
                	$price_clone.remove();
                }
			}

			// Update Image
			ST_WC_FRONT_SWATCH.productLoop.variationsImageUpdate( $product, $variation );

            // change add to cart button text
			ST_WC_FRONT_SWATCH.productLoop.changeAddToCartBtnText( $product, $variation );
		},

		variationsImageUpdate: function( $product, $variation ) {

			var $product_img = $product.find("img.wp-post-image");

			if( $variation && $variation.st_image_src && $variation.image.src && $variation.st_image_src.length > 1 ) {

				ST_WC_FRONT_SWATCH.productLoop.setVariationAttr( $product_img, 'src', $variation.st_image_src[0] );
				ST_WC_FRONT_SWATCH.productLoop.setVariationAttr( $product_img, 'srcset', $variation.st_image_srcset );
				ST_WC_FRONT_SWATCH.productLoop.setVariationAttr( $product_img, 'sizes', $variation.st_image_sizes );
			} else {

				ST_WC_FRONT_SWATCH.productLoop.resetVariationAttr( $product_img, 'src' );
				ST_WC_FRONT_SWATCH.productLoop.resetVariationAttr( $product_img, 'srcset' );
				ST_WC_FRONT_SWATCH.productLoop.resetVariationAttr( $product_img, 'sizes' );
			}
		},

		changeAddToCartBtnText: function( $product, $variation ) {

			var $btn = $product.find("a.add_to_cart_button"),
				$txt = '';

			$btn.removeClass("added");
			if( Object.keys( $variation.attributes ).length === $product.find( "ul.st-swatch-preview" ).length ) {

				if ( $variation.is_in_stock === true ) {

					$txt = sten_wc_params.add_to_cart_btn_text;
					$btn.addClass("st-swatch-btn-ready").removeClass("st-swatch-btn-readmore");
				} else {

					$txt = sten_wc_params.read_more_btn_text;
					$btn.addClass("st-swatch-btn-readmore").removeClass("st-swatch-btn-ready");
				}
			} else {

				$txt = sten_wc_params.select_options_btn_text;
				$btn.removeClass( "st-swatch-btn-ready st-swatch-btn-readmore" );
			}

			$btn.addClass( "st-swatch-btn-text-changed" ).text( $txt );
		},

		setVariationAttr: function ( $el, $attr, $value ) {

			if ( undefined === $el.attr( 'data-o_' + $attr ) ) {

				$el.attr( 'data-o_' + $attr, ( !$el.attr( $attr ) ) ? '' : $el.attr( $attr ) );
            }

            if ( false === $value ) {

            	$el.removeAttr( $attr );
            } else {

            	$el.attr( $attr, $value );
            }
        },

        resetVariationAttr: function ( $el, $attr ) {

        	if ( undefined !== $el.attr( 'data-o_' + $attr ) ) {

        		$el.attr( $attr, $el.attr( 'data-o_' + $attr ) );
            }
        },

        addSlashes: function ( $string ) {

        	$string = ( " " + $string ).replace( /'/g, '\\\'' );
        	$string = ( " " + $string ).replace( /"/g, '\\\"' );
        	$string = $string.trim();
        	
        	return $string;
        },        

        addToCart: function(){

        	$(document).on("click", "a.product_type_variable.add_to_cart_button.st-swatch-btn-ready", function(e){

				var	$btn          = $( this ),
					$swatches     = $btn.closest(".product-type-variable").find("div.st-swatch-in-loop"),
					$variation_id = $swatches.data("variation_id");

				if( typeof $variation_id == "undefined" || $variation_id == '' ) {

					return true;
				}

				var	$product_id = $btn.data("product_id"),
					$quantity   = $btn.data("quantity"),
					$item       = {};

				$swatches.find("ul.st-swatch-preview").each(function(){

					var	$attribute     = $(this).data("attribute"),
						$attribute_val = $(this).find("li.selected span").data("value");

					$item[ "attribute_" + $attribute ] = $attribute_val;
				});

                $btn.removeClass( 'added' );
                $btn.addClass( 'loading' );

                var $data = {
					action       :  'sten_wc_product_loop_add_to_cart',
					product_id   :  $product_id,
					quantity     :  $quantity,
					variation_id :  $variation_id,
					variation    :  $item,
                };

                $( 'body' ).trigger( 'adding_to_cart', [ $btn, $data ] );

                $.ajax({
					type    : "POST",
					url     : sten_wc_params.ajax_url,
					data    : $data,
					success : function ( $response ) {

						if( !$response ) {

							return false;
						}

						if ( $response.error && $response.product_url ) {

							window.location = $response.product_url;
							return false;
                        }

                        if( wc_add_to_cart_params.cart_redirect_after_add == 'yes' ) {

                        	window.location = wc_add_to_cart_params.cart_url;
                        	return;
                        }

                        // update cart fragment
                        var $fragments = $response.fragments,
                        	$cart_hash = $response.cart_hash;

                        if ( $fragments ) {

                        	$.each( $fragments, function ( $key, $value ) {
                        		$( $key ).replaceWith( $value );
                            } );
                        }

                        $btn.addClass( 'added' );
                        $( 'body' ).trigger( 'added_to_cart', [ $fragments, $cart_hash, $btn ] );
					},
					error   : function ( $error ) {
						console.log( $error );
					}
                });

                e.preventDefault();
        	});
        },
	};

	ST_WC_FRONT_SWATCH.documentOnReady = {

		init : function() {

			ST_WC_FRONT_SWATCH.onSelect();
			ST_WC_FRONT_SWATCH.onClear();
			ST_WC_FRONT_SWATCH.onSelectUpdateAttribute();

			ST_WC_FRONT_SWATCH.productLoop.init();
		}
	};

	$(document).ready( ST_WC_FRONT_SWATCH.documentOnReady.init );
})(jQuery);