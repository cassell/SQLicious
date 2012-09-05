<?php

include('ajax.inc.php');

$db = $generator->databases[$_POST['database']];
$table = $_POST['table'];
$columns = $db->getColumns($table);

if($db != null)
{
	if($table != null)
	{
		$resp['className'] = ucfirst(SQLiciousGenerator::toFieldCase($table));
		$resp['variableName'] = SQLiciousGenerator::toFieldCase($table);
			
		$columns = $db->getColumns($table);
		
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