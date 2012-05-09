<?php

namespace base;

require_once('BaseException.php');

class Storage {
	
	protected $host = NULL;
	
	protected $user = NULL;
	
	protected $password = NULL;
	
	protected $schema = NULL;
	
	protected $resource = NULL;
	
	protected $result = NULL;
	
	protected $columns = NULL;
	
	public function __construct(&$sandbox) {
		if(is_null($sandbox->settings)) {
			throw new BaseException("Please provide mysql connection settings");
		}
		$defaults = $sandbox->settings;
		$this->host = is_string($defaults['host']) ? $defaults['host'] : $this->host;
		$this->user = is_string($defaults['user']) ? $defaults['user'] : $this->user;
		$this->password = is_string($defaults['password']) ? $defaults['password'] : $this->password;
		$this->schema = is_string($defaults['schema']) ? $defaults['schema'] : $this->schema;
		$this->resource = @new \mysqli($this->host, $this->user, $this->password, $this->schema);
		if($this->resource->connect_error) {
			throw new BaseException($this->resource->connect_error);
		}
	}
	
	public function query($sql){
		$this->result = $this->resource->query($sql);
		if(strlen($this->resource->error)){
			throw new BaseException($this->resource->error." : ".$sql);
		}
		if($this->result instanceof \mysqli_result) {
			return $this->fetch();
		}
		return $this->result;
	}
		
	public function insert($arguments){
		$arguments['table'] = $this->sanitize($arguments['table']);
		$this->setTableColumns($arguments['table']);
		$statement = $this->insertStatement($arguments);
		foreach($arguments['content'] as $key => $value){
			$parameters[]  = array($key, $value);
		}
		$this->bind($statement, $parameters);
		if(!$statement->execute()){
			throw new BaseException($this->resource->error);
		}
	}
		
	protected function insertStatement($arguments){
		$table = $arguments['table'];
		$columns = $this->parameterColumns($arguments['content']);
		$markers = $this->parameterMarkers(count($arguments['content']));
		$query = "INSERT INTO `$table` ($columns) VALUES($markers)";
		return $this->prepare($query);
	}

	public function select($arguments){
		$arguments['table'] = $this->sanitize($arguments['table']);
		$columns = $this->selectColumns($arguments);
		$query = sprintf("SELECT %s FROM %s", $columns, $arguments['table']);
		$query .= $this->selectWhereCondition($arguments);
		$query .= $this->limit($arguments);
		$query .= $this->groupBy($arguments);
		return $this->query($query);
	}
	
	protected function selectColumns($arguments){
		if(array_key_exists('fields', $arguments)){
			foreach($arguments['fields'] as $key => $value){
				$arguments['fields'][$key] = "`$value`";
			}
			$columns = join(", ", $arguments['fields']);
		} else {
			$columns = "*";
				
		}
		return $columns;
	}
	
	protected function selectWhereCondition($arguments){
		if(!array_key_exists('constraints', $arguments)) return "";
		$query = " WHERE ";
		foreach($arguments['constraints'] as $key => $value){
			switch(gettype($value)){
				case 'integer':
					$conditions[] = sprintf("`$key` = %d", $value);
					break;
				case 'string':
					$conditions[] = sprintf("`$key` = '%s'", $value);
					break;
				case 'double':
					$conditions[] = sprintf("`$key` = %f", $value);
					break;
			}
		}
		$operator = array_key_exists('operator', $arguments) ? $arguments['operator'] : "AND";
		$query .= join(" $operator", $conditions);
		return $query;
	}
				
	public function update($arguments){
		$arguments['table'] = $this->sanitize($arguments['table']);
		$this->setTableColumns($arguments['table']);
		$statement = $this->updateStatement($arguments);
		foreach($arguments['content'] as $key => $value){
			$parameters[] = array($key, $value);
		}
		foreach($arguments['constraints'] as $key => $value){
			$parameters[] = array($key, $value);
		}
		$this->bind($statement, $parameters);
		if(!$statement->execute()){
			throw new BaseException($this->resource->error);
		}
	}
	
	protected function updateStatement($arguments){
		$query = sprintf("UPDATE `%s` SET", $arguments['table']);
		if(!array_key_exists('content', $arguments)){
			throw new BaseException("Please provide table fields to update ".$arguments['table']);
		}
		if(!array_key_exists('constraints', $arguments)){
			throw new BaseException("Please provide constraints for updating ".$arguments['table']);
		}
		foreach($arguments['content'] as $key => $value){
			$columns[] = sprintf(" `$key` = ?");
		}
		$query .= " ".join(", ", $columns);
		$query .= $this->whereCondition($arguments);
		$statement = $this->resource->prepare($query);
		if(!$statement){
			throw new BaseException($this->resource->error." : ".$query);
		}
		return $statement;
	}
	
