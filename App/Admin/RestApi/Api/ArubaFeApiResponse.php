<?php

namespace ArubaFe\Admin\RestApi\Api;
if (!defined('ABSPATH')) die('No direct access allowed');

use ArubaFe\Admin\RestApi\Interfaces\ArubaFeApiResponseInterface;

class ArubaFeApiResponse implements ArubaFeApiResponseInterface
{

	protected $body;
	protected $state;
	protected $errors;

	public function __construct($body,$state){
		$this->body = $body;
		$this->state = $state;
	}


	public function getState()
	{

		if($this->state >= 200 && $this->state < 300)
			return true;

		return false;

	}

	public function setState()
	{
		// TODO: Implement setState() method.
	}

	public function getJSON()
	{
		return json_decode($this->body);
	}

	public function setJSON()
	{
		// TODO: Implement setBody() method.
	}

	public function setError($error)
	{
		$this->errors[] = $error;
	}

	public function getError(bool $asString = false)
	{
		return $asString ? implode("\n",$this->errors) : $this->errors;
	}

    public function getHTTPStatus(){
        return $this->state;
    }

}
