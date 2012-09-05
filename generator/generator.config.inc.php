<?php

require_once('lib/class.SQLiciousGenerator.php');

if(array_key_exists(DatabaseProcessor::DATATBASE_CONFIG_GLOBAL_VARIABLE, $GLOBALS))
{
	$generator = new SQLiciousGenerator();
	
	foreach($GLOBALS[DatabaseProcessor::DATATBASE_CONFIG_GLOBAL_VARIABLE] as $name => $config)
	{
		$node = $config->getMaster();
		
		$node = new DatabaseNode();
		$node->setMySQLDatabaseName($mysqlDatabaseName);
		$node->setServerHost($host);
		$node->setServerUserName($username);
		$node->setServerPassword($password);
		
		$db = new SQLiciousGeneratorDatabase();
		$db->setDatabaseName('intranet');
		$db->setDatabaseHost('127.0.0.1');
		$db->setDatabaseUsername('web');
		$db->setDatabasePassword($node->get);
		$db->setGeneratorDestinationDirectory($config->getGeneratorCodeDestinationDirectory());
		
		echo $name;
		exit;
	}
	
}
else
{
	echo "Database Configuration Not Found";
	exit;
}

?>