	public function delete($arguments){
		$arguments['table'] = $this->sanitize($arguments['table']);
		$this->setTableColumns($arguments['table']);
		$query = sprintf("DELETE FROM `%s`");
		$condition = $this->whereCondition($arguments);
		if(!strlen($condition)) {
			throw new \Exception("Please provide constraints for table delete");
		}
		$query .= $condition;
		$statement = $this->prepare($query);
		$this->bind($statement, $arguments['constraints']);
		if(!$statement->execute()){
			throw new BaseException($this->resource->error);
		}
	}
	
	public function getInsertID(){
		return $this->resource->insert_id;
	}
	
	public function sanitize($value){
		return mysqli_real_escape_string($this->resource, trim($value));
	}	
	
	protected function whereCondition($arguments){
		if(!array_key_exists('constraints', $arguments)) return "";
		$query = " WHERE ";
		foreach($arguments['constraints'] as $key => $value){
			$condition[] = "`$key` = ?";
		}
		$operator = array_key_exists('operator', $arguments) ? $arguments['operator'] : "AND";
		$query .= join(" $operator", $condition);
		return $query;
	}
	
	protected function limit($arguments){
		if(!array_key_exists('limit', $arguments)) return "";
		return sprintf(" LIMIT %d, %d", $arguments['limit'][0], $arguments['limit'][1]);
	}
	
	protected function groupBy($arguments){
		if(!array_key_exists('group', $arguments)) return "";
		return join(", ", $arguments['$arguments']);
	}

	
	protected function fetch(){
		if($this->result->num_rows === 0) return array();
		while($row = $this->result->fetch_assoc()){
			$rows[] = $row;
		}
		return (count($rows) === 1) ? $rows[0] : $rows;
	}
	
	protected function prepare($query){
		$statement = $this->resource->prepare($query);
		if(!$statement) {
			throw new BaseException($this->resource->error." : ".$query);
		}
		return $statement;
	}
	
	protected function bindParameters($parameters){
		foreach($parameters as $value){
			$values[] = &$value[1];
			$types[] = $this->columns[$value[0]]['typeCharacter'];
		}
		array_unshift($values, join("", $types));
		return $values;
	}
	
	protected function bind(&$statement, $parameters){
		if(!call_user_func_array(array($statement, "bind_param"), $this->bindParameters($parameters))) {
			throw new BaseException($this->resource->error." : ".json_encode($parameters));
		}
	}
	
	protected function bindTypes($argument) {
		foreach($argument as $key => $value){
			$types[] = $this->columns[$key]['typeCharacter'];
		}
		return join("", $types);
	}
	
	protected function parameterColumns($argument){
		foreach($argument as $key => $value){
			$columns[] = "`$key`";
		}
		return join(", ", $columns);
	}
	
	protected function parameterValues($argument){
		foreach($argument as $key => $value){
			$values[] = $this->sanitize($value);
		}
		return $values;
	}
	
	protected function parameterMarkers($argument){
		while($argument--){
			$markers[] = "?";
		}
		return join(", ", $markers);
	}
	
	protected function parameterMarkersWithColumns($argument){
		foreach($argument as $key => $value){
			$markers[] = "$key = ?";
		}
		return join(", ", $markers);
	}
	
	protected function setTableColumns($table){
		$this->columns = array();
		$columns = $this->query(sprintf("SHOW COLUMNS FROM `%s`", $table));
		foreach($columns as $column){
			$field = $column['Field'];
			$type = $column['Type'];
			$this->columns[$field] = array('typeCharacter' => $this->typeCharacter($type));
		}
	}
	
	protected function typeCharacter($type){
		$matches = preg_split("/[\(\)]+/", "$type");
		switch(strtoupper($matches[0])){
			case 'INT':
			case 'BIGINT':
			case 'TINYINT':
			case 'SMALLINT':
			case 'MEDIUMINT':
			case 'INTEGER':
			case 'YEAR':
				return 'i';
				break;
			case 'FLOAT':
			case 'REAL':
			case 'DECIMAL':
			case 'DOUBLE':
				return 'd';
				break;
			case 'TINYTEXT':
			case 'MEDIUMTEXT':
			case 'LONGTEXT':
			case 'DATE':
			case 'DATETIME':
			case 'TIMESTAMP':
			case 'TIME':
			case 'TEXT':
			case 'VARCHAR':
			case 'CHAR':
			case 'ENUM':
			case 'SET':
				return 's';
			case 'LONGBLOB':
			case 'MEDIUMBLOB':
			case 'BLOB':
			case 'TINYBLOB':
				return 'b';
				break;
		}
	}
	
}

?>