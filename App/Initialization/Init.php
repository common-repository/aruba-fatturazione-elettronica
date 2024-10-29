<?php
/**
 * Main init class
 **/

namespace ArubaFe\Initialization;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\AdminInit as InitializzationAdminClass;
use ArubaFe\CheckoutBlocks\ArubaFeCheckoutBlocks;
use ArubaFe\Publics\PublicInit as InitializzationPublicClass;


class Init
{

    function __construct()
    {
        add_action('plugins_loaded', [$this, 'ffs_load_aruba_fe']);
    }

    function ffs_load_aruba_fe()
    {
        $this->init();
    }

    private function init()
    {

        $this->loadAdminCode();
        $this->loadPubblicCode();
        $this->registerCheckoutBlocks();

    }

    private function loadPubblicCode()
    {

        new InitializzationPublicClass();

    }

    private function loadAdminCode()
    {

        new InitializzationAdminClass();

    }

    protected function registerCheckoutBlocks()
    {
        ArubaFeCheckoutBlocks::initialize();
    }


}
