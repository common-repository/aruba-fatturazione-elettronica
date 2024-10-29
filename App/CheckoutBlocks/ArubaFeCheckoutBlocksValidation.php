<?php
namespace ArubaFe\CheckoutBlocks;

defined('ABSPATH') or die('Direct access is not allowed');

use ArubaFe\Admin\Checkout\Helper\ArubaFeCheckoutHelper;
use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;

class ArubaFeCheckoutBlocksValidation
{

    protected $errors = [];

    public function getErrors()
    {
        return $this->errors;
    }

    public function validate($fe_field, $postData)
    {

        $this->errors = [];

        $fe_checkout = new ArubaFeCheckoutHelper();
        $country = $postData['country'] ?? null;

        if ($fe_field['customer_type'] == 'company') {

            $name = isset($postData['first_name']) ? sanitize_text_field(wp_unslash($postData['first_name'])) : null;
            $surname = isset($postData['last_name']) ? sanitize_text_field(wp_unslash($postData['last_name'])) : null;
            $companyname = isset($postData['company']) ? sanitize_text_field(wp_unslash($postData['company'])) : null;

            if (!$companyname && (empty($name) || empty($surname))) {

                $this->errors[] = wp_kses(
                    __('<strong>Error :</strong>You must fill company name or name/surname', 'aruba-fatturazione-elettronica'),
                    array(
                        'strong' => array(),
                        'b' => array(),
                    )
                );

            }

            if(!$fe_field['send_invoice']){
                $this->errors[] =
                    wp_kses(
                        __('<strong>Error :</strong>How would you like to receive an invoice is a required field', 'aruba-fatturazione-elettronica'),
                        array(
                            'strong' => array(),
                            'b' => array(),
                        )
                    );
            }

            if (!($fe_field['dni'] || $fe_field['pi']) && $fe_field['send_invoice'] != 'cfe') {
                $this->errors[] =
                    wp_kses(
                        __('<strong>Error :</strong>You must fill in the field tax code or VAT number', 'aruba-fatturazione-elettronica'),
                        array(
                            'strong' => array(),
                            'b' => array(),
                        )
                    );
            }

            if (($fe_field['send_invoice'] == 'sdi') && !$fe_checkout->checkValue($fe_field['sdi'], 'billing_sdi_aruba_fe')) {
                $this->errors[] =
                    wp_kses(
                        __('<strong>Error :</strong> Company Destination Reference is not in the correct format', 'aruba-fatturazione-elettronica'),
                        array(
                            'strong' => array(),
                            'b' => array(),
                        )
                    );
            }

            if (($fe_field['send_invoice'] == 'pec') && !$fe_checkout->checkValue($fe_field['pec'], 'billing_pec_aruba_fe')) {
                $this->errors[] =
                    wp_kses(
                        __('<strong>Error :</strong> You must fill in the pec field correctly', 'aruba-fatturazione-elettronica'),
                        array(
                            'strong' => array(),
                            'b' => array(),
                        )
                    );
            }

            if ($fe_field['dni'] && !$fe_checkout->checkValue($fe_field['dni'], 'billing_codice_fiscale_aruba_fe',array('country'=>$country))) {
                $this->errors[] =
                    wp_kses(
                        __('<strong>Fiscal Code</strong> is not filled in correctly', 'aruba-fatturazione-elettronica'),
                        array(
                            'strong' => array(),
                            'b' => array(),
                        )
                    );
            }

            if ($fe_field['pi'] && !$fe_checkout->checkValue($fe_field['pi'], 'billing_partita_iva_aruba_fe')) {
                $this->errors[] =
                    wp_kses(
                        __('<strong>VAT number</strong> not filled in correctly', 'aruba-fatturazione-elettronica'),
                        array(
                            'strong' => array(),
                            'b' => array(),
                        )
                    );
            }

        } elseif ($fe_field['customer_type'] == 'person') {

            $options = new ArubaFeOptionParser();

            $be_option_aruba_fe_need_invoice = !$options->askForInvoice();

            if(!$be_option_aruba_fe_need_invoice)
                $fe_field['need_invoice'] = 1;

            if ($fe_field['need_invoice'] == 1 && $fe_field['dni'] && !$fe_checkout->checkValue($fe_field['dni'], 'billing_codice_fiscale_aruba_fe',array('country'=>$country))) {

                $this->errors[] =
                    wp_kses(
                        __('<strong>Fiscal Code</strong> is not filled in correctly', 'aruba-fatturazione-elettronica'),
                        array(
                            'strong' => array(),
                            'b' => array(),
                        )
                    );

            }
        } else {

            $this->errors[] =
                wp_kses(
                    __('<strong>Select customer type</strong> is a required field', 'aruba-fatturazione-elettronica'),
                    array(
                        'strong' => array(),
                        'b' => array(),
                    )
                );

        }

    }
}