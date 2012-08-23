<?php

class DatabaseProcessor
{
	const DATATBASE_CONFIG_GLOBAL_VARIABLE = 'DATABASE_CONFIG';
	
	var $connection;
	var $databaseNode;
	
	protected $result = null;
	protected $numberOfRows = null;
	protected $sql = null;
	
	function __construct($databaseNodeOrConfigurationName = null)
	{
		// setup node
		if($databaseNodeOrConfigurationName instanceof DatabaseNode)
		{
			$this->databaseNode = $databaseNodeOrConfigurationName;
		}
		else if(is_string($databaseNodeOrConfigurationName) && $GLOBALS[self::DATATBASE_CONFIG_GLOBAL_VARIABLE][$databaseNodeOrConfigurationName] instanceof DatabaseConfiguration)
		{
			$this->databaseNode = $GLOBALS[self::DATATBASE_CONFIG_GLOBAL_VARIABLE][$databaseNodeOrConfigurationName]->getMaster();
			$this->connectToMySQLDatabase();
		}
		else if($GLOBALS[self::DATATBASE_CONFIG_GLOBAL_VARIABLE] != null)
		{
			$this->databaseNode = reset($GLOBALS[self::DATATBASE_CONFIG_GLOBAL_VARIABLE])->getMaster();
		}
		else
		{
			throw new ErrorException("SQLicious Configuration Error",null,E_USER_ERROR);
		}
		
		// open connection
		if($this->databaseNode != null)
		{
			$this->connectToMySQLDatabase();
		}
		else
		{
			throw new ErrorException("SQLicious Configuration Missing",null,E_USER_ERROR);
		}
		
	}
	
	function escapeString($string)
	{
		if($this->connection == null)
		{
			$this->connectToMySQLDatabase();
		}
		
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
		$this->query();
		
		if($this->result != null)
		{
			if($this->numberOfRows > 0)
			{
				$this->result->data_seek(0);
				
				while ($row = $this->result->fetch_assoc())
				{
					call_user_func($function,$this->loadDataObject($row));
				}
			}
		}
		else
		{
			throw new ErrorException("SQLicious DatabaseProcessor SQL Error. No MySQL Result: " . htmlentities($this->getSQL()),null,E_USER_ERROR);
		}
	
		$this->freeResult();
	}
	
	function openNewConnection()
	{
		$this->connectToMySQLDatabase(true);
	}
	
	function query()
	{
		$this->getMySQLResult($this->getSQL());
		$this->numberOfRows = (int)$this->result->num_rows;
		
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
			if($this->connection == null)
			{
				$this->connectToMySQLDatabase();
			}
			
			$this->result = $this->connection->query($sql);
			
			return $this->result;
		}
		catch(ErrorException $e)
		{
			throw new ErrorException("SQLicious DatabaseProcessor SQL Error. Unable to MySQL Query: " . htmlentities($sql),null,E_USER_ERROR);
		}
	}
	
	// using unbuffered mysql queries
	function unbufferedProcess($function)
	{
		$connection = new mysqli($this->databaseNode->getServerHost(), $this->databaseNode->getServerUserName(), $this->databaseNode->getServerPassword(), $this->databaseNode->getMySQLDatabaseName(), $this->databaseNode->getPort(), $this->databaseNode->getSocket());
		new mysqli($this->databaseNode->getServerHost(), $this->databaseNode->getServerUserName(), $this->databaseNode->getServerPassword(), $this->databaseNode->getMySQLDatabaseName(), $this->databaseNode->getPort(), $this->databaseNode->getSocket());
		
		if($connection != null)
		{
			$connection->real_query($this->getSQL());
			
			$result = $connection->use_result();
			
			if($result != null)
			{
				while ($row = $result->fetch_assoc())
				{
					call_user_func($function,$this->loadDataObject($row));
				}

				$result->free();
			}
		}
		
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
			$result = $this->getMySQLResult("SELECT CONVERT_TZ('2004-01-01 12:00:00','" . $this->escapeString($sourceTimezone) . "','" . $this->escapeString($destTimezone) . "');");
				
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
			$this->result->free();
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
		$this->connection = new mysqli($this->databaseNode->getServerHost(), $this->databaseNode->getServerUserName(), $this->databaseNode->getServerPassword(), $this->databaseNode->getMySQLDatabaseName(), $this->databaseNode->getPort(), $this->databaseNode->getSocket());
		
		if($this->connection == null || $this->connection->connect_errno)
		{
			throw new ErrorException("SQLicioius Connection Errro",null,E_USER_ERROR);
		}
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
	
	
	// this function should only be used if you need to replace a bunch of mysql_real_escape_string($string)
	// functions with DatabaseProcessor::mysql_real_escape_string($sring)
	static function mysql_real_escape_string($string)
	{
		$dp = new DatabaseProcessor();
		
		return $dp->escapeString($string);
	}
	
}

?>