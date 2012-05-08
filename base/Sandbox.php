<?php

namespace base;

class Sandbox {
	
	protected $meta = NULL;
	
	protected $storage = NULL;
		
	protected $helpers = NULL;
	
	protected $events = NULL;
	
	protected $services = NULL;
	
	public function __construct() {
		$this->setBase();
		$this->setURI();
		$this->setMethod();
		$this->initService("Aliasing");
		$this->initService("Routing");
		$this->initService("Authentication");
		$this->initService("Dispatch");
		$this->helpers['session'] = new Session();
	}
	
	public function listen($types = NULL, $method = NULL, $instance = NULL){
		if(is_string($types)) $types = array($types);
		foreach($types as $type){
			$this->events[$type][] = array('instance' => $instance, 'method' => $method);
		}
		return NULL;
	}
	public function fire($type = NULL, &$data = NULL){
		if(is_null($type) || !array_key_exists($type, $this->events)) return;
		$listeners = $this->events[$type];
		foreach($listeners as $listener){
			$callback = is_null($listener['instance']) ? $listener['method'] : array($listener['instance'], $listener['method']);
			call_user_func_array($callback, &$data);
		}
		return NULL;
	}
	
	protected function initService($service){
		try {
			$class = "base\\$service";
			$base = $this->getBase();
			require_once("$base/base/$service.php");
			$this->services[$service] = new $class(&$this);
		}catch(Exception $e){
			$this->sandbox->fire('sandbox.error', $e->getTraceAsString());
		}
	}
	
	public function getService($service){
		return $this->services[$service];
	}
	
	public function setGlobalStorage($settings){
		$this->storage['global'] = new Storage($settings);
	}
	
	public function getGlobalStorage() {
		return $this->storage['global'];
	}
	
	public function setLocalStorage($settings){
		$this->storage['local'] = new Storage($settings);
	}
	
	public function getLocalStorage(){
		return $this->storage['local'];
	}
	
	public function getSession() {
		return $this->helpers['session'];
	}
				
	protected function setBase(){
		$this->meta['base'] = str_replace('/html', '', getcwd());;
	}
		
	protected function setURI(){
		$resource = rtrim($_SERVER['REQUEST_URI'], "/");
		$this->meta['URI'] = strlen($resource) == 0 ? "/" : $resource;
	}
		
	protected function setMethod(){
		$this->meta['method'] = $_SERVER['REQUEST_METHOD'] == "GET" ? "doGet" : "doPost";
	}
	
	public function getMethod(){
		return $this->meta['method'];
	}
	
	public function setMeta($key, &$value){
		$this->meta[$key] = $value;
	}
	
	public function getMeta($key){
		return array_key_exists($key, $this->meta) ? $this->meta[$key] : NULL;
	}
	
}

?>