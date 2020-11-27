<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
?>
<h2 class="order_details_title"><?php echo apply_filters('ctpw_order_details_title',esc_html__( 'Order Details', 'yith-custom-thank-you-page-for-woocommerce' )); ?></h2>
<table class="shop_table order_details">
    <thead>
    <tr>
        <th class="product-name"><?php esc_html_e( 'Product', 'yith-custom-thank-you-page-for-woocommerce' ); ?></th>
        <th class="product-total"><?php esc_html_e( 'Total', 'yith-custom-thank-you-page-for-woocommerce' ); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
    if ( sizeof( $order_items ) > 0 ) {

        foreach( $order->get_items() as $item ) {
            $_product     = $item->get_product();
            /* woocommerce 3.1 compatibility */
            if (version_compare( WC()->version , '3.1', '<' ) ){
                $item_meta = new WC_Order_Item_Meta($item, $_product);
            } else {
                $item_meta = new  WC_Order_Item_Product($_product);
            }

            ?>
            <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
                <td class="product-name">
                    <?php
                    if ( $_product && ! $_product->is_visible() )
                        echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item );
                    else
                        echo apply_filters( 'woocommerce_order_item_name', sprintf( '<a href="%s">%s</a>', get_permalink( $item['product_id'] ), $item['name'] ), $item );

                    echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>', $item );



                    if ( $_product && $_product->exists() && $_product->is_downloadable() && $order->is_download_permitted() ) {

                        $download_files = $item->get_item_downloads();
                        $i              = 0;
                        $links          = array();

                        foreach ( $download_files as $download_id => $file ) {
                            $i++;

                            $links[] = '<small><a href="' . esc_url( $file['download_url'] ) . '">' . sprintf( esc_html__( 'Download file %s',
                                    'yith-custom-thankyou-page-for-woocommerce' ), ( count( $download_files ) > 1 ? ' ' . $i . ': ' : ': ' ) ) . esc_html( $file['name'] ) . '</a></small>';
                        }

                        echo '<br/>' . implode( '<br/>', $links );
                    }
                    ?>
                </td>
                <td class="product-total">
                    <?php echo $order->get_formatted_line_subtotal( $item ); ?>
                </td>
            </tr>
            <?php

            $show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
            if ( $show_purchase_note ) { ?>
    <tr class="woocommerce-table__product-purchase-note product-purchase-note">
        <td colspan="2"><?php echo wpautop( do_shortcode( wp_kses_post( $_product->get_purchase_note() ) ) ); ?></td>
    </tr>
    <?php
            }
        }
    }

    do_action( 'woocommerce_order_items_table', $order );
    ?>
    </tbody>
    <tfoot>
    <?php
    if ( $totals = $order->get_order_item_totals() ) foreach ( $totals as $total ) :
        ?>
        <tr>
            <th scope="row"><?php echo $total['label']; ?></th>
            <td><?php echo $total['value']; ?></td>
        </tr>
    <?php
    endforeach;
    ?>
    </tfoot>
</table>

<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
