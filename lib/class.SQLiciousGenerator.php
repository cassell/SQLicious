<?php

class SQLiciousGeneratorDatabase
{
	function __construct()
	{
		
	}
	
	function setDatabaseName($val) { $this->databaseName = $val; }
	function getDatabaseName() { return $this->databaseName; }
	
	function setDatabaseHost($val) { $this->databaseHost = $val; }
	function getDatabaseHost() { return $this->databaseHost; }
	
	function setDatabasePassword($val) { $this->databasePassword = $val; }
	function getDatabasePassword() { return $this->databasePassword; }
	
	function setDatabaseUsername($val) { $this->databaseUsername = $val; }
	function getDatabaseUsername() { return $this->databaseUsername; }	
	
	function setGeneratorDestinationDirectory($val) { $this->generatorDestinationDirectory = $val; }
	function getGeneratorDestinationDirectory() { return $this->generatorDestinationDirectory; }
	
	function getTableNames()
	{
		$data = $this->sqliciousQuery('SHOW TABLES');
		
		$tableNames = array();
		
		if($data != null && count($data) > 0)
		{
			foreach($data as $d)
			{
				$tableNames[] = $d['Tables_in_'.$this->getDatabaseName()];
			}
		}
		
		return $tableNames;
	}
	
	function sqliciousQuery($sql)
	{
		$conn = DatabaseConnector::openMasterConnection($this->getDatabaseName());
		
		$result = mysql_query($sql, $conn) or trigger_error("error.dao.sql!!!EXP!!!". $sql, E_USER_ERROR);
		
		$assoc = array();
		while ($row = mysql_fetch_assoc($result))
		{
			$assoc[] = $row;
		}
		mysql_free_result($result);
		
		return $assoc;
	}
	
	function getDaoObjectClassContents($tableName)
	{
		$className = ucfirst(SQLiciousGenerator::toFieldCase($tableName));
		
		$idFieldName = "";
		$fieldsPack = array();
		$setsAndGetsPack = array();
		$defaultRowPack = array();
		
		// get columns
		$columns = $this->getColumns($tableName);
		if($columns != null && count($columns) > 0)
		{
			foreach($columns as $column)
			{
				if($column['Key'] == "PRI")
				{
					$idFieldName = $column['Field'];
				}
				
				if($column['Type'] == "datetime")
				{
					$setsAndGetsPack[] = "\tfinal function set" . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . '($val) { $this->setDatetimeFieldValue(\'' . $column['Field'] .'\',$val); }' . "\n" . "\tfinal function get" . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . '() { return $this->getFieldValue(\'' . $column['Field'] .'\'); }' . "\n";
				}
				else
				{
					$setsAndGetsPack[] = "\tfinal function set" . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . '($val) { $this->setFieldValue(\'' . $column['Field'] .'\',$val); }' . "\n" . "\tfinal function get" . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . '() { return $this->getFieldValue(\'' . $column['Field'] .'\'); }' . "\n";
				}
				
				if($column['Default'] == null)
				{
					$defaultRowPack[] = "'" . $column['Field'] . "' => null";
				}
				else
				{
					$defaultRowPack[] = "'" . $column['Field'] . "' => '" . $column['Default'] . "'";
				}
				
			}
		}
		
		$contents  = "<?php\n";
		$contents .= "\n";
		$contents .= "/* This file is generated by the SQLicious Generator. www.sqlicious.com */\n";
		$contents .= "\n";
		$contents .= "require_once('class.". $className . "DaoFactory.php');\n";
		$contents .= "\n";
		$contents .= "class " .$className . "DaoObject extends DataAccessObject\n";
		$contents .= "{\n";
		
		// construct
		$contents .= "\tfunction __construct(\$row = null)\n";
		$contents .= "\t{\n";
		$contents .= "\t\tparent::__construct(\$row);\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		$contents .= "\tfunction " . $className . "DaoObject(\$row = null)\n";
		$contents .= "\t{\n";
		$contents .= "\t\tself::__construct(\$row);\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// getDatabaseName
		$contents .= "\tfunction getDatabaseName()\n";
		$contents .= "\t{\n";
		$contents .= "\t\treturn '" . $this->getDatabaseName() . "';\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// getTableName
		$contents .= "\tfunction getTableName()\n";
		$contents .= "\t{\n";
		$contents .= "\t\treturn '" . $tableName . "';\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// getIdField
		$contents .= "\tfunction getIdField()\n";
		$contents .= "\t{\n";
		$contents .= "\t\treturn '" . $idFieldName . "';\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// getFactory
		$contents .= "\tfunction getFactory()\n";
		$contents .= "\t{\n";
		$contents .= "\t\treturn new " . $className . "DaoFactory();\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// sets and gets
		$contents .= implode("\n",$setsAndGetsPack);
		$contents .= "\n";
		
		// getDatabaseName
		$contents .= "\tfunction getDefaultRow()\n";
		$contents .= "\t{\n";
		$contents .= "\t\treturn array(" . implode(",",$defaultRowPack) . ");\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		$contents .= "}\n";
		
