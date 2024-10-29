<?php
/**
 * EnqueueScript Class
 **/

namespace ArubaFe\Admin;

if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\Label\ArubaFeLabel;

class AdminEnqueueScript
{


    public function __construct()
    {

        $this->init();

    }

    public function init()
    {
        add_action('admin_enqueue_scripts', array($this, 'addAdminScripts'), 100);
    }

    public function addAdminScripts($hook)
    {
        global $pagenow;

        wp_enqueue_style('fe.css', ARUBA_FE_URL . 'assets/admin/css/aruba_fe_global.css',array(),'1.2.1');

        if ("woocommerce_page_aruba-fatturazione-elettronica" == $hook) {

            wp_enqueue_style('wp-react-aruba_fe', ARUBA_FE_URL . 'build/index.css', null, '1.2.1');
            wp_enqueue_style('aruba_fe_admin_g_style', ARUBA_FE_URL . 'assets/admin/css/aruba_fe_global.css', array(), '1.2.1', "all");

            wp_enqueue_script('wp-react-aruba_fe', ARUBA_FE_URL . 'build/index.js', ['jquery', 'wp-element', 'wp-i18n', 'wp-i18n', 'wp-polyfill', 'wp-api-fetch'], '1.2.1', true);
            wp_localize_script('wp-react-aruba_fe', 'aruba_fe_data', [
                'apiUrl'    => home_url('/wp-json'),
                'nonce'     => wp_create_nonce('aruba_fe_nonce'),
                'aruba_fe_labels' => (ArubaFeLabel::getInstance()->getAll()),
                'aruba_fe_wc_configs' => [
                    'taxEnabled' => apply_filters('wc_tax_enabled', get_option('woocommerce_calc_taxes') === 'yes'),
                ]
            ]);

            wp_enqueue_script('wp-aruba-fe-exit-intent', ARUBA_FE_URL . 'assets/js/aruba-fe-exit-intent.js',array(),array(),'1.2.1',['in_footer' => true]);



        } elseif ($pagenow === 'plugins.php') {

            wp_enqueue_style('aruba_fe_admin_g_style', ARUBA_FE_URL . 'assets/admin/css/aruba_fe_global.css', array(), '1.2.1', "all");
            wp_enqueue_script('fe.disable', ARUBA_FE_URL . 'assets/js/aruba-fe-disable.js',array(),array(),'1.2.1',['in_footer' => true]);
            wp_enqueue_script('fe.handlebars', ARUBA_FE_URL . 'assets/js/handlebars-v4.7.8.js',array(),array(),'4.7.8',['in_footer' => true]);
            wp_localize_script('fe.disable', 'aruba_fe_text', [
                'aruba_fe_labels' => (ArubaFeLabel::getInstance()->getAll()),
            ]);

        }

    }

}
