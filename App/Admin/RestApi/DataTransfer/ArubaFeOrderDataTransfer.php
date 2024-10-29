<?php

namespace ArubaFe\Admin\RestApi\DataTransfer;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access allowed' );
}

use ArubaFe\Admin\ArubaFeWcUtils;
use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;
use ArubaFe\Admin\Traits\ArubaFeLogTrait;
use WC_Order_Item_Product;
use WC_Order_Item_Shipping;

class ArubaFeOrderDataTransfer {


	use ArubaFeLogTrait;

	protected $order;
	protected $order_id;
	protected $aruba_fe_order_id;
	const ARUBA_FE_INVOICE_TYPES = array(
		'sdi' => 0,
		'cfe' => 1,
		'pec' => 2,
		'*'   => 3,
	);

	protected $defaultZeroRate = '';

    protected $testMode = false;

	const ARUBA_FE_INVOICE_TYPES_POST_META = array(
		'sdi' => '_billing_sdi_aruba_fe',
		'cfe' => '_billing_partita_iva_aruba_fe',
		'pec' => '_billing_pec_aruba_fe',
		'*'   => '_billing_none',
	);
	protected $regexPEC                    = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
	protected $regexCF                     = '/^([0-9]{11}|[A-Z]{6}[0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{3}[A-Z])$/';
	protected $regexCFEXT                  = '/^[A-Z0-9]{11,16}$/i';
    protected $regexSDI                    = '/^[A-Z0-9]{6,7}$/i';
    protected $minLength                   = 11; // Lunghezza minima consentita
	protected $maxLength                   = 28; // Lunghezza massima consentita
	protected $errors                      = array();


	public function __construct( $order_id, $aruba_fe_order_id = null ) {
		$this->order             = wc_get_order( $order_id );
		$this->order_id          = $order_id;
		$this->fe_options        = new ArubaFeOptionParser();
		$this->aruba_fe_order_id = $aruba_fe_order_id;
		$this->numberOfDecimals  = 2;

		$this->defaultZeroRate = ArubaFeWcUtils::getDefaultZeroRate();
	}

	protected function formatNumberToSend( $number ) {

		return number_format( $number, $this->numberOfDecimals, '.', '' );
	}

	protected function getOrderPostMeta( $key, $isSingle = true, $default = '' ) {

        $meta = $this->order->get_meta(sanitize_key($key),$isSingle);

        return ! is_null( $meta ) ? $meta : $default;

	}

    public function setTestMode($set = true){

        $this->testMode = $set;

    }

