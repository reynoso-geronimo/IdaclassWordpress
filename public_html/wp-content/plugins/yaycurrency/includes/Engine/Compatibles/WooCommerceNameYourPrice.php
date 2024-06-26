<?php
namespace Yay_Currency\Engine\Compatibles;

use Yay_Currency\Helpers\Helper;
use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\YayCurrencyHelper;
use Yay_Currency\Helpers\SupportHelper;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://woocommerce.com/products/name-your-price/

class WooCommerceNameYourPrice {
	use SingletonTrait;

	private $apply_currency = array();

	public function __construct() {

		if ( ! function_exists( 'wc_nyp_init' ) ) {
			return;
		}

		$this->apply_currency = YayCurrencyHelper::detect_current_currency();

		add_filter( 'yay_currency_get_price_default_in_checkout_page', array( $this, 'get_price_default_in_checkout_page' ), 10, 2 );

		add_filter( 'yay_currency_get_cart_item_price_3rd_plugin', array( $this, 'get_cart_item_price_3rd_plugin' ), 10, 3 );
		add_filter( 'yay_currency_get_cart_subtotal_3rd_plugin', array( $this, 'get_cart_subtotal_3rd_plugin' ), 10, 2 );

		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 3 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 20, 2 );

		add_filter( 'wc_nyp_raw_suggested_price', array( $this, 'wc_nyp_raw_suggested_price' ), 10, 3 );
		add_filter( 'wc_nyp_raw_minimum_price', array( $this, 'wc_nyp_raw_minimum_price' ), 10, 3 );
		add_filter( 'wc_nyp_raw_maximum_price', array( $this, 'wc_nyp_raw_maximum_price' ), 10, 3 );

		add_filter( 'yaycurrency_your_name_price', array( $this, 'get_price' ), 10, 2 );

	}

	public function get_price_default_in_checkout_page( $price, $product ) {
		if ( isset( $product->yaycurrency_your_name_price ) ) {
			$price = $product->yaycurrency_your_name_price;
		}
		return $price;
	}

	public function get_cart_item_price_3rd_plugin( $product_price, $cart_item, $apply_currency ) {
		$product_price = SupportHelper::calculate_product_price_by_cart_item( $cart_item );

		if ( isset( $cart_item['nyp'] ) ) {
			$product_price = $cart_item['nyp'];
		} else {
			$product_price = YayCurrencyHelper::calculate_price_by_currency( $product_price, false, $apply_currency );
		}

		return $product_price;

	}

	public function get_cart_subtotal_3rd_plugin( $subtotal, $apply_currency ) {
		$subtotal      = 0;
		$cart_contents = WC()->cart->get_cart_contents();
		foreach ( $cart_contents  as $key => $cart_item ) {
			$product_price = SupportHelper::calculate_product_price_by_cart_item( $cart_item );
			$quantity      = $cart_item['quantity'];
			if ( isset( $cart_item['nyp'] ) ) {
				$product_subtotal = $cart_item['nyp'] * $quantity;
			} else {
				$product_subtotal = $product_price * $quantity;
				$product_subtotal = YayCurrencyHelper::calculate_price_by_currency( $product_subtotal, false, $apply_currency );
			}

			$subtotal = $subtotal + $product_subtotal;
		}

		return $subtotal;
	}

	public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		if ( isset( $cart_item_data['nyp'] ) ) {
			$cart_item_data['added_by_currency'] = $this->apply_currency;
		}
		return $cart_item_data;
	}

	public function get_cart_item_from_session( $cart_item, $values ) {
		// No need to check is_nyp b/c this has already been validated by validate_add_cart_item().
		if ( isset( $cart_item['nyp'] ) ) {
			$cart_item_apply_currency = Helper::get_value_variable( $cart_item['added_by_currency'] );
			if ( $cart_item_apply_currency ) {
				$price                                    = $cart_item['nyp'];
				$product_obj                              = $cart_item['data'];
				$product_obj->yaycurrency_your_name_price = $price / YayCurrencyHelper::get_rate_fee( $cart_item_apply_currency );
			}
		}
		return $cart_item;
	}

	public function get_price( $price, $product ) {
		if ( isset( $product->yaycurrency_your_name_price ) ) {
			$price = $product->yaycurrency_your_name_price;
			$price = YayCurrencyHelper::calculate_price_by_currency( $price, false, $this->apply_currency );
			return $price;
		}
		// WooCommerce Subscriptions
		if ( class_exists( 'WC_Subscriptions' ) ) {
			return apply_filters( 'yay_currency_subscription_get_price_renew', $price, $product );
		}
		return false;
	}

	public function wc_nyp_raw_suggested_price( $suggested, $product_id, $product ) {
		$suggested_price = YayCurrencyHelper::calculate_price_by_currency( $suggested, false, $this->apply_currency );
		return $suggested_price;
	}

	public function wc_nyp_raw_minimum_price( $minimum, $product_id, $product ) {
		if ( ! isset( $product->yaycurrency_your_name_price ) ) {
			$minimum_price = YayCurrencyHelper::calculate_price_by_currency( $minimum, false, $this->apply_currency );
		}
		return $minimum_price;
	}

	public function wc_nyp_raw_maximum_price( $maximum, $product_id, $product ) {
		if ( ! isset( $product->yaycurrency_your_name_price ) ) {
			$maximum_price = YayCurrencyHelper::calculate_price_by_currency( $maximum, false, $this->apply_currency );
		}
		return $maximum_price;
	}

}
