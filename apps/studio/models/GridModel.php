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
	
	protected $ordercolumn = NULL;
	
	protected $orderdirection = NULL;
	
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
	
	public function browseRecords(){
		if(property_exists($this->definition, "columns")){
			
			$sqlParts = $this->buildSQL();
			$storage = $this->controller->getStorage();

			$countSQL = sprintf("SELECT COUNT(*) AS `rowCount` %s %s", $sqlParts['From'], $sqlParts['LeftJoins']);
			$countRows = $storage->query($countSQL);
			
			$legend = $countRows[0];
			$legend['rowOffset'] = $this->offset;
			$legend['rowLimit'] = $legend['rowCount'] < $this->limit ? $legend['rowCount'] : $this->limit;
			$result['footer'] = $legend;
			
			$recordSQL = sprintf("SELECT %s %s %s %s %s", $sqlParts['Fields'], $sqlParts['From'], $sqlParts['LeftJoins'], $sqlParts['OrderBy'], $sqlParts['Limit']);
			$recordRows = $storage->query($recordSQL);
				
			$result['ordercolumn'] = $this->ordercolumn;
			$result['orderdirection'] = $this->orderdirection;
			$result['primarykey'] = (string) $this->definition->columns->attributes()->primarykey;
			$result['body'] = $recordRows;
			
			return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		}else{
			throw new ApplicationException("No columns defined for grid ".$this->name);
		}
	}
	
	public function searchRecords(){
		if(!array_key_exists('keywords', $_POST)) {
			throw new \apps\ApplicationException("No search keywords specified");
		}
		$parameters = intval($this->definition->records->search->attributes()->parameters);
		$sqlWhere = $this->sqlWhere();
		$sqlParts = $this->buildSQL();
		$storage = $this->controller->getStorage(); 
		try {
			$keywords = $storage->sanitize($_POST['keywords']);
			$searchArgs[] = sprintf("SELECT %s %s %s WHERE %s", $sqlParts['Fields'], $sqlParts['From'], $sqlParts['LeftJoins'], $sqlWhere);
			$countArgs[] = sprintf("SELECT COUNT(*) AS `rowCount` %s %s WHERE %s", $sqlParts['From'], $sqlParts['LeftJoins'], $sqlWhere);
			while($parameters--){
				$searchArgs[] = "%$keywords%";
				$countArgs[] = "%$keywords%";
			}
			$countSQL = call_user_func_array('sprintf', $countArgs);
			$countRows = $storage->query($countSQL);
			$legend = $countRows[0];
			$legend['rowOffset'] = $this->offset;
			$legend['rowLimit'] = $legend['rowCount'] < $this->limit ? $legend['rowCount'] : $this->limit;
			$result['footer'] = $legend;
				
			$searchSQL = call_user_func_array('sprintf', $searchArgs);
			$searchArgs[0] .= sprintf(" %s %s", $sqlParts['OrderBy'], $sqlParts['Limit']);

			$searchRows = $storage->query($searchSQL);
			
			$result['ordercolumn'] = $this->ordercolumn;
			$result['orderdirection'] = $this->orderdirection;
			$result['primarykey'] = (string) $this->definition->columns->attributes()->primarykey;
			$result['body'] = $searchRows;
				
			return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		
		} catch (\base\BaseException $e) {
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	protected function sqlWhere(){
		if(property_exists($this->definition->records, 'search')){
			return (string) $this->definition->records->search;
		}else{
			return NULL;
		}
	}
	
	protected function buildSQL(){
		$sql['Fields'] = $this->sqlFields();
		$sql['From'] = $this->sqlFrom();
		$sql['LeftJoins'] = $this->sqlLeftJoins();
		$sql['OrderBy'] = $this->sqlOrderBy();
		$sql['Limit'] = $this->sqlLimit();
		return $sql;
	}
		
	protected function sqlFields(){
		$primarykey = (string) $this->definition->columns->attributes()->primarykey;
		$columns[] = "`$primarykey` AS `primarykey`";
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
			$join = (string) $leftjoin;
			if(strlen($join)){
				$leftjoins[] = "LEFT JOIN $join";
			}
		}
		return isset($leftjoins) ? join(" ", $leftjoins) : "";
	}
	
	protected function sqlOrderBy(){
		$this->ordercolumn = (string) $this->definition->records->ordercolumn;
		$this->orderdirection = (string) $this->definition->records->orderdirection;
		$direction = array_key_exists('orderdirection', $_POST) ? trim(strtoupper($_POST['orderdirection'])) : NULL;
		$this->orderdirection = in_array($direction, array('DESC', 'ASC')) ? $direction : $this->orderdirection;
		$columns = $this->getTableColumns();
		$column = array_key_exists('ordercolumn', $_POST) ? trim($_POST['ordercolumn']) : NULL;
		$this->ordercolumn = array_key_exists($column, $columns) ? $column : $this->ordercolumn;
		return sprintf("ORDER BY `%s`.`%s` %s", (string) $this->definition->attributes()->name, $this->ordercolumn, $this->orderdirection);
	}
	
	protected function getTableColumns(){
		$storage = $this->controller->getStorage();
		$table = (string) $this->definition->attributes()->name;
		return $storage->getColumns($table);
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
		$html[] = "\t</div>"; //gridHeader
		$html[] = "\t<div class=\"gridColumns gradientSilver\">";
		$html[] = join("\n", $gridColumns);
		$html[] = "\t</div>"; //gridHeaderColumns
		$html[] = "\t<div class=\"gridContent\">";
		$html[] = "\t\t<div class=\"gridContentRecord\" title=\"{{primarykey}}\">";
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
		$html[] = "\t\t<div class=\"gridHeaderSearch column grid6of10 headerForm\">";
		$html[] = "\t\t\t<form action=\"$URI\" method=\"POST\">";
		$searchText = $this->controller->translate('action.search');
		$html[] = "\t\t\t\t<input type=\"text\" name=\"keywords\" placeholder=\"$searchText\"/>";
		$html[] = "\t\t\t\t<input type=\"submit\" value=\"&nbsp;\" class=\"searchButton button\"/>&nbsp;";
		$addText = $this->controller->translate('action.add');
		$html[] = "\t\t\t<input type=\"button\" name=\"addButton\" value=\"$addText\" class=\"addButton\"/>";
		$html[] = "\t\t\t</form>";
		$html[] = "\t\t</div>";
		return join("\n", $html);
	}
		
	protected function gridColumns ($column) {
		$class = $this->getAttribute("class", $column);
		$gridColumns[] = "\t\t<div$class>";
		$gridColumns[] = "\t\t\t".$this->titleTranslation($column);
		$field = (string) $column->attributes()->field;
		$gridColumns[] = "\t\t\t<span class=\"sort-icon\" name=\"$field\"></span>";
		$gridColumns[] = "\t\t</div>";
		return join("\n", $gridColumns);
	}
	
	protected function gridContent($column) {
		$name = (string) $column->attributes()->name;
		$class = $this->getAttribute("class", $column);
		$gridContent[] = "\t\t\t<div$class name=\"$name\">{{".$name."}}</div>";		
		return join("\n", $gridContent);
	}
	
	protected function gridFooter(){
		$translator = $this->controller->getSandbox()->getService("translation");
		$html[] = "\t\t<div class=\"column grid10of10\">";
		$html[] = "\t\t\t<span>".$translator->translate("pagination.legend")."</span>";
		$html[] = "\t\t\t<ul>";
		$html[] = "\t\t\t\t<li><a name=\"first\" class=\"button first\">".$translator->translate("pagination.first")."</a></li>";
		$html[] = "\t\t\t\t<li><a name=\"previous\" class=\"button previous\">".$translator->translate("pagination.previous")."</a></li>";
		$html[] = "\t\t\t\t<li><a name=\"next\" class=\"button next\">".$translator->translate("pagination.next")."</a></li>";
		$html[] = "\t\t\t\t<li><a name=\"last\" class=\"button last\">".$translator->translate("pagination.last")."</a></li>";
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