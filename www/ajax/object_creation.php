<?php

include('../../config.inc.php');
include('../../www/ajax/ajax.inc.php');

$db = $generator->databases[$_POST['database']];
$table = $_POST['table'];
$columns = $db->getColumns($table);

if($db != null)
{
	if($table != null)
	{
		$resp['className'] = ucfirst(SQLiciousGenerator::toFieldCase($table));
		$resp['include'] = $tools->getDaoClassRequireOnce($db,ucfirst(SQLiciousGenerator::toFieldCase($table).'DaoObject'));
			
		if($tools->getLookForExtendedObjects())
		{
			if($tools->doesExtendedDaoObjectExist(ucfirst(SQLiciousGenerator::toFieldCase($table))))
			{
				$resp['className']  = ucfirst(SQLiciousGenerator::toFieldCase($table));
				$resp['include'] = $tools->getExtendedDaoClassRequireOnce($db,ucfirst(SQLiciousGenerator::toFieldCase($table)));
			}
		}
		else
		{
			
		}
		
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