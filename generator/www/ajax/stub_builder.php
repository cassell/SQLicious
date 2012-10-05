<?php

include('ajax.inc.php');

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
		
		$resp['stub'] = array();
		
		$resp['stub']['html'] = "<?php" . "\n";
		$resp['stub']['html'] .= "\n";
		$resp['stub']['html'] .= "class " . $resp['className'] . "Factory extends " . $resp['className'] . "DaoFactory" . "\n";
		$resp['stub']['html'] .= "{" . "\n";
		$resp['stub']['html'] .= "\tfunction __construct()" . "\n";
		$resp['stub']['html'] .= "\t{" . "\n";
		$resp['stub']['html'] .= "\t\tparent::__construct();" . "\n";
		$resp['stub']['html'] .= "\t}" . "\n";
		$resp['stub']['html'] .= "" . "\n";
		$resp['stub']['html'] .= "\tfunction loadDataObject(\$row = null)" . "\n";
		$resp['stub']['html'] .= "\t{" . "\n";
		$resp['stub']['html'] .= "\t\treturn new " .  $resp['className'] . "(\$row);" . "\n";
		$resp['stub']['html'] .= "\t}" . "\n";
		$resp['stub']['html'] .= "}" . "\n";
		$resp['stub']['html'] .= "\n";
		$resp['stub']['html'] .= "\n";
		$resp['stub']['html'] .= "\n";
		$resp['stub']['html'] .= "class " . $resp['className'] . " extends " . $resp['className'] . "DaoObject" . "\n";
		$resp['stub']['html'] .= "{" . "\n";
		$resp['stub']['html'] .= "\tfunction __construct(\$row = null)" . "\n";
		$resp['stub']['html'] .= "\t{" . "\n";
		$resp['stub']['html'] .= "\t\tparent::__construct(\$row);" . "\n";
		$resp['stub']['html'] .= "\t}" . "\n";
		$resp['stub']['html'] .= "" . "\n";
		$resp['stub']['html'] .= "\tfunction getFactory()" . "\n";
		$resp['stub']['html'] .= "\t{" . "\n";
		$resp['stub']['html'] .= "\t\treturn new " .  $resp['className'] . "Factory();" . "\n";
		$resp['stub']['html'] .= "\t}" . "\n";
		$resp['stub']['html'] .= "}" . "\n";
		$resp['stub']['html'] .= "\n";
		$resp['stub']['html'] .= "?>";
		
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