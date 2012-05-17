<?php

namespace apps\studio;

class SignUpController extends \apps\Application {
	
	public function doGet(){
		try {
			require_once("models/FormModel.php");
			$form = new FormModel($this);
			$form->setSource("signup");
			$body = $form->asHTML();
			$title = $this->sandbox->getService('translation')->translate('signup');
			return array("title" => $title, "body" => $body);
		} catch (\apps\ApplicationException $e) {
			$this->onError($e);
		}
	}
	
	public function doPost(){
		
	}
	
}