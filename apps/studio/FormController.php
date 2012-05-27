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
		if(!array_key_exists('command', $_POST)) return;
		try {
			require_once("models/FormModel.php");
			$form = new FormModel($this);
			switch(trim($_POST['command'])){
				case "insert":
					return $form->createRecord();
					break;
				case "update":
					return $form->updateRecord();
					break;
				case "select":
					return $form->selectRecord();
					break;
			}
			
		}catch(\apps\ApplicationException $e){
			$this->onError($e);
		}
	}
		
}

?>