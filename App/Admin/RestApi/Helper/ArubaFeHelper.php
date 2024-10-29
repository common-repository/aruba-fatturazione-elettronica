<?php

namespace ArubaFe\Admin\RestApi\Helper;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\CustomOptions;
use ArubaFe\Admin\Filters\CustomFilters;
use ArubaFe\Admin\RestApi\ArubaFeApiRestManager;

class ArubaFeHelper
{

    protected static $domain = ARUBA_FE_EP;

    public static function getArubaFeOrderIdByWcId($post_id)
    {

        return get_post_meta($post_id, ArubaFeConstants::ARUBA_FE_ORDER_ID, true);

    }

    /**
     * @param $order_id
     * @return void
     */

    public static function getInvoiceDocumentByOrderId($order_id)
    {

        $aruba_fe_invoices = get_post_meta($order_id, ArubaFeConstants::ARUBA_FE_INVOICE_DATA, true);

        if (empty($aruba_fe_invoices))
            return false;

        $last = array_pop($aruba_fe_invoices);

        if (!$last->id)
            return false;

        $stream = ArubaFeApiRestManager::getInstance()->getInvoiceDocuments($last->id);

        if (is_object($stream)) {

            if ($stream->getState()) {
                return new class($stream) {
                    public $fileName;
                    public $fileContent;

                    public function __construct($stream)
                    {

                        $this->fileContent = base64_decode($stream->getJson()->content);
                        $this->fileName = $stream->getJson()->fileName;

                    }
                };
            } else {
                return false;
            }

        } else {
            return false;
        }

    }

    public static function getOrderInvoice(int $order_id)
    {

        $aruba_fe_invoices = get_post_meta($order_id, ArubaFeConstants::ARUBA_FE_INVOICE_DATA, true);

        if (empty($aruba_fe_invoices))
            return false;

        $last = array_pop($aruba_fe_invoices);

        if (!$last->id)
            return false;

        return $last->id;

    }

    public static function getClassByState($id)
    {

        if (in_array($id, [0, 2, 4, 5, 6, 9, 10]))
            return 'label-error';
        elseif (in_array($id, [1, 3, 7, 8]))
            return 'label-success';
        else
            return 'label-warning';


    }

    public static function getOrderLink($orderFeId)
    {
        return esc_url(self::$domain . '/?type=ODV&id=' . CustomFilters::sanitize_alphanumeric($orderFeId));
    }

    public static function getInvoiceLink($invoiceFeId)
    {
        return esc_url(self::$domain . '/?type=FTX&id=' . CustomFilters::sanitize_alphanumeric($invoiceFeId));
    }

    public static function getInvoiceNDCLink($invoiceFeId)
    {
        return esc_url(self::$domain . '/?type=NDC&id=' . CustomFilters::sanitize_alphanumeric($invoiceFeId));
    }

    public static function getInvoicePdf($invoiceId, $order_id)
    {

        return esc_url(wp_nonce_url(
            admin_url('admin.php?fe_action=download&type=invoice&order_id=' . CustomFilters::sanitize_alphanumeric($invoiceId) . '&post_id=' . (int)$order_id)
            , 'aruba_fe_download_document'
        ));

    }

    public static function registerDefaultZeroRate($rates)
    {

        foreach ($rates as $taxRate) {

            if (strpos(strtolower($taxRate->description), 'n2.2 ') !== false) {

                if (!CustomOptions::add_option(ArubaFeConstants::ARUBA_FE_DEFAULT_ZERO_RATE, sanitize_key($taxRate->id))) {
                    CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_DEFAULT_ZERO_RATE, sanitize_key($taxRate->id));
                }
            }

        }

    }

    public static function getPluginVersion(){

        if(!function_exists('get_plugin_data')){
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $plugin_data = get_plugin_data(ARUBA_FE_PATH . 'aruba-fatturazione-elettronica.php');
        $plugin_version = $plugin_data['Version'];
        return $plugin_version;
    }


}