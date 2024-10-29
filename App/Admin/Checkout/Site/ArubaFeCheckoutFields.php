<?php

namespace ArubaFe\Admin\Checkout\Site;

if (!defined('ABSPATH')) {
    die('No direct access allowed');
}

use ArubaFe\Admin\Checkout\Helper\ArubaFeCheckoutHelper;
use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;
use WC_Checkout;

class ArubaFeCheckoutFields
{

    public function __construct()
    {

        add_action('woocommerce_checkout_process', array($this, 'aruba_fe_checkout_validation'), 10, 2);
        add_filter('woocommerce_checkout_fields', array($this, 'aruba_fe_checkout'));
        add_filter('woocommerce_billing_fields', array($this, 'fe_woocommerce_billing_fields'));
    }

    public function aruba_fe_checkout_validation()
    {

        $fe_field = ArubaFeCheckoutHelper::getFeInputV();

        if (isset($_POST['_aruba_fe_checkout_nonce'])
            && wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_POST['_aruba_fe_checkout_nonce'])), '_aruba_fe_checkout')) {


            $fe_checkout = new ArubaFeCheckoutHelper();

            $country = $_POST['billing_country'] ?? null;

            if ($fe_field['customer_type'] == 'company') {

                $name = isset($_POST['billing_first_name']) ? sanitize_text_field(wp_unslash($_POST['billing_first_name'])) : null;
                $surname = isset($_POST['billing_last_name']) ? sanitize_text_field(wp_unslash($_POST['billing_last_name'])) : null;
                $companyname = isset($_POST['billing_company']) ? sanitize_text_field(wp_unslash($_POST['billing_company'])) : null;

                if (!$companyname && (empty($name) || empty($surname))) {

                    wc_add_notice(
                        wp_kses(
                            __('<strong>Error :</strong>You must fill company name or name/surname', 'aruba-fatturazione-elettronica'),
                            array(
                                'strong' => array(),
                                'b' => array(),
                            )
                        ),
                        'error'
                    );

                }

                if (!($fe_field['dni'] || $fe_field['pi']) && $fe_field['send_invoice'] != 'cfe') {
                    wc_add_notice(
                        wp_kses(
                            __('<strong>Error :</strong>You must fill in the field tax code or VAT number', 'aruba-fatturazione-elettronica'),
                            array(
                                'strong' => array(),
                                'b' => array(),
                            )
                        ),
                        'error'
                    );
                }

                if (($fe_field['send_invoice'] == 'sdi') && !$fe_checkout->checkValue($fe_field['sdi'], 'billing_sdi_aruba_fe')) {
                    wc_add_notice(
                        wp_kses(
                            __('<strong>Error :</strong> Company Destination Reference is not in the correct format', 'aruba-fatturazione-elettronica'),
                            array(
                                'strong' => array(),
                                'b' => array(),
                            )
                        ),
                        'error'
                    );
                }

                if (($fe_field['send_invoice'] == 'pec') && !$fe_checkout->checkValue($fe_field['pec'], 'billing_pec_aruba_fe')) {
                    wc_add_notice(
                        wp_kses(
                            __('<strong>Error :</strong> You must fill in the pec field correctly', 'aruba-fatturazione-elettronica'),
                            array(
                                'strong' => array(),
                                'b' => array(),
                            )
                        ),
                        'error'
                    );
                }

                if ($fe_field['dni'] && !$fe_checkout->checkValue($fe_field['dni'], 'billing_codice_fiscale_aruba_fe',array('country'=>$country))){
                    wc_add_notice(
                        wp_kses(
                            __('<strong>Fiscal Code</strong> is not filled in correctly', 'aruba-fatturazione-elettronica'),
                            array(
                                'strong' => array(),
                                'b' => array(),
                            )
                        ),
                        'error',
                        array('error-field' => 'billing_codice_fiscale_aruba_fe')
                    );
                }

                if ($fe_field['pi'] && !$fe_checkout->checkValue($fe_field['pi'], 'billing_partita_iva_aruba_fe')) {
                    wc_add_notice(
                        wp_kses(
                            __('<strong>VAT number</strong> not filled in correctly', 'aruba-fatturazione-elettronica'),
                            array(
                                'strong' => array(),
                                'b' => array(),
                            )
                        ),
                        'error',
                        array('error-field' => 'billing_partita_iva_aruba_fe')
                    );
                }
            } elseif ($fe_field['customer_type'] == 'person') {

                $options = new ArubaFeOptionParser();

                $be_option_aruba_fe_need_invoice = $options->askForInvoice();

                if ($be_option_aruba_fe_need_invoice && !(isset($fe_field['need_invoice']) && $fe_field['need_invoice'] == 1)) {

                    wc_add_notice(
                        wp_kses(
                            __('<strong>Need invoice </strong>is not filled in correctly'),
                            'aruba-fatturazione-elettronica',
                            array(
                                'strong' => array(),
                                'b' => array(),
                            )
                        ),
                        'error',
                        array('error-field' => 'billing_codice_fiscale_aruba_fe')
                    );

                }

                if ($fe_field['need_invoice'] == 1 && $fe_field['dni'] && !$fe_checkout->checkValue($fe_field['dni'], 'billing_codice_fiscale_aruba_fe',array('country'=>$country))) {

                    wc_add_notice(
                        wp_kses(
                            __('<strong>Fiscal Code</strong> is not filled in correctly', 'aruba-fatturazione-elettronica'),
                            array(
                                'strong' => array(),
                                'b' => array(),
                            )
                        ),
                        'error',
                        array('error-field' => 'billing_codice_fiscale_aruba_fe')
                    );

                }
            } else {

                wc_add_notice(
                    wp_kses(
                        __('<strong>Select customer type</strong> is a required field', 'aruba-fatturazione-elettronica'),
                        array(
                            'strong' => array(),
                            'b' => array(),
                        )
                    ),
                    'error',
                    array('error-field' => 'customer_type')
                );

            }

        } else {
            wc_add_notice(
                wp_kses(
                    __('<strong>Error:</strong> Invalid nonce', 'aruba-fatturazione-elettronica'),
                    array(
                        'strong' => array(),
                        'b' => array(),
                    )
                ),
                'error'
            );
        }
    }

    public function aruba_fe_checkout($fields)
    {
        $input_ = ArubaFeCheckoutHelper::getField();

        $fields['billing']['billing_country']['class'] = array('form-row-first');

        foreach ($input_ as $k => $v) {
            $fields['billing'][$k] = $v;
        }

        return $fields;
    }

    public function fe_woocommerce_billing_fields($fields)
    {

        $fe_checkout = new ArubaFeCheckoutHelper();

        $input = ArubaFeCheckoutHelper::getField();

        $fields = array_merge($fields, $input);

        $required = $fe_checkout->checkRequired('company');

        if (ArubaFeCheckoutHelper::hasAValidCheckoutNonce()) {

            if ($required) {

                $fields['billing_first_name']['required'] = false;
                $fields['billing_last_name']['required'] = false;
                $fields['billing_company']['required'] = false;

            } else {

                $fields['billing_company']['required'] = false;

            }
        }

        return $fields;
    }
}
