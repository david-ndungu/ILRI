<?php

namespace apps\studio;

class GridTemplateController extends \apps\Application {
		
	public function doGet(){
		try {
			require_once("models/GridModel.php");
			$form = new GridModel($this);
			return $form->asHTML();
		} catch (\apps\ApplicationException $e) {
			$this->onError($e);
		}
	}

	public function doPost(){
	
	}
		
}