	public function build($testOnly = false,$forceInvoice = false, $forceDraft = false) {

		$data = $this->order->get_data();

		$is_vat_exempt = $this->order->get_meta( 'is_vat_exempt' ) === 'yes';

		$arubaFeData = array(
			'date'   => $data['date_created']->date( 'Y-m-d\TH:i:s\Z' ),
			'number' => $this->order_id,
		);

		if ( ! is_null( $this->aruba_fe_order_id ) ) {
			$arubaFeData['id'] = $this->aruba_fe_order_id;
		}

		$isPerson     = false;
		$customerType = $this->getOrderPostMeta( '_billing_customer_type_aruba_fe' );
		if ( $customerType == 'person' ) {

			$isPerson = true;

			if ( $this->getOrderPostMeta( '_billing_send_choice_invoice_aruba_fe' ) != '*' ) {
				$this->order->update_meta_data( '_billing_send_choice_invoice_aruba_fe', '*' );
			}
		}

        $_billing_send_choice_invoice_aruba_fe = $this->getOrderPostMeta( '_billing_send_choice_invoice_aruba_fe' );

		$arubaFeData['buyer'] = array(
			'firstName'            => $this->removeNonLatinChars( sanitize_text_field( $this->order->get_billing_first_name() ) ),
			'lastName'             => $this->removeNonLatinChars( sanitize_text_field( $this->order->get_billing_last_name() ) ),
            'email'                => sanitize_email( $this->order->get_billing_email() ),
			'vatCode'              => $isPerson ? '' : sanitize_text_field( $this->getOrderPostMeta( '_billing_partita_iva_aruba_fe' ) ),
			'taxState'             => ( $isPerson || ! $this->getOrderPostMeta( '_billing_partita_iva_aruba_fe' ) ) ? '' : sanitize_text_field( $this->order->get_billing_country() ),
			'taxCode'              => sanitize_text_field( strtoupper( $this->getOrderPostMeta( '_billing_codice_fiscale_aruba_fe' ) ) ),
			'destinationReference' => array(
				'kind'  => self::ARUBA_FE_INVOICE_TYPES[ $_billing_send_choice_invoice_aruba_fe ] ?? null,
				'value' =>
					$this->getOrderPostMeta( '_billing_send_choice_invoice_aruba_fe' ) === 'cfe' ? 'XXXXXXX' :
                        ($_billing_send_choice_invoice_aruba_fe ? sanitize_text_field( $this->getOrderPostMeta( self::ARUBA_FE_INVOICE_TYPES_POST_META[ $_billing_send_choice_invoice_aruba_fe ] ) )
						: null),
			),
		);

        if($arubaFeData['buyer']['destinationReference']['kind'] == 0){

                $arubaFeData['buyer']['destinationReference']['value'] = strtoupper($arubaFeData['buyer']['destinationReference']['value']);

        }


        $regexCF = $this->order->get_billing_country() === 'IT' ? $this->regexCF : $this->regexCFEXT;

		if ( $customerType == 'company' ) {

			$arubaFeData['buyer']['designation'] = sanitize_text_field( $this->order->get_billing_company() );

			if ( empty( $arubaFeData['buyer']['designation'] ) ) {

                if ( empty( $arubaFeData['buyer']['firstName'] )  || empty( $arubaFeData['buyer']['lastName'] )) {

                    $this->addError( esc_html__( 'Company name or first name and last name are required', 'aruba-fatturazione-elettronica' ) );

                }

			}else{
                $arubaFeData['buyer']['firstName'] = '';
                $arubaFeData['buyer']['lastName']  = '';
            }

			if ( empty( $arubaFeData['buyer']['taxCode'] ) && empty( $arubaFeData['buyer']['vatCode'] ) ) {

				$this->addError( esc_html__( 'Company taxCode or vatCode are required', 'aruba-fatturazione-elettronica' ) );

			} else {

				if ( ! empty( $arubaFeData['buyer']['taxCode'] ) && ! preg_match( $regexCF, strtoupper( $arubaFeData['buyer']['taxCode'] ) ) ) {

					$this->addError( esc_html__( 'Company taxCode is not in the correct format', 'aruba-fatturazione-elettronica' ) );

				}

				if ( ! empty( $arubaFeData['buyer']['vatCode'] ) && ! ( strlen( $arubaFeData['buyer']['vatCode'] ) >= $this->minLength && strlen( $arubaFeData['buyer']['vatCode'] ) <= $this->maxLength ) ) {

					$this->addError( esc_html__( 'Company vatCode is not in the correct format', 'aruba-fatturazione-elettronica' ) );

				}
			}

			if ( $arubaFeData['buyer']['destinationReference']['kind'] == 0 ) {

				if ( empty( $arubaFeData['buyer']['destinationReference']['value'] ) ) {

					$this->addError( esc_html__( 'Company Destination Reference is required', 'aruba-fatturazione-elettronica' ) );

				}else if( ! ( preg_match( $this->regexSDI ,$arubaFeData['buyer']['destinationReference']['value']) ) ){

                    $this->addError( esc_html__( 'Company Destination Reference is not in the correct format', 'aruba-fatturazione-elettronica' ) );

                }
			}

			if ( $arubaFeData['buyer']['destinationReference']['kind'] == 2 ) {

				if ( ! ( preg_match( $this->regexPEC ,$arubaFeData['buyer']['destinationReference']['value']) ) ) {

					$this->addError( esc_html__( 'Company PEC is not in the correct format', 'aruba-fatturazione-elettronica' ) );

				}
			}
		} elseif ( $customerType == 'person' ) {

			if ( empty( $arubaFeData['buyer']['firstName'] ) ) {
				$this->addError( esc_html__( 'Customer first name is required', 'aruba-fatturazione-elettronica' ) );
			}

			if ( empty( $arubaFeData['buyer']['lastName'] ) ) {
				$this->addError( esc_html__( 'Customer last name is required', 'aruba-fatturazione-elettronica' ) );
			}

			if ( empty( $arubaFeData['buyer']['taxCode'] ) ) {

				$this->addError( esc_html__( 'Customer taxCode is required', 'aruba-fatturazione-elettronica' ) );

			} elseif ( ! preg_match( $regexCF, strtoupper( $arubaFeData['buyer']['taxCode'] ) ) ) {

				$this->addError( esc_html__( 'Customer taxCode is not in the correct format', 'aruba-fatturazione-elettronica' ) );

			}
		} else {

			$this->addError( esc_html__( 'Customer type is required', 'aruba-fatturazione-elettronica' ) );

		}

		$address = array(
			'type'         => 'Billing',
			'state'        => sanitize_text_field( $this->order->get_billing_country() ),
			'province'     => $this->order->get_billing_country() == 'IT' ? sanitize_text_field( substr( $this->order->get_billing_state(), 0, 2 ) ) : '',
			'city'         => sanitize_text_field( $this->removeNonLatinChars( $this->order->get_billing_city() ) ),
			'streetName'   => sanitize_text_field( $this->removeNonLatinChars( $this->order->get_billing_address_1() ) ),
			'streenNumber' => '',
			'zipCode'      => sanitize_text_field( $this->order->get_billing_postcode() ),
			'designation'  => 'buyer_billing_addresses_' . $this->order_id,
		);

        $zipCode = sanitize_text_field( $this->order->get_billing_postcode() );

        if($address['state'] === 'IT'){

            $address['zipCode']     = $zipCode;

        }else{

            $address['streetName'] .= " $zipCode";
            $address['zipCode']     = "00000";

        }

		$this->validateAddress( $address );

		$arubaFeData['buyer']['addresses'][] = $address;

		$hasDifferentAddressForShipping = ! empty( $this->order->get_shipping_address_1() );

		$shippingAddress = array(
			'type'         => 'Shipping',
			'state'        => sanitize_text_field( $hasDifferentAddressForShipping ? $this->order->get_shipping_country() : $this->order->get_billing_country() ),
			'province'     => sanitize_text_field( $this->removeNonLatinChars(
				$hasDifferentAddressForShipping ?
					( $this->order->get_shipping_country() == 'IT' ? substr( $this->order->get_shipping_state(), 0, 2 ) : '' ) :
					( $this->order->get_billing_country() == 'IT' ? substr( $this->order->get_billing_state(), 0, 2 ) : '' )
            ) ),
			'city'         => sanitize_text_field( $this->removeNonLatinChars($hasDifferentAddressForShipping ? $this->order->get_shipping_city() : $this->order->get_billing_city() ) ),
			'streetName'   => sanitize_text_field( $this->removeNonLatinChars($hasDifferentAddressForShipping ? $this->order->get_shipping_address_1() : $this->order->get_billing_address_1() ) ),
			'streenNumber' => '',
			'zipCode'      => sanitize_text_field( $hasDifferentAddressForShipping ? $this->order->get_shipping_postcode() : $this->order->get_billing_postcode() ),
			'designation'  => 'Sede spedizione',
		);

        $zipCode = $hasDifferentAddressForShipping ? $this->order->get_shipping_postcode() : $this->order->get_billing_postcode();

        if($shippingAddress['state'] === 'IT'){

            $shippingAddress['zipCode']     = $zipCode;

        }else{

            $shippingAddress['streetName'] .= " $zipCode";
            $shippingAddress['zipCode']     = "00000";

        }

		$this->validateAddress( $shippingAddress );

		$arubaFeData['buyer']['addresses'][] = $shippingAddress;

		$arubaFeData['products'] = array();

		$product_description_type = $this->fe_options->product_description_type();

		$maxTaxIdForShipping = new class() {
			public $shippingId;
			public $shippingTaxRate;
		};

        $items = $this->order->get_items();

        if(empty($items)){

            $this->addError( esc_html__( "Order can't be empty", 'aruba-fatturazione-elettronica' ) );

        }

		foreach ($items  as $order_line_id => $product ) {

			$productObject      = $product->get_product();
			$regularPrice       = (float)$productObject->get_regular_price();
			$order_item_product = new WC_Order_Item_Product( $product->get_id() );
			$tax_data           = ArubaFeWcUtils::getTaxData( $order_item_product );
			$quantity           = $product->get_quantity();
			$totalRealPrice     = $regularPrice * $quantity;
			$total              = $product->get_total();

			if ( '0' !== $product->get_tax_class() && 'taxable' === $product->get_tax_status() && wc_tax_enabled() ) {

				$taxRate    = sanitize_text_field( wc_get_order_item_meta( $order_line_id, ArubaFeConstants::ARUBA_FE_ORDER_ITEM_TAX_RATE ) );
				$refTaxRate = sanitize_text_field( wc_get_order_item_meta( $order_line_id, ArubaFeConstants::ARUBA_FE_ORDER_ITEM_TAX_RATE_ID ) );

			} else {

				$taxRate    = 0;
				$refTaxRate = $this->defaultZeroRate;

			}

            if( ! is_numeric($taxRate) || ! $refTaxRate ){
                // translators: %s: woocommerce product name.
                $this->addError( sprintf( esc_html__( "Product %s doesn't have related tax rate", 'aruba-fatturazione-elettronica' ), $productObject->get_name() ) );
                continue;

            }

            $variation = wc_get_order_item_meta($order_line_id, ArubaFeConstants::ARUBA_FE_ORDER_ITEM_DISCOUNT);

            $useAsFullPrice = $this->fe_options->showFullPrice();


            if (!$useAsFullPrice && $variation != 0) {

                $productRow['discount'][] = array(
                    'kind' => $variation > 0 ? 'SC' : 'MG',
                    'amount' => (float)(abs($variation)),
                );

            } else if($useAsFullPrice && $variation != 0) {

                    $regularPrice += -1 * (float)($variation);
            }

            $singleItemWithTax = $regularPrice * (1 + ($taxRate / 100));

            $totalItemsWithTax = $total * (1 + ($taxRate / 100));

            $productRow = array(
                'code' => sanitize_text_field($productObject->get_sku()),
                'measurementUnit' => 'Pz',
                'netAmount' => $this->formatNumberToSend((float)$regularPrice),
                'grossAmount' => $this->formatNumberToSend((float)$singleItemWithTax),
                'taxRate' => $taxRate,
                'refTaxCode' => $refTaxRate,
                'quantity' => (int)$quantity,
                'totalNetAmount' => $this->formatNumberToSend(sanitize_text_field($total)),
                'totalGrossAmount' => $this->formatNumberToSend($totalItemsWithTax)
			);

			if ( $productRow['taxRate'] > $maxTaxIdForShipping->shippingTaxRate ) {
				$maxTaxIdForShipping->shippingId      = $productRow['refTaxCode'];
				$maxTaxIdForShipping->shippingTaxRate = $productRow['taxRate'];
			}

			switch ( $product_description_type ) {
				case 'full':
					$productRow['description'] = sanitize_text_field( $this->removeNonLatinChars( $this->removeNonLatinChars( $productObject->get_name() ) . ' - ' . wp_kses_post( $productObject->get_description() ) ) );
					break;
				case 'short':
					$productRow['description'] = sanitize_text_field($this->removeNonLatinChars( $productObject->get_name() ) . ' - ' . wp_kses_post( $productObject->get_short_description() ) );
					break;
				default:
					$productRow['description'] = sanitize_text_field( $this->removeNonLatinChars( $productObject->get_name() ) );
					break;
			}

            $variation = wc_get_order_item_meta( $order_line_id, ArubaFeConstants::ARUBA_FE_ORDER_ITEM_DISCOUNT );

			if ( $variation > 0 ) {

				$productRow['discount'][] = array(
					'kind'   => 'SC',
					'amount' => (float) $variation,
				);

			}elseif ( $variation < 0 ) {

                $productRow['discount'][] = array(
                    'kind'   => 'MG',
                    'amount' => (float) abs( $variation ),
                );

            }

			$arubaFeData['products'][] = $productRow;
		}

        $shippingMethods = $this->order->get_shipping_methods();

		if ( $shippingMethods ) {

			foreach ( $shippingMethods as $shippingMethod ) {

				$taxRate    = sanitize_text_field( wc_get_order_item_meta( $shippingMethod->get_id(), ArubaFeConstants::ARUBA_FE_ORDER_ITEM_TAX_RATE ) );
				$refTaxRate = sanitize_text_field( wc_get_order_item_meta( $shippingMethod->get_id(), ArubaFeConstants::ARUBA_FE_ORDER_ITEM_TAX_RATE_ID ) );


                if(!is_numeric($taxRate) || ! $refTaxRate){
                    // translators: %s: woocommerce shipping name.
                    $this->addError( sprintf( esc_html__( "Shipping %s doesn't have related tax rate", 'aruba-fatturazione-elettronica' ), $productObject->get_name() ) );
                    continue;

                }


                $shippingRow = array(
                    'code'             => sanitize_text_field( $shippingMethod->get_method_id() ),
                    'measurementUnit'  => 'Nr',
                    'netAmount'        => $this->formatNumberToSend( (float) $shippingMethod->get_total() ),
                    'grossAmount'      => $this->formatNumberToSend( (float) $shippingMethod->get_total() + (float) $shippingMethod->get_total_tax() ),
                    'taxRate'          => sanitize_text_field( $taxRate ),
                    'refTaxCode'       => sanitize_text_field( $refTaxRate ),
                    'quantity'         => 1,
                    'totalNetAmount'   => $this->formatNumberToSend( (float) $shippingMethod->get_total() ),
                    'totalGrossAmount' => $this->formatNumberToSend( (float) $shippingMethod->get_total() + $shippingMethod->get_total_tax() ),
                    'description'      => esc_html__( 'Shipping - ', 'aruba-fatturazione-elettronica' ) . $shippingMethod->get_method_title(),
                );

                $arubaFeData['products'][] = $shippingRow;
			}
		}

		$arubaFeData['fulfillment'] = array(
			'status'   => 1,
			'date'     => $data['date_created']->date( 'Y-m-d\TH:i:s\Z' ),
			'expected' => $data['date_created']->date( 'Y-m-d\TH:i:s\Z' ),
		);

		$arubaFeData['documentReferences'] = array(
			$data['number'],
		);

		$paymentMethod = sanitize_text_field( $this->fe_options->getPaymentMethodCode( $this->order->get_payment_method() ) );

		if ( ! $paymentMethod ) {

			$pMethod = $this->order->get_payment_method();
			$pMethod = $pMethod ? $pMethod : esc_html__( 'None', 'aruba-fatturazione-elettronica' );
			$this->addError( sprintf(
                // translators: %s: woocommerce payment method name.
                esc_html__( 'Payment method `%s` isn`t valid', 'aruba-fatturazione-elettronica' ), $pMethod ) );

		}

        $paymentRow = array(
            'refPaymentMethod' => $paymentMethod,
            'amount'           => $this->formatNumberToSend( $this->order->get_total() ),
            'dueDate'          => $data['date_created']->date( 'Y-m-d\TH:i:s\Z' ),
        );

        if($paymentMethod === 'MP05'){

            $paymentRow['refBank'] = sanitize_text_field( $this->fe_options->getBankId() );

        }

		$arubaFeData['availablePayments'] = array(
			array(
				'paymentCondition' => 'TP02',
				'payments'         => array(
					$paymentRow
                    ,
				),
			),
		);

		/**
		 * This settings come from options but may be overridden inside api for specific functions
		 */

        $createDraft = $forceDraft   || ( $this->fe_options->createDraft() && $this->fe_options->sendDraftInvoice() && $this->fe_options->order_state() == $this->order->get_status() );
        $createAndSendInvoice = $forceInvoice || ( $this->fe_options->createInvoice() && $this->fe_options->sendInvoice() && $this->fe_options->order_state() == $this->order->get_status() );

		$arubaFeData['additionalInfo'] = array(
			'createDraft'     => $createDraft,
			'sendInvoice'     => $createAndSendInvoice,
			'orderStatus'     => $this->fe_options->orderStatus(),
			'createBuyerData' => $this->fe_options->createBuyerData(),
			'paymentStatus'   => (int) $this->order->is_paid(),
		);

		/**
		 * Hook per la modifica dei dati
		 */

		$arubaFeData = apply_filters( 'aruba_fe_before_send_order', $arubaFeData, $this );

		$this->writeOrderLog( $arubaFeData, $this->order_id );

		if ( ! empty( $this->errors ) ) {

			$this->registerErrorsInOrder();

			if ( $arubaFeData['additionalInfo']['sendInvoice'] ) {

				update_post_meta( $this->order_id, ArubaFeConstants::ARUBA_FE_INVOICE_ERROR, true );

			} elseif ( $arubaFeData['additionalInfo']['createDraft'] ) {

				update_post_meta( $this->order_id, ArubaFeConstants::ARUBA_FE_DRAFT_INVOICE_ERROR, true );

			}

			return false;

        }else{

            delete_post_meta($this->order_id, ArubaFeConstants::ARUBA_FE_ERRORS_LIST);

        }

		return $arubaFeData;
	}

