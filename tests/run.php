<?php

// Include the test framework
include_once('enhance/EnhanceTestFramework.php');
include_once('config/tests.config.inc.php');
include_once('units/class.DatabaseProcessorTests.php');
include_once('units/class.DaoFactoryTests.php');
include_once('units/class.DaoObjectTests.php');


// Run the tests
\Enhance\Core::runTests();


?>