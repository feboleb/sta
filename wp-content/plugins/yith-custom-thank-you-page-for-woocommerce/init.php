<?php
/*
* Plugin Name: YITH Custom Thank You Page for Woocommerce
* Plugin URI: https://yithemes.com/themes/plugins/yith-custom-thank-you-page-for-woocommerce
* Description: The <code><strong>YITH Custom ThankYou Page for Woocommerce</strong></code> shows a Thank You page to users buying in your shop. The page can be easily customized. Excellent to loyalize customers and show marketing campaigns after the purchase! <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce on <strong>YITH</strong></a>
* Author: YITH
* Text Domain: yith-custom-thank-you-page-for-woocommerce
* Domain Path: /languages
* Version: 1.3.1
* Author URI: https://yithemes.com/
* WC requires at least: 3.8
* WC tested up to: 4.7
*/

/*  Copyright 2013-2015  Your Inspiration Themes  (email : plugins@yithemes.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined ( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( ! function_exists( 'yith_ctpw_woocommerce_admin_notice' ) ) {
    /**
     * Show notice if WooCommerce is not active
     *
     * @author Armando Liccardo
     * @since  1.0.0
     */
    function yith_ctpw_woocommerce_admin_notice() {
        ?>
        <div class="error">

            <p><?php _e( 'YITH Custom Thank You page for Woocommerce is enabled but not effective. It requires WooCommerce in order to work.', 'yith-custom-thankyou-page-for-woocommerce' ); ?></p>
        </div>
    <?php
    }
}

/* === DEFINE === */
! defined( 'YITH_CTPW_VERSION' )            && define( 'YITH_CTPW_VERSION', '1.3.1' );
! defined( 'YITH_CTPW_FREE_INIT' )          && define( 'YITH_CTPW_FREE_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_CTPW_INIT' )               && define( 'YITH_CTPW_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_CTPW_SLUG' )               && define( 'YITH_CTPW_SLUG', 'yith-custom-thank-you-page-for-woocommerce' );
! defined( 'YITH_CTPW_FILE' )               && define( 'YITH_CTPW_FILE', __FILE__ );
! defined( 'YITH_CTPW_PATH' )               && define( 'YITH_CTPW_PATH', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_CTPW_URL' )                && define( 'YITH_CTPW_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_CTPW_ASSETS_URL' )         && define( 'YITH_CTPW_ASSETS_URL', YITH_CTPW_URL . 'assets/' );
! defined( 'YITH_CTPW_TEMPLATE_PATH' )      && define( 'YITH_CTPW_TEMPLATE_PATH', YITH_CTPW_PATH . 'templates/' );
! defined( 'YITH_CTPW_OPTIONS_PATH' )       && define( 'YITH_CTPW_OPTIONS_PATH', YITH_CTPW_PATH . 'panel' );


/* Plugin Framework Version Check */
if( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_CTPW_PATH . 'plugin-fw/init.php' ) ) {
    require_once( YITH_CTPW_PATH . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( YITH_CTPW_PATH  );

/* Load text domain */
load_plugin_textdomain( 'yith-custom-thankyou-page-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


if ( ! function_exists( 'YITH_Custom_Thankyou_page' ) ) {
    /**
     * Unique access to instance of YITH_Custom_Thankyou_Page
     *
     * @return YITH_Custom_Thankyou_Page|YITH_Custom_Thankyou_Page_Premium
     * @since 1.0.0
     */
    function YITH_Custom_Thankyou_Page() {
        // Load required classes and functions
        require_once( YITH_CTPW_PATH . 'includes/class.yith-custom-thankyou-page.php' );

        if ( defined( 'YITH_CTPW_PREMIUM' ) && file_exists( YITH_CTPW_PATH . 'includes/class.yith-custom-thankyou-page-premium.php' ) ) {
            require_once( YITH_CTPW_PATH . 'includes/class.yith-custom-thankyou-page-premium.php' );
            return YITH_Custom_Thankyou_Page_Premium::instance();
        }

        return YITH_Custom_Thankyou_Page::instance();
    }
}

if( ! function_exists( 'yith_ctpw_start' ) ){
    function yith_ctpw_start() {

        if ( ! function_exists( 'WC' ) ) {
            add_action( 'admin_notices', 'yith_ctpw_woocommerce_admin_notice' );
        }

        else {
            /**
             * Instance main plugin class
             */
            YITH_Custom_Thankyou_Page();
        }
    }
}
add_action( 'plugins_loaded', 'yith_ctpw_start', 11 );

