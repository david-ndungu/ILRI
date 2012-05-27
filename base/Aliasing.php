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
			$result = $this->getSite();
			$site = $result[0];
			$this->sandbox->setMeta('site', $site);
			$settings = $this->getSettings($site);
			$this->sandbox->setMeta('settings', $settings);
			$portal = $this->matchPortal($site);
			$this->sandbox->setMeta('portal', $portal);
		} catch (BaseException $e) {
			$message = $e->getMessage();
			return $this->sandbox->fire('aliasing.failed', $message);
		}
		$alias = $this->getAlias();
		$this->sandbox->fire('aliasing.passed', $alias);
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
			if(is_null($settings)) return NULL;
			$result = array();
			foreach($settings as $setting){
				$result[$setting['key']] = $setting['value'];
			}
			return $result;
		} catch(StorageException $e) {
			throw new BaseException($e->getMessage());
		}
	}
	
	protected function matchPortal($site){
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
		$handler = NULL;
		foreach($package->portal as $portal){
			foreach($portal->navigation as $match){
				$request = (string) $match->attributes()->uri;
				if($URI === $request) {
					return $portal;
				}
				if($request[strlen($request)-1] === "*") {
					if(substr_count($URI, rtrim($request, "*")) > 0){
						$handler = $portal;
					}
				}
			}
		}
		return $handler;
	}
	
	public function getAlias(){
		return strtolower($_SERVER['HTTP_HOST']);;
	}
		
}

?>