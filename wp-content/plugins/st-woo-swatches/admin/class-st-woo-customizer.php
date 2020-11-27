<?php
/**
 * Extends options to the customizer for WooCommerce.
 * ?post_type=product
 *
 * @package    St_Woo_Swatches
 * @subpackage St_Woo_Swatches/admin/partials
 * @author     SThemes <sthemes.envato@gmail.com>
 */
if( !class_exists('WC_Shop_Customizer') ) {

	return;
}

class St_Woo_Customizer extends St_Woo_Swatches_Base {

	/**
	 * Available Swatch Sizes
	 */
	protected $st_swatch_sizes;

	/**
	 * Available Swatch Shapes
	 */
	protected $st_swatch_shapes;

	/**
	 * Available Swatch Hooks ( Position )
	 */
	protected $st_swatch_hooks;

	/**
	 * The admin directory path url of a plugin.
	 */
	protected $plugin_admin_dir_path_url;	


	public function __construct() {

		parent::__construct();

		$this->st_swatch_sizes = array(
			'st-swatch-size-tiny'   => esc_html__( 'Tiny', 'st-woo-swatches'),
			'st-swatch-size-small'  => esc_html__( 'Small', 'st-woo-swatches'),
			'st-swatch-size-medium' => esc_html__( 'Medium', 'st-woo-swatches'),
			'st-swatch-size-large'  => esc_html__( 'Large', 'st-woo-swatches'),
		);		

		$this->st_swatch_shapes = array(
			'st-swatch-shape-circle'         => esc_html__( 'Circle', 'st-woo-swatches'),
			'st-swatch-shape-square'         => esc_html__( 'Square', 'st-woo-swatches'),
			'st-swatch-shape-rounded-square' => esc_html__( 'Rounded Square', 'st-woo-swatches'),
		);

		$this->st_swatch_hooks = array(
			'st-after-loop-item-title'  => esc_html__( 'After Product Title', 'st-woo-swatches'),
			'st-before-loop-item-title' => esc_html__( 'Before Product Title', 'st-woo-swatches'),
			'use-st-swatch-shortcode'   => sprintf( 
				/* translators: %s: shortcode tag */
				esc_html__( 'Use Shortcode %1$s', 'st-woo-swatches'),
				'[st-swatch-shortcode]'
			),
		);

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customizer_css' ) );

		add_action( 'customize_register', array( $this, 'add_sections' ), 100 );
		add_action( 'customize_preview_init', array( $this, 'customize_preview_js' ) );
	}

	function customizer_css() {

		wp_enqueue_style( $this->plugin_name.'-customizer-css', plugins_url ( 'st-woo-swatches/admin/css/customizer.css' ), array(), $this->version, 'all' );
	}

	/**
	 * Add settings to the customizer.
	 *
	 */
	public function add_sections( $wp_customize ) {

		$this->add_product_catalog_section( $wp_customize );
		$this->add_product_single_section( $wp_customize );
		$this->add_cart_and_chkout_section( $wp_customize );
		$this->add_tooltip_section( $wp_customize );
	}

	/**
	 * Bind JS handlers to instantly live-preview changes.
	 * 
	 */
	public function customize_preview_js() {
		
		wp_enqueue_script( $this->plugin_name.'-customizer', plugins_url ( 'st-woo-swatches/admin/js/customize-preview.js' ), array( 'customize-preview' ), $this->version, false );
	}

