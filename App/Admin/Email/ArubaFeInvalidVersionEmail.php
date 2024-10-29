<?php

namespace ArubaFe\Admin\Email;

use ArubaFe\Admin\Constants\ArubaFeConstants;
use ArubaFe\Admin\CustomOptions;

if (!defined('ABSPATH')) die('No direct access allowed');

abstract class ArubaFeInvalidVersionEmail
{

    protected static $errorSyntax = [
        'vs' => '',
        'sended' => '',
    ];

    public static function registerWarning($plg_verion){

        $current_warning = CustomOptions::get_option(ArubaFeConstants::ARUBA_FE_IN_ERROR,null);

        $warning = is_array($current_warning) ? wp_parse_args($current_warning,self::$errorSyntax) : self::$errorSyntax;

        if($warning){

            if($warning['vs'] == $plg_verion && $warning['sended']){
                return;
            }

        }

        if(!$current_warning){

            CustomOptions::add_option(ArubaFeConstants::ARUBA_FE_IN_ERROR,['vs' => sanitize_text_field($plg_verion) , 'sended' => '']);

        }else{

            CustomOptions::update_option(ArubaFeConstants::ARUBA_FE_IN_ERROR,['vs' => sanitize_text_field($plg_verion) , 'sended' => '']);

        }


    }

    public static function sendWarning(){

        $subject = esc_html__('Update the Aruba Electronic Invoicing plugin','aruba-fatturazione-elettronica');

        ob_start();
        include_once ARUBA_FE_PATH . 'templates/admin/tpl_admin_email.php';
        $body = ob_get_clean();

        $admin_email = get_option('admin_email');

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        return wp_mail($admin_email,$subject,$body,$headers);

    }

    public static function unsetWarning()
    {
        $current_warning = CustomOptions::get_option(ArubaFeConstants::ARUBA_FE_IN_ERROR,null);

        if($current_warning){
            CustomOptions::delete_option(ArubaFeConstants::ARUBA_FE_IN_ERROR);
        }

    }

}