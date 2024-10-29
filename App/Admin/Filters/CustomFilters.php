<?php
namespace ArubaFe\Admin\Filters;

if (!defined('ABSPATH')) die('No direct access allowed');

abstract class CustomFilters
{

    public static function sanitize_alphanumeric($text){

        return preg_replace('/[^a-zA-Z0-9]/','', $text );

    }

}