<?php
namespace Yay_Currency\Helpers;

use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\YayCurrencyHelper;


class SupportHelper {

	use SingletonTrait;

	protected function __construct() {}

	public static function get_price_options_by_3rd_plugin( $product ) {
		$price_options = apply_filters( 'yay_currency_price_options', 0, $product );
		return $price_options;
	}

	public static function get_product_price( $product_id, $apply_currency = false ) {
		$wc_product    = wc_get_product( $product_id );
		$product_price = $wc_product->get_price( 'edit' );
		if ( $apply_currency ) {
			$product_price = YayCurrencyHelper::calculate_price_by_currency( $product_price, true, $apply_currency );
		}
		return $product_price;
	}

	// GET PRICE SIGNUP FEE (WooCommerce Subscriptions plugin)
	public static function get_price_sign_up_fee_by_wc_subscriptions( $apply_currency, $product_obj ) {
		$sign_up_fee = 0;
		if ( ! class_exists( 'WC_Subscriptions' ) ) {
			return $sign_up_fee;
		}
		if ( class_exists( 'WC_Subscriptions_Product' ) ) {
			$sign_up_fee = \WC_Subscriptions_Product::get_sign_up_fee( $product_obj );
			if ( $sign_up_fee > 0 ) {
				$sign_up_fee = YayCurrencyHelper::calculate_price_by_currency( $sign_up_fee, true, $apply_currency );
			}
		}
		return $sign_up_fee;
	}

	public static function calculate_product_price_by_cart_item( $cart_item, $apply_currency = false ) {
		$product_id    = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
		$product_obj   = wc_get_product( $product_id );
		$product_price = $product_obj->get_price( 'edit' );
		if ( $apply_currency ) {
			$product_price = YayCurrencyHelper::calculate_price_by_currency( $product_price, false, $apply_currency );
		}
		return $product_price;
	}

	// Caculate Cart Subtotal
	public static function calculate_cart_subtotal( $apply_currency ) {

		$cart_contents = WC()->cart->get_cart_contents();
		if ( ! $cart_contents ) {
			return 0;
		}

		$subtotal = 0;
		foreach ( $cart_contents  as $key => $cart_item ) {
			$product_price    = self::calculate_product_price_by_cart_item( $cart_item, $apply_currency );
			$product_subtotal = $product_price * $cart_item['quantity'];
			$subtotal         = $subtotal + $product_subtotal;
		}
		return $subtotal;
	}

	public static function woo_discount_rules_active() {
		return apply_filters( 'yay_currency_active_woo_discount_rules', false );
	}
}
