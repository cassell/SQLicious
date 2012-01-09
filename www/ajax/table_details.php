<?php

include('../../config.inc.php');
include('../../www/ajax/ajax.inc.php');

require_once($generator->getDatabaseConnectorDestinationDirectory().'/class.DatabaseConnector.php');

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
			$c['name'] = $column['Field'];
			$c['type'] = $column['Type'];
			$c['null'] = ($column['Null'] == 'YES' ? 1 : 0);
			$c['default'] = $column['Default'];
			
			$resp['columns'][] = $c;
			
			if($column['Key'] == "PRI")
			{
				$resp['idField'] =  $column['Field'];
			}
			
		}
	}
	
	returnResponse($resp);
}
else
{
	returnError('Database not found.');
}


?>