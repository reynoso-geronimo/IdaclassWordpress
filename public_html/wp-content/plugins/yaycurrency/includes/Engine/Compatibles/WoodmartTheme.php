<?php
namespace Yay_Currency\Engine\Compatibles;

use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\YayCurrencyHelper;

defined( 'ABSPATH' ) || exit;

class WoodmartTheme {
	use SingletonTrait;

	private $apply_currency = array();

	public function __construct() {

		if ( 'woodmart' === wp_get_theme()->template ) {
			$this->apply_currency = YayCurrencyHelper::detect_current_currency();
			add_filter( 'yay_currency_get_price_by_currency', array( $this, 'get_round_price_by_currency' ), 10, 3 );
			add_filter( 'yay_currency_calculated_total_again', array( $this, 'yay_currency_calculated_total_again' ) );
			add_filter( 'woocommerce_cart_subtotal', array( $this, 'woocommerce_cart_subtotal' ), 9999, 3 );
		}
	}

	public function get_round_price( $price ) {
		if ( function_exists( 'round_price_product' ) ) {
			// Return rounded price
			return ceil( $price );
		}

		return $price;
	}

	public function yay_currency_calculated_total_again() {
		return true;
	}

	public function get_round_price_by_currency( $price, $product, $apply_currency ) {
		return $this->get_round_price( $price );
	}

	public function woocommerce_cart_subtotal( $cart_subtotal, $compound, $cart ) {
		WC()->cart->calculate_totals();
		return $cart_subtotal;
	}

}
