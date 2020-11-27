    <h2 class="customer_details"><?php echo apply_filters('ctpw_customer_details_title', esc_html__( 'Customer details', 'yith-custom-thank-you-page-for-woocommerce' ) ); ?></h2>
<ul class="customer_details">
    <?php
    if ( $order->get_billing_email() ) echo '<li><p>' . esc_html__( 'Email:', 'yith-custom-thank-you-page-for-woocommerce' ) . '</p><span> ' . $order->get_billing_email() . '</span></li>';
    if ( $order->get_billing_phone() ) echo '<li><p>' . esc_html__( 'Telephone:', 'yith-custom-thank-you-page-for-woocommerce' ) . '</p><span> ' . $order->get_billing_phone() . '</span></li>';

    // Additional customer details hook
    do_action( 'woocommerce_order_details_after_customer_details', $order );
    ?>
    <div style="clear:both;"></div>
</ul>

<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && get_option( 'woocommerce_calc_shipping' ) !== 'no' ) : ?>

<div class="col2-set addresses">

    <div class="col-1">

        <?php endif; ?>

        <header class="billig_address_title">
            <h3><?php esc_html_e( 'Billing Address', 'yith-custom-thank-you-page-for-woocommerce' ); ?></h3>
        </header>
        <address>
            <?php
            if ( ! $order->get_formatted_billing_address() ) esc_html_e( 'N/A', 'yith-custom-thank-you-page-for-woocommerce' ); else echo $order->get_formatted_billing_address();
            ?>
        </address>

        <?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && get_option( 'woocommerce_calc_shipping' ) !== 'no' ) : ?>

    </div><!-- /.col-1 -->

    <div class="col-2">

        <header class="shipping_address_title">
            <h3><?php esc_html_e( 'Shipping Address', 'yith-custom-thank-you-page-for-woocommerce' ); ?></h3>
        </header>
        <address>
            <?php
            if ( ! $order->get_formatted_shipping_address() ) esc_html_e( 'N/A', 'yith-custom-thank-you-page-for-woocommerce' ); else echo $order->get_formatted_shipping_address();
            ?>
        </address>

    </div><!-- /.col-2 -->

</div><!-- /.col2-set -->

<?php endif; ?>

<div class="clear"></div>