	protected function validateAddress( array $address ) {

		$type = $address['type'] == 'Billing' ? __( 'Billing', 'aruba-fatturazione-elettronica' ) : __( 'Shipping', 'aruba-fatturazione-elettronica' );

		if ( empty( $address['streetName'] ) ) {
			$this->addError( sprintf(
            // translators: %s: woocommerce address type.
                esc_html__( '%s address is required', 'aruba-fatturazione-elettronica' ), $type ) );
		}

		if ( empty( $address['city'] ) ) {
			$this->addError( sprintf(
            // translators: %s: woocommerce address type.
                esc_html__( '%s city is required', 'aruba-fatturazione-elettronica' ), $type ) );
		}

		if ( empty( $address['zipCode'] ) ) {
			$this->addError( sprintf(
            // translators: %s: woocommerce address type.
                esc_html__( '%s zipCode is required', 'aruba-fatturazione-elettronica' ), $type ) );
		} elseif ( strlen( $address['zipCode']  ) < 5) {

			$this->addError( sprintf(
            // translators: %s: woocommerce address type.
                esc_html__( '%s zipCode is not in the correct format', 'aruba-fatturazione-elettronica' ), $type ) );

		}
	}

	public function getErrors() {
		return $this->errors;
	}

	public function addError( $error ) {
		$this->errors[] = $error;
	}

	protected function registerErrorsInOrder() {

        $config = [
          'br' => [],
        ];

        $text = '<br/>';
        $optionText = [];
        foreach ($this->errors as $error){

            $text .= "- $error<br/>";
            $optionText[] = sanitize_text_field($error);
        }

        update_post_meta($this->order_id, ArubaFeConstants::ARUBA_FE_ERRORS_LIST, serialize($optionText));

        if( ! $this->testMode ){
		    $this->order->add_order_note( sprintf( wp_kses(
                // translators: %s: error description.
                __("Order can't be sent to Aruba Electronic Invoicing:%s", 'aruba-fatturazione-elettronica') ,$config ), $text ) );
	    }
    }

    /**
        * @param string $text
        * @return string
     */

    protected function removeNonLatinChars(string $text){

        return preg_replace('/[^\x{0020}-\x{007E}\x{00A0}-\x{00FF}]/u','',$text);

    }

}
