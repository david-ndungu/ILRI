<?php

namespace apps\user;

class ResetPasswordController extends \apps\Application {
	
	public function doGet(){
		try {
			$base = $this->sandbox->getMeta('base');
			require_once("$base/apps/studio/models/FormModel.php");
			$form = new \apps\studio\FormModel($this);
			$form->setSource("resetpassword");
			$title = $this->sandbox->getService('translation')->translate('resetpassword');
			return array("title" => $title, "body" => $form->asHTML());
		} catch (\apps\ApplicationException $e) {
			$this->onError($e);
		}
	}
	
	public function doPost(){
		
	}
	
}