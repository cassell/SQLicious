<?php

$GLOBALS['DATABASE_CONFIG']['application'] = new DatabaseConfiguration();
$GLOBALS['DATABASE_CONFIG']['application']->configureMaster('application', 'localhost', 'mysqluser', 'mysqlpassword');

$GLOBALS['DATABASE_CONFIG']['mail'] = new DatabaseConfiguration();
$GLOBALS['DATABASE_CONFIG']['mail']->configureMaster('mail', 'localhost', 'mysqluser', 'mysqlpassword');

$GLOBALS['DATABASE_CONFIG']['stats'] = new DatabaseConfiguration();
$GLOBALS['DATABASE_CONFIG']['stats']->configureMaster('stats', 'localhost', 'mysqluser', 'mysqlpassword');

?>