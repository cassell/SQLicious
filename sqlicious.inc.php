<?php

define("SQLICIOUS_INCLUDE_PATH", dirname(__FILE__));
define("SQLICIOUS_CONFIG_GLOBAL", "DATABASE_CONFIG");


require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.SQLiciousErrorException.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DatabaseConfiguration.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DataAccessArray.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DatabaseProcessor.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DataAccessObject.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DataAccessObjectFactory.php');

// configuration file
if(file_exists(SQLICIOUS_INCLUDE_PATH."/config.inc.php"))
{ 
	require_once(SQLICIOUS_INCLUDE_PATH."/config.inc.php");
}
else
{
	throw new SQLiciousErrorException("SQLicious configuration file (" . SQLICIOUS_INCLUDE_PATH."/config.inc.php) not found.");
}

// define SQLICIOUS_MYSQL_DATE_FORMAT if not defined in config.inc.php
if(!defined("SQLICIOUS_MYSQL_DATE_FORMAT"))
{
	define("SQLICIOUS_MYSQL_DATE_FORMAT","Y-m-d");
}

// define SQLICIOUS_MYSQL_DATETIME_FORMAT if not defined in config.inc.php
if(!defined("SQLICIOUS_MYSQL_DATETIME_FORMAT"))
{
	define("SQLICIOUS_MYSQL_DATETIME_FORMAT","Y-m-d H:i:s");
}


?>