	/**
	 * Single Product section.
	 *
	 */
	public function add_product_catalog_section( $wp_customize ) {

		// Shop Page
			$wp_customize->get_control('woocommerce_shop_page_display')->priority = 1;

			$wp_customize->add_setting(
				'st_swatch_shop_swatch_hook',
				array(
					'default'           => 'st-before-loop-item-title',
					'type'              => 'option',
					'capability'        => 'manage_woocommerce',
					'sanitize_callback' => array( $this, 'sanitize_swatch_hook' ),
				)
			);

			$wp_customize->add_control(
				'st_swatch_shop_swatch_hook',
				array(
					'label'           => esc_html__( 'Swatch position', 'st-woo-swatches' ),
					'priority'        => 2,	
					'description'     => esc_html__( 'Choose swatch position to display on the shop page.', 'st-woo-swatches' ),
					'section'         => 'woocommerce_product_catalog',
					'settings'        => 'st_swatch_shop_swatch_hook',
					'type'            => 'select',
					'choices'         => apply_filters( 'sten_wc_default_swatch_hooks', $this->st_swatch_hooks ),
					'active_callback' => array( $this, 'st_swatch_shop_page_callback' ),
				)
			);						

			$wp_customize->add_setting(
				'st_swatch_shop_swatch_size',
				array(
					'default'           => 'st-swatch-size-small',
					'type'              => 'option',
					'capability'        => 'manage_woocommerce',
					'transport'         => 'postMessage',
					'sanitize_callback' => array( $this, 'sanitize_swatch_size' ),
				)
			);

			$wp_customize->add_control(
				'st_swatch_shop_swatch_size',
				array(
					'label'           => esc_html__( 'Swatch size', 'st-woo-swatches' ),
					'priority'        => 3,	
					'description'     => esc_html__( 'Choose what size of swatch to display on the shop page.', 'st-woo-swatches' ),
					'section'         => 'woocommerce_product_catalog',
					'settings'        => 'st_swatch_shop_swatch_size',
					'type'            => 'select',
					'choices'         => apply_filters( 'sten_wc_default_swatch_sizes', $this->st_swatch_sizes ),
					'active_callback' => array( $this, 'st_swatch_shop_page_callback' ),
				)
			);

			$wp_customize->add_setting(
				'st_swatch_shop_swatch_shape',
				array(
					'default'           => 'st-swatch-shape-circle',
					'type'              => 'option',
					'capability'        => 'manage_woocommerce',
					'transport'         => 'postMessage',
					'sanitize_callback' => array( $this, 'sanitize_swatch_style' ),					
				)
			);

			$wp_customize->add_control(
				'st_swatch_shop_swatch_shape',
				array(
					'label'           => esc_html__( 'Swatch shape', 'st-woo-swatches' ),
					'priority'        => 4,
					'description'     => esc_html__( 'Choose what shape of swatch to display on the shop page.', 'st-woo-swatches' ),
					'section'         => 'woocommerce_product_catalog',
					'settings'        => 'st_swatch_shop_swatch_shape',
					'type'            => 'select',
					'choices'         => apply_filters( 'sten_wc_default_swatch_styles', $this->st_swatch_shapes ),
					'active_callback' => array( $this, 'st_swatch_shop_page_callback' ),
				)
			);

			$wp_customize->add_setting(
				'st_swatch_shop_swatch_border_color',
				array(
					'default'   => '#dddddd',
					'type'      => 'option',
					'transport' => 'postMessage'
				)
			);

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'st_swatch_shop_swatch_border_color',
				array(
					'label'           => esc_html__( 'Swatch border color', 'st-woo-swatches' ),
					'priority'        => 5,
					'section'         => 'woocommerce_product_catalog',
					'settings'        => 'st_swatch_shop_swatch_border_color',
					'type'            => 'color',
					'mode'            => 'full',
					'active_callback' => array( $this, 'st_swatch_shop_page_callback' ),					
				)
			) );

			$wp_customize->add_setting(
				'st_swatch_shop_swatch_active_border_color',
				array(
					'default'   => '#ff0000',
					'type'      => 'option',
					'transport' => 'postMessage'
				)
			);

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'st_swatch_shop_swatch_active_border_color',
				array(
					'label'           => esc_html__( 'Swatch active border color', 'st-woo-swatches' ),
					'priority'        => 6,
					'section'         => 'woocommerce_product_catalog',
					'settings'        => 'st_swatch_shop_swatch_active_border_color',
					'type'            => 'color',
					'mode'            => 'full',
					'active_callback' => array( $this, 'st_swatch_shop_page_callback' ),
				)
			) );											

		// Archive Page
			$wp_customize->get_control('woocommerce_category_archive_display')->priority = 10;
			$wp_customize->get_control('woocommerce_default_catalog_orderby')->priority  = 20;

			/**
			 * To provide compatiblility for flatsome theme
			 */
			$wc_catalog_col_controls = $wp_customize->get_control('woocommerce_catalog_columns');
			$wc_catalog_row_controls = $wp_customize->get_control('woocommerce_catalog_rows');

			if( !is_null( $wc_catalog_col_controls ) ) {
				$wp_customize->get_control('woocommerce_catalog_columns')->priority = 30;
			}

			if( !is_null( $wc_catalog_col_controls ) ) {
				$wp_customize->get_control('woocommerce_catalog_rows')->priority    = 40;				
			}


			$wp_customize->add_setting(
				'st_swatch_archive_swatch_hook',
				array(
					'default'           => 'st-before-loop-item-title',
					'type'              => 'option',
					'capability'        => 'manage_woocommerce',
					'sanitize_callback' => array( $this, 'sanitize_swatch_hook' ),
				)
			);

			$wp_customize->add_control(
				'st_swatch_archive_swatch_hook',
				array(
					'label'           => esc_html__( 'Swatch position', 'st-woo-swatches' ),
					'priority'        => 11,	
					'description'     => esc_html__( 'Choose swatch position to display on the archive page.', 'st-woo-swatches' ),
					'section'         => 'woocommerce_product_catalog',
					'settings'        => 'st_swatch_archive_swatch_hook',
					'type'            => 'select',
					'choices'         => apply_filters( 'sten_wc_default_swatch_hooks', $this->st_swatch_hooks ),
					'active_callback' => array( $this, 'st_swatch_woo_archive_page_callback' ),
				)
			);				

			$wp_customize->add_setting(
				'st_swatch_archive_swatch_size',
				array(
					'default'           => 'st-swatch-size-small',
					'type'              => 'option',
					'capability'        => 'manage_woocommerce',
					'transport'         => 'postMessage',
					'sanitize_callback' => array( $this, 'sanitize_swatch_size' ),
				)
			);

			$wp_customize->add_control(
				'st_swatch_archive_swatch_size',
				array(
					'label'           => esc_html__( 'Swatch size', 'st-woo-swatches' ),
					'priority'        => 12,	
					'description'     => esc_html__( 'Choose what size of swatch to display on the archive page.', 'st-woo-swatches' ),
					'section'         => 'woocommerce_product_catalog',
					'settings'        => 'st_swatch_archive_swatch_size',
					'type'            => 'select',
					'choices'         => apply_filters( 'sten_wc_default_swatch_sizes', $this->st_swatch_sizes ),
					'active_callback' => array( $this, 'st_swatch_woo_archive_page_callback' ),
				)
			);

			$wp_customize->add_setting(
				'st_swatch_archive_swatch_shape',
				array(
					'default'           => 'st-swatch-shape-circle',
					'type'              => 'option',
					'capability'        => 'manage_woocommerce',
					'transport'         => 'postMessage',
					'sanitize_callback' => array( $this, 'sanitize_swatch_style' ),					
				)
			);

			$wp_customize->add_control(
				'st_swatch_archive_swatch_shape',
				array(
					'label'           => esc_html__( 'Swatch shape', 'st-woo-swatches' ),
					'priority'        => 13,
					'description'     => esc_html__( 'Choose what shape of swatch to display on the archive page.', 'st-woo-swatches' ),
					'section'         => 'woocommerce_product_catalog',
					'settings'        => 'st_swatch_archive_swatch_shape',
					'type'            => 'select',
					'choices'         => apply_filters( 'sten_wc_default_swatch_styles', $this->st_swatch_shapes ),
					'active_callback' => array( $this, 'st_swatch_woo_archive_page_callback' ),
				)
			);

			$wp_customize->add_setting(
				'st_swatch_archive_swatch_border_color',
				array(
					'default'   => '#dddddd',
					'type'      => 'option',
					'transport' => 'postMessage'
				)
			);

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'st_swatch_archive_swatch_border_color',
				array(
					'label'           => esc_html__( 'Swatch border color', 'st-woo-swatches' ),
					'priority'        => 14,
					'section'         => 'woocommerce_product_catalog',
					'settings'        => 'st_swatch_archive_swatch_border_color',
					'type'            => 'color',
					'mode'            => 'full',
					'active_callback' => array( $this, 'st_swatch_woo_archive_page_callback' ),
				)
			) );

			$wp_customize->add_setting(
				'st_swatch_archive_swatch_active_border_color',
				array(
					'default'   => '#ff0000',
					'type'      => 'option',
					'transport' => 'postMessage'
				)
			);

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'st_swatch_archive_swatch_active_border_color',
				array(
					'label'           => esc_html__( 'Swatch active border color', 'st-woo-swatches' ),
					'priority'        => 15,
					'section'         => 'woocommerce_product_catalog',
					'settings'        => 'st_swatch_archive_swatch_active_border_color',
					'type'            => 'color',
					'mode'            => 'full',
					'active_callback' => array( $this, 'st_swatch_woo_archive_page_callback' ),
				)
			) );

			$wp_customize->add_setting(
				'st_swatch_one_swatch_mode',
				array(
					'default'              => 'no',
					'type'                 => 'option',
					'sanitize_callback'    => 'wc_bool_to_string',
					'sanitize_js_callback' => 'wc_string_to_bool',
				)
			);

			$wp_customize->add_control(
				'st_swatch_one_swatch_mode',
				array(
					'label'       => esc_html__( 'Show only one swatch on Product Catalog', 'st-woo-swatches' ),
					'priority'    => 16,
					'description' => esc_html__( "This option lets you to show only one swatch attribute in product catalog and archive pages", 'st-woo-swatches' ),
					'section'     => 'woocommerce_product_catalog',
					'settings'    => 'st_swatch_one_swatch_mode',
					'type'        => 'checkbox',
				)
			);

			$wp_customize->add_setting(
				'st_swatch_one_swatch_mode_attribute',
				array(
					'type'       => 'option',
					'capability' => 'manage_woocommerce',
				)
			);

			$wp_customize->add_control(
				'st_swatch_one_swatch_mode_attribute',
				array(
					'label'       => esc_html__( 'Product Attribute', 'st-woo-swatches' ),
					'description' => esc_html__( 'Choose what product attribute to display on product catalog and archive pages', 'st-woo-swatches' ),
					'priority'    => 17,
					'section'     => 'woocommerce_product_catalog',
					'settings'    => 'st_swatch_one_swatch_mode_attribute',
					'type'        => 'select',
					'choices'     => $this->product_attributes()
				)
			);
	}	

	/**
	 * Single Product section.
	 *
	 */
	public function add_product_single_section( $wp_customize ) {

		$wp_customize->add_section(
			'st_swatch_product_single',
			array(
				'title'    => esc_html__( 'Single Product Swatch', 'st-woo-swatches' ),
				'priority' => 30,
				'panel'    => 'woocommerce',
			)
		);

			$wp_customize->add_setting(
				'st_swatch_product_single_swatch_size',
				array(
					'default'           => 'st-swatch-size-small',
					'type'              => 'option',
					'capability'        => 'manage_woocommerce',
					'transport'         => 'postMessage',
					'sanitize_callback' => array( $this, 'sanitize_swatch_size' ),
				)
			);

			$wp_customize->add_control(
				'st_swatch_product_single_swatch_size',
				array(
					'label'       => esc_html__( 'Swatch size', 'st-woo-swatches' ),
					'description' => esc_html__( 'Choose what size of swatch to display on the single product page.', 'st-woo-swatches' ),
					'section'     => 'st_swatch_product_single',
					'settings'    => 'st_swatch_product_single_swatch_size',
					'type'        => 'select',
					'choices'     => apply_filters( 'sten_wc_default_swatch_sizes', $this->st_swatch_sizes )
				)
			);

			$wp_customize->add_setting(
				'st_swatch_product_single_swatch_shape',
				array(
					'default'           => 'st-swatch-shape-circle',
					'type'              => 'option',
					'capability'        => 'manage_woocommerce',
					'transport'         => 'postMessage',
					'sanitize_callback' => array( $this, 'sanitize_swatch_style' ),					
				)
			);

			$wp_customize->add_control(
				'st_swatch_product_single_swatch_shape',
				array(
					'label'       => esc_html__( 'Swatch shape', 'st-woo-swatches' ),
					'description' => esc_html__( 'Choose what shape of swatch to display on the single product page.', 'st-woo-swatches' ),
					'section'     => 'st_swatch_product_single',
					'settings'    => 'st_swatch_product_single_swatch_shape',
					'type'        => 'select',
					'choices'     => apply_filters( 'sten_wc_default_swatch_styles', $this->st_swatch_shapes )
				)
			);

			$wp_customize->add_setting(
				'st_swatch_product_single_swatch_border_color',
				array(
					'default'   => '#dddddd',
					'type'      => 'option',
					'transport' => 'postMessage'
				)
			);

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'st_swatch_product_single_swatch_border_color',
				array(
					'label'    => esc_html__( 'Swatch border color', 'st-woo-swatches' ),
					'section'  => 'st_swatch_product_single',
					'settings' => 'st_swatch_product_single_swatch_border_color',
					'type'     => 'color',
					'mode'     => 'full',
				)
			) );

			$wp_customize->add_setting(
				'st_swatch_product_single_swatch_active_border_color',
				array(
					'default'   => '#ff0000',
					'type'      => 'option',
					'transport' => 'postMessage'
				)
			);

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'st_swatch_product_single_swatch_active_border_color',
				array(
					'label'    => esc_html__( 'Swatch active border color', 'st-woo-swatches' ),
					'section'  => 'st_swatch_product_single',
					'settings' => 'st_swatch_product_single_swatch_active_border_color',
					'type'     => 'color',
					'mode'     => 'full',
				)
			) );			
	}

	/**
	 * Cart and Checkout Section
	 */
	public function add_cart_and_chkout_section( $wp_customize ) {

		$wp_customize->add_section(
			'st_swatch_cart_chkout',
			array(
				'title'    => esc_html__( 'Cart And Checkout Swatch', 'st-woo-swatches' ),
				'priority' => 40,
				'panel'    => 'woocommerce',
			)
		);
			$wp_customize->add_setting(
				'st_swatch_cart_chkout_enable',
				array(
					'default'              => 'yes',
					'type'                 => 'option',
					'sanitize_callback'    => 'wc_bool_to_string',
					'sanitize_js_callback' => 'wc_string_to_bool',
				)
			);

			$wp_customize->add_control(
				'st_swatch_cart_chkout_enable',
				array(
					'label'       => esc_html__( 'Enable Swatch', 'st-woo-swatches' ),
					'description' => esc_html__( "Enable / Disable swatch in cart and chekcout pages.", 'st-woo-swatches' ),
					'section'     => 'st_swatch_cart_chkout',
					'settings'    => 'st_swatch_cart_chkout_enable',
					'type'        => 'checkbox',
				)
			);		

			$wp_customize->add_setting(
				'st_swatch_cart_chkout_swatch_size',
				array(
					'default'           => 'st-swatch-size-tiny',
					'type'              => 'option',
					'capability'        => 'manage_woocommerce',
					'transport'         => 'postMessage',
					'sanitize_callback' => array( $this, 'sanitize_swatch_size' ),
				)
			);

			$wp_customize->add_control(
				'st_swatch_cart_chkout_swatch_size',
				array(
					'label'       => esc_html__( 'Swatch size', 'st-woo-swatches' ),
					'description' => esc_html__( 'Choose what size of swatch to display on the cart and checkout pages.', 'st-woo-swatches' ),
					'section'     => 'st_swatch_cart_chkout',
					'settings'    => 'st_swatch_cart_chkout_swatch_size',
					'type'        => 'select',
					'choices'     => apply_filters( 'sten_wc_default_swatch_sizes', $this->st_swatch_sizes )
				)
			);

			$wp_customize->add_setting(
				'st_swatch_cart_chkout_swatch_shape',
				array(
					'default'           => 'st-swatch-shape-circle',
					'type'              => 'option',
					'capability'        => 'manage_woocommerce',
					'transport'         => 'postMessage',
					'sanitize_callback' => array( $this, 'sanitize_swatch_style' ),					
				)
			);

			$wp_customize->add_control(
				'st_swatch_cart_chkout_swatch_shape',
				array(
					'label'       => esc_html__( 'Swatch shape', 'st-woo-swatches' ),
					'description' => esc_html__( 'Choose what shape of swatch to display on the cart and checkout pages.', 'st-woo-swatches' ),
					'section'     => 'st_swatch_cart_chkout',
					'settings'    => 'st_swatch_cart_chkout_swatch_shape',
					'type'        => 'select',
					'choices'     => apply_filters( 'sten_wc_default_swatch_styles', $this->st_swatch_shapes )
				)
			);

			$wp_customize->add_setting(
				'st_swatch_cart_chkout_swatch_border_color',
				array(
					'default'   => '#dddddd',
					'type'      => 'option',
					'transport' => 'postMessage'
				)
			);

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'st_swatch_cart_chkout_swatch_border_color',
				array(
					'label'    => esc_html__( 'Swatch border color', 'st-woo-swatches' ),
					'section'  => 'st_swatch_cart_chkout',
					'settings' => 'st_swatch_cart_chkout_swatch_border_color',
					'type'     => 'color',
					'mode'     => 'full',
				)
			) );
	}

	/**
	 * Tooltip Section
	 */
	public function add_tooltip_section( $wp_customize ) {

		$wp_customize->add_section(
			'st_swatch_tooltip',
			array(
				'title'    => esc_html__( "Swatch's Tooltip", 'st-woo-swatches' ),
				'priority' => 50,
				'panel'    => 'woocommerce',
			)
		);

			$wp_customize->add_setting(
				'st_swatch_tooltip_enable',
				array(
					'default'              => 'no',
					'type'                 => 'option',
					'sanitize_callback'    => 'wc_bool_to_string',
					'sanitize_js_callback' => 'wc_string_to_bool',
				)
			);

			$wp_customize->add_control(
				'st_swatch_tooltip_enable',
				array(
					'label'       => esc_html__( 'Enable Tooltip', 'st-woo-swatches' ),
					'description' => esc_html__( "Enable / Disable swatch's tooltip", 'st-woo-swatches' ),
					'section'     => 'st_swatch_tooltip',
					'settings'    => 'st_swatch_tooltip_enable',
					'type'        => 'checkbox',
				)
			);

			$wp_customize->add_setting(
				'st_swatch_tooltip_bg_color',
				array(
					'default'   => '#666666',
					'type'      => 'option',
					'transport' => 'postMessage'
				)
			);

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'st_swatch_tooltip_bg_color',
				array(
					'label'    => esc_html__( 'Tooltip Background color', 'st-woo-swatches' ),
					'section'  => 'st_swatch_tooltip',
					'settings' => 'st_swatch_tooltip_bg_color',
					'type'     => 'color',
					'mode'     => 'full',
				)
			) );

			$wp_customize->add_setting(
				'st_swatch_tooltip_txt_color',
				array(
					'default'   => '#ffffff',
					'type'      => 'option',
					'transport' => 'postMessage'
				)
			);

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'st_swatch_tooltip_txt_color',
				array(
					'label'    => esc_html__( 'Tooltip Text color', 'st-woo-swatches' ),
					'section'  => 'st_swatch_tooltip',
					'settings' => 'st_swatch_tooltip_txt_color',
					'type'     => 'color',
					'mode'     => 'full',
				)
			) );						
	}

	public function sanitize_swatch_hook( $value ) {

		$options = apply_filters( 'sten_wc_default_swatch_hooks', $this->st_swatch_hooks );

		return array_key_exists( $value, $options ) ? $value : 'st-before-loop-item-title';
	}

	public function sanitize_swatch_size( $value ) {

		$options = apply_filters( 'sten_wc_default_swatch_sizes', $this->st_swatch_sizes );

		return array_key_exists( $value, $options ) ? $value : 'small';
	}	

	public function sanitize_swatch_style( $value ) {

		$options = apply_filters( 'sten_wc_default_swatch_styles', $this->st_swatch_shapes );

		return array_key_exists( $value, $options ) ? $value : 'small-circle';
	}

	public function product_attributes() {

		$list                 = array();
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		foreach( $attribute_taxonomies as $taxonomy ){

			if( array_key_exists( $taxonomy->attribute_type,  $this->attribute_types ) ) {	

				$name =  wc_attribute_taxonomy_name( $taxonomy->attribute_name );
				$list[ $name ] = $taxonomy->attribute_label;
			}
		}

		$list = array( '' => esc_html__('Choose Custom Attribute', 'st-woo-swatches')  ) + $list;

		return $list;
	}

	public function st_swatch_shop_page_callback() {

		$display_type = get_option( 'woocommerce_shop_page_display', '' );

		return in_array( $display_type, array('', 'both') ) ? true : false;
	}

	public function st_swatch_woo_archive_page_callback() {

		$display_type = get_option( 'woocommerce_category_archive_display', '' );

		return in_array( $display_type, array('', 'both') ) ? true : false;
	}
}