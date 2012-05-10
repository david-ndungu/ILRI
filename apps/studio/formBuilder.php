<?php

namespace apps\studio;

use apps\ApplicationException;

class formBuilder {

	protected $source = NULL;
	
	protected $definition = NULL;
	
	protected $form = NULL;
	
	protected $sandbox = NULL;
	
	public function __construct(&$sandbox) {
		$this->sandbox = &$sandbox;
	}
	
	public function setDefinition(&$source){
		$this->source = $source;
		if(!is_readable($this->source)){
			throw new ApplicationException("'$this->source' form definition is not readable");
		} else {
			$this->definition = simplexml_load_file($this->source);
		}
	}
	
	public function toHTML(){
		if(property_exists($this->definition, "fieldset")){
			return $this->createFieldsets();
		} else if(property_exists($this->definition, "fields")){
			return $this->createForm();
		} else {
			throw new ApplicationException("No fields defined in form");
		}
	}
	
	protected function createForm(){
		
	}
	
	protected function createFieldsets(){
		foreach($this->definition as $fieldset){
			$html[] = '<fieldset name="'.(string) $fieldset->attributes()->name.'">';
			foreach($fieldset->field as $field){
				$html[] = "\t".$this->createField($field);
			}
			$html[] = "</fieldset>";
		}
		return join("\n", $html);
	}
	
	protected function createField($field){
		foreach($field->element as $element){
			$type = $element->attributes()->type;
			$html[] = '<label>';
			switch($type){
				case "text":
					$html[] = "\t".$this->createInputText($field, $element);
					break;
				case "radio":
					$html[] = "\t".$this->createInputRadio($field, $element);
					break;
				case "checkbox":
					$html[] = "\t".$this->createInputCheckbox($field, $element);
					break;
				case "hidden":
					$html[] = $this->createInputHidden($field, $element);
					break;							
				case "submit":
					$html[] = "\t".$this->createInputSubmit($field, $element);
					break;							
				case "reset":
					$html[] = "\t".$this->createInputReset($field, $element);
					break;							
				case "select":
					$html[] = "\t".$this->createSelect($field, $element);
					break;							
				case "textarea":
					$html[] = "\t".$this->createTextarea($field, $element);
					break;					
			}
			$html[] = '</label>';
		}
		return isset($html) ? join("\n\t", $html) : NULL;
	}
	
	protected function createInputText($field, $element){
		return '<input type="text" name="'.(string) $field->attributes()->name.'" value="{{'.(string) $element->attributes()->name.'}}"/>';
	}

	protected function createInputRadio($field, $element){
		return '<input type="radio" name="'.(string) $field->attributes()->name.'" value="'.(string) $element->attributes()->name.'"/>';
	}
	
	protected function createInputCheckbox($field, $element){
		return '<input type="checkbox" name="'.(string) $field->attributes()->name.'" value="'.(string) $element->attributes()->name.'"/>';
	}
	
	protected function createInputHidden($field, $element){
		return '<input type="hidden" name="'.(string) $field->attributes()->name.'" value="{{'.(string) $element->attributes()->name.'}}"/>';
	}

	protected function createInputSubmit($field, $element){
		$translator = $this->sandbox->getService("translation");
		return '<input type="submit" name="'.(string) $field->attributes()->name.'" value="'.$translator->translate((string) $element->attributes()->value).'"/>';
	}
	
	protected function createInputReset($field, $element){
		$translator = $this->sandbox->getService("translation");
		return '<input type="reset" name="'.(string) $field->attributes()->name.'" value="'.$translator->translate((string) $element->attributes()->value).'"/>';
	}
	
	protected function createSelect($field, $element){
		$html[] = '<select name="'.(string) $field->attributes()->name.'">';
		$html[] = "\t".'<option value="KEY">TEXT</option>';
		$html[] = '</select>';
		return join("\n\t\t", $html);
	}
	
	protected function createTextarea($field, $element){
		$name = (string) $field->attributes()->name;
		return '<textarea name="' . $name . '"></textarea>';
	}
	
}

?>