<?php

namespace ArubaFe\Admin\Checkout\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

use ArubaFe\Admin\Checkout\Helper\ArubaFeCheckoutHelper;
use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;


class ArubaFeAdminOrderPage {



	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'aruba_fe_enq_admin_order_script' ), 10, 1 );
		add_filter( 'woocommerce_ajax_get_customer_details', array( $this, 'aruba_fe_ajax_load_fields_values' ), 10, 3 );
		add_filter( 'woocommerce_admin_billing_fields', array( $this, 'aruba_fe_woocommerce_admin_billing_fields' ), 10, 2 );
		add_filter( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'aruba_fe_woocommerce_admin_order_data_after_billing_address' ), 20 );
	}

	public function aruba_fe_ajax_load_fields_values( $data, $customer, $user_id ) {

		foreach ( $fields = ArubaFeCheckoutHelper::getField() as $key => $field ) {

			$metaKey                 = "{$key}";
			$key                     = str_replace( 'billing_', '', $key );
			$data['billing'][ $key ] = get_user_meta( $user_id, $metaKey, true );

		}

		return $data;
	}

	public function aruba_fe_enq_admin_order_script( $hook ) {

        $screen = get_current_screen();

        if ( $screen && isset( $screen->post_type ) && $screen->post_type === 'shop_order' ) {

            $options = new ArubaFeOptionParser();

            wp_enqueue_script( 'aruba-fe-custom-fields-admin-js', ARUBA_FE_URL . 'assets/js/aruba-fe-custom-fields-admin.js', array(), '1.0',['in_footer' => true] );
            wp_localize_script(
                'aruba-fe-custom-fields-admin-js',
                'aruba_fe_settings',
                array(
                    'invoice_always_required' => $options->askForInvoice(),
                )
            );

        }
	}

	public function aruba_fe_woocommerce_admin_billing_fields( $fields ) {

		$post = get_post();

		if ( ! is_wp_error( $post ) ) {

			$fields_ = ArubaFeCheckoutHelper::getField();

			if ( ! empty( $fields_ ) ) {
				$options = new ArubaFeOptionParser();
				foreach ( $fields_ as $key => $field ) {

					$key = str_replace( 'billing_', '', $key );

					if ( $key == 'need_invoice_aruba_fe' ) {

						$options->askForInvoice();

						$opts = array();

						if ( ! $options->askForInvoice() ) {

							$opts['0'] = esc_html__( 'No', 'aruba-fatturazione-elettronica' );

						}

						$opts['1'] = esc_html__( 'Yes, I would like to receive the invoice', 'aruba-fatturazione-elettronica' );

						$field = array(
							'type'     => 'select',
							'label'    => esc_html__( 'Do you want an invoice?', 'aruba-fatturazione-elettronica' ),
							'clear'    => true,
							'required' => false,
							'options'  => $opts,
							'default'  => '0',
						);
					}

					$field['wrapper_class'] = 'form-field-wide';
					$field['show']          = false;
					$field['id']            = "_billing_{$key}";
					$field['name']          = "_billing_{$key}";

					unset( $field['class'] );

					$fields[ $key ] = $field;

				}
			}
		}

		return $fields;
	}

	public function aruba_fe_woocommerce_admin_order_data_after_billing_address( $order ) {

		$fields = ArubaFeCheckoutHelper::getField();

		$template = '<p><strong>%s</strong><br>%s</p>';

		$allowedTags = array(
			'p'      => array(),
			'strong' => array(),
			'br'     => array(),
		);

		$orderMetadata = array();

		foreach ( $fields as $key => $field ) {

			$metaKey = "_{$key}";

			$metaValue = $order->get_meta( sanitize_key( $metaKey ), true );

			$orderMetadata[ $key ] = $metaValue;

		}

		if ( $orderMetadata['billing_customer_type_aruba_fe'] == 'person' ) {

			unset( $fields['billing_partita_iva_aruba_fe'] );
			unset( $fields['billing_sdi_aruba_fe'] );
			unset( $fields['billing_pec_aruba_fe'] );

		} elseif ( $orderMetadata['billing_customer_type_aruba_fe'] == 'company' ) {

			unset( $fields['billing_need_invoice_aruba_fe'] );

			if ( $orderMetadata['billing_send_choice_invoice_aruba_fe'] == 'sdi' ) {

				unset( $fields['billing_pec_aruba_fe'] );

			} elseif ( $orderMetadata['billing_send_choice_invoice_aruba_fe'] == 'pec' ) {

				unset( $fields['billing_sdi_aruba_fe'] );

			} else {

				unset( $fields['billing_pec_aruba_fe'] );
				unset( $fields['billing_sdi_aruba_fe'] );

			}
		} else {

			return;

		}

		foreach ( $fields as $key => $field ) {

			if ( ! isset( $orderMetadata[ $key ] ) ) {
				continue;
			}

			$metaValue = $orderMetadata[ $key ];

			switch ( $key ) {

				case 'billing_customer_type_aruba_fe':
					$metaValue = $metaValue == 'company' ? esc_html__( 'Company', 'aruba-fatturazione-elettronica' ) : esc_html__( 'Person', 'aruba-fatturazione-elettronica' );

					echo wp_kses( sprintf( $template, $field['label'], $metaValue ), $allowedTags );
					break;

				case 'billing_send_choice_invoice_aruba_fe':
					$values = array(
						'sdi' => esc_html__( 'SDI (Recipient Code)', 'aruba-fatturazione-elettronica' ),
						'pec' => esc_html__( 'PEC', 'aruba-fatturazione-elettronica' ),
						'cfe' => esc_html__( 'Foreign invoice number', 'aruba-fatturazione-elettronica' ),
						'*'   => esc_html__( 'No identifier', 'aruba-fatturazione-elettronica' ),
					);

					$metaValue = $values[ $metaValue ] ?? '';

					echo wp_kses( sprintf( $template, $field['label'], $metaValue ), $allowedTags );
					break;

				case 'billing_need_invoice_aruba_fe':
					$metaValue = $metaValue == 1 ? __( 'Yes' ) : __( 'No' );

					echo wp_kses( sprintf( $template, esc_html__( 'Do you want an invoice?', 'aruba-fatturazione-elettronica' ), $metaValue ), $allowedTags );
					break;

				default:
					echo wp_kses( sprintf( $template, $field['label'], $metaValue ), $allowedTags );
					break;

			}
		}
	}
}
