<?php

include('../api.inc.php');

$db = $generator->databases[$_GET['database']];
$table = $_GET['table'];
$columns = $db->getColumns($table);

if($db != null)
{
	if($table != null)
	{
		/*
		 * $asset = new {{className}}();
$asset->setLocationId();
$asset->setEquipmentTypeLinkId();
$asset->setEquipmentId();
$asset->setName();
$asset->setParentId();
$asset->setEmail();
$asset->setUpdateDate();
$asset->setUpdatedBy();
$asset->save();

// non extened object
$asset = new {{className}}DaoObject();
		 */
		
		$resp['html'] = $db->getObjectCreationCode($table);
		returnResponse($resp);
		
//		$resp['className'] = ucfirst(SQLiciousGenerator::toFieldCase($table));
//		$resp['variableName'] = SQLiciousGenerator::toFieldCase($table);
//			
//		$columns = $db->getColumns($table);
//		
//		if($columns != null && count($columns) > 0)
//		{
//			foreach($columns as $column)
//			{
//				$c = array();
//				if($column['Key'] != "PRI")
//				{
//					$resp['columns'][] = array('name' => $column['Field'], 'setter' => 'set' . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])));
//				}
//			}
//		}
//		
//		
		
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