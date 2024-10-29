<?php

namespace ArubaFe\Admin\Constants;

abstract class ArubaFeOptionsList
{

    protected static $list = [
        '_postmeta' => [],
        '_woocommerce_order_itemmeta' => [],
        '_wp_options' => [],
    ];

    public static function getAlloptions()
    {

        self::$list['_postmeta'] = [
            "_billing_customer_type_aruba_fe",
            "_billing_send_choice_invoice_aruba_fe",
            "_billing_need_invoice_aruba_fe",
            "_billing_codice_fiscale_aruba_fe",
            "_billing_partita_iva_aruba_fe",
            "_billing_sdi_aruba_fe",
            "_billing_pec_aruba_fe",
            "_aruba_fe_order_registred",
            "_aruba_fe_order_id",
            "_aruba_fe_order_number",
            "_aruba_fe_order_status",
            "_aruba_fe_invoices",
            "_aruba_fe_drafts",
            "_aruba_fe_has_error",
            "_auba_fe_curtesy_auto_sent",
        ];


        self::$list['_woocommerce_order_itemmeta'] = [
            '_aruba_fe_order_item_discount',
            '_aruba_fe_order_item_tax_rate',
            '_aruba_fe_order_item_tax_rate_id',
        ];

        return self::$list;

    }

}