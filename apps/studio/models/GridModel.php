<?php

namespace apps\studio;

use apps\ApplicationException;

class GridModel {
	
	protected $source = NULL;
	
	protected $name = NULL;
	
	protected $definition = NULL;
	
	protected $controller = NULL;
	
	protected $offset = 0;
	
	protected $limit = 250;
	
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
	
	public function getRecords(){
		if(property_exists($this->definition, "columns")){
			$storage = $this->controller->getStorage();
			$sqlFields = $this->sqlFields();
			$sqlFrom = $this->sqlFrom();
			$sqlLeftJoins = $this->sqlLeftJoins();
			$sqlLimit = $this->sqlLimit();
			$sqlCount = "SELECT COUNT(*) AS `rowCount` $sqlFrom $sqlLeftJoins $sqlLimit";
			$sqlRecords = "SELECT $sqlFields $sqlFrom $sqlLeftJoins $sqlLimit";
			$result['body'] = $storage->query($sqlRecords);
			$legend = $storage->query($sqlCount);
			$legend['rowOffset'] = $this->offset;
			$legend['rowLimit'] = $legend['rowCount'] < $this->limit ? $legend['rowCount'] : $this->limit;
			$result['footer'] = $legend;
			return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		}else{
			throw new ApplicationException("No columns defined for grid ".$this->name);
		}
	}
	
	protected function sqlFields(){
		foreach($this->definition->columns->column as $column){
			$field = (string) $column->attributes()->name;
			$columns[] = "`$field`";
		}
		return isset($columns) ? join(", ", $columns) : "*";
	}
	
	protected function sqlFrom(){
		$table = (string) $this->definition->attributes()->name;
		$table = "`$table`";
		return sprintf("FROM %s", $table);
	}
	
	protected function sqlLeftJoins(){
		foreach($this->definition->records->leftjoin as $leftjoin){
			$leftjoins[] = "LEFT JOIN $leftjoin";
		}
		return isset($leftjoins) ? join(" ", $leftjoins) : "";
	}
	
	protected function sqlLimit(){
		$parts = explode("/", $this->controller->getSandbox()->getMeta('URI'));
		$this->offset = array_key_exists('offset', $_POST) ? intval(trim($_POST['offset'])) : $this->offset;
		$this->limit = array_key_exists('limit', $_POST) ? intval(trim($_POST['limit'])) : $this->limit;
		return sprintf("LIMIT %d, %d", $this->offset, $this->limit);
	}
	
	public function getTemplate(){
		if(property_exists($this->definition, "columns")){
			foreach($this->definition->columns->column as $column){
				$gridColumns[] = $this->gridColumns($column);
				$gridContent[] = $this->gridContent($column);
			}
			if(!isset($gridColumns)) throw new ApplicationException("Could not create grid header columns");
			if(!isset($gridContent)) throw new ApplicationException("Could not create grid content template");
			return $this->buildHTML($gridColumns, $gridContent);				
		} else {
			throw new ApplicationException("No columns defined for grid ".$this->name);
		}
	}
	
	protected function buildHTML($gridColumns, $gridContent){
		$html[] = "<div class=\"grid\">";
		$html[] = "\t<div class=\"gridHeader gradientSilver\">";
		$html[] = $this->gridHeaderTitle();
		$html[] = $this->gridHeaderSearch();
		$html[] = $this->gridHeaderAdd();
		$html[] = "\t</div>"; //gridHeader
		$html[] = "\t<div class=\"gridColumns gradientSilver\">";
		$html[] = join("\n", $gridColumns);
		$html[] = "\t</div>"; //gridHeaderColumns
		$html[] = "\t<div class=\"gridContent\">";
		$html[] = "\t\t<div class=\"gridContentRecord\">";
		$html[] = join("\n", $gridContent);
		$html[] = "\t\t</div>";
		$html[] = "\t</div>"; //gridContent
		$html[] = "\t<div class=\"gridFooter gradientSilver\">";
		$html[] = $this->gridFooter();
		$html[] = "\t</div>";
		$html[] = "</div>"; //grid
		return join("\n", $html);
	}
	
