<?php

namespace DgoraWcas\Integrations\Plugins\WooCommerceProductsVisibility;

/**
 * Integration with WooCommerce Products Visibility
 *
 * Plugin URL: https://themeforest.net/user/codemine
 * Author: codemine
 */
class WooCommerceProductsVisibility {
	public function init() {
		/**
		 * This plugin is hooked on plugins_loaded action with priority 100000, so we need
		 * wait for it, and try to load this integration in next hook.
		 */
		add_action( 'sanitize_comment_cookies', array( $this, 'late_init' ) );
	}

	public function late_init() {
		if ( ! class_exists( 'WCPV_FRONTEND' ) ) {
			return;
		}

		add_action( 'init', array( $this, 'store_in_session_included_products' ), 20 );
	}

	/**
	 * Store visible product ids in session
	 */
	public function store_in_session_included_products() {
		$wcpv_frontend = \WCPV_FRONTEND::get_instance();
		if ( ! empty( $wcpv_frontend->include_products ) ) {
			if ( ! session_id() ) {
				session_start();
			}
			$_SESSION['dgwt-wcas-wcpv-visible-products'] = $wcpv_frontend->include_products;
		}
	}
}
