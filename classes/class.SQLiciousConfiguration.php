<?php

$GLOBALS['SQLICIOUS_DATABASE_CONFIG'] = new SQLiciousConfiguration();

class SQLiciousConfiguration
{
	public $databases;
	
	function __construct()
	{
		
	}
	
	function addDatabase($name)
	{
		$this->databases[$name] = new DatabaseConfiguration();
	}
}
?>
