<?php

class DatabaseProcessor
{
	const GLOBAL_DATABASE_PROCESSOR_CONNECTIONS = 'GLOBAL_DATABASE_PROCESSOR_CONNECTIONS';
	
	private $databaseName;
	private $databaseUsername;
	private $databaseHost;
	private $databasePassword;
	private $connection;
	
	public $sql = null;
	
	// if a database name is provided the DatabaseProcess will open a master connection to the database based on config
	function __construct($databaseName = null)
	{
		if($databaseName != null)
		{
			$this->useMasterConnectionFromGlobalConfig($databaseName);
		}
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
		$this->openConnection();
		
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
		$conn = $this->openConnection(true);
		$this->openConnection(true);  // this so future queries do not steal this connnection, this is a total HACK! Thanks PHP!
	
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
	
	function useMasterConnectionFromGlobalConfig($databaseName)
	{
		if(is_array($GLOBALS[self::GLOBAL_DATABASE_PROCESSOR_CONNECTIONS]) && array_key_exists($databaseName, $GLOBALS[self::GLOBAL_DATABASE_PROCESSOR_CONNECTIONS]))
		{
			$this->setDatabaseName($GLOBALS[self::GLOBAL_DATABASE_PROCESSOR_CONNECTIONS][$databaseName]['master']['name']);
			$this->setDatabaseHost($GLOBALS[self::GLOBAL_DATABASE_PROCESSOR_CONNECTIONS][$databaseName]['master']['host']);
			$this->setDatabaseUsername($GLOBALS[self::GLOBAL_DATABASE_PROCESSOR_CONNECTIONS][$databaseName]['master']['username']);
			$this->setDatabasePassword($GLOBALS[self::GLOBAL_DATABASE_PROCESSOR_CONNECTIONS][$databaseName]['master']['password']);
		}
	}
	
	// opens a connection and sets $this->connection (note: this connection could be closed anywhere if connection close is called )
	function openConnection($openNew = false)
	{
		try
		{
			$this->connection = mysql_connect($this->getDatabaseHost(), $this->getDatabaseUsername(), $this->getDatabasePassword(),$openNew);
			mysql_select_db($this->getDatabaseName(),$this->connection);
			return $this->connection;
		}
		catch(ErrorException $e)
		{
			throw new ErrorException("DatabaseProcessor Connection Error. Unable to Connect.",$e->code,E_USER_ERROR,$e->filename,$e->lineno,$e->previous);
		}
	}
	
	function setDatabaseName($val) { $this->databaseName = $val; }
	function getDatabaseName() { return $this->databaseName; }
	
	function setDatabaseUsername($val) { $this->databaseUsername = $val; }
	function getDatabaseUsername() { return $this->databaseUsername; }
	
	function setDatabaseHost($val) { $this->databaseHost = $val; }
	function getDatabaseHost() { return $this->databaseHost; }
	
	function setDatabasePassword($val) { $this->databasePassword = $val; }
	function getDatabasePassword() { return $this->databasePassword; }
	
}

?>