<?php

namespace ArubaFe\Admin\Label;
if (!defined('ABSPATH')) die('No direct access allowed');

class ArubaFeLabel
{
    protected static $cache;
    protected $strings = [];

    protected function __construct()
    {
        $this->setUp();

    }

    public static function getInstance()
    {
        if (is_null(self::$cache))
            return self::$cache = new self();

        return self::$cache;
    }

    protected function setLabel($label, $value)
    {

        $this->strings[$label] = $value;

    }

    public function getLabel($label, $params = [])
    {

        return array_key_exists($label, $this->strings) ? sprintf($this->strings[$label],...$params) : '' ;

    }

    /**
     * @return void
     * Label for react dashboard
     */
    protected function setUp()
    {

        $this->setLabel('_wc_settings_link',esc_url(admin_url('admin.php?page=wc-settings')));
        $this->setLabel('aruba_fe_loading',esc_html__('LOADING', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_connction_success',esc_html__('The Aruba Electronic Invoicing plugin has been correctly connected to the WooCommerce module of the e-Billing service. Manage the plugin configuration to indicate the details of operation.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_disconnect_success', esc_html__('The plugin has been disconnected', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_config_saved', esc_html__('Settings saved successfully', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_text_create_always', esc_html__('You can select to always create an invoice for individuals or you can let the customer choose whether or not to receive the invoice. You will be prompted to enter the mandatory tax code when creating the invoice.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_text_desc', esc_html__('Select the type of description you want to appear on the invoice for each purchased item. The data will be retrieved from the WooCommerce plugin settings.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_shop_name_tt', esc_html__('The shop name is not editable and will be used in the eInvoice Management Panel to identify the shop from which the order originated.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_extra_title', esc_html__('Configure exemptions', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_save', esc_html__('Save configuration', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_manual_create_fe', esc_html__('For electronic invoices, the VAT number [PIVA] present within Aruba`s electronic invoicing service will be used', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_automatic_create_fe', esc_html__('E-invoices will be issued from the VAT [VAT ID] in the e-invoicing service.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_section_1', esc_html__('Aruba Electronic Invoicing Configuration', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_config_action', esc_html__('Configure', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_taxes_disabled', esc_html__('To continue, you must enable tax rates and tax calculations in the WooCommerce Settings page', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_checkTaxes', esc_html__('Checking Tax Rates and Taxes Enablement...', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_disable_title', esc_html__('You are disabling Aruba Electronic Invoicing', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_mantain', esc_html__('You`re keeping Aruba taxes', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_nomantain', esc_html__('Restore initial taxes', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_disable_messagge', esc_html__("You can choose whether to keep the tax configuration you had on the plugin or restore the WooCommerce ones that existed before the plugin was installed.", 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_disconnect_title', esc_html__('Disconnect the Aruba Electronic Invoicing plugin?', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_disconnect_message', esc_html__('By unplugging the plugin from the WooCommerce module, orders placed on the online shop will no longer be imported into the invoicing service and you won`t be able to automatically generate invoices for purchases. You will only be able to view existing orders and invoices.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_disconnect_message_conf', esc_html__('Do you want to disconnect the plugin?', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_abort', esc_html__('Cancel', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_unexpected_error', esc_html__('Communication error, contact support if not resolved', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_auth_fail', esc_html__('Authentication failed', 'aruba-fatturazione-elettronica'));
        // translators: %s: error description.
        $this->setLabel('aruba_fe_connection_error', esc_html__('error %s', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_error_on_connection', esc_html__('Connection failed: ', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_shop_name', esc_html__('Shop Name', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_customer_code', esc_html__('Customer Code', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_secret_code', esc_html__('Secret Code', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_connected', esc_html__('Connected', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_connected', esc_html__('Connected', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_connect',esc_html__('Connecting', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_disconnect_btn', esc_html__('Disconnect', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_title', esc_html__('Aruba Electronic Invoicing', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_intro', esc_html__('To link the plugin with the WooCommerce e-Invoicing module, open the [b]Settings[/b] of the billing service, select the [b]E-commerce[/b] item and copy the client code and secret code for [b]WooCommerce[/b], then enter them here and complete the configuration.','aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_more_info', esc_html__('For more details, see the ','aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_online_guide', esc_html__('online guide.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_online_guide_link', esc_url('https://guide.hosting.aruba.it/hosting/hosting-woocommerce-gestito/impostazioni-plugin-aruba-fatturazione-elettronica.aspx'));
        $this->setLabel('aruba_fe_block_create_invoice_title', esc_html__('Create Invoice', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_automatic_create_fe_lb', esc_html__('Automatic (recommended)', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_automatic_send_fe', esc_html__('Automatic (recommended)', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_automatic_create_fe_tt', esc_html__('Tooltip - Automatic (recommended)', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_manual_create_fe_tt', esc_html__('Tooltip - Manual', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_manual_create_fe_lb', esc_html__('Manual', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_manual_send_fe', esc_html__('Manual', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_block_send_invoice_title', esc_html__('Send Invoices', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_create_invoice_content', esc_html__('If you select Automatic, an electronic invoice will be created each time an order reaches the chosen status for issuance (e.g. completed).', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_create_invoice_content_1', esc_html__('By selecting Manual you will be the one to create the invoice for the sale.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_send_invoice_content', esc_html__('If you select Automatic, the invoice will automatically be sent to the Interchange System (SDI) whenever an order reaches the status chosen for sending (e.g. completed).', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_send_invoice_content_1', esc_html__('By selecting Manual you will be the one to send the invoice to SDI.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_order_state_tt_1', esc_html__('Payment has been received and the order has been processed', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_order_state_tt_2', esc_html__('Payment has been received and the order is being prepared', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_order_state_tt_3', esc_html__('Awaiting receipt of payment by bank transfer', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_state_order_complete', esc_html__('Completed', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_state_order_processing', esc_html__('In process', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_state_order_pending', esc_html__('Pending', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_block_state_create_invoice_title', esc_html__('Order State for automatic invoice creation', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_block_state_create_invoice_title_auto',esc_html__('Order state for automatic invoice creation and sending','aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_block_state_create_invoice_title_manual',esc_html__('Order state for creation','aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_block_state_create_invoice_content',esc_html__('Select order state to create invoice automatically','aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_block_state_create_invoice_content_auto', esc_html__('Select the order state at which to create and send the invoice automatically', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_block_state_create_invoice_content_manual', esc_html__('Select the order state at which to create order automatically', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_automatic_receipts_fe', esc_html__('Automatically mark invoices paid by credit card as collected', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_manual_receipts_fe', esc_html__('Do not mark any invoices as collected automatically', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_block_paind_invoice_title', esc_html__('Mark receipts for paid invoices', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_block_paind_invoice_text', esc_html__('Select whether you want to consider paid orders processed by credit card.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_product_name_fe', esc_html__('Product/Service Name', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_descriction_product_name_fe', esc_html__('Product/Service Name + Description', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_short_descriction_product_name_fe', esc_html__('Product/Service Name + Short Description', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_block_desc_title', esc_html__('Description to insert in invoice', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_create_always_fe', esc_html__('Always create an invoice for natural persons', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_allow_choose_create_fe', esc_html__('Allow natural person customer to choose whether or not to receive invoice', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_individual_create_invoice', esc_html__('Create invoices for individuals', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_automatically_update_data_customer', esc_html__('Automatically create new master records and update existing ones in case of new users or mismatched data.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_not_automatically_update_data_customer', esc_html__('Never create new master records and never update existing ones in case of new users or mismatched data', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_update_data_customer', esc_html__('Create and update customer master data', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_update_data_customer_desc', esc_html__('The system will perform a comparison between the customer master data linked to WooCommerce orders and the ones in the Electronic Invoicing service. Choose the action you want it to perform.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_automatic_send_coutesy_copy', esc_html__('Always send a pdf copy of the invoice to the customer`s email address (Recommended)', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_not_send_coutesy_copy', esc_html__('Do not send the courtesy copy to the customer', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_send_coutesy_copy', esc_html__('Automatically send courtesy copy of invoice', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_payments_loading', esc_html__('Uploading payments', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_wc_payments', esc_html__('Methods available on WooCommerce', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_payments_fe', esc_html__('Methods available on Fatturazione Elettronica', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_payments_text_link', esc_url('https://guide.hosting.aruba.it/hosting/hosting-woocommerce-gestito/impostazioni-plugin-aruba-fatturazione-elettronica.aspx#pagamenti'));
        $this->setLabel('aruba_fe_payments_tt', esc_html__('Set a match between the payment methods available on WooCommerce and those set on the eInvoicing service.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_set_invoice_title', esc_html__('Tax Configuration', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_set_invoice_text_1', esc_html__('You can define a single tax rate for the Italian, European or non-European area, or configure a rate for each country.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_set_invoice_text_2', esc_html__('Taxes will automatically be associated with products/services based on the nationality of the customer placing the order.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_set_invoice_text_3', esc_html__('This tax rate configuration will replace the one in the Tax section of the WooCommerce plugin.', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_set_invoice_text_4', esc_html__('Consult the guides for more details on applying taxes', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_set_invoice_text_4_link', esc_url('https://guide.hosting.aruba.it/hosting/hosting-woocommerce-gestito/impostazioni-plugin-aruba-fatturazione-elettronica.aspx#imposte'));
        $this->setLabel('aruba_fe_select', esc_html__('Select', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_has_some_errors', esc_html__('Some settings are not configured correctly', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_payments', esc_html__('Payments', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_payments_text', esc_html__('Refer to the payment method configuration guidelines', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_italia', esc_html__('Italy', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_ue', esc_html__('European Community Countries', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_extraue', esc_html__('Non-EU Countries', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_tax_rate', esc_html__('Tax rates', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_delete_tax_rate', esc_html__('Delete this tax rate', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_delete_tax_title', esc_html__('Do you really want to delete this tax rate?', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_delete_tax_message', esc_html__('By confirming, all tax rates set for this class will be deleted, please don`t need to save any more', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_confirm_tax_delete', esc_html__('I confirm that I want to delete the tax rate', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_rate_config_desc', esc_html__('Configure a tax rate by region or country', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_rate_config_desc_2', esc_html__('Consult the guides for more details on configuring taxes', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_add_tax_rate', esc_html__('Create new tax rate', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_add_tax_rate_save', esc_html__('Save', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_add_tax_rate_title', esc_html__('Confirm rate entry?', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_confirm_tax_add', esc_html__('Confirm', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_simple_version', esc_html__('Choose by area', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_complex_version', esc_html__('Choose by country', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_country_code', esc_html__('Country Code', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_country_vat_code', esc_html__('VAT Code', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_aliquota', esc_html__('Tax rates', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_add_tax_title', esc_html__('Do you confirm the tax rate entry?', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_country_code_tt', esc_html__('In case of a single country setting, enter the 2-digit code, e.g. IT', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_country_vat_code_tt', esc_html__('Percentage value based on which to calculate the tax', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_nex_tax_palceholder', esc_html__('Only letters and numbers are allowed', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_total', esc_html__('Elements:', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_apply', esc_html__('Apply', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_noapply', esc_html__('Do not apply', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_total', esc_html__('Elements:', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_exemption', esc_html__('Foreign Customer Exemption', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_exemption_text', esc_html__('You can set a VAT rate of 0 for the specified customer categories by selecting one of the available ones', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_subject', esc_html__('Subject', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_person', esc_html__('Person', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_company', esc_html__('Company', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_generic_error', esc_html__('An unexpected error has occurred', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_close', esc_html__('Close', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_connection_label', esc_html__('Plugin Connection', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_generic_label', esc_html__('General Settings', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_payments_label', esc_html__('Payments', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_texes_label', esc_html__('Taxes', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_customer_type', esc_html__('Customer Type', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_tax_rate', esc_html__('Tax Rate', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_tipo_cliente_1', esc_html__('EU Individuals', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_tipo_cliente_2', esc_html__('Non-EU natural persons', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_tipo_cliente_3', esc_html__('Companies/EU VAT Payers', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_tipo_cliente_4', esc_html__('Companies/VAT_entities non-EU', 'aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_of',esc_html__('of','aruba-fatturazione-elettronica'));

        $this->setLabel('aruba_fe_view_totals_title',esc_html__('Display of product amounts in the order/invoice','aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_view_totals_opt_1',esc_html__('Shows the amount of the product divided between the basic price and the cost of surcharges (e.g. for additional machining, customisation, etc.).','aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_view_totals_opt_2',esc_html__('Always show the full amount of the product in one price field (product price and any mark-ups)','aruba-fatturazione-elettronica'));

        $this->setLabel('aruba_fe_banks_title', esc_html__('Bank to be associated if payment by bank transfer (Optional)','aruba-fatturazione-elettronica'));
        $this->setLabel('aruba_fe_banks_info', esc_html__('If you select a bank this will be used for both invoices and orders and the bank entered in the woocommerce bank transfer settings will be overwritten.','aruba-fatturazione-elettronica'));

    }

    public function getAll()
    {
        return $this->strings;
    }

}