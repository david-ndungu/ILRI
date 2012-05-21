<?php

namespace apps\studio;

class NavigationModel {
	
	protected $controller = NULL;
	
	protected $menu = array();
	
	public function __construct(&$controller){
		$this->controller = &$controller;
		$this->buildMenu();
	}
	
	protected function buildMenu(){
		$authentication = $this->controller->getSandbox()->getService('authentication');
		$package = $this->controller->getSandbox()->getMeta('package');
		foreach ($package as $portal){
			if(property_exists($portal, "access")){
				$access = $portal->access;
				if($authentication->attestUser($access) || $authentication->attestRole($access)){
					foreach($portal->navigation as $match){
						$uri = (string) $match->attributes()->uri;
						$group = (string) $match->attributes()->group;
						$group = strlen($group) ? $group : "default";
						$this->menu[$group][] = $this->buildLink($match);
					}
				}
			}
		}
	}
	
	protected function buildLink($match){
		$parent = (string) $match->attributes()->parent;
		$parent = strlen($parent) ? $parent : "root";
		$index = (string) $match->attributes()->label;
		$uri = (string) $match->attributes()->uri;
		$label = (string) $this->controller->translate($index);
		$text = strlen($label) ? $label : "link item";
		$name = (string) $match->attributes()->name;
		$name = strlen($name) ? $name : "default";
		return array($parent => array($name => array('uri' => $uri, 'text' => $text)));
	}
	
	public function getMenu(){
		return $this->menu;
	}
	
}