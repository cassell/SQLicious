<?php

include('../api.inc.php');

$db = $generator->databases[$_GET['database']];

if($db != null)
{
	$resp['databaseName'] = $_GET['database'];
	//$resp['tables'] = $db->getTableNames();
	foreach($db->getTableNames() as $name)
	{
		$resp['tables'][] = array("tableName" => $name, "databaseName" => $_GET['database']);
	}
	returnResponse($resp);
}
else
{
	returnError('Database not found.');
}


?>