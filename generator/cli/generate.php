<?php

require_once('../generator.config.inc.php');

array_shift($argv);

$database = $argv[0];
$table = $argv[1];
$action = $argv[2];

if($generator->generate())
{
	echo "\n";
	echo "Generation Successful.";
	echo "\n";
}
else
{
	echo "\n";
	echo "Generation Failed.";
	echo "\n";
}

exit;


//foreach($generator->databases as $db)
//{
//	if($db->databaseName != $_GET['database'])
//	{
//		unset($generator->databases[$db->databaseName]);
//	}
//	else
//	{
//		$resp['databaseName'] = $db->databaseName;
//	}
//
//}





?>
