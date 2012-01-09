<?php

include('../../config.inc.php');
include('../../www/ajax/ajax.inc.php');

require_once($generator->getDatabaseConnectorDestinationDirectory().'/class.DatabaseConnector.php');

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