<?php

namespace apps\studio;

class formController extends \apps\Application{
		
	public function doGet(){
		$parts = explode("/", $this->sandbox->getMeta('URI'));
		$request = $parts[(count($parts)-1)];
		$source = $this->sandbox->getMeta('base')."/apps/studio/forms/$request.xml";
		require_once("formBuilder.php");
		$builder = new formBuilder($this->sandbox);
		try {
			$builder->setDefinition($source);
			return $builder->toHTML();
		} catch (\apps\ApplicationException $e) {
			error_log($e->getTraceAsString());
			\base\Response::sendHeader(400);
		}
	}

	public function doPost(){
	
	}
		
}

?>