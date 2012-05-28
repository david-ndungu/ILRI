<?php

namespace base;

class Logging {
	
	protected $sandbox = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('application.error', 'logError', $this);
		$this->sandbox->listen('latency.log', 'logAccess', $this);
		$this->sandbox->listen('aliasing.failed', 'logError', $this);
		$this->sandbox->listen('authentication.failed', 'logError', $this);
		$this->sandbox->listen('routing.failed', 'logError', $this);
		$this->sandbox->listen('assembly.failed', 'logError', $this);
	}
	
	public function logAccess($latency){
		$log = $this->logMeta();
		$log['latency'] = $latency;
		$this->sandbox->getService('storage')->insert(array('table' => 'access', 'content' => $log));
	}
	
	public function logError($data){
		$log = $this->logMeta();
		$log['message'] = $data;
		$log['trace'] = json_encode(debug_backtrace());
		$this->sandbox->getService('storage')->insert(array('table' => 'error', 'content' => $log));
	}
	
	protected function logMeta() {
		$user = $this->sandbox->getService('user')->getUser();
		if($user['isGuest'] == 'Yes'){
			$log['guest'] = $user['ID'];
		} else {
			$log['user'] = $user['ID'];
		}
		$log['resource'] = $this->sandbox->getMeta('URI');
		$log['IP'] = $_SERVER['REMOTE_ADDR'];
		$log['creationTime'] = microtime(true);
		return $log;
	}
	
}

?>