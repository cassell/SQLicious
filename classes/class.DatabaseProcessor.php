<?php

class DatabaseProcessor
{
	var $connection;
	var $databaseNode;
	
	protected $sql = null;
	
	function __construct($databaseNodeOrConfigurationName)
	{
		// setup node
		if($databaseNodeOrConfigurationName instanceof DatabaseNode)
		{
			$this->databaseNode = $databaseNodeOrConfigurationName;
		}
		else if(is_string($databaseNodeOrConfigurationName) && defined('SQLICIOUS_CONFIG_GLOBAL') && array_key_exists(SQLICIOUS_CONFIG_GLOBAL, $GLOBALS))
		{
			if($GLOBALS[SQLICIOUS_CONFIG_GLOBAL][$databaseNodeOrConfigurationName] instanceof DatabaseConfiguration)
			{
				$this->databaseNode = $GLOBALS[SQLICIOUS_CONFIG_GLOBAL][$databaseNodeOrConfigurationName]->getMaster();
			}
			else
			{
				throw new SQLiciousErrorException("databaseNodeOrConfigurationName specified must be an instance of DatabaseConfiguration");
			}
		}
		else
		{
			throw new SQLiciousErrorException("A DatabaseNode must be passed to DatabaseProcessor or SQLICIOUS_CONFIG_GLOBAL must be defined.");
		}
	}
	
	// escape a string to prevent mysql injection
	function escapeString($string)
	{
		$this->connectToMySQLDatabase();
		
		return $this->connection->real_escape_string($string);
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
	
	// return a single column from a database as an non-associative array	
	function getSingleColumnArray($column)
	{
		$data = array();
	
		$this->process(function($obj) use (&$data,$column)
		{
			$t = $obj->toArray();
			$data[] = $t[$column];
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
	
	// return a single value from the database
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
	
	// build a data object from the row data
	function loadDataObject($row)
	{
		return new DataAccessArray($row);
	}
	
	// loop through rows return from database calling closure function provided
	function process($function)
	{
		$result = $this->query();
		
		if($result != null)
		{
			if($this->getNumberOfRowsFromResult($result) > 0)
			{
				$result->data_seek(0);
				
				while ($row = $result->fetch_assoc())
				{
					call_user_func($function,$this->loadDataObject($row));
				}
			}
		}
	
		$this->freeResult($result);
	}
	
	// same as process but uses unbuffered connection
	function unbufferedProcess($function)
	{
		$this->connectToMySQLDatabase();
		
		$this->connection->real_query($this->getSQL());
		
		$result = $this->connection->use_result();
		
		while ($row = $result->fetch_assoc())
		{
			call_user_func($function,$this->loadDataObject($row));
		}
		
		$this->freeResult($result);

		return true;
	}
	
	function getNumberOfRowsFromResult($result)
	{
		return (int)$result->num_rows;
	}
	
	function query()
	{
		if(count(func_get_args()) > 0)
		{
			throw new SQLiciousErrorException("SQLicious DatabaseProcessor query does not accept any parameters");
		}
		$result = $this->getMySQLResult($this->getSQL());
		return $result;
	}
	
	function update($sql)
	{
		$result = $this->getMySQLResult($sql);
		$this->freeResult($result);
	}
	
	function executeMultiQuery()
	{
		return $this->__multiQuery();
	}
	
	private function __multiQuery()
	{
		$this->connectToMySQLDatabase();
		
		$this->connection->multi_query($this->getSQL());
		
		do
		{
			if($this->connection->error)
			{
				throw new SQLiciousErrorException("SQLicious DatabaseProcessor multiQuery SQL Error. Reason given " . $this->connection->error);
			}
			
			if(!$this->connection->more_results() || (!$this->connection->next_result() && $this->connection->error == null))
			{
				break;
			}
			
		} while (true);
		
		$this->connection->close();
	}
	
	
	
	function getMySQLResult($sql)
	{
		$this->connectToMySQLDatabase();
		
		try 
		{
			return $this->connection->query($sql);
		}
		catch(ErrorException $e)
		{
			throw new SQLiciousErrorException("SQLicious DatabaseProcessor SQL Error. MySQL Query Failed: " . htmlentities($sql) . '. Reason given ' . $e);
		}
	}
	
	// convert timezones, use "US/Eastern" mysql format (mysql.time_zone_name)
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
			$this->connectToMySQLDatabase();
			
			$sql = "SELECT CONVERT_TZ('" . date(SQLICIOUS_MYSQL_DATETIME_FORMAT,$dateTime) . "','" . $this->escapeString($sourceTimezone) . "','" . $this->escapeString($destTimezone) . "');";
			
			try 
			{
				$result = $this->connection->query($sql);
				
				$destDateTime = reset($result->fetch_row());
				
				if($destDateTime != null)
				{
					return $destDateTime;
				}
				else
				{
					throw new SQLiciousErrorException("SQLicious DatabaseProcessor SQL Error. convertTimezone Failed: " . htmlentities($sql));
				}
			}
			catch(ErrorException $e)
			{
				throw new SQLiciousErrorException("SQLicious DatabaseProcessor SQL Error. convertTimezone Failed: " . htmlentities($sql) . '. Reason given ' . $e);
			}
		}
	}
	
	function freeResult($result)
	{
		if($result != null)
		{
			try
			{
				if($result instanceof mysqli_result)
				{
					$result->free();
				}
			}
			catch(ErrorException $e)
			{
				// Do nothing
			}
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
	
	function connectToMySQLDatabase($forceReconnect = false)
	{
		if($this->connection == null || $forceReconnect)
		{
			$this->connection = new mysqli($this->databaseNode->serverHost, $this->databaseNode->serverUsername, $this->databaseNode->serverPassword, $this->databaseNode->serverDatabaseName, $this->databaseNode->serverPort ? $this->databaseNode->serverPort : null, $this->databaseNode->serverSocket);
		}
		
		if($this->connection == null || $this->connection->connect_errno)
		{
			throw new SQLiciousErrorException("SQLicioius Connection Error");
		}
		
		$this->connection->set_charset($this->databaseNode->serverCharset);
		
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
	
	// useful for replacing mysql_real_escape_string in old code with DatabaseProcessor::mysql_real_escape_string()
	static function mysql_real_escape_string($string)
	{
		if(defined('SQLICIOUS_CONFIG_GLOBAL') && array_key_exists(SQLICIOUS_CONFIG_GLOBAL, $GLOBALS))
		{
			$dp = new DatabaseProcessor(reset(array_keys($GLOBALS[SQLICIOUS_CONFIG_GLOBAL]->getDatabases())));
			return $dp->escapeString($string);
		}
		else
		{
			throw new SQLiciousErrorException("DatabaseProcess::mysql_real_escape_string requires SQLICIOUS_CONFIG_GLOBAL");
		}
	}
	
}

?>