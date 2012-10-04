<?php

/*

$_SERVER['MYSQL_DB_NAME'] = "sqlicious_test";
$_SERVER['MYSQL_DB_HOST'] = "localhost";
$_SERVER['MYSQL_USERNAME'] = "username";
$_SERVER['MYSQL_PASSWORD'] = "password";

*/

define("TESTS_CONFIG_PATH", dirname(__FILE__) . '/');

if(file_exists(TESTS_CONFIG_PATH.'tests.environment.inc.php'))
{
	include(TESTS_CONFIG_PATH.'tests.environment.inc.php');
}

include(TESTS_CONFIG_PATH.'../../sqlicious.inc.php');

$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['sqlicious_test'] = new DatabaseConfiguration();
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['sqlicious_test']->configureMaster($_SERVER['MYSQL_DB_NAME'], $_SERVER['MYSQL_DB_HOST'], $_SERVER['MYSQL_USERNAME'], $_SERVER['MYSQL_PASSWORD'], $_SERVER['MYSQL_PASSWORD']);
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['sqlicious_test']->setGeneratorCodeDestinationDirectory(TESTS_CONFIG_PATH."../dao");


?>