<?php

require_once('class.DatabaseProcessor.php');


abstract class DataAccessObjectFactory extends DatabaseProcessor
{
	// generated classes create these
	abstract function getTableName();
	abstract function getIdField();
	abstract function getFields();
	
	private $fields = array();
	private $conditional;
	private $additionalSelectFields = array();
	private $joinClause = '';
	private $groupByClause = '';
	private $orderByClause = '';
	
	private $result = null;
	private $numberOfRows = null;
	
	function __construct()
	{	
		// setup connection properties
		parent::__construct($this->getDatabaseName());
		
		// open a connection for mysql_real_escape_string
		$this->openConnection();
		
		// fields used in the SELECT clause
		$this->fields = $this->getFields();
		
		// conditional used in building the WHERE clause
		$this->conditional = new FactoryConditional();
		
	}
	
	// saves and deletes must open a master connection
	function openMasterConnection($openNew = false)
	{
		$this->useMasterConnectionFromGlobalConfig($databaseName);
		return $this->openConnection($openNew);
	}
	
	function getObjects()
	{
		$this->query();
		
		$data = array();
		
		$this->process(function($obj) use (&$data)
		{
			$data[$obj->getId()] = $obj;
		});
		
		$this->freeResult();
		
		return $data;
	}
	
	static function getObject($id)
	{
		$f = new static();
		$f->clearBindings();
		$f->addBinding(new EqualsBinding($f->getIdField(),intval($id)));
		$f->setLimit(1);
		return reset($f->getObjects());
	}
	
	function addBinding($binding)
	{
		$this->conditional->addBinding($binding);
	}
	
	function addConditional($conditional)
	{
		$this->conditional->addConditional($conditional);
	}
	
	function getCountSQL()
	{
		return implode(" ",array("SELECT count(" . $this->getIdField() . ") FROM " . $this->getTableName(),$this->getJoinClause(),$this->getConditionalSql(),$this->getGroupByClause(),$this->getOrderByClause(),$this->getLimitClause()));
	}
	
	function getSQL()
	{
		return implode(" ",array($this->getSelectClause(),$this->getJoinClause(),$this->getConditionalSql(),$this->getGroupByClause(),$this->getOrderByClause(),$this->getLimitClause()));
	}
	
	// used to do custom queries, uses the same get select clause that the query() method 
	function find($clause = "")
	{
		$result = $this->getMySQLResult($this->getSelectClause() . " " . $clause);
		
		if($result !== null)
		{
			$objects = array();
			while ($row = mysql_fetch_assoc($result))
			{
				if($this->getIdField() != "") // tables or views that do not have a primary key
				{
					$objects[$row[$this->getIdField()]] = $this->loadDataObject($row);
				}
				else
				{
					$objects[] = $this->loadDataObject($row);
				}
			}
			mysql_free_result($result);
		}
		
		return $objects;
	}
	
	// find an object or data by primary key
	function findId($id)
	{
		return $this->getObject($id);
	}
	
	// return all objects
	function findAll()
	{
		$this->clearBindings();
		return $this->getObjects();
	}
	
	// generate the select clause from $this->fields and $this->additionalSelectFields
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
			foreach($this->additionalSelectFields as $field)
			{
				$sql[] = $field;
			}
		}
		
		return 'SELECT ' . implode(",",$sql) . " FROM " . $this->getTableName();
	}
	
	function setSelectFields($arrayOfFields)
	{
		if(func_num_args() == 1 && is_array($arrayOfFields))
		{
			// passed array
			if(is_array($this->fields))
			{
				if($arrayOfFields != null && is_array($arrayOfFields) && count($arrayOfFields) > 0)
				{
					$this->fields = array_merge(array($this->getIdField()),array_intersect($this->fields, $arrayOfFields));
				}
			}
			else
			{
				$this->fields = $arrayOfFields;
			}
		}
		else
		{
			$this->setSelectFields(func_get_args());
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
			$this->setOrderByClause($this->getOrderByClause() . ", ");
		}
		
		$this->setOrderByClause($this->getOrderByClause() . mysql_real_escape_string($field) . " " .  mysql_real_escape_string($direction));
	}
	
	function orderByFieldsAscending($arrayOfFields)
	{
		if(func_num_args() == 1 && is_array($arrayOfFields) && count($arrayOfFields) > 0)
		{
			foreach($arrayOfFields as $field)
			{
				$this->orderByField($field);
			}
		}
		else
		{
			$this->orderByFieldsAscending(func_get_args());
		}
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
	
	function addSelectField($field)
	{
		$this->additionalSelectFields[] = $field;
	}
	
	function deleteWhere($whereClause)
	{
		return $this->update("DELETE FROM " . $this->getTableName() . " WHERE " . $whereClause);
	}
	
	// find the first object matching the clause
	function findFirst($clause = "")
	{
		return reset($this->find($clause . " LIMIT 1"));
	}
	
	function findDistinctField($field,$clause = "")
	{
		$array = array();
		
		$result = $this->getMySQLResult('SELECT DISTINCT(' . mysql_real_escape_string($field) . ") as fdf FROM ". $this->getTableName() . " " . $clause);
		
		if($result != null && is_resource($result) && mysql_numrows($result) > 0)
		{
			while ($row = mysql_fetch_assoc($result))
			{
				$array[] = $row["fdf"];
			}
		}
		
		return $array;
	}
	
	function findField($field,$clause = "")
	{
		$array = array();
		
		$result = $this->getMySQLResult('SELECT ' . mysql_real_escape_string($field) . " as ff FROM ". $this->getTableName() . " " . $clause);
		
		if($result != null && is_resource($result) && mysql_numrows($result) > 0)
		{
			while ($row = mysql_fetch_assoc($result))
			{
				$array[] = $row["ff"];
			}
		}
		
		return $array;
	}
	
	function findFirstField($field, $clause = "")
	{
		return reset($this->findField($field, $clause . " LIMIT 1"));
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
		$this->update("TRUNCATE TABLE ". $this->getTableName());
	}
	
	private function sqlFunctionFieldQuery($sqlFunction,$field,$clause)
	{
		return reset($this->findField($sqlFunction . '(' . mysql_real_escape_string($field) . ')',$clause));
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
	
	// depecate
	function executeQuery($sql)
	{
		$result = $this->getMySQLResult($sql);
		
		if($result != null && is_resource($result) && mysql_num_rows($result) > 0)
		{
			$data = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$data[] = $row;
			}
			
			@mysql_free_result($result);
			
			return $data;
			
		}
		else
		{
			return $result;
		}
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
		if(is_string($binding))
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

class TrueBooleanBinding extends Binding
{
	function __construct($field)
	{
		parent::__construct($field,'=','1');
	}
}

class FalseBooleanBinding extends Binding
{
	function __construct($field)
	{
		parent::__construct($field,'=','0');
	}
}


/* see if field contains query */
class ContainsBinding extends SQLString
{
	function __construct($field,$query)
	{
		parent::__construct();
		
		$this->field = $field;
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
		if(count($this->array) == 1)
		{
			return mysql_real_escape_string($this->field) . " = " . reset($this->array);
		}
		else if(count($this->array) > 0)
		{
			foreach($this->array as $key => $item)
			{
				$this->array[$key] = mysql_real_escape_string($item);
			}
			
			return mysql_real_escape_string($this->field) . " IN (" . implode(",",$this->array) . ")";
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
			
			return mysql_real_escape_string($this->field) . " NOT IN (" . implode(",",$this->array) . ")";
		}
		else
		{
			die("NotInBinding array is empty");
		}
	}
}

?>