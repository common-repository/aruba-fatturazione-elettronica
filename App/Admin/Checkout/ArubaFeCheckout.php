<?php

namespace ArubaFe\Admin\Checkout;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

use ArubaFe\Admin\Checkout\Admin\ArubaFeAdminOrderPage;
use ArubaFe\Admin\Checkout\Site\ArubaFeCheckoutFields;
use ArubaFe\Admin\Checkout\Site\ArubaFeOrderPage;
use ArubaFe\Admin\Constants\ArubaFeConstants;

class ArubaFeCheckout {

	public function __construct() {
		new ArubaFeCheckoutFields();
		new ArubaFeOrderPage();


		$this->registerHooks();
	}

	protected function registerHooks() {

		add_action( 'wp_enqueue_scripts', array( $this, 'aruba_fe_checkout_script' ), 99999999 );
		add_action( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'aruba_fe_woocommerce_order_item_get_formatted_meta_data' ), 100 );
        add_action( 'woocommerce_checkout_after_order_review', array($this,'addArubaFeCustomNocne'),10);
        add_action( 'woocommerce_after_edit_address_form_billing', array($this,'addArubaFeCustomNocne'),10);
        add_action( 'admin_init',array($this,'addArubaFeAdminOrderPage'));

	}

    public function addArubaFeAdminOrderPage(){

       new ArubaFeAdminOrderPage();

    }

    public function addArubaFeCustomNocne(){
        wp_nonce_field('_aruba_fe_checkout', '_aruba_fe_checkout_nonce');
    }

	public function aruba_fe_checkout_script() {
		global $pagenow;

		if ( ( is_checkout() || is_account_page() ) ) {

			wp_register_script( 'aruba-fe-custom-fields-js', ARUBA_FE_URL . 'assets/js/aruba-fe-custom-fields.js', array(), '1.3', true );
			wp_enqueue_script( 'aruba-fe-custom-fields-js' );
			wp_localize_script(
				'aruba-fe-custom-fields-js',
				'aruba_fe_labels_fe',
				array(
					'address'             => esc_html__( 'Address', 'aruba-fatturazione-elettronica' ),
					'dati_fiscali'        => esc_html__( 'Fiscal data', 'aruba-fatturazione-elettronica' ),
					'dati_fiscali_desc'   => esc_html__( '(One of the two fields required)', 'aruba-fatturazione-elettronica' ),
					'main_heading_1'      => esc_html__( 'Invoicing data', 'aruba-fatturazione-elettronica' ),
					'main_heading_1_desc' => esc_html__( '(name/surname or company name required)', 'aruba-fatturazione-elettronica' ),
					'main_heading_2'      => esc_html__( 'Invoicing data', 'aruba-fatturazione-elettronica' ),
					'main_heading_3'      => esc_html__( 'Customer data', 'aruba-fatturazione-elettronica' ),
					'isCheckout'          => ! is_account_page(),
				)
			);

            wp_enqueue_script( 'arubafe-checkoutpage', ARUBA_FE_URL . 'assets/js/aruba-fe-checkout.js',array(),'1.1',['in_footer' => true] );

		}
	}

	public function aruba_fe_woocommerce_order_item_get_formatted_meta_data( $formatted_meta ) {

		$excludeMeta = array(
			ArubaFeConstants::ARUBA_FE_ORDER_ITEM_DISCOUNT,
			ArubaFeConstants::ARUBA_FE_ORDER_ITEM_TAX_RATE,
			ArubaFeConstants::ARUBA_FE_ORDER_ITEM_TAX_RATE_ID,
		);

		$return = array();

		foreach ( $formatted_meta as $meta ) {
			if ( ! in_array( $meta->key, $excludeMeta ) ) {
				$return[] = $meta;
			}
		}

		return $return;
	}
}
