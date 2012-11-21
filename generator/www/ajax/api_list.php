<?php

include('ajax.inc.php');

$db = $generator->databases[$_POST['database']];

if($db != null && $_POST['table'] != "")
{
	$resp['breadCrumb'] = 'List';
	
	$table = $_POST['table'];
	$columns = $db->getColumns($table);
	
	$resp['html'] = $db->getApiListCode($table);
	
	returnResponse($resp);
}
else
{
	returnError('Database not found.');
}


?>