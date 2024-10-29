<?php

namespace ArubaFe\Admin\RestApi\DataTransfer;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\CustomOptions;
use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;
use ArubaFe\Admin\Traits\ArubaFeLogTrait;

class ArubaFeDataTransferSettings
{
    use ArubaFeLogTrait;

    protected $options;
    protected $fe_options;

    public function __construct()
    {
        $this->options = CustomOptions::get_option('aruba_global_data');
        $this->fe_options = new ArubaFeOptionParser();
    }

    public function build()
    {

        $settings = [
            "sendInvoice" => $this->fe_options->sendInvoice(),
            "createDraft" => $this->fe_options->createDraft(),
            "orderStatus" => $this->fe_options->order_state() == 'completed' ? 1 : 0,
            "paymentStatus" => $this->fe_options->getConfigOption('reporting_receipts_paid_invoices') == 'automatic_receipts_fe',
            "productDescription" => $this->fe_options->order_state_numeric(),
            "taxpayerInvoice" => $this->fe_options->getConfigOption('individual_create_invoce') == 'create_always_fe',
            "createBuyerData" => $this->fe_options->getConfigOption('update_data_customer') == 'automatically_update_data_customer',
            "sendPdf" => $this->fe_options->getConfigOption('send_coutesy_copy') == 'automatic_send_coutesy_copy',
            "payments" => $this->fe_options->processPayments(),
            "taxCodes" => $this->fe_options->processTaxes(),
            "showFullPrice" => $this->fe_options->showFullPrice(),
            "defaultBank" => $this->fe_options->getBankId(),
        ];

        return $settings;
    }



}
