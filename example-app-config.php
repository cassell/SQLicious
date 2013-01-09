<?php

$GLOBALS[SQLICIOUS_CONFIG_GLOBAL] = new SQLiciousConfig();

$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['application'] = new DatabaseConfiguration('application');
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['application']->configureMaster('application', 'localhost', 'mysqluser', 'mysqlpassword');
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['application']->setGeneratorCodeDestinationDirectory('/Library/WebServer/Documents/application/inc/sqlicious/dao/application');

$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['mail'] = new DatabaseConfiguration('mail');
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['mail']->configureMaster('mail', 'localhost', 'mysqluser', 'mysqlpassword');
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['mail']->setGeneratorCodeDestinationDirectory('/Library/WebServer/Documents/application/inc/sqlicious/dao/mail');

$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['stats'] = new DatabaseConfiguration('stats');
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['stats']->configureMaster('stats', 'localhost', 'mysqluser', 'mysqlpassword');
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['stats']->setGeneratorCodeDestinationDirectory('/Library/WebServer/Documents/application/inc/sqlicious/dao/stats');

?>