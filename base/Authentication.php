<?php

namespace base;

class Authentication {
	
	protected $sandbox = NULL;
	
	protected $package = NULL;
	
	protected $user = NULL;
	
	protected $URI = NULL;
	
	protected $portal = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$this->sandbox->listen('routing.passed', 'init', $this);
	}
	
	public function init($data = NULL){
		$this->portal = &$data['portal'];
		require_once("$this->base/base/User.php");
		$this->user = new User(&$sandbox);
		if(!$this->shieldPortal()) {
			$this->sandbox->fire('authentication.failed', $data);
			return;
		}
		if($this->shieldPortlets()){
			$this->sandbox->fire('authentication.passed', $data);
		} else {
			$this->sandbox->fire('authentication.failed', $data);
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
		$success = false;
		foreach($this->portal->portlet as $portlet){
			if(isset($portlet->access)){
				if($this->attestUser($portlet->access) || $this->attestRole($portlet->access)) {
					$success = true;
					$data['portlet'] = &$portlet;
					$data['user'] = $this->user;
					$this->sandbox->fire('portlet.authentication.passed', $data);
				}
			}
		}
		return $success;
	}
	
	protected function attestUser($access){
		if(isset($access->user)){
			foreach($access->user as $user){
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
				if(in_array((string) $role, $this->user->getRoles())) return true;
			}
			return false;
		} else {
			return false;
		}
	}
		
}

?>