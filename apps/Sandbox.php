<?php

namespace apps;

class Sandbox extends \base\Sandbox {
	
	public function __construct(&$settings) {
		$this->setMeta('settings', $settings);
		$this->setBase();
		$this->setURI();
		$this->setMethod();
		$this->initService("Storage");
		$this->initService("Translation");
	}
	
	public function setService($name, &$service){
		$this->services[$name] = &$service;
	}

		
}

?>