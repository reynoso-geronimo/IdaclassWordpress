<?php
namespace Yay_Currency\Engine\Compatibles;

use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\SupportHelper;
use Yay_Currency\Helpers\YayCurrencyHelper;
use Yay_Currency\Helpers\Helper;


defined( 'ABSPATH' ) || exit;

// Link plugin: https://woocommerce.com/products/product-add-ons/

class WooCommerceProductAddons {
	use SingletonTrait;

	private $converted_currency = array();
	private $apply_currency     = array();

	public function __construct() {

		if ( ! defined( 'WC_PRODUCT_ADDONS_VERSION' ) ) {
			return;
		}

		$this->converted_currency = YayCurrencyHelper::converted_currency();
		$this->apply_currency     = YayCurrencyHelper::get_apply_currency( $this->converted_currency );

		add_filter( 'yay_currency_price_options', array( $this, 'get_price_options' ), 10, 2 );

		add_filter( 'yay_currency_get_cart_item_price_3rd_plugin', array( $this, 'get_cart_item_price_3rd_plugin' ), 10, 3 );
		add_filter( 'yay_currency_get_cart_subtotal_3rd_plugin', array( $this, 'get_cart_subtotal_3rd_plugin' ), 10, 2 );

		add_filter( 'woocommerce_product_addons_option_price_raw', array( $this, 'custom_product_addons_option_price' ), 10, 2 );
		add_filter( 'woocommerce_product_addons_get_item_data', array( $this, 'custom_cart_item_addon_data' ), 10, 3 );
		// Place Order
		add_filter( 'woocommerce_product_addons_order_line_item_meta', array( $this, 'custom_order_line_item_meta' ), 10, 4 );

	}

	public function get_price_options( $price_options, $product ) {

		if ( isset( $product->yay_currency_addon_set_options_price ) ) {
			$price_options = $product->yay_currency_addon_set_options_price;
		}

		return $price_options;

	}

	public function get_price_options_by_cart_item( $product_price, $cart_item ) {
		$addons        = isset( $cart_item['addons'] ) ? $cart_item['addons'] : false;
		$price_options = 0;
		if ( $addons ) {
			foreach ( $addons as $key => $addon ) {
				if ( isset( $addon['price_type'] ) ) {
					if ( 'percentage_based' !== $addon['price_type'] ) {
						$price_options += $addon['price'];
					} else {
						$price_options += (float) $product_price * $addon['price'] / 100;
					}
				}
			}
		}
		return $price_options;
	}

	public function get_cart_item_price_3rd_plugin( $product_price, $cart_item, $apply_currency ) {
		$product_price = SupportHelper::calculate_product_price_by_cart_item( $cart_item );
		$price_options = $this->get_price_options_by_cart_item( $product_price, $cart_item );
		$product_price = YayCurrencyHelper::calculate_price_by_currency( $product_price + $price_options, false, $apply_currency );
		return $product_price;
	}

	public function get_cart_subtotal_3rd_plugin( $subtotal, $apply_currency ) {
		$subtotal      = 0;
		$cart_contents = WC()->cart->get_cart_contents();
		foreach ( $cart_contents  as $key => $cart_item ) {
			$product_price    = SupportHelper::calculate_product_price_by_cart_item( $cart_item );
			$price_options    = $this->get_price_options_by_cart_item( $product_price, $cart_item );
			$product_subtotal = ( $product_price + $price_options ) * $cart_item['quantity'];
			$subtotal         = $subtotal + YayCurrencyHelper::calculate_price_by_currency( $product_subtotal, false, $apply_currency );
		}

		return $subtotal;
	}

	public function custom_product_addons_option_price( $price, $option ) {
		if ( 'percentage_based' !== $option['price_type'] ) {
			$price = YayCurrencyHelper::calculate_price_by_currency( $price, false, $this->apply_currency );
		}
		return $price;
	}

