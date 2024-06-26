<?php
namespace Yay_Currency\Engine;

use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\YayCurrencyHelper;
use Yay_Currency\Helpers\SupportHelper;

defined( 'ABSPATH' ) || exit;

class Hooks {
	use SingletonTrait;

	public function __construct() {
		// ADD FILTER PRIORITY
		add_filter( 'yay_currency_filters_priority', array( $this, 'get_filters_priority' ), 9, 1 );

		add_filter( 'yay_currency_get_cart_item_price', array( $this, 'get_cart_item_price' ), 10, 3 );
		add_filter( 'yay_currency_get_cart_subtotal', array( $this, 'get_cart_subtotal' ), 10, 2 );

		// ADD FILTER GET PRICE WITH CONDITIONS
		add_filter( 'yay_currency_get_price_with_conditions', array( $this, 'get_price_with_conditions' ), 10, 3 );
		// ADD FILTER GET PRICE EXCEPT CLASS PLUGINS
		add_filter( 'yay_currency_get_price_except_class_plugins', array( $this, 'get_price_except_class_plugins' ), 10, 3 );
		// ADD FILTER CHECKOUT DIFFERENT CURRENCY

		add_filter( 'yay_currency_checkout_converted_cart_total', array( $this, 'checkout_converted_cart_total' ), 10, 4 );
		add_filter( 'yay_currency_checkout_converted_shipping_method_full_label', array( $this, 'checkout_converted_shipping_method_full_label' ), 10, 5 );
		add_filter( 'yay_currency_checkout_converted_cart_coupon_totals_html', array( $this, 'checkout_converted_cart_coupon_totals_html' ), 10, 4 );

		add_filter( 'yay_currency_stripe_request_amount', array( $this, 'custom_stripe_request_amount' ), 10, 3 );

		add_action( 'yay_currency_redirect_to_url', array( $this, 'yay_currency_redirect_to_url' ), 10, 2 );

	}

	public function get_filters_priority( $priority ) {

		// Compatible with B2B Wholesale Suite, Price by Country, B2BKing
		if ( class_exists( 'B2bwhs' ) || class_exists( 'CBP_Country_Based_Price' ) || class_exists( 'B2bkingcore' ) ) {
			$priority = 100000;
		}

		return $priority;

	}

	public function get_cart_item_price( $product_price, $cart_item, $apply_currency ) {
		$product_price = apply_filters( 'yay_currency_get_cart_item_price_3rd_plugin', $product_price, $cart_item, $apply_currency );
		if ( ! $product_price ) {
			$product_price = SupportHelper::calculate_product_price_by_cart_item( $cart_item, $apply_currency );
		}
		return $product_price;
	}

	public function get_cart_subtotal( $subtotal, $apply_currency ) {
		$subtotal = apply_filters( 'yay_currency_get_cart_subtotal_3rd_plugin', $subtotal, $apply_currency );
		if ( $subtotal ) {
			return $subtotal;
		}
		$subtotal = SupportHelper::calculate_cart_subtotal( $apply_currency );
		return $subtotal;
	}

