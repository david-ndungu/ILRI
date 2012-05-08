<?php

namespace base;

class Assembly {
	
	protected $sandbox = NULL;
	
	protected $content = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('dispatch.passed', 'setContent', $this);
	}
	
	public function setContent($data) {
		$module = (string) $data['portlet']->attributes()->module;
		$controller = (string) $data['portlet']->attributes()->controller;
		$this->content[$module] = array($controller, $data['response']); 
	}
	
	protected function toJSON(){
		return json_encode($this->models);
	}
	
}
?>