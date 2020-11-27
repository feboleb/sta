<?php
/**
 * Plugin Name:       STWooSwatches
 * Plugin URI:        http://sthemes.co/swatches
 * Description:       The Advanced Variable Product Attributes ( Swatches ) for WooCommerce
 * Version:           1.1.0
 * Author:            SThemes
 * Author URI:        http://codecanyon.net/user/sthemes
 * Text Domain:       st-woo-swatches
 * Domain Path:       /languages
 * Requires at least: 4.8
 * Tested up to: 	  5.2.4
 * WC requires at least: 3.3
 * WC tested up to:   3.7.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The base plugin class.
 *
 * @since      1.1.0
 * @package    St_Woo_Swatches
 * @author     SThemes <sthemes.envato@gmail.com>
 */
class St_Woo_Swatches_Base {

	/**
	 * The unique identifier of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 */
	protected $version;

	/**
	 * The basename of a plugin.
	 */
	protected $plugin_basename;

	/**
	 * The directory path of a plugin.
	 */
	protected $plugin_dir_path;

	/**
	 * The directory path url of a plugin.
	 */
	protected $plugin_dir_path_url;

	/**
	 * Additional Product Attribute types
	 */
	protected $attribute_types;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {

		$this->plugin_name         = 'st-woo-swatches';
		$this->version             = '1.1.0';
		$this->plugin_basename     = plugin_basename( __FILE__ );
		$this->plugin_dir_path     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_path_url = plugin_dir_url( __FILE__ );

		$this->attribute_types     = array(
			'st-color-swatch' => esc_html__( 'Color', 'st-woo-swatches'),
			'st-image-swatch' => esc_html__( 'Image', 'st-woo-swatches'),
			'st-label-swatch' => esc_html__( 'Label', 'st-woo-swatches'),			
		);

		add_filter( 'sten_wc_cart_attribute_html', array( $this, 'cart_swatch_html' ), 10, 4 );
		add_filter( 'sten_wc_cart_custom_attribute_html', array( $this, 'cart_custom_swatch_html' ), 10, 3 );
	}

	/**
	 * Get attribute's properties
	 */
	public function get_tax_attribute( $taxonomy ) {

		global $wpdb;

		$attr = substr( $taxonomy, 3 );
		$attr = $wpdb->get_row( "SELECT attribute_id, attribute_name, attribute_label, attribute_type FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = '$attr'" );

		return $attr;
	}

	/**
	 * Gets and formats a list of cart item data + variations for display on the frontend.
	 * Cart and Checkout Page
	 */
	public function st_wc_get_formatted_cart_item_data( $cart_item ) {

		$item_data = array();
		$custom_att_swatches = get_post_meta( $cart_item['product_id'], "_product_attributes_swatch", true );

		// Variation values are shown only if they are not found in the title as of 3.0.
		// This is because variation titles display the attributes.
		if ( $cart_item['data']->is_type( 'variation' ) && is_array( $cart_item['variation'] ) ) {

			foreach ( $cart_item['variation'] as $name => $value ) {

				$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

				if ( taxonomy_exists( $taxonomy ) ) {
					// If this is a term slug, get the term's nice name.
					$term = get_term_by( 'slug', $value, $taxonomy );
					if ( ! is_wp_error( $term ) && $term && $term->name ) {
						$value = $term->name;
					}
					$label = wc_attribute_label( $taxonomy );
				} else {
					// If this is a custom option slug, get the options name.
					$value = apply_filters( 'woocommerce_variation_option_name', $value );
					$label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $cart_item['data'] );
				}

				// Check the nicename against the title.
				if ( '' === $value || wc_is_attribute_in_product_name( $value, $cart_item['data']->get_name() ) ) {
					continue;
				}

				$data = array(
					'key'   => $label,
					'value' => $value,
				);

				// Modified - Included by SThemes				
				$st_name = str_replace( 'attribute_', '', $name );

				if ( taxonomy_exists( $taxonomy ) ) {
				
					$attribute = $this->get_tax_attribute( $taxonomy );
					if( array_key_exists( $attribute->attribute_type,  $this->attribute_types ) ) {

						if ( taxonomy_exists( $taxonomy ) ) {

							// If this is a term slug, get the term's nice name.
							$term      = get_term_by( 'slug', $value, $taxonomy );
							$term_id   = $term->term_id;
							$term_name = $term->name;

							// Filter item data to allow swatch details to add to the array.
							$data = apply_filters( 'sten_wc_cart_attribute_html', $data, $attribute->attribute_type, $term_id, $term_name );
						}
					}
				} else if( array_key_exists( $st_name, $custom_att_swatches ) ) {

					// Filter item data to allow swatch details to add to the array.
					$data = apply_filters( 'sten_wc_cart_custom_attribute_html', $data,
						$custom_att_swatches[$st_name]['swatch'],
						$custom_att_swatches[$st_name]['options'][$value]
					);
				} 

				$item_data[] = $data;
			}
		}

