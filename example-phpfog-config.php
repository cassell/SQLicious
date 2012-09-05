<?php

$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['application'] = new DatabaseConfiguration();
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['application']->configureMaster($_SERVER['MYSQL_DB_NAME'], $_SERVER['MYSQL_DB_HOST'], $_SERVER['MYSQL_USERNAME'], $_SERVER['MYSQL_PASSWORD']);

?>