<?php

namespace apps\studio;

use apps\ApplicationException;

class FormModel {
	
	protected $source = NULL;
	
	protected $definition = NULL;
	
	protected $name = NULL;
	
	protected $controller = NULL;
	
	protected $action = NULL;
	
	public function __construct(&$controller) {
		$this->controller = &$controller;
		if($this->autoSetup()) {
			try {
				$this->setSource($this->name);
			} catch (ApplicationException $e) {
				throw new ApplicationException($e->getMessage());
			}
		}
	}
	
	protected function autoSetup(){
		$parts = explode("/", $this->controller->getSandbox()->getMeta('URI'));
		if(count($parts) < 3){
			return false;
		} else {
			$this->name = $parts[(count($parts)-1)];
			$this->setAction($this->controller->getSandbox()->getMeta('URI'));
			return true;
		}
	}
	
	public function setSource($name){
		$this->name = $name;
		$this->source = $this->controller->getSandbox()->getMeta('base')."/apps/studio/forms/".$this->name.".xml";
		if(is_readable($this->source)) {
			$this->definition = simplexml_load_file($this->source);
		} else {
			throw new ApplicationException($this->source." is not readable or does not exist");
		}
	}
	
	public function setAction($action){
		$this->action = $action;
	}
				
	public function asHTML(){
		$html[] = "\r";
		$action = is_null($this->action) ? $this->controller->getSandbox()->getMeta('URI') : $this->action;
		$h2 = $this->controller->translate((string) $this->definition->attributes()->title);
		$html[] = '<h2 class="primaryHead gradientSilver"><span class="column grid10of10">'.$h2.'</span></h2>';
		$html[] = '<form name="'.(string) $this->definition->attributes()->name.'" action="'.$this->action.'" method="POST" class="primaryContent">';
		if(property_exists($this->definition, "fieldset")){
			foreach($this->definition as $fieldset){
				$html[] = "\t".'<fieldset name="'.(string) $fieldset->attributes()->name.'">';
				$legend = (string) $fieldset->attributes()->legend;
				$legend = $this->controller->getSandbox()->getService("translation")->translate($legend);
				$html[] = "\t\t<legend>$legend</legend>";
				foreach($fieldset->field as $field){
					$html[] = $this->createField($field);
				}
				$html[] = "\t"."</fieldset>";
			}
		} else if(property_exists($this->definition, "field")){
			foreach($this->definition as $field){
				$html[] = "\t\t\t".$this->createField($field);
			}
		} else {
			throw new ApplicationException("No fields defined for form".$this->name);
		}
		$html[] = "</form>";
		$html[] = "\r";
		return join("\n", $html);
	}
		
	protected function createField($field){
		$labeled = array('text', 'password', 'select', 'checkbox', 'radio', 'textarea', 'submit', 'reset');
		$elements = array();
		$label = false;
		foreach($field->element as $element){
			$type = (string) $element->attributes()->type;
			$elements[] = $this->createElement($field, $element, $type);
			if(in_array($type, $labeled)) {
				$label = true;
			}
		}
		if($label){
			$html[] = "\t\t<label name=\"".(string) $field->attributes()->name."\">";
			$html[] = "\t\t\t<span>".$this->controller->translate((string) $element->attributes()->label)."</span>";
			$html[] = join("\n", $elements);
			$html[] = "\t\t</label>";
		} else {
			$html = $elements;
		}
		return isset($html) ? join("\n", $html) : NULL;
	}
	
	protected function createElement($field, $element, $type){
		switch($type){
			case "text":
				return "\t\t\t".$this->createInputText($field, $element);
				break;
			case "password":
				return "\t\t\t".$this->createInputPassword($field, $element);
				break;
			case "radio":
				return "\t\t\t".$this->createInputRadio($field, $element);
				break;
			case "checkbox":
				return "\t\t\t".$this->createInputCheckbox($field, $element);
				break;
			case "hidden":
				return "\t\t\t".$this->createInputHidden($field, $element);
				break;
			case "submit":
				return "\t\t\t".$this->createInputSubmit($field, $element);
				break;
			case "reset":
				return "\t\t\t".$this->createInputReset($field, $element);
				break;
			case "select":
				return "\t\t\t".$this->createSelect($field, $element);
				break;
			case "textarea":
				return "\t\t\t".$this->createTextarea($field, $element);
				break;
		}
	}
	
	protected function createInputText($field, $element){
		return '<input type="text" name="'.(string) $field->attributes()->name.'" value="'.$this->elementValue($element).'"'.$this->maxLength($field).''.$this->placeHolder($element).$this->elementClass($element).'/>';
	}

	protected function createInputPassword($field, $element){
		return '<input type="password" name="'.(string) $field->attributes()->name.'" value=""'.$this->maxLength($field).'/>';
	}
	
	protected function createInputRadio($field, $element){
		return '<input type="radio" name="'.(string) $field->attributes()->name.'" value="'.$this->elementValue($element).'"/>';
	}
	
	protected function createInputCheckbox($field, $element){
		return '<input type="checkbox" name="'.(string) $field->attributes()->name.'" value="'.$this->elementValue($element).'"/>';
	}
	
	protected function createInputHidden($field, $element){
		return '<input type="hidden" name="'.(string) $field->attributes()->name.'" value="'.$this->elementValue($element).'"/>';
	}

