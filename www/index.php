<?php

require_once('../config.inc.php');
require_once('inc/class.SQLiciousPage.php');

$page = new SQLiciousPage($generator);

$page->display();

?>