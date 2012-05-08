<?php

namespace base;

class Session {
	
	protected $hash = NULL;
	
	protected $seed = NULL;

	public function __construct() {
		session_name("SESSION");
		session_start();
		$this->setHash();
	}
	
	public function write($key, $value) {
		$_SESSION[$key] = serialize($value);
	}
	
	public function read($key) {
		if($this->hash != $_SESSION['hash']);
		return isset($_SESSION[$key]) ? unserialize($_SESSION[$key]) : NULL;
	}
	
	protected function setHash(){
		$this->hash = $this->generateHash();
		if(!isset($_SESSION['hash'])) {
			$this->write("hash", $this->hash);
		}
	}
	
	protected function generateHash(){
		$this->setSeed();
		$hash = $this->seed;
		$hash .= $_SERVER['HTTP_USER_AGENT'];
		$blocks = explode('.', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1");
		for($i = 0; $i < 4; $i++){
			$hash .= $blocks[$i];
		}
		return md5($hash);
	}
	
	public function getHash(){
		return $this->hash;
	}
	
	
	protected function setSeed(){
		$pool = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$i = 0;
		$j = strlen($pool) - 1;
		while($i++ < 8){
			$seed[] = substr($pool, mt_rand(0, $j), 1);
		}
		$this->seed = join("", $seed);
	}
	
	public function getSeed(){
		return $this->seed;
	}
	
}

?>