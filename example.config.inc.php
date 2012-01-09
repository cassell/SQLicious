<?php

include('lib/class.SQLiciousGenerator.php');
include('lib/class.SQLiciousTools.php');

$generator = new SQLiciousGenerator();
$generator->setDatabaseConnectorDestinationDirectory('/Library/WebServer/Documents/dao');

$exampleDatabase = new SQLiciousGeneratorDatabase();
$exampleDatabase->setDatabaseName('example');
$exampleDatabase->setDatabaseHost('127.0.0.1');
$exampleDatabase->setDatabaseUsername('user');
$exampleDatabase->setDatabasePassword('password');
$exampleDatabase->setGeneratorDestinationDirectory('/Library/WebServer/Documents/dao/intranet');

$generator->addDatabase($exampleDatabase);

$tools = new SQLiciousTools();
$tools->setLookForExtendedObjects(true);
$tools->addIncludePath("/Library/WebServer/Documents/dao");
$tools->addIncludePath("/Library/WebServer/Documents/project");
$tools->addIncludePathStringReplace("/Library/WebServer/Documents/lib/trunk/source/", "Properties::LIB",true);
$tools->addIncludePathStringReplace("/Library/WebServer/Documents/intranet/trunk/source/", "Properties::DOC",true);

?>