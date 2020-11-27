<?php
/**
 * The public facing checkout page functionality.
 *
 * @package    St_Woo_Swatches
 * @subpackage St_Woo_Swatches/public/partials
 * @author     SThemes <sthemes.envato@gmail.com>
 */
class St_Woo_Checkout extends St_Woo_Swatches_Base {

	public function __construct() {

		parent::__construct();

		$show_swatches = get_option( "st_swatch_cart_chkout_enable", "yes" );

		if( $show_swatches == 'yes' ) {

			add_action( 'woocommerce_review_order_before_cart_contents', array( $this, 'start_capture' ) );
			add_action( 'woocommerce_review_order_after_cart_contents', array( $this, 'stop_capture' ), 1 );
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
	 * Update this functionality once, woocommerce\templates\checkout\review-order.php file will be updated.
	 */
	public function stop_capture() {

		ob_end_clean();

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {

				echo '<tr class="'.esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ).'">';

					echo '<td class="product-name">';
						echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;';
						echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times; %s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key );

						// Modified Meta Data
						echo $this->st_wc_get_formatted_cart_item_data( $cart_item );
					echo '</td>';

					echo '<td class="product-total">';
						echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
					echo '</td>';					
				echo '</tr>';
			}				
		}
	}
}