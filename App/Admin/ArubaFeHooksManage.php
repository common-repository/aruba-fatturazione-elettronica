<?php

namespace ArubaFe\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'No direct access allowed' );
}

use ArubaFe\Admin\AppState\AppStateManager;
use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\Cron\ArubaFeCronExec;
use ArubaFe\Admin\Documents\ArubaFeDocument;
use ArubaFe\Admin\Email\ArubaFeCurtesycopyEmail;
use ArubaFe\Admin\Email\ArubaFeInvalidVersionEmail;
use ArubaFe\Admin\Filters\CustomFilters;
use ArubaFe\Admin\Label\ArubaFeStatusesLabel;
use ArubaFe\Admin\Notice\ArubaFeNotices;
use ArubaFe\Admin\RestApi\ArubaFeApiRestManager;
use ArubaFe\Admin\RestApi\DataTransfer\ArubaFeOrderDataTransfer;
use ArubaFe\Admin\RestApi\Helper\ArubaFeHelper;
use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;
use ArubaFe\Admin\Tax\ArubaFeTaxLogic;
use ArubaFe\Admin\Traits\ArubaFeLogTrait;
use WC_Order_Item_Product;

class ArubaFeHooksManage {

    protected $options;
    protected $notice;

    use ArubaFeLogTrait;

    public function __construct() {
        $this->options   = new ArubaFeOptionParser();
        $this->notice    = ArubaFeNotices::getInstance();
        $this->scheduler = new ArubaFeCronExec();
        $this->registerHooks();
    }


    private function registerHooks() {

        /**
         * Woocommerce
         */

        add_filter( 'woocommerce_before_calculate_totals', array( $this, 'check_if_customer_tax_exempt' ), 10, 1 );
        add_action( 'woocommerce_order_status_changed', array( $this, 'fe_on_order_status_changed' ), 10, 3 );

        add_filter( 'manage_edit-shop_order_columns', array( $this, 'aruba_fe_invoice_state_column' ), 9999 );
        /*8.2*/
        add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'aruba_fe_invoice_state_column' ), 9999 );


        add_action( 'manage_shop_order_posts_custom_column', array( $this, 'aruba_fe_invoice_state_column_data' ), 10, 2 );
        /*8.2*/
        add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'aruba_fe_invoice_state_column_data' ), 10, 2 );

        add_filter( 'woocommerce_email_classes', array( $this, 'aruba_fe_register_emails' ), 9999, 1 );
        add_filter( 'woocommerce_order_actions', array( $this, 'aruba_fe_add_custom_order_action' ), 999, 1 );
