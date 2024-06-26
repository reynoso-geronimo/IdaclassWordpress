<?php
namespace Yay_Currency\Engine\Compatibles;

use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\YayCurrencyHelper;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://www.flycart.org/products/wordpress/woocommerce-discount-rules

class WooDiscountRules {

	use SingletonTrait;

	private $apply_currency = array();
	private $cart_item_from;
	public function __construct() {

		if ( ! defined( 'WDR_VERSION' ) ) {
			return;
		}
		$this->apply_currency = YayCurrencyHelper::detect_current_currency();

		add_filter( 'yay_currency_active_woo_discount_rules', array( $this, 'active_woo_discount_rules' ), 10, 1 );

		add_filter( 'advanced_woo_discount_rules_discounted_price_of_cart_item', array( $this, 'convert_price_apply_rules_discounted' ), 9999, 4 );
		add_filter( 'yay_discount_rules_get_price_with_options', array( $this, 'get_price_with_options' ), 10, 2 );

		add_filter( 'advanced_woo_discount_rules_converted_currency_value', array( $this, 'advanced_woo_discount_rules_converted_currency_value' ), 9999, 2 );
		add_filter( 'advanced_woo_discount_rules_bulk_table_ranges', array( $this, 'advanced_woo_discount_rules_bulk_table_ranges' ), 10, 3 );
		add_filter( 'advanced_woo_discount_rules_get_regular_price', array( $this, 'advanced_woo_discount_rules_get_price' ), 10, 2 );
		add_filter( 'advanced_woo_discount_rules_get_price', array( $this, 'advanced_woo_discount_rules_get_price' ), 10, 2 );

	}

	public function active_woo_discount_rules() {
		return true;
	}

	public function convert_price_apply_rules_discounted( $price, $cart_item, $cart_object, $calculated_cart_item_discount ) {
		if ( ! empty( $cart_item['data'] ) ) {
			$product_obj                      = $cart_item['data'];
			$product_obj->awdr_discount_price = $calculated_cart_item_discount['discounted_price'];
		}
		return $price;
	}

		// GET PRICE WITH OPTIONS
	public function get_price_with_options( $price, $product ) {
		if ( isset( $product->awdr_discount_price ) ) {
			if ( $product->awdr_discount_price >= 0 ) {
				$price = $product->awdr_discount_price;
			}
		}
		return $price;
	}

	// CONVERTED CURRENCY VALUE
	public function advanced_woo_discount_rules_converted_currency_value( $price, $type ) {
		if ( YayCurrencyHelper::disable_fallback_option_in_checkout_page( $this->apply_currency ) ) {
			return $price;
		}

		if ( 'flat' === $type && $price < YayCurrencyHelper::get_rate_fee( $this->apply_currency ) ) {
			$price = YayCurrencyHelper::calculate_price_by_currency( $price, false, $this->apply_currency );
		}

		if ( 'flat' != $type ) {
			$price = YayCurrencyHelper::calculate_price_by_currency( $price, false, $this->apply_currency );
		}

		return $price;
	}

	// Convert price with Bulk Table Ranges
	public function advanced_woo_discount_rules_bulk_table_ranges( $response_ranges, $list_rules, $product ) {
		if ( count( $response_ranges ) && ! empty( $list_rules ) && ! empty( $product ) ) {
			foreach ( $response_ranges as &$range ) {
				if ( isset( $range['discount_type'] ) ) {
					if ( 'flat' === $range['discount_type'] || 'fixed_price' === $range['discount_type'] ) {
						$range['discount_value'] = YayCurrencyHelper::calculate_price_by_currency( $range['discount_value'], false, $this->apply_currency );
					}
				}
			}
		}

		return $response_ranges;
	}

	public function advanced_woo_discount_rules_get_price( $price, $product ) {

		if ( YayCurrencyHelper::disable_fallback_option_in_checkout_page( $this->apply_currency ) ) {
			return $price;
		}

		return $price;

	}

}
