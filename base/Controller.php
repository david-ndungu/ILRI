<?php

namespace base;

class Controller {
	
	protected $sandbox = NULL;
	
	public function __construct(&$sandbox){
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('aliasing.failed', 'sendHeader404', $this);
		$this->sandbox->listen('authentication.failed', 'sendHeader403', $this);
		$this->sandbox->listen('routing.failed', 'sendHeader500', $this);
		$this->sandbox->listen('assembly.failed', 'sendHeader500', $this);
		$this->sandbox->listen('assembly.passed', 'sendContent', $this);
		$this->sandbox->fire('request.passed', $this->sandbox->getMeta('URI'));
	}
			
	public function sendContent(&$content){
		print $content;
	}
	
	public function sendHeader403(){
		return $this->sendHeader(403);
	}
	
	public function sendHeader404(){
		return $this->sendHeader(404);
	}

	public function sendHeader500(){
		return $this->sendHeader(500);
	}
	
	protected function sendHeader($code){
 		ob_clean();
		switch($code){
			case 204:
				header("HTTP/1.1 204 No Content");
				break;
			case 402:
				header("HTTP/1.1 403 Payment Required");
				break;
			case 403:
				header("HTTP/1.1 403 Forbidden");
				break;
			case 404:
				header("HTTP/1.1 404 Not Found");
				break;
			case 500:
				header("HTTP/1.1 500 Internal Server Error");
				break;
		}
		exit;
	}
	
	public function log($latency){
		return $this->sandbox->fire('latency.log', $latency);
	}
	
}

?>