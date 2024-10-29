<?php


namespace ArubaFe\Publics;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

use ArubaFe\Admin\Documents\ArubaFeDocument;
use ArubaFe\Admin\RestApi\ArubaFeApiRestManager;


/**
 * Public init Class
 **/
class PublicInit {



	public function __construct() {

		$this->init();
	}

	private function init() {

		add_action( 'init', array( $this, 'checkDownload' ) );

	}

	/**
	 * Front-end document download
	 *
	 * @return void
	 */

	public function checkDownload() {

		try {

			if ( isset( $_GET['aruba_fe_action_site'] ) ) {

				$order_id = isset( $_GET['order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) : 0;

				$post_id = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;

				if ( $post_id && is_user_logged_in() ) {

					if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'download_pdf_order' ) ) {

						throw new \Exception( __( 'Forbidden Access', 'aruba-fatturazione-elettronica' ), 404 );

					}

					$current_user_id = get_current_user_id();

					$order = new \WC_Order( $post_id );

					if ( ! $order || is_wp_error( $order ) ) {

						throw new \Exception( __( 'Order not found', 'aruba-fatturazione-elettronica' ), 404 );

					}

					if ( $order->get_user_id() !== $current_user_id ) {

						throw new \Exception( __( 'Forbidden Access', 'aruba-fatturazione-elettronica' ), 401 );

					}

					$response = ArubaFeApiRestManager::getInstance()->getInvoiceDocuments( $order_id );

					if ( $response && $response->getState() ) {

                        $ArubaFeDocument = new ArubaFeDocument();

                        if(is_a($ArubaFeDocument,'WP_Error')){
                            echo esc_html__( 'The document is not avaiable', 'aruba-fatturazione-elettronica' );
                            exit;
                        }

                        $ArubaFeDocument->download($response);

					} else {

						throw new \Exception( esc_html__( 'The document is not avaiable', 'aruba-fatturazione-elettronica' ), 404 );

					}
				} else {

					throw new \Exception( __( 'Forbidden Access', 'aruba-fatturazione-elettronica' ), 401 );

				}
			}
		} catch ( \Exception $e ) {

			wp_die( esc_html( $e->getMessage() ), esc_html( $e->getMessage() ), array( 'response' => esc_html( $e->getCode() ) ) );

		}
	}
}
