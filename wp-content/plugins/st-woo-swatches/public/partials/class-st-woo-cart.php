<?php
/**
 * The public facing cart page functionality.
 *
 * @package    St_Woo_Swatches
 * @subpackage St_Woo_Swatches/public/partials
 * @author     SThemes <sthemes.envato@gmail.com>
 */
class St_Woo_Cart extends St_Woo_Swatches_Base {

	public function __construct() {

		parent::__construct();

		add_filter( 'woocommerce_product_variation_title_include_attributes', '__return_false' );

		$show_swatches = get_option( "st_swatch_cart_chkout_enable", "yes" );

		if( $show_swatches === 'yes' ) {
			add_action( 'woocommerce_before_cart_contents', array( $this, 'start_capture' ) );
			add_action( 'woocommerce_cart_contents', array( $this, 'stop_capture' ), 1 );

			add_filter( 'woocommerce_cart_item_class', array( $this, 'add_extra_class' ),10, 3 );
		}
	}

	/**
	 *  Start capturing the cart content
	 */
	public function start_capture() {
		ob_start();
	}

	/**
	 * Erase the captured cart content
	 * Update this functionality once, woocommerce\templates\cart\cart.php file will be updated.
	 */
	public function stop_capture() {

		ob_end_clean();
		
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {

				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

				echo '<tr class="woocommerce-cart-form__cart-item '.esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ).'">';

					echo '<td class="product-remove">';

						echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
							'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
							esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
							esc_attr__( 'Remove this item', 'st-woo-swatches' ),
							esc_attr( $product_id ),
							esc_attr( $_product->get_sku() )
						), $cart_item_key );
					echo '</td>';

					echo '<td class="product-thumbnail">';
						$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

						if ( ! $product_permalink ) {
							echo $thumbnail;
						} else {
							printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
						}
					echo '</td>';

					echo '<td class="product-name" data-title="'.esc_attr__( 'Product', 'st-woo-swatches' ).'">';
						if ( ! $product_permalink ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
						}

						// Modified Meta Data
						echo $this->st_wc_get_formatted_cart_item_data( $cart_item );

						// Backorder notification.
						if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
							echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'st-woo-swatches' ) . '</p>';
						}						
					echo '</td>';

					echo '<td class="product-price" data-title="'.esc_attr__( 'Price', 'st-woo-swatches' ).'">';
						echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
					echo '</td>';

					echo '<td class="product-quantity" data-title="'.esc_attr__( 'Quantity', 'st-woo-swatches' ).'">';
						if ( $_product->is_sold_individually() ) {
							$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
						} else {
							$product_quantity = woocommerce_quantity_input( array(
								'input_name'    => "cart[{$cart_item_key}][qty]",
								'input_value'   => $cart_item['quantity'],
								'max_value'     => $_product->get_max_purchase_quantity(),
								'min_value'     => '0',
								'product_name'  => $_product->get_name(),
							), $_product, false );
						}
						echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
					echo '</td>';

					echo '<td class="product-subtotal" data-title="'.esc_attr__( 'Total', 'st-woo-swatches' ).'">';
						echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
					echo '</td>';
				echo '</tr>';
			}
		}
	}

	/**
	 * Add extra class for cart item table <tr>
	 */
	function add_extra_class( $class, $cart_item, $cart_item_key ) {

		$class = $class .' '. 'st-item-meta';

		return $class;
	}	
}