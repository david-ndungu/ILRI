<?php

namespace apps\studio;

class GridController extends \apps\Application {
		
	public function doGet(){
		try {
			$parts = explode("/", $this->getSandbox()->getMeta('URI'));
			if(count($parts) != 4) return;
			require_once("models/GridModel.php");
			$form = new GridModel($this);
			return $form->getTemplate();

		} catch (\apps\ApplicationException $e) {
			$this->onError($e);
		}
	}

	public function doPost(){
		if(!array_key_exists('command', $_POST)) return;
		require_once("models/GridModel.php");
		$form = new GridModel($this);
		switch(trim($_POST['command'])){
			case 'browse':
				header('Content-type: application/json');
				return $form->getRecords();
				break;
		}
	}
		
}