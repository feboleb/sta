<?php
/**
 * The Order Item Meta functionality.
 * Front end: Order review
 * Back end: Order
 *
 * @package    St_Woo_Swatches
 * @subpackage St_Woo_Swatches/public/partials
 * @author     SThemes <sthemes.envato@gmail.com>
 */
class St_Woo_OrderItem extends St_Woo_Swatches_Base {

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

		add_filter( 'woocommerce_order_item_display_meta_value', array( $this, 'order_item_display_meta_value' ), 10, 3 );
		add_filter( 'sten_wc_term_meta_html', array( $this, 'term_swatch_html' ), 10, 3 );
		add_filter( 'sten_wc_custom_attr_meta_html', array( $this, 'custom_attr_swatch_html' ), 10, 3 );

		add_filter( 'woocommerce_order_item_class', array( $this, 'add_extra_class' ),10, 3 );

		add_filter( 'woocommerce_email_styles', array( $this, 'term_swatch_html_css' ) );
	}

	function order_item_display_meta_value( $display_value, $meta, $obj ) {

		$custom_att_swatches = array();

		if( $obj instanceof WC_Order_Item_Product ) {

			$custom_att_swatches = get_post_meta( $obj->get_product_id(), "_product_attributes_swatch", true );
		}

		$taxonomy  = str_replace( 'attribute_', '', $meta->key );

		if ( taxonomy_exists( $taxonomy ) ) {

			$attribute = $this->get_tax_attribute( $taxonomy );

			if( array_key_exists( $attribute->attribute_type,  $this->attribute_types ) ) {

				// If this is a term slug, get the term's nice name.
				$term      = get_term_by( 'slug', $meta->value, $taxonomy );
				$term_id   = $term->term_id;
				$term_name = $term->name;

				// Filter item meta data to allow swatch details to set in display value.
				$display_value = apply_filters ( 'sten_wc_term_meta_html', $display_value, $attribute->attribute_type, $term_id );
			}
		} else if( is_array( $custom_att_swatches ) && array_key_exists( $taxonomy, $custom_att_swatches ) ) {

			// Filter item meta data to allow swatch details to set in display value.
			$swatch = $custom_att_swatches[$taxonomy]['swatch'];
			$value  = $custom_att_swatches[$taxonomy]['options'][$display_value]['value'];

			$display_value = apply_filters( 'sten_wc_custom_attr_meta_html', $display_value, $swatch, $value );
		}

		return $display_value;
	}

	function term_swatch_html( $display_value, $attribute_type, $term_id ) {

		// Add options or settings to retrive swatch size and shape for term data
		$custom_class = array(
			get_option( 'st_swatch_cart_chkout_swatch_size', 'st-swatch-size-tiny' ),
			get_option( 'st_swatch_cart_chkout_swatch_shape', 'st-swatch-shape-circle' ),
		);

		$custom_class = implode( ' ', array_filter( $custom_class ) );

		switch ( $attribute_type ) {

			case 'st-color-swatch':
				$color  = get_term_meta( $term_id, 'st-color-swatch', true );
				$swatch = sprintf('<span class="st-swatch-preview st-color-swatch %1$s"> <span class="st-custom-attribute" style="background-color:%2$s"> </span> </span>',
					esc_attr( $custom_class ),
					esc_attr( $color )
				);
				$display_value = $swatch;
			break;

			case 'st-label-swatch':
				$label  = get_term_meta( $term_id, 'st-label-swatch', true );
				$swatch = sprintf('<span class="st-swatch-preview st-label-swatch %1$s"> <span class="st-custom-attribute"> %2$s </span> </span>',
					esc_attr( $custom_class ),
					esc_html( $label )
				);
				$display_value = $swatch;				
			break;

			case 'st-image-swatch':
				$image  = get_term_meta( $term_id, 'st-image-swatch', true );
				$swatch = sprintf('<span class="st-swatch-preview st-image-swatch %1$s"> <span class="st-custom-attribute"> %2$s </span> </span>',
					esc_attr( $custom_class ),
					( $image ) ? wp_get_attachment_image( $image ) : '<img src="'.esc_url( $this->plugin_public_dir_path_url . 'images/placeholder.png' ).'"/>'
				);
				$display_value = $swatch;				
			break;			
		}		


		return $display_value;
	}

	function custom_attr_swatch_html( $display_value, $swatch, $value ) {

		// Add options or settings to retrive swatch size and shape for term data
		$custom_class = array(
			get_option( 'st_swatch_cart_chkout_swatch_size', 'st-swatch-size-tiny' ),
			get_option( 'st_swatch_cart_chkout_swatch_shape', 'st-swatch-shape-circle' ),
		);

		$custom_class = implode( ' ', array_filter( $custom_class ) );

		switch( $swatch ) {

			case 'st-color-swatch':
				$display_value = sprintf(
					'<span class="st-swatch-preview st-color-swatch %1$s"><span class="st-custom-attribute" style="background-color:%2$s"></span></span>',
					esc_attr( $custom_class ),
					esc_attr( $value )
				);
			break;

			case 'st-label-swatch':
				$display_value = sprintf(
					'<span class="st-swatch-preview st-label-swatch %1$s"><span class="st-custom-attribute"> %2$s </span> </span>',
					esc_attr( $custom_class ),
					esc_html( $value )
				);
			break;

			case 'st-image-swatch':
				$display_value = sprintf(
					'<span class="st-swatch-preview st-image-swatch %1$s"><span class="st-custom-attribute"> %2$s </span> </span>',
					esc_attr( $custom_class ),
					( $value ) ? wp_get_attachment_image( $value ) : '<img src="'.esc_url( $this->plugin_public_dir_path_url . 'images/placeholder.png' ).'"/>'
				);
			break;
		}

		return $display_value;
	}

	function add_extra_class( $class, $item, $order ) {

		$class = $class .' '. 'st-item-meta';
		return $class;
	}

	function term_swatch_html_css( $css ) {

		$css .= '.st-item-meta .st-swatch-preview { 
			display: inline-block;
			margin: 0 5px;
			border: 1px solid #dddddd;
			padding: 2px;
			cursor: pointer;	
			position: relative;
			border-radius: 1px;
			width: auto;
			height: auto;
			min-width:26px;
			min-height:26px;
			text-align: center;
			line-height: 26px;
		}';

		$css .= '.st-item-meta .st-swatch-preview span {
			display: inline-block;
			border-radius: 26px;
			width: auto;
			height: auto;
			min-width:26px;
			min-height:26px;
			font-size: 14px;
		}';

		$css .= '.st-item-meta .st-swatch-preview span img {
			width: 94px;
			height: auto;
			border-radius: 1px;
		}';

		$css .= '.wc-item-meta li strong {
			display: inline-block;
		}';
		$css .= '.wc-item-meta li p {
			display: inline-block;
			position: relative;
			top: 9px;
		}';

		return $css;
	}
}