	public function custom_cart_item_addon_data( $addon_data, $addon, $cart_item ) {
		$addon_price = Helper::get_value_variable( $addon['price'] );
		if ( isset( $addon['price_type'] ) && $addon_price ) {
			if ( 'percentage_based' !== $addon['price_type'] ) {
				$item_fee = $addon['price'];
				if ( 0 == $item_fee ) {
					return $addon_data;
				}
			} else {
				$product_id     = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
				$product_obj    = wc_get_product( $product_id );
				$original_price = $product_obj->get_price( 'original' );
				$item_fee       = (float) $original_price * $addon['price'] / 100;
			}

			$converted_item_fee = YayCurrencyHelper::calculate_price_by_currency( $item_fee, true, $this->apply_currency );
			$formatted_item_fee = YayCurrencyHelper::format_price( $converted_item_fee );

			if ( YayCurrencyHelper::disable_fallback_option_in_checkout_page( $this->apply_currency ) ) {

				if ( 'percentage_based' === $addon['price_type'] ) {
					$item_fee = (float) $product_obj->get_price( 'original' ) * $addon['price'] / 100;
				}

				if ( YayCurrencyHelper::is_checkout_in_fallback() ) {
					$fallback_currency  = YayCurrencyHelper::get_fallback_currency( $this->converted_currency );
					$formatted_item_fee = YayCurrencyHelper::calculate_price_by_currency_html( $fallback_currency, $item_fee );
				} else {
					$formatted_item_fee = wc_price( $item_fee );
				}
			}

			$addon_data['name'] = $addon['name'] . ' (' . $formatted_item_fee . ')';

		}
		return $addon_data;
	}

	public function custom_order_line_item_meta( $meta_data, $addon, $item, $cart_item ) {
		$product_id = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];

		$addon_price = Helper::get_value_variable( $addon['price'] );

		if ( isset( $addon['price_type'] ) && $addon_price ) {
			if ( 'percentage_based' !== $addon['price_type'] ) {
				$item_fee = $addon['price'];

				if ( 0 == $item_fee ) {
					return $meta_data;
				}
			} else {
				$product_id     = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
				$product_obj    = wc_get_product( $product_id );
				$original_price = $product_obj->get_price( 'original' );
				$item_fee       = (float) $original_price * $addon['price'] / 100;
			}

			$converted_item_fee = YayCurrencyHelper::calculate_price_by_currency( $item_fee, true, $this->apply_currency );
			$formatted_item_fee = YayCurrencyHelper::format_price( $converted_item_fee );

			if ( YayCurrencyHelper::disable_fallback_option_in_checkout_page( $this->apply_currency ) ) {
				if ( 'percentage_based' === $addon['price_type'] ) {
					$wc_product = wc_get_product( $product_id );
					$item_fee   = (float) $wc_product->get_price( 'original' ) * $addon['price'] / 100;
				}

				if ( YayCurrencyHelper::is_checkout_in_fallback() ) {
					$fallback_currency  = YayCurrencyHelper::get_fallback_currency( $this->converted_currency );
					$formatted_item_fee = YayCurrencyHelper::calculate_price_by_currency_html( $fallback_currency, $item_fee );
				} else {
					$formatted_item_fee = wc_price( $item_fee );
				}
			}

			$meta_data['key'] = $addon['name'] . ' (' . $formatted_item_fee . ')';

		}

		return $meta_data;
	}

	public function get_price_options_by_cart() {
		$cart_contents = WC()->cart->get_cart_contents();
		if ( count( $cart_contents ) > 0 ) {
			foreach ( $cart_contents  as $key => $value ) {
				$addons = isset( $value['addons'] ) ? $value['addons'] : false;
				if ( $addons ) {
					$product_obj    = $value['data'];
					$product_id     = $value['variation_id'] ? $value['variation_id'] : $value['product_id'];
					$original_price = wc_get_product( $product_id )->get_price( 'edit' );
					$price_options  = 0;
					foreach ( $addons as $key => $addon ) {
						if ( isset( $addon['price_type'] ) ) {
							if ( 'percentage_based' !== $addon['price_type'] ) {
								$price_options += $addon['price'];
							} else {
								$price_options += (float) $original_price * $addon['price'] / 100;
							}
						}
					}

					$price_convert_currency                            = YayCurrencyHelper::calculate_price_by_currency( $original_price, false, $this->apply_currency );
					$options_price                                     = YayCurrencyHelper::calculate_price_by_currency( $price_options, false, $this->apply_currency );
					$product_obj->yay_currency_addon_set_options_price = (float) $options_price;
					$product_obj->yay_currency_addon_set_price_with_options = $price_convert_currency + $options_price;
				}
			}
		}

	}

}
