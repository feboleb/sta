<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_CTPW_VERSION' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Custom_Thankyou_Page_Admin
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Your Inspiration Themes
 *
 */


if ( ! class_exists( 'YITH_Custom_Thankyou_Page_Admin' ) ) {
    /**
     * Class YITH_Custom_Thankyou_Page_Admin
     *
     * @author Armando Liccardo <armando.liccardo@yithemes.com>
     */
    class YITH_Custom_Thankyou_Page_Admin  {
        /**
         * @var Panel object
         */
        protected $_panel = null;


        /**
         * @var Panel page
         */
        protected $_panel_page = 'yith_ctpw_panel';

        /**
         * @var bool Show the premium landing page
         */
        public $show_premium_landing = true;

        /**
         * @var string Official plugin documentation
         */
        protected $_official_documentation = 'https://yithemes.com/docs-plugins/yith-custom-thank-you-page-for-woocommerce/' ;

        /**
         * @var string Official plugin landing page
         */
        protected $_premium_landing = 'https://yithemes.com/themes/plugins/yith-custom-thank-you-page-for-woocommerce/' ;

        /**
         * @var string Official plugin landing page
         */
        protected $_premium_live = 'https://plugins.yithemes.com/yith-custom-thank-you-page-for-woocommerce' ;

        /**
         * Construct
         *
         * @author  Armando Liccardo <armando.liccardo@yithemes.com>
         * @since 1.0
         */
        public function __construct(){
            /* === Register Panel Settings === */
            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

            /* === Show Plugin Information === */
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_CTPW_PATH . '/' . basename( YITH_CTPW_FILE ) ), array( $this, 'action_links' ) );
            add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

            /* === Premium Tab === */
            add_action( 'yith_ctpw_premium_tab', array( $this, 'show_premium_landing' ) );

        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Armando Liccardo <armando.liccardo@yithemes.com>
         * @use     /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function register_panel() {

            if ( ! empty( $this->_panel ) ) {
                return;
            }

            $menu_title = 'Custom Thank You Page';

            $admin_tabs = apply_filters( 'yith_ctpw_admin_tabs', array(
                    'settings'      => esc_html__( 'Settings', 'yith-custom-thank-you-page-for-woocommerce' ),
                )
            );

            if( $this->show_premium_landing ){
                $admin_tabs['premium'] = esc_html__( 'Premium Version', 'yith-custom-thank-you-page-for-woocommerce' );
            }


            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'plugin_slug'      => YITH_CTPW_SLUG,
                'page_title'       => $menu_title,
                'menu_title'       => $menu_title,
                'capability'       => 'manage_options',
                'parent'           => '',
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'admin-tabs'       => $admin_tabs,
                'class'            => yith_set_wrapper_class(),
                'options-path'     => YITH_CTPW_OPTIONS_PATH,
                'links'            => $this->get_sidebar_link()
            );


            /* === Fixed: not updated theme/old plugin framework  === */
            if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
                require_once( 'plugin-fw/lib/yit-plugin-panel-wc.php' );
            }

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
        }

        /**
         * Show the premium landing
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0.0
         * @return void
         */
        public function show_premium_landing(){

            if( file_exists( YITH_CTPW_PATH . 'premium/premium.php' )&& $this->show_premium_landing ){
                require_once( YITH_CTPW_PATH . 'premium/premium.php' );
            }
        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri() {
            return $this->_premium_landing;
        }

        /**
         * Get the panel page id
         *
         * @since   1.2.1
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_panel_page(){
            return $this->_panel_page;
        }

        /**
         * Sidebar links
         *
         * @return   array The links
         * @since    1.2.1
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function get_sidebar_link(){
            $links =  array(
                array(
                    'title' => esc_html__( 'Plugin documentation', 'yith-custom-thank-you-page-for-woocommerce' ),
                    'url'   => $this->_official_documentation,
                ),
                array(
                    'title' => esc_html__( 'Help Center', 'yith-custom-thank-you-page-for-woocommerce' ),
                    'url'   => 'http://support.yithemes.com/hc/en-us/categories/202568518-Plugins',
                ),
            );

            if( defined( 'YITH_CTPW_FREE_INIT' ) ){
                $links[] = array(
                    'title' => esc_html__( 'Discover the premium version', 'yith-custom-thank-you-page-for-woocommerce' ),
                    'url'   => $this->_premium_landing,
                );

                $links[] = array(
                    'title' => esc_html__( 'Free vs Premium', 'yith-custom-thank-you-page-for-woocommerce' ),
                    'url'   => 'https://yithemes.com/themes/plugins/yith-custom-thank-you-page-for-woocommerce/#tab-free_vs_premium_tab',
                );

                $links[] = array(
                    'title' => esc_html__( 'Premium live demo', 'yith-custom-thank-you-page-for-woocommerce' ),
                    'url'   => $this->_premium_live
                );

                $links[] =  array(
                    'title' => esc_html__( 'WordPress support forum', 'yith-custom-thank-you-page-for-woocommerce' ),
                    'url'   => 'https://wordpress.org/plugins/yith-custom-thank-you-page-for-woocommerce/',
                );

                $links[] =  array(
                    'title' => sprintf( '%s (%s %s)', esc_html__( 'Changelog', 'yith-custom-thank-you-page-for-woocommerce' ), esc_html__( 'current version','yith-custom-thank-you-page-for-woocommerce' ), YITH_CTPW_VERSION ),
                    'url'   => 'https://yithemes.com/docs-plugins/yith-custom-thank-you-page-for-woocommerce/06-changelog-free.html',
                );
            }

            if( defined( 'YITH_CTPW_PREMIUM' ) ){
                $links[] =  array(
                    'title' => esc_html__( 'Support platform', 'yith-custom-thank-you-page-for-woocommerce' ),
                    'url'   => 'https://yithemes.com/my-account/support/dashboard/',
                );

                $links[] =  array(
                    'title' => sprintf( '%s (%s %s)', esc_html__( 'Changelog', 'yith-custom-thank-you-page-for-woocommerce' ), esc_html__( 'current version','yith-custom-thank-you-page-for-woocommerce' ), YITH_CTPW_VERSION ),
                    'url'   => 'https://yithemes.com/docs-plugins/yith-custom-thank-you-page-for-woocommerce/07-changelog-premium.html',
                );
            }

            return $links;
        }


        public function action_links( $links ) {
            $links = yith_add_action_links( $links, $this->_panel_page, false );
            return $links;
        }

        public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_CTPW_FREE_INIT' ) {
            if ( defined( $init_file ) && constant( $init_file ) == $plugin_file ) {
                $new_row_meta_args['slug'] = YITH_CTPW_SLUG;
            }

            return $new_row_meta_args;
        }



    } //end class

}