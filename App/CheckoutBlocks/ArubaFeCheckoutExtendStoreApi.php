<?php
namespace ArubaFe\CheckoutBlocks;

defined('ABSPATH') or die('Direct access is not allowed');

use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use Automattic\WooCommerce\StoreApi\StoreApi;
use WC_Customer;

class ArubaFeCheckoutExtendStoreApi
{

    protected $baseFields = [

        'billing_codice_fiscale_aruba_fe' => '',
        'billing_customer_type_aruba_fe' => '',
        'billing_partita_iva_aruba_fe' => '',
        'billing_sdi_aruba_fe' => '',
        'billing_pec_aruba_fe' => '',
        'billing_send_choice_invoice_aruba_fe' => '',
        'billing_need_invoice_aruba_fe' => '',
        'billing_option_aruba_fe_need_invoice' => false,

    ];

    public function extend_store_api()
    {

        $extend = StoreApi::container()->get(
            ExtendSchema::class
        );

        $extend->register_endpoint_data(
            array(
                'endpoint' => CheckoutSchema::IDENTIFIER,
                'namespace' => 'aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks',
                'schema_callback' => $this->get_schema_callback(),
                'data_callback' => [$this, 'get_script_data']
            )
        );

        add_action('woocommerce_store_api_checkout_update_order_from_request', array($this, 'aruba_fe_update_block_order_meta'), 10, 2);

    }

    private function get_schema_callback()
    {
        $options = new ArubaFeOptionParser();

        $this->baseFields['billing_option_aruba_fe_need_invoice'] = $options->askForInvoice();

        return function () {
            $schema = array();
            $field_names = [
                'billing_codice_fiscale_aruba_fe',
                'billing_customer_type_aruba_fe',
                'billing_partita_iva_aruba_fe',
                'billing_sdi_aruba_fe',
                'billing_pec_aruba_fe',
                'billing_send_choice_invoice_aruba_fe',
                'billing_need_invoice_aruba_fe',
            ];

            $validate_callback = function ($value) {
                if (!is_string($value) && null !== $value) {
                    return new WP_Error(
                        'api-error',
                        sprintf(
                        /* translators: %s is the property type */
                            esc_html__('Value of type %s was posted to the order attribution callback', 'aruba-fatturazione-elettronica'),
                            gettype($value)
                        )
                    );
                }

                return true;
            };

            $sanitize_callback = function ($value) {
                return sanitize_text_field($value);
            };

            foreach ($field_names as $field_name) {
                $schema[$field_name] = array(
                    'type' => array('string'),
                    'context' => array(),
                    'arg_options' => array(
                        'validate_callback' => $validate_callback,
                        'sanitize_callback' => $sanitize_callback,
                    ),
                );
            }

            return $schema;
        };
    }

    public function aruba_fe_update_block_order_meta($order, $request)
    {

        $requestBody = $request->get_json_params()['billing_address'];

        $extensions = $request->get_param('extensions');

        $data = $extensions['aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks'];

        $formatted_data = $this->parseRequest($data);

        $validator = new ArubaFeCheckoutBlocksValidation();

        $validator->validate($formatted_data, $requestBody);

        if($errors = $validator->getErrors()){

            throw new \Exception(implode("<br>",$errors));

        }else{

            $order->update_meta_data('_billing_customer_type_aruba_fe', $formatted_data['customer_type']);
            $order->update_meta_data('_billing_send_choice_invoice_aruba_fe', $formatted_data['send_invoice']);
            $order->update_meta_data('_billing_need_invoice_aruba_fe', $formatted_data['need_invoice']);
            $order->update_meta_data('_billing_codice_fiscale_aruba_fe', $formatted_data['dni']);
            $order->update_meta_data('_billing_partita_iva_aruba_fe', $formatted_data['pi']);
            $order->update_meta_data('_billing_sdi_aruba_fe', $formatted_data['sdi']);
            $order->update_meta_data('_billing_pec_aruba_fe', $formatted_data['pec']);

        }

    }

    protected function parseRequest($data)
    {

        return array(
            'customer_type' => isset($data['billing_customer_type_aruba_fe']) ? sanitize_text_field(wp_unslash($data['billing_customer_type_aruba_fe'])) : null,
            'send_invoice' => isset($data['billing_send_choice_invoice_aruba_fe']) ? sanitize_text_field(wp_unslash($data['billing_send_choice_invoice_aruba_fe'])) : null,
            'company' => isset($data['billing_company']) ? sanitize_text_field(wp_unslash($data['billing_company'])) : null,
            'need_invoice' => isset($data['billing_need_invoice_aruba_fe']) ? sanitize_text_field(wp_unslash($data['billing_need_invoice_aruba_fe'])) : null,
            'dni' => isset($data['billing_codice_fiscale_aruba_fe']) ? sanitize_text_field(wp_unslash($data['billing_codice_fiscale_aruba_fe'])) : null,
            'pi' => isset($data['billing_partita_iva_aruba_fe']) ? sanitize_text_field(wp_unslash($data['billing_partita_iva_aruba_fe'])) : null,
            'sdi' => isset($data['billing_sdi_aruba_fe']) ? sanitize_text_field(wp_unslash($data['billing_sdi_aruba_fe'])) : null,
            'pec' => isset($data['billing_pec_aruba_fe']) ? sanitize_text_field(wp_unslash($data['billing_pec_aruba_fe'])) : null,
        );

    }

    /**
     * @throws \Exception
     */
    public function get_script_data()
    {
        $current_user = get_current_user_id();

        if ($current_user) {

            $customer = new WC_Customer($current_user);
            $current_user_meta = $customer->get_meta_data();

            foreach ($current_user_meta as $metaData) {


                $data = $metaData->get_data();

                $key = $data['key'];
                $value = $data['value'];

                if (array_key_exists($key, $this->baseFields)) {
                    $this->baseFields[$key] = $value;
                }

            }

        }

        return $this->baseFields;

    }


}