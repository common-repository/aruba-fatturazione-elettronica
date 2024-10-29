<?php

namespace ArubaFe\Admin;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\AppState\AppStateManager;
use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\Label\ArubaFeLabel;
use ArubaFe\Admin\RestApi\ArubaFeApiRestManager;
use ArubaFe\Admin\RestApi\Interfaces\ArubaFeApiResponseInterface;
use ArubaFe\Admin\Session\ArubaFeSession;
use ArubaFe\Admin\Tax\Countries;
use ArubaFe\Admin\Tax\TaxUtils;
use ArubaFe\Admin\Tax\TaxBackup;

//use ArubaFe\Admin\CustomOptions;
use ArubaFe\Admin\WcBackend\InitWcBackend;

/**
 *AdminRestApi
 */
class AdminRestApi extends InitWcBackend
{

    protected $countries;
    protected $taxUtils;
    protected $defaultData = [
        "global_data" => [
            "create_invoice" => "automatic_create_fe",
            "send_invoice" => "automatic_send_fe",
            "order_state" => "state_order_complete",
            "reporting_receipts_paid_invoices" => "automatic_receipts_fe",
            "description_invoice" => "product_name_fe",
            "individual_create_invoce" => "create_always_fe",
            "update_data_customer" => "automatically_update_data_customer",
            "send_coutesy_copy" => "automatic_send_coutesy_copy",
            "exemption_for_foreign" => "not_apply_exemption_for_foreign",
            "show_full_price" => 0,
            "default_bank"   => "",
        ],
        "api_connection" => [
            "shopName" => "",
            "shopUrl" => "",
            "customerCode" => "",
            "secretCode" => "",
            "connected" => false,
            "configDone" => false,
        ],
        "payments" => [],
        "tax_simple_data" => [],
        "tax_complex_data" => [],
        'tax_config' => [],
        'exemptions' => [],

    ];

    protected $aruba_fe_defaults_payments_methods = [
        'bacs' => 'MP05',
        'cheque' => 'MP02',
        'cod' => 'MP01',
        'paypal' => 'MP08',
        'stripe' => 'MP08',
        'stripe_sepa' => 'MP08',
        '18app' => 'MP08',
        'woocommerce_payments' => 'MP08',
        'xpay' => 'MP08',
        'soisy' => 'MP08',
        'igfs' => 'MP08',
        'alipay' => 'MP08',
        'amazon' => 'MP08',

    ];


    protected $aruba_fe_api;

    /**
     *
     */
    function __construct()
    {
        $this->countries = new Countries();
        $this->taxUtils = new TaxUtils();
        $this->aruba_fe_api = ArubaFeApiRestManager::getInstance();
        $this->aruba_fe_labels = ArubaFeLabel::getInstance();
        add_action('rest_api_init', [$this, 'create_rest_routes']);
    }

