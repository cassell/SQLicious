<?php

abstract class DataAccessObjectFactory
{
	const RETURN_TYPE_OBJECTS = 'objects';
	const RETURN_TYPE_ARRAY = 'array';
	const RETURN_TYPE_JSON_ARRAY = 'json';
	const RETURN_TYPE_JSON_STRING = 'jsonstring';
	
	private $fields = array();
	private $conditional;
	private $additionalSelectFields = array();
	private $joinClause = '';
	private $groupByClause = '';
	private $orderByClause = '';
	
	abstract function getDatabaseName();
	abstract function getTableName();
	abstract function getIdField();
	
	function __construct()
	{	
		$this->openMasterConnection(); // needed for mysql_real_escape_string
		
		$this->fields = $this->getFields();
		$this->conditional = new FactoryConditional();
		
		// defaults
		$this->setReturnType(self::RETURN_TYPE_OBJECTS);
	}
	
	
	function addBinding($binding)
	{
		$this->conditional->addBinding($binding);
	}
	
	function addConditional($conditional)
	{
		$this->conditional->addConditional($conditional);
	}
	
	function query()
	{
		return $this->getOutputFromMysqlQuery(implode(" ",array($this->getSelectClause(),$this->getJoinClause(),$this->getConditionalSql(),$this->getGroupByClause(),$this->getOrderByClause(),$this->getLimitClause())));
	}
	
	// used to do custom queries, uses the same get select clause that the query() method
	function find($clause = "")
	{
		return $this->getOutputFromMysqlQuery($this->getSelectClause() . " " . $clause);
	}
	
	// find an object or data by primary key
	function findId($id)
	{
		$this->clearBindings();
		$this->addBinding(new EqualsBinding($this->getIdField(),intval($id)));
		$this->setLimit(1);
		return $this->query();
	}
	
	// return all rows
	function findAll()
	{
		$this->clearBindings();
		return $this->query();
	}
	
	function getSelectClause()
	{
		$sql = array();
		
		if($this->fields != null && is_array($this->fields) && count($this->fields) > 0)
		{
			foreach($this->fields as $field)
			{
				$sql[] = $this->getTableName()  . "." . $field;
			}
		}
		
		if($this->additionalSelectFields != null && is_array($this->additionalSelectFields) && count($this->additionalSelectFields) > 0)
		{
			foreach($this->fields as $field)
			{
				$sql[] = $field;
			}
		}
		
		return 'SELECT ' . implode(",",$sql) . " FROM " . $this->getTableName();
	}
	
	function setSelectFields($arrayOfFields)
	{
		if(is_array($this->fields))
		{
			if($arrayOfFields != null && is_array($arrayOfFields) && count($arrayOfFields) > 0)
			{
				$this->fields = array_intersect($this->fields, $arrayOfFields);
			}
		}
		else
		{
			$this->fields = $arrayOfFields;
		}
	}
	
	// joins
	function addJoinClause($clause)
	{
		$this->setJoinClause($this->getJoinClause() . " " . $clause);
	}
	function setJoinClause($val) { $this->joinClause = $val; }
	function getJoinClause() { return $this->joinClause; }
	
	// group by
	function setGroupByClause($val) { $this->groupByClause = $val; }
	function getGroupByClause() { return $this->groupByClause; }
	
	
	// order by
	function setOrderByClause($val) { $this->orderByClause = $val; }
	function getOrderByClause() { return $this->orderByClause; }
	function orderByField($field,$direction = 'asc')
	{
		if($this->getOrderByClause() == "")
		{
			$this->setOrderByClause("ORDER BY ");
		}
		else
		{
			$this->setOrderByClause(", ");
		}
		
		$this->setOrderByClause($this->getOrderByClause() . mysql_real_escape_string($field) . " " .  mysql_real_escape_string($direction));
	}
	
	
	// limits
	function setLimit($numberOfRecords,$page = 0)
	{
		if($page > 0)
		{
			$this->setLimitClause("LIMIT " . intval($numberOfRecords) . "," . intval($page));
		}
		else
		{
			$this->setLimitClause("LIMIT " . intval($numberOfRecords));
		}
	}
	function setLimitClause($val) { $this->limitClause = $val; }
	function getLimitClause() { return $this->limitClause; }
	
	
	// clear	
	function clearBindings()
	{
		$this->conditional = new FactoryConditional();
	}
	
