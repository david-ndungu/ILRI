<?php

namespace apps\studio;

class FormController extends \apps\Application {
		
	public function doGet(){
		try {
			require_once("models/FormModel.php");
			$form = new FormModel($this);
			return $form->asHTML();
		} catch (\apps\ApplicationException $e) {
			$this->onError($e);
		}
	}

	public function doPost(){
	
	}
		
}

?>