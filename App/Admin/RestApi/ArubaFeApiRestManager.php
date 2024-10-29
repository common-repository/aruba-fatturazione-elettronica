<?php

namespace ArubaFe\Admin\RestApi;

if (!defined('ABSPATH')) {
    die('No direct access allowed');
}

use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\CustomOptions;
use ArubaFe\Admin\Email\ArubaFeInvalidVersionEmail;
use ArubaFe\Admin\Filters\CustomFilters;
use ArubaFe\Admin\RestApi\Api\ArubaFeApiResponse;
use ArubaFe\Admin\RestApi\Api\ArubaFeBaseApi;
use ArubaFe\Admin\RestApi\DataTransfer\ArubaFeDataTransferSettings;
use ArubaFe\Admin\RestApi\DataTransfer\ArubaFeOrderDataTransfer;
use ArubaFe\Admin\RestApi\Helper\ArubaFeHelper;
use ArubaFe\Admin\RestApi\Interfaces\ArubaFeApiResponseInterface;
use ArubaFe\Admin\Traits\ArubaFeLogTrait;

class ArubaFeApiRestManager extends ArubaFeBaseApi
{

    use ArubaFeLogTrait;

    protected static $instance;

    protected $arubaFeEndopoint;

    protected $clientId;

    protected $clientSecret;

    protected $shopName;

    protected static $tollerance = 10;

    /**
     * per evitare che una connessione venga rifiutata a metà aggiungo un margine TODO implementare nelle richieste la renogoziazione del token
     */


    public function __construct()
    {

        $this->arubaFeEndopoint = ARUBA_FE_EP;
        $this->domain = esc_url_raw(get_bloginfo('url'));
        $aruba_api_global_data = CustomOptions::get_option('aruba_global_data');
        $this->clientId = CustomFilters::sanitize_alphanumeric(($aruba_api_global_data['api_connection']['customerCode'] ?? ''));
        $this->clientSecret = $aruba_api_global_data['api_connection']['secretCode'] ?? null;
        $this->shopName = $aruba_api_global_data['api_connection']['shopName'] ?? null;
    }

    public static function getInstance($recreate = false)
    {

        if (is_null(self::$instance) || $recreate) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /***
     * @param $shopName Il nome del negozio
     * @param $customerCode Il codice cliente preso da FE
     * @param $secretCode Il codice segreto preso da FE
     * @return ArubaFeApiResponseInterface
     */

    public function connect(string $shopName, string $customerCode, string $secretCode): ArubaFeApiResponseInterface
    {
        $response = $this->doPostCurl(
            'api/ecommerce/connection',
            array(
                'clientId' => $customerCode,
                'clientSecret' => $secretCode,
                'domain' => $this->domain,
                'shopName' => $shopName,
            )
        );

        return $response;
    }

    public function getToken($forceRefresh = false): ArubaFeApiResponseInterface
    {

        $lastToken      = CustomOptions::get_option(ArubaFeConstants::ARUBA_FE_TOKEN);
        $lastRefresh    = CustomOptions::get_option(ArubaFeConstants::ARUBA_FE_TOKEN_LAST_REFRESH);
        $refreshTime    = CustomOptions::get_option(ArubaFeConstants::ARUBA_FE_TOKEN_EXPIRE);
        $username       = CustomOptions::get_option(ArubaFeConstants::ARUBA_FE_TOKEN_USERNAME);
        $tokenType      = CustomOptions::get_option(ArubaFeConstants::ARUBA_FE_TOKEN_TYPE);

        if (!$forceRefresh && $lastToken && $lastRefresh && $refreshTime && $username && (time() < ($lastRefresh + $refreshTime - self::$tollerance))) {

            return new ArubaFeApiResponse(
                wp_json_encode(
                    array(
                        'access_token' => $lastToken,
                        'userName' => $username,
                        'token_type' => $tokenType,
                    )
                ),
                200
            );

        }

        $request = array(
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'domain' => $this->domain,
            'shopName' => $this->shopName,
            'version' => ArubaFeHelper::getPluginVersion(),
        );

        $response = $this->doPostCurl('api/ecommerce/token', $request);

        if ($response->getState()) {

            try {

                $json = $response->getJSON();

                $time = time();

                if (!CustomOptions::add_option(ArubaFeConstants::ARUBA_FE_TOKEN_EXPIRE, sanitize_text_field($json->expires_in))) {
                    CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_EXPIRE, sanitize_text_field($json->expires_in));
                }

                if (!CustomOptions::add_option(ArubaFeConstants::ARUBA_FE_TOKEN_LAST_REFRESH, sanitize_text_field($time))) {
                    CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_LAST_REFRESH, sanitize_text_field($time));
                }

                if (!CustomOptions::add_option(ArubaFeConstants::ARUBA_FE_TOKEN, sanitize_text_field($json->access_token))) {
                    CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN, sanitize_text_field($json->access_token));
                }

                if (!CustomOptions::add_option(ArubaFeConstants::ARUBA_FE_TOKEN_USERNAME, sanitize_text_field($json->userName))) {
                    CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_USERNAME, sanitize_text_field($json->userName));
                }

