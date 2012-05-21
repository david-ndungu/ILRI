<?php

namespace apps\user;

class ChangePasswordController extends \apps\Application {
	
	public function doGet(){
		try {
			$user = $this->initUserModel();
			$form = $user->getForm("changepassword");
			return $form;
		} catch (\apps\ApplicationException $e) {
			$this->onError($e);
		}
	}
	
	public function doPost(){
		try {
			$user = $this->initUserModel();
			$user->validateChangePassword();
			$user->changePassword();
			$result = $user->getForm("signin", "/signin");
			$result['message'][] = $this->translate("password.changed");
			return $result;
		}catch (\apps\ApplicationException $e) {
			$result = $user->getForm("changepassword");
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