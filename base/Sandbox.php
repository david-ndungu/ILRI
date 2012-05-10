<?php

namespace base;

class Sandbox {
	
	protected $meta = NULL;
	
	protected $storage = NULL;
			
	protected $events = NULL;
	
	protected $services = NULL;
	
	public $settings = NULL;
	
	public function __construct(&$settings) {
		$this->setMeta('settings', $settings);
		$this->setBase();
		$this->setURI();
		$this->setMethod();
		$this->initService("Storage");
		$this->initService("Session");
		$this->initService("User");
		$this->initService("Logging");
		$this->initService("Aliasing");
		$this->initService("Authentication");
		$this->initService("Routing");
		$this->initService("Assembly");
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
			$parameter = is_array($data) ? $data : array(&$data);
			call_user_func_array($callback, $parameter);
		}
		return NULL;
	}
	
	protected function initService($service){
		try {
			$class = "\base\\$service";
			$base = $this->getMeta('base');
			require_once("$base/base/$service.php");
			$this->services[strtolower($service)] = new $class($this);
		}catch(Exception $e){
			$this->sandbox->fire('sandbox.error', $e->getTraceAsString());
		}
	}
	
	public function getService($service){
		return $this->services[$service];
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
		
	public function setMeta($key, &$value){
		$this->meta[$key] = $value;
	}
	
	public function getMeta($key){
		return array_key_exists($key, $this->meta) ? $this->meta[$key] : NULL;
	}
	
}

?>