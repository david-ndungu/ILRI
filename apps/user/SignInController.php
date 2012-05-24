<?php

namespace apps\user;

class SignInController extends \apps\Application {
	
	public function doGet(){
		try {
			$user = $this->initUserModel();
			$form = $user->getForm("signin");
			return $form;
		} catch (\apps\ApplicationException $e) {
			$this->onError($e);
		}
	}
	
	public function doPost(){
		try{
			$user = $this->initUserModel();
			$result = $user->validateSignIn();
			$user->ownGuest($result);
			$user->isGuest(false);
			$user->signIn($result);
			header("Location: /");
			exit;
		}catch(\apps\ApplicationException $e){
			$result = $user->getForm("signin");
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