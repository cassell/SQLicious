<?php

include('lib/class.SQLiciousGenerator.php');
include('lib/class.SQLiciousTools.php');

$generator = new SQLiciousGenerator();

$exampleDatabase = new SQLiciousGeneratorDatabase();
$exampleDatabase->setDatabaseName('example');
$exampleDatabase->setDatabaseHost('127.0.0.1');
$exampleDatabase->setDatabaseUsername('user');
$exampleDatabase->setDatabasePassword('password');
$exampleDatabase->setGeneratorDestinationDirectory('/Library/WebServer/Documents/dao/example');

$generator->addDatabase($exampleDatabase);

$tools = new SQLiciousTools();
$tools->setLookForExtendedObjects(true);
$tools->addIncludePath("/Library/WebServer/Documents/dao");
$tools->addIncludePath("/Library/WebServer/Documents/project");
$tools->addIncludePathStringReplace("/Library/WebServer/Documents/dao/", "Properties::DAO",true);
$tools->addIncludePathStringReplace("/Library/WebServer/Documents/project/trunk/source/", "Properties::DOC",true);

?>