		// Filter item data to allow 3rd parties to add more to the array.
		$item_data = apply_filters( 'woocommerce_get_item_data', $item_data, $cart_item );

		// Format item data ready to display
		foreach ( $item_data as $key => $data ) {

			// Set hidden to true to not display meta on cart.
			if ( ! empty( $data['hidden'] ) ) {
				unset( $item_data[ $key ] );
				continue;
			}

			$item_data[ $key ]['key']     = ! empty( $data['key'] ) ? $data['key'] : $data['name'];
			$item_data[ $key ]['display'] = ! empty( $data['display'] ) ? $data['display'] : $data['value'];
		}

		if ( count( $item_data ) > 0 ) {

			$html = sprintf('<dl class="variation">');
				foreach( $item_data as $data ) {

					$html .= sprintf('<dt class="%1$s">%2$s</dt>',
						esc_attr( sanitize_html_class( 'variation-' . $data['key'] ) ),
						wp_kses_post( $data['key'] )
					);

					if( isset( $data['swatch']) ) {

						$html .= sprintf('<dd class="%1$s">%2$s</dd>',
							esc_attr( sanitize_html_class( 'variation-' . $data['key'] ) ),
							$data['swatch']							
						);
					} else {
						$html .= sprintf('<dd class="%1$s">%2$s</dd>',
							esc_attr( sanitize_html_class( 'variation-' . $data['key'] ) ),
							wp_kses_post( $data['display'] )
						);
					}
				}
			$html .= sprintf('</dl>');

			return $html;
		}
	}

	/**
	 * Print HTML of swatches on the frontend.
	 * Cart and Checkout Page
	 */
	public function cart_swatch_html( $data, $attribute_type, $term_id, $term_name ) {

		$custom_class = array(
			get_option( 'st_swatch_cart_chkout_swatch_size', 'st-swatch-size-tiny' ),
			get_option( 'st_swatch_cart_chkout_swatch_shape', 'st-swatch-shape-circle' ),
		);

		$custom_class = implode( ' ', array_filter( $custom_class ) );

		switch ( $attribute_type ) {

			case 'st-color-swatch':
				$color  = get_term_meta( $term_id, 'st-color-swatch', true );
				$swatch = sprintf('<span class="st-swatch-preview st-color-swatch %1$s">
						<span class="st-custom-attribute" style="background-color:%2$s"></span>
					</span>',
					esc_attr( $custom_class ),
					esc_attr( $color )
				);
				$data['swatch'] = $swatch;
			break;

			case 'st-label-swatch':
				$label  = get_term_meta( $term_id, 'st-label-swatch', true );
				$swatch = sprintf('<span class="st-swatch-preview st-label-swatch %1$s">
						<span class="st-custom-attribute"> %2$s </span>
					</span>',
					esc_attr( $custom_class ),
					esc_attr( $label )
				);
				$data['swatch'] = $swatch;				
			break;

			case 'st-image-swatch':
				$image  = get_term_meta( $term_id, 'st-image-swatch', true );
				$swatch = sprintf('<span class="st-swatch-preview st-image-swatch %1$s">
						<span class="st-custom-attribute"> %2$s </span>
					</span>',
					esc_attr( $custom_class ),
					( $image ) ? wp_get_attachment_image( $image ) : '<img src="'.esc_url( $this->plugin_dir_path_url . 'public/images/placeholder.png' ).'"/>'
				);
				$data['swatch'] = $swatch;				
			break;			
		}

		return $data;
	}

	/**
	 * Print HTML of swatches for custom attribute on the frontend.
	 * Cart and Checkout Page
	 */
	public function cart_custom_swatch_html( $data, $swatch, $option ) {

		$custom_class = array(
			get_option( 'st_swatch_cart_chkout_swatch_size', 'st-swatch-size-tiny' ),
			get_option( 'st_swatch_cart_chkout_swatch_shape', 'st-swatch-shape-circle' ),
		);

		$custom_class = implode( ' ', array_filter( $custom_class ) );
		$value        = $option['value'];

		switch( $swatch ) {

			case 'st-color-swatch':
				$swatch = sprintf('<span class="st-swatch-preview st-color-swatch %1$s">
						<span class="st-custom-attribute" style="background-color:%2$s"></span>
					</span>',
					esc_attr( $custom_class ),
					esc_attr( $value )
				);
				$data['swatch'] = $swatch;
			break;

			case 'st-label-swatch':
				$swatch = sprintf('<span class="st-swatch-preview st-label-swatch %1$s">
						<span class="st-custom-attribute"> %2$s </span>
					</span>',
					esc_attr( $custom_class ),
					esc_attr( $value )
				);
				$data['swatch'] = $swatch;				
			break;

			case 'st-image-swatch':
				$swatch = sprintf('<span class="st-swatch-preview st-image-swatch %1$s">
						<span class="st-custom-attribute"> %2$s </span>
					</span>',
					esc_attr( $custom_class ),
					( $value ) ? wp_get_attachment_image( $value ) : '<img src="'.esc_url( $this->plugin_dir_path_url . 'public/images/placeholder.png' ).'"/>'
				);
				$data['swatch'] = $swatch;				
			break;				
		}

		return $data;
	}

	/**
	 * UTF8 URL decode
	 *
	 */
	public static function utf8_urldecode( $str ) {

		$str = preg_replace( "/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode( $str ) );

		return html_entity_decode( $str, null, 'UTF-8' );
	}			
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.1.0
 * @package    St_Woo_Swatches
 * @author     SThemes <sthemes.envato@gmail.com>
 */
class St_Woo_Swatches extends St_Woo_Swatches_Base {


	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {

		parent::__construct();

		$this->set_locale();
		
		$this->load_dependencies();

		add_action( 'plugins_loaded', array( $this, 'extend_woo_customizer' ) );
	}

	/**
	 * The code that runs during plugin activation.
	 */
	public static function activate_plugin() {}

	/**
	 * The code that runs during plugin deactivation.
	 */
	public static function deactivate_plugin() {}

	/**
	 * Define the locale for this plugin for internationalization.
	 */
	private function set_locale() {		

		add_action( 'plugins_loaded', function() {
			
			load_plugin_textdomain( 'st-woo-swatches', false, dirname ( plugin_basename ( __FILE__ ) ) . '/languages/');
		});
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {

		if( !$this->check_requirement() ) {

			return;
		}

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		if( is_admin() ) {
			require_once $this->plugin_dir_path . 'admin/class-st-woo-swatches-admin.php';
			new St_Woo_Swatches_Admin();
		}

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once $this->plugin_dir_path . 'public/class-st-woo-swatches-public.php';
		new St_Woo_Swatches_Public();		
	}

	/**
	 * Define customizer options.
	 */
	public function extend_woo_customizer() {

		/**
		 * The class responsible for add options to the customizer for WooCommerce.
		 */
		if( current_user_can('edit_theme_options') ) {
			require_once $this->plugin_dir_path . 'admin/class-st-woo-customizer.php';
			new St_Woo_Customizer();
		}
	}

	/**
	 * Check whether basic provision reached.
	 */
	private function check_requirement() {

        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}			

			deactivate_plugins( $this->plugin_basename );

			add_action( 'shutdown', function() {

				/* translators: %s: html tags */
				$message = sprintf( __( 'The %1$sSThemes WooCommerce Swatch %2$s plugin requires %1$sWooCommerce%2$s plugin installed & activated.', 'st-woo-swatches' ), '<strong>', '</strong>' );

				if( $this->is_woocommerce_installed() && current_user_can( 'activate_plugins' ) ) {

					$button = sprintf( '<a href="%1$s" class="button-primary">%2$s</a>',
						wp_nonce_url( admin_url(
							add_query_arg( [ 
								'action'        => 'activate',
								'plugin'        => 'woocommerce/woocommerce.php',
								'plugin_status' => 'all',
								'paged'         => '1' ],
								'plugins.php'
							) ),
							'activate-plugin_woocommerce/woocommerce.php'
						),
						esc_html__( 'Activate WooCommerce', 'st-woo-swatches' )
					);
				} else if( !$this->is_woocommerce_installed() && current_user_can( 'install_plugins' ) ) {

					$button = sprintf( '<a href="%1$s" class="button-primary">%2$s</a>',
						wp_nonce_url( self_admin_url( 
							add_query_arg( [
								'action' => 'install-plugin',
								'plugin' => 'woocommerce' ],
								'update.php'
							) ),
							'install-plugin_woocommerce'
						),
						esc_html__( 'Install WooCommerce', 'st-woo-swatches' ) 
					);
				}

				printf( '<div class="error"> <p> %1$s </p> <p> %2$s </p> </div>', $message, $button );
			});

			return false;
		}

		return true;
	}

	/**
	 * Check whether WooCommerce plugin installed.
	 */
	private function is_woocommerce_installed() {

		$plugins = get_plugins();

		return isset( $plugins['woocommerce/woocommerce.php'] );
	}	
}

/**
 * Set the activation hook for a plugin.
 */
register_activation_hook( __FILE__, array( 'St_Woo_Swatches', 'activate_plugin' ) );

/**
 * Set the deactivation hook for a plugin.
 */
register_deactivation_hook( __FILE__, array( 'St_Woo_Swatches', 'deactivate_plugin' ) );

$st_woo_swatches = new St_Woo_Swatches();