<?php

$GLOBALS['DATABASE_CONFIG']['application'] = new DatabaseConfiguration();
$GLOBALS['DATABASE_CONFIG']['application']->configureMaster($_SERVER['MYSQL_DB_NAME'], $_SERVER['MYSQL_DB_HOST'], $_SERVER['MYSQL_USERNAME'], $_SERVER['MYSQL_PASSWORD']);

?>
