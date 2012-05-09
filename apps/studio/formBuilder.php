<?php

namespace apps\studio;

class formBuilder {

	protected $definition = NULL;
	
	public function __construct(&$definition) {
		$this->definition = $definition;
	}
	
}

?>