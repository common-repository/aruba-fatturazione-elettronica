<?php
namespace ArubaFe\Admin\RestApi\Interfaces;
if (!defined('ABSPATH')) die('No direct access allowed');

interface ArubaFeApiResponseInterface
{
	public function getState();
	public function setState();
	public function getJSON();
	public function setJSON();
	public function setError($error);
	public function getError(bool $asString = false);
}
