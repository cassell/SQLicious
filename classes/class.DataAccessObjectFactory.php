<?php

abstract class DataAccessObjectFactory
{
	// generated classes create these
	abstract function getDatabaseName();
	abstract function getTableName();
	abstract function getIdField();
	abstract function getFields();
	abstract function getDatabaseHost();
	abstract function getDatabaseUsername();
	abstract function getDatabasePassword();
	
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
		$this->openMasterConnection(); // needed for mysql_real_escape_string
		
		$this->fields = $this->getFields();
		$this->conditional = new FactoryConditional();
		
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
	
	function getArray()
	{
		$this->query();
		
		$data = array();
		
		$this->process(function($obj) use (&$data)
		{
			$data[] = $obj->toArray();
		});
		
		$this->freeResult();
		
		return $data;
	}
	
	function getJSON()
	{
		$this->query();
		
		$data = array();
		
		$this->process(function($obj) use (&$data)
		{
			$data[] = $obj->toJSON();
		});
		
		$this->freeResult();
		
		return $data;
	}
	
	function query()
	{
		$this->result = $this->getMySQLResult($this->getSQL());
		if($this->result && is_resource($this->result))
		{
			$this->numberOfRows = mysql_num_rows($this->result);
		}
		else
		{
			$this->numberOfRows = null;
		}
	}
	
	function count()
	{
		$this->result = $this->getMySQLResult($this->getCountSQL());
		
		if($this->result && is_resource($this->result))
		{
			$row = mysql_fetch_row($this->result);
			$this->freeResult();
			return intval($row[0]);
			
		}
		else
		{
			return null;
		}
	}
	
	function getNumberOfRows()
	{
		return $this->numberOfRows;
	}
	
	function freeResult()
	{
		mysql_free_result($this->result);
	}
	
	function outputJSONString()
	{
		echo "[";
		
		$firstRecord = true;
		
		$this->unbufferedProcess(function($obj) use (&$firstRecord)
		{
			if(!$firstRecord)
			{
				echo ",";
			}
			
			echo $obj->toJSONString();
			
			$firstRecord = false;
			
		});
		
		echo "]";
	}
	
	function outputCSV()
	{
		$this->unbufferedProcess(function($obj)
		{
			echo $obj->toCSV();
			echo "\n";
		});
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
	
	function process($function)
	{
		
		// run query if it hasn't been already
		if($this->result == null && $this->numberOfRows == null)
		{
			$this->query();
			$free = true;
		}
		
		if($this->result && $this->numberOfRows > 0)
		{
			mysql_data_seek($this->result,0); // reset result back to first row
			
			while ($row = mysql_fetch_assoc($this->result))
			{
				call_user_func($function,$this->loadObject($row));
			}
		}
		
		if($free == true)
		{
			$this->freeResult();
		}
		
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
					$objects[$row[$this->getIdField()]] = $this->loadObject($row);
				}
				else
				{
					$objects[] = $this->loadObject($row);
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
	
	function update($sql)
	{
		$result = $this->getMySQLResult($sql);
		@mysql_free_result($result);
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
	
	function getMySQLResult($sql)
	{
		$conn = $this->openMasterConnection();
		mysql_select_db($this->getDatabaseName(),$conn);
		$result = mysql_query($sql, $conn) or trigger_error("DAOFactory Error: ". htmlentities($sql), E_USER_ERROR);
		return $result;
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
	
	function openMasterConnection($openNew = false)
	{
		$conn = mysql_connect($this->getDatabaseHost(), $this->getDatabaseUsername(), $this->getDatabasePassword(),$openNew) or trigger_error("DAOFactory: Database Connection Error", E_USER_ERROR);
		mysql_select_db($this->getDatabaseName(),$conn) or trigger_error("DAOFactory: Database Connection Error", E_USER_ERROR);
		return $conn;
	}
	
	// using unbuffered mysql queries
	function unbufferedProcess($function)
	{
		$conn = $this->openMasterConnection(true);
		$this->openMasterConnection(true);  // this so future queries do not steal this connnection, this is a total HACK! Thanks PHP!
		
		mysql_select_db($this->getDatabaseName(),$conn);
		
		$result = mysql_unbuffered_query($this->getSQL(), $conn) or trigger_error("DAOFactory Unbuffered Error: ". htmlentities($this->getSQL()), E_USER_ERROR);
		
		if($result)
		{
			while ($row = mysql_fetch_assoc($result))
			{
				call_user_func($function,$this->loadObject($row));
			}
		}
		
		mysql_free_result($result);
		mysql_close($conn); // close the connnection we created in this method
		
		return true;
	}
	
	// convert timezones
	function convertTimezone($dateTime,$sourceTimezone,$destTimezone)
	{
		if(!is_integer($dateTime))
		{
			if(strtotime($dateTime) !== false)
			{
				return $this->convertTimezone(strtotime($dateTime),$sourceTimezone,$destTimezone);
			}
		}
		else
		{
			$result = $this->getMySQLResult("SELECT CONVERT_TZ('2004-01-01 12:00:00','" . mysql_real_escape_string($sourceTimezone) . "','" . mysql_real_escape_string($destTimezone) . "');");
			
			if($result != null)
			{
				$row = mysql_fetch_row($result);
				
				mysql_free_result($result);
				
				return strtotime(reset($row));
			}
			
		}

		// failed
		return false;
	}
	
	// utils
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
		if(count($this->array > 0))
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