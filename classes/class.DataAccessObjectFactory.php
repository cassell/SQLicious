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
	private $joinClause = '';
	private $groupByClause = '';
	private $orderByClause = '';
	
	function __construct()
	{	
		// setup connection properties
		parent::__construct($this->getDatabaseName());
		
		// open a connection for mysql_real_escape_string
		$this->openConnection();
		
		// fields used in the SELECT clause
		$this->setSelectFields($this->getFields());
		
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
			if($obj->getIdField() != null)
			{
				$data[$obj->getId()] = $obj;
			}
			else
			{
				$data[] = $obj;
			}
			
		});
		
		$this->freeResult();
		
		return $data;
	}
	
	final function addPrimaryKeyBinding($id)
	{
		if($this->getIdField())
		{
			$this->addBinding(new EqualsBinding($this->getIdField(),intval($id)));
		}
	}
	
	static function getObject($id)
	{
		$f = new static();
		$f->clearBindings();
		$f->addBinding(new EqualsBinding($f->getIdField(),intval($id)));
		return $f->getFirstObject();
	}
	
	function getFirstObject()
	{
		$this->setLimit(1);
		$array = $this->getObjects();
		if($array != null && is_array($array))
		{
			return reset($array);
		}
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
		if($this->sql == null)
		{
			return implode(" ",array($this->getSelectClause(),$this->getJoinClause(),$this->getConditionalSql(),$this->getGroupByClause(),$this->getOrderByClause(),$this->getLimitClause()));
		}
		else
		{
			return $this->sql;
		}
	}
	
	// used to do custom queries, uses the same get select clause that the query() method 
	function find($clause = "")
	{
		$this->setSQL($this->getSelectClause() . " " . $clause);
		return $this->getObjects();
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
	
	// generate the select clause from $this->fields
	function getSelectClause()
	{
		return 'SELECT ' . implode(",",$this->fields) . " FROM " . $this->getTableName();
	}
	
	function setSelectFields($arrayOfFields)
	{
		if(func_num_args() == 1 && is_array($arrayOfFields))
		{
			// empty the fields array
			$this->fields = array();
			
			if($this->getIdField() != null)
			{
				$this->addSelectField($this->getIdField());
			}
			
			foreach($arrayOfFields as $field)
			{
				$this->addSelectField($field);
			}
		}
		else
		{
			$this->setSelectFields(func_get_args());
		}
	}
	function addSelectField($field)
	{
		if(strpos($field, ".") !== false)
		{
			$this->fields[] = $field;
		}
		else
		{
			$this->fields[] = $this->getTableName() . "." . $field;
		}
	}
	
	// joins
	function join($clause)
	{
		$this->setJoinClause($this->getJoinClause() . " " . $clause);
	}
	function setJoinClause($val) { $this->joinClause = $val; }
	function getJoinClause() { return $this->joinClause; }
	
	// eventually deprecate old naming convention
	function addJoinClause($clause)
	{
		$this->join($clause);
	}
	
	// group by
	function groupBy($fieldOrArray)
	{
		if(func_num_args() > 1)
		{
			// passed multiple fields $f->addGroupBy("first_name","last_name")
			$this->groupBy(func_get_args());
		}
		else if(func_num_args() == 1 && is_array($fieldOrArray) && count($fieldOrArray) > 0)
		{
			// passed an array of fields $f->addGroupBy(["first_name","last_name"])
			foreach($fieldOrArray as $field)
			{
				$this->groupBy($field);
			}
			
		}
		else if(func_num_args() == 1 && is_string($fieldOrArray))
		{
			// passed a single field $f->addGroupBy("last_name")
			if($this->getGroupByClause() == "")
			{
				$this->setGroupByClause("GROUP BY " . mysql_real_escape_string($fieldOrArray));
			}
			else
			{
				$this->setGroupByClause($this->getGroupByClause() . ", " . mysql_real_escape_string($fieldOrArray));
			}
		}
		
	}
	function setGroupByClause($val) { $this->groupByClause = $val; }
	function getGroupByClause() { return $this->groupByClause; }
	
	
	// order by
	function orderBy($field,$direction = 'asc')
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
	function setOrderByClause($val) { $this->orderByClause = $val; }
	function getOrderByClause() { return $this->orderByClause; }
	
	// deprecate old naming convetion
	function orderByField($field,$direction = 'asc')
	{
		$this->orderBy($field,$direction);
	}
	
	function orderByAsc($arrayOfFields)
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
	function limit($number,$startAtRow = 0)
	{
		if($startAtRow > 0)
		{
			$this->setLimitClause("LIMIT " . intval($number) . "," . intval($startAtRow));
		}
		else
		{
			$this->setLimitClause("LIMIT " . intval($number));
		}
	}
	function setLimitClause($val) { $this->limitClause = $val; }
	function getLimitClause() { return $this->limitClause; }
	
	// deprecate old naming convention
	function setLimit($numberOfRecords,$afterRow = 0)
	{
		$this->limit($numberOfRecords,$afterRow);
	}
	
	// clear	
	function clearBindings()
	{
		$this->conditional = new FactoryConditional();
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
		$data = array();
		
		$result = $this->getMySQLResult($sql);
		
		if($result != null && is_resource($result) && mysql_num_rows($result) > 0)
		{
			while ($row = mysql_fetch_assoc($result))
			{
				$data[] = $row;
			}
			
			@mysql_free_result($result);
		}
		
		return $data;
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