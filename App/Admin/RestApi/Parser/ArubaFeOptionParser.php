<?php
namespace ArubaFe\Admin\RestApi\Parser;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\CustomOptions;

class ArubaFeOptionParser
{

	protected $options;

	public function __construct()
	{
		$this->options = CustomOptions::get_option('aruba_global_data');
	}

	public function getConfigOption($key,$context = 'global_data')
	{
		return $this->options[$context][$key] ?? null;
	}

    public function getConnectionState(){

        return $this->options['api_connection']['connected'] ?? false;

    }

	public function createDraft()
	{

		switch ($this->getConfigOption('create_invoice')) {

			case 'manual_create_fe':
				return false;
				break;

			case 'automatic_create_fe':
				return true;
				break;

			default:
				return false;
				break;
		}

	}

	public function createInvoice()
	{
		switch ($this->getConfigOption('create_invoice')) {
			case 'manual_create_fe':
				return false;
				break;

			case 'automatic_create_fe':
				return true;
				break;

			default:
				return false;
				break;
		}
	}

    public function sendInvoice()
    {
        switch ($this->getConfigOption('send_invoice')) {
            case 'manual_send_fe':
                return false;
                break;

            case 'automatic_send_fe':
                return true;
                break;

            default:
                return false;
                break;
        }
    }

    public function sendDraftInvoice()
    {
        switch ($this->getConfigOption('send_invoice')) {
            case 'manual_send_fe':
                return true;
                break;

            case 'automatic_send_fe':
                return false;
                break;

            default:
                return false;
                break;
        }
    }

	public function orderStatus()
	{
		return 1;
	}

	public function createBuyerData()
	{
		return $this->getConfigOption('update_data_customer') == 'automatically_update_data_customer';
	}

	public function product_description_type()
	{

		switch ($this->getConfigOption('description_invoice')) {

			case 'descriction_product_name_fe':
				return 'full';
				break;

			case 'short_descriction_product_name_fe':
				return 'only_name';
				break;

			case 'product_name_fe':
			default:
				return 'only_name';
				break;

		}

	}

	/**
	 * @return string lo stato woocommerce impostato come valido per inviare l'ordine a FE
	 */

	public function order_state()
	{

		switch ($this->getConfigOption('order_state')) {

			case 'state_order_processing':
            case 'state_order_pending':
				return 'processing';

            case 'state_order_complete':
			default:
				return 'completed';

		}

	}

    public function order_state_numeric()
    {

        switch ($this->getConfigOption('order_state')) {

            case 'state_order_processing':
                return 1;
                break;

            case 'state_order_pending';
                return 0;
                break;

            default:

            case 'state_order_complete':
                return 2;
                break;
        }

    }

    public function getPaymentMethodCode(string $payment_method)
    {
       return $this->options['payments']["paymentsMethods_$payment_method"] ?? null;
    }

    public function getPaymentStaus( $paymentMethod)
    {
        return ($paymentMethod == 'MP08' &&
               $this->getConfigOption('reporting_receipts_paid_invoices') == 'automatic_receipts_fe');
    }

    public function exemtionActive()
    {
        return $this->getConfigOption('exemption_for_foreign') === 'apply_exemption_for_foreign';
    }


    public function getExemptionKeyValue($key){

        switch ($key){
            case 'aruba_fe_exemption_for_pf_eu':

                return $this->getConfigOption('aruba_fe_exemption_for_pf_eu','exemptions');

                break;

            case 'aruba_fe_exemption_for_co_eu';

            return $this->getConfigOption('aruba_fe_exemption_for_co_eu','exemptions');

                break;
            case 'aruba_fe_exemption_for_pf_exeu';

                return $this->getConfigOption('aruba_fe_exemption_for_pf_exeu','exemptions');

            break;
            case 'aruba_fe_exemption_for_co_exeu';

                return $this->getConfigOption('aruba_fe_exemption_for_co_exeu','exemptions');

            break;

        }

        return false;
    }

    public function askForInvoice(){

        return $this->getConfigOption('individual_create_invoce') === 'create_always_fe';

    }

    public function processPayments()
    {
        $payments = $this->options['payments'];

        $response = [];

        foreach ($payments as $key => $payment){

            $response[] =
                [
                    "key" => sanitize_text_field("$key"),
                    "value" => sanitize_text_field("$payment")
                ];



        }
        return $response;

    }


    public function processTaxes()
    {

        $payments = $this->options['tax_complex_data'];

        $response = [];

        foreach ($payments as $key => $values){

            $key = str_replace('taxComplexClass_','',$key);

            $parts = explode('_',$key);

            $tax     = $parts[0];
            $country = $parts[1];

            $response[$tax][$country] = $values;


        }

        $taxes = [];

        foreach ($response as $taxKey => $countries){

            $taxRange = [
                'name' => sanitize_text_field($taxKey),
                'mappings' => []
            ];

            foreach ($countries as $country => $tax){

                $taxRange['mappings'][] = ['key' => sanitize_text_field($country) , 'value' => sanitize_text_field($tax)];

            }

            $taxes[] = $taxRange;

        }

        return $taxes;

    }

    public function showFullPrice()
    {
        return (int)$this->getConfigOption('show_full_price') === 1;
    }

    public function getBankId()
    {
        $defaultBank = $this->getConfigOption('default_bank');
        return $defaultBank ? $this->getConfigOption('default_bank') : null;

    }

}
