<?php

namespace base;

class Routing {
	
	protected $sandbox = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('portlet.authentication.passed', 'init', $this);
	}
	
	public function init($data) {
		$base = $data['base'];
		$module = (string) $data['portlet']->attributes()->module;
		$controller = (string) $data['portlet']->attributes()->controller;
		$source = $this->sourceFile($base, $module, $controller);
		if(is_null($source)) return;
		$this->route($source, $module, $controller, $data);
	}
	
	protected function sourceFile($base, $module, $controller) {
		$dir = "$base/apps/$module";
		if(!is_dir($dir)) return NULL;
		$source = "$dir/$controller.php";
		if(!file_exists($source) || !is_readable($source)) return NULL;
		return $source;
	}
	
	protected function route($source, $module, $controller, $data) {
		require_once($source);
		$portlet = "apps\\$module\\$controller";
		if(!class_exists($portlet)) return;
		$instance = new $portlet();
		$method = $data['method'];
		if(!method_exists($instance, $method)) return;
		try {
			$data['response'] = call_user_func_array(array($instance, $method), array($this->sandbox, $data));
			$this->sandbox.fire('routing.passed', $data);
		} catch (Exception $e) {
			$data['assembly.error'] = $e->getTraceAsString();
			sandbox.fire('routing.failed', $data);
		}
	}
	
}

?>