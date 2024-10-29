<?php
namespace ArubaFe\CheckoutBlocks;

defined('ABSPATH') or die('Direct access is not allowed');

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

class ArubaFeCheckoutBlocksIntegration implements IntegrationInterface
{

    protected $name = 'aruba-fatturazione-elettronica-checkout-blocks';

    protected $version = "1.0.0";


    public function get_name()
    {
        return $this->name;
    }

    public function initialize()
    {
        $this->register_block_editor_scripts();
        $this->register_block_fe_scripts();
        add_filter('__experimental_woocommerce_blocks_add_data_attributes_to_block', [$this, 'add_attributes_to_frontend_blocks'], 10, 1);

    }

    public function get_script_handles()
    {
        return ['aruba-fatturazione-elettronica-checkout-blocks-frontend-billing','aruba-fatturazione-elettronica-checkout-blocks-frontend-shipping'];
    }

    public function get_editor_script_handles()
    {
        return ['aruba-fatturazione-elettronica-checkout-blocks-editor-billing','aruba-fatturazione-elettronica-checkout-blocks-editor-shipping'];
    }


    public function add_attributes_to_frontend_blocks($allowed_blocks)
    {
        $allowed_blocks[] = 'aruba-fatturazione-elettronica/aruba-fatturazione-elettronica-checkout-blocks';

        return $allowed_blocks;
    }



    protected function register_block_editor_scripts()
    {

        $script_url = ARUBA_FE_URL . 'build/aruba-fatturazione-elettronica-checkout-blocks-editor.js';
        $script_asset_path = ARUBA_FE_PATH . 'build/checkout-blocks/aruba-fatturazione-elettronica-checkout-blocks-editor.asset.php';

        $script_asset = file_exists($script_asset_path)
            ? require $script_asset_path
            : array(
                'dependencies' => array(),
                'version' => $this->get_file_version($script_asset_path),
            );

        wp_register_script(
            'aruba-fatturazione-elettronica-checkout-blocks-editor-billing',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        wp_set_script_translations(
            'aruba-fatturazione-elettronica-checkout-blocks-editor-billing', // script handle
            'aruba-fatturazione-elettronica', // text domain
            ARUBA_FE_PATH . 'languages'
        );

        wp_register_script(
            'aruba-fatturazione-elettronica-checkout-blocks-editor-shipping',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        wp_set_script_translations(
            'aruba-fatturazione-elettronica-checkout-blocks-editor-shipping', // script handle
            'aruba-fatturazione-elettronica', // text domain
            ARUBA_FE_PATH . 'languages'
        );

    }

    protected function register_block_fe_scripts()
    {
        $script_url = ARUBA_FE_URL . 'build/aruba-fatturazione-elettronica-checkout-blocks-frontend-billing.js';
        $script_url_shipping = ARUBA_FE_URL . 'build/aruba-fatturazione-elettronica-checkout-blocks-frontend-shipping.js';
        $script_asset_path = ARUBA_FE_PATH . 'build/aruba-fatturazione-elettronica-checkout-blocks-frontend-billing.asset.php';
        $script_asset_path_shipping = ARUBA_FE_PATH . 'build/aruba-fatturazione-elettronica-checkout-blocks-frontend-shipping.asset.php';

        $script_asset = file_exists($script_asset_path)
            ? require $script_asset_path
            : array(
                'dependencies' => array(),
                'version' => $this->get_file_version($script_asset_path),
            );

        $script_asset_shipping = file_exists($script_asset_path_shipping)
            ? require $script_asset_path_shipping
            : array(
                'dependencies' => array(),
                'version' => $this->get_file_version($script_asset_path_shipping),
            );

        wp_register_script(
            'aruba-fatturazione-elettronica-checkout-blocks-frontend-billing',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        wp_register_script(
            'aruba-fatturazione-elettronica-checkout-blocks-frontend-shipping',
            $script_url_shipping,
            $script_asset_shipping['dependencies'],
            $script_asset_shipping['version'],
            true
        );

        wp_set_script_translations(
            'aruba-fatturazione-elettronica-checkout-blocks-frontend-billing', // script handle
            'aruba-fatturazione-elettronica', // text domain
            ARUBA_FE_PATH . 'languages'
        );

        wp_set_script_translations(
            'aruba-fatturazione-elettronica-checkout-blocks-frontend-shipping', // script handle
            'aruba-fatturazione-elettronica', // text domain
            ARUBA_FE_PATH . 'languages'
        );
    }




    public function get_script_data()
    {
        return (new ArubaFeCheckoutExtendStoreApi())->get_script_data();
    }

    protected function get_file_version($file)
    {
        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG && file_exists($file)) {
            return filemtime($file);
        }

        return $this->version;
    }
}