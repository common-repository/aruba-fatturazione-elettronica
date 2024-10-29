<?php
namespace ArubaFe\Admin\Checkout\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\Filters\CustomFilters;
use ArubaFe\Admin\Label\ArubaFeStatusesLabel;

class ArubaFeOrderHelper {

	static function getPdf( int $id_order ) {

		$invoices = get_post_meta( $id_order, ArubaFeConstants::ARUBA_FE_INVOICE_DATA, true );

		if ( ! $invoices ) {
			return;
		}

		foreach ( $invoices as $invoice ) {

			/**
			 * only success status
			 */

			if ( in_array( $invoice->status, array( 1, 3, 7, 8 ) ) ) {

				echo '<a class="aruba-fe-label label-success" target="_blank" href="' . esc_url(
					wp_nonce_url(
						site_url(
							'?aruba_fe_action_site=download&type=invoice&order_id=' . CustomFilters::sanitize_alphanumeric($invoice->id) . '&post_id=' . $id_order
						),
						'download_pdf_order'
					)
				) . '">'
					. esc_html( ArubaFeStatusesLabel::getInvoiceLabel($invoice->status) ) . '</a>';

			}
		}
	}

	static function getValTxt( $meta, $key ) {
		switch ( $meta . '.' . $key ) {
			case 'person.customer_type_aruba_fe':
				$meta = esc_html__( 'Private', 'aruba-fatturazione-elettronica' );
				break;
			case 'company.customer_type_aruba_fe':
				$meta = esc_html__( 'Company', 'aruba-fatturazione-elettronica' );
				break;
			case '*.customer_type_aruba_fe':
				$meta = esc_html__( 'No identifier', 'aruba-fatturazione-elettronica' );
				break;
			case '0.need_invoice_aruba_fe':
				$meta = esc_html__( 'No', 'aruba-fatturazione-elettronica' );
				break;
			case '1.need_invoice_aruba_fe':
				$meta = esc_html__( 'Yes', 'aruba-fatturazione-elettronica' );
				break;
			case '*.send_choice_invoice_aruba_fe':
				$meta = esc_html__( 'Not selected', 'aruba-fatturazione-elettronica' );
				break;
			case 'cfe.send_choice_invoice_aruba_fe':
				$meta = esc_html__( 'Foreign invoice number', 'aruba-fatturazione-elettronica' );
				break;
			default:
				break;
		}
		return $meta;
	}
}