//        add_action( 'woocommerce_order_action_aruba_fe_send_order', array( ArubaFeApiRestManager::getInstance(), 'updateOrderState' ), 999, 1 );
        add_action( 'woocommerce_order_action_aruba_fe_create_draft', array( ArubaFeApiRestManager::getInstance(), 'createDraftInvoice' ), 999, 1 );
        add_action( 'woocommerce_order_action_aruba_fe_send_curtesy_copy', array( $this,'aruba_fe_send_curtesy_copy' ), 999, 2 );
        add_action( 'woocommerce_order_action_aruba_fe_send_credit_note', array( ArubaFeApiRestManager::getInstance(), 'generateCreditNote' ), 999, 1 );
        add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'ArubaFeAddLinks' ), 20, 1 );
        add_filter( 'the_posts', array( $this, 'aruba_fe_orders_list' ), 10, 2 );

        /**
         * Aruba Fatturazione elettronica
         */

        add_filter( 'plugin_action_links_' . ARUBA_FE_BN, array( $this, 'aruba_fe_action_links' ), 10, 1 );
        add_action( 'admin_notices', array( $this, 'aruba_fe_admin_manage_notices' ) );
        add_action( 'admin_init', array( $this, 'add_aruba_fe_endpoint' ), 10, 2 );
        add_action( 'admin_init', array( $this, 'add_aruba_fe_admin_only_hooks' ), 10, 2 );

        add_action( 'wp_ajax_dismiss_notice', array( $this, 'dismiss_notice' ) );

        add_action( 'init', array( $this, 'registerCron' ) );
        add_action( 'init', array( $this, 'checkWarnings' ) );
    }

    public function checkWarnings() {

        $current_warning = CustomOptions::get_option( ArubaFeConstants::ARUBA_FE_IN_ERROR, null );

        if ( ! $current_warning ) {
            return;
        }

        if ( $current_warning['sended'] ) {
            return;
        }

        if ( ArubaFeInvalidVersionEmail::sendWarning() ) {

            $current_warning['sended'] = 1;

            if ( ! $current_warning ) {

                CustomOptions::add_option( ArubaFeConstants::ARUBA_FE_IN_ERROR, $current_warning );

            } else {

                CustomOptions::update_option( ArubaFeConstants::ARUBA_FE_IN_ERROR, $current_warning );

            }
        }
    }

    public function registerCron() {

        if ( ! has_action( ArubaFeConstants::ARUBA_FE_CRON_HOOK ) ) {

            $time = time();

            add_filter( 'cron_schedules', array( $this->scheduler, 'every_five_minute' ) );

            if ( ! wp_next_scheduled( ArubaFeConstants::ARUBA_FE_CRON_HOOK ) ) {
                wp_schedule_event( $time, 'aruba_fe_five_minutes', ArubaFeConstants::ARUBA_FE_CRON_HOOK );
            }

            add_action( ArubaFeConstants::ARUBA_FE_CRON_HOOK, array( $this->scheduler, 'executeFeCronMail' ) );

        }

        if ( ! has_action( ArubaFeConstants::ARUBA_FE_CRON_HOOK_ORDERS ) ) {

            $time = time();

            add_filter( 'cron_schedules', array( $this->scheduler, 'every_eleven_minutes' ) );

            if ( ! wp_next_scheduled( ArubaFeConstants::ARUBA_FE_CRON_HOOK_ORDERS ) ) {
                wp_schedule_event( $time, 'aruba_fe_eleven_minutes', ArubaFeConstants::ARUBA_FE_CRON_HOOK_ORDERS );
            }

            add_action( ArubaFeConstants::ARUBA_FE_CRON_HOOK_ORDERS, array( $this->scheduler, 'executeFeCronOrders' ) );

        }
    }


    public function aruba_fe_woocommerce_new_order( $order_id ) {

        $order = wc_get_order( $order_id );

        if ( ! $order || is_wp_error( $order ) ) {
            return;
        }

        $this->aruba_fe_update_order_meta( $order_id );
    }

    public function dismiss_notice() {

        if(!isset($_POST['_aruba_fe_dismiss_notice']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_aruba_fe_dismiss_notice'])),'_aruba_fe_dismiss_notice') )
            exit( 0 );

        if ( $notice_ID = ( isset( $_POST['notice_id'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_id'] ) ) : 0 ) ) {
            $this->notice->hideNotice( $notice_ID );
        }

        exit( 1 );
    }

    public function ArubaFeAddLinks( $order ) {

        ArubaFeApiRestManager::getInstance()->getOrdersStatus( array( $order->get_id() ) );

        $ignore = get_post_meta( $order->get_id(), ArubaFeConstants::ARUBA_FE_ORDER_INELEGGIBLE, true );

        if ( ! $ignore ) {

            $orderId        = get_post_meta( $order->get_id(), ArubaFeConstants::ARUBA_FE_ORDER_ID, true );
            $invoices       = get_post_meta( $order->get_id(), ArubaFeConstants::ARUBA_FE_INVOICE_DATA, true );
            $drafts         = get_post_meta( $order->get_id(), ArubaFeConstants::ARUBA_FE_DRAFTS_DATA, true );
            $hasError       = get_post_meta( $order->get_id(), ArubaFeConstants::ARUBA_FE_INVOICE_ERROR, true );
            $hasErrorDraft  = get_post_meta( $order->get_id(), ArubaFeConstants::ARUBA_FE_DRAFT_INVOICE_ERROR, true );
            $errorList      = get_post_meta( $order->get_id(), ArubaFeConstants::ARUBA_FE_ERRORS_LIST,true);

            $regenerateLink = $hasError ? wp_nonce_url(
                admin_url(
                    'admin.php?fe_action=resendInvoice&type=order&order_id=' . (int) $order->get_id() . '&type=retry'
                ),
                'aruba_fe_resend_invoice'
            ) : null;

            $regenerateLinkDraft = $hasErrorDraft ? wp_nonce_url(
                admin_url(
                    'admin.php?fe_action=resendDraftInvoice&type=order&order_id=' . (int) $order->get_id() . '&type=retry'
                ),
                'aruba_fe_resend_invoice'
            ) : null;

        }
        include_once ARUBA_FE_PATH . 'templates/admin/tpl_order_links.php';
    }

    public function insert_handlebars_template() {
        include_once ARUBA_FE_PATH . 'templates/admin/tpl_disable_plugin.php';
    }

    public function aruba_fe_action_links( $actions ) {
        $action_links = array(
            '<a href="' . esc_url( ARUBA_FE_SETTING_PAGE_URL ) . '">' . esc_html__( 'Setting', 'aruba-fatturazione-elettronica' ) . '</a>',
        );

        return array_merge( $actions, $action_links );
    }

    public function check_if_customer_tax_exempt( $cart ) {
        $exemption = ( new ArubaFeTaxLogic() )->mayOverrideTaxLogicOnCheckout();

        if ( $exemption ) {
            WC()->cart->get_customer()->set_is_vat_exempt( true );
        } else {
            WC()->cart->get_customer()->set_is_vat_exempt( false );
        }
    }

    public function custom_html_after_settings_tax() {

        $query = isset($_SERVER['QUERY_STRING']) ? esc_url_raw(wp_unslash($_SERVER['QUERY_STRING'])) : null;

        if(!$query){
            return;
        }

        parse_str($query,$vars);;

        if(isset($vars['tab']) && isset($vars['section']) &!empty($vars['section']) ){

            include_once ARUBA_FE_PATH . 'templates/admin/tpl_disabled_taxes.php';

        }

    }


    public function aruba_fe_add_custom_order_action( $actions ) {
        $post_id = get_the_ID();

        $order = wc_get_order( $post_id );

        if ( $order && ! is_wp_error( $order ) ) {

            $ignore = $order->get_meta( ArubaFeConstants::ARUBA_FE_ORDER_INELEGGIBLE );

            if ( $ignore ) {
                return $actions;
            }

            ArubaFeApiRestManager::getInstance()->getOrdersStatus( array( $post_id ) );

            $cantProcess        = false;
            $hasOrder           = false;
            $hasDraft           = false;
            $hasInvoice         = false;
            $hasDraftCreditNote = false;
            $hasCreditNote      = false;

            $tmpl_vars = array();

            $orderFeId = get_post_meta( $post_id, ArubaFeConstants::ARUBA_FE_ORDER_ID, true );

            if ( $orderFeId ) {

                $hasOrder                         = true;
                $tmpl_vars['aruba_fe_order_link'] = ArubaFeHelper::getOrderLink( $orderFeId );
                $actions['aruba_fe_view_order']   = esc_html__( 'View order', 'aruba-fatturazione-elettronica' );
            }

            $invoices = get_post_meta( $post_id, ArubaFeConstants::ARUBA_FE_INVOICE_DATA, true );
            $drafts   = get_post_meta( $post_id, ArubaFeConstants::ARUBA_FE_DRAFTS_DATA, true );

            if ( ! empty( $drafts ) ) {

                foreach ( $drafts as $draft ) {

                    if ( strtolower( $draft->type ) == 'ndc' ) {
                        $hasDraftCreditNote = true;
                    } elseif ( strtolower( $draft->type ) == 'ftx' ) {
                        $hasDraft = true;
                    }
                }
            }

            if ( ! empty( $invoices ) ) {

                foreach ( $invoices as $invoice ) {

                    if ( strtolower( $invoice->type ) == 'ndc' ) {
                        $hasCreditNote = true;
                    } elseif ( strtolower( $invoice->type ) == 'ftx' ) {
                        $hasInvoice = true;
                    }
                }
            }

            /**
             * if draft and invoice doesn't exits and show actiton
             */

            /**
             * only if create invoice is set to manual
             */
            $canCreateDraft = $this->options->createInvoice();

            if ( ! $hasDraft && ! $hasInvoice && ! $canCreateDraft && $orderFeId) {
                $actions['aruba_fe_create_draft'] = esc_html__( 'Generate draft invoice', 'aruba-fatturazione-elettronica' );
            }

            if ( ! empty( $invoices ) ) {

                foreach ( $invoices as $invoice ) {

                    if ( strtolower( $invoice->type ) == 'ndc' ) {

                        $actions['aruba_fe_view_invoice_credit_note'] = esc_html__( 'View credit note', 'aruba-fatturazione-elettronica' );

                        $tmpl_vars['aruba_fe_invoice_ndc_link'] = ArubaFeHelper::getInvoiceNDCLink( $invoice->id );

                    } elseif ( strtolower( $invoice->type ) == 'ftx' ) {

                        $actions['aruba_fe_view_invoice'] = esc_html__( 'View Invoice', 'aruba-fatturazione-elettronica' );

                        $tmpl_vars['aruba_fe_invoice_link'] = ArubaFeHelper::getInvoiceLink( $invoice->id );

                        $actions['aruba_fe_send_curtesy_copy'] = esc_html__( 'Send copy invoice by email', 'aruba-fatturazione-elettronica' );

                        $actions['aruba_fe_download_invoice'] = esc_html__( 'Download PDF invoice copy', 'aruba-fatturazione-elettronica' );

                        $tmpl_vars['aruba_fe_invoice_pdf_link'] = ArubaFeHelper::getInvoicePdf( $invoice->id, $post_id );

                    }
                }
            }

            if ( ! $hasDraftCreditNote && ! $hasCreditNote && $hasInvoice ) {
                $actions['aruba_fe_send_credit_note'] = esc_html__( 'Generate draft credit note', 'aruba-fatturazione-elettronica' );
            }

            wp_enqueue_script( 'aruba-fe-order-actions', ARUBA_FE_URL . 'assets/js/aruba-fe-order-actions.js',array(),'1.1',['in_footer'=> false] );
            wp_localize_script( 'aruba-fe-order-actions', 'aruba_fe_links', $tmpl_vars );

        }

        return $actions;
    }

    public function aruba_fe_admin_manage_notices() {

        new AppStateManager();

        $this->notice->renderNotices();
    }

    public function aruba_fe_register_emails( $email_classes ) {

        $email_classes['ArubaFeCurtesycopyEmail'] = new ArubaFeCurtesycopyEmail();

        return $email_classes;
    }

    public function aruba_fe_orders_list( $posts, $query ) {

        global $pagenow,$current_screen;

        if ( !isset($current_screen->id) || $pagenow != 'edit.php' || empty( $posts )  || $current_screen->id != 'edit-shop_order' ) {
            return $posts;
        }
        
        $object_ids = array();

        foreach ( $posts as $post ) {
            $object_ids[] = $post->ID;
        }

        if ( $object_ids ) {
            ArubaFeApiRestManager::getInstance()->getOrdersStatus( $object_ids );
        }

        return $posts;
    }


    /**
     * Salvo i dati che mi interessano nelle options | Funziona anche con le update
     *
     * @param $order_id
     * @return void
     */

    public function aruba_fe_update_order_meta( $order_id ) {

        $order = wc_get_order( $order_id );

        if ( ! $order || is_wp_error( $order ) ) {
            return;
        }

        $is_vat_exempt = $order->get_meta( 'is_vat_exempt' ) === 'yes';

        $taxOption = get_option( 'woocommerce_shipping_tax_class', null );

        $maxTaxIdForShipping = new class() {
            public $shippingId;
            public $shippingTaxRate;
        };

        /**
         * saving additional order items data needed for Aruba fatturazione elettronica integration
         */

        foreach ( $order->get_items() as $item_id => $product ) {

            $productObject = $product->get_product();

            $regularPrice = (float) $productObject->get_regular_price();

            $order_item_product = new WC_Order_Item_Product( $product->get_id() );

            /** versione con sconto coupon scorporato
             * $quantity = $product->get_quantity();
             *
             * $totalRealPrice = $regularPrice * $quantity;
             *
             * $appliedPrice = $productObject->get_price();
             *
             * $total  = $appliedPrice * $quantity;
             *
             * $discount = number_format((($totalRealPrice - $total) / $quantity), 2, '.', '');
             */

            $quantity = $product->get_quantity();

            $totalRealPrice = $regularPrice * $quantity;

            $total = $product->get_total();

            $variation = number_format( ( ( $totalRealPrice - $total ) / $quantity ), 2, '.', '' );
            // SALVARE LE ALIQUOTE VALORE + ID ARUBa per ogni prodotto

            /**
             *
             * if variation is gt 0 is a discount otherwise is an increment
             *
             */

            wc_update_order_item_meta( $item_id, ArubaFeConstants::ARUBA_FE_ORDER_ITEM_DISCOUNT, $variation );

            if ( '0' !== $product->get_tax_class() && 'taxable' === $product->get_tax_status() && wc_tax_enabled() ) {

                if ( $is_vat_exempt ) {

                    $tax_data = ( new ArubaFeTaxLogic() )->getTaxRateExemption( $order->get_billing_country(), $order->get_meta( '_billing_customer_type_aruba_fe' ) );

                } else {

                    $tax_data = ArubaFeWcUtils::getTaxData( $order_item_product );

                }

                if ( isset( $tax_data->tax_rate ) ) {
                    wc_update_order_item_meta( $item_id, ArubaFeConstants::ARUBA_FE_ORDER_ITEM_TAX_RATE, sanitize_text_field( $tax_data->tax_rate ) );
                }
                if ( isset( $tax_data->id_aruba ) ) {
                    wc_update_order_item_meta( $item_id, ArubaFeConstants::ARUBA_FE_ORDER_ITEM_TAX_RATE_ID, sanitize_text_field( $tax_data->id_aruba ) );
                }

                if ( isset( $tax_data->tax_rate ) && isset( $tax_data->id_aruba ) ) {

                    if ( $tax_data->tax_rate > $maxTaxIdForShipping->shippingTaxRate ) {
                        $maxTaxIdForShipping->shippingId      = $tax_data->id_aruba;
                        $maxTaxIdForShipping->shippingTaxRate = $tax_data->tax_rate;
                    }
                }
            }
        }

        // REGISTER INFO FOR SHIPPIG

        if ( $shippingMethods = $order->get_shipping_methods() ) {

            foreach ( $shippingMethods as $shippingMethod ) {

                $row_id = $shippingMethod->get_id();

                $methodId    = $shippingMethod->get_method_id();
                $instance_id = $shippingMethod->get_instance_id();

                $settings = "woocommerce_{$methodId}_{$instance_id}_settings";

                $shippingSettings = get_option( $settings, false );

                if ( $shippingSettings && $shippingSettings['tax_status'] === 'none' ) {

                    $tax_rate = 0;
                    $id_aruba = ArubaFeWcUtils::getDefaultZeroRate();

                } elseif ( $taxOption == 'inherit' ) {

                    $tax_rate = $maxTaxIdForShipping->shippingTaxRate;
                    $id_aruba = $maxTaxIdForShipping->shippingId;

                } else {

                    /**
                     * now $taxOption is the name of the tac class, empty is standard
                     */

                    $row_data = ArubaFeWcUtils::getTaxDataFromClass( $taxOption, $order );
                    $tax_rate = $row_data->tax_rate;
                    $id_aruba = $row_data->id_aruba;
                }

                wc_update_order_item_meta( $row_id, ArubaFeConstants::ARUBA_FE_ORDER_ITEM_TAX_RATE, sanitize_text_field( $tax_rate ) );
                wc_update_order_item_meta( $row_id, ArubaFeConstants::ARUBA_FE_ORDER_ITEM_TAX_RATE_ID, sanitize_text_field( $id_aruba ) );

            }
        }

        /**
         * check if user doesn't want invoice and mark the order
         */

        if ( $this->options->askForInvoice() === false ) {

            $meta = get_post_meta( $order_id, '_billing_customer_type_aruba_fe', true );

            $billing_need_invoice_aruba_fe = (int) get_post_meta( $order_id, '_billing_need_invoice_aruba_fe', true );

            if ( $meta === 'person' && $billing_need_invoice_aruba_fe === 0 ) {
                update_post_meta( $order_id, ArubaFeConstants::ARUBA_FE_ORDER_INELEGGIBLE, 1 );
            }
        }

        update_post_meta( $order_id, ArubaFeConstants::ARUBA_FE_ORDER_REGISTRED, 1 );
    }

    public function add_aruba_fe_admin_only_hooks(){
        global $pagenow;

        add_action( 'woocommerce_after_order_object_save',  array($this,'checkForNewActions'), 10, 2);
        add_action( 'woocommerce_after_settings_tax', array( $this, 'custom_html_after_settings_tax' ) );
        add_filter( 'woocommerce_after_order_object_save', array( $this, 'aruba_fe_woocommerce_new_order' ), 999, 1 );

        if ( $pagenow === 'plugins.php' ) {

            add_action( 'admin_footer', array( $this, 'insert_handlebars_template' ) ); // Puoi usare 'wp_head' al posto di 'wp_footer' a seconda della posizione desiderata

        }
    }

    public function add_aruba_fe_endpoint() {

        if ( ! isset( $_GET['fe_action'] ) || ! isset( $_GET['type'] ) || ! isset( $_GET['order_id'] ) ) {

            return;
        }

        if ( $_GET['fe_action'] === 'resendInvoice' ) {

            $order_id = (int) $_GET['order_id'];

            $returnLink = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

            if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'aruba_fe_resend_invoice' ) ) {

                $hasError = get_post_meta( $order_id, ArubaFeConstants::ARUBA_FE_INVOICE_ERROR, true );

                if ( ! $hasError ) {

                    $this->notice->addNotice( esc_html__( 'The elettronic invoice was already sent', 'aruba-fatturazione-elettronica' ) );

                } else {

                    ArubaFeApiRestManager::getInstance()->sendOrder( $order_id, null, true );

                }
            }

            wp_safe_redirect( $returnLink );

            return;
        }

        if ( $_GET['fe_action'] === 'resendDraftInvoice' ) {

            $order_id = (int) $_GET['order_id'];

            $returnLink = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

            if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'aruba_fe_resend_invoice' ) ) {

                $hasError = get_post_meta( $order_id, ArubaFeConstants::ARUBA_FE_DRAFT_INVOICE_ERROR, true );

                if ( ! $hasError ) {

                    $this->notice->addNotice( esc_html__( 'The draft invoice was already sent', 'aruba-fatturazione-elettronica' ) );

                } else {

                    ArubaFeApiRestManager::getInstance()->createDraftInvoice( $order_id );

                }
            }

            wp_safe_redirect( $returnLink );

            return;

        }


        if ( isset( $_GET['order_id'] ) &&  $_GET['fe_action'] === 'download' && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'aruba_fe_download_document' ) ) {

            $orderID = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );

            switch ( $_GET['type'] ) :
                case 'order':
                    $response = ArubaFeApiRestManager::getInstance()->getOrderDocuments( $orderID );
                    break;
                case 'invoice':

                    $response = ArubaFeApiRestManager::getInstance()->getInvoiceDocuments( $orderID );
                    break;
                default:
                    return;
                    break;
            endswitch;

            if ( $response && $response->getState() ) {

               $ArubaFeDocument = new ArubaFeDocument();

               if(is_a($ArubaFeDocument,'WP_Error')){
                    echo esc_html__( 'The document is not avaiable', 'aruba-fatturazione-elettronica' );
                    exit;
               }

               $ArubaFeDocument->download($response);


            } else {
                echo esc_html__( 'The document is not avaiable', 'aruba-fatturazione-elettronica' );
                exit;
            }
        }
    }

    /**
        * @param $order \WC_Order
        * @param $data
        * @return void
     */

    public function checkForNewActions($order,$data){

        if(!is_a($order,'WC_Order') || $order->get_status() === 'auto-draft'){
            return;
        }

        $order_id = $order->get_id();

        $PM = get_post_meta( $order_id, ArubaFeConstants::ARUBA_FE_ORDER_ID, true );

        $ignore = get_post_meta( $order_id, ArubaFeConstants::ARUBA_FE_ORDER_INELEGGIBLE, true );

        $errors   = get_post_meta( $order_id,ArubaFeConstants::ARUBA_FE_ERRORS_LIST,true);

        /**
        * If exists an order id the order pass the 1st validation, no more data could be sent.
         */
        if($PM || $ignore || empty($errors)){
            return;
        }

        $orderData = new ArubaFeOrderDataTransfer($order_id);

        $orderData->setTestMode();

        $status = $order->get_status();

        if( $orderData->build() && ( $status === $this->options->order_state() ) ){

            // Sono nel caso automatizzato

            if ( $this->options->getConfigOption( 'create_invoice' ) == 'automatic_create_fe' ) {

                // Sono sull invio automatico al raggiungimento di uno stato

                if ( $this->options->getConfigOption( 'send_invoice' ) == 'automatic_send_fe' ) {

                        ArubaFeApiRestManager::getInstance()->sendOrder( $order_id, null, true );

                } else {
                    // CREO L'ordine
                    ArubaFeApiRestManager::getInstance()->sendOrder( $order_id );

                }

            } else {
                // CREO L'ordine di vendita
                ArubaFeApiRestManager::getInstance()->sendOrder( $order_id );

            }

        }

    }


    public function fe_on_order_status_changed( $order_id, $old_status, $new_status ) {

        $meta = get_post_meta( $order_id, ArubaFeConstants::ARUBA_FE_ORDER_REGISTRED, true );

        /**
         * Register additional order info to manage aruba fatturazione elettronica api required data
         */

        if ( ! $meta ) {
            $this->aruba_fe_update_order_meta( $order_id );
        }

        $ignore = get_post_meta( $order_id, ArubaFeConstants::ARUBA_FE_ORDER_INELEGGIBLE, true );

//        $create_invoice = $this->options->getConfigOption( 'create_invoice' ) === 'automatic_create_fe';

        $createAutomaticDocument = $new_status == $this->options->order_state();

        /**
        * If order is ineligible, automatic create invoice is disable or order isn't in the correct state will exit the function
         */

        if ( $ignore || /*! $create_invoice ||*/ ! $createAutomaticDocument ) {

            return null;

        }else{

            $create_invoice = $this->options->getConfigOption( 'create_invoice' );

            if($create_invoice === 'automatic_create_fe'){

                ArubaFeApiRestManager::getInstance()->sendOrder( $order_id, null, ($this->options->getConfigOption( 'send_invoice' ) === 'automatic_send_fe') );

            }elseif($create_invoice === 'manual_create_fe'){

                ArubaFeApiRestManager::getInstance()->sendOrder( $order_id );

            }



        }

    }

    public function aruba_fe_invoice_state_column( $columns ) {
        $tmp = array();

        foreach ( $columns as $key => $value ) {
            $tmp[ $key ] = $value;
            if ( $key === 'order_number' ) {
                $tmp['pdf_order'] = '';
            }
            if ( $key === 'order_status' ) {
                $tmp['state_invoice'] = esc_html__( 'Invoice Status', 'aruba-fatturazione-elettronica' );
            }
        }

        return $tmp;
    }


    public function aruba_fe_invoice_state_column_data( $column, $order ) {

        $order = is_object($order) ? $order :  wc_get_order( (int)$order );

        if ( ! $order || is_wp_error( $order ) ) {
            return;
        }

        $post_id = $order->get_id();

        $ignore = $order->get_meta( ArubaFeConstants::ARUBA_FE_ORDER_INELEGGIBLE );

        if ( $column == 'pdf_order' ) {

            if ( $ignore ) {
                return;
            }

            $orderFeId = get_post_meta( $post_id, ArubaFeConstants::ARUBA_FE_ORDER_ID, true );

            $orderStatus = get_post_meta( $post_id, ArubaFeConstants::ARUBA_FE_ORDER_STATUS, true );

            if ( $orderFeId ) {

                echo '<a class="aruba-fe-icon" target="_blank" href="' . esc_url(
                        wp_nonce_url(
                            admin_url(
                                'admin.php?fe_action=download&type=order&order_id=' . CustomFilters::sanitize_alphanumeric( $orderFeId ) . '&post_id=' . (int) $post_id
                            ),
                            'aruba_fe_download_document'
                        )
                    ) . '">' .
                    '<img width="26" height="30" src="' . esc_url( ARUBA_FE_URL . 'assets/images/pdf.svg' ) . '" title="' . esc_attr( ArubaFeConstants::ARUBA_FE_ORDER_STATES[ (int) $orderStatus ] ) . '" />'
                    . '</a>';

            }
        }

        if ( $column == 'state_invoice' && $order = wc_get_order( $post_id ) ) {

            if ( $ignore ) {

                echo '<div class="mt-1"><span class="aruba-fe-label label-warning">' .
                    esc_html__( 'Not applicable', 'aruba-fatturazione-elettronica' ) .
                    '</span></div>';

            } else {

                $invoices = get_post_meta( $post_id, ArubaFeConstants::ARUBA_FE_INVOICE_DATA, true );
                $drafts   = get_post_meta( $post_id, ArubaFeConstants::ARUBA_FE_DRAFTS_DATA, true );
                $errors   = get_post_meta( $post_id,ArubaFeConstants::ARUBA_FE_ERRORS_LIST,true);
                if ( $invoices ) {

                    foreach ( $invoices as $invoice ) {

                        $class = ArubaFeHelper::getClassByState( $invoice->status );
                        echo '<div class="mt-1"><a class="aruba-fe-label ' . esc_attr( $class ) . '" target="_blank" href="' . esc_url(
                                wp_nonce_url(
                                    admin_url(
                                        'admin.php?fe_action=download&type=invoice&order_id=' . CustomFilters::sanitize_alphanumeric( $invoice->id ) . '&post_id=' . (int) $post_id
                                    ),
                                    'aruba_fe_download_document'
                                )
                            ) . '">' . esc_html( ArubaFeStatusesLabel::getInvoiceLabel( $invoice->status ) ) . '</a></div>';

                    }
                }

                if ( $drafts ) {

                    foreach ( $drafts as $draft ) {

                        $class = ArubaFeHelper::getClassByState( $draft->status );

                        echo '<div class="mt-1"><span class="aruba-fe-label ' . esc_attr( $class ) . '">' . esc_html( ArubaFeStatusesLabel::getDraftLabel( $draft->status ) ) . ' - '
                            . esc_html( $draft->number )
                            . ' (' . esc_html__( 'Draft', 'aruba-fatturazione-elettronica' ) . ')'
                            . '</span></div>';

                    }
                }

                if($errors){

                    $errors = @unserialize($errors);

                    if(is_array($errors) && !empty($errors)){

                        echo '<div class="mt-1"><span class="aruba-fe-label label-error">' . esc_html__( 'Incomplete order', 'aruba-fatturazione-elettronica' ) . '</span></div>';

                    }

                }

            }
        }
    }

    public function aruba_fe_send_curtesy_copy($order){

        $mailer = WC()->mailer();

        $emailClasses = $mailer->get_emails();

        if(!isset($emailClasses['ArubaFeCurtesycopyEmail']))
            return;

        $ArubaFeCurtesycopyEmail = $emailClasses['ArubaFeCurtesycopyEmail'];

        $ArubaFeCurtesycopyEmail->sendEmail($order);

    }
}
