<style>
    .section{
        margin-left: -20px;
        margin-right: -20px;
        font-family: "Raleway",san-serif;
    }
    .section h1{
        text-align: center;
        text-transform: uppercase;
        color: #808a97;
        font-size: 35px;
        font-weight: 700;
        line-height: normal;
        display: inline-block;
        width: 100%;
        margin: 50px 0 0;
    }
    .section ul{
        list-style-type: disc;
        padding-left: 15px;
    }
    .section:nth-child(even){
        background-color: #fff;
    }
    .section:nth-child(odd){
        background-color: #f1f1f1;
    }
    .section .section-title img{
        display: table-cell;
        vertical-align: middle;
        width: auto;
        margin-right: 15px;
    }
    .section h2,
    .section h3 {
        display: inline-block;
        vertical-align: middle;
        padding: 0;
        font-size: 24px;
        font-weight: 700;
        color: #808a97;
        text-transform: uppercase;
    }

    .section .section-title h2{
        display: table-cell;
        vertical-align: middle;
        line-height: 25px;
    }

    .section-title{
        display: table;
    }

    .section h3 {
        font-size: 14px;
        line-height: 28px;
        margin-bottom: 0;
        display: block;
    }

    .section p{
        font-size: 13px;
        margin: 15px 0;
    }
    .section ul li{
        margin-bottom: 4px;
    }
    .landing-container{
        max-width: 750px;
        margin-left: auto;
        margin-right: auto;
        padding: 50px 0 30px;
    }
    .landing-container:after{
        display: block;
        clear: both;
        content: '';
    }
    .landing-container .col-1,
    .landing-container .col-2{
        float: left;
        box-sizing: border-box;
        padding: 0 15px;
    }
    .landing-container .col-1 img{
        width: 100%;
    }
    .landing-container .col-1{
        width: 55%;
    }
    .landing-container .col-2{
        width: 45%;
    }
    .premium-cta{
        background-color: #808a97;
        color: #fff;
        border-radius: 6px;
        padding: 20px 15px;
    }
    .premium-cta:after{
        content: '';
        display: block;
        clear: both;
    }
    .premium-cta p{
        margin: 7px 0;
        font-size: 14px;
        font-weight: 500;
        display: inline-block;
        width: 60%;
    }
    .premium-cta a.button{
        border-radius: 6px;
        height: 60px;
        float: right;
        background: url(<?php echo YITH_CTPW_ASSETS_URL ?>images/upgrade.png) #ff643f no-repeat 13px 13px;
        border-color: #ff643f;
        box-shadow: none;
        outline: none;
        color: #fff;
        position: relative;
        padding: 9px 50px 9px 70px;
    }
    .premium-cta a.button:hover,
    .premium-cta a.button:active,
    .premium-cta a.button:focus{
        color: #fff;
        background: url(<?php echo YITH_CTPW_ASSETS_URL ?>images/upgrade.png) #971d00 no-repeat 13px 13px;
        border-color: #971d00;
        box-shadow: none;
        outline: none;
    }
    .premium-cta a.button:focus{
        top: 1px;
    }
    .premium-cta a.button span{
        line-height: 13px;
    }
    .premium-cta a.button .highlight{
        display: block;
        font-size: 20px;
        font-weight: 700;
        line-height: 20px;
    }
    .premium-cta .highlight{
        text-transform: uppercase;
        background: none;
        font-weight: 800;
        color: #fff;
    }

    .section.one{
        background: url(<?php echo YITH_CTPW_ASSETS_URL ?>/images/01-bg.png) no-repeat #fff; background-position: 85% 75%
    }
    .section.two{
        background: url(<?php echo YITH_CTPW_ASSETS_URL ?>/images/02-bg.png) no-repeat ; background-position: 15% 100%;
    }
    .section.three{
        background: url(<?php echo YITH_CTPW_ASSETS_URL ?>/images/03-bg.png) no-repeat #fff; background-position: 85% 75%
    }
    .section.four{
        background: url(<?php echo YITH_CTPW_ASSETS_URL ?>/images/04-bg.png) no-repeat ; background-position: 15% 100%;
    }
    .section.five{
        background: url(<?php echo YITH_CTPW_ASSETS_URL ?>/images/05-bg.png) no-repeat #fff; background-position: 85% 100%;
    }
    .section.six{
        background: url(<?php echo YITH_CTPW_ASSETS_URL ?>/images/06-bg.png) no-repeat ; background-position: 15% 100%;
    }
    .section.seven{
        background: url(<?php echo YITH_CTPW_ASSETS_URL ?>/images/07-bg.png) no-repeat #fff; background-position: 85% 100%;
    }
    .section.eight{
        background: url(<?php echo YITH_CTPW_ASSETS_URL ?>/images/08-bg.png) no-repeat ; background-position: 15% 100%;
    }
    .section.nine{
        background: url(<?php echo YITH_CTPW_ASSETS_URL ?>/images/09-bg.png) no-repeat #fff; background-position: 85% 100%;
    }

    @media (max-width: 768px) {
        .section{margin: 0}
        .premium-cta p{
            width: 100%;
        }
        .premium-cta{
            text-align: center;
        }
        .premium-cta a.button{
            float: none;
        }
    }

    @media (max-width: 480px){
        .wrap{
            margin-right: 0;
        }
        .section{
            margin: 0;
        }
        .landing-container .col-1,
        .landing-container .col-2{
            width: 100%;
            padding: 0 15px;
        }
        .section-odd .col-1 {
            float: left;
            margin-right: -100%;
        }
        .section-odd .col-2 {
            float: right;
            margin-top: 65%;
        }
    }

    @media (max-width: 320px){
        .premium-cta a.button{
            padding: 9px 20px 9px 70px;
        }

        .section .section-title img{
            display: none;
        }
    }
