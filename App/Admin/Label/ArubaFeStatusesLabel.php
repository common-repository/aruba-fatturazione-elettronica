<?php

namespace ArubaFe\Admin\Label;

use ArubaFe\Admin\Constants\ArubaFeConstants;

if (!defined('ABSPATH')) die('No direct access allowed');

class ArubaFeStatusesLabel
{

    public static function getDraftLabel($status){

        $text = '';

        switch ($status):

            case 0:
                $text = __( 'Incompleto' , 'aruba-fatturazione-elettronica');
                break;

            case 1:
                $text = __( 'Completo' , 'aruba-fatturazione-elettronica');
                break;

        endswitch;

        return $text;

    }

    public static function getInvoiceLabel($status)
    {

        $text = '';

        switch ($status):

            case 0:
                $text = __( 'Incompleto' , 'aruba-fatturazione-elettronica');
                break;

            case 1:
                $text = __( 'Presa in carico' , 'aruba-fatturazione-elettronica');
                break;

            case 2:
                $text = __( 'Errore elaborazione' , 'aruba-fatturazione-elettronica');
                break;

            case 3:
                $text = __( 'Inviata a SDI' , 'aruba-fatturazione-elettronica');
                break;

            case 4:
                $text = __( 'Scartata' , 'aruba-fatturazione-elettronica');
                break;

            case 5:
                $text = __( 'Non consegnata' , 'aruba-fatturazione-elettronica');
                break;

            case 6:
                $text = __( 'Recapito impossibile' , 'aruba-fatturazione-elettronica');
                break;

            case 7:
                $text = __( 'Consegnato' , 'aruba-fatturazione-elettronica');
                break;

            case 8:
                $text = __( 'Accettata' , 'aruba-fatturazione-elettronica');
                break;

            case 9:
                $text = __( 'Rifiutata' , 'aruba-fatturazione-elettronica');
                break;

            case 10:
                $text = __( 'Decorrenza termini' , 'aruba-fatturazione-elettronica');
                break;

        endswitch;

        return $text;

    }

}