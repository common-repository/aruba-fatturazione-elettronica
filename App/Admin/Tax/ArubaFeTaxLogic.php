<?php

namespace ArubaFe\Admin\Tax;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;

class ArubaFeTaxLogic {

	protected $isActive;
	protected $it;
	protected $eu;
	protected $world;

	public function __construct() {

		$this->options   = new ArubaFeOptionParser();
		$this->isActive  = $this->options->exemtionActive();
		$this->countries = new Countries();
		$this->it        = 'IT';
		$this->eu        = array_keys( $this->countries->get_european_union_countries() );
		$this->world     = array_keys( $this->countries->get_european_union_countries( false ) );
	}

	public function mayOverrideTaxLogic() {
	}

	public function mayOverrideTaxLogicOnCheckout() {

		if ( ! $this->isActive ) {

			WC()->cart->get_customer()->set_is_vat_exempt( false );
			return false;

		}

		$customerBillingType = null;

		$country = null;
 		if ( is_ajax() && (
                check_ajax_referer('update-order-review','security',false)
                ||
                (isset($_POST['_aruba_fe_checkout_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_aruba_fe_checkout_nonce'])),'_aruba_fe_checkout') ))
        ) {

			$country  = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : WC()->cart->get_customer()->get_billing_country();

			$postData = isset( $_POST['post_data'] ) ? sanitize_text_field( wp_unslash( $_POST['post_data'] ) ) : null;

			if ( $postData ) {

				parse_str( $postData, $postParams );

				$customerBillingType = isset( $postParams['billing_customer_type_aruba_fe'] ) ? sanitize_text_field( wp_unslash( $postParams['billing_customer_type_aruba_fe'] ) ) : null;

			} else {

				$customerBillingType = isset( $_POST['billing_customer_type_aruba_fe'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_customer_type_aruba_fe'] ) ) : null;
				$country             = isset( $_POST['billing_country'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) : null;

			}

		} else {

			$customerBillingData = WC()->cart->get_customer()->get_data();
			$country             = WC()->cart->get_customer()->get_billing_country();

			foreach ( $customerBillingData['meta_data'] as $metadata ) {
				$metadata = $metadata->get_data();
				if ( $metadata['key'] == 'billing_customer_type_aruba_fe' ) {
					$customerBillingType = $metadata['value'];
				}
			}
		}

		$customerBillingType = ( in_array( $customerBillingType, array( 'person', 'company' ) ) ) ? $customerBillingType : null;



		if ( ! $customerBillingType || ! $country ) {

			WC()->cart->get_customer()->set_is_vat_exempt( false );
			return false;

		}

		if ( $country == 'IT' ) {
			WC()->cart->get_customer()->set_is_vat_exempt( false );
			return;
		}

		/**
		 * implemento una logia base per fare delle prove
		 * solo le aziende extra ue
		 */

        if($customerBillingType == 'company'){

            $exemptionKey = in_array( $country, $this->eu ) ? 'aruba_fe_exemption_for_pf_exeu' : 'aruba_fe_exemption_for_co_exeu' ;

        }else{

            $exemptionKey = in_array( $country, $this->eu ) ? 'aruba_fe_exemption_for_pf_eu' : 'aruba_fe_exemption_for_co_eu' ;

        }

		$isExempted = $this->options->getExemptionKeyValue( $exemptionKey );

		if ( $isExempted ) {

			WC()->cart->get_customer()->set_is_vat_exempt( true );
			return true;
		}

		return false;
	}

	public function getTaxRateExemption( string $country, string $customerBillingType ) {

		$exemptionKey  = $customerBillingType == 'company' ? 'aruba_fe_exemption_for_co_' : 'aruba_fe_exemption_for_pf_';
		$exemptionKey .= in_array( $country, $this->eu ) ? 'eu' : 'exeu';
		$isExempted    = $this->options->getExemptionKeyValue( $exemptionKey );
		$isExempted    = explode( '::', $isExempted );

		return ( new class($isExempted) {

			public $tax_rate;
			public $id_aruba;

			public function __construct( $isExempted ) {
				$this->tax_rate = $isExempted[0];
				$this->id_aruba = $isExempted[1];
			}
		} );
	}
}