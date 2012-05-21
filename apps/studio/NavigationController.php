<?php

namespace apps\studio;

class NavigationController extends \apps\Application {
	
	public function doGet(){
		require_once("models/NavigationModel.php");
		$model = new NavigationModel($this);
		return $model->getMenu();
	}
	
}