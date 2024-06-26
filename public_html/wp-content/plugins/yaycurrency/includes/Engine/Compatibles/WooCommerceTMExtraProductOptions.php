<?php
namespace Yay_Currency\Engine\Compatibles;

use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\YayCurrencyHelper;
use Yay_Currency\Helpers\Helper;
use Yay_Currency\Helpers\SupportHelper;
use Automattic\WooCommerce\Utilities\OrderUtil;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://codecanyon.net/item/woocommerce-extra-product-options/7908619

class WooCommerceTMExtraProductOptions {
	use SingletonTrait;

	private $apply_currency                = array();
	private $is_dis_checkout_diff_currency = false;
	private $default_currency_code;

	public function __construct() {

		if ( ! defined( 'THEMECOMPLETE_EPO_PLUGIN_FILE' ) ) {
			return;
		}
		$this->apply_currency                = YayCurrencyHelper::detect_current_currency();
		$is_checkout_different_currency      = get_option( 'yay_currency_checkout_different_currency', 0 );
		$this->is_dis_checkout_diff_currency = YayCurrencyHelper::is_dis_checkout_diff_currency( $is_checkout_different_currency, $this->apply_currency['status'] );
		$this->default_currency_code         = get_option( 'woocommerce_currency' );

		add_filter( 'yay_currency_get_price_default_in_checkout_page', array( $this, 'get_price_default_in_checkout_page' ), 10, 2 );

		add_filter( 'yay_currency_price_options', array( $this, 'get_price_options' ), 10, 2 );

		add_filter( 'yay_currency_get_cart_item_price_3rd_plugin', array( $this, 'get_cart_item_price_3rd_plugin' ), 10, 3 );
		add_filter( 'yay_currency_get_cart_subtotal_3rd_plugin', array( $this, 'get_cart_subtotal_3rd_plugin' ), 10, 2 );
		// Convert Price from WooCommerce TM Extra Product Options plugin
		add_filter( 'wc_epo_option_price_correction', array( $this, 'wc_epo_option_price_correction' ), 10, 2 );
		add_filter( 'wc_epo_get_current_currency_price', array( $this, 'wc_epo_get_current_currency_price' ), 10, 6 );
		add_filter( 'wc_epo_convert_to_currency', array( $this, 'wc_epo_convert_to_currency' ), 10, 4 );
		add_filter( 'wc_epo_get_currency_price', array( $this, 'wc_epo_get_currency_price' ), 10, 7 );
		add_filter( 'wc_epo_price_on_cart', array( $this, 'wc_epo_price_on_cart' ), 10, 2 );

		add_filter( 'yay_epo_get_price_with_options', array( $this, 'get_price_with_options' ), 10, 2 );

		add_filter( 'wc_epo_adjust_cart_item', array( $this, 'wc_epo_adjust_cart_item' ), 9999, 1 );

	}

	public function get_price_default_in_checkout_page( $price, $product ) {

		if ( isset( $product->tm_epo_set_product_price_with_options_default ) ) {
			$price = $product->tm_epo_set_product_price_with_options_default;
			// Active Woo Discount Rules plugin and apply discount
			if ( SupportHelper::woo_discount_rules_active() && isset( $product->awdr_discount_price ) ) {
				$price = $product->awdr_discount_price;
			}
		}

		return $price;
	}

	public function get_price_options( $price_options, $product ) {

		if ( isset( $product->tm_epo_set_options_price ) ) {
			$price_options = $product->tm_epo_set_options_price;
		}

		return $price_options;

	}

	public function get_cart_item_price_3rd_plugin( $product_price, $cart_item, $apply_currency ) {
		$product_price = SupportHelper::calculate_product_price_by_cart_item( $cart_item );
		$price_options = isset( $cart_item['data']->yay_tm_epo_price_options ) ? $cart_item['data']->yay_tm_epo_price_options : 0;
		$product_price = YayCurrencyHelper::calculate_price_by_currency( $product_price + $price_options, false, $apply_currency );
		return $product_price;

	}

