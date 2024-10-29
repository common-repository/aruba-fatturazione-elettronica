<?php

namespace ArubaFe\Admin;
if (!defined('ABSPATH')) die('No direct access allowed');

class AdminMenuPage
{


    function __construct()
    {
        add_action('admin_menu', array($this, 'admin_page_custom_orders'));


    }

    public function admin_page_custom_orders()
    {
        $capability = 'manage_options';
        $slug = 'aruba-fatturazione-elettronica';

        add_submenu_page('woocommerce',
            esc_attr__('Aruba Electronic Invoicing - Configuration','aruba-fatturazione-elettronica'),
            esc_html__('Aruba Electronic Invoicing','aruba-fatturazione-elettronica'),
            $capability,
            $slug,
            array($this, 'aruba_fe_controller_cb')
        );

    }


    public function aruba_fe_controller_cb()
    {

        echo '<div class="wrap"><div id="aruba-fe-admin-app"></div></div>';

    }

}
