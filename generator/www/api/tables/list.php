<?php

include('../api.inc.php');

$db = $generator->databases[$_GET['database']];

if($db != null)
{
	$resp['databaseName'] = $_GET['database'];
	$resp['tables'] = $db->getTableNames();
	returnResponse($resp);
}
else
{
	returnError('Database not found.');
}


?>