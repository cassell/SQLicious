<?php

$GLOBALS[SQLICIOUS_CONFIG_GLOBAL] = new SQLiciousConfig();

$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['application'] = new DatabaseConfiguration('application');
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['application']->configureMaster($_SERVER['MYSQL_DB_NAME'], $_SERVER['MYSQL_DB_HOST'], $_SERVER['MYSQL_USERNAME'], $_SERVER['MYSQL_PASSWORD']);
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['application']->setGeneratorCodeDestinationDirectory('/Library/WebServer/Documents/application/inc/sqlicious/dao/application'); // this is only used in development

?>