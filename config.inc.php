<?php

include('lib/class.SQLiciousGenerator.php');
include('lib/class.SQLiciousTools.php');

$generator = new SQLiciousGenerator();
$generator->setDatabaseConnectorDestinationDirectory('/Library/WebServer/Documents/lib/trunk/source/dao');

$intranet = new SQLiciousGeneratorDatabase();
$intranet->setDatabaseName('intranet');
$intranet->setDatabaseHost('127.0.0.1');
$intranet->setDatabaseUsername('web');
$intranet->setDatabasePassword('spillresponse');
$intranet->setGeneratorDestinationDirectory('/Library/WebServer/Documents/lib/trunk/source/dao/intranet');

$customerSurvey = new SQLiciousGeneratorDatabase();
$customerSurvey->setDatabaseName('customer_survey');
$customerSurvey->setDatabaseHost('127.0.0.1');
$customerSurvey->setDatabaseUsername('web');
$customerSurvey->setDatabasePassword('spillresponse');
$customerSurvey->setGeneratorDestinationDirectory('/Library/WebServer/Documents/lib/trunk/source/dao/customer_survey');

$responseHero = new SQLiciousGeneratorDatabase();
$responseHero->setDatabaseName('rh');
$responseHero->setDatabaseHost('127.0.0.1');
$responseHero->setDatabaseUsername('web');
$responseHero->setDatabasePassword('spillresponse');
$responseHero->setGeneratorDestinationDirectory('/Library/WebServer/Documents/lib/trunk/source/dao/rh');

$mail = new SQLiciousGeneratorDatabase();
$mail->setDatabaseName('mail');
$mail->setDatabaseHost('127.0.0.1');
$mail->setDatabaseUsername('web');
$mail->setDatabasePassword('spillresponse');
$mail->setGeneratorDestinationDirectory('/Library/WebServer/Documents/lib/trunk/source/dao/mail');

$generator->addDatabase($intranet);
$generator->addDatabase($mail);
$generator->addDatabase($customerSurvey);
$generator->addDatabase($responseHero);


$tools = new SQLiciousTools();
$tools->setLookForExtendedObjects(true);
$tools->addIncludePath("/Library/WebServer/Documents/lib");
$tools->addIncludePath("/Library/WebServer/Documents/intranet");
$tools->addIncludePathStringReplace("/Library/WebServer/Documents/lib/trunk/source/", "Properties::LIB",true);
$tools->addIncludePathStringReplace("/Library/WebServer/Documents/intranet/trunk/source/", "Properties::DOC",true);

?>