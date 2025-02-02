<?php
/**
 * Booster for WooCommerce - Module - Product MSRP
 *
 * @version 5.1.0
 * @since   3.6.0
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCJ_Product_MSRP' ) ) :

class WCJ_Product_MSRP extends WCJ_Module {

	private $current_template_path = '';

	/**
	 * Constructor.
	 *
	 * @version 4.4.0
	 * @since   3.6.0
	 * @todo    (maybe) option to change `_wcj_msrp` meta key
	 * @todo    (maybe) REST API
	 * @todo    (maybe) grouped products
	 * @todo    (maybe) composite products
	 * @todo    (maybe) `[wcj_product_msrp]` shortcode (and link to "Product Info" module in description)
	 */
	function __construct() {

		$this->id         = 'product_msrp';
		$this->short_desc = __( 'Product MSRP', 'woocommerce-jetpack' );
		$this->extra_desc = __( 'The <strong>manufacturer\'s suggested retail price</strong> (<strong>MSRP</strong>), also known as the <strong>list price</strong>, or the <strong>recommended retail price</strong> (<strong>RRP</strong>), or the <strong>suggested retail price</strong> (<strong>SRP</strong>), of a product is the price at which the manufacturer recommends that the retailer sell the product.', 'woocommerce-jetpack' ) . '<br>' .
			sprintf( __( 'Booster stores MSRP as product meta with %s key.', 'woocommerce-jetpack' ), '<code>_wcj_msrp</code>' );
		$this->desc       = __( 'Save and display product MSRP in WooCommerce.', 'woocommerce-jetpack' );
		$this->link_slug  = 'woocommerce-msrp';
		parent::__construct();

		if ( $this->is_enabled() ) {
			if ( 'inline' === get_option( 'wcj_product_msrp_admin_view', 'inline' ) ) {
				// MSRP input on admin product page (simple product)
				add_action( 'woocommerce_product_options_pricing', array( $this, 'add_msrp_input' ) );
				add_action( 'save_post_product',                   array( $this, 'save_msrp_input' ), PHP_INT_MAX, 2 );
				// MSRP input on admin product page (variable product)
				add_action( 'woocommerce_variation_options_pricing', array( $this, 'add_msrp_input_variable' ), 10, 3 );
				add_action( 'woocommerce_save_product_variation',    array( $this, 'save_msrp_input_variable' ), PHP_INT_MAX, 2 );
			} else { // 'meta_box'
				// Products meta box
				add_action( 'add_meta_boxes',    array( $this, 'add_meta_box' ) );
				add_action( 'save_post_product', array( $this, 'save_meta_box' ), PHP_INT_MAX, 2 );
			}
			// Display
			add_filter( 'woocommerce_get_price_html', array( $this, 'display' ), PHP_INT_MAX, 2 );

			// Make _wcj_msrp compatible with other modules or third party plugins
			add_filter( 'wcj_product_msrp', array( $this, 'make_wcj_msrp_price_compatible_with_3rd_party' ), 10, 2 );

			// Get current template path
			add_action( 'woocommerce_before_template_part', array( $this, 'get_current_template_path' ), 10 );
		}

	}

	/**
	 * get_current_template_path.
	 *
	 * @version 4.9.0
	 * @since   4.9.0
	 */
	function get_current_template_path( $template_name ) {
		$this->current_template_path = $template_name;
		return $template_name;
	}

	/**
	 * Add compatibility between third party modules / plugins and _wcj_msrp
	 *
	 * @version 4.4.0
	 * @since   4.4.0
	 *
	 * @param $price
	 * @param $product
	 *
	 * @return mixed
	 */
	function make_wcj_msrp_price_compatible_with_3rd_party( $price, $product ) {
		if ( 'yes' !== get_option( 'wcj_payment_msrp_comp_mc', 'no' ) ) {
			return $price;
		}
		$module = 'multicurrency';
		if ( wcj_is_module_enabled( $module ) ) {
			$price = WCJ()->modules[ $module ]->change_price( $price, null );
		}
		return $price;
	}

	/**
	 * add_msrp_input_variable.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 */
	function add_msrp_input_variable( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input( array(
			'id'            => "variable_wcj_msrp_{$loop}",
			'name'          => "variable_wcj_msrp[{$loop}]",
			'value'         => wc_format_localized_price( isset( $variation_data['_wcj_msrp'][0] ) ? $variation_data['_wcj_msrp'][0] : '' ),
			'label'         => __( 'MSRP', 'woocommerce-jetpack' ) . ' (' . get_woocommerce_currency_symbol() . ')',
			'data_type'     => 'price',
			'wrapper_class' => 'form-row form-row-full',
		) );
	}

	/**
	 * save_msrp_input_variable.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 */
	function save_msrp_input_variable( $variation_id, $i ) {
		if ( isset( $_POST['variable_wcj_msrp'][ $i ] ) ) {
			update_post_meta( $variation_id, '_wcj_msrp', wc_clean( $_POST['variable_wcj_msrp'][ $i ] ) );
		}
	}

	/**
	 * add_msrp_input.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 * @todo    (maybe) rethink `$product_id`
	 */
	function add_msrp_input() {
		$product_id = get_the_ID();
		woocommerce_wp_text_input( array(
			'id'          => '_wcj_msrp',
			'value'       => get_post_meta( $product_id, '_' . 'wcj_msrp', true ),
			'data_type'   => 'price',
			'label'       => __( 'MSRP', 'woocommerce-jetpack' ) . ' (' . get_woocommerce_currency_symbol() . ')',
		) );
	}

	/**
	 * save_msrp_input.
	 *
	 * @version 3.6.0
	 * @since   3.6.0
	 */
	function save_msrp_input( $post_id, $__post ) {
		if ( isset( $_POST['_wcj_msrp'] ) ) {
			update_post_meta( $post_id, '_wcj_msrp', $_POST['_wcj_msrp'] );
		}
	}

	/**
	 * get_section_id_by_template_path.
	 *
	 * @version 5.1.0
	 * @since   5.1.0
	 *
	 * @param $template
	 *
	 * @return string
	 */
	function get_section_id_by_template_path( $template ) {
		$archive_detection_method = $this->get_option( 'wcj_product_msrp_archive_detection_method', 'loop' );
		$archive_detection_method = array_values( array_filter( explode( PHP_EOL, $archive_detection_method ) ) );
		return count( array_filter( $archive_detection_method, function ( $item ) use ( $template ) {
			return strpos( $template, $item ) !== false;
		} ) ) == 0 && is_singular() ? 'single' : 'archives';
	}

	/**
	 * display.
	 *
	 * @version 5.1.0
	 * @since   3.6.0
	 * @todo    (maybe) multicurrency
	 * @todo    (feature) (maybe) variable product's msrp: add another option to enter MSRP directly for the whole variable product, instead of taking first variation's MSRP
	 */
	function display( $price_html, $product ) {
		$section_id = $this->get_section_id_by_template_path( $this->current_template_path );
		$display    = get_option( 'wcj_product_msrp_display_on_' . $section_id, 'show' );
		if ( 'hide' == $display ) {
			return $price_html;
		}
		$product_id = false;
		if ( $product->is_type( 'variable' ) && $product->get_variation_price( 'min' ) === $product->get_variation_price( 'max' ) ) {
			if ( 'yes' === get_option( 'wcj_product_msrp_variable_as_simple_enabled', 'no' ) ) {
				$product_id = wcj_get_product_id( $product );
			} else {
				foreach ( $product->get_available_variations() as $variation ) {
					// grab first variation's ID
					$product_id = $variation['variation_id'];
					break;
				}
			}
		} elseif ( $product->is_type( 'variation' ) && 'yes' === get_option( 'wcj_product_msrp_variable_as_simple_enabled', 'no' ) ) {
			return $price_html;
		}

		if ( ! $product_id ) {
			$product_id = wcj_get_product_id( $product );
		}

		// MSRP
		$msrp_product_meta_name = '_wcj_msrp';
		if (
			'yes' === get_option( 'wcj_product_msrp_archive_page_field', 'no' ) &&
			'archives' === $section_id
		) {
			$product_id = wcj_get_product_id( $product );
			$msrp_product_meta_name = '_wcj_msrp_archive';
		}
		$msrp       = apply_filters( 'wcj_product_msrp', get_post_meta( $product_id, $msrp_product_meta_name, true ), $product );
		$msrp       = str_replace( ',', '.', $msrp );
		if ( '' == $msrp || 0 == $msrp ) {
			return $price_html;
		}

		$price = $product->get_price();
		if ( ( 'show_if_diff' == $display && $msrp == $price ) || ( 'show_if_higher' == $display && $msrp <= $price ) ) {
			return $price_html;
		}

		//WCJ Math
		require_once( wcj_plugin_path() . '/includes/lib/PHPMathParser/Math.php' );
		$math = new WCJ_Math();

		// You Save Formula
		$you_save_option = get_option( 'wcj_product_msrp_formula_you_save', '%msrp% - %product_price%' );
		$you_save_formula_result = $math->evaluate( str_replace( array( '%msrp%', '%product_price%' ), array( $msrp, $price ), $you_save_option ) );

		// You Save Percent Formula
		$you_save_percent_option = get_option( 'wcj_product_msrp_formula_you_save_percent', '(%msrp% - %product_price%) / %msrp% * 100' );
		$you_save_percent_formula_result = $math->evaluate( str_replace( array( '%msrp%', '%product_price%' ), array( $msrp, $price ), $you_save_percent_option ) );

		$position         = get_option( 'wcj_product_msrp_display_on_' . $section_id . '_position', 'after_price' );
		$default_template = '<div class="price"><label for="wcj_product_msrp">MSRP</label>: <span id="wcj_product_msrp"><del>%msrp%</del>%you_save%</span></div>';
		$template         = apply_filters( 'booster_option', $default_template, get_option( 'wcj_product_msrp_display_on_' . $section_id . '_template', $default_template ) );
		$diff             = $msrp - ( float ) $price;
		$you_save         = ( $diff > 0 ? get_option( 'wcj_product_msrp_display_on_' . $section_id . '_you_save',         ' (%you_save_raw%)' )           : '' );
		$you_save_percent = ( $diff > 0 ? get_option( 'wcj_product_msrp_display_on_' . $section_id . '_you_save_percent', ' (%you_save_percent_raw% %)' ) : '' );
		$you_save_round   = get_option( 'wcj_product_msrp_display_on_' . $section_id . '_you_save_percent_round', 0 );
		$replaced_values  = array(
			'%msrp%'             => wc_price( $msrp ),
			'%you_save%'         => str_replace( '%you_save_raw%',         wc_price( $you_save_formula_result ),                             $you_save ),
			'%you_save_percent%' => str_replace( '%you_save_percent_raw%', round( $you_save_percent_formula_result, $you_save_round ), $you_save_percent ),
		);
		return ( 'before_price' == $position ?
			wcj_handle_replacements( $replaced_values, $template ) . $price_html :
			$price_html . wcj_handle_replacements( $replaced_values, $template ) );
	}

}

endif;

return new WCJ_Product_MSRP();
