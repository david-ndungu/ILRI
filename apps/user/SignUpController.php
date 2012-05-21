<?php

namespace apps\user;

class SignUpController extends \apps\Application {
	
	public function doGet(){
		try {
			$user = $this->initUserModel();
			$form = $user->getForm("signup");
			return $form;
		} catch (\apps\ApplicationException $e) {
			$this->onError($e);
		}
	}
	
	public function doPost(){
		try {
			$user = $this->initUserModel();
			$user->validateSignUp();
			$user->signUp();
			header("Location: /");
			exit;
		}catch(\apps\ApplicationException $e){
			echo $e->getMessage();exit;
			$result = $user->getForm("signup");
			$result['error'][] = $e->getMessage();
			return $result;
		}
	}
			
	protected function initUserModel(){
		$base = $this->sandbox->getMeta('base');
		require_once("models/UserModel.php");
		return new UserModel($this);
	}
	
}