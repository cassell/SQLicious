<?php

include('ajax.inc.php');

$db = $generator->databases[$_POST['database']];
$table = $_POST['table'];
$columns = $db->getColumns($table);

if($db != null)
{
	if($table != null)
	{
		$resp['html'] = $db->getExtendedObjectStub($table);
		
		returnResponse($resp);
	}
	else
	{
		returnError('Table not found.');
	}
	
}
else
{
	returnError('Database not found.');
}


?>