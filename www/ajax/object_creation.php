<?php

include('../../config.inc.php');
include('../../www/ajax/ajax.inc.php');

require_once($generator->getDatabaseConnectorDestinationDirectory().'/class.DatabaseConnector.php');


$GLOBALS['className'] = ucfirst(SQLiciousGenerator::toFieldCase($_POST['table']));

function setExtendedClass($filename)
{
	$GLOBALS['extendedClass'] = $filename;
	$GLOBALS['className'] = $GLOBALS['className'];
}


if($tools != null && $tools->getLookForExtendedObjects() && $_POST['daoObjectOnly'] == null)
{
	// look for dao factories
	if(count($tools->getIncludePaths()) > 0)
	{
		foreach($tools->getIncludePaths() as $path)
		{
			$tools->find_files($path,"/class.".$className."DaoFactory.php/i",setExtendedClass);
		}
	}
	
	// look for dao object
	if(count($tools->getIncludePaths()) > 0)
	{
		foreach($tools->getIncludePaths() as $path)
		{
			$tools->find_files($path,"/class.".$className.".php/i",setExtendedClass);
		}
	}
	
}




$db = $generator->databases[$_POST['database']];

if($db != null)
{
	DatabaseConnector::openMasterConnection($db->getDatabaseName());
	$columns = $db->getColumns($_POST['table']);
	
	if($columns != null && count($columns) > 0)
	{
		foreach($columns as $column)
		{
			$c = array();
			if($column['Key'] != "PRI")
			{
				$resp['columns'][] = array('name' => $column['Field'], 'setter' => 'set' . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])));
			}
		}
	}
	
	if($GLOBALS['extendedClass'] != "")
	{
		//$resp['include'] = "require_once(".$tools->stringReplaceFilePath($GLOBALS['extendedClass']) . ");\n\n";
		
		$resp['include'] = $tools->getPHPRequireOnce($GLOBALS['extendedClass']) . "\n\n";
		
		$resp['className'] = $GLOBALS['className'];
	}
	
	returnResponse($resp);
}
else
{
	returnError('Database not found.');
}


?>