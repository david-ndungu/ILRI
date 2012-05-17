<?php

namespace apps\studio;

class SignInController extends \apps\Application {
	
	public function doGet(){
		try {
			require_once("models/FormModel.php");
			$form = new FormModel($this);
			$form->setSource("signin");
			$title = $this->sandbox->getService('translation')->translate('signin');
			return array("title" => $title, "body" => $form->asHTML());
		} catch (\apps\ApplicationException $e) {
			$this->onError($e);
		}
	}
	
	public function doPost(){
		
	}
	
}