<?php
namespace Yay_Currency\Engine\Compatibles;

use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\YayCurrencyHelper;

defined( 'ABSPATH' ) || exit;

// link plugin : https://woocommerce.com/products/woocommerce-deposits/

class WooCommerceDeposits {
	use SingletonTrait;

	private $apply_currency = array();

	public function __construct() {

		if ( class_exists( 'WC_Deposits' ) ) {
			$this->apply_currency = YayCurrencyHelper::detect_current_currency();
			add_filter( 'woocommerce_deposits_fixed_deposit_amount', array( $this, 'custom_woocommerce_deposits_amount' ), 10, 2 );
		}
	}

	public function custom_woocommerce_deposits_amount( $amount, $product ) {
		$amount = YayCurrencyHelper::calculate_price_by_currency_cookie( $amount, true, $this->apply_currency );
		return $amount;
	}
}
