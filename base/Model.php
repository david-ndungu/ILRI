<?php

namespace base;

class Model {
	
	private $storage = NULL;
	
	public function __construct(Storage &$storage) {
		$this->storage = &$storage;
	}
	
	public function insert($table){
		
	}
	
}

?>