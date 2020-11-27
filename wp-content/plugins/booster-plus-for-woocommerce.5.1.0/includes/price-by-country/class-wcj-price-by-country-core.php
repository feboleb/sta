<?php
/**
 * Booster for WooCommerce - Price by Country - Core
 *
 * @version 5.1.0
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Price_by_Country_Core' ) ) :

class WCJ_Price_by_Country_Core {

	/**
	 * Constructor.
	 *
	 * @version 3.6.0
	 * @todo    [dev] check if we can just always execute `init()` on `init` hook
	 */
	function __construct() {
		$this->customer_country_group_id = null;
		if ( ( 'no' === get_option( 'wcj_price_by_country_for_bots_disabled', 'no' ) || ! wcj_is_bot() ) && ! wcj_is_admin_product_edit_page() ) {
			if ( in_array( get_option( 'wcj_price_by_country_customer_country_detection_method', 'by_ip' ), array( 'by_user_selection', 'by_ip_then_by_user_selection' ) ) ) {
				if ( 'wc' === WCJ_SESSION_TYPE ) {
					// `init()` executed on `init` hook because we need to use `WC()->session`
					add_action( 'init', array( $this, 'init' ) );
				} else {
					$this->init();
				}
			}
			$this->add_hooks();
			// `maybe_init_customer_country_by_ip()` executed on `init` hook - in case we need to call `get_customer_country_by_ip()` `WC_Geolocation` class is ready
			add_action( 'init', array( $this, 'maybe_init_customer_country_by_ip' ) );
		}
	}

	/**
	 * init.
	 *
	 * @version 5.0.0
	 * @since   2.9.0
	 */
	function init() {
		wcj_session_maybe_start();
		$req_country = isset( $_REQUEST['wcj-country'] ) && ! empty( $country = $_REQUEST['wcj-country'] ) ? $country : ( isset( $_REQUEST['wcj_country_selector'] ) && ! empty( $country = $_REQUEST['wcj_country_selector'] ) ? $country : null );
		if ( ! empty( $req_country ) ) {
			wcj_session_set( 'wcj-country', $req_country );
			do_action( 'wcj_price_by_country_set_country', $req_country, $this->get_currency_by_country( $req_country ) );
		}
	}

	/**
	 * Gets currency by country.
	 *
	 * @version 4.1.0
	 * @since   4.1.0
	 *
	 * @param $country
	 *
	 * @return bool|mixed|void
	 */
	function get_currency_by_country( $country ) {
		$group_id              = $this->get_country_group_id( $country );
		$country_currency_code = get_option( 'wcj_price_by_country_exchange_rate_currency_group_' . $group_id );
		return ( '' != $country_currency_code ? $country_currency_code : false );
	}

	/**
	 * Saves currency on session by country.
	 *
	 * @version 4.1.0
	 * @since   4.1.0
	 *
	 * @param $country
	 */
	function save_currency_on_session_by_country( $country ) {
		$currency = $this->get_currency_by_country( $country );
		if ( ! empty( $currency ) ) {
			wcj_session_set( 'wcj-currency', $currency );
		}
	}

	/**
	 * maybe_init_customer_country_by_ip.
	 *
	 * @version 5.0.0
	 * @since   2.9.0
	 */
	function maybe_init_customer_country_by_ip() {
		if ( 'by_ip_then_by_user_selection' === get_option( 'wcj_price_by_country_customer_country_detection_method', 'by_ip' ) ) {
			if ( null === wcj_session_get( 'wcj-country' ) ) {
				if ( null != ( $country = $this->get_customer_country_by_ip() ) ) {
					wcj_session_set( 'wcj-country', $country );
					do_action( 'wcj_price_by_country_set_country', $country, $this->get_currency_by_country( $country ) );
				}
			}
		}
	}

	/**
	 * add_hooks.
	 *
	 * @version 5.1.0
	 */
	function add_hooks() {

		// Select with flags
		if ( 'yes' === get_option( 'wcj_price_by_country_jquery_wselect_enabled', 'no' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_wselect_scripts' ) );
		}

		$this->price_hooks_priority = wcj_get_module_price_hooks_priority( 'price_by_country' );

		// Price hooks
		wcj_add_change_price_hooks( $this, $this->price_hooks_priority );

		// Currency hooks
		add_filter( 'woocommerce_currency', array( $this, 'change_currency_code' ),   $this->price_hooks_priority, 1 );

		// Price Filter Widget
		if ( 'yes' === get_option( 'wcj_price_by_country_price_filter_widget_support_enabled', 'no' ) ) {
			add_filter( 'woocommerce_product_query_meta_query', array( $this, 'price_filter_meta_query' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_price_filter_sql', array( $this, 'woocommerce_price_filter_sql' ), 10, 3 );
			add_action( 'wp_footer', array( $this, 'add_compatibility_with_price_filter_widget' ) );
			add_filter( 'posts_clauses', array( $this, 'price_filter_post_clauses' ), 10, 2 );
			add_filter( 'posts_clauses', array( $this, 'price_filter_post_clauses_sort' ), 10, 2 );
			add_action( 'woocommerce_product_query', function ( $query ) {
				$group_id              = $this->get_customer_country_group_id();
				$country_exchange_rate = get_option( 'wcj_price_by_country_exchange_rate_group_' . $group_id, 1 );
				if ( 1 == (float) $country_exchange_rate ) {
					return;
				}
				wcj_remove_class_filter( 'posts_clauses', 'WC_Query', 'order_by_price_asc_post_clauses' );
				wcj_remove_class_filter( 'posts_clauses', 'WC_Query', 'order_by_price_desc_post_clauses' );
				wcj_remove_class_filter( 'posts_clauses', 'WC_Query', 'price_filter_post_clauses' );
			} );
			add_filter( 'woocommerce_price_filter_widget_step', function ( $step ) {
				$step = 1;
				return $step;
			} );
		}

		// Price Format
		if ( wcj_is_frontend() ) {
			if ( 'wc_get_price_to_display' === get_option( 'wcj_price_by_country_price_format_method', 'get_price' ) ) {
				add_filter( 'woocommerce_get_price_including_tax', array( $this, 'format_price_after_including_excluding_tax' ), PHP_INT_MAX, 3 );
				add_filter( 'woocommerce_get_price_excluding_tax', array( $this, 'format_price_after_including_excluding_tax' ), PHP_INT_MAX, 3 );
			}
		}

		// Free Shipping
		add_filter( 'woocommerce_shipping_free_shipping_instance_option', array( $this, 'convert_free_shipping_min_amount' ), 10, 3 );
		add_filter( 'woocommerce_shipping_free_shipping_option', array( $this, 'convert_free_shipping_min_amount' ), 10, 3 );
	}

	/**
	 * convert_free_shipping_min_amount.
	 *
	 * @version 5.1.0
	 * @since   5.1.0
	 *
	 * @param $option
	 * @param $key
	 * @param $method
	 *
	 * @return mixed
	 */
	function convert_free_shipping_min_amount( $option, $key, $method ) {
		if (
			'no' === get_option( 'wcj_price_by_country_compatibility_free_shipping', 'no' )
			|| 'min_amount' !== $key
			|| ! is_numeric( $option )
			|| 0 === (float) $option
		) {
			return $option;
		}
		$option = $this->change_price( $option, null );
		return $option;
	}

	/**
	 * append_price_filter_post_meta_join.
	 *
	 * @version 5.1.0
	 * @since   5.1.0
	 *
	 * @param $sql
	 * @param $country_group_id
	 *
	 * @return string
	 */
	private function append_price_filter_post_meta_join( $sql, $country_group_id ) {
		global $wpdb;
		if ( ! strstr( $sql, 'postmeta AS pm' ) ) {
			$join = $this->get_price_filter_post_meta_join( $country_group_id );
			$sql  .= " {$join} ";
		}
		return $sql;
	}

	/**
	 * get_price_filter_post_meta_join.
	 *
	 * @version 5.1.0
	 * @since   5.1.0
	 *
	 * @param $country_group_id
	 *
	 * @return string
	 */
	private function get_price_filter_post_meta_join( $country_group_id ) {
		global $wpdb;
		return "LEFT JOIN {$wpdb->postmeta} AS pm ON $wpdb->posts.ID = pm.post_id AND pm.meta_key='_wcj_price_by_country_{$country_group_id}'";
	}

	/**
	 * price_filter_post_clauses_sort.
	 *
	 * @version 5.1.0
	 * @since   5.1.0
	 *
	 * @param $args
	 * @param $wp_query
	 *
	 * @return mixed
	 */
	function price_filter_post_clauses_sort( $args, $wp_query ) {
		if (
			! $wp_query->is_main_query() ||
			'price' !== $wp_query->get( 'orderby' ) ||
			empty( $group_id = $this->get_customer_country_group_id() ) ||
			1 == (float) ( $country_exchange_rate = get_option( 'wcj_price_by_country_exchange_rate_group_' . $group_id, 1 ) )
		) {
			return $args;
		}
		global $wpdb;
		$order            = $wp_query->get( 'order' );
		$original_orderby = $args['orderby'];
		if ( 'desc' === strtolower( $order ) ) {
			$args['orderby'] = 'MIN(pm.meta_value+0)+0 DESC';
		} else {
			$args['orderby'] = 'MAX(pm.meta_value+0)+0 ASC';
		}
		$args['orderby'] = ! empty( $original_orderby ) ? $args['orderby'] . ', ' . $original_orderby : $args['orderby'];
		$args['join'] = $this->append_price_filter_post_meta_join( $args['join'], $group_id );
		return $args;
	}

	/**
	 * price_filter_post_clauses.
	 *
	 * @version 5.1.0
	 * @since   5.1.0
	 *
	 * @see WC_Query::price_filter_post_clauses()
	 *
	 * @param $args
	 * @param $wp_query
	 *
	 * @return mixed
	 */
	function price_filter_post_clauses( $args, $wp_query ) {
		global $wpdb;
		if (
			! $wp_query->is_main_query() ||
			( ! isset( $_GET['max_price'] ) && ! isset( $_GET['min_price'] ) ) ||
			empty( $group_id = $this->get_customer_country_group_id() ) ||
			1 == (float) ( $country_exchange_rate = get_option( 'wcj_price_by_country_exchange_rate_group_' . $group_id, 1 ) )
		) {
			return $args;
		}
		$current_min_price = isset( $_GET['min_price'] ) ? floatval( wp_unslash( $_GET['min_price'] ) ) : 0;
		$current_max_price = isset( $_GET['max_price'] ) ? floatval( wp_unslash( $_GET['max_price'] ) ) : PHP_INT_MAX;
		if ( wc_tax_enabled() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) && ! wc_prices_include_tax() ) {
			$tax_class = apply_filters( 'woocommerce_price_filter_widget_tax_class', '' );
			$tax_rates = WC_Tax::get_rates( $tax_class );
			if ( $tax_rates ) {
				$current_min_price -= WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $current_min_price, $tax_rates ) );
				$current_max_price -= WC_Tax::get_tax_total( WC_Tax::calc_inclusive_tax( $current_max_price, $tax_rates ) );
			}
		}
		$current_min_price *= $country_exchange_rate;
		$current_max_price *= $country_exchange_rate;
		$args['fields']    .= ', MIN(pm.meta_value+0) AS wcj_min_price, MAX(pm.meta_value+0) AS wcj_max_price';
		$args['join']      = $this->append_price_filter_post_meta_join( $args['join'], $group_id );
		$args['groupby']   .= $wpdb->prepare( " HAVING wcj_min_price >= %f AND wcj_max_price <= %f", $current_min_price, $current_max_price );
		return $args;
	}

	/**
	 * Adds compatibility with WooCommerce Price Filter widget.
	 *
	 * @see price-slider.js->init_price_filter()
	 *
	 * @version 5.1.0
	 * @since   5.1.0
	 */
	function add_compatibility_with_price_filter_widget() {
		if (
			! is_active_widget( false, false, 'woocommerce_price_filter' )
			|| 'no' === get_option( 'wcj_price_by_country_price_filter_widget_support_enabled', 'no' )
		) {
			return;
		}
		?>
		<?php
		$group_id = $this->get_customer_country_group_id();
		$exchange_rate = get_option( 'wcj_price_by_country_exchange_rate_group_' . $group_id, 1 );
		if ( $exchange_rate == 1 ) {
			return;
		}
		?>
		<input type="hidden" id="wcj_mc_exchange_rate" value="<?php echo esc_html( $exchange_rate ) ?>"/>
		<script>
			var wcj_mc_pf_slider = {
				slider: null,
				convert_rate: 1,
				original_min: 1,
				original_max: 1,
				original_values: [],
				current_min: 1,
				current_max: 1,
				current_values: [],
				step: 1,
				init(slider, convert_rate, step) {
					this.step = step;
					this.slider = slider;
					this.convert_rate = convert_rate;
					this.original_min = jQuery(this.slider).slider("option", "min");
					this.original_max = jQuery(this.slider).slider("option", "max");
					this.original_values = jQuery(this.slider).slider("option", "values");
					this.current_min = this.original_min;
					this.current_max = this.original_max;
					this.current_values[0] = jQuery(this.slider).parent().find('#min_price').val();
					this.current_values[1] = jQuery(this.slider).parent().find('#max_price').val();
					if (
						jQuery(this.slider).parent().find('#min_price').val() != this.original_min ||
						jQuery(this.slider).parent().find('#max_price').val() != this.original_max
					) {
						this.current_values[0] *= wcj_mc_pf_slider.convert_rate;
						this.current_values[1] *= wcj_mc_pf_slider.convert_rate;
					}
					this.update_slider();
				},
				update_slider() {
					jQuery(this.slider).slider("destroy");
					var current_min_price = Math.floor(this.current_min);
					var current_max_price = Math.ceil(this.current_max);
					jQuery(this.slider).slider({
						range: true,
						animate: true,
						min: current_min_price,
						max: current_max_price,
						step: parseFloat(this.step),
						values: wcj_mc_pf_slider.current_values,
						create: function () {
							jQuery(wcj_mc_pf_slider.slider).parent().find('.price_slider_amount #min_price').val(Math.floor(wcj_mc_pf_slider.current_values[0] / wcj_mc_pf_slider.convert_rate));
							jQuery(wcj_mc_pf_slider.slider).parent().find('.price_slider_amount #max_price').val(Math.ceil(wcj_mc_pf_slider.current_values[1] / wcj_mc_pf_slider.convert_rate));
							jQuery(document.body).trigger('price_slider_create', [Math.floor(wcj_mc_pf_slider.current_values[0]), Math.ceil(wcj_mc_pf_slider.current_values[1])]);
						},
						slide: function (event, ui) {
							jQuery(wcj_mc_pf_slider.slider).parent().find('.price_slider_amount #min_price').val(Math.floor(ui.values[0] / wcj_mc_pf_slider.convert_rate));
							jQuery(wcj_mc_pf_slider.slider).parent().find('.price_slider_amount #max_price').val(Math.ceil(ui.values[1] / wcj_mc_pf_slider.convert_rate));
							var the_min = ui.values[0] == Math.round(wcj_mc_pf_slider.current_values[0]) ? Math.floor(wcj_mc_pf_slider.current_values[0]) : ui.values[0];
							var the_max = ui.values[1] == Math.round(wcj_mc_pf_slider.current_values[1]) ? Math.ceil(wcj_mc_pf_slider.current_values[1]) : ui.values[1];
							jQuery(document.body).trigger('price_slider_slide', [the_min, the_max]);
						},
						change: function (event, ui) {
							jQuery(document.body).trigger('price_slider_change', [ui.values[0], ui.values[1]]);
						}
					});
				}
			};
			var wcj_mc_pf = {
				price_filters: null,
				rate: 1,
				step: 1,
				init: function (price_filters) {
					this.price_filters = price_filters;
					this.rate = document.getElementById('wcj_mc_exchange_rate').value;
					this.update_slider();
				},
				update_slider: function () {
					[].forEach.call(wcj_mc_pf.price_filters, function (el) {
						wcj_mc_pf_slider.init(el, wcj_mc_pf.rate, wcj_mc_pf.step);
					});
				}
			}
			document.addEventListener("DOMContentLoaded", function () {
				var price_filters = document.querySelectorAll('.price_slider.ui-slider');
				if (price_filters.length) {
					wcj_mc_pf.init(price_filters);
				}
			});
		</script>
		<?php
	}

	/**
	 * woocommerce_price_filter_sql.
	 *
	 * @version 5.1.0
	 * @since   5.1.0
	 *
	 * @see WC_Widget_Price_Filter::get_filtered_price()
	 *
	 * @param $sql
	 * @param $meta_query_sql
	 * @param $tax_query_sql
	 *
	 * @return string
	 */
	function woocommerce_price_filter_sql( $sql, $meta_query_sql, $tax_query_sql){
		if (
			is_admin()
			|| 'no' === get_option( 'wcj_price_by_country_price_filter_widget_support_enabled', 'no' )
			|| empty( $group_id = $this->get_customer_country_group_id() )
			|| 1 == (float) ( $country_exchange_rate = get_option( 'wcj_price_by_country_exchange_rate_group_' . $group_id, 1 ) )
		) {
			return $sql;
		}

		global $wpdb;
		$args                  = WC()->query->get_main_query()->query_vars;
		$tax_query             = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
		$meta_query            = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

		if ( ! is_post_type_archive( 'product' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
			$tax_query[] = array(
				'taxonomy' => $args['taxonomy'],
				'terms'    => array( $args['term'] ),
				'field'    => 'slug',
			);
		}

		foreach ( $meta_query + $tax_query as $key => $query ) {
			if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
				unset( $meta_query[ $key ] );
			}
		}

		$search     = WC_Query::get_main_search_query_sql();
		$search_query_sql = $search ? ' AND ' . $search : '';
		$sql = "
			SELECT IFNULL(MIN(pm.meta_value+0),min_price) AS min_price, IFNULL(MAX(pm.meta_value+0),max_price) AS max_price			
			FROM {$wpdb->wc_product_meta_lookup}
			LEFT JOIN {$wpdb->postmeta} AS pm ON (pm.post_id = product_id AND pm.meta_key='_wcj_price_by_country_{$group_id}')
			WHERE product_id IN (
				SELECT ID FROM {$wpdb->posts}
				" . $tax_query_sql['join'] . $meta_query_sql['join'] . "
				WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
				AND {$wpdb->posts}.post_status = 'publish'
				" . $tax_query_sql['where'] . $meta_query_sql['where'] . $search_query_sql . '
			)';

		return $sql;
	}

	/**
	 * format_price_after_including_excluding_tax.
	 *
	 * @version 4.4.0
	 * @since   4.4.0
	 *
	 * @param $return_price
	 * @param $qty
	 * @param $product
	 *
	 * @return float|int
	 */
	function format_price_after_including_excluding_tax( $return_price, $qty, $product ) {
		$precision    = get_option( 'woocommerce_price_num_decimals', 2 );
		$return_price = wcj_price_by_country_rounding( $return_price, $precision );
		if ( 'yes' === get_option( 'wcj_price_by_country_make_pretty', 'no' ) && $return_price >= 0.5 && $precision > 0 ) {
			$return_price = wcj_price_by_country_pretty_price( $return_price, $precision );
		}
		return $return_price;
	}

	/**
	 * enqueue_wselect_scripts.
	 *
	 * @version 2.5.4
	 * @since   2.5.4
	 */
	function enqueue_wselect_scripts() {
		wp_enqueue_style(  'wcj-wSelect-style', wcj_plugin_url() . '/includes/lib/wSelect/wSelect.css' );
		wp_enqueue_script( 'wcj-wSelect',       wcj_plugin_url() . '/includes/lib/wSelect/wSelect.min.js', array(), false, true );
		wp_enqueue_script( 'wcj-wcj-wSelect',   wcj_plugin_url() . '/includes/js/wcj-wSelect.js', array(), false, true );
	}

	/**
	 * price_filter_meta_query.
	 *
	 * @version 2.5.3
	 * @since   2.5.3
	 */
	function price_filter_meta_query( $meta_query, $_wc_query ) {
		foreach ( $meta_query as $_key => $_query ) {
			if ( isset( $_query['price_filter'] ) && true === $_query['price_filter'] && isset( $_query['key'] ) ) {
				if ( null != ( $group_id = $this->get_customer_country_group_id() ) ) {
					$meta_query[ $_key ]['key'] = '_' . 'wcj_price_by_country_' . $group_id;
				}
			}
		}
		return $meta_query;
	}

	/**
	 * change_price_grouped.
	 *
	 * @version 2.7.0
	 * @since   2.5.0
	 */
	function change_price_grouped( $price, $qty, $_product ) {
		if ( $_product->is_type( 'grouped' ) ) {
			if ( 'yes' === get_option( 'wcj_price_by_country_local_enabled', 'yes' ) ) {
				foreach ( $_product->get_children() as $child_id ) {
					$the_price = get_post_meta( $child_id, '_price', true );
					$the_product = wc_get_product( $child_id );
					$the_price = wcj_get_product_display_price( $the_product, $the_price, 1 );
					if ( $the_price == $price ) {
						return $this->change_price( $price, $child_id );
					}
				}
			} else {
				return $this->change_price( $price, 0 );
			}
		}
		return $price;
	}

	/**
	 * get_customer_country_by_ip.
	 *
	 * @version 3.8.0
	 * @since   2.5.0
	 */
	function get_customer_country_by_ip() {
		if ( isset( $this->customer_country_by_ip ) ) {
			return $this->customer_country_by_ip;
		}
		if ( class_exists( 'WC_Geolocation' ) ) {
			// Get the country by IP
			$location = WC_Geolocation::geolocate_ip( ( 'wc' === get_option( 'wcj_price_by_country_ip_detection_method', 'wc' ) ? '' : wcj_get_the_ip() ) );
			// Base fallback
			if ( empty( $location['country'] ) ) {
				$location = wc_format_country_state_string( apply_filters( 'woocommerce_customer_default_location', get_option( 'woocommerce_default_country' ) ) );
			}
			if ( ! empty( $location['country'] ) ) {
				$this->customer_country_by_ip = $location['country'];
			}
			return ( isset( $location['country'] ) ) ? $location['country'] : null;
		} else {
			return null;
		}
	}

	/**
	 * change_price_shipping.
	 *
	 * @version 3.2.0
	 */
	function change_price_shipping( $package_rates, $package ) {
		if ( null != ( $group_id = $this->get_customer_country_group_id() ) ) {
			$country_exchange_rate = get_option( 'wcj_price_by_country_exchange_rate_group_' . $group_id, 1 );
			return wcj_change_price_shipping_package_rates( $package_rates, $country_exchange_rate );
		} else {
			return $package_rates;
		}
	}

	/**
	 * get_customer_country_group_id.
	 *
	 * @version 4.1.0
	 * @todo    [feature] (maybe) `( 'cart_and_checkout' === get_option( 'wcj_price_by_country_override_scope', 'all' ) && ( is_cart() || is_checkout() ) ) ||`
	 */
	function get_customer_country_group_id() {

		if ( 'yes' === get_option( 'wcj_price_by_country_revert', 'no' ) && is_checkout() ) {
			$this->customer_country_group_id = -1;
			return null;
		}

		// We already know the group - nothing to calculate - return group
		if ( isset( $this->customer_country_group_id ) && null != $this->customer_country_group_id && $this->customer_country_group_id > 0 ) {
			return $this->customer_country_group_id;
		}

		// Get the country
		if ( isset( $_GET['country'] ) && '' != $_GET['country'] && wcj_is_user_role( 'administrator' ) ) {
			$country = $_GET['country'];
		} elseif ( 'no' != ( $override_option = get_option( 'wcj_price_by_country_override_on_checkout_with_billing_country', 'no' ) )
			&& (
				( 'all'               === get_option( 'wcj_price_by_country_override_scope', 'all' ) ) ||
				( 'checkout'          === get_option( 'wcj_price_by_country_override_scope', 'all' ) && is_checkout() )
			)
			&& isset( WC()->customer )
			&& ( ( 'yes' === $override_option && '' != wcj_customer_get_country() ) || ( 'shipping_country' === $override_option && '' != WC()->customer->get_shipping_country() ) )
		) {
			$country = ( 'yes' === $override_option ) ? wcj_customer_get_country() : WC()->customer->get_shipping_country();
		} else {
			if ( 'by_ip' === get_option( 'wcj_price_by_country_customer_country_detection_method', 'by_ip' ) ) {
				$country = $this->get_customer_country_by_ip();
			} elseif ( 'by_ip_then_by_user_selection' === get_option( 'wcj_price_by_country_customer_country_detection_method', 'by_ip' ) ) {
				$country = ( null !== ( $session_value = wcj_session_get( 'wcj-country' ) ) ? $session_value : $this->get_customer_country_by_ip() );
			} elseif ( 'by_user_selection' === get_option( 'wcj_price_by_country_customer_country_detection_method', 'by_ip' ) ) {
				$country = wcj_session_get( 'wcj-country' );
			} elseif ( 'by_wpml' === get_option( 'wcj_price_by_country_customer_country_detection_method', 'by_ip' ) ) {
				$country = ( defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : null );
			}
		}

		if ( null === $country ) {
			$this->customer_country_group_id = -1;
			return null;
		}

		$this->customer_country_group_id = $this->get_country_group_id( $country );
		if ( - 1 != $this->customer_country_group_id ) {
			return $this->customer_country_group_id;
		}

		// No country group found
		$this->customer_country_group_id = -1;
		return null;
	}

	/**
	 * Gets country group id.
	 *
	 * @version 4.1.0
	 * @since   4.1.0
	 * @param   $country
	 *
	 * @return int
	 */
	function get_country_group_id( $country ) {
		// Get the country group id - go through all the groups, first found group is returned
		for ( $i = 1; $i <= apply_filters( 'booster_option', 1, get_option( 'wcj_price_by_country_total_groups_number', 1 ) ); $i ++ ) {
			switch ( get_option( 'wcj_price_by_country_selection', 'comma_list' ) ) {
				case 'comma_list':
					$country_exchange_rate_group = get_option( 'wcj_price_by_country_exchange_rate_countries_group_' . $i );
					$country_exchange_rate_group = str_replace( ' ', '', $country_exchange_rate_group );
					$country_exchange_rate_group = explode( ',', $country_exchange_rate_group );
				break;
				case 'multiselect':
					$country_exchange_rate_group = get_option( 'wcj_price_by_country_countries_group_' . $i, '' );
				break;
				case 'chosen_select':
					$country_exchange_rate_group = get_option( 'wcj_price_by_country_countries_group_chosen_select_' . $i, '' );
				break;
			}
			if ( is_array( $country_exchange_rate_group ) && in_array( $country, $country_exchange_rate_group ) ) {
				return $i;
			}
		}
		return - 1;
	}

	/**
	 * change_currency_code.
	 */
	function change_currency_code( $currency ) {
		if ( null != ( $group_id = $this->get_customer_country_group_id() ) ) {
			$country_currency_code = get_option( 'wcj_price_by_country_exchange_rate_currency_group_' . $group_id );
			if ( '' != $country_currency_code ) {
				return $country_currency_code;
			}
		}
		return $currency;
	}

	/**
	 * get_variation_prices_hash.
	 *
	 * @version 4.0.0
	 * @since   2.4.3
	 */
	function get_variation_prices_hash( $price_hash, $_product, $display ) {
		$group_id = $this->get_customer_country_group_id();
		$price_hash['wcj_price_by_country_group_id_data'] = array(
			$group_id,
			get_option( 'wcj_price_by_country_rounding', 'none' ),
			get_option( 'wcj_price_by_country_make_pretty', 'no' ),
			get_option( 'wcj_price_by_country_make_pretty_min_amount_multiplier', 1 ),
			get_option( 'woocommerce_price_num_decimals', 2 ),
			get_option( 'wcj_price_by_country_local_enabled', 'yes' ),
			get_option( 'wcj_price_by_country_exchange_rate_currency_group_' . $group_id, 'EUR' ),
			get_option( 'wcj_price_by_country_exchange_rate_group_' . $group_id, 1 ),
			get_option( 'wcj_price_by_country_make_empty_price_group_' . $group_id, 'no' ),
		);
		return $price_hash;
	}

	/**
	 * change_price.
	 *
	 * @version 4.9.0
	 */
	function change_price( $price, $product ) {
		if ( null != ( $group_id = $this->get_customer_country_group_id() ) ) {
			if ( 'yes' === get_option( 'wcj_price_by_country_compatibility_woo_discount_rules', 'no' ) ) {
				global $flycart_woo_discount_rules;
				if (
					! empty( $flycart_woo_discount_rules ) &&
					! has_action( 'woocommerce_before_calculate_totals', array( $flycart_woo_discount_rules, 'applyDiscountRules' ) )
					&& ( $product_cart_id = WC()->cart->generate_cart_id( wcj_get_product_id( $product ) ) ) &&
					WC()->cart->find_product_in_cart( $product_cart_id )
				) {
					return $price;
				}
			}
			$do_save='yes'===get_option('wcj_price_by_country_save_prices','no');
			$_current_filter = current_filter();
			if ( empty( $_current_filter ) ) {
				$_current_filter = 'wcj_filter__none';
			}
			if ( $do_save && isset( WCJ()->modules['price_by_country']->calculated_products_prices[ wcj_get_product_id( $product ) ][ $_current_filter ] ) ) {
				return WCJ()->modules['price_by_country']->calculated_products_prices[ wcj_get_product_id( $product ) ][ $_current_filter ];
			}
			$new_price = wcj_price_by_country( $price, $product, $group_id );
			WCJ()->modules['price_by_country']->calculated_products_prices[ wcj_get_product_id( $product ) ][ $_current_filter ] = $new_price;
			return $new_price;
		}
		// No changes
		return $price;
	}
}

endif;

return new WCJ_Price_by_Country_Core();