</style>
<div class="landing">
    <div class="section section-cta section-odd">
        <div class="landing-container">
            <div class="premium-cta">
                <p>
                    <?php echo sprintf( esc_html__('Upgrade to %1$spremium version%2$s of %1$sYITH Custom Thank You Page for WooCommerc%2$s to benefit from all features!','yith-custom-thank-you-page-for-woocommerce'),'<span class="highlight">','</span>' );?>
                </p>
                <a href="<?php echo $this->get_premium_landing_uri() ?>" target="_blank" class="premium-cta-button button btn">
                    <span class="highlight"><?php esc_html_e('UPGRADE','yith-custom-thank-you-page-for-woocommerce');?></span>
                    <span><?php esc_html_e('to the premium version','yith-custom-thank-you-page-for-woocommerce');?></span>
                </a>
            </div>
        </div>
    </div>
    <div class="one section section-even clear">
        <h1><?php esc_html_e('Premium Features','yith-custom-thank-you-page-for-woocommerce');?></h1>
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/01.png" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/01-icon.png" />
                    <h2><?php esc_html_e('Custom Thank You page per product','yith-custom-thank-you-page-for-woocommerce');?></h2>
                </div>
                <p>
                    <?php echo sprintf( esc_html__('A custom thank-you page for each product of your shop. %1$sCreate and associate a different Thank You page with each product.%2$s Only this will let you convey a different message based on your users’ peculiar interests.', 'yith-custom-thank-you-page-for-woocommerce'),'<b>','</b>');?>
                </p>
                <p>
                    <?php esc_html_e('Only this will let you convey a different message based on your users\' peculiar interests.', 'yith-custom-thank-you-page-for-woocommerce');?>
                </p>
            </div>
        </div>
    </div>
    <div class="two section section-odd clear">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/02-icon.png" />
                    <h2><?php esc_html_e('Custom Thank You page per product category','yith-custom-thank-you-page-for-woocommerce');?></h2>
                </div>
                <p>
                    <?php esc_html_e('If you don’t want to work on each single product, you might be willing to do this for single product categories.', 'yith-custom-thank-you-page-for-woocommerce');?>
                </p>
                <p>
                    <?php echo sprintf(esc_html__('This way the user will be shown the %1$sthank-you page you have associated with the category%2$s the purchased product belongs to.', 'yith-custom-thank-you-page-for-woocommerce'), '<b>', '</b>','<br>');?>
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/02.png" />
            </div>
        </div>
    </div>
    <div class="three section section-even clear">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/03.png" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/03-icon.png" />
                    <h2><?php esc_html_e('Set a priority','yith-custom-thank-you-page-for-woocommerce');?></h2>
                </div>
                <p>
                    <?php esc_html_e('Select which Thank You page to show if there are products in cart associated with different Thank You pages.', 'yith-custom-thank-you-page-for-woocommerce');?>
                </p>
            </div>
        </div>
    </div>
    <div class="four section section-odd clear">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/04-icon.png" />
                    <h2><?php esc_html_e('Product sharing','yith-custom-thank-you-page-for-woocommerce');?></h2>
                </div>
                <p>
                    <?php echo sprintf(esc_html__('Starting right from the Thank You page, users will be able to %1$sshare the products%2$s they’ve just bought on the %1$smost popular social networks.%2$s%3$s One more possibility to increase your product visibility.', 'yith-custom-thank-you-page-for-woocommerce'), '<b>', '</b>','<br>');?>
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/04.png" />
            </div>
        </div>
    </div>
    <div class="five section section-even clear">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/05.png" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/05-icon.png" />
                    <h2><?php esc_html_e('Sharing-optimized URLs','yith-custom-thank-you-page-for-woocommerce');?></h2>
                </div>
                <p>
                    <?php echo sprintf( esc_html__('Thanks to services like %1$sBitly%2$s or %1$sGoogle%2$s, the sharing URLs will be optimised and you’ll never have to deal with extremely long URLs.', 'yith-custom-thank-you-page-for-woocommerce'),'<b>','</b>');?>
                </p>
            </div>
        </div>
    </div>
    <div class="six section section-odd clear">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/06-icon.png" />
                    <h2><?php esc_html_e('Order details','yith-custom-thank-you-page-for-woocommerce');?></h2>
                </div>
                <p>
                    <?php esc_html_e('Choose whether to show order details or not, thanks to the handy options in the plugin settings panel.', 'yith-custom-thank-you-page-for-woocommerce');?>
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/06.png" />
            </div>
        </div>
    </div>
    <div class="seven section section-even clear">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/07.png" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/07-icon.png" />
                    <h2><?php esc_html_e('Upsell products','yith-custom-thank-you-page-for-woocommerce');?></h2>
                </div>
                <p>
                    <?php esc_html_e('Compose your own little showcase on your Thank You page and select the products to feature there.', 'yith-custom-thank-you-page-for-woocommerce');?>
                </p>
                <p>
                    <?php esc_html_e('This way, your customers could be shown more products from your shop in which they could be interested', 'yith-custom-thank-you-page-for-woocommerce');?>
                </p>
            </div>
        </div>
    </div>
    <div class="eight section section-odd clear">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/08-icon.png" />
                    <h2><?php esc_html_e('Colour palette','yith-custom-thank-you-page-for-woocommerce');?></h2>
                </div>
                <p>
                    <?php esc_html_e('Choose the colours that best suit your theme thanks to the options included in the plugin settings.', 'yith-custom-thank-you-page-for-woocommerce');?>
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/08.png" />
            </div>
        </div>
    </div>
    <div class="nine section section-even clear">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/09.png" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_CTPW_URL . '/assets' ?>/images/09-icon.png" />
                    <h2><?php esc_html_e('Thank you page for payment method','yith-custom-thank-you-page-for-woocommerce');?></h2>
                </div>
                <p>
                    <?php esc_html_e('Set a specific thank you page based on the payment method the customer chooses. Select either a pre-set page or use an external URL.', 'yith-custom-thank-you-page-for-woocommerce');?>
                </p>
            </div>
        </div>
    </div>
    <div class="section section-cta section-odd">
        <div class="landing-container">
            <div class="premium-cta">
                <p>
                    <?php echo sprintf( esc_html__('Upgrade to %1$spremium version%2$s of %1$sYITH Custom Thank You Page for WooCommerc%2$s to benefit from all features!','yith-custom-thank-you-page-for-woocommerce'),'<span class="highlight">','</span>' );?>
                </p>
                <a href="<?php echo $this->get_premium_landing_uri() ?>" target="_blank" class="premium-cta-button button btn">
                    <span class="highlight"><?php esc_html_e('UPGRADE','yith-custom-thank-you-page-for-woocommerce');?></span>
                    <span><?php esc_html_e('to the premium version','yith-custom-thank-you-page-for-woocommerce');?></span>
                </a>
            </div>
        </div>
    </div>
</div>