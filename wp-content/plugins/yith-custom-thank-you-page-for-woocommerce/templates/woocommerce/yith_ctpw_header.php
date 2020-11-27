<ul class="order_details">
    <p><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Thank you. Your order has been received.', 'yith-custom-thank-you-page-for-woocommerce' ), $order ); ?></p>
    <li class="order">
        <?php esc_html_e( 'Order:', 'yith-custom-thank-you-page-for-woocommerce' ); ?>
        <strong><?php echo $order->get_order_number(); ?></strong>
    </li>
    <li class="date">
        <?php esc_html_e( 'Date:', 'yith-custom-thank-you-page-for-woocommerce' ); ?>
        <strong><?php echo date_i18n( get_option( 'date_format' ), strtotime( $order->get_date_created() ) ); ?></strong>
    </li>
    <li class="total">
        <?php esc_html_e( 'Total:', 'yith-custom-thank-you-page-for-woocommerce' ); ?>
        <strong><?php echo $order->get_formatted_order_total(); ?></strong>
    </li>
    <?php if ( $order->get_payment_method_title() ) : ?>
        <li class="method">
            <?php esc_html_e( 'Payment method:', 'yith-custom-thank-you-page-for-woocommerce' ); ?>
            <strong><?php echo $order->get_payment_method_title(); ?></strong>
        </li>
    <?php endif; ?>
</ul>
<div class="clear"></div>