    /**
     * @return void
     */
    public function create_rest_routes()
    {

        register_rest_route('aruba_fe/v1', '/get_text_data', [
            'methods' => 'GET',
            'callback' => [$this, 'get_labels'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/getTaxRateApi', [
            'methods' => 'POST',
            'callback' => [$this, 'getTaxRateApi'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/check_wc_tax_rate_enabled', [
            'methods' => 'POST',
            'callback' => [$this, 'check_wc_tax_rate_enabled'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/get_payments', [
            'methods' => 'GET',
            'callback' => [$this, 'get_payments'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/get_wc_payments', [
            'methods' => 'GET',
            'callback' => [$this, 'get_wc_payments'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/get_banks', [
            'methods' => 'POST',
            'callback' => [$this, 'get_banks'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/drop_connection', [
            'methods' => 'POST',
            'callback' => [$this, 'drop_connection'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/get_countries', [
            'methods' => 'POST',
            'callback' => [$this, 'get_countries'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/get_taxes_complete', [
            'methods' => 'POST',
            'callback' => [$this, 'get_taxes_complete'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/get_taxs', [
            'methods' => 'POST',
            'callback' => [$this, 'get_taxs'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);
        register_rest_route('aruba_fe/v1', '/delete_tax_rate', [
            'methods' => 'POST',
            'callback' => [$this, 'delete_tax_rate'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);
        register_rest_route('aruba_fe/v1', '/add_tax_rate', [
            'methods' => 'POST',
            'callback' => [$this, 'add_tax_rate'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/update_global_data', [
            'methods' => 'POST',
            'callback' => [$this, 'update_global_data'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/get_global_data', [
            'methods' => 'POST',
            'callback' => [$this, 'get_global_data'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);

        register_rest_route('aruba_fe/v1', '/check_connection', [
            'methods' => 'POST',
            'callback' => [$this, 'check_connection'],
            'permission_callback' => [$this, 'get_settings_permission']
        ]);
    }

    public function get_taxes_complete($req)
    {

        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');

        if (!$nonce) {
            return;
        }

        $data_taxes = $this->taxUtils->get_rates();
        $data = $this->aruba_fe_api->getTaxRates();

        $tax_rate_api = [];

        if ($data) {

            foreach ($data as $taxRate) {
                $tax_rate_api[] = [
                    'id' => $taxRate->id,
                    'value' => $taxRate->taxRate . '::' . $taxRate->id,
                    'label' => esc_html($taxRate->description),
                ];
            }

        }

        $allowed_countries = \WC()->countries->get_allowed_countries();

        $euCountries = $this->countries->get_active_eu_countries();

        $noEuCountries = $this->countries->get_active_extra_eu_countries();

        $countriesByZone = [
            'IT' => array_key_exists('IT', $allowed_countries) ? 'IT' : [],
            'EU' => $euCountries,
            'EXTRA_EU' => $noEuCountries,
        ];

        $response = ["success" => true, "tax_rate_api" => $tax_rate_api, 'data_taxes' => $data_taxes, 'allowed_countries' => $allowed_countries, 'allowed_countries_by_zone' => $countriesByZone];

        return rest_ensure_response($response);
    }

    public function getTaxRateApi($req)
    {
        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');

        if (!$nonce) {
            return;
        }


        $data = $this->aruba_fe_api->getTaxRates();

        $tax_rate_api = [];

        if ($data) {

            foreach ($data as $taxRate) {
                $tax_rate_api[] = [
                    'id' => $taxRate->id,
                    'value' => $taxRate->taxRate . '::' . $taxRate->id,
                    'label' => esc_html($taxRate->description),
                ];
            }

        }

        $response = ["success" => true, "tax_rate_api" => $tax_rate_api];

        return rest_ensure_response($response);
    }


    public function check_wc_tax_rate_enabled($req)
    {

        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');

        if (!$nonce) {
            return;
        }

        $aruba_api_global_data = CustomOptions::get_option('aruba_global_data');

        $taxes_enabled = apply_filters('wc_tax_enabled', get_option('woocommerce_calc_taxes') === 'yes');

        if (!$taxes_enabled) {

            $aruba_api_global_data['api_connection']['connected'] = false;

            CustomOptions::update_option('aruba_global_data', $aruba_api_global_data);

            $response = ["success" => false, "message" => "taxes_disabled", "aruba_data" => $aruba_api_global_data];

        } else {

            $response = ["success" => true, "message" => "taxes_enabled", "aruba_data" => $aruba_api_global_data];

        }


        return rest_ensure_response($response);
    }


    /**
     * @param $req
     *
     * @return void|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function get_global_data($req)
    {

        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');

        if (!$nonce) {
            return;
        }

        $connectionStatus = false;

        if (!CustomOptions::get_option('aruba_global_data')) {

            $aruba_global_data = $this->defaultData;

            CustomOptions::add_option('aruba_global_data', $this->defaultData);

        } else {

            $aruba_global_data = CustomOptions::get_option('aruba_global_data');
            $currentConnectionStatus = $aruba_global_data['api_connection']['connected'];

            /**
             * if connected check if is always valid
             * if not check if we can reconnect
             */
            if ($currentConnectionStatus) {

                $connectionStatus = $this->aruba_fe_api->getConnectionData();

                if (!$connectionStatus || !$connectionStatus->getState() || (int)$connectionStatus->getJSON()->status != 1) {

                    $aruba_global_data['api_connection']['connected'] = false;

                    CustomOptions::update_option('aruba_global_data', $aruba_global_data);
                    CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_EXPIRE, '');
                    CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_LAST_REFRESH, '');
                    CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN, '');
                    CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_USERNAME, '');
                    CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_TYPE, '');
                }

            } else {

                $connectionStatus = $this->aruba_fe_api->getConnectionData();

                if ($connectionStatus && $connectionStatus->getState()) {

                    $aruba_global_data['api_connection']['connected'] = true;

                    CustomOptions::update_option('aruba_global_data', $aruba_global_data);
                }

            }

        }


        if (!isset($aruba_global_data['tax_config']))
            $aruba_global_data['tax_config'] = [];

        if (!isset($aruba_global_data['exemptions']))
            $aruba_global_data['exemptions'] = [];

        $state = AppStateManager::hasIncompatiblePlugins();

        $partitaIva = null;
        $connected = false;
        if ($connectionStatus instanceof ArubaFeApiResponseInterface && $connectionStatus->getJSON()->status == 1) {
            $partitaIva = $connectionStatus->getJSON()->countryCode . $connectionStatus->getJSON()->vatCode;
            $connected = true;
        }

        unset($aruba_global_data['api_connection']['shopUrl']);
        unset($aruba_global_data['api_connection']['customerCode']);
        unset($aruba_global_data['api_connection']['secretCode']);

        $response = ["success" => true, "aruba_global_data" => $aruba_global_data, 'incompatible_plugins' => $state, 'conn_status' => $connected, 'vatCode' => $partitaIva];

        return rest_ensure_response($response);
    }

    protected function cleanData(&$data)
    {

        /**
         * validate the array structure
         */
        $ativeTaxClasses = \WC_Tax::get_tax_classes();
        array_unshift($ativeTaxClasses, 'standard');
        array_unshift($ativeTaxClasses, '');

        foreach ($data as $key => $value) {

            if (!isset($this->defaultData[$key]))
                unset($data[$key]);

        }

        foreach ($data['global_data'] as $key => $value) {
            if (!isset($this->defaultData['global_data'][$key]))
                unset($data['global_data'][$key]);
            else
                $data['global_data'][$key] = sanitize_text_field($value);
        }

        foreach ($data['api_connection'] as $key => $value) {
            if (!isset($this->defaultData['api_connection'][$key]))
                unset($data['api_connection'][$key]);
            else
                $data['api_connection'][$key] = sanitize_text_field($value);
        }

        if (isset($data['payments'])) {

            $sanitize = [];

            foreach ($data['payments'] as $key => $value) {
                $sanitize[sanitize_text_field($key)] = sanitize_text_field($value);
            }

            $data['payments'] = $sanitize;
        } else {

            $data['payments'] = [];

        }

        if (isset($data['tax_config'])) {

            $sanitize = [];

            foreach ($data['tax_config'] as $key => $value) {

                $name = str_replace('tax_method_','',$key);
                if(in_array($name,$ativeTaxClasses))
                    $sanitize[sanitize_text_field($key)] = sanitize_text_field($value);
            }

            $data['tax_config'] = $sanitize;

        } else {
            $data['tax_config'] = [];
        }

        if (isset($data['exemptions'])) {
            $sanitize = [];

            foreach ($data['exemptions'] as $key => $value) {
                $sanitize[sanitize_text_field($key)] = sanitize_text_field($value);
            }

            $data['exemptions'] = $sanitize;

        } else {
            $data['exemptions'] = [];
        }

        if (isset($data['tax_simple_data'])) {

            $sanitize = [];

            foreach ($data['tax_simple_data'] as $key => $value) {

                $name = str_replace('taxClass_','',$key);
                $name = explode("_",$name);
                array_pop($name);
                $name = implode("_",$name);
                if(in_array($name,$ativeTaxClasses)) {
                    $sanitize[sanitize_text_field($key)] = sanitize_text_field($value);
                }
            }

            $data['tax_simple_data'] = $sanitize;

        } else {
            $data['tax_simple_data'] = [];
        }


        if (isset($data['tax_complex_data'])) {

            $activeCountries = $this->countries->get_all_countries();

            foreach ($data['tax_complex_data'] as $key => $value) {

                $parts = explode('_', $key);

                $last = (string)array_pop($parts);
                $first = array_shift($parts);

                $name = implode("_",$parts);

                if (strlen($last) !== 2 || empty($value) || !isset($activeCountries[$last]) || !in_array($name,$ativeTaxClasses))
                    unset($data['tax_complex_data'][$key]);
                else
                    $data['tax_complex_data'][$key] = sanitize_text_field($value);
            }

        }

    }

    /**
     * @param $req
     *
     * @return void|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function update_global_data($req)
    {
        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');

        if (!$nonce) {
            return;
        }

        $update_aruba_global_data = $req['aruba_global_data'];

        $old_aruba_global_data = CustomOptions::get_option('aruba_global_data');

        $update_aruba_global_data['api_connection']['shopUrl'] = esc_url_raw($old_aruba_global_data['api_connection']['shopUrl']);
        $update_aruba_global_data['api_connection']['customerCode'] = $old_aruba_global_data['api_connection']['customerCode'];
        $update_aruba_global_data['api_connection']['secretCode'] = $old_aruba_global_data['api_connection']['secretCode'];

        $this->cleanData($update_aruba_global_data);

        $prevValueOfCurtesyCopy = isset($old_aruba_global_data['global_data']['send_coutesy_copy']) ? $old_aruba_global_data['global_data']['send_coutesy_copy'] : null;
        $newValueOfCurtesyCopy = isset($update_aruba_global_data['global_data']['send_coutesy_copy']) ? $update_aruba_global_data['global_data']['send_coutesy_copy'] : null;

        if (CustomOptions::update_option('aruba_global_data', $update_aruba_global_data)) {

            $response = ["success" => true, "data_updated" => "succefully updated"];

            $this->init($old_aruba_global_data);

            $update_aruba_global_data['tax_complex_data'] = $this->rebuildComplexTaxes();

            $update_aruba_global_data['api_connection']['configDone'] = $update_aruba_global_data['api_connection']['connected'] && $old_aruba_global_data['api_connection']['connected'];

            CustomOptions::update_option('aruba_global_data', $update_aruba_global_data);

            if (!CustomOptions::add_option('_aruba_fe_first_config_done', 1))
                CustomOptions::update_option('_aruba_fe_first_config_done', 1);

            if (
                ($newValueOfCurtesyCopy == 'automatic_send_coutesy_copy' && $prevValueOfCurtesyCopy != 'automatic_send_coutesy_copy')
                ||
                $newValueOfCurtesyCopy == 'automatic_send_coutesy_copy' && !(int)CustomOptions::get_option('_aruba_fe_send_from_date')
            ) {
                if (!CustomOptions::add_option('_aruba_fe_send_from_date', gmdate('Y-m-d H:i:s')))
                    CustomOptions::update_option('_aruba_fe_send_from_date', gmdate('Y-m-d H:i:s'));

            } else if ($newValueOfCurtesyCopy != 'automatic_send_coutesy_copy') {

                if (!CustomOptions::add_option('_aruba_fe_send_from_date', '0000-00-00 00:00:00'))
                    CustomOptions::update_option('_aruba_fe_send_from_date', "0000-00-00 00:00:00");

            }

            if($update_aruba_global_data['global_data']['default_bank'] && $update_aruba_global_data['global_data']['default_bank'] != $old_aruba_global_data['global_data']['default_bank']){

                  $banks = $this->aruba_fe_api->getBanks();

                    $bank = array_filter($banks,function($item) use ($update_aruba_global_data){
                        return $item->id == $update_aruba_global_data['global_data']['default_bank'];
                    });

                    if($bank){

                        $bank = array_pop($bank);

                        $new_bacs_accounts = [
                            'account_name'   => '',
                            'account_number' => '',
                            'bank_name'      => sanitize_text_field($bank->description),
                            'sort_code'      => '',
                            'iban'           => sanitize_text_field($bank->iban),
                            'bic'            => sanitize_text_field($bank->swiftBic),
                        ];

                        update_option('woocommerce_bacs_accounts', [$new_bacs_accounts]);

                    }

            }

        } else {

            $response = ["success" => true, "data_updated" => "Not data to updated"];

        }

        $this->aruba_fe_api->sendConfigs();

        return rest_ensure_response($response);
    }

    protected function rebuildComplexTaxes()
    {

        $wc_taxes = \WC_Tax::get_tax_classes();

        $classes = $wc_taxes ? ['', ...$wc_taxes] : [''];

        $object = [];

        foreach ($classes as $tax_class) {

            $taxes = \WC_Tax::get_rates_for_tax_class($tax_class);
            $taxName = $tax_class ? $tax_class : 'standard';
            $taxNamePrefix = "taxComplexClass_{$taxName}";
            foreach ($taxes as $tax) {
                $taxRate = (int)$tax->tax_rate;
                $object["{$taxNamePrefix}_{$tax->tax_rate_country}"] = "{$taxRate}::{$tax->id_aruba}";
            }
        }

        return $object;

    }

    public function add_tax_rate($req)
    {

        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');

        if (!$nonce) {
            return;
        }

        if (\WC_Tax::create_tax_class($req['tax_class'], strtolower($req['tax_class']))) {

            $updated_tax = $this->taxUtils->get_rates();
            $response = ["success" => true, "data_taxes" => $updated_tax];

        } else {

            $response = ["success" => false, "data_taxes" => 'not added'];

        }


        return rest_ensure_response($response);
    }


    public function delete_tax_rate($req)
    {

        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');

        if (!$nonce) {
            return;
        }

        if (\WC_Tax::delete_tax_class_by('name', $req['rate_type'])) {

            $updated_tax = $this->taxUtils->get_rates();

            $response = ["success" => true, "data_taxes" => $updated_tax];

        } else {

            $response = ["success" => false, "data_taxes" => 'not deleted'];

        }


        return rest_ensure_response($response);
    }

    /**
     * @param $req
     *
     * @return void|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function get_taxs($req)
    {

        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');

        if (!$nonce) {
            return;
        }

        $data_taxes = $this->taxUtils->get_rates();

        $response = ["success" => true, "data_taxes" => $data_taxes];

        return rest_ensure_response($response);
    }

    public function get_banks($req){

        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');

        if (!$nonce) {
            return;
        }

        $data = $this->aruba_fe_api->getBanks();

        $response = ["success" => true, "banks" => $data];

        return rest_ensure_response($response);

    }

    /**
     * @param $req
     *
     * @return void|\WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function get_countries($req)
    {
        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');

        if (!$nonce) {
            return;
        }

        $countries = array_values($this->countries->get_all_countries());


        $response = ["success" => true, "countries" => $countries];


        return rest_ensure_response($response);
    }

    public function check_connection($req)
    {
        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');

        if (!$nonce) {
            return;
        }

        $session = ArubaFeSession::getInstance();
        $maxErrors = ArubaFeConstants::ARUBA_FE_MAX_ERRORS;
        $minErrorInterval = ArubaFeConstants::ARUBA_FE_MIN_ERROR_INTERVAL;
        $currentErrors = (int)$session->get('aruba_fe_error_count');
        $minTimeRetry = (int)$session->get('aruba_next_retry');

        if ($minTimeRetry > 0 && $minTimeRetry < time()) {

            $session->set('aruba_fe_error_count', 0);
            $session->set('aruba_next_retry', 0);
            $currentErrors = 0;
            $minTimeRetry = 0;
        }

        if ($currentErrors >= $maxErrors) {

            $response = ["success" => false,
                "button" => '',
                "is_warning" => true,
                /* translators: 1: max errors, 2: min interval in seconds. */
                "message" => sprintf(esc_html__('You have exceeded the maximum limit of %1$s attempts to enter codes. Please wait %2$s seconds before trying again.', 'aruba-fatturazione-elettronica'),
                    $maxErrors,
                    $minErrorInterval
                )];

            return rest_ensure_response($response);


        }


        $shopName = sanitize_text_field(str_replace(' ', '-', $req['api_data']['shopName']));

        $customerCode = sanitize_text_field($req['api_data']['customerCode']);

        $secretCode = sanitize_text_field($req['api_data']['secretCode']);

        ###########

        $api = $this->aruba_fe_api->connect($shopName, $customerCode, $secretCode);

        ###########

        if ($api->getState()) {



            $aruba_api_global_data = CustomOptions::get_option('aruba_global_data');
            $aruba_api_global_data['api_connection']['connected'] = true;
            $aruba_api_global_data['api_connection']['secretCode'] = $secretCode;
            $aruba_api_global_data['api_connection']['customerCode'] = $customerCode;
            $aruba_api_global_data['api_connection']['shopName'] = $shopName;
            $aruba_api_global_data['api_connection']['shopUrl'] = esc_url_raw(get_bloginfo('url'));
            $aruba_api_global_data['api_connection']['connected'] = true;

            CustomOptions::update_option('aruba_global_data', $aruba_api_global_data);

            $this->aruba_fe_api = ArubaFeApiRestManager::getInstance(true);

            $this->aruba_fe_api->getToken(true);

            $partitaIva = $api->getJSON()->countryCode . $api->getJSON()->vatCode;

            $response = [
                "success" => true,
                "button" => "Connected",
                "message" => "Connected",
                'data' => $aruba_api_global_data,
                'vatCode' => $partitaIva
            ];

        } else {

            $currentErrors++;
            $session->set('aruba_fe_error_count', $currentErrors);
            $session->set('aruba_next_retry', time() + $minErrorInterval);

            if ($currentErrors >= $maxErrors) {

                $response = ["success" => false,
                    "button" => '',
                    "is_warning" => true,
                    /* translators: 1: max errors, 2: min interval in seconds. */
                    "message" => sprintf(esc_html__('You have exceeded the maximum limit of %1$s attempts to enter codes. Please wait %2$s seconds before trying again.', 'aruba-fatturazione-elettronica'),
                        $maxErrors,
                        $minErrorInterval
                    )];

                return rest_ensure_response($response);


            }

            $httpStatus = $api->getHTTPStatus();

            $txt = $httpStatus == 401 ? $this->aruba_fe_labels->getLabel('aruba_fe_auth_fail') : $this->aruba_fe_labels->getLabel('aruba_fe_unexpected_error');

            $response = ["success" => false,
                "button" => $this->aruba_fe_labels->getLabel('aruba_fe_connect'),
                "message" => $txt];

        }

        return rest_ensure_response($response);

    }


    /**
     * Drops the connection to the Aruba API and returns an appropriate response.
     *
     * @return WP_REST_Response containing success status, button text, message, and updated global data
     * @throws Exception if unable to update options
     */
    public function drop_connection($req)
    {
        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');
        if (!$nonce) {
            return;
        }

        $this->aruba_fe_api->disconnect();

        // carico i dati salvati in precedenza
        $aruba_api_global_data = CustomOptions::get_option('aruba_global_data');

        $aruba_api_global_data['api_connection']['connected'] = false;
        $aruba_api_global_data['api_connection']['shopName'] = '';
        $aruba_api_global_data['api_connection']['secretCode'] = '';
        $aruba_api_global_data['api_connection']['customerCode'] = '';
        $aruba_api_global_data['api_connection']['shopUrl'] = '';
        $aruba_api_global_data['api_connection']['configDone'] = false;


        CustomOptions::update_option('aruba_global_data', $aruba_api_global_data);

        $response = [
            "success" => true,
            "button" => "Connected",
            "message" => "Connected",
            'data' => $aruba_api_global_data
        ];
        $response = ["success" => true, "button" => "Connect", "message" => "not connected", 'data' => $aruba_api_global_data];
        return rest_ensure_response($response);
    }


    public function get_wc_payments()
    {
        $wc_gateways = new \WC_Payment_Gateways();
        $payment_gateways = $wc_gateways->get_available_payment_gateways();

        $wc_avaiable_payments = [];

        foreach ($payment_gateways as $gateway_id => $gateway) {

            $title = $gateway->get_title();
            $wc_avaiable_payments[] = [
                'name' => $title,
                'code' => $gateway_id,
                'slug' => $gateway_id
            ];
        };
        return $wc_avaiable_payments;

    }

    public function get_labels($req)
    {
        $nonce = wp_verify_nonce($req['nonce'], 'aruba_fe_nonce');
        if (!$nonce) {
            return;
        }
        return rest_ensure_response($this->aruba_fe_labels);

    }


    /**
     * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function get_payments()
    {
        $wc_gateways = new \WC_Payment_Gateways();

        $payment_gateways = $wc_gateways->get_available_payment_gateways();

        $avaiable_payments = [];

        $data = $this->aruba_fe_api->getPaymentsMethods();

        if ($data) {

            foreach ($data as $paymentsMethod) {
                $avaiable_payments[] = [
                    'name' => $paymentsMethod->description,
                    'code' => $paymentsMethod->code,
                    'slug' => $paymentsMethod->code,
                ];
            }

        }


        $response = [
            'avaiable_payments' => $avaiable_payments,
            'wc_avaiable_payments' => $this->get_wc_payments(),
            'default_payments' => $this->aruba_fe_defaults_payments_methods,
        ];

        return rest_ensure_response($response);
    }

    /**
     * @return true
     */
    public function get_settings_permission()
    {

        return current_user_can('manage_options');

    }


}
