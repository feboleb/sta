<?php
/**
 * The shop public facing functionality.
 *
 * @package    St_Woo_Swatches
 * @subpackage St_Woo_Swatches/public/partials
 * @author     SThemes <sthemes.envato@gmail.com>
 */
class St_Woo_Shop extends St_Woo_Swatches_Base {

	/**
	 * The public directory path of a plugin.
	 */
	protected $plugin_public_dir_path;

	/**
	 * The public directory path url of a plugin.
	 */
	protected $plugin_public_dir_path_url;


	public function __construct( $args ) {

		parent::__construct();


		if (!empty($args)) {
			foreach ($args as $property => $arg) {
                $this->{$property} = $arg;
            }
        }

        add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 11 );
        add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 9 );

        // To show swatch after product title link
	    add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'sten_swatch_above_title' ), 12 );

        // To show swatch before product add to cart link 
        add_action( 'woocommerce_after_shop_loop_item', array( $this, 'sten_swatch_below_title' ), 9 );

		add_shortcode( 'st-swatch-shortcode', array( $this, 'loop_swatch' ) );
		add_action( 'loop_swatch', array( $this, 'loop_swatch' ) );

		add_filter( 'sten_wc_archive_loop_available_variations', array( $this, 'available_variations' ) );

		add_filter( 'sten_wc_archive_loop_swatch_html', array( $this, 'swatch_html' ), 10, 5 );
		add_filter( 'sten_wc_archive_loop_custom_attr_swatch_html', array( $this, 'swatch_html_alt' ), 10, 5 );

		add_action( 'wp_ajax_nopriv_sten_wc_product_loop_add_to_cart', array( $this, 'add_to_cart' ) );
		add_action( 'wp_ajax_sten_wc_product_loop_add_to_cart', array( $this, 'add_to_cart' ) );

		add_filter( 'woocommerce_product_get_image', array( $this, 'product_get_image' ) );
	}

	public function sten_swatch_above_title() {

		$swatch_hook = '';

		if( is_shop() ) {

			$swatch_hook = get_option( 'st_swatch_shop_swatch_hook', 'st-before-loop-item-title');

		} elseif( is_product_category() || is_product_tag() ) {

			$swatch_hook = get_option( 'st_swatch_archive_swatch_hook', 'st-before-loop-item-title');
		} else {

			// Swatch for other pages and shortcode
			$swatch_hook = get_option( 'st_swatch_archive_swatch_hook', 'st-before-loop-item-title');
		}

		if( $swatch_hook == 'st-before-loop-item-title' ) {

			do_action( 'loop_swatch');
		}
	}

	public function sten_swatch_below_title() {

		$swatch_hook = '';

		if( is_shop() ) {

			$swatch_hook = get_option( 'st_swatch_shop_swatch_hook', 'st-before-loop-item-title');

		} elseif( is_product_category() || is_product_tag() ) {

			$swatch_hook = get_option( 'st_swatch_archive_swatch_hook', 'st-before-loop-item-title');
		} else {

			// Swatch for other pages and shortcode
			$swatch_hook = get_option( 'st_swatch_archive_swatch_hook', 'st-before-loop-item-title');
		}

		if( $swatch_hook == 'st-after-loop-item-title' ) {

			do_action( 'loop_swatch');
		}
	}	

	public function loop_swatch() {

		global $product;

		if( !$product->is_type( 'variable' ) )
			return;

		$available_variations = apply_filters( 'sten_wc_archive_loop_available_variations', $product->get_available_variations() );

		if( empty( $available_variations ) )
			return;

		$html = '';

		$html .= sprintf('<div class="st-swatch-in-loop" data-product_id="%1$s" data-product_variations="%2$s">',
			esc_attr( absint( $product->get_id() ) ),
			htmlspecialchars( wp_json_encode( $available_variations ) )
		);

		$attributes          = $product->get_variation_attributes();
		$custom_att_swatches = get_post_meta( $product->get_id(), "_product_attributes_swatch", true );
		$custom_att_swatches = is_array( $custom_att_swatches ) ? $custom_att_swatches : array();			

		$catalog_mode_attributes = array();
		$catalog_mode = get_option( 'st_swatch_one_swatch_mode', 'no' );

		if( $catalog_mode == "yes" ) {

			$global_catalog_attr  = get_option( 'st_swatch_one_swatch_mode_attribute', '' );
			$product_catalog_mode = get_post_meta( $product->get_id(), "_product_stwc_catalog_mode", true );

			if( isset( $product_catalog_mode['mode'] ) ) {

				if( $product_catalog_mode['mode'] == 'global' ) {

					array_push( $catalog_mode_attributes, $global_catalog_attr );
				} elseif( $product_catalog_mode['mode'] == 'custom' && isset( $product_catalog_mode['attributes'] ) ) {

					$catalog_mode_attributes = $product_catalog_mode['attributes'];
				}
			}  else {

				array_push( $catalog_mode_attributes, $global_catalog_attr );
			}
		}

		$catalog_mode_attributes = array_filter( $catalog_mode_attributes );

		foreach( array_keys( $attributes ) as $taxonomy ) {

			$attribute         = $this->get_tax_attribute( $taxonomy );
			$available_options = $attributes[$taxonomy];

			$key      = 'attribute_' . sanitize_title( $taxonomy );
			$selected = $product->get_variation_default_attribute( $taxonomy );

			if( empty( $catalog_mode_attributes ) || in_array( sanitize_title( $taxonomy ), $catalog_mode_attributes ) ) {

				if( !is_null( $attribute ) ) { // custom attributes throws null

					if( array_key_exists( $attribute->attribute_type,  $this->attribute_types ) ) {

						// Get terms if this is a taxonomy - ordered. We need the names too.
						$terms    = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) );

						$html .= apply_filters( 'sten_wc_archive_loop_swatch_html', $attribute->attribute_type, $taxonomy, $terms, $available_options, $selected  );
					}
				} elseif( array_key_exists( sanitize_title( $taxonomy ), $custom_att_swatches ) ) {

					$taxonomy = sanitize_title( $taxonomy );

					$html .= apply_filters( 'sten_wc_archive_loop_custom_attr_swatch_html', $custom_att_swatches[$taxonomy]['swatch'], $taxonomy, $custom_att_swatches[$taxonomy]['options'], $available_options, $selected );
				}
			}
		}

		$html .= sprintf( '<a href="javascript:void(0);" class="sten-reset-loop-variation" style="display:none;"> %1$s </a>', esc_html__( 'Clear', 'st-woo-swatches' )  );
		$html .= sprintf('</div>');
		print ( $html );
	}

	public function available_variations( $variations ) {

		$new_variations = array();

		foreach ( $variations as $variation ) {

			if ( $variation['variation_id'] != '' ) {

				$id     = get_post_thumbnail_id( $variation['variation_id'] );
				$src    = wp_get_attachment_image_src( $id, 'shop_catalog' );
				$srcset = wp_get_attachment_image_srcset( $id, 'shop_catalog' );
				$sizes  = wp_get_attachment_image_sizes( $id, 'shop_catalog' );

				$variation['st_image_src']    = $src;
				$variation['st_image_srcset'] = $srcset;
				$variation['st_image_sizes']  = $sizes;

				$new_variations[] = $variation;
			}
		}

		return $new_variations;
	}

	/**
	 * Print HTML of swatches
	 */
	public function swatch_html( $attribute_type, $taxonomy, $terms, $variations, $selected ) {

		$html = '';
		$custom_class = array();

		if( is_shop() ) {

			$custom_class = array(
				get_option( 'st_swatch_shop_swatch_size', 'st-swatch-size-small' ),
				get_option( 'st_swatch_shop_swatch_shape', 'st-swatch-shape-circle' ),
			);
		} elseif( is_product_category() || is_product_tag() ) {

			$custom_class = array(
				get_option( 'st_swatch_archive_swatch_size', 'st-swatch-size-small' ),
				get_option( 'st_swatch_archive_swatch_shape', 'st-swatch-shape-circle' ),
			);
		} else {

			$custom_class = array(
				get_option( 'st_swatch_archive_swatch_size', 'st-swatch-size-small' ),
				get_option( 'st_swatch_archive_swatch_shape', 'st-swatch-shape-circle' ),
			);			
		}

		$custom_class = implode( ' ', array_filter( $custom_class ) );		

		switch ( $attribute_type ) {

			case 'st-color-swatch':
				if( $terms ) {

					$html .= sprintf('<ul class="st-swatch-preview st-color-swatch %1$s %3$s" data-attribute="%2$s" data-default-attribute="%4$s">',
						esc_attr( $custom_class ),
						sanitize_title( $taxonomy ),
						!empty( $selected ) ? 'has-default-attribute' : '',
						!empty( $selected ) ? $selected : 'none'
					);

					foreach( $terms as $term ) {

						if ( in_array( $term->slug, $variations, true ) ) {

							$color   = get_term_meta( $term->term_id, 'st-color-swatch', true );
							$tooltip = get_term_meta( $term->term_id, 'st-tooltip', true );

							$class = ( $selected == $term->slug ) ? 'selected' : '';
							$class .= ( $color == '#ffffff' || $color == '#fcfcfc' || $color == '#f7f7f7' || $color == '#f4f4f4'  ) ?  'st-swatch-white' : '';

							$html .= sprintf('<li class="%1$s"> <span class="st-custom-attribute" data-value="%2$s" data-name="%3$s" 
								style="background-color:%4$s"%5$s></span> </li>',
								esc_attr( $class ),
								esc_attr( $term->slug ),
								esc_attr( $term->name ),
								esc_attr( $color ),
								!empty( $tooltip) ? ' data-st-tooltip="'.esc_attr( $tooltip ).'"' : ""								
							);
						}
					}
					$html .= sprintf('</ul>');
				}
			break;

			case 'st-label-swatch':
				if( $terms ) {

					$html .= sprintf('<ul class="st-swatch-preview st-label-swatch %1$s %3$s" data-attribute="%2$s" data-default-attribute="%4$s">',
						esc_attr( $custom_class ),
						sanitize_title( $taxonomy ),
						!empty( $selected ) ? 'has-default-attribute' : '',
						!empty( $selected ) ? $selected : 'none'
					);

					foreach( $terms as $term ) {

						if ( in_array( $term->slug, $variations, true ) ) {

							$label   = get_term_meta( $term->term_id, 'st-label-swatch', true );
							$tooltip = get_term_meta( $term->term_id, 'st-tooltip', true );
							$class   = ( $selected == $term->slug ) ? 'selected' : '';

							$html .= sprintf('<li class="%1$s"> <span class="st-custom-attribute" data-value="%2$s" data-name="%3$s"%5$s> %4$s </span> </li>',
								esc_attr( $class ),
								esc_attr( $term->slug ),
								esc_attr( $term->name ),
								esc_html( $label ),
								!empty( $tooltip) ? ' data-st-tooltip="'.esc_attr( $tooltip ).'"' : ""
							);
						}		
					}
					$html .= sprintf('</ul>');
				}
			break;

			case 'st-image-swatch':
				if( $terms ) {

					$html .= sprintf('<ul class="st-swatch-preview st-image-swatch %1$s %3$s" data-attribute="%2$s" data-default-attribute="%4$s">',
						esc_attr( $custom_class ),
						sanitize_title( $taxonomy ),
						!empty( $selected ) ? 'has-default-attribute' : '',
						!empty( $selected ) ? $selected : 'none'						
					);
					
					foreach( $terms as $term ) {

						if ( in_array( $term->slug, $variations, true ) ) {

							$image   = get_term_meta( $term->term_id, 'st-image-swatch', true );
							$tooltip = get_term_meta( $term->term_id, 'st-tooltip', true );
							$class   = ( $selected == $term->slug ) ? 'selected' : '';

							$html .= sprintf('<li class="%1$s"> <span class="st-custom-attribute" data-value="%2$s" data-name="%3$s"%5$s> %4$s </span> </li>',
								esc_attr( $class ),
								esc_attr( $term->slug ),
								esc_attr( $term->name ),
								( $image ) ? wp_get_attachment_image( $image ) : '<img src="'.esc_url( $this->plugin_public_dir_path_url . 'images/placeholder.png' ).'"/>',
								!empty( $tooltip) ? ' data-st-tooltip="'.esc_attr( $tooltip ).'"' : ""								 
							);
						}
					}
					$html .= sprintf('</ul>');
				}
			break;
		}

		return $html;
	}

	/**
	 * Print HTML of swatch for product custom attributes
	 */
	public function swatch_html_alt( $swatch, $taxonomy, $meta, $options, $selected ) {

		$html = '';
		$custom_class = array();

		if( is_shop() ) {

			$custom_class = array(
				get_option( 'st_swatch_shop_swatch_size', 'st-swatch-size-small' ),
				get_option( 'st_swatch_shop_swatch_shape', 'st-swatch-shape-circle' ),
			);
		} elseif( is_product_category() || is_product_tag() ) {

			$custom_class = array(
				get_option( 'st_swatch_archive_swatch_size', 'st-swatch-size-small' ),
				get_option( 'st_swatch_archive_swatch_shape', 'st-swatch-shape-circle' ),
			);
		} else {

			$custom_class = array(
				get_option( 'st_swatch_archive_swatch_size', 'st-swatch-size-small' ),
				get_option( 'st_swatch_archive_swatch_shape', 'st-swatch-shape-circle' ),
			);			
		}

		$custom_class = implode( ' ', array_filter( $custom_class ) );

		switch( $swatch ) {

			case 'st-color-swatch':
				if( $options ){

					$html .= sprintf('<ul class="st-swatch-preview st-color-swatch %1$s %3$s" data-attribute="%2$s" data-default-attribute="%4$s">',
						esc_attr( $custom_class ),
						sanitize_title( $taxonomy ),
						!empty( $selected ) ? 'has-default-attribute' : '',
						!empty( $selected ) ? $selected : 'none'
					);

					foreach( $options as $option ) {

						$color   = $meta[$option]['value'];
						$tooltip = $meta[$option]['tooltip'];

						$class = ( $selected == $option ) ? 'selected' : '';
						$class .= ( $color == '#ffffff' || $color == '#fcfcfc' || $color == '#f7f7f7' || $color == '#f4f4f4'  ) ?  'st-swatch-white' : '';

						$html .= sprintf('<li class="%1$s"> <span class="st-custom-attribute" data-value="%2$s" data-name="%2$s" style="background-color:%3$s"%4$s></span> </li>',
							esc_attr( $class ),
							esc_attr( $option ),
							esc_attr( $color ),
							!empty( $tooltip) ? 'data-st-tooltip="'.esc_attr( $tooltip ).'"' : ""
						);
					}				

					$html .= sprintf('</ul>');
				}
			break;

			case 'st-label-swatch':
				if( $options ) {

					$html .= sprintf('<ul class="st-swatch-preview st-label-swatch %1$s" data-attribute="%2$s">',
						esc_attr( $custom_class ),
						sanitize_title( $taxonomy )
					);

					foreach( $options as $option ) {

						$label   = $meta[$option]['value'];
						$tooltip = $meta[$option]['tooltip'];
						$class   = ( $selected == $option ) ? 'selected' : '';

						$html .= sprintf('<li class="%1$s"> <span class="st-custom-attribute" data-value="%2$s" data-name="%2$s" %3$s>%4$s</span> </li>',
							esc_attr( $class ),
							esc_attr( $option ),
							!empty( $tooltip) ? ' data-st-tooltip="'.esc_attr( $tooltip ).'"' : "",
							esc_html( $label )
						);
					}

					$html .= sprintf('</ul>');
				}			
			break;

			case 'st-image-swatch':
				if( $options ) {

					$html .= sprintf('<ul class="st-swatch-preview st-image-swatch %1$s" data-attribute="%2$s">',
						esc_attr( $custom_class ),
						sanitize_title( $taxonomy )
					);

					foreach( $options as $option ) {

						$image   = $meta[$option]['value'];
						$tooltip = $meta[$option]['tooltip'];
						$class   = ( $selected == $option ) ? 'selected' : '';

						$html .= sprintf('<li class="%1$s"> <span class="st-custom-attribute" data-value="%2$s" data-name="%2$s" %3$s> %4$s </span> </li>',
							esc_attr( $class ),
							esc_attr( $option ),
							!empty( $tooltip) ? 'data-st-tooltip="'.esc_attr( $tooltip ).'"' : "",
							( $image ) ? wp_get_attachment_image( $image ) : '<img src="'.esc_url( $this->plugin_public_dir_path_url . 'images/placeholder.png' ).'"/>'
						);
					}

					$html .= sprintf('</ul>');
				}			
			break;
		}
		
		return $html;
	}

	public function add_to_cart() {

		$product_id   = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$quantity     = empty( $_POST['quantity'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $_POST['quantity'] );
		$variation_id = $_POST['variation_id'];
		$variation    = array();
		$data         = array();

		if ( is_array( $_POST['variation'] ) ) {

			foreach ( $_POST['variation'] as $key => $value ) {

				$variation[ $key ] = $this->utf8_urldecode( $value );
			}
		}

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

		if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) ) {

			do_action( 'woocommerce_ajax_added_to_cart', $product_id );

			if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
				wc_add_to_cart_message( $product_id );
			}

			$data = WC_AJAX::get_refreshed_fragments();
		} else {

			WC_AJAX::json_headers();

			$data = array(
				'error'       => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
			);
		}

		wp_send_json( $data );
		wp_die();
	}

	/**
	 * Modify class attribute for image in woCommerce 3.5.1
	 */
	public function product_get_image( $image ) {
		
		// woCommerce 3.5.1 removes wp-post-image class for image
		$image = preg_replace('/(\<img [^>]*class=")([^"]*)"/i', '\1\2 wp-post-image"', $image);

		return $image;
	}	
}