	protected function gridHeaderTitle(){
		$html[] = "\t\t<div class=\"gridHeaderTitle column grid4of10\">";
		$title = $this->titleTranslation($this->definition);
		$html[] = "\t\t\t<h2>$title</h2>";
		$html[] = "\t\t</div>";
		return join("\n", $html);
	}
	
	protected function gridHeaderSearch(){
		$URI = $this->controller->getSandbox()->getMeta('URI');
		$html[] = "\t\t<div class=\"gridHeaderSearch column grid5of10 headerForm\">";
		$html[] = "\t\t\t<form action=\"$URI\" method=\"POST\">";
		$searchText = $this->controller->translate('action.search');
		$html[] = "\t\t\t\t<input type=\"text\" name=\"keywords\" placeholder=\"$searchText\"/>";
		$html[] = "\t\t\t\t<input type=\"submit\" value=\"&nbsp;\" class=\"searchButton button\" />";
		$html[] = "\t\t\t</form>";
		$html[] = "\t\t</div>";
		return join("\n", $html);
	}
	
	protected function gridHeaderAdd(){
		$html[] = "\t\t<div class=\"gridHeaderAdd column grid1of10 headerForm\">";
		$addText = $this->controller->translate('action.add');
		$html[] = "\t\t\t<input type=\"button\" name=\"addButton\" value=\"$addText\" class=\"addButton\" />";
		$html[] = "\t\t</div>";
		return join("\n", $html);
	}
	
	protected function gridColumns ($column) {
		$class = $this->getAttribute("class", $column);
		$gridColumns[] = "\t\t<div$class>";
		$gridColumns[] = "\t\t\t".$this->titleTranslation($column);
		$gridColumns[] = "\t\t\t<span class=\"sort-icon\"></span>";
		$gridColumns[] = "\t\t</div>";
		return join("\n", $gridColumns);
	}
	
	protected function gridContent($column) {
		$name = (string) $column->attributes()->name;
		$class = $this->getAttribute("class", $column);
		$title = $this->getAttribute("title", $column);
		$gridContent[] = "\t\t\t<div$class$title>{{".$name."}}</div>";		
		return join("\n", $gridContent);
	}
	
	protected function gridFooter(){
		$translator = $this->controller->getSandbox()->getService("translation");
		$html[] = "\t\t<div class=\"column grid10of10\">";
		$html[] = "\t\t\t<span>".$translator->translate("pagination.legend")."</span>";
		$html[] = "\t\t\t<ul>";
		$html[] = "\t\t\t\t<li><a class=\"button first\">".$translator->translate("pagination.first")."</a></li>";
		$html[] = "\t\t\t\t<li><a class=\"button previous\">".$translator->translate("pagination.previous")."</a></li>";
		$html[] = "\t\t\t\t<li><a class=\"button next\">".$translator->translate("pagination.next")."</a></li>";
		$html[] = "\t\t\t\t<li><a class=\"button last\">".$translator->translate("pagination.last")."</a></li>";
		$html[] = "\t\t\t</ul>";
		$html[] = "\t\t</div>";
		return join("\n", $html);
	}
	
	protected function setURIName(){
		$parts = explode("/", $this->controller->getSandbox()->getMeta('URI'));
		if(count($parts) < 3){
			return NULL;
		} else {
			$this->name = $parts[3];
			return $this->name;
		}
	}
	
	public function setSource($name){
		$this->name = $name;
		$this->source = $this->controller->getSandbox()->getMeta('base')."/apps/studio/grids/".$this->name.".xml";
		if(is_readable($this->source)) {
			$this->definition = simplexml_load_file($this->source);
		} else {
			throw new ApplicationException($this->source." is not readable or does not exist");
		}
	}	
	
	protected function titleTranslation($node){
		$attributes = $node->attributes();
		if(property_exists($attributes, "title")){
			$title = (string) $attributes->title;
			return $this->controller->getSandbox()->getService("translation")->translate($title);
		} else {
			return "";
		}
	}
	
	protected function getAttribute($attribute, $node){
		$attributes = $node->attributes();
		if(property_exists($attributes, $attribute)){
			$value = (string) $attributes->$attribute;
			return " $attribute=\"$value\"";
		} else {
			return "";
		}
	}

	
}

?>