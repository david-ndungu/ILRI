<?php

namespace base;

class Assembly {
	
	protected $sandbox = NULL;
	
	protected $content = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('routing.passed', 'init', $this);
	}
	
	public function init($data) {
		$this->content = $data;
		switch((string) $this->sandbox->getMeta('portal')->attributes()->type){
			case "raw":
				return $this->sandbox->fire('assembly.passed', $this->getContent());
				break;
			case "json":
				return $this->sandbox->fire('assembly.passed', $this->toJSON());
				break;
			case "xml":
				return $this->sandbox->fire('assembly.passed', $this->toXML());
				break;
			case "html":
				try {
					return $this->sandbox->fire('assembly.passed', $this->toHTML());
				}catch (BaseException $e) {
					return $this->sandbox->fire('assembly.failed', $e->getTraceAsString());
				}
				break;							
		}
	}
	
	public function getContent(){
		return join("", $this->content);
	}
	
	protected function toJSON(){
		return json_encode($this->content);
	}
	
	protected function toXML($models = null, &$xml = null){
		if(is_null($xml)) {
			$xml = new \SimpleXMLElement("<?xml version='1.0'?><response></response>");
		}
		if(is_null($models)) {
			$models = $this->content;
		}
		foreach($models as $key => $value){
			$node = is_numeric($key) ? "node-$key" : $key;
			if(is_array($value)){
				$this->toXML($value, $xml->addChild($node));
			}else{
				$xml->addChild($node, $value);
			}
		}
		return $xml->asXML();
	}
	
	protected function toHTML(){
		try {
			$template = (string) $this->sandbox->getMeta('portal')->getPortal()->attributes()->template;
			$theme = (string) $this->sandbox->getMeta('package')->theme;
			$xslt = new XsltProcessor();
			$xslt->importStylesheet(simplexml_load_file("../$theme/$template"));
			$html = $xp->transformToXML($this->toXML());
		} catch (\Exception $e) {
			throw new BaseException($e->getTraceAsString());
		} 
	}
	
}
?>