		$contents .= '?>';
		
		return $contents;
	}
	
	function getDaoFactoryClassContents($tableName)
	{
		$className = ucfirst(SQLiciousGenerator::toFieldCase($tableName));
		
		$fieldsPack = array();
		
		// get columns
		$columns = $this->getColumns($tableName);
		if($columns != null && count($columns) > 0)
		{
			foreach($columns as $column)
			{
				if($column['Key'] == "PRI")
				{
					$idFieldName = $column['Field'];
				}
				
				$fieldsPack[] = "'" . $column['Field'] . "'";
			}
		}
		
		$contents  = "<?php\n";
		$contents .= "\n";
		$contents .= "/* This file is generated by the SQLicious Generator. www.sqlicious.com */\n";
		$contents .= "\n";
		$contents .= "require_once('class.". $className . "DaoObject.php');\n";
		$contents .= "\n";
		$contents .= "class " .$className . "DaoFactory extends DataAccessObjectFactory\n";
		$contents .= "{\n";
		
		// construct
		$contents .= "\tfunction __construct()\n";
		$contents .= "\t{\n";
		$contents .= "\t\tparent::__construct();\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// deprecate later
		$contents .= "\tfunction " . $className . "DaoFactory()\n";
		$contents .= "\t{\n";
		$contents .= "\t\tself::__construct();\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// getDatabaseName
		$contents .= "\tfunction getDatabaseName()\n";
		$contents .= "\t{\n";
		$contents .= "\t\treturn '" . $this->getDatabaseName() . "';\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// getTableName
		$contents .= "\tfunction getTableName()\n";
		$contents .= "\t{\n";
		$contents .= "\t\treturn '" . $tableName . "';\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// getIdField
		$contents .= "\tfunction getIdField()\n";
		$contents .= "\t{\n";
		$contents .= "\t\treturn '" . $idFieldName . "';\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// loadObject
		$contents .= "\tfunction loadObject(\$row)\n";
		$contents .= "\t{\n";
		$contents .= "\t\treturn new " . $className . "DaoObject(\$row);\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// getFields
		$contents .= "\tfunction getFields()\n";
		$contents .= "\t{\n";
		$contents .= "\t\treturn array(" . implode(", ",$fieldsPack). ");\n";
		$contents .= "\t}\n";
		$contents .= "}\n";
		
		return $contents;
		
	}
	
	function getColumns($tableName)
	{
		return $this->sqliciousQuery("SHOW COLUMNS FROM " . mysql_real_escape_string($tableName) . "");
	}
	
}




class SQLiciousGenerator
{
	function __construct()
	{
		$this->databases = array();
	}
	
	function setDatabaseConnectorDestinationDirectory($val) { $this->databaseConnectorDestinationDirectory = $val; }
	function getDatabaseConnectorDestinationDirectory() { return $this->databaseConnectorDestinationDirectory; }
	
	function addDatabase($sqliciousDatabase)
	{
		$this->databases[$sqliciousDatabase->getDatabaseName()] = $sqliciousDatabase;
	}
	
	function generate()
	{
		// generate the database connector
		if(!$this->generateDatabaseConnector()) { return false; }
		
		// now include the database connector
		include($this->getDatabaseConnectorDestinationDirectory().'/class.DatabaseConnector.php');
		if(!class_exists('DatabaseConnector')) { return false; }
		
		foreach($this->databases as $database)
		{
			if(!$this->generateDatabaseDestinationDirectory($database))
			{
				return false;
			}
			
			if(!$this->generateTableClasses($database))
			{
				return false;
			}
		}
		
		// methods succeeded
		return true;
	}
	