	public function get_price_with_conditions( $price, $product, $apply_currency ) {
		// YayExtra , YayPricing
		$is_yaye_adjust_price = false;
		$is_ydp_adjust_price  = false;
		$caculate_price       = YayCurrencyHelper::calculate_price_by_currency( $price, false, $apply_currency );
		if ( class_exists( '\YayExtra\Classes\ProductPage' ) ) {
			$is_yaye_adjust_price = apply_filters( 'yaye_check_adjust_price', false );
		}
		if ( class_exists( '\YayPricing\FrontEnd\ProductPricing' ) ) {
			$is_ydp_adjust_price = apply_filters( 'ydp_check_adjust_price', false );
		}

		if ( class_exists( '\YayPricing\FrontEnd\ProductPricing' ) && $is_ydp_adjust_price ) {
			if ( class_exists( '\YayExtra\Classes\ProductPage' ) && $is_yaye_adjust_price ) {
				return $price;
			} else {
				return $caculate_price;
			}
		}
		if ( class_exists( '\YayExtra\Classes\ProductPage' ) ) {
			//Active Woo Discount Rules PRO plugin and apply discount
			if ( SupportHelper::woo_discount_rules_active() && isset( $product->awdr_discount_price ) ) {
				$price = $product->awdr_discount_price;
			} else {
				$price = apply_filters( 'yay_currency_extra_get_price_with_options', false, $product );
			}

			if ( ! $price ) {
				return $product->get_price( 'edit' );
			}
			return $price;
		}
		// Compatible with Tiered Pricing Table for WooCommerce plugin
		if ( class_exists( 'TierPricingTable\TierPricingTablePlugin' ) ) {
			if ( isset( $product->get_changes()['price'] ) ) {
				return $product->get_changes()['price'];
			}
		}

		// WooCommerce TM Extra Product Options
		if ( defined( 'THEMECOMPLETE_EPO_PLUGIN_FILE' ) ) {
			return apply_filters( 'yay_epo_get_price_with_options', false, $product );
		}

		// WooCommerce WPFunnels
		if ( defined( 'WPFNL_VERSION' ) ) {
			$price = apply_filters( 'yay_wpfunnels_get_price', false, $product );
			return $price;
		}

		// Advanced Product Fields Pro (Extended) for WooCommerce
		if ( class_exists( '\SW_WAPF_PRO\WAPF' ) ) {
			return apply_filters( 'yay_wapf_get_price_with_options', false, $product );
		}

		// YITH WooCommerce Product Add-ons & Extra Options Premium
		if ( defined( 'YITH_WAPO' ) ) {
			return apply_filters( 'yay_yith_wapo_get_price_with_options', false, $product );
		}

		// WooCommerce Name Your Price
		if ( function_exists( 'wc_nyp_init' ) ) {
			return apply_filters( 'yaycurrency_your_name_price', $price, $product );
		}

		// Woo Discount Rules
		if ( SupportHelper::woo_discount_rules_active() ) {
			return apply_filters( 'yay_discount_rules_get_price_with_options', false, $product );
		}

		// WooCommerce Subscriptions
		if ( class_exists( 'WC_Subscriptions' ) ) {
			return apply_filters( 'yay_currency_subscription_get_price_renew', $price, $product );
		}

		return false;

	}

	public function get_price_except_class_plugins( $price, $product, $apply_currency ) {
		$caculate_price       = YayCurrencyHelper::calculate_price_by_currency( $price, false, $apply_currency );
		$except_class_plugins = array(
			'WC_Measurement_Price_Calculator',
			'\WP_Grid_Builder\Includes\Plugin',
			'WCPA', // Woocommerce Custom Product Addons
			'\Acowebs\WCPA\Main', // Woocommerce Custom Product Addons
			'WoonpCore', // Name Your Price for WooCommerce
			'Webtomizer\\WCDP\\WC_Deposits', // WooCommerce Deposits
			'\WC_Product_Price_Based_Country', // Price Per Country
			'\JET_APB\Plugin', // Jet Appointments Booking
		);
		$except_class_plugins = apply_filters( 'yay_currency_except_class_plugin', $except_class_plugins );
		foreach ( $except_class_plugins as $class ) {
			if ( class_exists( $class ) ) {
				return $caculate_price;
			}
		}
		return false;
	}

	public function checkout_converted_cart_total( $cart_total, $total_price, $fallback_currency, $apply_currency ) {
		$original_total          = YayCurrencyHelper::calculate_price_by_currency_html( $fallback_currency, $total_price );
		$converted_approximately = apply_filters( 'yay_currency_checkout_converted_approximately', true, $apply_currency );
		if ( ! $converted_approximately ) {
			return $original_total;
		}
		$converted_total      = YayCurrencyHelper::calculate_price_by_currency_html( $apply_currency, $total_price );
		$converted_total_html = YayCurrencyHelper::converted_approximately_html( $converted_total );
		$cart_total           = $original_total . $converted_total_html;
		return $cart_total;
	}

