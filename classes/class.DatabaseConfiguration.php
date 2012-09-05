<?php

class DatabaseConfiguration
{
	private $master;
	private $slaves = array();
	private $generatorCodeDestinationDirectory = '';

	function __construct()
	{
		
	}
	
	function configureMaster($mysqlDatabaseName,$host,$username,$password)
	{
		$this->master = new DatabaseNode();
		$this->master->setMySQLDatabaseName($mysqlDatabaseName);
		$this->master->setServerHost($host);
		$this->master->setServerUserName($username);
		$this->master->setServerPassword($password);
	}
	
	function configureSlave($mysqlDatabaseName,$host,$username,$password, $slaveName = null)
	{
		$dn = new DatabaseNode();
		$dn->setMySQLDatabaseName($mysqlDatabaseName);
		$dn->setServerHost($host);
		$dn->setServerUserName($username);
		$dn->setServerPassword($password);
		$this->addSlave($dn,$slaveName);
	}
	
	function setMaster($master)
	{
		$this->master = $master;
	}
	
	function getMaster()
	{
		return $this->master;
	}
	
	function addSlave($slave, $slaveName = null)
	{
		if($slaveName != null)
		{
			$this->slaves[$slaveName] = $slave;
		}
		else
		{
			$this->slaves[] = $slave;
		}
		
	}
	
	function getSlave($slaveName = null)
	{
		if($slaveName != null && $this->slaves[$slaveName] instanceof DatabaseNode)
		{
			return $this->slaves[$slaveName];
		}
		else
		{
			return array_rand($this->slaves,1);
		}
	}
	
	function setGeneratorCodeDestinationDirectory($val) { $this->generatorCodeDestinationDirectory = $val; }
	function getGeneratorCodeDestinationDirectory() { return $this->generatorCodeDestinationDirectory; }
	
}

class DatabaseNode
{
	function __construct() { }
	
	function setMySQLDatabaseName($val) { $this->MySQLDatabaseName = $val; }
	function getMySQLDatabaseName() { return $this->MySQLDatabaseName; }
	
	function setServerHost($val) { $this->serverHost = $val; }
	function getServerHost() { return $this->serverHost; }
	
	function setServerUserName($val) { $this->serverUserName = $val; }
	function getServerUserName() { return $this->serverUserName; }

	function setServerPassword($val) { $this->serverPassword = $val; }
	function getServerPassword() { return $this->serverPassword; }
	
	function setPort($val) { $this->port = $val; }
	function getPort() { return $this->port; }
	
	function setSocket($val) { $this->socket = $val; }
	function getSocket() { return $this->socket; }
	
}



?>