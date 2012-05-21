<?php

namespace apps;

require_once("$base/base/ApplicationException.php");

class Application {
	
	protected $sandbox = NULL;
	
	protected $storage = NULL;

	protected $settings = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->settings = $this->sandbox->getMeta('settings');
		$this->storage = new \base\Storage($this->sandbox);
	}
	
	public function doGet(){
		
	}
	
	public function doPost(){
		
	}
	
	public function getStorage(){
		return $this->storage;
	}
	
	public function getSandbox(){
		return $this->sandbox;
	}
	
	public function onError($e){
		$this->sandbox->fire('application.error', $e->getMessage());
		\base\Response::sendHeader(400);
	}
	
	public function translate($index){
		return $this->sandbox->getService("translation")->translate($index);
	}
	
}

?>