<?php
namespace Yay_Currency\Engine\Compatibles;

use Yay_Currency\Utils\SingletonTrait;
use Yay_Currency\Helpers\YayCurrencyHelper;

defined( 'ABSPATH' ) || exit;

// Link plugin: https://wordpress.org/plugins/woocommerce-product-addon/

class PPOM {
	use SingletonTrait;

	private $converted_currency = array();
	private $apply_currency     = array();

	public function __construct() {

		if ( ! class_exists( '\NM_PersonalizedProduct' ) ) {
			return;
		}

		$this->converted_currency = YayCurrencyHelper::converted_currency();
		$this->apply_currency     = YayCurrencyHelper::get_apply_currency( $this->converted_currency );
		add_filter( 'ppom_option_price', array( $this, 'ppom_option_price' ), 10 );
		add_filter( 'ppom_price_option_meta', array( $this, 'ppom_price_option_meta' ), 10, 5 );
		add_filter( 'ppom_cart_fixed_fee', array( $this, 'ppom_cart_fixed_fee' ) );

	}

	public function ppom_price_option_meta( $option_meta, $field_meta, $field_price, $option, $qty ) {
		$option_price               = isset( $option['price'] ) ? $option['price'] : ( isset( $option['raw_price'] ) ? $option['raw_price'] : '' );
		$field_title                = isset( $field_meta['title'] ) ? stripslashes( $field_meta['title'] ) : '';
		$label_price                = "{$field_title} - " . wc_price( $option_price );
		$option_meta['price']       = $option_price;
		$option_meta['label_price'] = $label_price;
		return $option_meta;
	}

	public function ppom_cart_fixed_fee( $fee_price ) {
		return YayCurrencyHelper::calculate_price_by_currency( $fee_price, true, $this->apply_currency );
	}

	public function ppom_option_price( $option_price ) {
		return YayCurrencyHelper::calculate_price_by_currency( $option_price, true, $this->apply_currency );
	}
}