	function openMasterConnection()
	{
		return DatabaseConnector::openMasterConnection($this->getDatabaseName());
	}
	
	function setReturnType($val) { $this->returnType = $val; }
	function getReturnType() { return $this->returnType; }
	function setReturnTypeToJSON(){ $this->setReturnType(self::RETURN_TYPE_JSON_ARRAY); }
	function setReturnTypeToArray(){ $this->setReturnType(self::RETURN_TYPE_ARRAY); }
	function setReturnTypeToObjects(){ $this->setReturnType(self::RETURN_TYPE_OBJECTS); }
	
	function setTimezoneAware($val) { $this->timezoneAware = $val; }
	function getTimezoneAware() { return $this->timezoneAware; }
	
	function setServerTimezone($val) { $this->serverTimezone = $val; }
	function getServerTimezone() { return $this->serverTimezone; }
	
	function setDestinationTimezone($val) { $this->destinationTimezone = $val; }
	function getDestinationTimezone() { return $this->destinationTimezone; }
	
	function addSelectField($field)
	{
		$this->additionalSelectFields[] = $field;
	}
	
	// save
	function save($object)
	{
		$conn = $this->openMasterConnection();
		
		if(!empty($object->modifiedColumns))
		{
			foreach(array_keys($object->modifiedColumns) as $field)
			{
				if($field != $this->getIdField())
				{
					if($object->data[$field] !== null)
					{
						$sql[] = $this->getTableName()  . "." . $field . ' = "' . mysql_real_escape_string($object->data[$field]) . '"';
					}
					else
					{
						$sql[] = ' ' . $this->getTableName() . "." . $field . ' = NULL';
					}
				}
			}
			
			if($object->getId() != DataAccessObject::NEW_OBJECT_ID)
			{
				$sql = 'UPDATE ' . $this->getTableName() . " SET " .  implode(",",$sql) . " WHERE " . $this->getTableName() . "." . $this->getIdField() . ' = ' . $object->getId();
				$result = mysql_query($sql,$this->openMasterConnection()) or trigger_error("DAOFactory Update Error: ". htmlentities($sql), E_USER_ERROR);
			}
			else
			{
				$sql = 'INSERT INTO ' . $this->getTableName() . " SET " .  implode(",",$sql);
				$result = mysql_query($sql,$this->openMasterConnection()) or trigger_error("DAOFactory Insert Error: ". htmlentities($sql), E_USER_ERROR);
				$object->data[$this->getIdField()] = mysql_insert_id();
			}
			
			@mysql_free_result($result);
			unset($object->modifiedColumns);
		}
	}
	
	// delete
	function deleteWhere($where)
	{
		if($where != "")
		{
			return $this->executeGenericSQL("DELETE FROM " . $this->getTableName() . " WHERE ".$where);
		}
		else
		{
			return $this->executeGenericSQL("DELETE FROM " . $this->getTableName());
		}
	}
	
	function delete($object)
	{
		if ($object != null && intval($object->getId()) > 0)
		{
			$this->deleteWhere($this->getIdField()." = ".$object->getId());
		}
	}
	
