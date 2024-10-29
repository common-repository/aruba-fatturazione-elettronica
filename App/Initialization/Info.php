<?php

namespace ArubaFe\Initialization;

if (!defined('ABSPATH')) die('No direct access allowed');

class Info{

  public $version = '';

  public function __construct( ){
    
  }

  

  public static function getPluginVersion() {
   
		if (!function_exists('get_plugin_data'))
			return "1.0.0";
		$data = get_plugin_data(ARUBA_FE__FILE);
		return $data['Version'];
	}


	public static function getPluginUrl(){
		if (defined(ARUBA_FE_URL)) {
			return ARUBA_FE_URL;
		}
		
	}
	public static function getPluginPath(){
		if (defined(ARUBA_FE_PATH)) {
			return ARUBA_FE_PATH;
		}
	}

}
