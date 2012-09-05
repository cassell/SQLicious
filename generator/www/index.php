<?php

require_once('../generator.config.inc.php');

require_once('inc/class.SQLiciousPage.php');

$page = new SQLiciousPage($generator);

$page->display();

?>