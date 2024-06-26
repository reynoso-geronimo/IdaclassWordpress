<?php
namespace Yay_Currency\Engine\Compatibles;

use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\YayCurrencyHelper;
use Yay_Currency\Helpers\SupportHelper;
use YayExtra\Helper\Utils;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://yaycommerce.com/yayextra-woocommerce-extra-product-options/

class YayExtra {
	use SingletonTrait;

	private $apply_currency = array();

	public function __construct() {

		if ( ! defined( 'YAYE_VERSION' ) ) {
			return;
		}

		$this->apply_currency = YayCurrencyHelper::detect_current_currency();

		add_filter( 'yay_currency_get_cart_item_price_3rd_plugin', array( $this, 'get_cart_item_price_3rd_plugin' ), 10, 3 );
		add_filter( 'yay_currency_get_cart_subtotal_3rd_plugin', array( $this, 'get_cart_subtotal_3rd_plugin' ), 10, 2 );

	}

	public function get_cart_item_price_3rd_plugin( $product_price, $cart_item, $apply_currency ) {
		$product_price = SupportHelper::calculate_product_price_by_cart_item( $cart_item );
		$price_options = isset( $cart_item['yaye_total_option_cost'] ) ? $cart_item['yaye_total_option_cost'] : 0;
		$product_price = YayCurrencyHelper::calculate_price_by_currency( $product_price + $price_options, false, $apply_currency );
		return $product_price;

	}

	public function get_cart_subtotal_3rd_plugin( $subtotal, $apply_currency ) {
		$subtotal      = 0;
		$cart_contents = WC()->cart->get_cart_contents();
		foreach ( $cart_contents  as $key => $cart_item ) {
			$product_price    = SupportHelper::calculate_product_price_by_cart_item( $cart_item );
			$price_options    = isset( $cart_item['yaye_total_option_cost'] ) ? $cart_item['yaye_total_option_cost'] : 0;
			$product_subtotal = ( $product_price + $price_options ) * $cart_item['quantity'];
			$subtotal         = $subtotal + YayCurrencyHelper::calculate_price_by_currency( $product_subtotal, false, $apply_currency );
		}

		return $subtotal;
	}

}
