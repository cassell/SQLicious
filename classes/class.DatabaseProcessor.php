<?php

class DatabaseProcessor
{
	var $connection;
	var $databaseNode;
	var $result = null;
	
	protected $numberOfRows = null;
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
			if($GLOBALS[SQLICIOUS_CONFIG_GLOBAL]->$databaseNodeOrConfigurationName instanceof DatabaseConfiguration)
			{
				$this->databaseNode = $GLOBALS[SQLICIOUS_CONFIG_GLOBAL]->$databaseNodeOrConfigurationName->getMaster();
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
	
		$this->freeResult();
	}
	
	// using unbuffered mysql queries
	function unbufferedProcess($function)
	{
		$connection = new mysqli($this->databaseNode->serverHost, $this->databaseNode->serverUsername, $this->databaseNode->serverPassword, $this->databaseNode->serverDatabaseName, $this->databaseNode->serverPort ? $this->databaseNode->serverPort : null, $this->databaseNode->serverSocket);
		
		if($connection != null)
		{
			$connection->set_charset($this->databaseNode->serverCharset);
			
			$connection->real_query($this->getSQL());
			$result = $connection->use_result();
			
			while ($row = $result->fetch_assoc())
			{
				call_user_func($function,$this->loadDataObject($row));
			}
			
			$result->free();
		}
		
		return true;
	}
	
	function query()
	{
		$this->getMySQLResult($this->getSQL());
		$this->numberOfRows = (int)$this->result->num_rows;
		
		return $this->result;
	}
	
	function executeMultiQuery()
	{
		return $this->__multiQuery();
	}
	
	private function __multiQuery()
	{
		if($this->connection == null)
		{
			$this->connectToMySQLDatabase();
		}
		
		$this->connection->multi_query($this->getSQL());
		
		do
		{
			if($this->connection->error)
			{
				throw new SQLiciousErrorException("SQLicious DatabaseProcessor multiQuery SQL Error. Reason given " . $this->connection->error);
			}
			
			if(!$this->connection->next_result() && $this->connection->error == null)
			{
				break;
			}
			
		} while (true);
		
		$this->connection->close();
	}
	
	function update($sql)
	{
		$this->result = $this->getMySQLResult($sql);
		$this->freeResult();
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
			throw new SQLiciousErrorException("SQLicious DatabaseProcessor SQL Error. MySQL Query Failed: " . htmlentities($sql) . '\n\nReason given ' . $e . '\n\n');
		}
	}
	
	
	
	// convert timezones
//	function convertTimezone($dateTime,$sourceTimezone,$destTimezone)
//	{
//		if(!is_integer($dateTime))
//		{
//			if(strtotime($dateTime) !== false)
//			{
//				return $this->convertTimezone(strtotime($dateTime),$sourceTimezone,$destTimezone);
//			}
//		}
//		else
//		{
//			$result = $this->getMySQLResult("SELECT CONVERT_TZ('2004-01-01 12:00:00','" . $this->escapeString($sourceTimezone) . "','" . $this->escapeString($destTimezone) . "');");
//			if($result != null)
//			{
//				$row = mysql_fetch_row($result);
//	
//				mysql_free_result($result);
//	
//				return strtotime(reset($row));
//			}
//				
//		}
//	
//		// failed
//		return false;
//	}
	
	function getNumberOfRows()
	{
		return $this->numberOfRows;
	}
	
	function freeResult()
	{
		if($this->result != null)
		{
			try
			{
				if($this->result instanceof mysqli_result)
				{
					$this->result->free();
				}
				
			}
			catch(ErrorException $e)
			{
				// Do nothing. My eyes! The goggles do nothing!
			}
			
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
	
//	function outputCSV()
//	{
//		$this->unbufferedProcess(function($obj)
//		{
//			echo $obj->toCSV();
//			echo "\n";
//		});
//	}
	
//	function explain()
//	{
//		$explain = $this->getMySQLResult('EXPLAIN ' . $this->getSQL());
//	
//		$params = mysql_fetch_assoc($explain);
//	
//		@mysql_free_result($explain);
//	
//		return $params;
//	}
	
//	function queryTest()
//	{
//		echo '<pre>';
//	
//		echo 'SQL: ' . htmlentities($this->getSQL());
//	
//		echo "\n\n";
//	
//		echo 'EXPLAIN: ' . htmlentities(print_r($this->explain(),true));
//	
//		echo "\n\n";
//	
//		$result = $this->query();
//	
//		echo 'ROW COUNT: '  . htmlentities($this->getNumberOfRows(),true);
//	
//		echo "\n\n";
//	
//		echo 'FIRST ROW: ' . htmlentities(print_r(reset($this->getArray()),true));
//	
//		$this->freeResult();
//	
//		echo '</pre>';
//	
//	}
	
	
	function connectToMySQLDatabase()
	{
		$this->connection = new mysqli($this->databaseNode->serverHost, $this->databaseNode->serverUsername, $this->databaseNode->serverPassword, $this->databaseNode->serverDatabaseName, $this->databaseNode->serverPort ? $this->databaseNode->serverPort : null, $this->databaseNode->serverSocket);
		
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
	
	// deprecate
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