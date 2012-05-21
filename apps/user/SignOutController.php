<?php

namespace apps\user;

class SignOutController extends \apps\Application {
	
	public function doGet(){
		try{
			$user = $this->initUserModel();
			$user->signOut();
			$result = $user->getForm("signin", "/signin");
			$result['message'][] = $this->translate("user.sign.out");
			return $result;
		}catch(\apps\ApplicationException $e){
			$this->onError($e);
		}
	}
	
	protected function initUserModel(){
		$base = $this->sandbox->getMeta('base');
		require_once("models/UserModel.php");
		return new UserModel($this);
	}
		
}