	protected function createInputSubmit($field, $element){
		$translator = $this->controller->getSandbox()->getService("translation");
		return '<input type="submit" name="'.(string) $field->attributes()->name.'" value="'.$translator->translate((string) $element->attributes()->value).'"/>';
	}
	
	protected function createInputReset($field, $element){
		$translator = $this->controller->getSandbox()->getService("translation");
		return '<input type="reset" name="'.(string) $field->attributes()->name.'" value="'.$translator->translate((string) $element->attributes()->value).'"/>';
	}
	
	protected function createSelect($field, $element){
		$translator = $this->controller->getSandbox()->getService("translation");
		$placeholder = (string) $element->attributes()->placeholder;
		$html[] = '<select name="'.(string) $field->attributes()->name.'">';
		$html[] = "\t\t\t\t".'<option value="0">'.$translator->translate($placeholder).'</option>';
		$table = (string) $element->attributes()->lookup;
		if(strlen($table)){
			$value = (string) $element->attributes()->value;
			$display = (string) $element->attributes()->display;
			$options = $this->controller->getStorage()->select(array("table" => $table));
			foreach($options as $option){
				$html[] = "\t\t\t\t".'<option value="'.$option[$value].'">'.$option[$display].'</option>';
			}
		}
		$html[] = "\t\t\t</select>";
		return join("\n", $html);
	}
	
	protected function createTextarea($field, $element){
		$name = (string) $field->attributes()->name;
		return '<textarea name="' . $name . '"></textarea>';
	}
	
	protected function elementValue($element){
		$attributes = $element->attributes();
		if(property_exists($attributes, "value")){
			return (string) $attributes->value;
		} else {
			return '{{'.(string) $attributes->name.'}}';
		}
	}
	
	protected function elementClass($element){
		if(property_exists($element, 'class')){
			foreach($element->class as $class){
				$classes[] = (string) $class;
			}
			return " class=\"".join("", $classes)."\"";
		} else {
			return "";
		}
	}
	
	protected function maxLength($field){
		$attributes = $field->attributes();
		if(property_exists($attributes, "length")){
			$length = (string) $attributes->length;
			return " maxlength=\"$length\"";
		} else {
			return "";
		}
	}
	
	protected function placeHolder($element){
		$attributes = $element->attributes();
		if(property_exists($attributes, "placeholder")){
			$placeholder = (string) $attributes->placeholder;
			$placeholder = $this->controller->getSandbox()->getService("translation")->translate($placeholder);
			return strlen($placeholder) ? " placeholder=\"$placeholder\"" : "";
		} else {
			return "";
		}
	}
	
	public function createRecord(){
		$record['table'] = (string) $this->definition->attributes()->name;		
		$fields = $this->getFields();
		if(is_null($fields)) {
			throw new \apps\ApplicationException('Fields not defined for '.$this->name);
		}
		foreach($fields as $field){
			$type = (string) $field->attributes()->type;
			if(strlen($type)) {
				$key = (string) $field->attributes()->name;
				$value = $this->postVariable($key);
				$record['content'][$key] = $value;
			}
		}
		try {
			$this->controller->getStorage()->insert($record);
			$insertID = $this->controller->getStorage()->getInsertID();
			return json_encode(array("success", $insertID), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		}catch(\base\BaseException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	public function selectRecord(){
		if(!array_key_exists('primarykey', $_POST)) return;
		$storage = $this->controller->getStorage();
		$value = $storage->sanitize($_POST['primarykey']);
		$key = (string) $this->definition->attributes()->primarykey;
		$table = (string) $this->definition->attributes()->name;
		$columns = $this->getColumns();
		$sql = sprintf("SELECT %s FROM `%s` WHERE `%s` = %d", join(", ", $columns), $table, $key, $value);
		$rows = $storage->query($sql);
		return json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}
	
	public function updateRecord(){
		if(!array_key_exists('primarykey', $_POST)) return;
		$storage = $this->controller->getStorage();
		$columns = $this->getColumns();
		$key = (string) $this->definition->attributes()->primarykey;
		$update['table'] = (string) $this->definition->attributes()->name;
		$update['constraints'][$key] = $storage->sanitize($_POST['primarykey']);
		foreach ($columns as $column) {
			$update['content'][$column] = $this->postVariable($column);
		}
		$result['success'] = $storage->update($update);
		return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}
	
	protected function getColumns(){
		$fields = $this->getFields();
		foreach($fields as $field){
			$type = (string) $field->attributes()->type;
			if(strlen($type)){
				$columns[] = (string) $field->attributes()->name;
			}
		}
		return $columns;
	}
	
	protected function getFields(){
		if(property_exists($this->definition, "fieldset")){
			foreach($this->definition->fieldset as $fieldset){
				foreach($fieldset->field as $field){
					$fields[] = $field;
				}
			}
			return $fields;
		} else if (property_exists($this->definition, "field")) {
			foreach ($this->definition->field as $field) {
				$fields[] = $field;
			}
			return $fields;
		} else {
			throw new \apps\ApplicationException("No fields defined for form : ".$this->name);
		}
	}
	
	protected function postVariable($key){
		return array_key_exists($key, $_POST) ? $_POST[$key] : NULL;
	}

}

?>