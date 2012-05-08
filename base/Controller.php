<?php

namespace base;

class Controller {
	
	protected $sandbox = NULL;
	
	public function __construct(){
		$this->initSandbox();
		$base = $this->sandbox->getBase();
		require_once("$base/settings.php");
		$this->sandbox->setGlobalStorage($settings);
		$this->sandbox->fire('request.passed');
	}
	
	protected function initSandbox(){
		require_once("$this->base/base/Sandbox.php");
		$this->sandbox = new Sandbox();
	}
					
}

?>