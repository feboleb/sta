<?php
/**
 * The single variable product public facing functionality.
 *
 * @package    St_Woo_Swatches
 * @subpackage St_Woo_Swatches/public/partials
 * @author     SThemes <sthemes.envato@gmail.com>
 */
class St_Woo_Single_Variable_Product extends St_Woo_Swatches_Base {

	/**
	 * The public directory path url of a plugin.
	 */
	protected $plugin_public_dir_path_url;


	public function __construct( array $args = array() ) {

		parent::__construct();

		if (!empty($args)) {
			foreach ($args as $property => $arg) {
                $this->{$property} = $arg;
            }
        }

		add_action( 'woocommerce_before_variations_form', array( $this, 'start_capture' ) );
		add_action( 'woocommerce_after_variations_form', array( $this, 'stop_capture' ) );

		add_filter( 'sten_wc_attribute_html', array( $this, 'swatch_html' ), 10, 5 );
		add_filter( 'sten_wc_custom_attribute_html', array( $this, 'swatch_html_alt' ), 10, 5 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );			
	}

	/**
	 *  Start capturing the variation form
	 */
	public function start_capture() {
		ob_start();
	}

	/**
	 * Stop capturing and print the variation form
	 */
	public function stop_capture() {
		global $product;

		$form = ob_get_contents();

		if( $form ) {

			ob_end_clean();
		}

		if ( $product->is_type( 'variable' ) ) {

			$attributes          = $product->get_variation_attributes();
			$custom_att_swatches = get_post_meta( $product->get_id(), "_product_attributes_swatch", true );
			$custom_att_swatches = is_array( $custom_att_swatches ) ? $custom_att_swatches : array();			

			foreach( array_keys( $attributes ) as $taxonomy ) {

				$attribute = $this->get_tax_attribute( $taxonomy );
				
				$available_options = $attributes[$taxonomy];

				// Generate request variable name.
				$key      = 'attribute_' . sanitize_title( $taxonomy );
				$selected = isset( $_REQUEST[ $key ] ) ? wc_clean( $_REQUEST[ $key ] ) : $product->get_variation_default_attribute( $taxonomy );

				if( !is_null( $attribute ) ) { // custom attributes throws null

					if( array_key_exists( $attribute->attribute_type,  $this->attribute_types ) ) {

						// Get terms if this is a taxonomy - ordered. We need the names too.
						$terms  = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) );
						$swatch = apply_filters( 'sten_wc_attribute_html', $attribute->attribute_type, $taxonomy, $terms, $available_options, $selected  );

						$taxonomy = sanitize_title( $taxonomy );
				
						// update variation form
						$form = preg_replace(
							'/<select id="(' . $taxonomy . ')" class="([^"]*)" name="([^"]+)" data-attribute_name="([^"]+)"[^>]*>/',
							$swatch . '<select id="\\1" class="\\2 st-variation-select" name="\\3" data-attribute_name="\\4" style="display: none;">',
							$form
						);
					}
				} else if( array_key_exists( sanitize_title( $taxonomy ), $custom_att_swatches ) ){

					$taxonomy = sanitize_title( $taxonomy );
					$swatch   = apply_filters( 'sten_wc_custom_attribute_html', $custom_att_swatches[$taxonomy]['swatch'], $taxonomy, $custom_att_swatches[$taxonomy]['options'], $available_options, $selected );

					// update variation form
					$form = preg_replace(
						'/<select id="(' . $taxonomy . ')" class="([^"]*)" name="([^"]+)" data-attribute_name="([^"]+)"[^>]*>/',
						$swatch . '<select id="\\1" class="\\2 st-variation-select" name="\\3" data-attribute_name="\\4" style="display: none;">',
						$form
					);
				}
			}
		}

		print( $form );
	}

	/**
	 * Print HTML of swatches
	 */
	public function swatch_html( $attribute_type, $taxonomy, $terms, $variations, $selected ) {

		$html = '';

		$custom_class = array(
			get_option( 'st_swatch_product_single_swatch_size', 'st-swatch-size-small' ),
			get_option( 'st_swatch_product_single_swatch_shape', 'st-swatch-shape-circle' ),
		);
		$custom_class = implode( ' ', array_filter( $custom_class ) );

		switch ( $attribute_type ) {

			case 'st-color-swatch':
				if( $terms ) {

					$html .= sprintf('<ul class="st-swatch-preview st-color-swatch %1$s" data-attribute="%2$s">',
						esc_attr( $custom_class ),
						sanitize_title( $taxonomy )
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

					$html .= sprintf('<ul class="st-swatch-preview st-label-swatch %1$s" data-attribute="%2$s">',
						esc_attr( $custom_class ),
						sanitize_title( $taxonomy )
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

					$html .= sprintf('<ul class="st-swatch-preview st-image-swatch %1$s" data-attribute="%2$s">',
						esc_attr( $custom_class ),
						sanitize_title( $taxonomy )
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
	 * Print HTML of swatches for custom attributes
	 */
	public function swatch_html_alt( $swatch, $taxonomy, $meta, $options, $selected ){

		$html = '';

		$custom_class = array(
			get_option( 'st_swatch_product_single_swatch_size', 'st-swatch-size-small' ),
			get_option( 'st_swatch_product_single_swatch_shape', 'st-swatch-shape-circle' ),
		);

		$custom_class = implode( ' ', array_filter( $custom_class ) );

		switch( $swatch ) {

			case 'st-color-swatch':
				if( $options ) {

					$html .= sprintf('<ul class="st-swatch-preview st-color-swatch %1$s" data-attribute="%2$s">',
						esc_attr( $custom_class ),
						sanitize_title( $taxonomy )
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

	/**
	 * Enqueue Swatch Colors inline
	 */
	public function enqueue_scripts() {

		if( !is_singular( 'product') ) {
			return;
		}

		$border_color        = get_option( "st_swatch_product_single_swatch_border_color", "#dddddd" );
		$active_border_color = get_option( "st_swatch_product_single_swatch_active_border_color", "#ff0000" );

		$css = "body.single-product form.variations_form ul.st-swatch-preview li { border-color:".$border_color."; }\n";
		$css .= "body.single-product form.variations_form ul.st-swatch-preview li.selected { border-color:".$active_border_color."; }";

		wp_add_inline_style( $this->plugin_name, trim( $css ));
	}
}