                if (!CustomOptions::add_option(ArubaFeConstants::ARUBA_FE_TOKEN_TYPE, sanitize_text_field($json->token_type))) {
                    CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_TYPE, sanitize_text_field($json->token_type));
                }

                ArubaFeInvalidVersionEmail::unsetWarning();

            } catch (\Exception $e) {
                // $this->writeLog('token_error', $e->getMessage());
            }

        } elseif ($response) {


            CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_EXPIRE, '');
            CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_LAST_REFRESH, '');
            CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN, '');
            CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_USERNAME, '');
            CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_TYPE, '');

            $json = $response->getJSON();

            if(isset($json->error) && $json->error === 'invalid_version') {

                ArubaFeInvalidVersionEmail::registerWarning($request['version']);

            }

        }

        return $response;
    }

    public function getBanks(){

        $token = $this->getToken();

        if ($token->getState()) {

            $banks = $this->doGetCurl('api/ecommerce/banks', array(), $token->getJSON()->access_token, null, true);

            if ($banks->getState()) {

                return $banks->getJSON();

            } else {
                return false;
            }
        } else {

            return false;
        }

    }

    public function getTaxRates()
    {

        $token = $this->getToken();

        if ($token->getState()) {

            $taxRates = $this->doGetCurl('api/ecommerce/tax-codes', array(), $token->getJSON()->access_token, null, true);

            if ($taxRates->getState()) {

                ArubaFeHelper::registerDefaultZeroRate($taxRates->getJSON());

                return $taxRates->getJSON();

            } else {
                return false;
            }
        } else {

            return false;
        }
    }

    public function getPaymentsMethods()
    {

        $token = $this->getToken();

        if ($token->getState()) {

            $paymentsMethods = $this->doGetCurl(
                'api/ecommerce/payment-methods',
                array(),
                $token->getJSON()->access_token,
                null,
                true
            );

            if ($paymentsMethods->getState()) {

                return $paymentsMethods->getJSON();

            } else {

                return false;

            }
        } else {

            return false;
        }
    }

    public function disconnect()
    {

        $token = $this->getToken();

        if ($token->getState()) {

            $result = $this->doDeleteCurl(
                "api/ecommerce/{$this->clientId}/connection",
                $token->getJSON()->access_token
            );

            if ($result->getState()) {

                $aruba_api_global_data = CustomOptions::get_option('aruba_global_data');

                $aruba_api_global_data['api_connection']['connected'] = false;
                $aruba_api_global_data['api_connection']['shopName'] = '';
                $aruba_api_global_data['api_connection']['secretCode'] = '';
                $aruba_api_global_data['api_connection']['customerCode'] = '';
                $aruba_api_global_data['api_connection']['shopUrl'] = '';
                $aruba_api_global_data['api_connection']['configDone'] = false;
                CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN,'');
                CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_EXPIRE,'');
                CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_TYPE,'');
                CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_USERNAME,'');
                CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_TOKEN_LAST_REFRESH,'');
                CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_DEFAULT_ZERO_RATE,'');
                CustomOptions::update_option('aruba_global_data', $aruba_api_global_data);

                return true;

            } else {

                return false;

            }
        } else {

            return false;
        }
    }

    public function sendOrder($order_id, $aruba_fe_order_id = null, $isInvoiceRequest = false)
    {

        $order = new ArubaFeOrderDataTransfer($order_id, $aruba_fe_order_id);

        $wcOrder = new \WC_Order($order_id);

        $token = $this->getToken();

        if ($token->getState()) {

            $dataToSend = $order->build(false,$isInvoiceRequest,false);

            if (!$dataToSend) {

                return false;

            }

            $response = $this->doPostCurl(
                'api/ecommerce/orders',
                $dataToSend,
                $token->getJSON()->access_token
            );

            if ($response->getState()) {

                $json = $response->getJSON();

                update_post_meta($order_id, '_aruba_fe_order_id', sanitize_text_field($json->order->id));
                update_post_meta($order_id, '_aruba_fe_order_number', sanitize_text_field($json->order->number));
                update_post_meta($order_id, '_aruba_fe_order_status', sanitize_text_field($json->order->status));
                update_post_meta($order_id, '_aruba_fe_invoices', $this->sanitizeJson($json->invoices));
                update_post_meta($order_id, '_aruba_fe_drafts', $this->sanitizeJson($json->drafts));
                update_post_meta($order_id, '_aruba_fe_has_error', false);

                if ($dataToSend['additionalInfo']['sendInvoice']) {

                    if(!isset($json->invoices) || empty($json->invoices) ){

                        if(isset($json->drafts) && !empty($json->drafts) ){

                            $wcOrder->add_order_note(esc_html__('The invoice could not be created, its draft was created', 'aruba-fatturazione-elettronica'));

                        }else{

                            $wcOrder->add_order_note(esc_html__('A problem occurred while creating the invoice, please check the e-Invoicing panel', 'aruba-fatturazione-elettronica'));

                        }

                    }else{

                        $wcOrder->add_order_note(esc_html__('The invoice was generated', 'aruba-fatturazione-elettronica'));

                    }

                    update_post_meta($order_id, ArubaFeConstants::ARUBA_FE_INVOICE_ERROR, false);

                } elseif ($dataToSend['additionalInfo']['createDraft']) {


                    if(!isset($json->drafts) || empty($json->drafts) ){

                        $wcOrder->add_order_note(esc_html__('A problem occurred while creating the draft, please check the e-Invoicing panel', 'aruba-fatturazione-elettronica'));

                    }else{

                        $wcOrder->add_order_note(esc_html__('The invoice was generated and inserted in the Drafts', 'aruba-fatturazione-elettronica'));

                    }


                }else{

                    $wcOrder->add_order_note(esc_html__('The order was generated and inserted in the Orders', 'aruba-fatturazione-elettronica'));

                }

            } else {

                $errors = $response->getJSON();

                $errorsLists = array();

                if (isset($errors->ErrorList)) {
                    foreach ($errors->ErrorList as $error) {
                        $errorsLists[] = sanitize_text_field($error->ErrorMessage);
                    }
                }

                if(empty($errorsLists)){
                    $errorsLists[] = esc_html__('Authentication failed', 'aruba-fatturazione-elettronica');
                }

                $wcOrder->add_order_note(
                    sprintf(
                    // translators: %s: fallback error from aruba.
                        esc_html__('An error occurred while sending to Aruba Electronic Invoicing [%s].', 'aruba-fatturazione-elettronica'),
                        implode(', ', $errorsLists)
                    )
                );

                if ($dataToSend['additionalInfo']['sendInvoice']) {
                    update_post_meta($order_id, ArubaFeConstants::ARUBA_FE_INVOICE_ERROR, true);
                }
            }

        } else {

            $wcOrder->add_order_note(
                sprintf(
                // translators: %s: fallback error from aruba.
                esc_html__('An error occurred while sending to Aruba Electronic Invoicing [%s].', 'aruba-fatturazione-elettronica'),
                    esc_html__('Authentication failed', 'aruba-fatturazione-elettronica')
                )
            );

            if ($isInvoiceRequest) {

                update_post_meta($order_id, ArubaFeConstants::ARUBA_FE_INVOICE_ERROR, true);

            }
        }
    }

    public function getConnectionStatus()
    {

        $token = $this->getToken();

        if ($token->getState()) {

            $response = $this->doGetCurl("api/ecommerce/{$this->clientId}/connection", array(), $token->getJSON()->access_token, null, false);

            if ($response->getState() && (int)$response->getJSON()->status == 1) {

                return true;

            } elseif ($response->getState()) {

                return $response->getJSON()->status();

            }
        }

        return false;
    }


    public function getConnectionData()
    {

        $token = $this->getToken();

        if ($token->getState()) {

            $response = $this->doGetCurl("api/ecommerce/{$this->clientId}/connection", array(), $token->getJSON()->access_token, null, false);

            return $response;

        }

        return false;
    }

    public function getOrdersStatus(array $orders_id)
    {

        $token = $this->getAccessToken();

        $associations = array();

        if ($token) {

            $options = '';

            foreach ($orders_id as $value) {

                $aru_fe_id = ArubaFeHelper::getArubaFeOrderIdByWcId($value);

                if (!$aru_fe_id) {
                    continue;
                }

                $associations[$aru_fe_id] = $value;

                $options .= '&id=' . CustomFilters::sanitize_alphanumeric($aru_fe_id);

            }

            if (!$options) {
                return false;
            }

            $options = ltrim($options, '&');

            $response = $this->doGetCurl("api/ecommerce/orders?$options", array(), $token);

            if ($response->getState()) {

                $json = $response->getJSON();

                foreach ($json as $order_data) {

                    $post_id = $associations[$order_data->order->id];

                    if (!$post_id) {
                        continue;
                    }

                    update_post_meta($post_id, '_aruba_fe_invoices', $this->sanitizeJson($order_data->invoices));
                    update_post_meta($post_id, '_aruba_fe_drafts', $this->sanitizeJson($order_data->drafts));
                    update_post_meta($post_id, '_aruba_fe_has_error', false);

                }
            } else {

                return false;

            }
        }

        return false;
    }

    protected function getAccessToken()
    {
        $token = $this->getToken();
        if ($token->getState()) {
            return $token->getJSON()->access_token;
        }

        return null;
    }

    /**
     *  Da rimuovere dopo verifiche
        * @param $order_id
        * @return ArubaFeApiResponseInterface|false
        * @depecated 0.1.4 no moro uses of this function
     */
    public function updateOrderState($order_id)
    {

        if (is_object($order_id)) {
            $order_id = $order_id->get_id();
        }

        $token = $this->getAccessToken();

        if (!$token) {
            return false;
        }

        $meta = get_post_meta($order_id, ArubaFeConstants::ARUBA_FE_ORDER_ID, true);

        if (!$meta) {

            $order = new ArubaFeOrderDataTransfer($order_id);

            $token = $this->getToken();

            if ($token->getState()) {

                $dataToSend = $order->build();

                if (!$dataToSend) {

                    return false;
                }

                $dataToSend['additionalInfo']['createDraft'] = false;
                $dataToSend['additionalInfo']['sendInvoice'] = false;

                $response = $this->doPostCurl(
                    'api/ecommerce/orders',
                    $dataToSend,
                    $token->getJSON()->access_token
                );

                if ($response->getState()) {

                    $json = $response->getJSON();

                    update_post_meta($order_id, '_aruba_fe_order_id', sanitize_text_field($json->order->id));
                    update_post_meta($order_id, '_aruba_fe_order_number', sanitize_text_field($json->order->number));
                    update_post_meta($order_id, '_aruba_fe_order_status', sanitize_text_field($json->order->status));
                    update_post_meta($order_id, '_aruba_fe_invoices', $this->sanitizeJson($json->invoices));
                    update_post_meta($order_id, '_aruba_fe_drafts', $this->sanitizeJson($json->drafts));
                    update_post_meta($order_id, '_aruba_fe_has_error', false);

                } else {

                    update_post_meta($order_id, '_aruba_fe_has_error', true);

                }
            } else {

                update_post_meta($order_id, '_aruba_fe_has_error', true);

            }

            return $response;

        } else {

            $response = $this->getOrdersStatus(array($order_id));

        }

        return $response;
    }

    public function getOrderDocuments(string $order_id): ArubaFeApiResponseInterface
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return false;
        }

        $order_id = CustomFilters::sanitize_alphanumeric($order_id);

        $response = $this->doPostCurl(
            "api/ecommerce/orders/$order_id/download?format=pdf",
            array(),
            $token
        );

        return $response;
    }

    public function getInvoiceDocuments($invoice_id)
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return false;
        }

        $invoice_id = CustomFilters::sanitize_alphanumeric($invoice_id);

        $response = $this->doPostCurl(
            "api/ecommerce/invoices/$invoice_id/download?format=pdf",
            array(),
            $token
        );

        return $response;
    }

    public function createDraftInvoice($order_id)
    {

        if (is_object($order_id)) {
            $order_id = $order_id->get_id();
        }

        $order_id = (int) $order_id;

        $orderFeId = get_post_meta($order_id, ArubaFeConstants::ARUBA_FE_ORDER_ID, true);

        $wcOrder = new \WC_Order($order_id);

        $token = $this->getAccessToken();
        // DEVE FUNZIONARE in 2 modi, se ho id invoco qui senno richiamo genera ordine
        if ($token) {
            /**se esiste un ordine già associato eseguo la chiamata diretta*/
            if ($orderFeId) {

                $response = $this->doPostCurl(
                    "api/ecommerce/orders/$orderFeId/draft",
                    array(),
                    $token
                );

                if ($response->getState()) {

                    $json = $response->getJSON();

                    $wcOrder->add_order_note(esc_html__('Draft invoice generated', 'aruba-fatturazione-elettronica'));

                    update_post_meta($order_id, '_aruba_fe_drafts', $this->sanitizeJson($json->drafts));
                    update_post_meta($order_id, '_aruba_fe_has_error', false);
                    update_post_meta($order_id, ArubaFeConstants::ARUBA_FE_DRAFT_INVOICE_ERROR, false);

                } else {

                    $wcOrder->add_order_note(
                    sprintf(
                    // translators: %s: fallback error from aruba.
                    esc_html__('An error occurred while creating the invoice in Draft [%s].', 'aruba-fatturazione-elettronica'),
                            esc_html__('Invalid order', 'aruba-fatturazione-elettronica')
                        )
                    );

                    update_post_meta($order_id, '_aruba_fe_has_error', true);
                    update_post_meta($order_id, ArubaFeConstants::ARUBA_FE_DRAFT_INVOICE_ERROR, true);
                }
            } else {

                $order = new ArubaFeOrderDataTransfer($order_id);

                $dataToSend = $order->build();

                if (!$dataToSend) {
                    return false;
                }

                $dataToSend['additionalInfo']['createDraft'] = true;
                $dataToSend['additionalInfo']['sendInvoice'] = false;

                $response = $this->doPostCurl(
                    'api/ecommerce/orders',
                    $dataToSend,
                    $token
                );

                if ($response->getState()) {

                    $json = $response->getJSON();

                    update_post_meta($order_id, '_aruba_fe_order_id', sanitize_text_field($json->order->id));
                    update_post_meta($order_id, '_aruba_fe_order_number', sanitize_text_field($json->order->number));
                    update_post_meta($order_id, '_aruba_fe_order_status', sanitize_text_field($json->order->status));
                    update_post_meta($order_id, '_aruba_fe_invoices', $this->sanitizeJson($json->invoices));
                    update_post_meta($order_id, '_aruba_fe_drafts', $this->sanitizeJson($json->drafts));
                    update_post_meta($order_id, '_aruba_fe_has_error', false);

                    $wcOrder->add_order_note(__('Draft invoice generated', 'aruba-fatturazione-elettronica'));
                    update_post_meta($order_id, ArubaFeConstants::ARUBA_FE_DRAFT_INVOICE_ERROR, false);

                    // update_post_meta($order_id, '_aruba_fe_invoices_data', $json);

                } else {

                    $errors = $response->getJSON();

                    $errorsLists = array();

                    if (isset($errors->ErrorList)) {
                        foreach ($errors->ErrorList as $error) {
                            $errorsLists[] = sanitize_text_field($error->ErrorMessage);
                        }
                    }

                    if(empty($errorsLists)){
                        $errorsLists[] = esc_html__('Authentication failed', 'aruba-fatturazione-elettronica');
                    }

                    $wcOrder->add_order_note(
                        sprintf(
                        // translators: %s: fallback error from aruba.
                        esc_html__('An error occurred while creating the invoice in Draft [%s].', 'aruba-fatturazione-elettronica'),
                            implode(', ', $errorsLists)
                        )
                    );

                    update_post_meta($order_id, '_aruba_fe_has_error', true);
                    update_post_meta($order_id, ArubaFeConstants::ARUBA_FE_DRAFT_INVOICE_ERROR, true);

                }
            }
        } else {

            $wcOrder->add_order_note(
                sprintf(
                // translators: %s: fallback error from aruba.
                esc_html__('An error occurred while creating the invoice in Draft [%s].', 'aruba-fatturazione-elettronica'),
                    esc_html__('Invalid Token', 'aruba-fatturazione-elettronica')
                )
            );

            update_post_meta($order_id, '_aruba_fe_has_error', true);
            update_post_meta($order_id, ArubaFeConstants::ARUBA_FE_DRAFT_INVOICE_ERROR, true);

        }
    }


    public function sendConfigs()
    {

        $token = $this->getAccessToken();

        if ($token) {

            $settings = (new ArubaFeDataTransferSettings())->build();

            $response =
                $this->doPatchCurl(
                    "api/ecommerce/{$this->clientId}/settings",
                    $settings,
                    $token
                );
            return $response->getState();

        }

        return false;
    }

    public function generateCreditNote($order)
    {

        if (!$order) {
            return false;
        }

        $order_id = is_a($order, 'WC_Order') ? (int) $order->get_id() : (int)$order;

        if (!$order_id) {
            return false;
        }

        $invoiceId = ArubaFeHelper::getOrderInvoice($order_id);

        /**
         * id fattura non trovato, aggiorno l'ordine per vedere se la fattura è stata generata
         */

        if (!$invoiceId) {
            return false;

        }

        if (!$invoiceId) {
            return false;
        }

        $token = $this->getAccessToken();

        if ($token) {

            $response = $this->doPostCurl("api/ecommerce/invoices/{$invoiceId}/credit-note", array(), $token);

            if ($response->getState()) {

                $order->add_order_note(esc_html__('Draft credit note generated', 'aruba-fatturazione-elettronica'));

            } else {

                $order->add_order_note(esc_html__('Error while creating credit note', 'aruba-fatturazione-elettronica'));

            }
        }
    }

    protected function sanitizeJson($json)
    {

        $allowedKeys = array(
            'id',
            'number',
            'status',
            'type',
        );

        $sanitized = array();

        foreach ($json as $row) {

            $sanitizedRow = new \stdClass();

            foreach ($row as $key => $value) {

                if (in_array($key, $allowedKeys)) {
                    $sanitizedRow->{$key} = sanitize_text_field($value);
                }
            }
            $sanitized[] = $sanitizedRow;
        }

        return $sanitized;
    }
}
