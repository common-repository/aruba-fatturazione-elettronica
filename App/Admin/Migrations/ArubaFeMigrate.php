<?php
namespace ArubaFe\Admin\Migrations;

if (!defined('ABSPATH')) die('No direct access allowed');

class ArubaFeMigrate
{
    protected $pluginVersion;

    public function __construct(){

        if( ! function_exists('get_plugin_data') ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $plugin_data = get_plugin_data( ARUBA_FE_PATH . '/aruba-fatturazione-elettronica.php' );

        if(isset($plugin_data['Version']) && $plugin_data['Version']){
            $this->pluginVersion = $plugin_data['Version'];
        }

    }

    public function checkVersions(){

        if(version_compare($this->pluginVersion,'0.1.5','<')){
            (new \ArubaFe\Admin\Migrations\Migrate\ArubaFeMigrateTo_0_1_4())->migrate();
        }

    }

}