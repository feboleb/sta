<div id="yith_ctpw_failed_payment">
<p><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction.', 'yith-custom-thank-you-page-for-woocommerce' ); ?></p>

<p><?php
    if ( is_user_logged_in() )
        esc_html_e( 'Please attempt your purchase again or go to your account page.', 'yith-custom-thank-you-page-for-woocommerce' );
    else
        esc_html_e( 'Please attempt your purchase again.', 'yith-custom-thank-you-page-for-woocommerce' );
    ?></p>

<p>
    <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'yith-custom-thank-you-page-for-woocommerce' ) ?></a>
    <?php if ( is_user_logged_in() ) : ?>
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>" class="button pay"><?php esc_html_e( 'My Account', 'yith-custom-thank-you-page-for-woocommerce' ); ?></a>
    <?php endif; ?>
</p>
    </div>