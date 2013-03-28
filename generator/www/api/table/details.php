<?php

include('../api.inc.php');

$db = $generator->databases[$_GET['database']];

if($db != null && $_GET['table'] != "")
{
	$table = $_GET['table'];
	$columns = $db->getColumns($table);
	
	if($columns != null && count($columns) > 0)
	{
		foreach($columns as $column)
		{
			$c = array();
			$c['name'] = $column['Field'];
			$c['type'] = $column['Type'];
			$c['null'] = ($column['Null'] == 'YES' ? 1 : 0);
			$c['default'] = $column['Default'];
			
			if($column['Key'] == "PRI")
			{
				$c['getter'] = '$' . SQLiciousGenerator::toFieldCase($table) . '->getId()';
				$c['setter'] = '';
			}
			else
			{
				$c['getter'] = '$' . SQLiciousGenerator::toFieldCase($table) . '->get' . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . '()';
				$c['setter'] = '$' . SQLiciousGenerator::toFieldCase($table) . '->set' . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . '($val)';
			}
			
			
			
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