	public function get_cart_subtotal_3rd_plugin( $subtotal, $apply_currency ) {
		$subtotal      = 0;
		$cart_contents = WC()->cart->get_cart_contents();
		foreach ( $cart_contents  as $key => $cart_item ) {
			$product_price    = SupportHelper::calculate_product_price_by_cart_item( $cart_item );
			$price_options    = isset( $cart_item['data']->yay_tm_epo_price_options ) ? $cart_item['data']->yay_tm_epo_price_options : 0;
			$product_subtotal = ( $product_price + $price_options ) * $cart_item['quantity'];
			$subtotal         = $subtotal + YayCurrencyHelper::calculate_price_by_currency( $product_subtotal, false, $apply_currency );
		}

		return $subtotal;
	}

	public function wc_epo_adjust_cart_item( $cart_item ) {
		if ( $this->is_dis_checkout_diff_currency ) {
			if ( isset( $cart_item['tmcartepo'] ) && ! empty( $cart_item['tmcartepo'] ) ) {

				foreach ( $cart_item['tmcartepo'] as $k => $epo ) {
					$_price_type = THEMECOMPLETE_EPO()->get_saved_element_price_type( $epo );
					if ( 'percent' === $_price_type ) {
						$key_selected   = $epo['key'];
						$discount_value = isset( $epo['element']['rules'][ $key_selected ] ) ? array_shift( $epo['element']['rules'][ $key_selected ] ) : 0;
						$product_id     = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
						$product_price  = wc_get_product( $product_id )->get_price( 'edit' );
						$product_price  = $product_price * ( $discount_value / 100 );

						$cart_item['tmcartepo'][ $k ]['price']              = YayCurrencyHelper::calculate_price_by_currency( $product_price, true, $this->apply_currency );
						$cart_item['tmcartepo'][ $k ]['price_per_currency'] = array(
							$this->default_currency_code => $product_price,
						);
					} else {
						$cart_item['tmcartepo'][ $k ]['price_per_currency'] = array(
							$this->default_currency_code => $epo['price'] / YayCurrencyHelper::get_rate_fee( $this->apply_currency ),
						);
					}
				}
			}
		}
		return $cart_item;
	}

	public function get_price_options_convert( $cart_item, $product_price ) {
		$price_convert_options = 0;
		$is_percent_options    = false;
		foreach ( $cart_item['tmcartepo'] as $k => $epo ) {
			$_price_type = THEMECOMPLETE_EPO()->get_saved_element_price_type( $epo );
			if ( 'percent' === $_price_type ) {
				$is_percent_options = true;
				$discount_value     = $epo['element']['rules'][ $epo['key'] ];
				if ( $discount_value ) {
					$discount_value         = array_shift( $discount_value );
					$price_convert_options += ( $product_price * ( $discount_value / 100 ) ) / YayCurrencyHelper::get_rate_fee( $this->apply_currency );
				}
			} else {
				$price_convert_options += $epo['price'] / YayCurrencyHelper::get_rate_fee( $this->apply_currency );
			}
		}
		return array(
			'price_options'      => $price_convert_options,
			'is_percent_options' => $is_percent_options,
		);
	}

	public function wc_epo_option_price_correction( $price, $cart_item ) {
		if ( ! empty( $cart_item['tm_epo_set_product_price_with_options'] ) ) {
			$product_obj = $cart_item['data'];
			$product_id  = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];

			$original_price        = (float) $cart_item['tm_epo_product_original_price'];
			$options_price         = (float) $cart_item['tm_epo_options_prices'];
			$reverse_options_price = (float) ( $options_price / YayCurrencyHelper::get_rate_fee( $this->apply_currency ) );
			$tmcartepo             = Helper::get_value_variable( $cart_item['tmcartepo'] );
			$product_price         = SupportHelper::get_product_price( $product_id, $this->apply_currency );
			if ( $tmcartepo ) {
				$data_convert_options = $this->get_price_options_convert( $cart_item, $product_price );
				$is_percent_options   = Helper::get_value_variable( $data_convert_options['is_percent_options'] );
				if ( $is_percent_options ) {
					$price_convert_options                       = $data_convert_options['price_options'];
					$price_with_options_default                  = (float) $original_price + $price_convert_options;
					$cart_item['data']->yay_tm_epo_price_options = $price_convert_options;
				} else {
					$cart_item['data']->yay_tm_epo_price_options = $reverse_options_price;
					$price_with_options_default                  = (float) $original_price + $reverse_options_price;
				}
			}

			$product_obj->tm_epo_set_options_price = (float) $options_price;

			$product_obj->tm_epo_set_product_price_with_options_default = $price_with_options_default;
			$product_obj->tm_epo_set_product_price_with_options         = YayCurrencyHelper::calculate_price_by_currency( $price_with_options_default, true, $this->apply_currency );

		}

