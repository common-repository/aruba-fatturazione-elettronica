<?php

namespace ArubaFe\Admin\Checkout\Helper;

use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;

if (!defined('ABSPATH')) {
    die('No direct access allowed');
}

class ArubaFeCheckoutHelper
{

    protected $regexCFIT   = '/^([0-9]{11}|[A-Z]{6}[0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{3}[A-Z])$/' ;
    protected $regexCFEXIT = '/^[A-Z0-9]{11,16}$/i';
    protected $regexSDI    = '/^[A-Z0-9]{6,7}$/i';

    public static function hasAValidCheckoutNonce(){
        return (isset($_POST['_aruba_fe_checkout_nonce'])
            && wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_POST['_aruba_fe_checkout_nonce'])), '_aruba_fe_checkout'));
    }

    public static function getFeInputV()
    {

        if (isset($_POST['_aruba_fe_checkout_nonce'])
            && wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_POST['_aruba_fe_checkout_nonce'])), '_aruba_fe_checkout')) {

            return array(
                'customer_type'     => isset($_POST['billing_customer_type_aruba_fe']) ? sanitize_text_field(wp_unslash($_POST['billing_customer_type_aruba_fe'])) : null,
                'send_invoice'      => isset($_POST['billing_send_choice_invoice_aruba_fe']) ? sanitize_text_field(wp_unslash($_POST['billing_send_choice_invoice_aruba_fe'])) : null,
                'company'           => isset($_POST['billing_company']) ? sanitize_text_field(wp_unslash($_POST['billing_company'])) : null,
                'need_invoice'      => isset($_POST['billing_need_invoice_aruba_fe']) ? sanitize_text_field(wp_unslash($_POST['billing_need_invoice_aruba_fe'])) : null,
                'dni'               => isset($_POST['billing_codice_fiscale_aruba_fe']) ? sanitize_text_field(wp_unslash($_POST['billing_codice_fiscale_aruba_fe'])) : null,
                'pi'                => isset($_POST['billing_partita_iva_aruba_fe']) ? sanitize_text_field(wp_unslash($_POST['billing_partita_iva_aruba_fe'])) : null,
                'sdi'               => isset($_POST['billing_sdi_aruba_fe']) ? sanitize_text_field(wp_unslash($_POST['billing_sdi_aruba_fe'])) : null,
                'pec'               => isset($_POST['billing_pec_aruba_fe']) ? sanitize_text_field(wp_unslash($_POST['billing_pec_aruba_fe'])) : null,
            );

        } else {

            return array(
                'customer_type' => null,
                'send_invoice' => null,
                'company' => null,
                'need_invoice' => null,
                'dni' => null,
                'pi' => null,
                'sdi' => null,
                'pec' => null,
            );

        }
    }

    public function checkRequired($name_field)
    {
        $fe_field = self::getFeInputV();

        if (is_null($fe_field['customer_type'])) {
            return true;
        }

        switch ($name_field) {
            case 'billing_codice_fiscale_aruba_fe':
                return ($fe_field['need_invoice'] == 1 && $this->isPerson()) ? true : false;
                break;
            case 'billing_partita_iva_aruba_fe':
                return false;
                break;
            case 'billing_sdi_aruba_fe':
                return ($this->isCompany() && $fe_field['send_invoice'] === 'sdi') ? true : false;
                break;
            case 'billing_pec_aruba_fe':
                return ($this->isCompany() && $fe_field['send_invoice'] === 'pec') ? true : false;
                break;
            case 'company':
                return ($this->isCompany()) ? true : false;
                break;
            case 'billing_send_choice_invoice_aruba_fe':
                return ($this->isCompany()) ? true : false;
                break;
            case 'billing_need_invoice_aruba_fe':
                return ($this->isPerson()) ? true : false;
                break;
            default:
                return false;
                break;
        }
    }

    public function isCompany()
    {
        $fe_field = self::getFeInputV();

        if ($fe_field['customer_type'] == 'company') {
            return true;
        }

        return false;
    }

    public function isPerson()
    {
        $fe_field = self::getFeInputV();

        if ($fe_field['customer_type'] == 'person') {
            return true;
        }

        return false;
    }

    public function checkValue($value, $name_field, $args = array())
    {

        switch ($name_field) {
            case 'billing_sdi_aruba_fe':
                return (preg_match($this->regexSDI, strtoupper($value))) ? true : false;
                break;
            case 'billing_pec_aruba_fe':
                $regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
                return (preg_match($regex, strtoupper($value))) ? true : false;
                break;

            case 'billing_codice_fiscale_aruba_fe':

                if($args['country'] == 'IT'){
                    $regex = $this->regexCFIT;
                }else{
                    $regex = $this->regexCFEXIT;
                }
                return (preg_match($regex, strtoupper($value))) ? true : false;
                break;
            case 'billing_partita_iva_aruba_fe':
                $minLength = 11; // Lunghezza minima consentita
                $maxLength = 28; // Lunghezza massima consentita
                $length = strlen($value);

                if ($length >= $minLength && $length <= $maxLength) {
                    return true;
                } else {
                    return false;
                }
                break;
        }
    }

    public static function getField()
    {
        $fe_checkout = new ArubaFeCheckoutHelper();

        $options = new ArubaFeOptionParser();

        $be_option_aruba_fe_need_invoice = $options->askForInvoice();

        $fields = array();

        $fields['billing_customer_type_aruba_fe'] = array(
            'type' => 'select',
            'class' => array('form-row-wide'),
            'label' => esc_html__('Customer type', 'aruba-fatturazione-elettronica'),
            'clear' => true,
            'required' => true,
            'options' => array(
                '*' => esc_html__('Select', 'aruba-fatturazione-elettronica'),
                'company' => esc_html__('Company', 'aruba-fatturazione-elettronica'),
                'person' => esc_html__('Physical person', 'aruba-fatturazione-elettronica'),
            ),
            'default' => '*',
        );
        $required = $fe_checkout->checkRequired('billing_send_choice_invoice_aruba_fe');

        $fields['billing_send_choice_invoice_aruba_fe'] = array(
            'type' => 'select',
            'class' => array('form-row-wide'),
            'label' => esc_html__('How would you like to receive an invoice?', 'aruba-fatturazione-elettronica'),
            'clear' => true,
            'required' => $required,
            'options' => array(
                'sdi' => esc_html__('SDI (Recipient Code)', 'aruba-fatturazione-elettronica'),
                'pec' => esc_html__('PEC', 'aruba-fatturazione-elettronica'),
                '*' => esc_html__('No identifier', 'aruba-fatturazione-elettronica'),
                'cfe' => esc_html__('Foreign invoice number', 'aruba-fatturazione-elettronica'),
            ),
            'default' => '*',
        );

        if ($be_option_aruba_fe_need_invoice === true) {

            $required = $fe_checkout->checkRequired('billing_need_invoice_aruba_fe');

            $fields['billing_need_invoice_aruba_fe'] = array(
                'type' => 'hidden',
                'label' => null,
                'clear' => true,
                'required' => $required,
                'value' => 1,
                'default' => '1',
            );

        } else {

            $required = $fe_checkout->checkRequired('billing_need_invoice_aruba_fe');

            $fields['billing_need_invoice_aruba_fe'] = array(
                'type' => 'checkbox',
                'class' => array('form-row-wide'),
                'label' => esc_html__('Do you want an invoice?', 'aruba-fatturazione-elettronica'),
                'clear' => true,
                'required' => false,
                'options' => array(
                    '0' => esc_html__('No', 'aruba-fatturazione-elettronica'),
                    '1' => esc_html__('Yes, I would like to receive the invoice', 'aruba-fatturazione-elettronica'),

                ),
                'default' => '0',
            );
        }

        $required = $fe_checkout->checkRequired('billing_codice_fiscale_aruba_fe');

        $fields['billing_codice_fiscale_aruba_fe'] = array(
            'class' => array('form-row-wide'),
            'label' => esc_html__('Tax code', 'aruba-fatturazione-elettronica'),
            'clear' => true,
            'required' => $required,
        );

        $required = $fe_checkout->checkRequired('billing_partita_iva_aruba_fe');
        $fields['billing_partita_iva_aruba_fe'] = array(
            'class' => array('form-row-wide'),
            'label' => esc_html__('VAT number', 'aruba-fatturazione-elettronica'),
            'clear' => true,
            'required' => $required,
        );

        $required = $fe_checkout->checkRequired('billing_sdi_aruba_fe');
        $fields['billing_sdi_aruba_fe'] = array(
            'class' => array('form-row-wide'),
            'label' => esc_html__('SDI (Recipient Code)', 'aruba-fatturazione-elettronica'),
            'clear' => true,
            'required' => $required,
        );

        $required = $fe_checkout->checkRequired('billing_pec_aruba_fe');
        $fields['billing_pec_aruba_fe'] = array(
            'class' => array('form-row-wide'),
            'label' => esc_html__('PEC address', 'aruba-fatturazione-elettronica'),
            'clear' => true,
            'required' => $required,
        );

        return $fields;
    }
}