	function generateTableClasses($database)
	{
		$tables = $database->getTableNames();
		
		if($tables != null && count($tables) > 0)
		{
			foreach($tables as $tableName)
			{
				$className = ucfirst($this->toFieldCase($tableName));
				
				if(!$this->writeContents($database->getGeneratorDestinationDirectory().'/class.' . $className . 'DaoFactory.php',$database->getDaoFactoryClassContents($tableName)))
				{
					return false;
				}
				if(!$this->writeContents($database->getGeneratorDestinationDirectory().'/class.' . $className . 'DaoObject.php',$database->getDaoObjectClassContents($tableName)))
				{
					return false;
				}
			}
			
			return true;
		}
		else
		{
			$this->setErrorMessage("No tables in database.");
			return false;
		}
	}
	
	
	function generateDatabaseConnector()
	{
		// if methods fail in this function return to calling script
		if(!$this->generateDatabaseConnectorDestinationDirectory()) { return false; }
		
		$contents  = "<?php\n";
		$contents .= "\n";
		$contents .= "/* This file is generated by the SQLicious Generator. www.sqlicious.com */\n";
		$contents .= "\n";
		$contents .= "class DatabaseConnector\n";
		$contents .= "{\n";
		
		// getMasterDatabase
		$contents .= "\tfunction getMasterDatabase(\$databaseName)\n";
		$contents .= "\t{\n";
		$contents .= "\t\t\$db = array();\n";
		
		$databaseContent = array();
		
		foreach($this->databases as $database)
		{
			$databaseContent[] = "\t\t" . '$db[\'' . $database->getDatabaseName() . '\'] = array(\'name\' => \'' . $database->getDatabaseName() . '\', \'host\' => \'' . $database->getDatabaseHost() . '\', \'username\' => \'' . $database->getDatabaseUsername() . '\', \'password\' => \'' . $database->getDatabasePassword() . '\');';
		}
		
		$contents .= implode("\n",$databaseContent)."\n";
		$contents .= "\t\treturn \$db[\$databaseName];\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// openConnection
		$contents .= "\tstatic function openConnection(\$db)\n";
		$contents .= "\t{\n";
		$contents .= "\t\t\$conn = mysql_connect(\$db['host'], \$db['username'], \$db['password']) or trigger_error(\"error.dao.connect!!!EXP!!!\". \$sql, E_USER_ERROR);\n";
		$contents .= "\t\tmysql_select_db(\$db['name'],\$conn) or trigger_error(\"error.dao.sql!!!EXP!!!\". \$sql, E_USER_ERROR);\n";
		$contents .= "\t\treturn \$conn;\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		
		// openMasterConnection
		$contents .= "\tstatic function openMasterConnection(\$databaseName)\n";
		$contents .= "\t{\n";
		$contents .= "\t\treturn self::openConnection(self::getMasterDatabase(\$databaseName));\n";
		$contents .= "\t}\n";
		$contents .= "\n";
		$contents .= "}\n";
		$contents .= '?>';
		
		return $this->writeContents($this->getDatabaseConnectorDestinationDirectory().'/class.DatabaseConnector.php',$contents);
		
		
	}
	
	function generateDatabaseDestinationDirectory($database)
	{
		if($database->getGeneratorDestinationDirectory() != "")
		{
			if(!file_exists($database->getGeneratorDestinationDirectory()))
			{
				if(!mkdir($database->getGeneratorDestinationDirectory()))
				{
					$this->setErrorMessage('Unable to create database destination directory.');
					return false;
				}
			}
			else
			{
				// folder exists
				return true;
			}
		}
		else
		{
			$this->setErrorMessage('Database destination directory not specified.');
			return false;
		}
	}
	
	function generateDatabaseConnectorDestinationDirectory()
	{
		if($this->getDatabaseConnectorDestinationDirectory() != "")
		{
			if(!file_exists($this->getDatabaseConnectorDestinationDirectory()))
			{
				if(!@mkdir($this->getDatabaseConnectorDestinationDirectory()))
				{
					$this->setErrorMessage('Unable to create database connector destination directory. ' . $this->getDatabaseConnectorDestinationDirectory());
					return false;
				}
			}
			else
			{
				// folder exists
				return true;
			}
		}
		else
		{
			$this->setErrorMessage('Database connector destination directory not specified. Add setCopyClassesToDestinationDirectory() to your config.');
			return false;
		}
	}
	
	function writeContents($fileName,$contents)
	{
		if(file_exists($fileName) && !is_writable($fileName))
		{
			$this->setErrorMessage('File is unwritable: ' . $fileName);
			return false;
		}
		else if(file_put_contents($fileName,$contents) !== FALSE)
		{
			return true;
		}
		else
		{
			$this->setErrorMessage('Unable to write file: ' . $fileName);
			return false;
		}
	}
	
	static function jsonEncode($array)
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
	
	function setErrorMessage($val) { $this->errorMessage = $val; }
	function getErrorMessage() { return $this->errorMessage; }
	
}

?>