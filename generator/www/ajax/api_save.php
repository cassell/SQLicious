<?php

include('ajax.inc.php');

$db = $generator->databases[$_POST['database']];

if($db != null && $_POST['table'] != "")
{
	$table = $_POST['table'];
	$columns = $db->getColumns($table);
	
	$resp['html'] = "<p>Test</p>";
	
//	if($columns != null && count($columns) > 0)
//	{
//		foreach($columns as $column)
//		{
//			$c = array();
//			$c['name'] = $column['Field'];
//			$c['type'] = $column['Type'];
//			$c['null'] = ($column['Null'] == 'YES' ? 1 : 0);
//			$c['default'] = $column['Default'];
//			
//			if($column['Key'] == "PRI")
//			{
//				$c['getter'] = '$' . SQLiciousGenerator::toFieldCase($table) . '->getId()';
//				$c['setter'] = '';
//			}
//			else
//			{
//				$c['getter'] = '$' . SQLiciousGenerator::toFieldCase($table) . '->get' . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . '()';
//				$c['setter'] = '$' . SQLiciousGenerator::toFieldCase($table) . '->set' . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . '($val)';
//			}
//			
//			
//			
//			$resp['columns'][] = $c;
//			
//			if($column['Key'] == "PRI")
//			{
//				$resp['idField'] =  $column['Field'];
//			}
//			
//		}
//	}
	
	returnResponse($resp);
}
else
{
	returnError('Database not found.');
}


?>