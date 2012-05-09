<?php

namespace base;

class Authentication {
	
	protected $sandbox = NULL;
	
	protected $portal = NULL;
		
	protected $user = NULL;
		
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->user = $this->sandbox->getService('user');
		$this->sandbox->listen('aliasing.passed', 'init', $this);
	}
	
	public function init($data){
		$this->portal = &$this->sandbox->getMeta('portal');
		if(!$this->shieldPortal()) {
			$message = "Access to portal not allowed";
			return $this->sandbox->fire('authentication.failed', $message);
		}
		if($this->shieldPortlets()){
			$this->sandbox->setMeta('portal', $this->portal);
			$this->sandbox->fire('authentication.passed', $this->portal);
		} else {
			$this->sandbox->fire('authentication.failed', "Access to portlets not allowed");
		}
	}
	
	protected function shieldPortal(){
		if(isset($this->portal->access)){
			return ($this->attestUser($this->portal->access) || $this->attestRole($this->portal->access));
		} else {
			return false;
		}
	}
	
	protected function shieldPortlets(){
		foreach($this->portal->portlet as $key => $portlet){
			if(isset($portlet->access)){
				if(!$this->attestUser($portlet->access) && !$this->attestRole($portlet->access)) {
					unset($this->portal->portlet[$key]);
				}
			}
		}
		return count($this->portal->portlet) ? true : false;
	}
	
	protected function attestUser($access){
		if(isset($access->user)){
			foreach($access->user as $user){
				if((string) $user === "everyone") return true;
				if($this->user->getLogin() === (string) $user) return true;
			}
			return false;
		} else {
			return false;
		}
	}
	
	protected function attestRole($access){
		if(isset($access->role)){
			foreach($access->role as $role){
				if((string) $role === "everyone") return true;
				if(in_array((string) $role, $this->user->getRoles())) return true;
			}
			return false;
		} else {
			return false;
		}
	}
		
}

?>