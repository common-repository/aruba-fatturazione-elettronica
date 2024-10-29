<?php
namespace ArubaFe\Admin;
if (!defined('ABSPATH')) die('No direct access allowed');

class AdminInit
{

	public function __construct()
	{
		$this->init();
	}

	private function init()
	{

		new AdminEnqueueScript();
		new AdminMenuPage();
        new ArubaFeHooksManage();
        new AdminRestApi();
        new \ArubaFe\Admin\Checkout\ArubaFeCheckout();

	}
}
