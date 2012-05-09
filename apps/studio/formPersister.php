<?php

namespace apps\studio;

class formPersister {

	protected $definition = NULL;
	
	public function __construct(&$definition) {
		$this->definition = $definition;
	}
	
}

?>