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
		$resp['factory']['html'] = '';
		$resp['object']['html'] = '';
		
		$resp['className'] = ucfirst(SQLiciousGenerator::toFieldCase($table));
		$resp['include'] = $tools->getDaoClassRequireOnce($db,ucfirst(SQLiciousGenerator::toFieldCase($table).'DaoObject'));
		
// 		if($tools->getLookForExtendedObjects())
// 		{
// 			if($tools->doesExtendedDaoObjectExist(ucfirst(SQLiciousGenerator::toFieldCase($table))))
// 			{
// 				$resp['className']  = ucfirst(SQLiciousGenerator::toFieldCase($table));
// 				$resp['include'] = $tools->getExtendedDaoClassRequireOnce($db,ucfirst(SQLiciousGenerator::toFieldCase($table)));
// 			}
// 		}
// 		else
// 		{
			
// 		}
		
		$resp['factory']['html'] .= "<?php" . "\n";
		$resp['factory']['html'] .= $tools->getDaoClassRequireOnce($db,ucfirst(SQLiciousGenerator::toFieldCase($table).'DaoFactory')) . "\n";
		$resp['factory']['html'] .= "require_once(/* place extended object require_once here */);";
		$resp['factory']['html'] .= "\n";
		$resp['factory']['html'] .= "\n";
		$resp['factory']['html'] .= "class " . $resp['className'] . "Factory extends " . $resp['className'] . "DaoFactory" . "\n";
		$resp['factory']['html'] .= "{" . "\n";
		$resp['factory']['html'] .= "\tfunction __construct()" . "\n";
		$resp['factory']['html'] .= "\t{" . "\n";
		$resp['factory']['html'] .= "\t\tparent::__construct();" . "\n";
		$resp['factory']['html'] .= "\t}" . "\n";
		$resp['factory']['html'] .= "" . "\n";
		$resp['factory']['html'] .= "\tfunction loadDataObject(\$row = null)" . "\n";
		$resp['factory']['html'] .= "\t{" . "\n";
		$resp['factory']['html'] .= "\t\treturn new " .  $resp['className'] . "(\$row);" . "\n";
		$resp['factory']['html'] .= "\t}" . "\n";
		$resp['factory']['html'] .= "}" . "\n";
		$resp['factory']['html'] .= "\n";
		$resp['factory']['html'] .= "?>";
		
		
		$resp['object']['html'] .= "<?php" . "\n";
		$resp['object']['html'] .= $tools->getDaoClassRequireOnce($db,ucfirst(SQLiciousGenerator::toFieldCase($table).'DaoObject')) . "\n";
		$resp['object']['html'] .= "require_once(/* place extended factory require_once here */);";
		$resp['object']['html'] .= "\n";
		$resp['object']['html'] .= "\n";
		$resp['object']['html'] .= "class " . $resp['className'] . " extends " . $resp['className'] . "DaoObject" . "\n";
		$resp['object']['html'] .= "{" . "\n";
		$resp['object']['html'] .= "\tfunction __construct(\$row = null)" . "\n";
		$resp['object']['html'] .= "\t{" . "\n";
		$resp['object']['html'] .= "\t\tparent::__construct(\$row);" . "\n";
		$resp['object']['html'] .= "\t}" . "\n";
		$resp['object']['html'] .= "" . "\n";
		$resp['object']['html'] .= "\tfunction getFactory()" . "\n";
		$resp['object']['html'] .= "\t{" . "\n";
		$resp['object']['html'] .= "\t\treturn new " .  $resp['className'] . "Factory();" . "\n";
		$resp['object']['html'] .= "\t}" . "\n";
		$resp['object']['html'] .= "}" . "\n";
		$resp['object']['html'] .= "\n";
		$resp['object']['html'] .= "?>";
		
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