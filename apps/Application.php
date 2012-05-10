<?php

namespace apps;

require_once("$base/apps/ApplicationException.php");

class Application {
	
	protected $sandbox = NULL;

	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
	}
}

?>