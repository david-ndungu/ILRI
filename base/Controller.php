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
		return Response::sendHeader(403);
	}
	
	public function sendHeader404(){
		return Response::sendHeader(404);
	}

	public function sendHeader500(){
		return Response::sendHeader(500);
	}
		
	public function log($latency){
		return $this->sandbox->fire('latency.log', $latency);
	}
	
}

?>