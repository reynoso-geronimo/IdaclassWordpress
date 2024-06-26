<?php
namespace Yay_Currency\Engine\Compatibles;

use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\YayCurrencyHelper;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://woocommerce.com/products/tiered-pricing-table-for-woocommerce/

class TieredPricingTableForWooCommerce {

	use SingletonTrait;

	public function __construct() {

		if ( ! class_exists( 'TierPricingTable\TierPricingTablePlugin' ) ) {
			return;
		}

		add_filter( 'tier_pricing_table/price/product_price_rules', array( $this, 'custom_product_price_rules' ), 10, 4 );

	}

	public function custom_product_price_rules( $rules, $product_id, $type, $parent_id ) {
		if ( 'fixed' === $type ) {
			$converted_rules = array_map(
				function( $rule ) {
					$apply_currency = YayCurrencyHelper::detect_current_currency();
					$rule           = YayCurrencyHelper::calculate_price_by_currency( $rule, true, $apply_currency );
					return $rule;
				},
				$rules
			);
			return $converted_rules;
		}
		return $rules;
	}
}
