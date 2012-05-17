<?php

namespace base;

use apps\ApplicationException;

class Translation {
	
	protected $sandbox = NULL;
	
	protected $locale = NULL;
	
	protected $translations = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('authentication.passed', 'init', $this);
	}
	
	public function translate($index=NULL){
		$key = (string) $index;
		if(strlen($key)<1) return NULL;
		return (string) @$this->translations[$key];
	}
	
	public function init($data){
		$settings = $this->sandbox->getMeta('settings');
		$base = $this->sandbox->getMeta('base');
		$filename = "$base/locale/".$settings['locale'].".xml";
		if(!file_exists($filename)){
			throw new \apps\ApplicationException("Locale '$filename' not found");
		} else {
			$this->locale = simplexml_load_file($filename);
			foreach($this->locale->label as $translation){
				$index = (string) $translation->attributes()->index;
				$this->translations[$index] = (string) $translation;
			}
		}
	}
	
}

?>