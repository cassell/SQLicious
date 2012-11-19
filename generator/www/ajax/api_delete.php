<?php

include('ajax.inc.php');

$db = $generator->databases[$_POST['database']];

if($db != null && $_POST['table'] != "")
{
	$resp['breadCrumb'] = 'List';
	
	$table = $_POST['table'];
	$columns = $db->getColumns($table);
	
	$resp['html'] = '<p>Not yet implemented!<br/><br/><a href="https://github.com/cassell/SQLicious/issues/77">View on Github</a></p>';
	
	returnResponse($resp);
}
else
{
	returnError('Database not found.');
}


?>