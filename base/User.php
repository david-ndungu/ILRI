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
		$user = $this->sandbox->getService('session')->read('user');
		if(is_null($user)){
			$user = $this->createGuest();
		}
		$this->update($user);
		$this->sandbox->getService('session')->write('user', $this->getUser());
	}
	
	protected function createGuest() {
		$user = array(
					'IP' => $_SERVER['REMOTE_ADDR'],
					'hash' => $this->sandbox->getService('session')->getHash(),
					'creationTime' => time()
				);
		$this->sandbox->getService('storage')->insert(array('table' => 'guest', 'content' => $user));
		$user['ID'] = $this->sandbox->getService('storage')->getInsertID();
		$user['login'] = 'guest-'.$user['ID'];
		$user['roles'] = NULL;
		$user['isGuest'] = "Yes";
		return $user;
	}
	
	protected function update($user){
		$this->setID($user['ID']);
		$this->setLogin($user['login']);
		$this->setRoles($user['roles']);
		$this->setCreationTime($user['creationTime']);
		$this->isGuest($user['isGuest'] === "Yes" ? true : false);
	}
	
	public function setID($ID) {
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
	
	public function setLogin($login) {
		$this->login = $login;
	}
	
	public function getLogin() {
		return $this->login;
	}
	
	public function setCreationTime($creationTime) {
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
					'isGuest' => ($this->isGuest() ? 'Yes' : 'No')
				);
		return $user;
	}
	
}
?>