	public function checkout_converted_shipping_method_full_label( $label, $method_label, $shipping_fee, $fallback_currency, $apply_currency ) {
		$formatted_fallback_currency_shipping_fee = YayCurrencyHelper::calculate_price_by_currency_html( $fallback_currency, $shipping_fee );
		$converted_approximately                  = apply_filters( 'yay_currency_checkout_converted_approximately', true, $apply_currency );
		if ( ! $converted_approximately ) {
			return '' . $method_label . ': ' . $formatted_fallback_currency_shipping_fee;
		}

		$converted_shipping_fee      = YayCurrencyHelper::calculate_price_by_currency( $shipping_fee, true, $apply_currency );
		$formatted_shipping_fee      = YayCurrencyHelper::format_price( $converted_shipping_fee );
		$formatted_shipping_fee_html = YayCurrencyHelper::converted_approximately_html( $formatted_shipping_fee );
		$label                       = '' . $method_label . ': ' . $formatted_fallback_currency_shipping_fee . $formatted_shipping_fee_html;
		return $label;
	}

	public function checkout_converted_cart_coupon_totals_html( $coupon_html, $coupon, $fallback_currency, $apply_currency ) {

		$discount_totals         = WC()->cart->get_coupon_discount_totals();
		$discount_price          = $discount_totals[ $coupon->get_code() ];
		$discount_amount_html    = YayCurrencyHelper::calculate_price_by_currency_html( $fallback_currency, $discount_price );
		$converted_approximately = apply_filters( 'yay_currency_checkout_converted_approximately', true, $apply_currency );
		if ( ! $converted_approximately ) {
			return '-' . $discount_amount_html;
		}
		$converted_discount_price      = YayCurrencyHelper::calculate_price_by_currency( $discount_price, true, $apply_currency );
		$formatted_discount_price      = YayCurrencyHelper::format_price( $converted_discount_price );
		$formatted_discount_price_html = YayCurrencyHelper::converted_approximately_html( $formatted_discount_price );
		$custom_coupon_html            = '-' . $discount_amount_html . $formatted_discount_price_html . substr( $coupon_html, strpos( $coupon_html, '<a' ) ) . '';
		return $custom_coupon_html;
	}

	public function custom_stripe_request_amount( $request, $api, $apply_currency ) {
		global $wpdb;
		if ( isset( $request['currency'] ) && isset( $request['metadata'] ) && isset( $request['metadata']['order_id'] ) ) {
			$array_zero_decimal_currencies = array(
				'BIF',
				'CLP',
				'DJF',
				'GNF',
				'JPY',
				'KMF',
				'KRW',
				'MGA',
				'PYG',
				'RWF',
				'UGX',
				'VND',
				'VUV',
				'XAF',
				'XOF',
				'XPF',
			);
			if ( in_array( strtoupper( $request['currency'] ), $array_zero_decimal_currencies ) ) {
				$orderID = $request['metadata']['order_id'];

				$result = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT meta_value FROM {$wpdb->postmeta} WHERE (post_id = %d AND meta_key = '_order_total')",
						$orderID
					)
				);

				if ( empty( $result ) ) {
					return $request;
				}

				$order_total = $result;

				$request['amount'] = (int) $order_total;
			}
		}
		return $request;
	}

	public function yay_currency_redirect_to_url( $current_url, $currency_ID ) {
		$current_currency = YayCurrencyHelper::get_currency_by_ID( $currency_ID );
		$current_url      = add_query_arg( array( 'yay-currency' => $current_currency['currency'] ), $current_url );
		if ( wp_safe_redirect( $current_url ) ) {
			exit;
		}

	}

}
