<?php

namespace ArubaFe\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\RestApi\ArubaFeApiRestManager;
use WC_Order;
use WC_Order_Item_Product;

abstract class ArubaFeWcUtils {


	/**
	 * Funzione che restituisce l'id di fatturazione elettronica associato ad un prodotto nell'ordine
	 *
	 * @param WC_Order_Item_Product $order_item_product
	 * @return string|null
	 */

	public static function getTaxData( WC_Order_Item_Product $order_item_product ) {
		global $wpdb;

		$taxClass = $order_item_product->get_tax_class();
		$taxClass = sanitize_text_field( $taxClass );
		$taxes    = $order_item_product->get_taxes();
		$keys     = array_keys( $taxes['total'] );

        foreach ($keys as $key){
            // @codingStandardsIgnoreStart
            $result = $wpdb->get_row( $wpdb->prepare(
                "SELECT id_aruba, tax_rate
                    FROM {$wpdb->prefix}woocommerce_tax_rates
                    WHERE tax_rate_id = %d
                    AND tax_rate_class = %s",
                (int)$key,
                $taxClass
            ) );
            // @codingStandardsIgnoreEnd
            if($result)
                return $result;

        }

        return null;

	}

	/**
	 * @param $taxOption
	 * @param $order WC_Order
	 * @return object
	 */

	public static function getTaxDataFromClass( $taxClass, $order ) {
		global $wpdb;

		$basedOn = get_option( 'woocommerce_tax_based_on', 'billing' );
		$country = '';

		switch ( $basedOn ) {
			case 'base':
				$country = WC()->countries->get_base_country();
				break;
			case 'shipping':
				$country = $order->get_shipping_country();
				break;
			case 'billing':
			default:
				$country = $order->get_billing_country();
				break;
		}


        // @codingStandardsIgnoreStart
		return $wpdb->get_row( $wpdb->prepare(
            "SELECT id_aruba, tax_rate
                    FROM {$wpdb->prefix}woocommerce_tax_rates
                    WHERE tax_rate_country = %s
                    AND tax_rate_class = %s",
            $country,
            $taxClass
        ) );
        // @codingStandardsIgnoreEnd
	}

	public static function getDefaultZeroRate() {

		if ( $zeroRate = CustomOptions::get_option( ArubaFeConstants::ARUBA_FE_DEFAULT_ZERO_RATE ) ) {
			return $zeroRate;
		}

		if ( ArubaFeApiRestManager::getInstance()->getTaxRates() ) {

			if ( $zeroRate = CustomOptions::get_option( ArubaFeConstants::ARUBA_FE_DEFAULT_ZERO_RATE ) ) {
				return $zeroRate;
			}
		}

		return '';
	}
}
