<?php

class DatabaseProcessor
{
	const DATATBASE_CONFIG_GLOBAL_VARIABLE = 'DATABASE_CONFIG';
	
	private $connection;
	private $databaseNode;
	
	protected $result = null;
	protected $numberOfRows = null;
	protected $sql = null;
	
	function __construct($databaseNodeOrConfigurationName = null)
	{
		if(is_string($databaseNodeOrConfigurationName) && $GLOBALS[self::DATATBASE_CONFIG_GLOBAL_VARIABLE][$databaseNodeOrConfigurationName] instanceof DatabaseConfiguration)
		{
			$this->databaseNode = $GLOBALS[self::DATATBASE_CONFIG_GLOBAL_VARIABLE][$databaseNodeOrConfigurationName]->getMaster();
			$this->connectToMySQLDatabase();
		}
	}
	
	function openNewConnection()
	{
		$this->connectToMySQLDatabase(true);
	}
	
	
	// returns an array of rows from the database
	function getArray()
	{
		$data = array();
	
		$this->process(function($obj) use (&$data)
		{
			$data[] = $obj->toArray();
		});
	
		return $data;
	}
	
	// returns an array of rows from the database
	function getJSON()
	{
		$data = array();
	
		$this->process(function($obj) use (&$data)
		{
			$data[] = $obj->toJSON();
		});
	
		return $data;
	}
	
	function getFirstField($columnName)
	{
		$a = $this->getArray();
		
		if(is_array($a))
		{
			$a = reset($a);
			return $a[$columnName];
		}
	}
	
	function setSQL($sql)
	{
		$this->sql = $sql;
	}
	
	function getSQL()
	{
		return $this->sql;
	}
	
	function loadDataObject($row)
	{
		return new DataAccessArray($row);
	}
	
	function process($function)
	{
		// should we free the result later
		$free = false;
		
		// run query if it hasn't been already
		if($this->result == null && $this->numberOfRows == null)
		{
			$this->query();
			$free = true;
		}
		
		if($this->result != null)
		{
			if($this->numberOfRows > 0)
			{
				mysql_data_seek($this->result,0); // reset result back to first row
				
				while ($row = mysql_fetch_assoc($this->result))
				{
					call_user_func($function,$this->loadDataObject($row));
				}
			}
		}
		else
		{
			throw new ErrorException("SQLicious DatabaseProcessor SQL Error. No MySQL Result: " . htmlentities($this->getSQL()),$e->code,E_USER_ERROR,$e->filename,$e->lineno,$e->previous);
		}
	
		if($free == true)
		{
			$this->freeResult();
		}
	
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
	
		return $this->result;
	}
	
	function update($sql)
	{
		$result = $this->getMySQLResult($sql);
		@mysql_free_result($result);
	}
	
	function getMySQLResult($sql)
	{
		try 
		{
			$result = mysql_query($sql, $this->connection);
			return $result;
		}
		catch(ErrorException $e)
		{
			throw new ErrorException("SQLicious DatabaseProcessor SQL Error. Unable to MySQL Query: " . htmlentities($sql),$e->code,E_USER_ERROR,$e->filename,$e->lineno,$e->previous);
		}
	}
	
	// using unbuffered mysql queries
	function unbufferedProcess($function)
	{
		$conn = $this->connectToMySQLDatabase(true);
		$this->connectToMySQLDatabase(true);  // this so future queries do not steal this connnection, this is a total HACK! Thanks PHP!
	
		$result = mysql_unbuffered_query($this->getSQL(), $conn) or trigger_error("DAOFactory Unbuffered Error: ". htmlentities($this->getSQL()), E_USER_ERROR);
	
		if($result)
		{
			while ($row = mysql_fetch_assoc($result))
			{
				call_user_func($function,$this->loadDataObject($row));
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
	
	function getNumberOfRows()
	{
		return $this->numberOfRows;
	}
	
	function freeResult()
	{
		if($this->result != null)
		{
			mysql_free_result($this->result);
			unset($this->result);
		}
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
			else
			{
				$firstRecord = false;
			}
				
			echo $obj->toJSONString();
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
	
	function explain()
	{
		$explain = $this->getMySQLResult('EXPLAIN ' . $this->getSQL());
	
		$params = mysql_fetch_assoc($explain);
	
		@mysql_free_result($explain);
	
		return $params;
	}
	
	function queryTest()
	{
		echo '<pre>';
	
		echo 'SQL: ' . htmlentities($this->getSQL());
	
		echo "\n\n";
	
		echo 'EXPLAIN: ' . htmlentities(print_r($this->explain(),true));
	
		echo "\n\n";
	
		$result = $this->query();
	
		echo 'ROW COUNT: '  . htmlentities($this->getNumberOfRows(),true);
	
		echo "\n\n";
	
		echo 'FIRST ROW: ' . htmlentities(print_r(reset($this->getArray()),true));
	
		$this->freeResult();
	
		echo '</pre>';
	
	}
	
	private function connectToMySQLDatabase($new = false)
	{
		$this->connection = mysql_connect($this->databaseNode->getServerHost(), $this->databaseNode->getServerUserName(), $this->databaseNode->getServerPassword(), $new);
		mysql_select_db($this->databaseNode->getMySQLDatabaseName(),$this->connection);
	}
	
	// util
	static function formatTextCSV($text)
	{
		$text = preg_replace("/<(.|\n)*?>/","",$text);
	
		$text = str_replace("<br/>","\n",$text);
	
		$text = str_replace("&nbsp;"," ",$text);
	
		if(strpos($text,'"') === true)
		{
			$text = '"' . str_replace('"','""',$text) . '"';
		}
		else if(strpos($text,',') || strpos($text,"\n") || strpos($text,"\r"))
		{
			$text = '"' . str_replace('"','""',$text) . '"';
		}
	
		return html_entity_decode($text);
	}
	
}

?>