<?php

namespace apps\studio;

use apps\ApplicationException;

class FormModel {
	
	protected $source = NULL;
	
	protected $definition = NULL;
	
	protected $name = NULL;
	
	protected $controller = NULL;
	
	public function __construct(&$controller) {
		$this->controller = &$controller;
		if(is_null($this->setURIName())) {
			return;
		} else {
			try {
				$this->setSource($this->name);
			} catch (ApplicationException $e) {
				throw new ApplicationException($e->getMessage());
			}
		}
	}
	
	protected function setURIName(){
		$parts = explode("/", $this->controller->getSandbox()->getMeta('URI'));
		if(count($parts) < 3){
			return NULL;
		} else {
			$this->name = $parts[(count($parts)-1)];
			return $this->name;
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
				
	public function asHTML(){
		$html[] = "\r";
		$html[] = '<form name="'.(string) $this->definition->attributes()->name.'" action="'.$this->controller->getSandbox()->getMeta('URI').'" method="POST">';
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
		$html[] = "\t\t<label name=\"".(string) $field->attributes()->name."\">";
		foreach($field->element as $element){
			$type = $element->attributes()->type;
			$label = (string) $element->attributes()->label;
			$label = $this->controller->getSandbox()->getService("translation")->translate($label);
			$html[] = "\t\t\t<span>$label</span>";
			switch($type){
				case "text":
					$html[] = "\t\t\t".$this->createInputText($field, $element);
					break;
				case "password":
					$html[] = "\t\t\t".$this->createInputPassword($field, $element);
					break;
				case "radio":
					$html[] = "\t\t\t".$this->createInputRadio($field, $element);
					break;
				case "checkbox":
					$html[] = "\t\t\t".$this->createInputCheckbox($field, $element);
					break;
				case "hidden":
					$html[] = "\t\t\t".$this->createInputHidden($field, $element);
					break;							
				case "submit":
					$html[] = "\t\t\t".$this->createInputSubmit($field, $element);
					break;							
				case "reset":
					$html[] = "\t\t\t".$this->createInputReset($field, $element);
					break;							
				case "select":
					$html[] = "\t\t\t".$this->createSelect($field, $element);
					break;							
				case "textarea":
					$html[] = "\t\t\t".$this->createTextarea($field, $element);
					break;					
			}
		}
		$html[] = "\t\t</label>";
		return isset($html) ? join("\n", $html) : NULL;
	}
	
	protected function createInputText($field, $element){
		return '<input type="text" name="'.(string) $field->attributes()->name.'" value="'.$this->elementValue($element).'"'.$this->maxLength($field).''.$this->placeHolder($element).'/>';
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
		return '<input type="hidden" name="'.(string) $field->attributes()->name.'" value="{{'.$this->elementValue($element).'}}"/>';
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
			return " placeholder=\"$placeholder\"";
		} else {
			return "";
		}
	}
}

?>