	function executeGenericSQL($sql)
	{
		$result = $this->getMySQLResult($sql);
		
		if($result != null && is_resource($result) && mysql_num_rows($result) > 0)
		{
			$data = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$data[] = $row;
			}
			
			return $data;
		}
		else
		{
			return $result;
		}
	}
	
	function executeQuery($sql)
	{
		$result = $this->getMySQLResult($sql);
		
		$data = array();
		while ($row = mysql_fetch_assoc($result))
		{
			$data[] = $row;
		}
		
		return $data;
	}

	function executeUpdate($sql)
	{
		return $this->executeGenericSQL($sql);
	}
	
	
	
	
	// find the first object matching the clause
	function findFirst($clause = "")
	{
		return reset($this->find($clause . " LIMIT 1"));
	}
	
	
	function findDistinctField($field,$clause = "")
	{
		$array = array();
		
		$rows = $this->executeGenericSQL('SELECT DISTINCT(' . mysql_real_escape_string($field) . ") as dataAccessObjectFactoryfindDistinctField FROM ". $this->getTableName() . " " . $clause);
		
		if($rows != null && is_array($rows) && count($rows) > 0)
		{
			foreach($rows as $row)
			{
				$array[] = $row["dataAccessObjectFactoryfindDistinctField"];
			}
		}
		
		return $array;
	}
	
	function findField($field,$clause = "")
	{
		$array = array();
		
		$rows = $this->executeGenericSQL('SELECT ' . mysql_real_escape_string($field) . " as dataAccessObjectFactoryfindField FROM ". $this->getTableName() . " " . $clause);
		
		if($rows != null && is_array($rows) && count($rows) > 0)
		{
			foreach($rows as $row)
			{
				$array[] = $row["dataAccessObjectFactoryfindField"];
			}
		}
		
		return $array;
	}
	
	function findFirstField($field, $clause = "")
	{
		return reset($this->findField($field, $clause));
	}
	
	function getCount($clause = "")
	{
		if($this->getIdField() != '')
		{
			return intval($this->sqlFunctionFieldQuery('COUNT', $this->getTableName() . "." . $this->getIdField(), $clause));
		}
		else
		{
			return intval($this->sqlFunctionFieldQuery('COUNT', '*', $clause));
		}
		
	}
	
	function getMaxField($field, $clause = "")
	{
		return $this->sqlFunctionFieldQuery('MAX', $field, $clause);
	}
	
	function getSumField($field, $clause = "")
	{
		return $this->sqlFunctionFieldQuery('SUM', $field, $clause);
	}
	
	function truncateTable()
	{
		$this->executeGenericSQL("TRUNCATE TABLE ". $this->getTableName());
	}
	
	// deprecate later
	function exists($object)
	{
		die("exists");
	}
	
	private function getMySQLResult($sql)
	{
		$result = mysql_query($sql, $this->openMasterConnection()) or trigger_error("DAOFactory Query Error: ". htmlentities($sql), E_USER_ERROR);
		
		return $result;
	}
	
	private function sqlFunctionFieldQuery($sqlFunction,$field,$clause)
	{
		$rows = $this->executeGenericSQL('select ' . $sqlFunction . '(' . mysql_real_escape_string($field) . ") as sqlFunctionFieldQuery from ". $this->getTableName() . " " . $clause);
		
		if($rows != null)
		{
			$row = current($rows);
			return $row["sqlFunctionFieldQuery"];
		}
	}
	
	private function getConditionalSql()
	{
		$conditionalSQL =  $this->conditional->getSql();
		
		if($conditionalSQL != "")
		{
			$conditionalSQL = " WHERE " . $conditionalSQL;
		}
		
		return $conditionalSQL;
	}
	
	private function getOutputFromMysqlQuery($sql)
	{
		$result = $this->getMySQLResult($sql);
		
		if($this->getReturnType() == self::RETURN_TYPE_JSON_ARRAY || $this->getReturnType() == self::RETURN_TYPE_JSON_STRING)
		{
			$data = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$j = array();
				if($row != null)
				{
					foreach($row as $field => $value)
					{
						if($field == $this->getIdField())
						{
							// push sub_id as id
							$j['id'] = $value;
						}
						else if($field != $this->getIdField()) // skip the primary key
						{
							$j[self::toFieldCase($field)] = $value;
						}
					}
				}
				
				$data[] = $j;
			}
			mysql_free_result($result);
			
			if($this->getReturnType() == self::RETURN_TYPE_JSON_STRING)
			{
				return self::JSONEncodeArray($data);
			}
			else
			{
				return $data;
			}
			
		}
		else if($this->getReturnType() == self::RETURN_TYPE_ARRAY)
		{
			$data = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$data[$row[$this->getIdField()]] = $row;
			}
			mysql_free_result($result);
			
			return $data;
		}
		else
		{
			$objects = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$objects[$row[$this->getIdField()]] = $this->loadObject($row);
			}
			mysql_free_result($result);
			
			return $objects;
		}
		
	}
	
	
	static function toFieldCase($val)
	{
		$segments = explode("_", $val);
		for ($i = 0; $i < count($segments); $i++)
		{
			$segment = $segments[$i];
			if ($i == 0)
				$result .= $segment;
			else
				$result .= strtoupper(substr($segment, 0, 1)).substr($segment, 1);
		}
		return $result;
	}
	
	static function JSONEncodeArray($array)
	{
		return json_encode(self::utf8EncodeArray($array));
	}
	
	static function utf8EncodeArray($array)
	{
	    foreach($array as $key => $value)
	    {
	    	if(is_array($value))
	    	{
	    		$array[$key] = self::utf8EncodeArray($value);
	    	}
	    	else
	    	{
	    		$array[$key] = utf8_encode($value);
	    	}
	    }
	       
	    return $array;
	}
	
}










