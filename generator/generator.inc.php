<?php

if(defined("SQLICIOUS_THIS_IS_PRODUCTION"))
{
	exit;
}

require_once(str_replace("/generator","",dirname(__FILE__))."/sqlicious.inc.php");
require_once(SQLICIOUS_INCLUDE_PATH.'/generator/lib/class.SQLiciousGenerator.php');

if(defined("SQLICIOUS_CONFIG_GLOBAL") && array_key_exists(SQLICIOUS_CONFIG_GLOBAL, $GLOBALS))
{
	$generator = new SQLiciousGenerator();
	
	foreach($GLOBALS[SQLICIOUS_CONFIG_GLOBAL] as $name => $config)
	{
		$node = $config->getMaster();
		
		$db = new SQLiciousGeneratorDatabase();
		$db->setDatabaseName($node->getMySQLDatabaseName());
		$db->setDatabaseHost($node->getServerHost());
		$db->setDatabaseUsername($node->getServerUserName());
		$db->setDatabasePassword($node->getServerPassword());
		$db->setGeneratorDestinationDirectory($config->getGeneratorCodeDestinationDirectory());
		
		$generator->addDatabase($db);
	}
	
}
else
{
	echo "Database Configuration Not Found";
	exit;
}


?>