<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    St_Woo_Swatches
 * @subpackage St_Woo_Swatches/public
 * @author     SThemes <sthemes.envato@gmail.com>
 */
class St_Woo_Swatches_Public extends St_Woo_Swatches_Base {

	/**
	 * The public directory path of a plugin.
	 */
	protected $plugin_public_dir_path;

	/**
	 * The public directory path url of a plugin.
	 */
	protected $plugin_public_dir_path_url;

	public function __construct() {

		parent::__construct();		

		$this->plugin_public_dir_path     = plugin_dir_path( __FILE__ );
		$this->plugin_public_dir_path_url = plugin_dir_url( __FILE__ );

		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_filter( 'get_the_generator_html', array( $this, 'generator_tag' ), 15, 2 );
		add_filter( 'get_the_generator_xhtml', array( $this, 'generator_tag' ), 15, 2 );

		$this->load_template_hooks();
	}

	public function body_class( $classes ) {

		$classes[] = ( is_cart() || is_checkout() || is_account_page() ) ? 'st-swatch-plugin st-swatch-apply-border' : 'st-swatch-plugin';

		$tooltip = get_option( "st_swatch_tooltip_enable", "no" ); 
		$classes[] = ( $tooltip == 'yes' ) ? 'st-swatch-tooltip' : '';

		return $classes;
	}

	/**
	 * Load stylesheets and scripts
	 */
	public function enqueue_scripts() {

		wp_enqueue_style( $this->plugin_name, $this->plugin_public_dir_path_url . 'css/frontend.css', array( 'dashicons' ), $this->version, 'all' );
		wp_enqueue_script( $this->plugin_name, $this->plugin_public_dir_path_url . 'js/frontend.js', array( 'jquery' ), $this->version, false );

		$params = array(
			"ajax_url"                => esc_url( admin_url( 'admin-ajax.php' ) ),
			"is_customize_preview"    => is_customize_preview(),
			"is_singular_product"     => is_singular( 'product'),
			
			"add_to_cart_btn_text"    => esc_html__( 'Add  to cart', 'st-woo-swatches'),
			"read_more_btn_text"      => esc_html__( 'Read More', 'st-woo-swatches'),
			"select_options_btn_text" => esc_html__( 'Select options', 'st-woo-swatches'),
		);

		wp_localize_script( $this->plugin_name, 'sten_wc_params', apply_filters( 'sten_wc_params', $params ) );

		$css = '';

		if( is_shop() ) {

			$border_color = get_option( "st_swatch_shop_swatch_border_color", "#dddddd" ); 
			$active_color = get_option( "st_swatch_shop_swatch_active_border_color", "#ff0000" ); 

			$css .= "body.post-type-archive-product ul.st-swatch-preview li { border-color:".$border_color."}";
			$css .= "body.post-type-archive-product ul.st-swatch-preview li.selected { border-color:".$active_color."}\n";

		} elseif( is_product_category() || is_product_tag() ) {

			$border_color = get_option( "st_swatch_archive_swatch_border_color", "#dddddd" ); 
			$active_color = get_option( "st_swatch_archive_swatch_active_border_color", "#ff0000" ); 

			$css .= "body.tax-product_cat ul.st-swatch-preview li, body.tax-product_tag ul.st-swatch-preview li { border-color:".$border_color."}";
			$css .= "body.tax-product_cat ul.st-swatch-preview li.selected, body.tax-product_tag ul.st-swatch-preview li.selected { border-color:".$active_color."}\n";
		}

		if( wc_is_active_theme('twentyseventeen') && ( is_shop() || is_product_category() || is_product_tag() ) ) {

			$css .= 'ul.products li .woocommerce-LoopProduct-link:first-child { padding-bottom: 1.5em; display: block; }';
			$css .= 'ul.products li .st-swatch-in-loop { padding-bottom: 10px; }';
			$css .= 'ul.products li h2 { padding-top: 0; }';
		}

		if( is_cart() || is_checkout() || is_account_page() ) {

			$border_color  = get_option( "st_swatch_cart_chkout_swatch_border_color", "#dddddd" );
			$css          .= "body.st-swatch-apply-border span.st-swatch-preview { border-color:".$border_color."; }\n";
		}

		$tooltip = get_option( "st_swatch_tooltip_enable", "no" );
		if( 'yes' == $tooltip ) {

			$bg_color  = get_option( 'st_swatch_tooltip_bg_color', '#666666' );
			$txt_color = get_option( 'st_swatch_tooltip_txt_color', '#ffffff' );

			$css .= "[data-st-tooltip]:after { background-color:".$bg_color."; color:".$txt_color.";}\n";
			$css .= "[data-st-tooltip]:before { border-top-color:".$bg_color."}\n";
		}

		$css = trim( $css );
		if( !empty( $css ) ) {

			wp_add_inline_style( $this->plugin_name, trim( $css ));			
		}
	}

	/**
	 * STWooSwatches by SThemes
	 */
	public function generator_tag ( $gen, $type ) {

		$tag = sprintf( 
			/* translators: 1:Name of a plugin 2:Name of plugin author */
            esc_attr__( '%1$s by %2$s', 'st-woo-swatches' ),
            'STWooSwatches',
            'SThemes'
        );		

		switch ( $type ) {

			case 'html':
				$gen .= "\n" . '<meta name="generator" content="'.$tag.'">';
			break;
			case 'xhtml':
				$gen .= "\n" . '<meta name="generator" content="'.$tag.'"/>';
			break;
		}

		return $gen;		
	}

	/**
	 * Load the required template hooks for frontend.
	 */
	public function load_template_hooks() {

		/**
		 * The class responsible for defining all actions and hooks that occur in the shop page.
		 */
		require_once $this->plugin_public_dir_path . 'partials/class-st-woo-shop.php';
		new St_Woo_Shop( array(
			'plugin_public_dir_path'     => $this->plugin_public_dir_path,
			'plugin_public_dir_path_url' => $this->plugin_public_dir_path_url,
		) );

		/**
		 * The class responsible for defining all actions and hooks that occur in the single variable product.
		 */
		require_once $this->plugin_public_dir_path . 'partials/class-st-woo-single-variable-product.php';
		new St_Woo_Single_Variable_Product( array(
			'plugin_public_dir_path_url' => $this->plugin_public_dir_path_url,
		) );

		/**
		 * The class responsible for defining all actions and hooks that occur in the cart.
		 */
		require_once $this->plugin_public_dir_path . 'partials/class-st-woo-cart.php';		
		new St_Woo_Cart();

		/**
		 * The class responsible for defining all actions and hooks that occur in the checkout.
		 */
		require_once $this->plugin_public_dir_path . 'partials/class-st-woo-checkout.php';
		new St_Woo_Checkout();

		/**
		 * The class responsible for defining all actions and hooks that occur to show order item meta.
		 */
		require_once $this->plugin_public_dir_path . 'partials/class-st-woo-order-item.php';
		new St_Woo_OrderItem( array(
			'plugin_public_dir_path_url' => $this->plugin_public_dir_path_url,
		) );		
	}
}