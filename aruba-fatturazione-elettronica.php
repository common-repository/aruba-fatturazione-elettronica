<?php
/**
Plugin Name: Aruba Fatturazione Elettronica
Requires Plugins: woocommerce
Plugin URI: https://www.pec.it/acquista-fatturazione-elettronica.aspx
Description: Aruba Fatturazione Elettronica is a WooCommerce plugin for managing e-commerce orders and invoices using Aruba's e-invoicing service
Version: 1.2.0
Author: Aruba.it
Author URI: https://www.aruba.it
Text Domain: aruba-fatturazione-elettronica
Domain Path: /languages
License: GPL v3
Requires at least: 6.2
Tested up to: 6.6
Requires PHP: 7.4
**/
if (!defined('ABSPATH')) die('No direct access allowed');


use \ArubaFe\Initialization\Init;
use \ArubaFe\Initialization\Activator;
use \ArubaFe\Initialization\Deactivator;
use \ArubaFe\Initialization\Uninstall;

$plugin_dir_path = "ARUBA_FE_PATH";
$plugin_dir_url = "ARUBA_FE_URL";
$setting_page_url = "ARUBA_FE_SETTING_PAGE_URL";
$config_wrapper_plugin = "ARUBA_FE__FILE";

if (!defined($plugin_dir_path)) {
    define('ARUBA_FE_PATH', plugin_dir_path(__FILE__));
}
if (!defined($plugin_dir_url)) {
    define('ARUBA_FE_URL', plugin_dir_url(__FILE__));
}
if (!defined($setting_page_url)) {
    define('ARUBA_FE_SETTING_PAGE_URL', admin_url('admin.php?page=aruba-fatturazione-elettronica'));
}

if (!defined($config_wrapper_plugin)) {
    define($config_wrapper_plugin, __FILE__);
}

if (!defined('ARUBA_FE_BN'))
    define('ARUBA_FE_BN', plugin_basename(__FILE__));


require ARUBA_FE_PATH . '/vendor/autoload.php';


load_plugin_textdomain('aruba-fatturazione-elettronica', false, plugin_basename(dirname(__FILE__)) . '/languages');


if(!defined('ARUBA_FE_EP')){

    try{

        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ );

        $dotenv->load();

        $arubaFeEndopoint = esc_url_raw($_ENV['ARUBE_FE_ENDPOINT'],['https']);

        if(!$arubaFeEndopoint){
            return new WP_Error('501',__('Invalid endpoint','aruba-fatturazione-elettronica'));
        }

        define('ARUBA_FE_EP', $arubaFeEndopoint);
        if(isset($_ENV['ARUBA_FE_ENABLE_LOG']))
            define('ARUBA_FE_LOG_ENABLED',(int)$_ENV['ARUBA_FE_ENABLE_LOG']);
        else
            define('ARUBA_FE_LOG_ENABLED',0);

    }catch (Dotenv\Exception\InvalidPathException $exception){

        define('ARUBA_FE_EP','https://fatturazioneelettronica.aruba.it');
        define('ARUBA_FE_LOG_ENABLED',0);
    }

}

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return new WP_Error('404',__('WooCommerce is required','aruba-fatturazione-elettronica'));
}

new Init();

register_activation_hook(__FILE__, '\\aruba_fe_activation');

function aruba_fe_activation()
{
    Activator::activator();
}

register_deactivation_hook(__FILE__, '\\aruba_fe_deactivation');

function aruba_fe_deactivation()
{
    Deactivator::deactivator();
}

register_uninstall_hook(__FILE__, '\\aruba_fe_uninstall');
function aruba_fe_uninstall()
{
    Uninstall::startUninstall();
}
