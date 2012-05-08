<?php

namespace base;

class User {
	
	protected $ID = NULL;
	
	protected $guest = NULL;
	
	protected $login = NULL;
	
	protected $creationTime = NULL;
	
	protected $roles = NULL;
	
	protected $sandbox = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
		$user = $this->sandbox->getSession()->read('user');
		if(is_null($user)){
			$user = $this->createGuest();
		}
		$this->update($user);
	}
	
	protected function createGuest() {
		$user = array(
					'IP' => $_SERVER['REMOTE_ADDR'],
					'hash' => $this->sandbox->getSession()->getHash(),
					'creationTime' => time()
				);
		
	}
	
	protected function update($user){
		$this->setID($user['ID']);
		$this->setLogin($user['login']);
		$this->setRoles($user['roles']);
		$this->setCreationTime($user['creationTime']);
	}
	
	public function setID() {
		$this->ID = $ID;
	}

	public function getID() {
		return $this->ID;
	}
	
	public function isGuest($guest = NULL) {
		if(is_null($guest)){
			return $this->guest;
		} else {
			if(is_bool($guest)) {
				$this->guest = $guest;
			}
		}
	}
	
	public function setLogin() {
		$this->login = $login;
	}
	
	public function getLogin() {
		return $this->login;
	}
	
	public function setCreationTime() {
		$this->creationTime = $creationTime;
	}

	public function getCreationTime() {
		return $this->creationTime;
	}
	
	public function setRoles($roles){
		$this->roles = $roles;
	}
	
	public function setRole($role){
		$this->roles[] = $role;
	}
	
	public function getRoles(){
		return $this->roles;
	}
	
	public function getUser(){
		$user = array(
					'ID' => $this->getID(),
					'login' => $this->getLogin(),
					'roles' => $this->getRoles(),
					'creationTime' => $this->getCreationTime(),
					'isGuest' => $this->isGuest()
				);
		return $user;
	}
	
}
?>