/* abstract sql string class, bindings and conditionals extend this class */
abstract class SQLString
{
	function __construct() { }
	
	abstract protected function getSQL();
	
}


/* used to logical AND together bindings */
class Conditional extends SQLString
{
	function __construct()
	{
		parent::__construct();
		$this->items = $argv;
	}
	
	function addBinding($binding)
	{
		if($binding instanceof String)
		{
			$this->addItem(new StringBinding($binding));
		}
		else 
		{
			$this->addItem($binding);
		}
	}
	
	function addConditional($conditional)
	{
		$this->addItem($conditional);
	}
	
	private function addItem($item)
	{
		$this->items[] = $item;
	}
	
	function getSQL()
	{
		if($this->items != null && count($this->items) > 0)
		{
			$sql = array();
			
			foreach($this->items as $item)
			{
				$sql[] = $item->getSQL();
			}
			
			return "(" . implode(" AND ",$sql) . ")";
		}
		else return '';
	}
	
}


/* used to logical OR together bidnings */
class OrConditional extends Conditional
{
	function __construct()
	{
		parent::__construct($argv);
	}
	
	function getSQL()
	{
		if($this->items != null && count($this->items) > 0)
		{
			$sql = array();
			
			foreach($this->items as $item)
			{
				$sql[] = $item->getSQL();
			}
			
			return "(" . implode(" OR ",$sql) . ")";
		}
		else return '';
	}
	
}


// FactoryConditional is an AND conditional with WHERE in front an no parenthesis, used in DaoAccessObjectFactory
class FactoryConditional extends Conditional
{
	function __construct()
	{
		parent::__construct($argv);
	}
	
	function getSQL()
	{
		if($this->items != null && count($this->items) > 0)
		{
			$sql = array();
			
			foreach($this->items as $item)
			{
				$sql[] = $item->getSQL();
			}
			
			return implode(" AND ",$sql);
		}
		else return '';
	}
}

/* logical test binding */
class Binding extends SQLString
{
	function __construct($field,$operator,$value)
	{
		$this->field = $field;
		$this->value = $value;
		$this->operator = $operator;
		parent::__construct();
	}
	
	function getSQL()
	{
		return mysql_real_escape_string($this->field) . " " . $this->operator . " '". mysql_real_escape_string($this->value) ."'";
	}
}

/* string binding is a simple string */
class StringBinding extends SQLString
{
	function __construct($sql)
	{
		$this->sql = $sql;
		parent::__construct();
	}
	
	function getSQL()
	{
		return $this->sql;
	}
}

/* test if equal */
class EqualsBinding extends Binding
{
	function __construct($field,$value)
	{
		parent::__construct($field,'=',$value);
	}
}

class NotEqualsBinding extends Binding
{
	function __construct($field,$value)
	{
		parent::__construct($field,'!=',$value);
	}
}


/* see if field contains query */
class ContainsBinding extends SQLString
{
	function __construct($field,$query)
	{
		parent::__construct();
		$this->query = $query;
	}
	
	function getSQL()
	{
		return mysql_real_escape_string($this->field) . " LIKE '%". mysql_real_escape_string($this->query) ."%'";
	}
}

class InBinding extends SQLString
{
	function __construct($field,$array)
	{
		parent::__construct();
		$this->field = $field;
		$this->array = $array;
	}
	
	function getSQL()
	{
		if(count($this->array > 0))
		{
			foreach($this->array as $key => $item)
			{
				$this->array[$key] = mysql_real_escape_string($item);
			}
			
			return mysql_real_escape_string($this->field) . " in (" . implode(",",$this->array) . ")";
		}
		else
		{
			die("InBinding array is empty");
		}
	}
}

class NotInBinding extends SQLString
{
	function __construct($field,$array)
	{
		parent::__construct();
		$this->field = $field;
		$this->array = $array;
	}
	
	function getSQL()
	{
		if(count($this->array > 0))
		{
			foreach($this->array as $key => $item)
			{
				$this->array[$key] = mysql_real_escape_string($item);
			}
			
			return mysql_real_escape_string($this->field) . " not in (" . implode(",",$this->array) . ")";
		}
		else
		{
			die("InBinding array is empty");
		}
	}
}



?>