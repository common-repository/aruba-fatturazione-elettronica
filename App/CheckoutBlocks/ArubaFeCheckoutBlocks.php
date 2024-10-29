<?php
namespace ArubaFe\CheckoutBlocks;

defined('ABSPATH') or die('Direct access is not allowed');

class ArubaFeCheckoutBlocks
{
    protected static $initialized = false;

    public static function initialize()
    {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        add_action(
            'woocommerce_blocks_loaded',
            function () {
                add_action(
                    'woocommerce_blocks_checkout_block_registration',
                    function ($integration_registry) {

                        $integration_registry->register(new ArubaFeCheckoutBlocksIntegration());

                    }
                );

                (new ArubaFeCheckoutExtendStoreApi())->extend_store_api();

            }
        );

    }

}