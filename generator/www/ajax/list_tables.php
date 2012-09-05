<?php

include('ajax.inc.php');

$db = $generator->databases[$_POST['database']];

if($db != null)
{
	$resp['tables'] = $db->getTableNames();
	returnResponse($resp);
}
else
{
	returnError('Database not found.');
}


?>