		return $price;

	}

	public function wc_epo_get_current_currency_price( $price = '', $type = '', $currencies = null, $currency = false, $product_price = false, $tc_added_in_currency = false ) {
		$types = array( '', 'math', 'fixedcurrenttotal' );
		if ( $currency ) {
			return $price;
		}
		if ( in_array( $type, $types, true ) ) {
			if ( is_checkout() && ( is_wc_endpoint_url( 'order-pay' ) || is_wc_endpoint_url( 'order-received' ) ) ) {
				return $price;
			}
			// edit order
			if ( is_admin() && isset( $_GET['post'] ) ) {
				$post_id = (int) sanitize_key( $_GET['post'] );
				if ( Helper::check_custom_orders_table_usage_enabled() ) {
					if ( 'shop_order' === OrderUtil::get_order_type( $post_id ) ) {
						return $price;
					}
				} else {
					if ( 'shop_order' === get_post_type( $post_id ) ) {
						return $price;
					}
				}
			}
			$price = YayCurrencyHelper::calculate_price_by_currency( $price, false, $this->apply_currency );
		}
		return $price;
	}

	//apply with case is percent
	public function wc_epo_convert_to_currency( $cpf_product_price = '', $tc_added_in_currency = false, $current_currency = false, $force = false ) {
		if ( ! $tc_added_in_currency || ! $current_currency || $tc_added_in_currency === $current_currency ) {
			return $cpf_product_price;
		}
		$default_currency = get_option( 'woocommerce_currency' );
		if ( $tc_added_in_currency === $default_currency && $current_currency === $default_currency ) {
			return $cpf_product_price;
		} elseif ( $tc_added_in_currency === $default_currency && $current_currency !== $default_currency ) {
			$price = YayCurrencyHelper::calculate_price_by_currency( $cpf_product_price, false, $this->apply_currency );
		} else {
			$apply_currency = YayCurrencyHelper::get_currency_by_currency_code( $tc_added_in_currency );
			$price          = $cpf_product_price / YayCurrencyHelper::get_rate_fee( $apply_currency );
		}
		return $price;
	}

	public function wc_epo_get_currency_price( $price = '', $currency = false, $price_type = '', $current_currency = false, $price_per_currencies = null, $key = null, $attribute = null ) {
		if ( ! $currency ) {
			return $this->wc_epo_get_current_currency_price( $price, $price_type, $currency );
		}
		$default_currency = get_option( 'woocommerce_currency' );

		if ( $current_currency && $current_currency === $currency && $current_currency === $default_currency ) {
			return $price;
		}

		$price = YayCurrencyHelper::calculate_price_by_currency( $price, false, $this->apply_currency );
		return $price;

	}


	public function wc_epo_price_on_cart( $price, $cart_item ) {

		if ( YayCurrencyHelper::disable_fallback_option_in_checkout_page( $this->apply_currency ) ) {
			$price = (float) ( $price / YayCurrencyHelper::get_rate_fee( $this->apply_currency ) );
		}

		return $price;
	}

	public function get_price_with_options( $price, $product ) {
		if ( isset( $product->tm_epo_set_product_price_with_options ) ) {
			$price = $product->tm_epo_set_product_price_with_options;
			// Active Woo Discount Rules PRO plugin and apply discount
			if ( SupportHelper::woo_discount_rules_active() && isset( $product->awdr_discount_price ) ) {
				$price = $product->awdr_discount_price;
			}
		} else {
			// Active Woo Discount Rules PRO plugin and apply discount
			if ( SupportHelper::woo_discount_rules_active() && isset( $product->awdr_discount_price ) ) {
				$price = $product->awdr_discount_price;
			}
		}
		return $price;
	}


}
