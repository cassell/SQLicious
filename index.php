<?php

include('config.inc.php');

require_once('www/inc/class.SQLiciousPage.php');

$page = new SQLiciousPage($generator);

$page->display();

?>