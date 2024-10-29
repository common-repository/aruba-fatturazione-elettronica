<?php

namespace ArubaFe\Admin\Checkout\Site;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

use ArubaFe\Admin\Checkout\Helper\ArubaFeOrderHelper;

class ArubaFeOrderPage {



	protected $requiredMetadata = array(
		'person'  => array(
			'customer_type_aruba_fe',
			'need_invoice_aruba_fe',
			'codice_fiscale_aruba_fe',
		),
		'company' => array(
			'customer_type_aruba_fe',
			'send_choice_invoice_aruba_fe',
			'sdi_aruba_fe',
			'pec_aruba_fe',
			'codice_fiscale_aruba_fe',
			'partita_iva_aruba_fe',
		),

	);

	public function __construct() {

		add_filter( 'woocommerce_order_get_formatted_billing_address', array( $this, 'aruba_fe_woocommerce_order_get_formatted_billing_address' ), 1000, 3 );
		add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'aruba_fe_woocommerce_order_formatted_billing_address' ), 10, 2 );
		add_filter( 'woocommerce_account_orders_columns', array( $this, 'aruba_fe_woocommerce_account_orders_columns' ), 1000, 1 );
		add_filter( 'woocommerce_my_account_my_orders_column_order_invoice', array( $this, 'aruba_fe_woocommerce_my_account_my_orders_column_order_invoice' ), 1000, 1 );
	}

	public function aruba_fe_woocommerce_order_get_formatted_billing_address( $address, $raw_address, $WC_Order ) {

		if ( isset( $raw_address['customer_type_aruba_fe'] ) && $raw_address['customer_type_aruba_fe'] ) {

			if ( $raw_address['customer_type_aruba_fe'] == ArubaFeOrderHelper::getValTxt( 'person', 'customer_type_aruba_fe' ) ) {
				$new_fields = array_reverse( $this->requiredMetadata['person'] );
			} else {
				$new_fields = array_reverse( $this->requiredMetadata['company'] );
			}

			foreach ( $new_fields as $field ) {
				if ( isset( $raw_address[ $field ] ) && $raw_address[ $field ] ) {
					$address = $raw_address[ $field ] . '<br />' . $address;
				}
			}
		}
		return $address;
	}

	public function aruba_fe_woocommerce_order_formatted_billing_address( $address, $WC_Order ) {

		$prefix = '_billing_';

		$meta_data = $WC_Order->get_meta_data();

		if ( empty( $meta_data ) ) {
			return $address;
		}

		$meta_data_array = array();

		/**
		 * init array to prevent warning
		 */
		foreach ( $this->requiredMetadata as $array ) {
			foreach ( $array as $init ) {
				$meta_data_array[ $prefix . $init ] = '';
			}
		}

		foreach ( $meta_data as $s_meta_data ) {
			$data_s                            = $s_meta_data->get_data();
			$meta_data_array[ $data_s['key'] ] = $data_s['value'];
		}

		if ( isset( $meta_data_array[ $prefix . 'customer_type_aruba_fe' ] ) && $meta_data_array[ $prefix . 'customer_type_aruba_fe' ] == 'person' ) {

			$new_address = array(
				'customer_type_aruba_fe'  => ArubaFeOrderHelper::getValTxt( $meta_data_array[ $prefix . 'customer_type_aruba_fe' ], 'customer_type_aruba_fe' ),
				'need_invoice_aruba_fe'   => ArubaFeOrderHelper::getValTxt( $meta_data_array[ $prefix . 'need_invoice_aruba_fe' ], 'need_invoice_aruba_fe' ),
				'codice_fiscale_aruba_fe' => ArubaFeOrderHelper::getValTxt( $meta_data_array[ $prefix . 'codice_fiscale_aruba_fe' ], 'codice_fiscale_aruba_fe' ),
			);

			$address = array_merge( $address, $new_address );

		} elseif ( isset( $meta_data_array[ $prefix . 'customer_type_aruba_fe' ] ) && $meta_data_array[ $prefix . 'customer_type_aruba_fe' ] == 'company' ) {

			$new_address = array(
				'customer_type_aruba_fe'       => ArubaFeOrderHelper::getValTxt( $meta_data_array[ $prefix . 'customer_type_aruba_fe' ], 'customer_type_aruba_fe' ),
				'send_choice_invoice_aruba_fe' => '<b>' . esc_html__( 'Invoice reception method:', 'aruba-fatturazione-elettronica' ) . '</b> ' . ArubaFeOrderHelper::getValTxt( $meta_data_array[ $prefix . 'send_choice_invoice_aruba_fe' ], 'customer_type_aruba_fe' ),
				'sdi_aruba_fe'                 => ArubaFeOrderHelper::getValTxt( $meta_data_array[ $prefix . 'sdi_aruba_fe' ], 'customer_type_aruba_fe' ),
				'pec_aruba_fe'                 => ArubaFeOrderHelper::getValTxt( $meta_data_array[ $prefix . 'pec_aruba_fe' ], 'customer_type_aruba_fe' ),
				'codice_fiscale_aruba_fe'      => ArubaFeOrderHelper::getValTxt( $meta_data_array[ $prefix . 'codice_fiscale_aruba_fe' ], 'customer_type_aruba_fe' ),
				'partita_iva_aruba_fe'         => ArubaFeOrderHelper::getValTxt( $meta_data_array[ $prefix . 'partita_iva_aruba_fe' ], 'customer_type_aruba_fe' ),
			);

			$address = array_merge( $address, $new_address );

		}

		return $address;
	}

	public function aruba_fe_woocommerce_account_orders_columns( $tr_header ) {

		$tr_header['order_invoice'] = esc_html__( 'Invoice', 'aruba-fatturazione-elettronica' );

		return $tr_header;
	}

	function aruba_fe_woocommerce_my_account_my_orders_column_order_invoice( $order ) {
		ArubaFeOrderHelper::getPdf( $order->get_id() );
	}
}
