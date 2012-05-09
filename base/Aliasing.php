<?php

namespace base;

class Aliasing {
	
	protected $sandbox = NULL;
		
	public function __construct(&$sandbox){
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('request.passed', 'init', $this);
	}
	
	public function init($data) {
		try {
			$site = $this->getSite();
			$settings = $this->getSettings($site);
			$this->sandbox->setMeta('settings', $settings);
			$portal = $this->matchPortal($site);
			$this->sandbox->setMeta('portal', $portal);
		} catch (BaseException $e) {
			$message = $e->getMessage();
			return $this->sandbox->fire('aliasing.failed', $message);
		}
		$this->sandbox->fire('aliasing.passed', $this->getAlias());
	}
	
	protected function getSite(){
		$sql = sprintf("SELECT `site`, `source` FROM `alias` LEFT JOIN `site` ON `alias`.`site` = `site`.`ID` WHERE `title` = '%s' LIMIT 1", $this->getAlias());
		try {
			return $this->sandbox->getService('storage')->query($sql);
		} catch(StorageException $e) {
			throw new BaseException($e->getMessage());
		}
	}
	
	protected function getSettings($site){
		try {
			$sql = sprintf("SELECT * FROM `setting` WHERE `site` = %d", $site['site']);
			$settings = $this->sandbox->getService('storage')->query($sql);
		} catch(StorageException $e) {
			throw new BaseException($e->getMessage());
		}
	}
	
	protected function matchPortal($site){
		if(!file_exists($site['source'])) {
			throw new BaseException("Alias package '".$site['source']."' does not exists");
		}
		if(!is_readable($site['source'])){
			throw new BaseException("Alias package '".$site['source']."' file is not readable");
		}
		$package = simplexml_load_file($site['source']);
		$this->sandbox->setMeta('package', $package);
		$portal = $this->findPortalMatch($package);
		if(is_null($portal)) {
			throw new BaseException("Portal does not exists for URI : ".$this->sandbox->getMeta('URI'));
		} else {
			return $portal;
		}
	}
	
	protected function findPortalMatch($package){
		$URI = $this->sandbox->getMeta('URI');
		foreach($package->portal as $portal){
			foreach($portal->match as $match){
				$match = (string) $match;
				if($URI === $match) {
					return $portal;
				}
				if($match[strlen($match)-1] === "*") {
					if(substr_count($URI, rtrim($match, "*")) > 0){
						return $portal;
					}
				}
			}
		}
		return NULL;
	}
	
	public function getAlias(){
		return strtolower($_SERVER['HTTP_HOST']);;
	}
		
}

?>