<?php

namespace apps\studio;

class UserModel extends \base\User {
	
	public function createUser(){
		$user = array(
				"login" => $this->getLogin(),
				"email" => $this->getEmail(),
				"password" => $this->getPassword(),
				);
	}
	
	protected function isValidUser(){
		
	}
	
}
?>