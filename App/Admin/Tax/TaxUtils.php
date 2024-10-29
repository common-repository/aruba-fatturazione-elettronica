<?php
// REST API

namespace ArubaFe\Admin\Tax;

use ArubaFe\Admin\CustomOptions;
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}


class TaxUtils extends Countries {


	/**
	 * Retrieve all tax rates from WooCommerce.
	 *
	 * @return array Returns an array containing all tax classes in WooCommerce.
	 */
	public function get_rates() {
		$all_tax_rates    = array();
		$tax_classesArray = \WC_Tax::get_tax_rate_classes(); // Retrieve all tax classes.

		usort(
			$tax_classesArray,
			function ( $a, $b ) {
				return $a->tax_rate_class_id - $b->tax_rate_class_id;
			}
		);

		$tax_classes[] = '';
		foreach ( $tax_classesArray as $class ) {
			$tax_classes[] = $class->name;
		}

		return array( 'tax_classes' => $tax_classes );
	}



	/**
	 * Insert TAXT rate
	 *
	 * @param [type] $country_code
	 * @param [type] $iva
	 * https://woocommerce.com/document/setting-up-taxes-in-woocommerce/
	 * @return void
	 */
	public function insertTaxRate( $args ) {
		try {
			$tax_rate_id = \WC_TAX::_insert_tax_rate( $args );
			return $tax_rate_id;
		} catch ( \Exception $e ) {
			echo 'Caught exception: ', esc_html( $e->getMessage() ), "\n";
			return false;
		}
	}

	public function updaterubaIdInWoocommerceTaxRates( $tax_rate_id, $id_aruba_value ) {
		global $wpdb;
        // @codingStandardsIgnoreStart

		$wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->prefix}woocommerce_tax_rates SET id_aruba = %s WHERE tax_rate_id = %d",
            $id_aruba_value,
            $tax_rate_id
        ) );
        // @codingStandardsIgnoreEnd

    }



	/**
	 * Insert TAXT rate
	 * DA TESTARE
	 *
	 * @param [type] $tax_rate_id
	 * @param [type] $tax_rate_value
	 * @return void
	 */
	public function updateTaxRate( $tax_rate_id, $tax_rate_value ) {

		\WC_TAX::_update_tax_rate( $tax_rate_id, array( 'tax_rate' => $tax_rate_value ) );
	}


	/**
	 * DELETE ALL TAX RATES
	 *
	 * @param [type] $tax_rate_id
	 *
	 * @return void
	 */
	public function deleteTaxRate( $tax_rate_id ) {
		\WC_TAX::_delete_tax_rate( $tax_rate_id );
	}



	public function restore_default_tax_rate_by_term( $args, $tax_rate_id, $id_aruba ) {
		$class             = $args['tax_rate_class'];
		$country           = $args['tax_rate_country'];
		$country_union     = null;
		$aruba_global_data = CustomOptions::get_option( 'aruba_global_data' );

		// remove IT from eu country
		if ( array_key_exists( 'IT', $this->get_european_union_countries( true ) ) ) {
			unset( $this->get_european_union_countries( true )['IT'] );
		}

		if ( array_key_exists( $country, $this->get_european_union_countries( true ) ) ) {
			$country_union = 'ue';
		}
		if ( array_key_exists( $country, $this->get_european_union_countries( false ) ) ) {
			$country_union = 'extra-ue';
		}
		if ( 'IT' == $country ) {
			$country_union = 'Italia';
		}

		$default_tax_rate = $aruba_global_data['tax_simple_data'][ 'taxClass_' . $class . '_' . $country_union ];
		$id_aruba         = explode( '::', $default_tax_rate )[1];

		if ( '*' == $default_tax_rate ) {
			// delete
			$this->deleteTaxRate( $tax_rate_id );
		} else {

			// update
			$this->updateTaxRate( $tax_rate_id, $default_tax_rate );
			$this->updaterubaIdInWoocommerceTaxRates( $tax_rate_id, $id_aruba );
		}
	}
}
