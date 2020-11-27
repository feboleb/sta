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
 *
 * @class      YITH_Custom_Thankyou_Page_Frontend
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Your Inspiration Themes
 *
 */


if ( ! class_exists( 'YITH_Custom_Thankyou_Page_Frontend' ) ) {
    /**
     * Class YITH_Custom_Thankyou_Page_Frontend
     *
     * @author Armando Liccardo <armando.liccardo@yithemes.com>
     */
    class YITH_Custom_Thankyou_Page_Frontend  {
        /* wc version */
        public $yith_ctw_wc_version = '';

        /* general page */
        public $ctpw_general_page = '';

        /**
         * Construct
         *
         * @author Armando Liccardo <armando.liccardo@yithemes.com>
         * @since 1.0
         */
        public function __construct(){

            //register the style
            //the file is loaded by the function yith_custom_thankyou_page so it load only on custom thank you page
            wp_register_style('yith-ctpw-style',YITH_CTPW_ASSETS_URL . 'css/style.css',null,true,'all');

            //get general page id
            $this->ctpw_general_page = (get_option('yith_ctpw_general_page')) ? get_option('yith_ctpw_general_page') : 0;

            //set the thank you page redirect
            add_action('woocommerce_thankyou', array($this, 'yith_ctpw_redirect_after_purchase'));

            //Add the content and body class filters only after the redirect so we are on custom thank you page for sure
            if (isset($_GET['order']) && isset($_GET['key']) && isset($_GET['ctpw'])) {
                //set the_content filter to customize the page if it is selected as thank you page
                add_filter('the_content', array($this, 'yith_custom_thankyou_page'));
                add_filter('body_class',array($this,'body_class_front'));
            }

            //add woocommerce parts: header, order table, customer details
            if (apply_filters('yith_ctpw_show_header_filter',true)) add_action('yith_ctpw_successful_ac', array($this, 'yith_ctpw_header'), 10);
            if (apply_filters('yith_ctpw_show_table_filter',true)) add_action('yith_ctpw_successful_ac', array($this, 'yith_ctpw_table'), 20);
            if (apply_filters('yith_ctpw_show_details_filter',true)) add_action('yith_ctpw_successful_ac', array($this, 'yith_ctpw_customer_details'), 30);

            //load the failed template
            add_action('yith_ctpw_failed_ac', array($this, 'yith_ctpw_failed'), 10);

            //if ctpw page we return true to wc checkout filters
            if ( isset($_GET['ctpw']) && isset($_GET['order']) && isset($_GET['key']) ) {
                add_filter('woocommerce_is_order_received_page', array($this, 'yctpw_is_order_recevide_page'), 99);
                add_filter('woocommerce_is_checkout',array($this, 'yctpw_woocommerce_is_checkout'), 99);
            }

        }

        /**
         * Redirect Function
         *
         * @author Armando Liccardo <armando.liccardo@yithemes.com>
         * @since 1.0
         *
         * param wc $order
         *
         */
        public function yith_ctpw_redirect_after_purchase($order)
        {


             //if no global custom thank you page is set or no single product custom thank you page is set, not redirect needed
            if ($this->ctpw_general_page != 0 ) {
                //get order object
                $check_order = wc_get_order(intval($order));

                             $thankyoupage = get_permalink(get_option('yith_ctpw_general_page'));
                              $selected_thankyou_page = get_option('yith_ctpw_general_page');

               //making the url redirect
                $order_key = wc_clean($_GET['key']);
                $redirect = $thankyoupage;
                $redirect .= get_option('permalink_structure') === '' ? '&' : '?';
                $redirect .= 'order=' . absint($order) . '&key=' . $order_key . '&ctpw=' . $selected_thankyou_page;

                wp_redirect($redirect);

            }
        }

        /**
         * Custom ThankYou Page Filter function
         *
         * @author Armando Liccardo <armando.liccardo@yithemes.com>
         * @since 1.0
         *
         * param $content
         *
         * filter the wp content and if it is the custom selected page add the templates
         *
         */
        public function yith_custom_thankyou_page($content)
        {


            // check if the order ID exists and if is set the order key
            if (!isset($_GET['order']) || !isset($_GET['key'])) {
                return $content;
            }

            //get order object; intval() ensures that we use an integer value for the order ID
            $order = wc_get_order(intval($_GET['order']));

            //order exists check the order key passed is isset and if it is the same of the order
            $ctpw_order_key = $order->get_order_key();


            if (isset($_GET['key']) && $_GET['key'] != $ctpw_order_key ) {
                return $content;
            }

            // check if the custom thank yuo page ID exists
            if (!isset($_GET['ctpw'])) {
                return $content;
            }

            // Check if is the correct page: general thankyou or product related custom thankyou page
            if ( !is_page($_GET['ctpw']) ) {
                return $content;
            }

            //load the plugin style
            wp_enqueue_style('yith-ctpw-style');

            ob_start();

            // Check that the order is valid
            if (!$order) {
                // The order can't be returned by WooCommerce - Just say thank you
                ?>
                <p><?php echo apply_filters('woocommerce_thankyou_order_received_text', esc_html__('Thank you. Your order has been received.', 'woocommerce'), null); ?></p><?php
            } else {
                if ($order->has_status('failed')) {
                    // Order failed - Print error messages and ask to pay again
                    /**
                     * @hooked wc_custom_thankyou_failed - 10
                     */
                    do_action('yith_ctpw_failed_ac', $order);
                } else {
                    // The order is successfull - print section as selected in admin settings
                    /**
                     * @hooked yith_ctpw_header - 10
                     * @hooked yith_ctpw_table - 20
                     * @hooked yith_ctpw_customer_details - 30
                     * #hooked yith_ctpw_social_box - 40
                     */
                    do_action('yith_ctpw_successful_ac', $order);

                }

            }



            $content .= ob_get_contents();
            ob_end_clean();

            //if there's custom style we add it to the page
            if (get_option('yith_ctpw_custom_style') != '') {
                $content .= '<style>' . esc_html(get_option('yith_ctpw_custom_style')) . '</style>';
            }



            return $content;
        }
        //end content filter function


        //load header template
        public function yith_ctpw_header($order)
        {
             include(YITH_CTPW_TEMPLATE_PATH . 'woocommerce/yith_ctpw_header.php');
        }

        //load table order template
        public function yith_ctpw_table($order)
        {
             include(YITH_CTPW_TEMPLATE_PATH . 'woocommerce/yith_ctpw_table.php');
        }

        //load customer details template
        public function yith_ctpw_customer_details($order)
        {
            include(YITH_CTPW_TEMPLATE_PATH . 'woocommerce/yith_ctpw_customer_details.php');
        }

        //load failed template
        public function yith_ctpw_failed($order)
        {
            include(YITH_CTPW_TEMPLATE_PATH . 'woocommerce/yith_ctpw_failed.php');
        }

        /**
         * pass true value to wc filter woocommerce_is_order_received_page
         *
         * @author Armando Liccardo <armando.liccardo@yithemes.com>
         * @since 1.1.3
         * @return boolean
         */
        public function yctpw_is_order_recevide_page() {
            return true;
        }
        /**
         * pass true value to wc filter woocommerce_is_checkout
         *
         * @author Armando Liccardo <armando.liccardo@yithemes.com>
         * @since 1.1.3
         * @return boolean
         */
        public function yctpw_woocommerce_is_checkout() {
            return true;
        }

        /**
         * Add a body class(es)
         *
         * @param $classes The classes array
         *
         * @author Armando Liccardo <armando.liccardo@yithemes.com>
         * @since 1.0
         * @return array
         */
        public function body_class_front( $classes ){
            $ctpw_classes = array('yith-ctpw-front', 'woocommerce');
            $classes = array_merge($classes,$ctpw_classes);
            return $classes;
        }
    } //end class

}