<?php

namespace Yay_Currency\Engine\Compatibles;

use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\YayCurrencyHelper;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://marketpress.com/shop/plugins/woocommerce/b2b-market/

class B2BMarket {

	use SingletonTrait;

	private $apply_currency = array();

	public function __construct() {

		if ( ! class_exists( 'BM' ) ) {
			return;
		}

		$this->apply_currency = YayCurrencyHelper::detect_current_currency();

		add_filter( 'bm_filter_rrp_price', array( $this, 'bm_filter_rrp_price' ), 10, 2 );
		add_filter( 'bm_filter_get_cheapest_price_update_price', array( $this, 'bm_filter_get_cheapest_price_update_price' ), 10, 5 );
		add_filter( 'bm_filter_listable_bulk_prices', array( $this, 'bm_filter_listable_bulk_prices' ), 10, 1 );
		add_filter( 'bm_filter_bulk_price_dynamic_generate_first_row', array( $this, 'bm_filter_bulk_price_dynamic_generate_first_row' ), 10, 5 );
		add_filter( 'bm_filter_cheapest_bulk_price', array( $this, 'bm_filter_cheapest_bulk_price' ), 10, 1 );

	}

	public function bm_filter_rrp_price( $rrp_price, $product_id ) {
		$converted_price = YayCurrencyHelper::calculate_price_by_currency( $rrp_price, false, $this->apply_currency );
		return $converted_price;
	}

	public function bm_filter_get_cheapest_price_update_price( $cheapest_price, $product_price, $product, $group_id, $qty ) {
		$converted_price = YayCurrencyHelper::calculate_price_by_currency( $cheapest_price, false, $this->apply_currency );
		return $converted_price;
	}

	public function bm_filter_listable_bulk_prices( $bulk_prices ) {
		if ( is_array( $bulk_prices ) ) {
			foreach ( $bulk_prices as $key => $table_row ) {
				if ( isset( $table_row['price'] ) ) {
					$price                        = $table_row['price'];
					$price                        = YayCurrencyHelper::calculate_price_by_currency( $price, false, $this->apply_currency );
					$bulk_prices[ $key ]['price'] = $price;
				}
			}
		}
		return $bulk_prices;
	}

	public function bm_filter_bulk_price_dynamic_generate_first_row( $temp_price, $price, $product, $group_id, $quantity ) {
		$converted_price = YayCurrencyHelper::calculate_price_by_currency( $temp_price, false, $this->apply_currency );
		return $converted_price;
	}

	public function bm_filter_cheapest_bulk_price( $cheapest_bulk_price ) {
		if ( is_array( $cheapest_bulk_price ) ) {
			$price                  = $cheapest_bulk_price[0];
			$price                  = YayCurrencyHelper::calculate_price_by_currency( $price, false, $this->apply_currency );
			$cheapest_bulk_price[0] = $price;
		}
		return $cheapest_bulk_price;
	}
}
