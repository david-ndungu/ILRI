<?php

namespace base;

class Aliasing {
	
	protected $sandbox = NULL;
		
	public function __construct(&$sandbox){
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('request.passed', 'init', $this);
	}
	
	public function init($data) {
		$site = $this->getSite();
		if(is_null($site)) return;
		$settings = $this->getSettings($site[0]);
		if(is_null($settings)) return;
		try {
			$portal = $this->getPortal($site[0]);
		} catch (AliasingException $e) {
			return $this->sandbox->fire('aliasing.failed', $e->getTraceAsString());
		}
		$this->sandbox->setMeta('portal', &$portal);
		$this->fire('aliasing.passed', NULL);
	}
	
	protected function getSite($site){
		$sql = sprintf("SELECT `site`, `source` FROM `alias` LEFT JOIN `site` ON `alias`.`site` = `site`.`ID` WHERE `title` = '%s' LIMIT 1", $this->getAlias());
		try {
			$site = $this->sandbox->getGlobalStorage()->query($sql);
		} catch(StorageException $e) {
			return $this->sandbox->fire('aliasing.failed', $e->getTraceAsString());
		}
	}
	
	protected function getSettings($data){
		$sql = sprintf("SELECT * FROM `setting` WHERE `site` = %d", $site['ID']);
		try {
			$settings = $this->sandbox->getGlobalStorage()->query($sql);
		} catch(StorageException $e) {
			return $this->sandbox->fire('aliasing.failed', $e->getTraceAsString());
		}
	}
	
	protected function getPortal($site){
		$URI = $this->sandbox->getMeta('URI');
		$package = $site['package'];
		if(!file_exists($package) || is_readable($package)) {
			throw new AliasingException("Alias package '$package' does not exists");
		}
		$package = simplexml_load_file($package);
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
		throw new AliasingException("Portal does not exists for URI '$URI'");
	}
	
	public function getAlias(){
		return strtolower($_SERVER['HTTP_HOST']);;
	}
		
}

?>