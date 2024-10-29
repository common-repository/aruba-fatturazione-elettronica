<?php
namespace ArubaFe\Admin\Migrations\Migrate;

if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\CustomOptions;
use ArubaFe\Admin\Migrations\ArubaFeMigrationInterface;
use ArubaFe\Admin\RestApi\Parser\ArubaFeOptionParser;


class ArubaFeMigrateTo_0_1_4 implements ArubaFeMigrationInterface
{

    public function migrate(){

       $options = new ArubaFeOptionParser();

       if($options->getConfigOption('order_state') === 'state_order_pending'){

           $aruba_global_data = CustomOptions::get_option('aruba_global_data');

           if( is_array($aruba_global_data) && isset( $aruba_global_data['global_data']['order_state'] ) ){

               $aruba_global_data['global_data']['order_state'] = 'state_order_processing';

               CustomOptions::update_option('aruba_global_data',$aruba_global_data);

           }

       }


    }

}