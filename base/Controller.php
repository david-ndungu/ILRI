<?php

namespace base;

class Controller {
	
	protected $sandbox = NULL;
	
	public function __construct(&$sandbox){
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('aliasing.failed', 'sendHeader404', $this);
		$this->sandbox->listen('authentication.failed', 'shieldResponse', $this);
		$this->sandbox->listen('routing.failed', 'sendHeader500', $this);
		$this->sandbox->listen('assembly.failed', 'sendHeader500', $this);
		$this->sandbox->listen('assembly.passed', 'sendContent', $this);
		$URI = $this->sandbox->getMeta('URI');
		$this->sandbox->fire('request.passed', $URI);
	}
	
	public function shieldResponse(){
		$type = (string) $this->sandbox->getMeta('portal')->attributes()->type;
		if($type == "html"){
			header("Location: /signin");
			exit;
		}else{
			$this->sendHeader403();
		}
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